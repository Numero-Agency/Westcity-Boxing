<?php

namespace Breakdance\WooCommerce;

function showSecondImageOnHover()
{
    $imageSize = apply_filters('single_product_archive_thumbnail_size', 'woocommerce_thumbnail');
    $gallery = wc_get_product()->get_gallery_image_ids();
    $imageId = count($gallery) >= 1 ? $gallery[0] : null;

    if ($imageId) {
        echo '<div class="bde-woo-product-image__hover-img">';
        echo wp_get_attachment_image($imageId, $imageSize);
        echo '</div>';
    }
}