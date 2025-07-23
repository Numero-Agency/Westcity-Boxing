<?php

namespace Breakdance\DesignPresets;

/**
 * @return ElementPreset[]
 */
function getPresetsDataForBuilder()
{
    /**
     * @psalm-suppress MixedAssignment
     */
    $presets_json_string = \Breakdance\Data\get_global_option('presets_json_string');
    if (!$presets_json_string) {
        $presets = [];
    } else {
        /**
         * @var ElementPreset[]
         */
        $presets = json_decode((string) $presets_json_string, true);
    }

    return $presets;
}

/**
 * @param string $presetId
 * @return ElementPreset|null
 */
function getPreset($presetId)
{
    $presets = getPresetsDataForBuilder();

    foreach ($presets as $preset) {
        if ($preset['id'] === $presetId) {
            return $preset;
        }
    }

    return null;
}

/**
 * @param string $cssTemplate
 * @return string
 */
function getElementCssWhenPresetIsApplied($cssTemplate) {
    $parts = explode("{#%--- Auto Generated Twig Code: ---%#}", $cssTemplate);
    return $parts[1] ?? '';
}
