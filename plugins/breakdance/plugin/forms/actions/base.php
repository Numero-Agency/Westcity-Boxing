<?php

namespace Breakdance\Forms\Actions;

abstract class Action
{


    /**  @var ActionContext[] */
    private $context = [];


    /**
     * @param string $section
     * @param mixed $context
     * @return void
     */
    public function addContext($section, $context)
    {
        if (!is_array($context)) {
            $context = ['context' => $context];
        }
        $this->context[] = ['section' => $section, 'data' => $context];
    }

    /**
     * @return ActionContext[]
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get the displayable label of the action.
     * @return string
     */
    abstract public static function name();

    /**
     * Get the URL friendly slug of the action.
     * @return string
     */
    abstract public static function slug();

    /**
     * Get controls for the builder
     * @return array
     */
    public function controls()
    {
        return [];
    }

    /**
     * Does something on form submission
     * @param FormData $form
     * @param FormSettings $settings
     * @param FormExtra $extra
     * @return ActionSuccess|ActionError|array<array-key, ActionSuccess|ActionError>
     */
    abstract public function run($form, $settings, $extra);



    /**
     * Replace any {field_name} with user submitted values
     * @param FormData $form
     * @param string $content
     * @param boolean $escapeHtml
     * @return string
     */
    public function renderData($form, $content, $escapeHtml = false)
    {
        // Search for {field_name} variables
        $search = array_map(
            /**
             * @param FormFieldWithValue $field
             * @return string
             */
            function ($field) {
                $fieldId = \Breakdance\Forms\getIdFromField($field);
                return sprintf('{%s}', $fieldId);
            },
            $form
        );

        $search[] = '{all_fields}';

        // And replace them with the value
        $replace = array_map(
            /**
             * @param FormFieldWithValue $field
             * @return string
             */
            function ($field) use ($escapeHtml) {
                return $this->formatFieldValue($field, $escapeHtml);
            },
            $form
        );

        $replace[] = $this->getFormattedTextOfAllFields($form, $escapeHtml);
        return str_replace($search, $replace, $content);
    }

    /**
     * @param FormData $form
     * @param boolean $escapeHtml
     * @return string
     */
    public function getFormattedTextOfAllFields($form, $escapeHtml = false)
    {
        $formattedFields = array_map(
            /**
             * @param FormFieldWithValue $field
             * @return mixed
             */
            function ($field) use ($escapeHtml) {
                $label = $field['label'] ?: $field['advanced']['id'];
                /** @var mixed $value */
                return "{$label}: " . $this->formatFieldValue($field, $escapeHtml);
            },
            $form
        );

        return implode(PHP_EOL, $formattedFields);
    }

    /**
     * @param FormFieldWithValue $field
     * @param boolean $escapeHtml
     * @return string
     */
    public function formatFieldValue($field, $escapeHtml = false)
    {
        $value = $field['value'];

        if ($field['type'] === 'date') {
            /** @var string $date_format */
            $date_format = get_option('date_format');
            $timezone = new \DateTimeZone('UTC');
            $value = wp_date($date_format, strtotime($value), $timezone) ?: $value;
        }

        if ($field['type'] === 'time') {
            /** @var string $time_format */
            $time_format = get_option('time_format');
            $timezone = new \DateTimeZone('UTC');
            $value = wp_date($time_format, strtotime($value), $timezone) ?: $value;
        }

        return $escapeHtml ? esc_html($value) : (string) $value;
    }

    /**
     * @return bool
     */
    public static function proOnly()
    {
        return true;
    }
}
