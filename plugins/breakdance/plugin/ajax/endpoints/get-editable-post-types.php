<?php

namespace Breakdance\AjaxEndpoints;

use function Breakdance\Util\get_public_and_allowed_post_types;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_get_editable_post_types',
        'Breakdance\AjaxEndpoints\getEditablePostTypes',
        'edit',
        true
    );
});

/**
 * @return array{value: string, text: string}[]
 */
function getEditablePostTypes()
{
    $postTypeSlugs = get_public_and_allowed_post_types();
    /** @var string[] $bdPostTypeSlugs */
    $bdPostTypeSlugs = BREAKDANCE_ALL_TEMPLATE_POST_TYPES;
    $bdGlobalBlockSlugs = array(BREAKDANCE_BLOCK_POST_TYPE);

    $slugs = array_merge($postTypeSlugs, $bdPostTypeSlugs, $bdGlobalBlockSlugs);

    return array_map(function ($slug) {
        $postTypeObj = get_post_type_object($slug);
        /** @var string $name */
        $name = $postTypeObj ? $postTypeObj->labels->singular_name : $slug;

        return [
            'value' => $slug,
            'text'  => $name,
        ];
    }, $slugs);
}
