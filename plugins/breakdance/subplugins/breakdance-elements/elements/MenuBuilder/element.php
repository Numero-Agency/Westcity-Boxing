<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\MenuBuilder",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class MenuBuilder extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'BarsIcon';
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
        return 'Menu Builder';
    }

    static function className()
    {
        return 'bde-menu';
    }

    static function category()
    {
        return 'site';
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
        return [['slug' => 'EssentialElements\MenuLink', 'defaultProperties' => ['content' => ['content' => ['link' => ['type' => 'url', 'url' => '#'], 'text' => 'Home']]]], ['slug' => 'EssentialElements\MenuDropdown', 'defaultProperties' => ['content' => ['content' => ['text' => 'Products', 'columns' => [['title' => 'Airplanes', 'links' => [['text' => 'Flyer Basic', 'description' => 'Go fast, don\'t crash.', 'url' => '#'], ['text' => 'Flyer Intermediate', 'description' => 'Go even faster and don\'t crash.', 'url' => '#'], ['text' => 'Flyer Matrix Edition', 'description' => 'Haul cargo long distances.'], ['text' => 'ExpressRocket', 'description' => 'Launch cargo into space on the cheap.'], ['text' => 'Boomslang Level 1', 'description' => 'Get certified for night operations.', 'url' => '#']]], ['title' => 'Helicopter', 'links' => [['text' => 'Liftmeister 21', 'description' => 'The ultimate light utility helicopter.'], ['text' => 'Heavymeister', 'description' => 'Lift large amounts of weight.'], ['text' => 'Triblade Vortex', 'description' => 'Multi-engine reliability.'], ['text' => 'Rugged & Heavy Industry', 'description' => 'Designed to take a beating and keep flying.'], ['text' => 'Identity 6000', 'description' => 'The ultimate in business luxury travel.']]]]]], 'design' => ['columns' => ['stack' => null]]]], ['slug' => 'EssentialElements\MenuDropdown', 'defaultProperties' => ['content' => ['content' => ['text' => 'Use Cases', 'columns' => [['links' => [['text' => 'Small Business', 'description' => 'Accept and display widgets.'], ['text' => 'Medium Business', 'description' => 'Widgets for teams larger than 100.'], ['text' => 'Enterprise', 'description' => 'Enterprise-grade reliability and security.'], ['text' => 'Organizations', 'description' => 'Do even more with widgets.']]]]]]]], ['slug' => 'EssentialElements\MenuDropdown', 'defaultProperties' => ['content' => ['content' => ['text' => 'Developers', 'columns' => [['title' => 'Documentation', 'links' => [['text' => 'Getting Started Guide'], ['text' => 'API Reference'], ['text' => 'Sample Code'], ['text' => 'Plugin Developer Handbook']]], ['title' => 'Developer Community', 'links' => [['text' => 'Forums'], ['text' => 'Slack Channel'], ['text' => 'Facebook Group']]]]]]]], ['slug' => 'EssentialElements\MenuCustomDropdown', 'defaultProperties' => ['content' => ['content' => ['text' => 'About']]], 'children' => [['slug' => 'EssentialElements\Heading', 'defaultProperties' => ['content' => ['content' => ['text' => 'Custom Dropdown', 'tags' => 'h5']], 'design' => ['spacing' => ['margin_bottom' => ['breakpoint_base' => ['number' => 12, 'unit' => 'px', 'style' => '12px']]]]]], ['slug' => 'EssentialElements\Text', 'defaultProperties' => ['content' => ['content' => ['text' => 'Design your dropdowns with Breakdance.']], 'design' => ['spacing' => ['margin_bottom' => ['breakpoint_base' => ['number' => 13, 'unit' => 'px', 'style' => '13px']]]]]], ['slug' => 'EssentialElements\Image', 'defaultProperties' => ['content' => ['content' => ['size' => null, 'caption_type' => 'none', 'caption_position' => 'below-image', 'link_type' => 'none', 'loading' => 'lazy', 'image' => ['id' => -1, 'type' => 'external_image', 'url' => 'https://images.unsplash.com/photo-1573164574472-797cdf4a583a?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=600&q=80', 'alt' => '', 'caption' => '']]]]]]]];
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [c(
        "desktop_menu",
        "Desktop Menu",
        [c(
        "links",
        "Links",
        [getPresetSection(
      "EssentialElements\\typography_with_effects_with_hoverable_color_and_effects",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), c(
        "effect",
        "Effect",
        [c(
        "effect_type",
        "Effect Type",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Underline', 'value' => 'underline'], ['text' => 'Overline', 'value' => 'overline'], ['value' => 'strikethrough', 'text' => 'Strikethrough'], ['text' => 'Background', 'value' => 'background']]],
        false,
        false,
        [],
      ), c(
        "effect_direction",
        "Effect Direction",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['text' => 'Center', 'value' => 'center'], ['text' => 'Right', 'value' => 'right'], ['text' => 'Top', 'value' => 'top'], ['text' => 'Bottom', 'value' => 'bottom']], 'buttonBarOptions' => ['layout' => 'multiline', 'size' => 'small'], 'condition' => ['path' => 'design.desktop_menu.links.effect.effect_type', 'operand' => 'is none of', 'value' => ['none']]],
        false,
        false,
        [],
      ), c(
        "thickness",
        "Thickness",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => 'design.desktop_menu.links.effect.effect_type', 'operand' => 'is one of', 'value' => ['overline', 'underline', 'strikethrough']]]]],
        false,
        false,
        [],
      ), c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline', 'condition' => ['path' => 'design.desktop_menu.links.effect.effect_type', 'operand' => 'not equals', 'value' => 'none']],
        false,
        false,
        [],
      ), c(
        "radius",
        "Radius",
        [],
        ['type' => 'border_radius', 'layout' => 'vertical', 'condition' => ['path' => 'design.desktop_menu.links.effect.effect_type', 'operand' => 'equals', 'value' => 'background']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "active",
        "Active",
        [c(
        "effect_type",
        "Effect Type",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Underline', 'value' => 'underline'], ['text' => 'Overline', 'value' => 'overline'], ['value' => 'strikethrough', 'text' => 'Strikethrough'], ['text' => 'Background', 'value' => 'background']]],
        false,
        false,
        [],
      ), c(
        "thickness",
        "Thickness",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => 'design.desktop_menu.links.active.effect_type', 'operand' => 'is one of', 'value' => ['overline', 'underline', 'strikethrough']]]]],
        false,
        false,
        [],
      ), c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline', 'condition' => [[['path' => 'design.desktop_menu.links.active.effect_type', 'operand' => 'is set', 'value' => '']]]],
        false,
        false,
        [],
      ), c(
        "radius",
        "Radius",
        [],
        ['type' => 'border_radius', 'layout' => 'vertical', 'condition' => ['path' => 'design.desktop_menu.links.effect.effect_type', 'operand' => 'equals', 'value' => 'background']],
        false,
        false,
        [],
      ), c(
        "text_color",
        "Text Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Size",
      "size",
       ['type' => 'popout']
     ), c(
        "space_between",
        "Space Between",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "dropdown_arrows",
        "Dropdown Arrows",
        [c(
        "disable",
        "Disable",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "gap",
        "Gap",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "offset",
        "Offset",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "closed_rotation",
        "Closed Rotation",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['deg'], 'defaultType' => 'deg']],
        false,
        false,
        [],
      ), c(
        "open_rotation",
        "Open Rotation",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['deg'], 'defaultType' => 'deg']],
        false,
        false,
        [],
      ), c(
        "custom_arrow",
        "Custom Arrow",
        [],
        ['type' => 'icon', 'layout' => 'vertical', 'iconOptions' => ['suggestions' => ['arrow']]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "dropdowns",
        "Dropdowns",
        [c(
        "wrapper",
        "Wrapper",
        [c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), c(
        "placement",
        "Placement",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['text' => 'Center', 'value' => 'center'], ['text' => 'Right', 'value' => 'right'], ['text' => 'Full Width', 'value' => 'full-width'], ['text' => 'Section Width', 'value' => 'section-width']], 'buttonBarOptions' => ['layout' => 'multiline', 'size' => 'small'], 'condition' => [[['path' => 'design.desktop_menu.vertical', 'operand' => 'is not set', 'value' => '']], [['path' => 'design.desktop_menu.vertical_mode', 'operand' => 'is none of', 'value' => ['accordion']]]]],
        false,
        false,
        [],
      ), c(
        "width",
        "Width",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['step' => 50, 'min' => 100, 'max' => 1140], 'condition' => [[['path' => 'design.desktop_menu.dropdowns.wrapper.placement', 'operand' => 'is none of', 'value' => ['full-width', 'section-width']]]]],
        false,
        false,
        [],
      ), c(
        "caret_radius",
        "Caret Radius",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => 'design.desktop_menu.vertical', 'operand' => 'is not set', 'value' => '']], [['path' => 'design.desktop_menu.vertical_mode', 'operand' => 'is none of', 'value' => ['accordion', 'side']]]]],
        false,
        false,
        [],
      ), c(
        "disable_caret",
        "Disable Caret",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => [[['path' => 'design.desktop_menu.vertical', 'operand' => 'is not set', 'value' => '']], [['path' => 'design.desktop_menu.vertical_mode', 'operand' => 'is none of', 'value' => ['accordion', 'side']]]]],
        false,
        false,
        [],
      ), c(
        "offset",
        "Offset",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['step' => 1, 'min' => 0, 'max' => 100], 'condition' => [[['path' => 'design.desktop_menu.vertical_mode', 'operand' => 'is none of', 'value' => ['accordion']], ['path' => 'design.desktop_menu.vertical', 'operand' => 'is set', 'value' => '']], [['path' => 'design.desktop_menu.vertical', 'operand' => 'is not set', 'value' => '']]]],
        false,
        false,
        [],
      ), c(
        "animation",
        "Animation",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'None', 'value' => 'none'], ['text' => 'Fade', 'value' => 'fade'], ['text' => 'Stripe', 'value' => 'stripe'], ['text' => 'Skew', 'value' => 'skew'], ['text' => 'Rotate', 'value' => 'rotate'], ['text' => 'Scale Down', 'value' => 'scale-down'], ['text' => 'Scale Down Right', 'value' => 'scale-down-r']], 'condition' => [[['path' => 'design.desktop_menu.vertical', 'operand' => 'is not set', 'value' => ''], ['path' => 'design.desktop_menu.vertical_mode', 'operand' => 'is none of', 'value' => ['accordion']]]]],
        false,
        false,
        [],
      ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms'], 'defaultType' => 'ms']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "links",
        "Links",
        [getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Title",
      "title",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Description",
      "description",
       ['type' => 'popout']
     ), c(
        "graphic",
        "Graphic",
        [c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "icon",
        "Icon",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "image_opacity",
        "Image Opacity",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "alignment",
        "Alignment",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'flex-start', 'text' => 'Top'], ['text' => 'Middle', 'value' => 'center'], ['text' => 'Bottom', 'value' => 'flex-end']]],
        false,
        false,
        [],
      ), c(
        "translate",
        "Translate",
        [c(
        "x",
        "X",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "y",
        "Y",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     ), c(
        "radius",
        "Radius",
        [],
        ['type' => 'border_radius', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "spacing",
        "Spacing",
        [c(
        "after_graphic",
        "After Graphic",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "before_description",
        "Before Description",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "between_links",
        "Between Links",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     ), c(
        "radius",
        "Radius",
        [],
        ['type' => 'border_radius', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "hover_background",
        "Hover Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "columns",
        "Columns",
        [getPresetSection(
      "EssentialElements\\typography",
      "Title",
      "title",
       ['type' => 'popout']
     ), c(
        "space_after_title",
        "Space After Title",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "gap_between_columns",
        "Gap Between Columns",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "stack_vertically",
        "Stack Vertically",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "additional_section",
        "Additional Section",
        [getPresetSection(
      "EssentialElements\\AtomV1MenuDropdownLinkDesign",
      "Links",
      "links",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\AtomV1MenuColumnDesign",
      "Columns",
      "columns",
       ['type' => 'popout']
     ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     ), c(
        "stack_horizontally",
        "Stack Horizontally",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.desktop_menu.dropdowns.additional_section.stack_horizontally', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "open_dropdowns_on_click",
        "Open Dropdowns on Click",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms'], 'defaultType' => 'ms']],
        false,
        false,
        [],
      ), c(
        "vertical",
        "Vertical",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "vertical_mode",
        "Vertical Mode",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Dropdown', 'value' => 'dropdown'], ['text' => 'Accordion', 'value' => 'accordion'], ['text' => 'Side', 'value' => 'side']], 'condition' => [[['path' => 'design.desktop_menu.vertical', 'operand' => 'is set', 'value' => '']]]],
        false,
        false,
        [],
      ), c(
        "vertical_alignment",
        "Alignment",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'flex-start', 'text' => 'Left', 'icon' => 'AlignLeftIcon'], ['text' => 'Center', 'value' => 'center', 'icon' => 'AlignCenterIcon'], ['text' => 'Right', 'value' => 'flex-end', 'icon' => 'AlignRightIcon']], 'condition' => [[['path' => 'design.desktop_menu.vertical', 'operand' => 'is set', 'value' => '']]]],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "mobile_menu",
        "Mobile Menu",
        [c(
        "show_at",
        "Show At",
        [],
        ['type' => 'breakpoint_dropdown', 'layout' => 'vertical', 'breakpointOptions' => ['enableNever' => true]],
        false,
        false,
        [],
      ), c(
        "mode",
        "Mode",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'default', 'text' => 'Default'], ['text' => 'Fullscreen', 'value' => 'fullscreen'], ['text' => 'Offcanvas', 'value' => 'offcanvas']]],
        false,
        false,
        [],
      ), c(
        "offcanvas_width",
        "Offcanvas Width",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.mobile_menu.mode', 'operand' => 'equals', 'value' => 'offcanvas']],
        false,
        false,
        [''],
      ), c(
        "offcanvas_position",
        "Offcanvas Position",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['text' => 'Right', 'value' => 'right']], 'condition' => ['path' => 'design.mobile_menu.mode', 'operand' => 'equals', 'value' => 'offcanvas']],
        false,
        false,
        [],
      ), c(
        "top_bar",
        "Top Bar",
        [c(
        "logo",
        "Logo",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'mediaOptions' => ['acceptedFileTypes' => ['image'], 'multiple' => false]],
        false,
        false,
        [],
      ), c(
        "logo_width",
        "Logo Width",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => 'design.mobile_menu.top_bar.logo', 'operand' => 'is set', 'value' => '']]]],
        true,
        false,
        [],
      ), c(
        "logo_on_right",
        "Logo On Right",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => ['path' => 'design.mobile_menu.top_bar.logo', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      ), c(
        "logo_url",
        "Logo URL",
        [],
        ['type' => 'url', 'layout' => 'vertical', 'condition' => ['path' => 'design.mobile_menu.top_bar.logo', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      ), c(
        "logo_alt",
        "Logo Alt",
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
        ['type' => 'text', 'layout' => 'vertical', 'condition' => ['path' => 'design.mobile_menu.top_bar.logo_alt.alt', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout'], 'condition' => [[['path' => 'design.mobile_menu.top_bar.logo', 'operand' => 'is set', 'value' => '']]]],
        false,
        false,
        [],
      ), c(
        "padding",
        "Padding",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "close_button",
        "Close Button",
        [c(
        "disable",
        "Disable",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "bar_height",
        "Bar Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.mobile_menu.mode', 'operand' => 'is one of', 'value' => ['offcanvas', 'fullscreen']]],
        false,
        false,
        [],
      ), c(
        "vertically_align_center",
        "Vertically Align Center",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => ['path' => 'design.mobile_menu.mode', 'operand' => 'is one of', 'value' => ['fullscreen', 'offcanvas']]],
        false,
        false,
        [],
      ), c(
        "offset",
        "Offset",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => 'design.mobile_menu.mode', 'operand' => 'is none of', 'value' => ['fullscreen', 'offcanvas']], 'unitOptions' => ['types' => ['px'], 'defaultType' => 'px']],
        false,
        false,
        [],
      ), c(
        "links",
        "Links",
        [c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "no_border",
        "No Border",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "border",
        "Border",
        [],
        ['type' => 'color', 'layout' => 'inline', 'condition' => ['path' => 'design.mobile_menu.links.no_border', 'operand' => 'is not set', 'value' => '']],
        false,
        false,
        [],
      ), c(
        "typography",
        "Typography",
        [getPresetSection(
      "EssentialElements\\typography",
      "Level 1",
      "level_1",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography",
      "Level 2",
      "level_2",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography",
      "Level 3",
      "level_3",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "active",
        "Active",
        [c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
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
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "padding",
        "Padding",
        [c(
        "level_1",
        "Level 1",
        [],
        ['type' => 'spacing_complex', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "level_2",
        "Level 2",
        [],
        ['type' => 'spacing_complex', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "level_3",
        "Level 3",
        [],
        ['type' => 'spacing_complex', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "dropdowns_like_desktop",
        "Dropdowns Like Desktop",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => ['path' => 'content.content.elements', 'operand' => 'equals', 'value' => 'hide-this-field']],
        false,
        false,
        [],
      ), c(
        "dropdowns",
        "Dropdowns",
        [c(
        "hide_link_graphic",
        "Hide Link Graphic",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "hide_link_description",
        "Hide Link Description",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.mobile_menu.links.dropdowns_like_desktop', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      ), c(
        "remove_column_titles",
        "Remove Column Titles",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "dropdown_arrows",
        "Dropdown Arrows",
        [c(
        "disable",
        "Disable",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "gap",
        "Gap",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "offset",
        "Offset",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "closed_rotation",
        "Closed Rotation",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['deg'], 'defaultType' => 'deg']],
        false,
        false,
        [],
      ), c(
        "open_rotation",
        "Open Rotation",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['deg'], 'defaultType' => 'deg']],
        false,
        false,
        [],
      ), c(
        "custom_arrow",
        "Custom Arrow",
        [],
        ['type' => 'icon', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "horizontally_center_links",
        "Horizontally Center Links",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "toggle",
        "Toggle",
        [c(
        "icon",
        "Icon",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "icon_open",
        "Icon Open",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "wrapper",
        "Wrapper",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
      ), c(
        "wrapper_open",
        "Wrapper Open",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "wrapper_padding",
        "Wrapper Padding",
        [c(
        "padding",
        "Padding",
        [],
        ['type' => 'spacing_complex', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "wrapper_radius",
        "Wrapper Radius",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "animation",
        "Animation",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['text' => 'Disabled', 'value' => 'disabled'], ['text' => 'None', 'value' => 'none'], ['text' => 'Arrow', 'value' => 'arrow'], ['text' => 'Collapse', 'value' => 'collapse'], ['value' => 'elastic', 'text' => 'Elastic'], ['text' => 'Emphatic', 'value' => 'emphatic'], ['text' => 'Minus', 'value' => 'minus'], ['text' => 'Slider', 'value' => 'slider'], ['text' => 'Spin', 'value' => 'spin'], ['text' => 'Spring', 'value' => 'spring'], ['text' => 'Squeeze', 'value' => 'squeeze'], ['value' => 'stand', 'text' => 'Stand'], ['text' => 'Vortex', 'value' => 'vortex'], ['text' => '3DX', 'value' => '3dx'], ['text' => '3DXY', 'value' => '3dxy'], ['text' => '3DY', 'value' => '3dy']]],
        false,
        false,
        [],
      ), c(
        "custom_icon",
        "Custom Icon",
        [c(
        "bar_width",
        "Bar Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "bar_height",
        "Bar Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "bar_spacing",
        "Bar Spacing",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "bar_radius",
        "Bar Radius",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "open_icon",
        "Open Icon",
        [],
        ['type' => 'icon', 'layout' => 'vertical', 'iconOptions' => ['suggestions' => ['menu', 'burger', 'ellipsis']]],
        false,
        false,
        [],
      ), c(
        "close_icon",
        "Close Icon",
        [],
        ['type' => 'icon', 'layout' => 'vertical', 'condition' => ['path' => 'design.mobile_menu.toggle.custom_icon.open_icon', 'operand' => 'is set', 'value' => '']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
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
      ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms'], 'defaultType' => 'ms']],
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
        "content",
        "Content",
        [c(
        "elements",
        "Elements",
        [],
        ['type' => 'add_registered_children', 'layout' => 'vertical', 'addRegisteredChildrenOptions' => ['addExtraElementsBySlug' => ['EssentialElements\TextLink', 'EssentialElements\Button']]],
        false,
        false,
        [],
      ), c(
        "accessibility",
        "Accessibility",
        [c(
        "attributes",
        "Attributes",
        [c(
        "name",
        "Name",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "value",
        "Value",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'inline_repeater', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "builder_behavior",
        "Keep Menu Selected While Styling",
        [],
        ['type' => 'builder_behavior', 'layout' => 'vertical', 'items' => [['value' => 'live', 'text' => 'Live'], ['text' => 'Edit', 'value' => 'edit']]],
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
        return ['0' =>  ['inlineStyles' => ['/* Hide menu during load */
.breakdance-menu {
  display: none;
}'],'builderCondition' => 'return true;','frontendCondition' => 'return false;','title' => 'Hide menu during load - Builder only',],'1' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/awesome-menu@1/awesome-menu.js'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/awesome-menu@1/awesome-menu.css'],'title' => 'Breakdance Menu',],'2' =>  ['inlineScripts' => ['{% set dropdowns = design.desktop_menu.dropdowns %}
new AwesomeMenu("%%SELECTOR%% .breakdance-menu", {
  dropdown: {
    openOnClick: {{ dropdowns.open_dropdowns_on_click ? \'true\' : \'false\' }},
    mode: {
      desktop: \'{{ design.desktop_menu.vertical ? design.desktop_menu.vertical_mode|default(\'dropdown\') : \'dropdown\' }}\'
    },
    placement: \'{{ dropdowns.wrapper.placement|default(\'left\') }}\',
    width: {{ dropdowns.wrapper.width ?? \'null\' }},
    animation: \'{{ dropdowns.wrapper.animation|default(\'fade\') }}\'
  },
  link: {
    effect: \'{{ design.desktop_menu.links.effect.effect_type }}\',
    effectDirection: \'{{ design.desktop_menu.links.effect.effect_direction }}\',
  },
  mobile: {
    breakpoint: \'{{ design.mobile_menu.show_at }}\',
    mode: \'{{ design.mobile_menu.mode|default(\'accordion\') }}\',
    offcanvasPosition: \'{{ design.mobile_menu.offcanvas_position|default(\'left\') }}\',
    offset: {{ design.mobile_menu.offset.number ?? \'null\' }},
    followLinks: true
  }
});'],'builderCondition' => 'return false;','frontendCondition' => 'return true;','title' => 'Frontend init',],];
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
        return [

'onPropertyChange' => [['script' => 'if (window.breakdanceMenus && window.breakdanceMenus[%%ID%%]) {
  window.breakdanceMenus[%%ID%%].destroy();
}

{% set dropdowns = design.desktop_menu.dropdowns %}
const options = {
  dropdown: {
    openOnClick: true,
    mode: {
      desktop: \'{{ design.desktop_menu.vertical ? design.desktop_menu.vertical_mode|default(\'dropdown\') : \'dropdown\' }}\'
    },
    placement: \'{{ dropdowns.wrapper.placement|default(\'left\') }}\',
    width: {{ dropdowns.wrapper.width ?? \'null\' }},
    animation: \'{{ dropdowns.wrapper.animation|default(\'fade\') }}\'
  },
  link: {
    effect: \'{{ design.desktop_menu.links.effect.effect_type }}\',
    effectDirection: \'{{ design.desktop_menu.links.effect.effect_direction }}\',
  },
  mobile: {
    breakpoint: \'{{ design.mobile_menu.show_at }}\',
    mode: \'{{ design.mobile_menu.mode|default(\'accordion\') }}\',
    offcanvasPosition: \'{{ design.mobile_menu.offcanvas_position|default(\'left\') }}\',
    offset: {{ design.mobile_menu.offset.number ?? \'null\' }},
    followLinks: true
  },
  isBuilder: true
};

window.breakdanceMenus[%%ID%%] = new AwesomeMenu("%%SELECTOR%% .breakdance-menu", options);',
],['script' => 'const menuId = \'%%ID%%\';

if (
  menuId &&
  window.breakdanceMenus &&
  window.breakdanceMenus[menuId]
) {
  const firstDropdown = document.querySelector(\'%%SELECTOR%% .breakdance-dropdown\');
  const anyOpen = document.querySelector(\'%%SELECTOR%% .breakdance-dropdown--open\');
  if (firstDropdown && !anyOpen) window.breakdanceMenus[menuId].openDropdown(firstDropdown);
}','dependencies' => ['design.desktop_menu.dropdowns'],
],],

'onMovedElement' => [['script' => 'if (window.breakdanceMenus && window.breakdanceMenus[%%ID%%]) {
  window.breakdanceMenus[%%ID%%].refresh();
}',
],],

'onBeforeDeletingElement' => [['script' => 'if (window.breakdanceMenus && window.breakdanceMenus[%%ID%%]) {
  window.breakdanceMenus[%%ID%%].destroy();
  delete window.breakdanceMenus[%%ID%%];
}',
],],

'onMountedElement' => [['script' => 'if (!window.breakdanceMenus) {
  window.breakdanceMenus = {};
}

if (window.breakdanceMenus && window.breakdanceMenus[%%ID%%]) {
  window.breakdanceMenus[%%ID%%].destroy();
}

{% set dropdowns = design.desktop_menu.dropdowns %}
const options = {
  dropdown: {
    openOnClick: true,
    mode: {
      desktop: \'{{ design.desktop_menu.vertical ? design.desktop_menu.vertical_mode|default(\'dropdown\') : \'dropdown\' }}\'
    },
    placement: \'{{ dropdowns.wrapper.placement|default(\'left\') }}\',
    width: {{ dropdowns.wrapper.width ?? \'null\' }},
    animation: \'{{ dropdowns.wrapper.animation|default(\'fade\') }}\'
  },
  dropdown: {
    openOnClick: true,
    placement: \'{{ design.desktop_menu.dropdowns.wrapper.placement|default(\'left\') }}\',
    width: {{ design.desktop_menu.dropdowns.wrapper.width ?? \'null\' }},
    mode: {
      desktop: \'{{ design.desktop_menu.vertical_mode|default(\'dropdown\') }}\' 
    },
    animation: \'{{ design.desktop_menu.dropdowns.wrapper.animation|default(\'fade\') }}\'
  },
  link: {
    effect: \'{{ design.desktop_menu.links.effect.effect_type }}\',
    effectDirection: \'{{ design.desktop_menu.links.effect.effect_direction }}\',
  },
  mobile: {
    breakpoint: \'{{ design.mobile_menu.show_at }}\',
    mode: \'{{ design.mobile_menu.mode|default(\'accordion\') }}\',
    offcanvasPosition: \'{{ design.mobile_menu.offcanvas_position|default(\'left\') }}\',
    offset: {{ design.mobile_menu.offset.number ?? \'null\' }},
    followLinks: true
  },
  isBuilder: true
};

window.breakdanceMenus[%%ID%%] = new AwesomeMenu("%%SELECTOR%% .breakdance-menu", options);',
],],];
    }

    static function nestingRule()
    {
        return ["type" => "container-restricted",   ];
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
        return 50;
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
        return ['design.mobile_menu.show_at'];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return false;
    }
}
