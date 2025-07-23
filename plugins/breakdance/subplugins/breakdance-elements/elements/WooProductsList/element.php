<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\Wooproductslist",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class Wooproductslist extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'Grid2Icon';
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
        return 'Products List';
    }

    static function className()
    {
        return 'bde-wooproductslist';
    }

    static function category()
    {
        return 'woocommerce';
    }

    static function badge()
    {
        return ['backgroundColor' => 'var(--brandWooCommerceBackground)', 'textColor' => 'var(--brandWooCommerce)', 'label' => 'Woo'];
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
        return ['content' => ['content' => ['product_count_to_show' => 9, 'order_by' => 'date', 'order' => 'DESC']], 'design' => ['layout' => ['layout' => 'grid', 'slider' => ['settings' => ['advanced' => ['slides_per_view' => ['breakpoint_base' => 4], 'between_slides' => ['breakpoint_base' => ['number' => 30, 'unit' => 'px', 'style' => '30px']], 'one_per_view_at' => 'breakpoint_phone_landscape']]]]]];
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
        "elements",
        "Elements",
        [c(
        "image",
        "Image",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_before",
        "Space Before",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), c(
        "show_second_image_on_hover",
        "Show Second Image On Hover",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms', 's']], 'condition' => [[['path' => '%%CURRENTPATH%%.show_second_image_on_hover', 'operand' => 'is set', 'value' => '']]]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "title",
        "Title",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_effects",
      "Typography",
      "typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "price",
        "Price",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_effects",
      "Old Price Typography",
      "old_price_typography",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography_with_effects",
      "Current Price Typography",
      "current_price_typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "rating",
        "Rating",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "review_count",
        "Review Count",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography",
      "Count Typography",
      "count_typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "sale_badge",
        "Sale Badge",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'top-left', 'text' => 'Top Left'], ['text' => 'Top Right', 'value' => 'top-right']]],
        true,
        false,
        [],
      ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline', 'colorOptions' => ['type' => 'solidAndGradient']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography",
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
      "Padding",
      "padding",
       ['type' => 'popout']
     ), c(
        "nudge",
        "Nudge",
        [c(
        "x",
        "X",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['min' => -48, 'max' => 48, 'step' => 1]],
        true,
        false,
        [],
      ), c(
        "y",
        "Y",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['min' => -48, 'max' => 48, 'step' => 1]],
        true,
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
        "excerpt",
        "Excerpt",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_effects",
      "Typography",
      "typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "categories",
        "Categories",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_effects",
      "Typography",
      "typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "quantity_input",
        "Quantity Input",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "button",
        "Button",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => ['path' => '%%CURRENTPATH%%.include', 'operand' => 'equals', 'value' => 'enable']],
        true,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\AtomV1CustomButtonDesign",
      "Styles",
      "styles",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "custom_areas",
        "Custom Areas",
        [c(
        "areas",
        "Areas",
        [c(
        "global_block",
        "Global Block",
        [],
        ['type' => 'global_block_chooser', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'inside', 'text' => 'Inside Link'], ['text' => 'Outside Link', 'value' => 'outside']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
      ), c(
        "space_before",
        "Space Before",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "space_after",
        "Space After",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'vertical'],
        false,
        false,
        [],
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '', 'defaultTitle' => 'Area', 'buttonName' => 'Add Area']],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "layout",
        "Layout",
        [c(
        "layout",
        "Layout",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'grid', 'text' => 'Grid'], ['text' => 'Slider', 'value' => 'slider']], 'hideForElements' => ['EssentialElements\Wooshoppage']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\AtomV1SwiperSettings",
      "Slider",
      "slider",
       ['condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'equals', 'value' => 'slider']]], 'type' => 'popout']
     ), c(
        "products_per_row",
        "Products Per Row",
        [],
        ['type' => 'number', 'layout' => 'inline', 'condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'not equals', 'value' => 'slider']]]],
        true,
        false,
        [],
      ), c(
        "between_products",
        "Between Products",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'not equals', 'value' => 'slider']]]],
        true,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "product_wrapper",
        "Product Wrapper",
        [c(
        "align_content",
        "Align Content",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'start', 'text' => 'Left', 'icon' => 'AlignLeftIcon'], ['value' => 'center', 'text' => 'Center', 'icon' => 'AlignCenterIcon'], ['icon' => 'AlignRightIcon', 'text' => 'Right', 'value' => 'end']]],
        false,
        false,
        [],
      ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline', 'colorOptions' => ['type' => 'solidAndGradient']],
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
      "EssentialElements\\tabs_design",
      "Filter Bar",
      "filter_bar",
       ['condition' => [[['path' => 'content.filter_bar.enable', 'operand' => 'is set', 'value' => '']]], 'type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Spacing",
      "spacing",
       ['type' => 'popout']
     ), c(
        "advanced",
        "Advanced",
        [getPresetSection(
      "EssentialElements\\WooGlobalStylerOverride",
      "Override Global Styles",
      "override_global_styles",
       ['type' => 'popout']
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
        "show_products",
        "Show Products",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'all', 'text' => 'All'], ['text' => 'Featured', 'value' => 'featured'], ['text' => 'On sale', 'value' => 'sale'], ['text' => 'Manually', 'value' => 'manually'], ['text' => 'Query', 'value' => 'query']], 'buttonBarOptions' => ['size' => 'small', 'layout' => 'multiline']],
        false,
        false,
        [],
      ), c(
        "products",
        "Products",
        [],
        ['type' => 'post_chooser', 'layout' => 'vertical', 'condition' => ['path' => 'content.content.show_products', 'operand' => 'equals', 'value' => 'manually'], 'postChooserOptions' => ['multiple' => true, 'showThumbnails' => true, 'postType' => 'product']],
        false,
        false,
        [],
      ), c(
        "query",
        "Query",
        [],
        ['type' => 'wp_query', 'layout' => 'vertical', 'queryOptions' => ['postTypes' => ['product']], 'condition' => ['path' => 'content.content.show_products', 'operand' => 'equals', 'value' => 'query']],
        false,
        false,
        [],
      ), c(
        "product_count",
        "Product Count",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "order_by",
        "Order By",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'date', 'text' => 'Date'], ['text' => 'Price', 'value' => 'price'], ['text' => 'Random', 'value' => 'rand']]],
        false,
        false,
        [],
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'DESC', 'text' => 'Descending'], ['text' => 'Ascending', 'value' => 'ASC']]],
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
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'accordion']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\posts-filter-bar",
      "Filter Bar",
      "filter_bar",
       ['type' => 'popout']
     )];
    }

    static function settingsControls()
    {
        return [];
    }

    static function dependencies()
    {
        return ['0' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/swiper-bundle.min.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-swiper/breakdance-swiper.js'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/swiper-bundle.min.css','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/breakdance-swiper-preset-defaults.css'],'builderCondition' => 'return true;','frontendCondition' => 'return \'{{ design.layout.layout }}\' == \'slider\';','title' => 'Slider',],'1' =>  ['inlineScripts' => ['window.BreakdanceSwiper().update({
  id: \'%%UNIQUESLUG%%\',
  selector:\'%%SELECTOR%%\',
  isBuilder: true,
  extras: {
    wrapperClass: \'products\',
    slideClass: \'product\',
    el: \'%%SELECTOR%% .woocommerce\'
  },
  settings: {{ design.layout.slider.settings|json_encode }},
  paginationSettings:{{ design.layout.slider.pagination|json_encode }}
});'],'builderCondition' => 'return false;','frontendCondition' => 'return \'{{ design.layout.layout }}\' == \'slider\';','title' => 'Frontend Slider Init',],'2' =>  ['title' => 'Filter Bar','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/isotope-layout@3.0.6/isotope.pkgd.min.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-filter@1/filter.js','%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.js'],'builderCondition' => 'return {{ content.filter_bar.enable ? \'true\' : \'false\' }};','frontendCondition' => 'return {{ content.filter_bar.enable ? \'true\' : \'false\' }};','styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.css'],],'3' =>  ['title' => 'Filter Bar - Frontend','inlineScripts' => ['new BreakdanceFilter(\'%%SELECTOR%%\', {
  layout: \'{{ design.layout.layout }}\',
  wrapperSelector: \'.products\',
  itemSelector: \'.product\',
  isVertical: {{ design.filter_bar.vertical|json_encode }},
  horizontalAt: {{ design.filter_bar.horizontal_at|json_encode }}
});'],'frontendCondition' => 'return {{ content.filter_bar.enable ? \'true\' : \'false\' }};','builderCondition' => 'return false;',],];
    }

    static function settings()
    {
        return ['requiredPlugins' => ['WooCommerce'], 'proOnly' => true];
    }

    static function addPanelRules()
    {
        return false;
    }

    static public function actions()
    {
        return [

'onMountedElement' => [['script' => 'if (\'{{ design.layout.layout }}\' === \'slider\') {
  window.BreakdanceSwiper().update({
    id: \'%%ID%%\',
    selector:\'%%SELECTOR%%\',
    isBuilder: true,
    extras: {
      wrapperClass: \'products\',
      slideClass: \'product\',
      el: \'%%SELECTOR%% .woocommerce\'
    },
    settings: {{ design.layout.slider.settings|json_encode }},
    paginationSettings:{{ design.layout.slider.pagination|json_encode }}
  });
}',
],['script' => 'if (!window.breakdanceFilterInstances) window.breakdanceFilterInstances = {};

{% if content.filter_bar.enable %}
  window.breakdanceFilterInstances[%%ID%%] = new BreakdanceFilter(\'%%SELECTOR%%\', {
    layout: \'{{ design.layout.layout }}\',
    wrapperSelector: \'.products\',
    itemSelector: \'.product\',
    isVertical: {{ design.filter_bar.vertical|json_encode }},
    horizontalAt: {{ design.filter_bar.horizontal_at|json_encode }}
  });
{% endif %}',
],],

