<?php

namespace Breakdance\AjaxEndpoints;

use function Breakdance\Data\set_meta;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_insert_post',
        'Breakdance\AjaxEndpoints\insertPost',
        'full',
        true,
        [
            'args' => [
                'postTitle' => FILTER_SANITIZE_SPECIAL_CHARS,
                'postType' => FILTER_SANITIZE_SPECIAL_CHARS
            ]
        ]
    );
});

/**
 * @param string $postTitle
 * @param string $postType
 * @return array
 * @throws \Exception
 */
function insertPost($postTitle, $postType)
{
    $post = [
        'post_title' => $postTitle,
        'post_type' => $postType,
        'post_status' => 'draft'
    ];

    $postId = wp_insert_post($post);

    if (is_wp_error($postId)) {
        /** @var \WP_Error $postId */
        $error = $postId;
        $error_code = $error->get_error_message();
        throw new \Exception( $error_code );
    }

    /** @var int $postId */
    $postId = $postId;

    // Blank Tree
    set_meta(
        $postId,
        '_breakdance_data',
        [
            'tree_json_string' => "",
        ]
    );

    /** @var string[] $allTemplateTypes */
    $allTemplateTypes = BREAKDANCE_ALL_TEMPLATE_POST_TYPES;
    if (in_array($postType, $allTemplateTypes)) {
        // Blank Template
        $settings = [
            'type' => "",
            'ruleGroups' => [],
            'priority' => 1,
            'triggers' => []
        ];
        set_meta($postId, '_breakdance_template_settings', json_encode($settings));
    }

    return [ 'postId' => $postId ];
}
