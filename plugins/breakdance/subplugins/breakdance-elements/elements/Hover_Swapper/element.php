<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\HoverSwapper",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class HoverSwapper extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ArrowRightIcon';
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
        return 'Hover Swapper';
    }

    static function className()
    {
        return 'bde-hover-swapper';
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
        return [['slug' => 'EssentialElements\Image2', 'defaultProperties' => ['content' => ['image' => ['from' => 'url', 'lazy_load' => true, 'alt' => 'from_media_library', 'media' => null, 'media_dynamic_meta' => null, 'url' => 'https://breakdance.com/wp-content/uploads/2024/10/nic-y-c-u7hKg-Lgzm0-unsplash-768x512.jpg']], 'design' => ['image' => ['width' => null]]]], ['slug' => 'EssentialElements\Image2', 'defaultProperties' => ['content' => ['image' => ['from' => 'url', 'lazy_load' => true, 'alt' => 'from_media_library', 'media' => null, 'media_dynamic_meta' => null, 'url' => 'https://breakdance.com/wp-content/uploads/2024/10/jeremy-doddridge-efhEncQLi4w-unsplash-768x512.jpg']], 'design' => ['image' => ['width' => null]]]]];
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [c(
        "effect",
        "Effect",
        [c(
        "effect",
        "Effect",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Fade', 'value' => 'fade'], ['text' => 'Slide Left', 'value' => 'slide-left'], ['text' => 'Slide Right', 'value' => 'slide-right'], ['text' => 'Slide Up', 'value' => 'slide-up'], ['text' => 'Slide Down', 'value' => 'slide-down']]],
        false,
        false,
        [],
      ), c(
        "duration",
        "Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms']], 'rangeOptions' => ['min' => 0, 'max' => 3000, 'step' => 100]],
        false,
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
     )];
    }

    static function contentControls()
    {
        return [c(
        "new_section",
        "New Section",
        [c(
        "new_control",
        "New Control",
        [],
        ['type' => 'alert_box', 'layout' => 'vertical', 'alertBoxOptions' => ['style' => 'default', 'content' => '<p>Place two elements of the same size inside the Hover Swapper.</p>']],
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
        return ["type" => "container",   ];
    }

    static function spacingBars()
    {
        return false;
    }

    static function attributes()
    {
        return [['name' => 'data-hover-swapper-effect', 'template' => '{{ design.effect.effect|default(\'fade\') }}']];
    }

    static function experimental()
    {
        return false;
    }

    static function order()
    {
        return 650;
    }

    static function dynamicPropertyPaths()
    {
        return [];
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
