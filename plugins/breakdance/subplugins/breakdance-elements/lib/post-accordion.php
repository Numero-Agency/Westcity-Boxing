<?php

namespace Breakdance\LoopBuilder\Accordion;

use Breakdance\DynamicData\AcfFlexibleContent;
use Breakdance\DynamicData\RepeaterField;
use function Breakdance\DynamicData\renderDynamicShortcodes;
use function Breakdance\LoopBuilder\getLoopLayout;

function isAccordionLayoutActive($propertiesData)
{
    $layout = getLoopLayout($propertiesData);

    // Don't show accordions if filter bar is enabled
    $isFilterBarEnabled = $propertiesData['content']['filter_bar']['enable'] ?? false;
    return $layout === 'accordion' && !$isFilterBarEnabled;
}

function containerClasses($classes, $data)
{
  if (!isAccordionLayoutActive($data['propertiesData'])) {
      return $classes;
  }

  $design = $data['propertiesData']['design']['list']['accordion'] ?? [];
  $style = $design['item']['style'] ?? '';
  $bottomOnly = $design['item']['bordered']['bottom_only'] ?? false;

  if ($style === 'bordered' && !$bottomOnly) {
    $classes .= ' bde-accordion--bordered';
  } elseif ($bottomOnly) {
    $classes .= ' bde-accordion--bordered-bottom';
  } elseif ($style === 'pills') {
    $classes .= ' bde-accordion--pills';
  }

  return $classes;
}

function beforeLoopItem($object, $objectType, $data)
{
    if (!isAccordionLayoutActive($data['propertiesData'])) {
      return;
    }

    if ($object instanceof \WP_Post) {
        $title = get_the_title($object);
        $id = $object->ID;
    } elseif ($object instanceof \WP_Term) {
        $title = $object->name;
        $id = $object->term_id;
    } elseif ($object instanceof RepeaterField) {
        // We use item_title_dynamic_meta instead of item_title, because item_title is already rendered outside the loop and ends up an empty string.
        $title = renderDynamicShortcodes(
            $data['propertiesData']['content']['field']['item_title_dynamic_meta']['shortcode']
                ?? $data['propertiesData']['content']['field']['item_title']
                ?? 'Item ' . $data['loopIndex']
        );

        $id = $object->field['key'] . '_' . $data['loopIndex'];
    }
    ?>
    <div class="bde-accordion__content-wrapper">
        <h3 class="bde-accordion__title-tag">
            <button type="button" id="accordion-button-<?php echo $id; ?>" aria-controls="accordion-panel-<?php echo $id; ?>" aria-expanded="false" class="bde-accordion__button">
                <span class="bde-accordion__title"><?php echo $title; ?></span>
                <span class="bde-accordion__icon bde-accordion__icon--default"></span>
                <span class="bde-accordion__icon bde-accordion__icon--active"></span>
            </button>
        </h3>

        <div role="region" id="accordion-panel-<?php echo $id; ?>" aria-labelledby="accordion-button-<?php echo $id; ?>" class="bde-accordion__panel">
            <div class="bde-accordion__panel-content">
    <?php
}

function afterLoopItem($object, $objectType, $data)
{
    if (!isAccordionLayoutActive($data['propertiesData'])) {
        return;
    }
    ?>
            </div>
        </div>
    </div>
    <?php
}

add_action('breakdance_loop_classes', '\Breakdance\LoopBuilder\Accordion\containerClasses', 10, 4);
add_action('breakdance_before_loop_item', '\Breakdance\LoopBuilder\Accordion\beforeLoopItem', 10, 4);
add_action('breakdance_after_loop_item', '\Breakdance\LoopBuilder\Accordion\afterLoopItem', 10, 4);