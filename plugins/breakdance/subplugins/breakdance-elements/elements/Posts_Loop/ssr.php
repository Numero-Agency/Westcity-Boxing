<?php

/**
 * @var array $propertiesData
 * @var boolean $renderOnlyIndividualPosts this one is used for "load more" ajax and comes from pagination.php
 */

use function Breakdance\LoopBuilder\getLoopLayout;
use function Breakdance\LoopBuilder\getWpQuery;
use function Breakdance\LoopBuilder\loopBlockPosts;
use function Breakdance\LoopBuilder\outputAfterTheLoop;
use function Breakdance\LoopBuilder\outputBeforeTheLoop;
use function Breakdance\LoopBuilder\setupIsotopeFilterBar;
use function Breakdance\Util\getDirectoryPathRelativeToPluginFolder;
use function Breakdance\Util\WP\isAnyArchive;
use function Breakdance\EssentialElements\Lib\PostsPagination\getPostsPaginationFromProperties;

$renderOnlyIndividualPosts = $renderOnlyIndividualPosts ?? false;
$emptyBlockId = $propertiesData['content']['repeated_block']['advanced']['when_empty'] ?? false;

showWarningInBuilderForImproperUseOfPaginationAndCustomQueriesOnArchives(
    $propertiesData['content']['query']['query'] ?? false,
    $propertiesData['content']['pagination']['pagination'] ?? false,
    isAnyArchive()
);

$actionData = ['propertiesData' => $propertiesData];

global $post;
$initialGlobalPost = $post;

$loop = getWpQuery($propertiesData);

if ($loop->have_posts()) {
    $layout = getLoopLayout($propertiesData);
    $filterbar = setupIsotopeFilterBar([
        'settings' => $propertiesData['content']['filter_bar'] ?? [],
        'design' => $propertiesData['design']['filter_bar'] ?? [],
        'query' => $loop
    ]);

    do_action("breakdance_before_loop", $loop, $actionData);
    do_action("breakdance_posts_loop_before_loop", $actionData);

    outputBeforeTheLoop($renderOnlyIndividualPosts, $filterbar, $layout, $actionData);

    loopBlockPosts($loop, $filterbar, $propertiesData, $actionData);

    outputAfterTheLoop($renderOnlyIndividualPosts, $filterbar, $propertiesData);

    do_action("breakdance_posts_loop_after_loop", $actionData);
    do_action("breakdance_after_loop", $loop, $actionData);

    getPostsPaginationFromProperties(
        $propertiesData,
        $loop->max_num_pages,
        $layout,
        getDirectoryPathRelativeToPluginFolder(__FILE__)
    );

    do_action("breakdance_posts_loop_after_pagination", $actionData);

    wp_reset_postdata();

    // If these IDs don't match after resetting the postdata,
    // this is a nested post loop, so we need to set the
    // post data back to the original value
    $currentPostId = $post instanceof \WP_Post ? $post->ID : $post;
    $initialPostId = $initialGlobalPost instanceof \WP_Post ? $initialGlobalPost->ID : $initialGlobalPost;
    if ($currentPostId && $currentPostId !== $initialPostId) {
        $GLOBALS['post'] = $initialGlobalPost;
    }

} elseif ($emptyBlockId && !$renderOnlyIndividualPosts) {
    echo \Breakdance\Render\renderGlobalBlock($emptyBlockId);
}