<?php

namespace Breakdance\LoopBuilder\Slider;

use function Breakdance\LoopBuilder\closeSwiperContainer;
use function Breakdance\LoopBuilder\getLoopLayout;
use function Breakdance\LoopBuilder\renderSwiperContainer;

function containerClasses($classes, $data)
{
    $layout = getLoopLayout($data['propertiesData']);

    if ($layout === 'slider') {
        $classes .= ' swiper-wrapper';
    }

    return $classes;
}

function beforeLoop($loop, $data)
{
    $layout = getLoopLayout($data['propertiesData']);

    if ($layout === 'slider') {
        renderSwiperContainer();
    }
}

function afterLoop($loop, $data)
{
    $layout = getLoopLayout($data['propertiesData']);

    if ($layout === 'slider') {
        closeSwiperContainer($data['propertiesData']['design']['list']['slider'] ?? []);
    }
}

function itemClasses($classes, $object, $objectType, $data)
{
    $layout = getLoopLayout($data['propertiesData']);

    if ($layout === 'slider') {
        $classes .= ' swiper-slide';
    }

    return $classes;
}

add_action('breakdance_loop_classes', '\Breakdance\LoopBuilder\Slider\containerClasses', 10, 4);
add_action('breakdance_before_loop', '\Breakdance\LoopBuilder\Slider\beforeLoop', 10, 2);
add_action('breakdance_after_loop', '\Breakdance\LoopBuilder\Slider\afterLoop', 10, 2);
add_action('breakdance_loop_item_classes', '\Breakdance\LoopBuilder\Slider\itemClasses', 10, 4);
