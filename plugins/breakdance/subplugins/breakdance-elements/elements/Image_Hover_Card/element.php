<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\ImageHoverCard",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ImageHoverCard extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ImagePolaroidIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function tagOptions()
    {
        return [];
    }

    static function tagControlPath()
    {
        return false;
    }

    static function name()
    {
        return 'Image Hover Card';
    }

    static function className()
    {
        return 'bde-image-hover-card';
    }

    static function category()
    {
        return 'blocks';
    }

    static function badge()
    {
        return false;
    }

    static function slug()
    {
        return __CLASS__;
    }

    static function template()
    {
        return file_get_contents(__DIR__ . '/html.twig');
    }

    static function defaultCss()
    {
        return file_get_contents(__DIR__ . '/default.css');
    }

    static function defaultProperties()
    {
        return ['content' => ['content' => ['caption_tag' => 'h3', 'caption_position' => 'c4-layout-center-right', 'border_effect' => 'c4-border-ccc-1', 'image_effect' => 'c4-image-zoom-out', 'image' => '', 'text_effect' => 'c4-reveal-right', 'single_caption' => 'My caption', 'url' => null], 'captions' => ['caption' => 'null', 'captions' => [['text' => 'use breakdance', 'effect' => 'c4-reveal-right', 'delay' => 'c4-delay-200'], ['effect' => 'c4-reveal-right', 'delay' => 'c4-delay-400', 'text' => 'be happy']], 'position' => 'c4-layout-center-right'], 'multiple_captions' => ['captions' => [['text' => 'Easily add beautiful', 'delay' => 'c4-delay-200', 'effect' => null], ['text' => 'image hover effects', 'delay' => 'c4-delay-400', 'effect' => null]]], 'link' => null, 'image' => ['image' => null, 'lazy_load' => true, 'image_dynamic_meta' => null]], 'design' => ['settings' => ['primary_color' => '#D82F2FFF', 'secondary_color' => '#080808FF', 'gradient_overlay' => 'c4-gradient-right', 'transition_duration' => 0.3, 'overlay_opacity' => 0.4, 'border_color' => '#FFFFFFFF', 'border_width' => ['number' => 4, 'unit' => 'px', 'style' => '4px'], 'width' => ['breakpoint_base' => ['number' => 100, 'unit' => '%', 'style' => '100%']], 'max_width' => ['breakpoint_base' => ['number' => -1, 'unit' => 'px', 'style' => '-1px']], 'caption_typography' => ['line_height' => ['breakpoint_base' => ['number' => 1.3, 'unit' => 'em', 'style' => '1.3em']], 'font_size' => ['breakpoint_base' => ['number' => 20, 'unit' => 'px', 'style' => '20px']], 'color' => ['breakpoint_base' => '#FFFFFFFF'], 'font_weight' => ['breakpoint_base' => '500']]], 'size' => ['width' => ['breakpoint_base' => ['number' => 500, 'unit' => 'px', 'style' => '500px']], 'max_width' => ['breakpoint_base' => ['number' => 500, 'unit' => 'px', 'style' => '500px']]], 'overlay' => ['primary_color' => '#DE2626FF', 'secondary_color' => '#121111FF', 'gradient_overlay' => 'c4-gradient-top-right', 'overlay_opacity' => 0.4, 'color_1' => '#060110FF', 'color_2' => '#5D0E9EFF', 'direction' => 'c4-gradient-right', 'opacity' => 0.3, 'overlay_color' => ['points' => [['left' => 1.5384615384615385, 'red' => 15, 'green' => 165, 'blue' => 172, 'alpha' => 1], ['left' => 98.46153846153847, 'red' => 95, 'green' => 72, 'blue' => 162, 'alpha' => 1]], 'type' => 'linear', 'degree' => 0, 'svgValue' => '<linearGradient x1="0.5" y1="1" x2="0.5" y2="0" id="%%GRADIENTID%%"><stop stop-opacity="1" stop-color="#0fa5ac" offset="0.015384615384615385"></stop><stop stop-opacity="1" stop-color="#5f48a2" offset="0.9846153846153847"></stop></linearGradient>', 'value' => 'linear-gradient(0deg,rgba(15, 165, 172, 1) 1.5384615384615385%,rgba(95, 72, 162, 1) 98.46153846153847%)']], 'spacing_margin_y' => null, 'effects' => ['border_width' => ['number' => 2, 'unit' => 'px', 'style' => '2px'], 'border_color' => '#F1F1F1FF', 'transition_duration' => ['number' => 600, 'unit' => 'ms', 'style' => '600ms'], 'border_effect' => 'c4-border-bottom', 'image_effect' => 'c4-image-pan-down'], 'typography' => ['custom' => ['breakpoint_base' => ['fontSize' => ['number' => 24, 'unit' => 'px', 'style' => '24px'], 'fontWeight' => '700', 'textTransform' => 'none', 'fontStyle' => 'normal', 'letterSpacing' => ['unit' => 'custom', 'number' => 'normal', 'style' => 'normal'], 'textDirection' => 'ltr', 'lineHeight' => ['unit' => 'custom', 'number' => 'normal', 'style' => 'normal'], 'textDecoration' => 'none']], 'color' => ['breakpoint_base' => '#FDFDFDFF']]]];
    }

    static function defaultChildren()
    {
        return false;
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [c(
        "size",
        "Size",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "overlay",
        "Overlay",
        [c(
        "overlay_color",
        "Overlay Color",
        [],
        ['type' => 'color', 'layout' => 'inline', 'colorOptions' => ['type' => 'solidAndGradient']],
        false,
        false,
        [],
      ), c(
        "opacity",
        "Opacity",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['step' => 0.1, 'min' => 0, 'max' => 1]],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "effects",
        "Effects",
        [c(
        "image_effect",
        "Image Effect",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Zoom In', 'label' => 'Label', 'value' => 'c4-image-zoom-in'], ['text' => 'Zoom Out', 'value' => 'c4-image-zoom-out'], ['text' => 'Pan Up', 'value' => 'c4-image-pan-up'], ['text' => 'Pan Down', 'value' => 'c4-image-pan-down'], ['text' => 'Pan Left', 'value' => 'c4-image-pan-left'], ['text' => 'Pan Right', 'value' => 'c4-image-pan-right'], ['text' => 'Rotate Left', 'value' => 'c4-image-rotate-left'], ['text' => 'Rotate Right', 'value' => 'c4-image-rotate-right'], ['text' => 'Blur', 'value' => 'c4-image-blur']]],
        false,
        false,
        [],
      ), c(
        "border_effect",
        "Border Effect",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Center', 'label' => 'Label', 'value' => 'c4-border-center'], ['value' => 'c4-border-vert', 'text' => 'Vertical'], ['text' => 'Horizontal', 'value' => 'c4-border-horiz'], ['text' => 'Top', 'value' => 'c4-border-top'], ['text' => 'Bottom', 'value' => 'c4-border-bottom'], ['text' => 'Left', 'value' => 'c4-border-left'], ['text' => 'Right', 'value' => 'c4-border-right'], ['text' => 'Top Left', 'value' => 'c4-border-top-left'], ['text' => 'Top Right', 'value' => 'c4-border-top-right'], ['text' => 'Bottom Left', 'value' => 'c4-border-bottom-left'], ['text' => 'Bottom Right', 'value' => 'c4-border-bottom-right'], ['text' => 'Corners', 'value' => 'c4-border-corners-1'], ['text' => 'Corners 2', 'value' => 'c4-border-corners-2'], ['text' => 'CC 1', 'value' => 'c4-border-cc-1'], ['text' => 'CC 2', 'value' => 'c4-border-cc-2'], ['text' => 'CC 3', 'value' => 'c4-border-cc-3'], ['text' => 'CCC 1', 'value' => 'c4-border-ccc-1'], ['text' => 'CCC 2', 'value' => 'c4-border-ccc-2'], ['text' => 'CCC 3', 'value' => 'c4-border-ccc-3']]],
        false,
        false,
        [],
      ), c(
        "border_width",
        "Border Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "border_color",
        "Border Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['step' => 10, 'min' => 0, 'max' => 1000], 'unitOptions' => ['types' => ['ms'], 'defaultType' => 'ms']],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_effects",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Spacing",
      "spacing",
       ['type' => 'popout']
     )];
    }

    static function contentControls()
    {
        return [c(
        "image",
        "Image",
        [c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'media_size_dropdown', 'layout' => 'vertical', 'mediaSizeOptions' => ['imagePropertyPath' => 'content.image.image'], 'condition' => ['path' => 'content.image.image', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      ), c(
        "alt",
        "Alt",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'condition' => ['path' => 'content.image.image', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      ), c(
        "lazy_load",
        "Lazy Load",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => ['path' => 'content.image.image', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "captions",
        "Captions",
        [c(
        "captions",
        "Captions",
        [c(
        "text",
        "Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "effect",
        "Effect",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'reveal-up', 'label' => 'Label', 'value' => 'c4-reveal-up'], ['text' => 'reveal-down', 'value' => 'c4-reveal-down'], ['text' => 'reveal-left', 'value' => 'c4-reveal-left'], ['text' => 'reveal-right', 'value' => 'c4-reveal-right'], ['text' => 'fade-up', 'value' => 'c4-fade-up'], ['text' => 'fade-down', 'value' => 'c4-fade-down'], ['text' => 'fade-left', 'value' => 'c4-fade-left'], ['text' => 'fade-right', 'value' => 'c4-fade-right'], ['text' => 'rotate-up-right', 'value' => 'c4-rotate-up-right'], ['text' => 'rotate-up-left', 'value' => 'c4-rotate-up-left'], ['text' => 'rotate-down-right', 'value' => 'c4-rotate-down-right'], ['text' => 'rotate-down-left', 'value' => 'c4-rotate-down-left']]],
        false,
        false,
        [],
      ), c(
        "delay",
        "Delay",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => '100ms', 'label' => 'Label', 'value' => 'c4-delay-100'], ['text' => '200ms', 'value' => 'c4-delay-200'], ['text' => '300ms', 'value' => 'c4-delay-300'], ['text' => '400ms', 'value' => 'c4-delay-400'], ['text' => '500ms', 'value' => 'c4-delay-500'], ['text' => '600ms', 'value' => 'c4-delay-600'], ['text' => '700ms', 'value' => 'c4-delay-700'], ['text' => '800ms', 'value' => 'c4-delay-800'], ['text' => '900ms', 'value' => 'c4-delay-900'], ['text' => '1s', 'value' => 'c4-delay-1000']]],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{text}', 'defaultTitle' => 'Caption', 'buttonName' => 'Add Caption']],
        false,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Top Left', 'label' => 'Label', 'value' => 'c4-layout-top-left'], ['text' => 'Top Center', 'value' => 'c4-layout-top-center'], ['text' => 'Top Right', 'value' => 'c4-layout-top-right'], ['text' => 'Center Left', 'value' => 'c4-layout-center-left'], ['text' => 'Center Center', 'label' => 'Label', 'value' => 'c4-layout-center-center'], ['text' => 'Center Right', 'value' => 'c4-layout-center-right'], ['text' => 'Bottom Left', 'value' => 'c4-layout-bottom-left'], ['text' => 'Bottom Center', 'value' => 'c4-layout-bottom-center'], ['text' => 'Bottom Right', 'value' => 'c4-layout-bottom-right']]],
        false,
        false,
        [],
      ), c(
        "tag",
        "Tag",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'h1', 'label' => 'Label', 'value' => 'h1'], ['text' => 'h2', 'value' => 'h2'], ['text' => 'h3', 'value' => 'h3'], ['text' => 'h4', 'value' => 'h4'], ['text' => 'h5', 'value' => 'h5'], ['text' => 'h6', 'value' => 'h6'], ['text' => 'Div', 'value' => 'div']]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "link",
        "Link",
        [c(
        "link",
        "Link",
        [],
        ['type' => 'link', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
      )];
    }

    static function settingsControls()
    {
        return [];
    }

    static function dependencies()
    {
        return ['0' =>  ['styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/izmir@1/izmir.min.css'],],];
    }

    static function settings()
    {
        return ['proOnly' => true];
    }

    static function addPanelRules()
    {
        return false;
    }

    static public function actions()
    {
        return false;
    }

    static function nestingRule()
    {
        return ["type" => "final",   ];
    }

    static function spacingBars()
    {
        return [['location' => 'outside-top', 'cssProperty' => 'margin-top', 'affectedPropertyPath' => 'design.spacing.margin_top.%%BREAKPOINT%%'], ['location' => 'outside-bottom', 'cssProperty' => 'margin-bottom', 'affectedPropertyPath' => 'design.spacing.margin_bottom.%%BREAKPOINT%%']];
    }

    static function attributes()
    {
        return false;
    }

    static function experimental()
    {
        return false;
    }

    static function order()
    {
        return 3800;
    }

    static function dynamicPropertyPaths()
    {
        return [['accepts' => 'image_url', 'path' => 'content.image.image'], ['accepts' => 'string', 'path' => 'content.captions.captions[].text'], ['accepts' => 'url', 'path' => 'content.link.link.url']];
    }

    static function additionalClasses()
    {
        return false;
    }

    static function projectManagement()
    {
        return ['looksGood' => 'yes', 'optionsGood' => 'yes', 'optionsWork' => 'no', 'dynamicBehaviorWorks' => 'yes', 'notes' => 'https://github.com/soflyy/breakdance-elements/issues/583'];
    }

    static function propertyPathsToWhitelistInFlatProps()
    {
        return false;
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return false;
    }
}
