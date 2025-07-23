<?php

namespace Breakdance\WooCommerce;

/**
 * @param string $template
 * @param string $templateName
 * @param string $templatePath
 */
function debugTemplate($template, $templateName, $templatePath)
{
    echo "<b>Template (woocommerce_locate_template)</b>";

    \Breakdance\Debug\pre_print_r(
        [
            'template' => $template,
            'templateName' => $templateName,
            'templatePath' => $templatePath
        ]
    );

    echo "<br /><br />";
}

add_filter('woocommerce_template_overrides_scan_paths', function($paths) {
    $paths[] = ['breakdance' => BREAKDANCE_WOO_TEMPLATES_DIR];
    return $paths;
});

/*
 * wc_get_template_part handles caching
 * i don't think this code does though. it looks like it just calls load_template on the returned path
 * does that cache? i don't think so.
 * so we are calling load template for the same file 27 times in a lot of cases
 (shop loop, for example)
-*/

add_filter(
    "wc_get_template_part",
    /**
     * @param string $template
     * @param string $slug
     * @param string|null $name
     * @return string
     */
    function($template, $slug, $name) {
        static $template_cache = [];
        $cache_key = $slug . ($name ? "-$name" : '');

        if (isset($template_cache[$cache_key])) {
            return $template_cache[$cache_key];
        }

        $file = $name ? "{$slug}-{$name}.php" : "{$slug}.php";
        $path = BREAKDANCE_WOO_TEMPLATES_DIR . $file;

        if (file_exists($path)) {
            $template_cache[$cache_key] = $path;
        } else {
            $template_cache[$cache_key] = $template;
        }

        return $template_cache[$cache_key];
    },
    10, 3
);

// Get path for all other templates.
add_filter('wc_get_template', function($template, $templateName, $templatePath) {
    static $template_cache = [];
    $path = BREAKDANCE_WOO_TEMPLATES_DIR . $templateName;
    $cache_key = $path;

    if (isset($template_cache[$cache_key])) {
        return $template_cache[$cache_key];
    }

    if (file_exists($path)) {
        $template_cache[$cache_key] = $path;
    } else {
        $template_cache[$cache_key] = $template;
    }

    return $template_cache[$cache_key];
}, 10, 3);
