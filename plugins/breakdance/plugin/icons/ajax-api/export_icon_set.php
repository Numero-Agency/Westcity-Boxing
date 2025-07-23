<?php

namespace Breakdance\Icons\AjaxApi;

use function Breakdance\Icons\generate_icon_set_zip;

/**
 * @param \WP_REST_Request $request
 * @return string|\WP_Error
 */
function export($request) {
    $slug = (string) $request->get_param('icon_set_slug');
    $file = generate_icon_set_zip($slug);

    if (is_wp_error($file)) {
        return $file;
    }

    /** @var string $file */
    $file = $file;

    header('Content-disposition: attachment; filename=breakdance-icon-set.zip');
    header('Content-type: application/zip');
    header("Content-length: " . filesize($file));
    header("Pragma: no-cache");
    header("Expires: 0");

    readfile($file);
    unlink($file);
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('breakdance/v1', '/icons/export', [
        'methods' => 'GET',
        'callback' => '\Breakdance\Icons\AjaxApi\export',
        'permission_callback' => function () {
            return \Breakdance\Permissions\hasMinimumPermission('edit');
        }
    ]);
});
