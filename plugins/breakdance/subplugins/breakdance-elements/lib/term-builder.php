<?php

namespace Breakdance\LoopBuilder;

function loopTerms($loop, $filterbar, $propertiesData, $actionData)
{
    foreach($loop->get_terms() as $loopIndex => $term) {
        setCurrentTerm($term);

        $block = getBlockForLoopIndex($propertiesData, $loopIndex);
        $itemClasses = getItemClasses($term, 'term', $actionData);

        $termTag = $propertiesData['content']['repeated_block']['tag'] ?? 'article';
        $attrs = getFilterAttributesForPost($filterbar, $itemClasses);

        renderTerm(
            $actionData,
            $termTag,
            $attrs,
            $block['id']
        );
    }
}

function renderTerm($actionData, $termTag, $attrs, $blockId)
{
    $term = getCurrentTerm();

    do_action("breakdance_before_loop_item", $term, 'term', $actionData);
    do_action("breakdance_terms_loop_before_term", $actionData);
    ?>
    <<?php echo $termTag; ?> <?php echo $attrs; ?>>
    <?php
    if ($blockId) {
        echo \Breakdance\Render\renderGlobalBlock($blockId);
    } else {
        if ($_REQUEST['triggeringDocument'] ?? true) {
            echo '<div class="breakdance-empty-ssr-message">Choose a Global Block from the dropdown.</div>';
        } else {
            echo "<!-- Breakdance error: $blockId not found -->";
        }
    }
    ?>
    </<?php echo $termTag; ?>>
    <?php
    do_action("breakdance_terms_loop_after_term", $actionData);
    do_action("breakdance_after_loop_item", $term, 'term', $actionData);
}

function getTermQueryArgs($propertiesData) {
    $loadFromQuery = $propertiesData['content']['query']['load_terms_by_query'] ?? false;
    $paginationEnabled = $propertiesData['content']['pagination']['pagination'] ?? false;

    if ($loadFromQuery) {
        $code     = $propertiesData['content']['query']['term_query'];
        $taxQuery = eval($code);
    } else {
        $taxQuery = [
            'taxonomy'   => $propertiesData['content']['query']['taxonomy'] ?? 'category',
            'hide_empty' => $propertiesData['content']['query']['hide_empty'] ?? false,
            'number'     => $propertiesData['content']['query']['limit'] ?? ($paginationEnabled ? 10 : ''),
            'orderby'    => $propertiesData['content']['query']['orderby'] ?? 'name',
            'order'      => $propertiesData['content']['query']['order'] ?? 'ASC',
        ];
    }

    if ($paginationEnabled) {
        $paged = getTermPage();
        $taxQuery['offset'] = max(0, $paged - 1) * $taxQuery['number'];
    }

    return $taxQuery;
}

function getTermQuery($propertiesData)
{
    $taxQuery = getTermQueryArgs($propertiesData);
    return new \WP_Term_Query($taxQuery);
}

function countTermsInQuery($propertiesData)
{
    $taxQuery = getTermQueryArgs($propertiesData);
    // Reset number and offset to get all terms
    $taxQuery['number'] = '';
    $taxQuery['offset'] = '';
    $terms = new \WP_Term_Query($taxQuery);

    return count($terms->get_terms());
}

/*
 * @param $preview boolean
 * @return \WP_Term|null
 */
function getCurrentTerm($preview = false) {
    global $breakdance_current_term;

    if ($preview && is_null($breakdance_current_term)) {
        if (is_tax()) {

            return get_queried_object();
        } else {
            $taxonomies = get_object_taxonomies(get_post_type()) ?: ['category'];

            if (!empty($taxonomies)) {
                $first_taxonomy = $taxonomies[0];

                $term = get_terms([
                    'taxonomy' => $first_taxonomy,
                    'hide_empty' => false,
                    'number' => 1
                ]);

                if (!empty($term) && !is_wp_error($term)) {
                    return $term[0];
                }
            }
        }
        return null;
    }

    return $breakdance_current_term;
}

function setCurrentTerm($term) {
    global $breakdance_current_term;
    $breakdance_current_term = $term;
}

function resetCurrentTerm() {
    global $breakdance_current_term;
    $breakdance_current_term = null;
}

function isTermAjaxRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST' && str_contains($_POST['ssrFilePath'], 'Term_Loop_Builder');
}

function getTermPage() {
    if (isTermAjaxRequest()) {
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 0;
    } else {
        $paged = isset($_GET['term_page']) ? intval($_GET['term_page']) : 0;
    }

    return $paged;
}

function getTermsMaxPage($propertiesData) {
    $args        = getTermQueryArgs($propertiesData);
    $totalTerms  = countTermsInQuery($propertiesData);
    $perPage     = $args['number'];
    
    return $totalTerms > 0 && $perPage > 0 ? ceil($totalTerms / $perPage) : 1;
}