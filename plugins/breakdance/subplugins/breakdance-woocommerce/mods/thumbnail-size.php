<?php

namespace Breakdance\WooCommerce;

add_filter('woocommerce_get_image_size_gallery_thumbnail', '\Breakdance\WooCommerce\override_woocommerce_image_size_gallery_thumbnail');
function override_woocommerce_image_size_gallery_thumbnail($size) {
    return [
        'width' => 300,
        'height' => 300,
        'crop' => 1,
    ];
}