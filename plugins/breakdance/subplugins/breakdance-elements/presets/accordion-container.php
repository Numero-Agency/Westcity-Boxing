<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\Elements\PresetSections\PresetSectionsController::getInstance()->register(
    "EssentialElements\\accordion-container",
    c(
        "container",
        "Container",
        [c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline', 'colorOptions' => ['type' => 'solidAndGradient']],
        false,
        false,
        [],
      ), c(
        "width",
        "Width",
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
     )],
        ['type' => 'section', 'sectionOptions' => ['preset' => ['slug' => 'EssentialElements\\accordion-container']]],
        false,
        false,
        [],
      ),
    true,
    null
);

