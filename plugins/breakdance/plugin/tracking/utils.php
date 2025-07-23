<?php

namespace Breakdance\Tracking;

/**
 * @return bool
 */
function is_tracking_enabled()
{
    $optionName = 'enable_tracking';

    $hasTrackingEnabled = (string) \Breakdance\Data\get_global_option($optionName);

    return $hasTrackingEnabled === 'yes';
}
