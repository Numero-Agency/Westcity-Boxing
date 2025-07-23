<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\ContentReveal",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ContentReveal extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ExpandIcon';
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
        return 'Content Reveal';
    }

    static function className()
    {
        return 'bde-content-reveal';
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
        return ['content' => ['content' => ['hide_by' => null]], 'design' => ['overlay' => ['gradient' => ['points' => [['left' => 0, 'red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 1], ['left' => 96.41025641025641, 'red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 0]], 'type' => 'linear', 'degree' => 0, 'svgValue' => '<linearGradient x1="0.5" y1="1" x2="0.5" y2="0" id="%%GRADIENTID%%"><stop stop-opacity="1" stop-color="#ffffff" offset="0"></stop><stop stop-opacity="0" stop-color="#ffffff" offset="0.9641025641025641"></stop></linearGradient>', 'value' => 'linear-gradient(0deg,rgba(255, 255, 255, 1) 0%,rgba(255, 255, 255, 0) 96.41025641025641%)']]]];
    }

    static function defaultChildren()
    {
        return [['slug' => 'EssentialElements\RichText', 'defaultProperties' => ['content' => ['content' => ['text' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas pulvinar ex ut dolor porttitor, ut ultricies leo porttitor. Etiam ac convallis nisl. Curabitur dapibus nec nunc nec iaculis. Quisque vitae ullamcorper risus. Ut rhoncus convallis nisi eget accumsan. Integer vitae lectus iaculis, volutpat diam eu, faucibus enim. Maecenas augue purus, hendrerit ac fermentum a, mollis ut nisl. Cras egestas lobortis dolor. Quisque nunc felis, bibendum quis risus ac, facilisis ultrices enim.</p><p>Maecenas mattis dolor est, in hendrerit libero ornare eget. Nam eget tortor et diam laoreet aliquam id ut dui. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aenean convallis molestie massa, id luctus ex scelerisque scelerisque. Integer bibendum ullamcorper mi. Sed congue, nibh et tincidunt vulputate, turpis risus maximus mauris, eget commodo nulla risus eu ante. Nulla at dolor aliquam quam accumsan venenatis. Aliquam hendrerit accumsan convallis. Ut consequat urna id lorem vestibulum blandit. Fusce dui metus, molestie vel mattis vitae, tempus tempor urna. Sed lacinia ligula nec enim tempus, at porta urna varius. Nam eget lobortis diam. Etiam ultricies libero et libero consectetur, at imperdiet orci tristique. Morbi pulvinar nunc ut molestie ornare. Vestibulum finibus pulvinar ligula id auctor.</p><p>Duis ullamcorper ornare metus at condimentum. Sed auctor, ipsum et sagittis dapibus, augue urna pretium metus, vitae tempor sem quam sit amet justo. Cras hendrerit condimentum odio, ac rhoncus magna mollis ac. Vestibulum rhoncus placerat risus vel lobortis. Nullam fringilla augue eu mauris ultrices, in imperdiet elit tincidunt. Maecenas tincidunt id mauris id rutrum. Duis imperdiet nisi est, in scelerisque diam sagittis et. Etiam rutrum nulla ut augue cursus finibus. Quisque posuere auctor sem, sed volutpat odio ullamcorper quis. Proin nec orci condimentum, ultrices turpis ac, fermentum metus. Ut tempor felis risus, efficitur ultricies augue mattis finibus. Nulla aliquet tempor velit. Proin eget ligula bibendum, finibus sem eu, congue ligula. Ut magna diam, feugiat ut leo et, gravida accumsan ante.</p><p>Nulla lacinia tempor accumsan. Maecenas volutpat quam eu massa accumsan ultrices. Praesent maximus lorem in augue pretium aliquet. Nam mauris massa, fermentum in tincidunt quis, gravida eget dolor. Vestibulum in tristique lorem. Vivamus non malesuada dolor. Morbi vulputate bibendum aliquet. Etiam sed varius ipsum, vitae tincidunt massa.</p>']], 'design' => ['typography' => ['default' => ['typography' => ['custom' => ['customTypography' => ['advanced' => ['lineHeight' => null]]]]]]]], 'children' => []]];
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [c(
        "content",
        "Content",
        [c(
        "transition",
        "Transition",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['ms', 's']]],
        false,
        false,
        [],
      ), c(
        "max_width",
        "Max Width",
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
      ), c(
        "overlay",
        "Overlay",
        [c(
        "vertical_alignment",
        "Alignment",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'flex-start', 'text' => 'Left', 'icon' => 'AlignLeftIcon'], ['text' => 'Center', 'value' => 'center', 'icon' => 'AlignCenterIcon'], ['text' => 'Right', 'value' => 'flex-end', 'icon' => 'AlignRightIcon']]],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\AtomV1ButtonDesign",
      "Button",
      "button",
       ['type' => 'popout']
     ), c(
        "gradient",
        "Gradient",
        [],
        ['type' => 'color', 'layout' => 'vertical', 'colorOptions' => ['type' => 'gradientOnly']],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), c(
        "spacing",
        "Spacing",
        [getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Container",
      "container",
       ['type' => 'popout']
     ), c(
        "before_button",
        "Before Button",
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
      )];
    }

    static function contentControls()
    {
        return [c(
        "content",
        "Content",
        [c(
        "hide_by",
        "Hide By",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['text' => 'Height', 'value' => 'height'], ['value' => 'lines', 'text' => 'Lines']]],
        false,
        false,
        [],
      ), c(
        "max_height",
        "Max Height",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => 'content.content.hide_by', 'operand' => 'equals', 'value' => 'height']], [['path' => 'content.content.hide_by', 'operand' => 'is not set', 'value' => '']]]],
        false,
        false,
        [],
      ), c(
        "max_lines",
        "Max Lines",
        [],
        ['type' => 'number', 'layout' => 'inline', 'condition' => [[['path' => 'content.content.hide_by', 'operand' => 'equals', 'value' => 'lines']]], 'rangeOptions' => ['min' => 1, 'max' => 50, 'step' => 1]],
        false,
        false,
        [],
      ), c(
        "scroll_to_top_when_closed",
        "Scroll To Top When Closed",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "labels",
        "Labels",
        [c(
        "reveal_label",
        "Reveal Label",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "hide_label",
        "Hide Label",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
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
      )];
    }

    static function settingsControls()
    {
        return [];
    }

    static function dependencies()
    {
        return ['0' =>  ['title' => 'Content Reveal','scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-content-reveal@1/breakdance-content-reveal.js'],],'1' =>  ['title' => 'Content Reveal - Frontend Init','builderCondition' => 'return false;','frontendCondition' => 'return true;','inlineScripts' => ['new BreakdanceContentReveal(\'%%SELECTOR%%\', {
  hideBy: {{ content.content.hide_by|json_encode }},
  maxHeight: {{ content.content.max_height|json_encode }},
  maxLines: {{ content.content.max_lines|json_encode }},
  scrollToTop: {{ content.content.scroll_to_top_when_closed|json_encode }}
});'],],];
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
        return [

'onMountedElement' => [['script' => 'if (!window.bdeContentRevealInstances) window.bdeContentRevealInstances = {};

if (window.bdeContentRevealInstances && window.bdeContentRevealInstances[%%ID%%]) {
  window.bdeContentRevealInstances[%%ID%%].destroy();
}

window.bdeContentRevealInstances[%%ID%%] = new BreakdanceContentReveal(\'%%SELECTOR%%\', {
  hideBy: {{ content.content.hide_by|json_encode }},
  maxHeight: {{ content.content.max_height|json_encode }},
  maxLines: {{ content.content.max_lines|json_encode }},
  scrollToTop: {{ content.content.scroll_to_top_when_closed|json_encode }}
});
',
],],

'onPropertyChange' => [['script' => 'if (window.bdeContentRevealInstances && window.bdeContentRevealInstances[%%ID%%]) {
  window.bdeContentRevealInstances[%%ID%%].update({
    hideBy: {{ content.content.hide_by|json_encode }},
    maxHeight: {{ content.content.max_height|json_encode }},
    maxLines: {{ content.content.max_lines|json_encode }},
    scrollToTop: {{ content.content.scroll_to_top_when_closed|json_encode }}
  });
}
',
],],

'onMovedElement' => [['script' => 'if (window.bdeContentRevealInstances && window.bdeContentRevealInstances[%%ID%%]) {
  window.bdeContentRevealInstances[%%ID%%].update();
}
',
],],

'onBeforeDeletingElement' => [['script' => 'if (window.bdeContentRevealInstances && window.bdeContentRevealInstances[%%ID%%]) {
  window.bdeContentRevealInstances[%%ID%%].destroy();
}
',
],],];
    }

    static function nestingRule()
    {
        return ["type" => "container",   ];
    }

    static function spacingBars()
    {
        return [['cssProperty' => 'margin-top', 'location' => 'outside-top', 'affectedPropertyPath' => 'design.spacing.%%BREAKPOINT%%.container.margin_top'], ['cssProperty' => 'margin-bottom', 'location' => 'outside-bottom', 'affectedPropertyPath' => 'design.spacing.%%BREAKPOINT%%.container.margin_bottom']];
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
        return 20;
    }

    static function dynamicPropertyPaths()
    {
        return [['accepts' => 'string', 'path' => 'content.content.text'], ['accepts' => 'string', 'path' => 'content.content.link.url']];
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
        return ['design.button.button.custom.size.full_width_at', 'design.button.button.styles', 'design.overlay.button.custom.size.full_width_at', 'design.overlay.button.styles'];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return false;
    }
}
