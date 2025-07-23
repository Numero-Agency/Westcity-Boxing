<?php

namespace Breakdance\AjaxEndpoints;

use function Breakdance\Blocks\getBlockSettings;
use function Breakdance\Data\get_meta;
use function Breakdance\Themeless\getTemplateByIdIfItExistsAndHasSettings;
use function Breakdance\Themeless\getTemplateSettingsFromDatabase;
use function Breakdance\Util\WP\performant_get_posts;

/**
 * @psalm-type DocumentMeta = array{postType:string,typeLabel:string,titleLabel:string,id:int}
 */

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_get_available_documents_with_search',
        'Breakdance\AjaxEndpoints\getAvailableDocumentsWithSearch',
        'edit',
        true,
        [
            'args' => [
                'search' => FILTER_UNSAFE_RAW,
                'postType' => FILTER_UNSAFE_RAW,
                'sortBy' => FILTER_UNSAFE_RAW,
            ]
        ]
    );
});

/**
 * @param string $searchString
 * @param string $postType
 * @param string $sortBy
 * @return array{data:DocumentMeta[]}
 */
function getAvailableDocumentsWithSearch($searchString, $postType, $sortBy)
{
    return ['data' => get_available_documents($searchString, $postType, $sortBy)];
}

/**
 * @param array $searchArgs
 * @param string $orderBy
 * @param string $order
 * @param string|false $restrictPostType
 * @return DocumentMeta[]
 */
function get_documents($searchArgs, $orderBy, $order, $restrictPostType = false)
{
    $postTypesWithoutBreakdancePostTypes = \Breakdance\Settings\get_allowed_post_types(false);

    $allPosts = performant_get_posts(
        array_merge(
            [
                'post_type' => $restrictPostType ?: $postTypesWithoutBreakdancePostTypes,
                'orderby' => $orderBy,
                'order' => $order,
                'meta_query' => [
                    [
                        'key' => '_breakdance_data',
                        'compare' => 'EXISTS'
                    ],
                ]
            ],
            $searchArgs
        )
    );

    return array_map(
        '\Breakdance\AjaxEndpoints\getDocumentMetaFromPost',
        $allPosts
    );
}

/**
 * @param array $searchArgs
 * @param string $orderBy
 * @param string $order
 * @param string|false $restrictPostType
 * @return DocumentMeta[]
 */
function get_breakdance_documents($searchArgs, $orderBy, $order, $restrictPostType = false)
{
    if ($restrictPostType) {
        $allPostTypes = [$restrictPostType];
    } else {
        /** @var string[] $allPostTypes */
        $allPostTypes = BREAKDANCE_ALL_EDITABLE_POST_TYPES ?? [];
    }

    $allPosts = performant_get_posts(
        array_merge(
            [
                'post_type' => array_filter(
                    $allPostTypes,
                    static fn($postType) => $postType !== 'breakdance_acf_block'
                ),
                'orderby' => $orderBy,
                'order' => $order,
            ],
            $searchArgs
        )
    );

    $withoutFallbacks = array_filter(
        $allPosts,
        function ($post) {
            $settings = getTemplateSettingsFromDatabase($post->ID);
            return !($settings['fallback'] ?? false);
        }
    );

    return array_map(
        '\Breakdance\AjaxEndpoints\getDocumentMetaFromPost',
        array_values($withoutFallbacks)
    );
}

/**
 * @param string|false $searchString
 * @param string|false $restrictPostType
 * @param string|false $sortBy
 * @return DocumentMeta[]
 */
function get_available_documents($searchString = false, $restrictPostType = false, $sortBy = false)
{
    /** @var string[] $postTypes */
    $postTypes = BREAKDANCE_ALL_EDITABLE_POST_TYPES ?? [];
    $isBreakdanceType = $restrictPostType && in_array($restrictPostType, $postTypes);

    // Search Query
    $searchArgs = [
        'posts_per_page' => $searchString ? TEMPLATE_POSTS_LIMIT * 5 : TEMPLATE_POSTS_LIMIT,
    ];

    if ($searchString) {
        $searchArgs['breakdance_search_post_title'] = $searchString;
    }

    // Sort By
    [$orderBy, $order] = $sortBy ?
        explode('_', $sortBy) :
        ['modified', 'desc'];

    $postsDocs     = get_documents($searchArgs, $orderBy, $order, $restrictPostType);
    $templatesDocs = get_breakdance_documents($searchArgs, $orderBy, $order, $restrictPostType);

    if ($restrictPostType && !$isBreakdanceType) {
        return $postsDocs;
    }

    if ($isBreakdanceType) {
        return $templatesDocs;
    }

    return array_merge($templatesDocs, $postsDocs);
}

/**
 * @param \WP_Post $post
 * @return DocumentMeta
 */
function getDocumentMetaFromPost($post)
{

    // TODO: memoize and/or cache for performance?
    $postTypeObj = get_post_type_object($post->post_type);

    if ($postTypeObj) {
        $labels = get_post_type_labels($postTypeObj);
        $typeLabel = (string) $labels->singular_name;
    } else {
        $typeLabel = $post->post_type;
    }

    $template = getTemplateByIdIfItExistsAndHasSettings($post->ID);
    $templateSettingsType = false;

    if ($template) {
        /**
         * @var TemplateTypeSlug $templateSettingsType
         * @psalm-suppress MixedArrayAccess
         */
        $templateSettingsType = $template['settings']['type'] ?? false;
    }

    $blockSettings = false;

    if ($post->post_type == BREAKDANCE_BLOCK_POST_TYPE) {
        $blockSettings = getBlockSettings($post->ID);
    }

    return [
        'postType' => $post->post_type,
        'typeLabel' => $typeLabel,
        'titleLabel' => $post->post_title,
        'id' => $post->ID,
        'templateSettingsType' => $templateSettingsType,
        'blockSettings' => $blockSettings,
    ];
}
