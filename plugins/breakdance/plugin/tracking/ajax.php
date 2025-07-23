<?php

namespace Breakdance\Tracking;

use function Breakdance\Licensing\Events\log;
use function Breakdance\Licensing\Events\getOwauid;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_track_amplitude_event',
        'Breakdance\Tracking\track_amplitude_event',
        'edit',
        true,
        [
            'args' => [
                'eventType' => FILTER_SANITIZE_SPECIAL_CHARS,
                'eventProperties' => FILTER_UNSAFE_RAW
            ],
            'optional_args' => ['eventProperties'],
        ]

    );
});

/**
 * @param string $event_type
 * @param string $event_properties
 * @return void
 */
function track_amplitude_event($event_type, $event_properties)
{
    /**
     * @var array
     */
    $event_properties_as_array = json_decode($event_properties, true);

    log(
        getOwauid(),
        $event_type,
        $event_properties_as_array
    );
}
