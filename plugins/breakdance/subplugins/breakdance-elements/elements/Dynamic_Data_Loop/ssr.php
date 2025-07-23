<?php


/*
these function are copy-paste duplicates from the Post Loop Builder element
*/

use function Breakdance\LoopBuilder\getItemClasses;
use function Breakdance\LoopBuilder\getLoopLayout;

require_once __DIR__ . "/dyn-data-loop.php";

/**
 * @var array $propertiesData
 */
$fieldSlug = $propertiesData['content']['field']['repeater_field'] ?? false;
$blockId = $propertiesData['content']['repeated_block']['global_block'] ?? -1;
$postTag = $propertiesData['content']['repeated_block']['tag'] ?? 'article';
$emptyBlockId = $propertiesData['content']['repeated_block']['advanced']['when_empty'] ?? false;

$actionData = ['propertiesData' => $propertiesData];

/** @var \Breakdance\DynamicData\RepeaterField $field */
$field = \Breakdance\DynamicData\DynamicDataController::getInstance()->getField($fieldSlug);

$layout = getLoopLayout($propertiesData) ?: 'grid';

if ($field) {
    $isOption = $field->field['is_option_page'] ?? false;
    $postId = $isOption ? 'option' : get_the_ID();

    $loopIndex = 1;

    do_action("breakdance_before_loop", $field, $actionData);

    $wrapperClass = "bde-loop bde-loop-{$layout} ee-posts bde-dynamic-repeater bde-dynamic-repeater-%%ID%% bde-dynamic-repeater-{$layout}";
    $wrapperClass = apply_filters('breakdance_loop_classes', $wrapperClass, $actionData);

    echo "<div class=\"{$wrapperClass}\">";
    $havePosts = false;
    while ($field->hasSubFields($postId)) {

        $havePosts = true;
        $actionData['loopIndex'] = $loopIndex;

        $block = getBlockForLoopIndex2($propertiesData, $loopIndex);

        if ($block['type'] === 'static') {

            // stop the loop index from incrementing for static items

            if ($field instanceof \Breakdance\DynamicData\MetaboxGroupField) {
                /* with metabox, currentIndex is incremented when calling hasSubFields above */
                $field->decrementCurrentIndexByOne();
            } elseif ($field instanceof \Breakdance\DynamicData\AcfRepeaterField && function_exists('acf_get_loop')) {
                /*
                with acf the loop is incremented when you call the has_sub_fields function,
                cuz that ultimately calls the_row
                so the following was cargo-culted in here from reading the_row in the ACF codebase...
                it does the same thing as the_row, but in reverse (they call $i++, we call $i--)
                */
                $acfI = acf_get_loop('active', 'i');
                $acfI--;
                acf_update_loop('active', 'i', $acfI);
            }
        }

        $itemClasses = getItemClasses($field, 'field', $actionData);

        do_action("breakdance_before_loop_item", $field, 'field', $actionData);

        echo '<' . $postTag . ' class="' . $itemClasses .'">';
        echo \Breakdance\Render\renderGlobalBlock($block['id'], "{$postId}-{$loopIndex}");
        echo '</' . $postTag . '>';
        $loopIndex++;

        do_action("breakdance_after_loop_item", $field, 'field', $actionData);
    }

    if (!$havePosts && $emptyBlockId) {
        echo \Breakdance\Render\renderGlobalBlock($emptyBlockId);
    }

    // similar to render_static_item_at_end_of_loop_if_present() for post loops:
    $staticItemAtEnd = getBlockForLoopIndex2($propertiesData, $loopIndex);

    if ($staticItemAtEnd['type'] === 'static') {
        do_action("breakdance_before_loop_item", $field, 'field', $actionData);
        echo '<' . $postTag . ' class="' . $itemClasses .'">';
        echo \Breakdance\Render\renderGlobalBlock($staticItemAtEnd['id'], "{$postId}-{$loopIndex}");
        echo '</' . $postTag . '>';
        do_action("breakdance_after_loop_item", $field, 'field', $actionData);
    }

    echo '</div>';

    do_action("breakdance_after_loop", $field, $actionData);
}
