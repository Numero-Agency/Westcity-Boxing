<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\Image",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class Image extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ImageIcon';
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
        return 'Image V1';
    }

    static function className()
    {
        return 'bde-image';
    }

    static function category()
    {
        return 'basic';
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
        "image",
        "Image",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit'],
        true,
        false,
        [],
      ), c(
        "max_width",
        "Max Width",
        [],
        ['type' => 'unit'],
        true,
        false,
        [],
      ), c(
        "height",
        "Height",
        [],
        ['type' => 'unit'],
        true,
        false,
        [],
      ), c(
        "aspect_ratio",
        "Aspect Ratio",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => '1/1', 'text' => '1:1'], ['value' => '4/3', 'text' => '4:3'], ['value' => '3/2', 'text' => '3:2'], ['text' => '16:9', 'value' => '16/9'], ['text' => '8:5', 'value' => '8/5'], ['text' => 'Custom', 'value' => 'custom']]],
        true,
        false,
        [],
      ), c(
        "custom_ratio",
        "Custom Ratio",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "height",
        "Height",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'condition' => [[['path' => 'design.image.aspect_ratio', 'operand' => 'equals', 'value' => 'custom']]], 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "object_fit",
        "Object Fit",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Cover', 'value' => 'cover'], ['value' => 'contain', 'text' => 'Contain'], ['text' => 'Fill', 'value' => 'fill'], ['text' => 'None', 'value' => 'none']], 'condition' => [[['path' => 'design.image.height', 'operand' => 'is set', 'value' => '']], [['path' => 'design.image.aspect_ratio', 'operand' => 'is set', 'value' => 'custom']]]],
        true,
        false,
        [],
      ), c(
        "object_position",
        "Object Position",
        [],
        ['type' => 'focus_point', 'layout' => 'vertical', 'condition' => [[['path' => 'design.image.object_fit', 'operand' => 'is set', 'value' => '']]], 'focusPointOptions' => ['gridMode' => true]],
        true,
        false,
        [],
      ), c(
        "zoom",
        "Zoom",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['defaultType' => '%', 'types' => ['%']], 'rangeOptions' => ['min' => 0, 'max' => 100, 'step' => 1]],
        false,
        true,
        [],
      ), c(
        "focus_point",
        "Focus Point",
        [],
        ['type' => 'focus_point', 'layout' => 'vertical', 'focusPointOptions' => ['imagePropertyPath' => 'content.content.image'], 'condition' => ['path' => 'design.image.zoom', 'operand' => 'is set', 'value' => '']],
        false,
        true,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'accordion']],
        false,
        false,
        [],
      ), c(
        "effects",
        "Effects",
        [c(
        "opacity",
        "Opacity",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
        false,
        true,
        [],
      ), getPresetSection(
      "EssentialElements\\filter",
      "Filters",
      "filters",
       ['type' => 'popout']
     ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms'], 'defaultType' => 'ms'], 'rangeOptions' => ['min' => 0, 'max' => 6000, 'step' => 50]],
        false,
        false,
        [],
      ), c(
        "mask",
        "Mask",
        [c(
        "shape",
        "Shape",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Donut', 'label' => 'Label', 'value' => 'donut'], ['text' => 'Tv', 'value' => 'tv'], ['text' => 'Waves', 'value' => 'waves'], ['text' => 'Waves 2', 'value' => 'waves2'], ['text' => 'Blob', 'value' => 'blob'], ['text' => 'Star 1', 'value' => 'star1'], ['text' => 'Star 2', 'value' => 'star2'], ['text' => 'Star 3', 'value' => 'star3'], ['text' => 'Star 4', 'value' => 'star4'], ['text' => 'Stripes', 'value' => 'stripes'], ['text' => 'Pill', 'value' => 'pill'], ['text' => 'Custom', 'value' => 'custom']]],
        false,
        false,
        [],
      ), c(
        "custom_shape",
        "Custom Shape",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'condition' => ['path' => 'design.effects.mask.shape', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Contain', 'value' => 'contain'], ['value' => 'cover', 'text' => 'Cover'], ['text' => 'Custom', 'value' => 'custom']]],
        false,
        false,
        [],
      ), c(
        "custom_size",
        "Custom Size",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.effects.mask.size', 'operand' => 'equals', 'value' => 'custom'], 'unitOptions' => ['types' => ['px', '%'], 'defaultType' => '%']],
        false,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'focus_point', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "repeat",
        "Repeat",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'no-repeat', 'text' => 'no-repeat'], ['text' => 'repeat', 'value' => 'repeat'], ['text' => 'repeat-x', 'value' => 'repeat-x'], ['text' => 'repeat-y', 'value' => 'repeat-y'], ['text' => 'space', 'value' => 'space'], ['text' => 'round', 'value' => 'round']]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "blend_mode",
        "Blend Mode",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'normal', 'text' => 'normal'], ['value' => 'multiply', 'text' => 'multiply'], ['value' => 'screen', 'text' => 'screen'], ['value' => 'overlay', 'text' => 'overlay'], ['value' => 'darken', 'text' => 'darken'], ['value' => 'lighten', 'text' => 'lighten'], ['text' => 'color-dodge', 'value' => 'color-dodge'], ['text' => 'color-burn', 'value' => 'color-burn'], ['text' => 'hard-light', 'value' => 'hard-light'], ['text' => 'soft-light', 'value' => 'soft-light'], ['text' => 'difference', 'value' => 'difference'], ['text' => 'exclusion', 'value' => 'exclusion'], ['text' => 'hue', 'value' => 'hue'], ['text' => 'saturation', 'value' => 'saturation'], ['text' => 'color', 'value' => 'color'], ['text' => 'luminosity', 'value' => 'luminosity']]],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Spacing",
      "spacing",
       ['type' => 'popout']
     ), c(
        "caption",
        "Caption",
        [c(
        "position",
        "Position",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['text' => 'Overlap', 'value' => 'overlap'], ['text' => 'Below', 'value' => 'below']]],
        false,
        false,
        [],
      ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_align",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Spacing",
      "spacing",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'accordion']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\lightbox_single_design",
      "Lightbox",
      "lightbox",
       ['type' => 'popout']
     )];
    }

    static function contentControls()
    {
        return [c(
        "content",
        "Content",
        [c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'mediaOptions' => ['acceptedFileTypes' => ['image'], 'multiple' => false]],
        false,
        false,
        [],
      ), c(
        "size",
        "Image Size",
        [],
        ['type' => 'media_size_dropdown', 'layout' => 'vertical', 'mediaSizeOptions' => ['imagePropertyPath' => 'content.content.image', 'disableSrcset' => false]],
        false,
        false,
        [],
      ), c(
        "alt",
        "Alt",
        [c(
        "alt",
        "Alt",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'disable', 'text' => 'Disable'], ['text' => 'Custom', 'value' => 'custom']]],
        false,
        false,
        [],
      ), c(
        "custom_alt",
        "Custom Alt",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'condition' => ['path' => 'content.content.alt.alt', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "caption",
        "Caption",
        [c(
        "from_library",
        "From Library",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'items' => [['text' => 'Custom', 'value' => 'custom'], ['text' => 'From Library', 'value' => 'attachment']]],
        false,
        false,
        [],
      ), c(
        "caption",
        "Caption",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'condition' => ['path' => 'content.content.caption.from_library', 'operand' => 'is not set', 'value' => ''], 'textOptions' => ['multiline' => true]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "link",
        "Link",
        [c(
        "link_type",
        "Link",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'items' => [['text' => 'URL', 'value' => 'url'], ['text' => 'Full Size Image', 'value' => 'media'], ['text' => 'Lightbox', 'value' => 'lightbox']]],
        false,
        false,
        [],
      ), c(
        "url",
        "URL",
        [],
        ['type' => 'url', 'layout' => 'vertical', 'condition' => ['path' => 'content.content.link.link_type', 'operand' => 'equals', 'value' => 'url']],
        false,
        false,
        [],
      ), c(
        "new_tab",
        "New Tab",
        [],
        ['type' => 'toggle', 'condition' => ['path' => 'content.content.link.link_type', 'operand' => 'is one of', 'value' => ['url', 'media']]],
        false,
        false,
        [],
      ), c(
        "image_size",
        "Image Size",
        [],
        ['type' => 'media_size_dropdown', 'layout' => 'vertical', 'mediaSizeOptions' => ['imagePropertyPath' => 'content.content.image', 'disableSrcset' => true], 'condition' => ['path' => 'content.content.link.link_type', 'operand' => 'equals', 'value' => 'lightbox']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "lazy_load",
        "Lazy Load",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
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
        return ['0' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/lightgallery@2/lightgallery-bundle.min.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/elements-reusable-code/lightbox.js'],'inlineScripts' => ['new BreakdanceLightbox(\'%%SELECTOR%%\', {
  itemSelector: \'.breakdance-image-link\',
  ...{{ design.lightbox|json_encode }}
});'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/lightgallery@2/css/lightgallery-bundle.min.css'],'builderCondition' => 'return false;','frontendCondition' => '{% if content.content.link.link_type == \'lightbox\' %}return true;{% endif %}','title' => 'Lightbox',],];
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
        return [['location' => 'outside-bottom', 'cssProperty' => 'margin-bottom', 'affectedPropertyPath' => 'design.spacing.margin_bottom.%%BREAKPOINT%%'], ['location' => 'outside-top', 'cssProperty' => 'margin-top', 'affectedPropertyPath' => 'design.spacing.margin_top.%%BREAKPOINT%%']];
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
        return 90;
    }

    static function dynamicPropertyPaths()
    {
        return [['path' => 'content.content.image', 'accepts' => 'image_url'], ['accepts' => 'string', 'path' => 'content.content.alt.custom_alt'], ['accepts' => 'string', 'path' => 'content.content.caption.caption'], ['accepts' => 'url', 'path' => 'content.content.link.url']];
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
