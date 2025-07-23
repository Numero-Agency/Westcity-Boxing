<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\Fancybackgroundpreset",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class Fancybackgroundpreset extends \Breakdance\Elements\Element
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
        return 'FancyBackgroundPreset';
    }

    static function className()
    {
        return 'bde-fancybackgroundpreset';
    }

    static function category()
    {
        return 'other';
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
        "background",
        "Background",
        [c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "type",
        "Type",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'image', 'text' => 'Image'], ['text' => 'Gradient', 'value' => 'gradient'], ['text' => 'Video', 'value' => 'video'], ['text' => 'Slideshow', 'value' => 'slideshow']]],
        false,
        false,
        [],
      ), c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'image']],
        true,
        true,
        [],
      ), c(
        "image_size",
        "Image Size",
        [],
        ['type' => 'media_size_dropdown', 'layout' => 'inline', 'condition' => [[['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'image']]], 'mediaSizeOptions' => ['imagePropertyPath' => 'design.background.image']],
        true,
        true,
        [],
      ), c(
        "lazy_load",
        "Lazy Load",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => [[['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'image']]]],
        false,
        false,
        [],
      ), c(
        "image_settings",
        "Image Settings",
        [c(
        "size",
        "Size",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'cover', 'text' => 'cover'], ['text' => 'contain', 'value' => 'contain'], ['text' => 'auto', 'value' => 'auto'], ['text' => 'custom', 'value' => 'custom']]],
        true,
        false,
        [],
      ), c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.background.image_settings.size', 'operand' => 'equals', 'value' => 'custom']],
        true,
        true,
        [],
      ), c(
        "height",
        "Height",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.background.image_settings.size', 'operand' => 'equals', 'value' => 'custom']],
        true,
        true,
        [],
      ), c(
        "repeat",
        "Repeat",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'repeat', 'text' => 'repeat'], ['text' => 'norepeat', 'value' => 'no-repeat'], ['text' => 'repeat-x', 'value' => 'repeat-x'], ['text' => 'repeat-y', 'value' => 'repeat-y']]],
        true,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'center top', 'text' => 'center top'], ['value' => 'center center', 'text' => 'center center'], ['value' => 'center bottom', 'text' => 'center bottom'], ['value' => 'left top', 'text' => 'left top'], ['value' => 'left center', 'text' => 'left center'], ['value' => 'left bottom', 'text' => 'left bottom'], ['value' => 'right top', 'text' => 'right top'], ['value' => 'right center', 'text' => 'right center'], ['value' => 'right bottom', 'text' => 'right bottom'], ['text' => 'custom', 'value' => 'custom']]],
        true,
        false,
        [],
      ), c(
        "custom_position",
        "Custom Position",
        [],
        ['type' => 'focus_point', 'layout' => 'vertical', 'condition' => ['path' => 'design.background.image_settings.position', 'operand' => 'equals', 'value' => 'custom']],
        true,
        false,
        [],
      ), c(
        "attachment",
        "Attachment",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'scroll', 'text' => 'scroll'], ['text' => 'fixed', 'value' => 'fixed']]],
        true,
        false,
        [],
      ), c(
        "unset_image_at",
        "Unset Image At",
        [],
        ['type' => 'breakpoint_dropdown', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
      ), c(
        "gradient",
        "Gradient",
        [],
        ['type' => 'color', 'layout' => 'vertical', 'colorOptions' => ['type' => 'gradientOnly'], 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'gradient']],
        false,
        false,
        [],
      ), c(
        "gradient_animation",
        "Gradient Animation",
        [c(
        "scale",
        "Scale",
        [],
        ['type' => 'unit', 'layout' => 'vertical', 'unitOptions' => ['types' => ['%']], 'rangeOptions' => ['min' => 100, 'max' => 500, 'step' => 10]],
        false,
        false,
        [],
      ), c(
        "speed",
        "Speed",
        [],
        ['type' => 'unit', 'layout' => 'vertical', 'unitOptions' => ['types' => ['s']], 'rangeOptions' => ['min' => 1, 'max' => 20, 'step' => 1]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'gradient']],
        false,
        false,
        [],
      ), c(
        "video",
        "Video",
        [],
        ['type' => 'video', 'layout' => 'vertical', 'condition' => [[['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'video']]], 'videoOptions' => ['providers' => ['youtube', 'vimeo']]],
        false,
        false,
        [],
      ), c(
        "video_warning",
        "Video Warning",
        [],
        ['type' => 'alert_box', 'layout' => 'vertical', 'condition' => [[['path' => '%%CURRENTPATH%%.video.url', 'operand' => 'contains', 'value' => 'youtube']], [['path' => '%%CURRENTPATH%%.video.url', 'operand' => 'contains', 'value' => 'vimeo']]], 'alertBoxOptions' => ['style' => 'warning', 'content' => '<p>Using YouTube or Vimeo for video backgrounds is no longer recommended. The video appearance is likely to change in the future. <a target="_blank" href="https://breakdance.com/documentation/other/youtube-vimeo-video-backgrounds/">Learn More</a>.</p>']],
        false,
        false,
        [],
      ), c(
        "video_settings",
        "Video Settings",
        [c(
        "fallback_image",
        "Fallback Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "play_on_mobile",
        "Play On Mobile",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "no_loop",
        "No Loop",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "pause_when_out_of_view",
        "Pause When Out Of View",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "youtube_privacy_mode",
        "YouTube Privacy Mode",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => ['path' => 'design.background.video.url', 'operand' => 'contains', 'value' => 'youtube.com']],
        false,
        false,
        [],
      ), c(
        "start_time",
        "Start Time",
        [],
        ['type' => 'number', 'layout' => 'inline', 'unitOptions' => ['types' => ['s'], 'defaultType' => 's']],
        false,
        false,
        [],
      ), c(
        "end_time",
        "End Time",
        [],
        ['type' => 'number', 'layout' => 'inline', 'unitOptions' => ['types' => ['s'], 'defaultType' => 's']],
        false,
        false,
        [],
      ), c(
        "zoom",
        "Zoom",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['%'], 'defaultType' => '%'], 'rangeOptions' => ['min' => 100, 'max' => 300]],
        true,
        false,
        [],
      ), c(
        "offsety",
        "Offset Y",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['px'], 'defaultType' => 'px'], 'rangeOptions' => ['min' => 0, 'max' => 200]],
        true,
        false,
        [],
      ), c(
        "offsetx",
        "Offset X",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['px'], 'defaultType' => 'px'], 'rangeOptions' => ['min' => 0, 'max' => 200]],
        true,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'video']],
        false,
        false,
        [],
      ), c(
        "slideshow",
        "Slideshow",
        [c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'slideshow'], 'repeaterOptions' => ['titleTemplate' => '', 'defaultTitle' => '', 'buttonName' => '', 'galleryMode' => true, 'galleryMediaPath' => 'image']],
        false,
        false,
        [],
      ), c(
        "slideshow_settings",
        "Slideshow Settings",
        [c(
        "slide_duration",
        "Slide Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms']], 'rangeOptions' => ['min' => 0, 'max' => 10000, 'step' => 100]],
        false,
        false,
        [],
      ), c(
        "transition_effect",
        "Transition Effect",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Slide', 'value' => 'slide'], ['value' => 'fade', 'text' => 'Fade']]],
        false,
        false,
        [],
      ), c(
        "effect_duration",
        "Effect Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms']], 'rangeOptions' => ['min' => 0, 'max' => 10000, 'step' => 100]],
        false,
        false,
        [],
      ), c(
        "slide_direction",
        "Slide Direction",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'horizontal', 'text' => 'Horizontal'], ['text' => 'Vertical', 'value' => 'vertical']], 'condition' => ['path' => 'design.background.slideshow_settings.transition_effect', 'operand' => 'equals', 'value' => 'slide']],
        false,
        false,
        [],
      ), c(
        "ken_burns",
        "Ken Burns",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'in', 'text' => 'In'], ['text' => 'Out', 'value' => 'out']], 'condition' => ['path' => 'design.background.slideshow_settings.transition_effect', 'operand' => 'equals', 'value' => 'fade']],
        false,
        false,
        [],
      ), c(
        "zoom",
        "Zoom",
        [],
        ['type' => 'number', 'layout' => 'inline', 'condition' => ['path' => 'design.background.slideshow_settings.ken_burns', 'operand' => 'is set', 'value' => null], 'rangeOptions' => ['min' => 1, 'max' => 2, 'step' => 0.1]],
        false,
        false,
        [],
      ), c(
        "origin_",
        "Origin",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.background.slideshow_settings.ken_burns', 'operand' => 'is set', 'value' => null], 'unitOptions' => ['types' => ['%']], 'rangeOptions' => ['min' => 0, 'max' => 100, 'step' => 1]],
        false,
        false,
        [],
      ), c(
        "play_only_once",
        "Play Only Once",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.background.type', 'operand' => 'equals', 'value' => 'slideshow']],
        false,
        false,
        [],
      ), c(
        "overlay",
        "Overlay",
        [c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "type",
        "Type",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'image', 'text' => 'Image'], ['text' => 'Gradient', 'value' => 'gradient']]],
        false,
        false,
        [],
      ), c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'condition' => ['path' => 'design.background.overlay.type', 'operand' => 'equals', 'value' => 'image']],
        true,
        true,
        [],
      ), c(
        "image_size",
        "Image Size",
        [],
        ['type' => 'media_size_dropdown', 'layout' => 'inline', 'condition' => [[['path' => 'design.background.overlay.type', 'operand' => 'equals', 'value' => 'image']]]],
        true,
        true,
        [],
      ), c(
        "lazy_load",
        "Lazy Load",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => [[['path' => 'design.background.overlay.type', 'operand' => 'equals', 'value' => 'image']]]],
        false,
        false,
        [],
      ), c(
        "image_settings",
        "Image Settings",
        [c(
        "size",
        "Size",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'cover', 'text' => 'cover'], ['text' => 'contain', 'value' => 'contain'], ['text' => 'auto', 'value' => 'auto'], ['text' => 'custom', 'value' => 'custom']]],
        true,
        false,
        [],
      ), c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.background.overlay.image_settings.size', 'operand' => 'equals', 'value' => 'custom']],
        true,
        true,
        [],
      ), c(
        "height",
        "Height",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.background.overlay.image_settings.size', 'operand' => 'equals', 'value' => 'custom']],
        true,
        true,
        [],
      ), c(
        "repeat",
        "Repeat",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'repeat', 'text' => 'repeat'], ['text' => 'norepeat', 'value' => 'no-repeat'], ['text' => 'repeat-x', 'value' => 'repeat-x'], ['text' => 'repeat-y', 'value' => 'repeat-y']]],
        true,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'center top', 'text' => 'center top'], ['value' => 'center center', 'text' => 'center center'], ['value' => 'center bottom', 'text' => 'center bottom'], ['value' => 'left top', 'text' => 'left top'], ['value' => 'left center', 'text' => 'left center'], ['value' => 'left bottom', 'text' => 'left bottom'], ['value' => 'right top', 'text' => 'right top'], ['value' => 'right center', 'text' => 'right center'], ['value' => 'right bottom', 'text' => 'right bottom'], ['text' => 'custom', 'value' => 'custom']]],
        true,
        false,
        [],
      ), c(
        "custom_position",
        "Custom Position",
        [],
        ['type' => 'focus_point', 'layout' => 'vertical', 'condition' => ['path' => 'design.background.overlay.image_settings.position', 'operand' => 'equals', 'value' => 'custom']],
        true,
        false,
        [],
      ), c(
        "attachment",
        "Attachment",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'scroll', 'text' => 'scroll'], ['text' => 'fixed', 'value' => 'fixed']]],
        true,
        false,
        [],
      ), c(
        "unset_image_at",
        "Unset Image At",
        [],
        ['type' => 'breakpoint_dropdown', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.background.overlay.type', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
      ), c(
        "gradient",
        "Gradient",
        [],
        ['type' => 'color', 'layout' => 'vertical', 'colorOptions' => ['type' => 'gradientOnly'], 'condition' => ['path' => 'design.background.overlay.type', 'operand' => 'equals', 'value' => 'gradient']],
        false,
        false,
        [],
      ), c(
        "gradient_animation",
        "Gradient Animation",
        [c(
        "scale",
        "Scale",
        [],
        ['type' => 'unit', 'layout' => 'vertical', 'unitOptions' => ['types' => ['%']], 'rangeOptions' => ['min' => 100, 'max' => 500, 'step' => 10]],
        false,
        false,
        [],
      ), c(
        "speed",
        "Speed",
        [],
        ['type' => 'unit', 'layout' => 'vertical', 'unitOptions' => ['types' => ['s']], 'rangeOptions' => ['min' => 1, 'max' => 20, 'step' => 1]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.background.overlay.type', 'operand' => 'equals', 'value' => 'gradient']],
        false,
        false,
        [],
      ), c(
        "opacity",
        "Opacity",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 0, 'max' => 1, 'step' => 0.1]],
        false,
        true,
        [],
      ), c(
        "effects",
        "Effects",
        [getPresetSection(
      "EssentialElements\\filter",
      "Filter",
      "filter",
       ['type' => 'popout']
     ), c(
        "blend_mode",
        "Blend Mode",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'items' => [['value' => 'normal', 'text' => 'normal'], ['value' => 'multiply', 'text' => 'multiply'], ['value' => 'screen', 'text' => 'screen'], ['value' => 'overlay', 'text' => 'overlay'], ['value' => 'darken', 'text' => 'darken'], ['value' => 'lighten', 'text' => 'lighten'], ['text' => 'color-dodge', 'value' => 'color-dodge'], ['text' => 'color-burn', 'value' => 'color-burn'], ['text' => 'hard-light', 'value' => 'hard-light'], ['text' => 'soft-light', 'value' => 'soft-light'], ['text' => 'difference', 'value' => 'difference'], ['text' => 'exclusion', 'value' => 'exclusion'], ['text' => 'hue', 'value' => 'hue'], ['text' => 'saturation', 'value' => 'saturation'], ['text' => 'color', 'value' => 'color'], ['text' => 'luminosity', 'value' => 'luminosity']]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms'], 'defaultType' => 'ms'], 'rangeOptions' => ['min' => 0, 'max' => 9900, 'step' => 100]],
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
        return [];
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
        return ['alwaysHide' => true];
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
        return ['design.background.image_settings.unset_image_at', 'design.background.overlay.image_settings.unset_image_at', 'design.background.video_settings.zoom', 'design.background.video_settings.offsetx', 'design.background.video_settings.offsety'];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return false;
    }
}
