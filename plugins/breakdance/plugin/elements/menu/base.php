<?php

namespace Breakdance\Elements\Menu;

use Breakdance\Render\Twig;

/**
 * @param array $properties
 * @param string $theme
 * @return string
 */
function renderMenuSection($properties, $theme = '')
{
    $template = '{{ macros.menuSection(props, theme) }}';

    return Twig::getInstance()->runTwig($template, [
        'props' => $properties,
        'theme' => $theme,
    ]);
}
