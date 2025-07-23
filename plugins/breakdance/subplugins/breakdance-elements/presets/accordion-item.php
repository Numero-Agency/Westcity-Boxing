<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\Elements\PresetSections\PresetSectionsController::getInstance()->register(
    "EssentialElements\\accordion-item",
    c(
        "item",
        "Item",
        [c(
        "style",
        "Style",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'bordered', 'text' => 'Bordered'], ['text' => 'Pills', 'value' => 'pills']]],
        false,
        false,
        [],
      ), c(
        "icon",
        "Icon",
        [c(
        "position",
        "Position",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['text' => 'Right', 'value' => 'right']]],
        false,
        false,
        [],
      ), c(
        "icon",
        "Icon",
        [],
        ['type' => 'icon', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "active_icon",
        "Active Icon",
        [],
        ['type' => 'icon', 'layout' => 'vertical'],
        false,
        false,
        [],
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['min' => 4, 'max' => 100, 'step' => 1], 'unitOptions' => ['types' => ['px']]],
        true,
        false,
        [],
      ), c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "active_color",
        "Active Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
      ), c(
        "bordered",
        "Bordered",
        [c(
        "border_color",
        "Border Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "border_width",
        "Border Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "border_radius",
        "Border Radius",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "bottom_only",
        "Bottom Only",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout'], 'condition' => ['path' => 'design.item.style', 'operand' => 'equals', 'value' => 'bordered']],
        false,
        false,
        [],
      ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
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
       ['condition' => ['path' => 'design.item.style', 'operand' => 'equals', 'value' => 'pills'], 'type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography_with_align_with_hoverable_color",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), c(
        "active",
        "Active",
        [c(
        "text_color",
        "Text Color",
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
        "transition_duration",
        "Transition Duration",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['s', 'ms']]],
        false,
        false,
        [],
      )],
        ['type' => 'section', 'sectionOptions' => ['preset' => ['slug' => 'EssentialElements\\accordion-item']]],
        false,
        false,
        [],
      ),
    true,
    null
);

