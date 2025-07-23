<?php

namespace Breakdance\Data;

use function Breakdance\Data\GlobalRevisions\add_new_revision;
use function Breakdance\Data\GlobalRevisions\load_revisions_list;
use const Breakdance\Data\GlobalRevisions\BREAKDANCE_REVISION_TYPE_PRESETS;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_save_presets',
        '\Breakdance\Data\save_presets',
        'edit',
        false,
        ['args' => ['presets' => FILTER_UNSAFE_RAW]]
    );
});

/**
 * @param string $newPresets
 * @return void
 */
function save_presets($newPresets)
{
    /** @var false|string $currentPresets */
    $currentPresets = get_global_option('presets_json_string');

    if ($newPresets !== $currentPresets) {
        $currentRevisions = load_revisions_list(BREAKDANCE_REVISION_TYPE_PRESETS);
        if (!sizeof($currentRevisions) && $currentPresets) {
            add_new_revision($currentPresets, BREAKDANCE_REVISION_TYPE_PRESETS);
        }

        set_global_option('presets_json_string', $newPresets);

        add_new_revision($newPresets, BREAKDANCE_REVISION_TYPE_PRESETS);

        \Breakdance\Render\generateCacheForGlobalSettings();
    }
}
