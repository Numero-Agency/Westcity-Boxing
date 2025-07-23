<?php

namespace Breakdance\DesignLibrary;
use function Breakdance\Util\validateUrl;

add_action( 'breakdance_loaded', '\Breakdance\DesignLibrary\onActivate' );
add_action( 'breakdance_update_design_library', '\Breakdance\DesignLibrary\updateDesignLibraryCron' );

/**
 * @psalm-suppress UndefinedConstant
 * @psalm-suppress MixedArgument
 */
register_deactivation_hook( __BREAKDANCE_PLUGIN_FILE__, '\Breakdance\DesignLibrary\onDeactivate' );

function onActivate() {
    if ( !wp_next_scheduled ( 'breakdance_update_design_library' ) ) {
    	wp_schedule_event( time(), 'daily', 'breakdance_update_design_library' );
    }
}

function onDeactivate() {
    wp_clear_scheduled_hook( 'breakdance_update_design_library' );
}

function updateDesignLibraryCron() {
    $providers = getRegisteredDesignSets();

    foreach ($providers as $url) {
        if ($url === "" || !validateUrl($url)) {
            continue;
        }

        getDesignSetRemoteData($url, true);
    }
}