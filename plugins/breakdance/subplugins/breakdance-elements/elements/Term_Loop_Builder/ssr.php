<?php

/**
 * @var array $propertiesData
 * @var boolean $renderOnlyIndividualPosts this one is used for "load more" ajax and comes from pagination.php
 */

use function Breakdance\EssentialElements\Lib\PostsPagination\getPostsPaginationFromProperties;
use function Breakdance\LoopBuilder\getLoopLayout;
use function Breakdance\LoopBuilder\getTermQuery;
use function Breakdance\LoopBuilder\getTermsMaxPage;
use function Breakdance\LoopBuilder\loopTerms;
use function Breakdance\LoopBuilder\outputAfterTheLoop;
use function Breakdance\LoopBuilder\outputBeforeTheLoop;

use function Breakdance\LoopBuilder\resetCurrentTerm;
use function Breakdance\LoopBuilder\setupIsotopeFilterBar;
use function Breakdance\Util\getDirectoryPathRelativeToPluginFolder;

$renderOnlyIndividualPosts = $renderOnlyIndividualPosts ?? false;

$paginationEnabled = $propertiesData['content']['pagination']['pagination'] ?? false;
$actionData = ['propertiesData' => $propertiesData];

$loop = getTermQuery($propertiesData);

$layout = getLoopLayout($propertiesData);

$emptyBlockId = $propertiesData['content']['repeated_block']['advanced']['when_empty'] ?? false;

$filterbar = setupIsotopeFilterBar([
    'settings' => $propertiesData['content']['filter_bar'] ?? [],
    'design' => $propertiesData['design']['filter_bar'] ?? [],
    'query' => $loop
]);

do_action("breakdance_before_loop", $loop, $actionData);
do_action("breakdance_terms_loop_before_loop", $actionData);

outputBeforeTheLoop($renderOnlyIndividualPosts, $filterbar, $layout, $actionData);

loopTerms($loop, $filterbar, $propertiesData, $actionData);

outputAfterTheLoop($renderOnlyIndividualPosts, $filterbar, $propertiesData);

do_action("breakdance_terms_loop_after_loop", $actionData);
do_action("breakdance_after_loop", $loop, $actionData);

if ($paginationEnabled) {
    $maxNumPages = getTermsMaxPage($propertiesData);

    getPostsPaginationFromProperties(
        $propertiesData,
        $maxNumPages,
        $layout,
        getDirectoryPathRelativeToPluginFolder(__FILE__),
        "term"
    );
}
else if ($emptyBlockId && !$renderOnlyIndividualPosts) {
    echo \Breakdance\Render\renderGlobalBlock($emptyBlockId);
}

resetCurrentTerm();

do_action("breakdance_terms_loop_after_pagination", $actionData);