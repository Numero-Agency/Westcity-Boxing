<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\FlexibleContent",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class FlexibleContent extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'DatabaseIcon';
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
        return 'Flexible Content';
    }

    static function className()
    {
        return 'bde-flexible-content';
    }

    static function category()
    {
        return 'dynamic';
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
        return ['design' => ['list' => ['layout' => 'list', 'items_per_row' => ['breakpoint_base' => 3, 'breakpoint_tablet_landscape' => 2], 'one_item_at' => 'breakpoint_tablet_portrait', 'space_between_items' => ['breakpoint_base' => ['number' => 0, 'unit' => 'px', 'style' => '0px']], 'accordion' => ['item' => ['style' => 'bordered', 'icon' => ['icon' => ['id' => 10544, 'slug' => 'icon-chevron-right.', 'name' => 'chevron right', 'svgCode' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>', 'iconSetSlug' => 'FontAwesome 6 Free - Solid']]]]], 'post' => ['container' => ['background' => null, 'borders' => ['border' => null, 'radius' => ['breakpoint_base' => ['all' => ['number' => 4, 'unit' => 'px', 'style' => '4px'], 'topLeft' => ['number' => 4, 'unit' => 'px', 'style' => '4px'], 'topRight' => ['number' => 4, 'unit' => 'px', 'style' => '4px'], 'bottomLeft' => ['number' => 4, 'unit' => 'px', 'style' => '4px'], 'bottomRight' => ['number' => 4, 'unit' => 'px', 'style' => '4px'], 'editMode' => 'all']], 'shadow' => ['breakpoint_base' => ['shadows' => [['color' => '#0000000F', 'x' => '2', 'y' => '4', 'blur' => '20', 'spread' => '0', 'position' => 'outset']], 'style' => '2px 4px 20px 0px #0000000F']]], 'padding' => ['padding' => ['breakpoint_base' => ['left' => ['number' => 20, 'unit' => 'px', 'style' => '20px'], 'right' => ['number' => 20, 'unit' => 'px', 'style' => '20px'], 'top' => ['number' => 20, 'unit' => 'px', 'style' => '20px'], 'bottom' => ['number' => 20, 'unit' => 'px', 'style' => '20px']]]], 'alignment' => 'flex-start'], 'image' => null, 'title' => null, 'meta' => null, 'content' => null, 'button' => ['style' => null]], 'item' => ['padding' => ['padding' => ['breakpoint_base' => ['left' => ['number' => 0, 'unit' => 'px', 'style' => '0px'], 'right' => ['number' => 0, 'unit' => 'px', 'style' => '0px'], 'top' => ['number' => 0, 'unit' => 'px', 'style' => '0px'], 'bottom' => ['number' => 0, 'unit' => 'px', 'style' => '0px']]]]]], 'content' => ['post' => ['title' => ['disable' => false], 'meta' => ['disable' => false, 'meta' => ['author', 'date', 'comment'], 'separator' => '/', 'link' => false], 'taxonomy' => ['disable' => true], 'content' => ['type' => 'excerpt', 'excerpt_length' => 30], 'button' => ['disable' => false]], 'query' => null, 'field' => null]];
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
        return [getPresetSection(
      "EssentialElements\\posts-list-design",
      "List",
      "list",
       ['type' => 'popout']
     ), c(
        "item",
        "Item",
        [c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "container",
        "Container",
        [c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
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
        "field",
        "Field",
        [c(
        "flexible_content_field",
        "Flexible Content Field",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'dropdownOptions' => ['populate' => ['path' => '', 'text' => '', 'value' => '', 'fetchDataAction' => 'breakdance_fetch_flexible_content_fields', 'fetchContextPath' => '', 'refetchPaths' => []]]],
        false,
        false,
        [],
      ), c(
        "tag",
        "Tag",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'article', 'text' => 'article'], ['text' => 'section', 'value' => 'section'], ['text' => 'div', 'value' => 'div']]],
        false,
        false,
        [],
      ), c(
        "item_title",
        "Item Title",
        [],
        ['type' => 'text', 'layout' => 'inline', 'variableOptions' => ['enabled' => false], 'condition' => [[['path' => 'design.list.layout', 'operand' => 'is one of', 'value' => ['accordion', 'tabs']]]]],
        false,
        false,
        [],
      ), c(
        "advanced",
        "Advanced",
        [c(
        "when_empty",
        "When Empty",
        [],
        ['type' => 'global_block_chooser', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "blocks",
        "Blocks",
        [c(
        "blocks",
        "Blocks",
        [c(
        "layout",
        "Layout",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'dropdownOptions' => ['populate' => ['path' => '', 'text' => '', 'value' => '', 'fetchDataAction' => 'breakdance_fetch_flexible_content_layouts', 'fetchContextPath' => 'content.field.flexible_content_field', 'refetchPaths' => ['content.field.flexible_content_field']]]],
        false,
        false,
        [],
      ), c(
        "block",
        "Block",
        [],
        ['type' => 'global_block_chooser', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{layout}', 'defaultTitle' => 'Block', 'buttonName' => 'Add Block']],
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
        return ['0' =>  ['title' => 'Posts','styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-posts@1/posts.css'],],'1' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/swiper-bundle.min.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-swiper/breakdance-swiper.js'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/swiper-bundle.min.css','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/breakdance-swiper-preset-defaults.css'],'builderCondition' => 'return {{ design.list.layout == \'slider\' }};','frontendCondition' => 'return {{ design.list.layout == \'slider\' }};','title' => 'Slider',],'2' =>  ['title' => 'Slider - Frontend','inlineScripts' => ['window.BreakdanceSwiper().update({
  id: \'%%UNIQUESLUG%%\', selector:\'%%SELECTOR%%\',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});'],'frontendCondition' => 'return {{ design.list.layout == \'slider\' }};','builderCondition' => 'return false;',],'3' =>  ['inlineScripts' => ['window.BreakdancePostsList?.loadMorePostsInit(
  {
    selector: "%%SELECTOR%%",
    postId: "%%POSTID%%",
    id: "%%ID%%"
  }
)'],'frontendCondition' => '{% if content.pagination.pagination == "load_more" and not content.filter_bar.enable %}
return true;
{% else%}
 return false;
{% endif %}','builderCondition' => 'return false;','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-pagination@1/pagination.js'],'title' => 'Pagination - Load more',],'4' =>  ['inlineScripts' => ['window.BreakdancePostsList?.infiniteScrollInit(
  {
    selector: "%%SELECTOR%%",
    postId: "%%POSTID%%",
    id: "%%ID%%"
  }
)'],'frontendCondition' => '{% if content.pagination.pagination == "infinite" and not content.filter_bar.enable %}
return true;
{% else%}
 return false;
{% endif %}','builderCondition' => 'return false;','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-pagination@1/pagination.js'],'title' => 'Pagination - Infinite scroll',],'5' =>  ['title' => 'Filter Bar','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/isotope-layout@3.0.6/isotope.pkgd.min.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-filter@1/filter.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.js'],'builderCondition' => 'return {{ content.filter_bar.enable ? \'true\' : \'false\' }};','frontendCondition' => 'return {{ content.filter_bar.enable ? \'true\' : \'false\' }};','styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.css'],],'6' =>  ['title' => 'Filter Bar - Frontend','inlineScripts' => ['new BreakdanceFilter(\'%%SELECTOR%%\', {
  layout: \'{{ design.list.layout }}\',
  isVertical: {{ design.filter_bar.vertical|json_encode }},
  horizontalAt: {{ design.filter_bar.horizontal_at|json_encode }}
});'],'frontendCondition' => 'return {{ content.filter_bar.enable ? \'true\' : \'false\' }};','builderCondition' => 'return false;',],'7' =>  ['title' => 'Load Advanced Accordion','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-advanced-accordion/breakdance-advanced-accordion.js'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-advanced-accordion/accordion.css'],'builderCondition' => 'return {{ design.list.layout == \'accordion\' ? \'true\' : \'false\' }};','frontendCondition' => 'return {{ design.list.layout == \'accordion\' ? \'true\' : \'false\' }};',],'8' =>  ['title' => 'Load Advanced Accordion Management','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-advanced-accordion/breakdance-advanced-accordion-management.js'],'builderCondition' => 'return {{ design.list.layout == \'accordion\' ? \'true\' : \'false\' }};','frontendCondition' => 'return false;',],'9' =>  ['inlineScripts' => ['new BreakdanceAdvancedAccordion(\'%%SELECTOR%% .bde-loop-accordion\', { accordion: {{ design.list.accordion|json_encode }}, openFirst: {{ design.list.first_item_opened|json_encode }} } );'],'builderCondition' => 'return false;','title' => 'Init BreakdanceAdvancedAccordion in the frontend','frontendCondition' => 'return {{ design.list.layout == \'accordion\' ? \'true\' : \'false\' }};',],'10' =>  ['title' => 'Load Breakdance Tabs','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.js'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.css'],'builderCondition' => 'return {{ design.list.layout == \'tabs\' ? \'true\' : \'false\' }};','frontendCondition' => 'return {{ design.list.layout == \'tabs\' ? \'true\' : \'false\' }};',],'11' =>  ['frontendCondition' => 'return {{ design.list.layout == \'tabs\' ? \'true\' : \'false\' }};','builderCondition' => 'return false;','title' => 'Init Breakdance Tabs in the frontend','inlineScripts' => ['new BreakdanceTabs(\'%%SELECTOR%%\', { openOnHover: {{ design.list.tabs.open_on_hover|json_encode }}, activeTab: {{ design.list.tabs.active_tab|json_encode }}, isVertical: {{ design.tabs.vertical|json_encode }}, horizontalAt: {{ design.tabs.horizontal_at|json_encode }} } );'],],];
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

'onPropertyChange' => [['script' => '{% if design.list.layout == \'slider\' %}
window.BreakdanceSwiper().update({
  id: \'%%ID%%\', selector:\'%%SELECTOR%%\',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});
{% else %}
window.swiperInstances && window.swiperInstances[\'%%ID%%\'] && window.BreakdanceSwiper().destroy(
  \'%%ID%%\'
);
{% endif %}','dependencies' => ['design.list.layout'],
],['script' => 'if (window.breakdanceFilterInstances && window.breakdanceFilterInstances[%%ID%%]) {
  window.breakdanceFilterInstances[%%ID%%].update({
    layout: \'{{ design.list.layout }}\',
    isVertical: {{ design.filter_bar.vertical|json_encode }},
    horizontalAt: {{ design.filter_bar.horizontal_at|json_encode }}
  });
}',
],['script' => '{% if design.list.layout == \'accordion\' %}
  if (window.breakdanceAdvancedAccordionInstances && window.breakdanceAdvancedAccordionInstances[%%ID%%]) {
    window.breakdanceAdvancedAccordionInstances[%%ID%%].destroy();
  }

  window.breakdanceAdvancedAccordionInstances[%%ID%%] = new BreakdanceAdvancedAccordion(\'%%SELECTOR%% .bde-loop-accordion\', { accordion: {{ design.list.accordion.accordion|json_encode }}, openFirst: {{ design.list.accordion.first_item_opened|json_encode }} } );
{% endif %}',
],['script' => '{% if design.list.layout == \'tabs\' %}
if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
  window.breakdanceTabsInstances[%%ID%%].destroy();
}

window.breakdanceTabsInstances[%%ID%%] = new BreakdanceTabs(\'%%SELECTOR%%\', { openOnHover: {{ design.list.tabs.open_on_hover|json_encode }}, activeTab: {{ design.list.tabs.active_tab|json_encode }}, isVertical: {{ design.tabs.vertical|json_encode }}, horizontalAt: {{ design.tabs.horizontal_at|json_encode }} } );
{% endif %}',
],],

'onMovedElement' => [['script' => '{% if design.list.layout == \'slider\' %}
window.BreakdanceSwiper().update({
  id: \'%%ID%%\', selector:\'%%SELECTOR%%\',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});
{% endif %}',
],['script' => 'if (window.breakdanceFilterInstances && window.breakdanceFilterInstances[%%ID%%]) {
  window.breakdanceFilterInstances[%%ID%%].update();
}',
],['script' => '{% if design.list.layout == \'accordion\' %}
  if (window.breakdanceAdvancedAccordionInstances && window.breakdanceAdvancedAccordionInstances[%%ID%%]) {
    window.breakdanceAdvancedAccordionInstances[%%ID%%].update();
  }
{% endif %}',
],['script' => '{% if design.list.layout == \'tabs\' %}
if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
  window.breakdanceTabsInstances[%%ID%%].update();
}
{% endif %}',
],],

'onBeforeDeletingElement' => [['script' => '{% if design.list.layout == \'slider\' %}
window.swiperInstances && window.swiperInstances[\'%%ID%%\'] && window.BreakdanceSwiper().destroy(
  \'%%ID%%\'
);
{% endif %}',
],['script' => 'if (window.breakdanceFilterInstances && window.breakdanceFilterInstances[%%ID%%]) {
  window.breakdanceFilterInstances[%%ID%%].destroy();
  delete window.breakdanceFilterInstances[%%ID%%];
}',
],['script' => '{% if design.list.layout == \'accordion\' %}
  if (window.breakdanceAdvancedAccordionInstances && window.breakdanceAdvancedAccordionInstances[%%ID%%]) {
    window.breakdanceAdvancedAccordionInstances[%%ID%%].destroy();
    delete window.breakdanceAdvancedAccordionInstances[%%ID%%];
  }
{% endif %}',
],['script' => '{% if design.list.layout == \'tabs\' %}
if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
  window.breakdanceTabsInstances[%%ID%%].destroy();
  delete window.breakdanceTabsInstances[%%ID%%];
}
{% endif %}',
],],

'onMountedElement' => [['script' => '{% if design.list.layout == \'slider\' %}
window.BreakdanceSwiper().update({
  id: \'%%ID%%\', selector:\'%%SELECTOR%%\',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});
{% endif %}',
],['script' => 'if (!window.breakdanceFilterInstances) window.breakdanceFilterInstances = {};
{% if content.filter_bar.enable %}
  window.breakdanceFilterInstances[%%ID%%] = new BreakdanceFilter(\'%%SELECTOR%%\', {
    layout: \'{{ design.list.layout }}\',
    isVertical: {{ design.filter_bar.vertical|json_encode }},
    horizontalAt: {{ design.filter_bar.horizontal_at|json_encode }}
  });
{% endif %}',
],['script' => '{% if design.list.layout == \'accordion\' %}
  if (!window.breakdanceAdvancedAccordionInstances) window.breakdanceAdvancedAccordionInstances = {};

      if (window.breakdanceAdvancedAccordionInstances && window.breakdanceAdvancedAccordionInstances[%%ID%%]) {
        window.breakdanceAdvancedAccordionInstances[%%ID%%].destroy();
      }

      window.breakdanceAdvancedAccordionInstances[%%ID%%] = new BreakdanceAdvancedAccordion(\'%%SELECTOR%% .bde-loop-accordion\', { accordion: {{ design.list.accordion.accordion|json_encode }}, openFirst: {{ design.list.accordion.first_item_opened|json_encode }} } );
{% endif %}
',
],['script' => '{% if design.list.layout == \'tabs\' %}
if (!window.breakdanceTabsInstances) window.breakdanceTabsInstances = {};

if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
  window.breakdanceTabsInstances[%%ID%%].destroy();
}

window.breakdanceTabsInstances[%%ID%%] = new BreakdanceTabs(\'%%SELECTOR%%\', { openOnHover: {{ design.list.tabs.open_on_hover|json_encode }}, activeTab: {{ design.list.tabs.active_tab|json_encode }}, isVertical: {{ design.tabs.vertical|json_encode }}, horizontalAt: {{ design.tabs.horizontal_at|json_encode }} } );
{% endif %}',
],],];
    }

    static function nestingRule()
    {
        return ["type" => "final-that-is-allowed-inside-root",   ];
    }

    static function spacingBars()
    {
        return [['cssProperty' => 'margin-top', 'location' => 'outside-top', 'affectedPropertyPath' => 'design.spacing.margin_top.%%BREAKPOINT%%'], ['cssProperty' => 'margin-bottom', 'location' => 'outside-bottom', 'affectedPropertyPath' => 'design.spacing.margin_bottom.%%BREAKPOINT%%']];
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
        return 2102;
    }

    static function dynamicPropertyPaths()
    {
        return [['accepts' => 'string', 'path' => 'content.field.item_title']];
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
        return ['design.list.one_item_at', 'design.list.layout', 'design.list.slider.settings.advanced.one_per_view_at', 'design.list.slider.settings.advanced.slides_per_group', 'design.list.slider.arrows.overlay', 'design.list.slider.arrows.disable'];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return ['content', 'design.list.layout', 'design.filter_bar', 'design.list.accordion.item.style', 'design.list.tabs'];
    }
}
