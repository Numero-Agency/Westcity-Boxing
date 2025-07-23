<?php

namespace Breakdance\DynamicData;

use function Breakdance\Blocks\getBlockSettings;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_dynamic_data_get',
        '\Breakdance\DynamicData\getData',
        'edit',
        true,
        ['args' => [
            'id' => ['filter' => FILTER_VALIDATE_INT],
            'shortcodes' => ['filter' => FILTER_DEFAULT, 'flags' => FILTER_REQUIRE_ARRAY],
        ]]
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_fetch_dynamic_repeater_fields',
        '\Breakdance\DynamicData\getDynamicRepeaterFields',
        'edit',
        true,
        []
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_fetch_flexible_content_fields',
        '\Breakdance\DynamicData\getFlexibleContentFields',
        'edit',
        true,
        []
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_fetch_flexible_content_layouts',
        '\Breakdance\DynamicData\getFlexibleContentLayouts',
        'edit',
        true,
        [
            'args' => [
                'requestData' => [
                    'filter' => FILTER_DEFAULT,
                    'flags' => FILTER_REQUIRE_ARRAY
                ]
            ]
        ]
    );
});

/**
 * @param int $id
 * @return void
 */
function maybePreviewAcfFlexibleContent($id)
{
    if ( !class_exists( '\acf' ) && !function_exists( 'acf_get_field_groups' ) ) {
        return; // Bail out if ACF is not installed.
    }

    $isGlobalBlock = get_post_type($id) == BREAKDANCE_BLOCK_POST_TYPE;

    if ($isGlobalBlock) {
        $blockSettings = getBlockSettings($id);
        $fieldName = $blockSettings['preview']['acfFlexibleField'] ?? '';
        $rowIndex = $blockSettings['preview']['acfFlexibleFieldRow'] ?? 0;
        $fieldSlug = str_replace('acf_flexible_content_', '', $fieldName);

        /** @psalm-suppress UndefinedFunction */
        if ($fieldSlug && have_rows($fieldSlug)) {
            /** @psalm-suppress UndefinedFunction */
            the_row();

            /** @psalm-suppress UndefinedFunction */
            acf_update_loop( 'active', 'i', $rowIndex );
        }
    }
}

/**
 * @param int $id
 * @param array{string,string} $shortcodes
 * @param string|null $repeaterField
 * @return array{data:array|object}
 * @throws \Exception
 */
function getData($id, $shortcodes)
{
    if (!count($shortcodes)) {
        return ['data' => (object) []];
    }

    maybePreviewAcfFlexibleContent($id);

    $result = [];
    foreach ($shortcodes as $hash => $shortcode) {
        /**
         * @psalm-suppress MixedAssignment
         */
        $result[$hash] = breakdanceDoShortcode($shortcode);
    }

    return ['data' => $result];
}

/**
 * @return array{text: mixed, value: mixed}[]
 */
function getDynamicRepeaterFields()
{
    $fields = DynamicDataController::getInstance()->getFieldsByReturnType('repeater');

    return array_values(array_map(static function($field) {
        $append = isset($field->field['group']) ? " ({$field->field['group']})" : '';

        return [
            'text' => $field->label() . $append,
            'value' => $field->slug()
        ];
    }, $fields));
}

/**
 * @return array{text: mixed, value: mixed}[]
 */
function getFlexibleContentFields()
{
    $fields = DynamicDataController::getInstance()->getFieldsByReturnType('flexible_content');

    return array_values(array_map(static function($field) {
        $append = isset($field->field['group']) ? " ({$field->field['group']})" : '';

        return [
            'text' => $field->label() . $append,
            'value' => $field->slug()
        ];
    }, $fields));
}

/**
 * @param array{ context?: string } $requestData
 * @return array|array[]
 */
function getFlexibleContentLayouts($requestData)
{
    $fieldName = $requestData['context'] ?? '';
    $field = DynamicDataController::getInstance()->getField($fieldName);

    if (!$field) {
        return [];
    }


    /**
     * @var array{label: string, name: string}[] $layouts
     * @psalm-suppress MixedArgument
     * @psalm-suppress UndefinedMethod
     */
    $layouts = $field->getLayouts();

    return array_values(array_map(
        /**
         * @param array{label: string, name: string} $layout
         * @return array
         */
        fn($layout) => [
            'text' => $layout['label'],
            'value' => $layout['name']
        ],
        $layouts
    ));
}