'onPropertyChange' => [['script' => 'if (\'{{ design.layout.layout }}\' === \'slider\') {
  window.BreakdanceSwiper().update({
    id: \'%%ID%%\',
    selector:\'%%SELECTOR%%\',
    isBuilder: true,
    extras: {
      wrapperClass: \'products\',
      slideClass: \'product\',
      el: \'%%SELECTOR%% .woocommerce\'
    },
    settings: {{ design.layout.slider.settings|json_encode }},
    paginationSettings:{{ design.layout.slider.pagination|json_encode }}
  });
} else {
  window.BreakdanceSwiper().destroy(\'%%ID%%\');
}',
],['script' => 'if (window.breakdanceFilterInstances && window.breakdanceFilterInstances[%%ID%%]) {
  window.breakdanceFilterInstances[%%ID%%].update({
    layout: \'{{ design.layout.layout }}\',
    wrapperSelector: \'.products\',
    itemSelector: \'.product\',
    isVertical: {{ design.filter_bar.vertical|json_encode }},
    horizontalAt: {{ design.filter_bar.horizontal_at|json_encode }}
  });
}',
],],

'onBeforeDeletingElement' => [['script' => 'if (\'{{ design.layout.layout }}\' === \'slider\') {
  window.BreakdanceSwiper().destroy(\'%%ID%%\');
}',
],['script' => 'if (window.breakdanceFilterInstances && window.breakdanceFilterInstances[%%ID%%]) {
  window.breakdanceFilterInstances[%%ID%%].destroy();
  delete window.breakdanceFilterInstances[%%ID%%];
}',
],],

