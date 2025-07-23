<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\MenuCustomArea",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class MenuCustomArea extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'SquareIcon';
    }

    static function tag()
    {
        return 'li';
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
        return 'Menu Custom Area';
    }

    static function className()
    {
        return 'bde-menu-custom-area';
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
        return ['content' => ['content' => ['enable_dropdown' => false, 'menu_source' => null, 'columns' => [['links' => [['text' => 'Our Story'], ['text' => 'Mission & Vision'], ['text' => 'Team']], 'title' => 'About Us'], ['title' => 'Services', 'links' => [['text' => 'Consulting'], ['text' => 'Development'], ['text' => 'Design']]], ['title' => 'Careers', 'links' => [['text' => 'Job Openings'], ['text' => 'Internships'], ['text' => 'Employee Benefits']]]], 'show_another_section' => false]]];
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
        "visibility",
        "Visibility",
        [c(
        "hide_in",
        "Hide In",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'desktop_menu', 'text' => 'Desktop Menu'], ['text' => 'Mobile Menu', 'value' => 'mobile_menu']]],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "container",
        "Container",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "min_height",
        "Min Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
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
     )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\LayoutV2",
      "Layout",
      "layout_v2",
       ['condition' => [[['path' => 'design.layout', 'operand' => 'is not set', 'value' => '']]], 'type' => 'popout']
     ), c(
        "dropdown",
        "Dropdown",
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
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['step' => 50, 'min' => 100, 'max' => 1140], 'condition' => [[['path' => 'design.desktop_menu.dropdowns.wrapper.placement', 'operand' => 'not equals', 'value' => 'full-width']]]],
        true,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\AtomV1MenuDropdownLinkDesign",
      "Links",
      "links",
       ['condition' => [[['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'standard']]], 'type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\AtomV1MenuColumnDesign",
      "Columns",
      "columns",
       ['condition' => [[['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'standard']]], 'type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\AtomV1MenuAdditionalSectionDesign",
      "Additional Section",
      "additional_section",
       ['condition' => [[['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'standard']]], 'type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "advanced",
        "Advanced",
        [c(
        "inherit_link_styles",
        "Inherit Link Styles",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "icons_inherit_link_styles",
        "Icons Inherit Link Styles",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "disable_desktop_styles_at",
        "Disable Desktop Styles At",
        [],
        ['type' => 'breakpoint_dropdown', 'layout' => 'vertical', 'breakpointOptions' => ['enableNever' => true]],
        false,
        false,
        [],
      ), c(
        "warning",
        "Warning",
        [],
        ['type' => 'alert_box', 'layout' => 'vertical', 'alertBoxOptions' => ['style' => 'warning', 'content' => '<p>It\'s recommended to set the same breakpoint that the Menu Builder mobile menu is enabled for.</p>']],
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
        "link",
        "Link",
        [],
        ['type' => 'link', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "enable_dropdown",
        "Enable Dropdown",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "menu_source",
        "Menu Source",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'standard', 'text' => 'Standard Controls'], ['text' => 'Global Block', 'value' => 'global_block']], 'condition' => [[['path' => 'content.content.enable_dropdown', 'operand' => 'is set', 'value' => '']]], 'buttonBarOptions' => ['size' => 'small', 'layout' => 'default']],
        false,
        false,
        [],
      ), c(
        "global_block",
        "Global Block",
        [],
        ['type' => 'global_block_chooser', 'layout' => 'vertical', 'condition' => [[['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'global_block'], ['path' => 'content.content.enable_dropdown', 'operand' => 'is set', 'value' => '']]]],
        false,
        false,
        [],
      ), c(
        "columns",
        "Columns",
        [c(
        "title",
        "Title",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "links",
        "Links",
        [c(
        "text",
        "Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "description",
        "Description",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'textOptions' => ['multiline' => true]],
        false,
        false,
        [],
      ), c(
        "url",
        "URL",
        [],
        ['type' => 'link', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "icon_or_image",
        "Icon or Image",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['text' => 'Icon', 'value' => 'icon', 'icon' => 'IconsIcon'], ['text' => 'Image', 'value' => 'image', 'icon' => 'ImageIcon']]],
        false,
        false,
        [],
      ), c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'condition' => ['path' => '%%CURRENTPATH%%.icon_or_image', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
      ), c(
        "icon",
        "Icon",
        [],
        ['type' => 'icon', 'layout' => 'vertical', 'condition' => ['path' => '%%CURRENTPATH%%.icon_or_image', 'operand' => 'equals', 'value' => 'icon']],
        false,
        false,
        [],
      ), c(
        "advanced",
        "Advanced",
        [getPresetSection(
      "EssentialElements\\AtomV1MenuDropdownLinkGraphicDesign",
      "Graphic",
      "graphic",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{text}', 'defaultTitle' => 'Link', 'buttonName' => 'Add Link', 'defaultNewValue' => ['text' => 'Link']]],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{title}', 'defaultTitle' => 'Column', 'buttonName' => 'Add Column'], 'condition' => [[['path' => 'content.content.enable_dropdown', 'operand' => 'is set', 'value' => ''], ['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'standard']]]],
        false,
        false,
        [],
      ), c(
        "show_another_section",
        "Show Another Section",
        [],
        ['type' => 'toggle', 'layout' => 'inline', 'condition' => [[['path' => 'content.content.enable_dropdown', 'operand' => 'is set', 'value' => ''], ['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'standard']]]],
        false,
        false,
        [],
      ), c(
        "columns_2",
        "Columns 2",
        [c(
        "title",
        "Title",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "links",
        "Links",
        [c(
        "text",
        "Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "description",
        "Description",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'textOptions' => ['multiline' => true]],
        false,
        false,
        [],
      ), c(
        "url",
        "URL",
        [],
        ['type' => 'link', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "icon_or_image",
        "Icon or Image",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['text' => 'Icon', 'value' => 'icon', 'icon' => 'IconsIcon'], ['text' => 'Image', 'value' => 'image', 'icon' => 'ImageIcon']]],
        false,
        false,
        [],
      ), c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'condition' => ['path' => '%%CURRENTPATH%%.icon_or_image', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
      ), c(
        "icon",
        "Icon",
        [],
        ['type' => 'icon', 'layout' => 'vertical', 'condition' => ['path' => '%%CURRENTPATH%%.icon_or_image', 'operand' => 'equals', 'value' => 'icon']],
        false,
        false,
        [],
      ), c(
        "advanced",
        "Advanced",
        [getPresetSection(
      "EssentialElements\\AtomV1MenuDropdownLinkGraphicDesign",
      "Graphic",
      "graphic",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{text}', 'defaultTitle' => 'Link', 'buttonName' => 'Add Link', 'defaultNewValue' => ['text' => 'Link']]],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{title}', 'defaultTitle' => 'Column', 'buttonName' => 'Add Column'], 'condition' => [[['path' => 'content.content.show_another_section', 'operand' => 'is set', 'value' => ''], ['path' => 'content.content.enable_dropdown', 'operand' => 'is set', 'value' => ''], ['path' => 'content.content.menu_source', 'operand' => 'equals', 'value' => 'standard']]]],
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
        return [

'onPropertyChange' => [['script' => 'const element = document.querySelector(\'[data-node-id="%%ID%%"]\');
const menuElement = element.closest(\'.bde-menu\');
const menuId = menuElement ? menuElement.dataset.nodeId : null;

if (
  menuId &&
  window.breakdanceMenus &&
  window.breakdanceMenus[menuId]
) {
  window.breakdanceMenus[menuId].refresh();
}','runForAllChildren' => true,
],],

'onMountedElement' => [['script' => 'const element = document.querySelector(\'[data-node-id="%%ID%%"]\');
const menuElement = element.closest(\'.bde-menu\');
const menuId = menuElement ? menuElement.dataset.nodeId : null;

if (
  menuId &&
  window.breakdanceMenus &&
  window.breakdanceMenus[menuId]
) {
  window.breakdanceMenus[menuId].refresh();
}','runForAllChildren' => true,
],],];
    }

    static function nestingRule()
    {
        return ["type" => "container", "restrictedToBeADirectChildOf" => ['EssentialElements\MenuBuilder'],  ];
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
        return 90;
    }

    static function dynamicPropertyPaths()
    {
        return [];
    }

    static function additionalClasses()
    {
        return [['name' => 'bde-menu-custom-area-hide-in-desktop-menu', 'template' => '{% if design.visibility.hide_in == \'desktop_menu\' %}yes{% endif %}'], ['name' => 'bde-menu-custom-area-hide-in-mobile-menu', 'template' => '{% if design.visibility.hide_in == \'mobile_menu\' %}yes{% endif %}'], ['name' => 'breakdance-menu-item', 'template' => 'yes']];
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
        return ['content.content.menu_source', 'content.content.global_block', 'content.content.enable_dropdown', 'content.content.columns', 'content.content.show_another_section', 'content.content.columns_2', 'content.content.link', 'design.dropdown.wrapper.width'];
    }
}
