<?php

// @psalm-ignore-file

namespace Breakdance\Partners\AnalyticsWP;

function installAnalyticsWPPlugin()
{

    \update_option('analyticswp_dont_redirect_to_homescreen_after_install', true);

    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    include_once ABSPATH . 'wp-admin/includes/file.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    $pluginUrl = 'https://analyticswp.com/latest-free-zip';

    $upgrader = new \Plugin_Upgrader();
    $installed = $upgrader->install($pluginUrl);

    if (is_wp_error($installed)) {
        echo 'AnalyticsWP Integration - error installing the plugin: ' . $installed->get_error_message();
        return;
    }

    $plugin_to_activate = $upgrader->plugin_info();

    if (!$plugin_to_activate) {
        echo 'AnalyticsWP Integration - Failed to find the plugin in the installed package.';
        return;
    }

    add_filter('wp_redirect', 'prevent_wp_redirect', 10, 1);
    function prevent_wp_redirect($location)
    {
        return false;
    }

    $activated = activate_plugin($plugin_to_activate);

    remove_filter('wp_redirect', 'prevent_wp_redirect', 10);

    if (is_wp_error($activated)) {
        echo 'AnalyticsWP Integration - Error activating the plugin: ' . $activated->get_error_message();
        return;
    }

    // success
}
