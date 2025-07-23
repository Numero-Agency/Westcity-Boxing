<?php

namespace Breakdance\DynamicData;

/**
 * @param mixed $atts
 * @return string | array
 * @throws \Exception
 */
function breakdanceShortcodeHandler($atts)
{
    if (!is_array($atts) || !array_key_exists('field', $atts)) {
        return "";
    }

    $field = DynamicDataController::getInstance()->getField((string) $atts['field']);

    if ($field) {
        ob_start();

        $fieldData = $field->handler($atts);

        /** @var string | array $output */
        $output = $fieldData->getValue($atts);

        $unexpectedOutput = ob_get_clean();

        if ($unexpectedOutput) {
            throw new \Exception('Unexpected Dynamic Data Output:' . $unexpectedOutput);
        }

        if (isset($atts['process_value']) && ($atts['process_value'] ?? false)) {
            $output = pipeValueThroughProcessValueReturn($output, (string) $atts['process_value']);
        }

        return $output;
    }

    return "";
}

/**
 * Parse a [breakdance_dynamic] shortcode and return its value
 * @param string $shortcode
 * @return string | array
 * @throws \Exception
 */
function breakdanceDoShortcode($shortcode = '')
{
    $atts = getAttributesFromShortcode($shortcode);
    return breakdanceShortcodeHandler($atts);
}

/**
 * Parse multiple [breakdance_dynamic] in a string.
 * @param string $value
 * @return string|array
 * @throws \Exception
 */
function renderDynamicShortcodes($value = '')
{
    $isOneExactShortcode = preg_match("/^\[breakdance_dynamic\b[^]]*\]$/", $value);

    if ($isOneExactShortcode) {
        return breakdanceDoShortcode($value);
    }

    return preg_replace_callback(
        '/\[breakdance_dynamic\b(?:[^\[\]]|\[.*?\])*\]/',
        /**
         * @param array<int, string> $matches
         * @return string
         * @throws \Exception
         */
        function($matches) {
            $match = $matches[0] ?? '';
            /** @var string $output */
            $output = breakdanceDoShortcode($match);
            return $output;
        },
        $value
    );
}

/**
 * @param string $shortcode
 * @return array
 */
function getAttributesFromShortcode($shortcode) {
    // Shortcodes should end with ' /]', otherwise shortcode_parse_atts doesn't work.
    $shortcode = preg_replace('/\/?]/', ' /]', $shortcode);

    /** @var array $defaultParams */
    $defaultParams = shortcode_parse_atts($shortcode);
    // Extract the "param" attribute manually as `shortcode_parse_atts` can not handle json properly.
    /** @var array<string, string> $params */
    $params = [];
    $paramsRegex = "/params='((?:[^'\\\\]|\\\\.|'(?!\s*\/?\s*\]))*)'/";

    preg_match($paramsRegex, $shortcode, $matches);

    if (!empty($matches[1])) {
        // Unescape single/double quotes.
        $jsonText = str_replace('\\\\"', '\"', $matches[1]);
        $jsonText = str_replace("\\\\'", "\'", $jsonText);

        /** @var array<string, string> $params */
        $params = json_decode($jsonText, true) ?? [];
    }

    // Remove 'param' key from the shortcode string.
    $shortcode = preg_replace($paramsRegex, '', $shortcode);

    return array_merge(
        $defaultParams,
        $params
    );
}

/**
 * @param string $shortcode
 * @return FieldData|null
 * @throws \Exception
 */
function getFieldDataFromShortcode($shortcode) {
    $attributes = getAttributesFromShortcode($shortcode);
    if (!array_key_exists('field', $attributes)) {
        return null;
    }

    $field = DynamicDataController::getInstance()->getField((string) $attributes['field']);

    if ($field) {
        ob_start();

        $fieldData = $field->handler($attributes);

        $unexpectedOutput = ob_get_clean();

        if ($unexpectedOutput) {
            throw new \Exception('Unexpected Dynamic Data Output:' . $unexpectedOutput);
        }

        return $fieldData;
    }

    return null;
}
