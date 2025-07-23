<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\AnalyticswpEvent",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class AnalyticswpEvent extends \Breakdance\Elements\Element
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
        return 'AnalyticsWP Event';
    }

    static function className()
    {
        return 'bde-analyticswp-event';
    }

    static function category()
    {
        return 'advanced';
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
        return [];
    }

    static function contentControls()
    {
        return [c(
        "event",
        "Event",
        [c(
        "trigger",
        "Trigger",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'items' => ['0' => ['value' => 'scrolled_into_view', 'text' => 'Scrolled Into View'], '1' => ['text' => 'Time Active On Page', 'value' => 'time_active_on_page']]],
        false,
        false,
        [],
      ), c(
        "time_active_on_page",
        "Time Active On Page",
        [c(
        "seconds",
        "Seconds",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['0' => ['0' => ['path' => 'content.event.trigger', 'operand' => 'equals', 'value' => 'time_active_on_page']]]],
        false,
        false,
        [],
      ), c(
        "label",
        "Label",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "builder_label",
        "Builder Label",
        [c(
        "disable_builder_label",
        "Disable Builder Label",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'items' => ['0' => ['value' => 'hidden', 'text' => 'hidden', 'icon' => 'EyeSlashSolidIcon'], '1' => ['text' => 'visible', 'value' => 'visible', 'icon' => 'EyeSolidIcon']]],
        false,
        false,
        [],
      ), c(
        "builder_label",
        "Builder Label",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'placeholder' => '', 'condition' => ['0' => ['0' => ['path' => 'content.content.disable_builder_label', 'operand' => 'is not set', 'value' => '']]]],
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
        return ['0' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-analyticswp/breakdance-analyticswp.js'],'inlineScripts' => ['new BreakdanceAnalyticsWP(\'%%SELECTOR%%\', {{ content.event|json_encode }});
'],],];
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
        return false;
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
        return 0;
    }

    static function dynamicPropertyPaths()
    {
        return ['0' => ['path' => 'settings.advanced.attributes[].value', 'accepts' => 'string']];
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
