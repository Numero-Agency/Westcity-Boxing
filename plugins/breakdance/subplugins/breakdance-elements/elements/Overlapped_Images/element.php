<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\OverlappedImages",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class OverlappedImages extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'SquareIcon';
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
        return 'Overlapped Images';
    }

    static function className()
    {
        return 'bde-overlapped-images';
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
        return false;
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
      ), c(
        "height",
        "Height",
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
      ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Spacing",
      "spacing",
       ['type' => 'popout']
     ), c(
        "misc",
        "Misc",
        [c(
        "allow_overflow",
        "Allow Overflow",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "default_image_width",
        "Default Image Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      )];
    }

    static function contentControls()
    {
        return [c(
        "content",
        "Content",
        [c(
        "images",
        "Images",
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
        "Image Size",
        [],
        ['type' => 'media_size_dropdown', 'layout' => 'vertical', 'mediaSizeOptions' => ['imagePropertyPath' => 'content.content.images[].image', 'disableSrcset' => false]],
        false,
        false,
        [],
      ), c(
        "alt",
        "Alt",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "height",
        "Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'items' => [['value' => 'top-left', 'text' => 'Top Left'], ['text' => 'Top Right', 'value' => 'top-right'], ['text' => 'Bottom Left', 'value' => 'bottom-left'], ['text' => 'Bottom Right', 'value' => 'bottom-right'], ['text' => 'Custom', 'value' => 'custom']]],
        false,
        false,
        [],
      ), c(
        "custom_position",
        "Custom Position",
        [],
        ['type' => 'focus_point', 'layout' => 'vertical', 'condition' => [[['path' => '%%CURRENTPATH%%.position', 'operand' => 'equals', 'value' => 'custom']]]],
        true,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '', 'defaultTitle' => '', 'buttonName' => '', 'galleryMode' => true, 'galleryMediaPath' => 'image']],
        false,
        false,
        [],
      ), c(
        "lazy_load",
        "Lazy Load",
        [],
        ['type' => 'toggle', 'layout' => 'vertical'],
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
        return false;
    }

    static function settings()
    {
        return false;
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
        return [['affectedPropertyPath' => 'design.spacing.margin_top.%%BREAKPOINT%%', 'cssProperty' => 'margin-top', 'location' => 'outside-top'], ['affectedPropertyPath' => 'design.spacing.margin_bottom.%%BREAKPOINT%%', 'cssProperty' => 'margin-bottom', 'location' => 'outside-bottom']];
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
        return 1000;
    }

    static function dynamicPropertyPaths()
    {
        return [['accepts' => 'string', 'path' => 'content.content.images[].alt']];
    }

    static function additionalClasses()
    {
        return false;
    }

    static function projectManagement()
    {
        return false;
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
