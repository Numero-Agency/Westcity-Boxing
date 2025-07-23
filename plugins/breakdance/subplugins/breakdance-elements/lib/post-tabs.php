<?php

namespace Breakdance\LoopBuilder\Tabs;

use Breakdance\DynamicData\RepeaterField;
use function Breakdance\DynamicData\renderDynamicShortcodes;
use function Breakdance\LoopBuilder\getLoopLayout;

function isTabsLayoutActive($propertiesData)
{
    $layout = getLoopLayout($propertiesData);

    // Don't show tabs if filter bar is enabled
    $isFilterBarEnabled = $propertiesData['content']['filter_bar']['enable'] ?? false;
    return $layout === 'tabs' && !$isFilterBarEnabled;
}

function containerClasses($classes, $data)
{
    if (!isTabsLayoutActive($data['propertiesData'])) {
        return $classes;
    }

    $classes .= ' bde-tabs-content-container';

    return $classes;
}

/**
 * @param \WP_Query|\WP_Term_Query $loop
 * @param array{propertiesData: mixed} $data
 */
function beforeLoop($loop, $data)
{
    if (!isTabsLayoutActive($data['propertiesData'])) {
        return;
    }

    $tabs = [];
    $design = $data['propertiesData']['design']['list']['tabs']['styles'] ?? [];

    if ($loop instanceof \WP_Query) {
        if ($loop->have_posts()) {
            while ($loop->have_posts()) {
                $loop->the_post();
                $tabs[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title()
                ];
            }
        }

        wp_reset_postdata();
    } elseif ($loop instanceof \WP_Term_Query) {
        if ($loop->get_terms()) {
            foreach ($loop->get_terms() as $term) {
                $tabs[] = [
                    'id' => $term->term_id,
                    'title' => $term->name
                ];
            }
        }
    } elseif ($loop instanceof RepeaterField) {
        $loopIndex = 1;
        $isOption = $field->field['is_option_page'] ?? false;
        $postId = $isOption ? 'option' : get_the_ID();

        while ($loop->hasSubFields($postId)) {
            $title = renderDynamicShortcodes(
                $data['propertiesData']['content']['field']['item_title_dynamic_meta']['shortcode']
                  ?? $data['propertiesData']['content']['field']['item_title']
                  ?? 'Item ' . $loopIndex
            );

            $tabs[] = [
                'id' => $loopIndex,
                'title' => $title
            ];

            $loopIndex++;
        }
    }

    echo '<div class="bde-tabs">';
    echo \Breakdance\Elements\AtomV1Tabs\render('%%ID%%', $tabs, $design, []);
}

function afterLoop($loop, $data)
{
    if (!isTabsLayoutActive($data['propertiesData'])) {
        return;
    }

    echo '</div>';
}

/**
 * @param \WP_Post|\WP_Term $object
 * @param string $objectType
 * @param array{propertiesData: mixed} $data
 */
function beforeLoopItem($object, $objectType, $data)
{
    if (!isTabsLayoutActive($data['propertiesData'])) {
        return;
    }

    $id = $object instanceof \WP_Post ? $object->ID : $object->term_id;
    ?>
    <div tabindex="0" role="tabpanel" class="bde-tabs__panel js-panel" id="tab-panel-%%ID%%-<?php echo $id; ?>" aria-labelledby="tab-%%ID%%-<?php echo $id; ?>">
        <div class="bde-tabs__panel-content">
    <?php
}

function afterLoopItem($object, $objectType, $data)
{
    if (!isTabsLayoutActive($data['propertiesData'])) {
        return;
    }
    ?>
        </div>
    </div>
    <?php
}

add_action('breakdance_loop_classes', '\Breakdance\LoopBuilder\Tabs\containerClasses', 10, 4);
add_action('breakdance_before_loop', '\Breakdance\LoopBuilder\Tabs\beforeLoop', 10, 2);
add_action('breakdance_after_loop', '\Breakdance\LoopBuilder\Tabs\afterLoop', 10, 2);
add_action('breakdance_before_loop_item', '\Breakdance\LoopBuilder\Tabs\beforeLoopItem', 10, 4);
add_action('breakdance_after_loop_item', '\Breakdance\LoopBuilder\Tabs\afterLoopItem', 10, 4);