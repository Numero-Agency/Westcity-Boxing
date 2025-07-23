<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\Elements\PresetSections\PresetSectionsController::getInstance()->register(
    "EssentialElements\\posts-list-design",
    c(
        "list",
        "List",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "layout",
        "Layout",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'list', 'text' => 'List'], ['text' => 'Grid', 'value' => 'grid'], ['text' => 'Slider', 'value' => 'slider'], ['text' => 'Accordion', 'value' => 'accordion'], ['text' => 'Tabs', 'value' => 'tabs']]],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\AtomV1SwiperSettings",
      "Slider",
      "slider",
       ['condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'equals', 'value' => 'slider']]], 'type' => 'popout']
     ), c(
        "accordion",
        "Accordion",
        [c(
        "accordion",
        "Accordion",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "first_item_opened",
        "First Item Opened",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\accordion-item",
      "Item",
      "item",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'equals', 'value' => 'accordion']]]],
        false,
        false,
        [],
      ), c(
        "tabs",
        "Tabs",
        [c(
        "active_tab",
        "Active Tab",
        [],
        ['type' => 'number', 'layout' => 'inline', 'dropdownOptions' => ['populate' => ['path' => 'content.content.tabs.tabs', 'text' => 'title', 'value' => 'title']]],
        false,
        false,
        [],
      ), c(
        "open_on_hover",
        "Open On Hover",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\tabs_design",
      "Styles",
      "styles",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'equals', 'value' => 'tabs']]]],
        false,
        false,
        [],
      ), c(
        "items_per_row",
        "Items Per Row",
        [],
        ['type' => 'number', 'layout' => 'inline', 'condition' => ['path' => '%%CURRENTPATH%%.layout', 'operand' => 'equals', 'value' => 'grid']],
        true,
        false,
        [],
      ), c(
        "one_item_at",
        "One Item At",
        [],
        ['type' => 'breakpoint_dropdown', 'layout' => 'inline', 'condition' => ['path' => '%%CURRENTPATH%%.layout', 'operand' => 'equals', 'value' => 'grid']],
        false,
        false,
        [],
      ), c(
        "space_between_items",
        "Space Between Items",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'condition' => [[['path' => '%%CURRENTPATH%%.layout', 'operand' => 'is none of', 'value' => ['slider', 'accordion', 'tabs']]]], 'rangeOptions' => ['min' => 0, 'max' => 100, 'step' => 1]],
        true,
        false,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout', 'preset' => ['slug' => 'EssentialElements\\posts-list-design']]],
        false,
        false,
        [],
      ),
    true,
    null
);

