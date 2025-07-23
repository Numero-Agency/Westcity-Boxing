<?php

namespace Breakdance\WPUIForBuilder\TinyMce;

if (isset($_GET['breakdance_wpuiforbuilder_tinymce']) && $_GET['breakdance_wpuiforbuilder_tinymce']) {
    add_action('admin_enqueue_scripts', '\Breakdance\WPUIForBuilder\TinyMce\enqueue');
    add_action('admin_footer', '\Breakdance\WPUIForBuilder\TinyMce\inject');
}

function enqueue() {
    /** @psalm-suppress UndefinedConstant */
    $version = (string) __BREAKDANCE_VERSION;

    wp_enqueue_script('breakdance-tinymce-control', BREAKDANCE_PLUGIN_URL . "plugin/wpuiforbuilder/tinymce/tinymce.js", [], $version, true);
    wp_enqueue_style('breakdance-tinymce-control', BREAKDANCE_PLUGIN_URL . "plugin/wpuiforbuilder/tinymce/tinymce.css", [], $version);
}

function inject() {
    echo "<div id='breakdance-tinymce-wrapper'>";
    wp_editor('', 'breakdance-tinymce-control', []);
    echo "</div>";

    wp_print_media_templates();
}
