<?php
/**
 * @var array $propertiesData
 * @var int|null $repeaterItemNodeId
 */

/*
these function are copy-paste duplicates from the Post Loop Builder element
*/

use function Breakdance\LoopBuilder\getItemClasses;
use function Breakdance\LoopBuilder\getLoopLayout;

$blocks = $propertiesData['content']['blocks']['blocks'] ?? [];
$blocksMapping = array_reduce($blocks, function ($carry, $block) {
    if (isset($block['layout'], $block['block'])) {
        $carry[$block['layout']][] = $block['block'];
    }
    return $carry;
}, []);

$fieldName = $propertiesData['content']['field']['flexible_content_field'] ?? '';
$fieldSlug = str_replace('acf_flexible_content_', '', $fieldName);
$postTag = $propertiesData['content']['repeated_block']['tag'] ?? 'article';
$emptyBlockId = $propertiesData['content']['field']['advanced']['when_empty'] ?? false;

$actionData = ['propertiesData' => $propertiesData];

/** @var \Breakdance\DynamicData\AcfFlexibleContent $field */
$field = \Breakdance\DynamicData\DynamicDataController::getInstance()->getField($fieldName);

$layout = getLoopLayout($propertiesData) ?: 'grid';

if (have_rows($fieldSlug)) :
    $isOption = $field->field['is_option_page'] ?? false;
    $postId = $isOption ? 'option' : get_the_ID();

    $loopIndex = 1;

    do_action("breakdance_before_loop", $field, $actionData);

    $wrapperClass = "bde-loop bde-loop-{$layout} ee-posts";
    $wrapperClass = apply_filters('breakdance_loop_classes', $wrapperClass, $actionData);

    echo "<div class=\"{$wrapperClass}\">";

    while (have_rows($fieldSlug)) : the_row();
        $layout = get_row_layout();
        $blocks = $blocksMapping[$layout];
        $actionData['loopIndex'] = $loopIndex;
        $itemClasses = getItemClasses($field, 'field', $actionData);

        do_action("breakdance_before_loop_item", $field, 'field', $actionData);

        echo '<' . $postTag . ' class="' . $itemClasses .'">';
            if ($blocks) {
                foreach ($blocks as $blockId):
                    echo \Breakdance\Render\renderGlobalBlock($blockId, $repeaterItemNodeId);
                endforeach;
            } else {
                if ($_REQUEST['triggeringDocument'] ?? true) {
                    echo '<div class="breakdance-empty-ssr-message">Choose a Global Block from the dropdown for layout ' . $layout . '.</div>';
                } else {
                    echo "<!-- Breakdance error: $blockId not found for layout $layout -->";
                }
            }
        echo '</' . $postTag . '>';
        $loopIndex++;

        do_action("breakdance_after_loop_item", $field, 'field', $actionData);
    endwhile;

    echo '</div>';

    do_action("breakdance_after_loop", $field, $actionData);
elseif ($emptyBlockId):
    echo \Breakdance\Render\renderGlobalBlock($emptyBlockId);
endif;