'onMovedElement' => [['script' => 'if (window.breakdanceFilterInstances && window.breakdanceFilterInstances[%%ID%%]) {
  window.breakdanceFilterInstances[%%ID%%].update();
}',
],],];
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
        return 0;
    }

    static function dynamicPropertyPaths()
    {
        return [];
    }

    static function additionalClasses()
    {
        return [['name' => 'breakdance-woocommerce', 'template' => 'yes'], ['name' => 'bde-wooproductslist-isotope', 'template' => '{% if content.filter_bar.enable %}yes{% endif %}']];
    }

    static function projectManagement()
    {
        return false;
    }

    static function propertyPathsToWhitelistInFlatProps()
    {
        return ['design.filter_bar.horizontal_at', 'design.filter_bar.responsive.visible_at', 'design.layout.slider.settings.advanced.one_per_view_at', 'design.layout.slider.settings.advanced.slides_per_group', 'design.layout.slider.arrows.overlay', 'design.layout.slider.arrows.disable'];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return ['content', 'design.layout.layout', 'design.elements.image.include', 'design.elements.title.include', 'design.elements.price.include', 'design.elements.rating.include', 'design.elements.sale_badge.include', 'design.elements.excerpt.include', 'design.elements.categories.include', 'design.elements.quantity_input.include', 'design.elements.button.include', 'design.elements.custom_areas.areas', 'design.filter_bar', 'design.elements.image.show_second_image_on_hover'];
    }
}
