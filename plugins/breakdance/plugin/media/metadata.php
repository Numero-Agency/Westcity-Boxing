<?php

namespace Breakdance\Media\Metadata;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_image_metadata',
        'Breakdance\Media\Metadata\endpoint',
        'edit',
        true,
        ['args' => ['ids' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]]
    );
});

/**
 * @param array<int, array{value: string, descriptor: string, url: string}> $sources
 * @param array $size_array
 * @param string $image_src
 * @param array{width: int} $image_meta
 * @param int    $attachment_id
 * @return array
 */
function excludeFullSizeFromSrcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    foreach ( $sources as $source_url => $source ) {
        // If the source width matches the full image width, remove it
        if ( isset( $image_meta['width'] ) && $source['value'] == $image_meta['width'] ) {
            unset( $sources[$source_url] );
        }
    }

    return $sources;
}

/**
 * @param int $id
 * @param string $size
 * @return string[]
 */
function getMediaSrcset($id, $size = 'full') {
    if ($size !== 'full') {
        add_filter('wp_calculate_image_srcset', 'Breakdance\Media\Metadata\excludeFullSizeFromSrcset', 10, 5);
    }

    $result = [
        'srcset' => (string) wp_get_attachment_image_srcset($id, $size),
        'sizes'  => (string) wp_get_attachment_image_sizes($id, $size),
    ];

    if ($size !== 'full') {
        remove_filter('wp_calculate_image_srcset', 'Breakdance\Media\Metadata\excludeFullSizeFromSrcset');
    }

    return $result;
}

/**
 * Prepare the media object to the format the frontend expects.
 * @param int $id
 * @return array|null
 */
function prepareMedia($id)
{
    $attachment = wp_prepare_attachment_for_js($id);
    if (!$attachment) return null;

    $fieldsToKeep = ['id', 'filename', 'alt', 'caption', 'url', 'type', 'mime', 'sizes'];

    $filteredAttachment = array_filter(
        $attachment,
        /**
         * @param string $key
         * @return bool
         */
        fn($key) => in_array($key, $fieldsToKeep),
        ARRAY_FILTER_USE_KEY
    );

    $attrs = getMediaSrcset($id);

    return array_merge($filteredAttachment, [
        'attributes' => $attrs
    ]);
}

/**
 * @param int[] $ids
 * @return array{data: array}
 */
function endpoint($ids)
{
    $metadata = array_map('Breakdance\Media\Metadata\prepareMedia', $ids);

    return ['data' => $metadata];
}
