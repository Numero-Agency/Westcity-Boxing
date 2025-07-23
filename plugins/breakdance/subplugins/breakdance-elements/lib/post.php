<?php

namespace Breakdance\LoopBuilder;

function getItemClasses($object, $objectType, $actionData)
{
    $itemClasses = 'bde-loop-item ee-post';

    if ($objectType === 'field') {
        if ($object->field['type'] === 'flexible_content') {
            $itemClasses .= ' bde-flexible-content-item';
        } elseif ($object->field['type'] === 'repeater') {
            $itemClasses .= ' bde-dynamic-repeater-item';
        }
    }

    return apply_filters('breakdance_loop_item_classes', $itemClasses, $object, $objectType, $actionData);
}

function getLoopLayout($propertiesData)
{
    return $propertiesData['design']['list']['layout'] ?? '';
}

function outputBeforeTheLoop($renderOnlyIndividualPosts, $filterbar, $layout, $actionData = [])
{
    $wrapperClass = "bde-loop bde-loop-{$layout} ee-posts ee-posts-{$layout}";

    if (!$renderOnlyIndividualPosts) {
        renderIsotoperFilterBar($filterbar);

        if ($filterbar['enable'] ?? false) {
            $wrapperClass .= ' bde-loop-isotope ee-posts-isotope';
        }

        $wrapperClass = apply_filters('breakdance_loop_classes', $wrapperClass, $actionData);

        echo "<div class=\"{$wrapperClass}\">";
    }
}

function outputAfterTheLoop($renderOnlyIndividualPosts, $filterbar, $actionData)
{
    if (!$renderOnlyIndividualPosts) {
        renderIsotopeFooter($filterbar);

        echo "</div>"; // close wrapper
    }
}

function renderStaticItemAtEndOfLoopIfPresent($propertiesData, $loopIndex, $filterbar, $actionData)
{

    $staticItemAtEnd = getBlockForLoopIndex($propertiesData, $loopIndex + 1);

    if ($staticItemAtEnd['type'] === 'static') {
        $post = get_post();
        $itemClasses = getItemClasses($post, 'post', $actionData);

        $postTag = $propertiesData['content']['repeated_block']['tag'] ?? 'article';
        $attrs = getFilterAttributesForPost($filterbar, $itemClasses);

        renderBlockPost(
            $actionData,
            $postTag,
            $attrs,
            $staticItemAtEnd['id']
        );
    }
}

function getBlockForLoopIndex($propertiesData, $loopIndex)
{
    if ($propertiesData['content']['repeated_block']['advanced']['static_items'] ?? false) {
        $staticItemBlock = getBlockIdForLoopIndex($propertiesData['content']['repeated_block']['advanced']['static_items'], $loopIndex);

        if ($staticItemBlock) {
            return [
                'type' => 'static',
                'id' => $staticItemBlock,
            ];
        }
    }

    if ($propertiesData['content']['repeated_block']['advanced']['alternates'] ?? false) {
        $alternateBlock = getBlockIdForLoopIndex($propertiesData['content']['repeated_block']['advanced']['alternates'], $loopIndex);

        if ($alternateBlock) {
            return [
                'type' => 'alternate',
                'id' => $alternateBlock,
            ];
        }
    }

    $blockId = $propertiesData['content']['repeated_block']['global_block'] ?? false;

    return [
        'type' => 'default',
        'id' => $blockId,
    ];
}

/**
 * @param array{repeat?:boolean,global_block?:int,position?:int,frequency?:int}[]
 * @param int $loopIndex
 * @return false|int
 */
function getBlockIdForLoopIndex($blocksPropertiesData, $loopIndex)
{
    $blockId = false;

    foreach ($blocksPropertiesData as $alternate) {

        $position = $alternate['position'] ?? false;
        $global_block = $alternate['global_block'] ?? false;
        $frequency = $alternate['frequency'] ?? $position;

        if ($frequency <= 1) {
            /*
            frequency of 1 or less makes no sense, and will timeout the server when using static item position because it'll cause
            the posts loop to never finish since it'll just keep outputting static items at every position - i.e. an infinite loop
            */
            $frequency = 10000;
        }

        if ($position === $loopIndex) {
            $blockId = $global_block;
            break;
        }

        if ($alternate['repeat'] && $loopIndex > $position) {

            $distanceFromStartingPosition = $loopIndex - $position;

            if ($distanceFromStartingPosition % $frequency === 0) {
                $blockId = $global_block;
                break;
            }
        }
    }

    return $blockId ?: false;
}
