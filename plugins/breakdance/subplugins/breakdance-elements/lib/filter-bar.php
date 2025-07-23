<?php

namespace Breakdance\LoopBuilder;

use Breakdance\Render\Twig;

/**
 * @psalm-ignore-file
 * Adding types to Properties Data is pure madness
 */

/**
 * @psalm-type FilterBarData = array {
 *   enable: boolean,
 *   type: string,
 *   all_filter: boolean,
 *   all_label: string,
 *   sort_by: string,
 *   hide_uncategorized: boolean,
 * }
 *
 * @psalm-type FilterBar = FilterBarData & array {
 *   design: array,
 *   terms: array{id: int, title: string}[],
 *   activeTerm: int,
 * }
 */

/**
 * @param array{ settings: FilterBarData, design: array, query: \WP_Query } $data
 * @return FilterBar
 */
function setupIsotopeFilterBar($data) {
    $rawSettings = array_filter((array) ($data['settings'] ?? []));
    /** @var string $defaultType */
    $defaultType = $data['defaultType'] ?? 'category';
    /** @var string $defaultAll */
    $defaultAll = $data['defaultAll'] ?? __('All', 'breakdance');

    $settings = array_merge([
        'enable' => false,
        'type' => $defaultType,
        'all_filter' => false,
        'all_label' => $defaultAll,
        'sort_by' => false,
        'hide_uncategorized' => false
    ], $rawSettings);

    $terms = [];
    $activeTerm = 0;

    if ($settings['enable']) {
        $terms = getTermsOnCurrentPage($data['query'], $settings);
        $activeTerm = \Breakdance\LoopBuilder\getActiveTerm($settings['all_filter'], $terms);
    }

    return array_merge($settings, [
        'design' => $data['design'],
        'terms' => $terms,
        'activeTerm' => $activeTerm
    ]);
}

/**
 * @param FilterBar $filterbar
 * @return void
 */
function renderIsotoperFilterBar($filterbar) {
    if (!$filterbar['enable']) return;
?>
    <div class="bde-isotope-filter-bar">
        <div class="bde-tabs">
            <?php
            echo \Breakdance\Elements\AtomV1Tabs\render('isotope', $filterbar['terms'], $filterbar['design'], [
                'enabled' => $filterbar['all_filter'],
                'label' => $filterbar['all_label']
            ]);
            ?>
        </div>
    </div>
<?php
}

/**
 * @param FilterBar $filterbar
 * @return void
 */
function renderIsotopeFooter($filterbar, $tag = 'div') {
    if (!$filterbar['enable']) return;
    echo "<{$tag} class=\"bde-loop-item-gutter ee-post-gutter\"></{$tag}>";
    echo "<{$tag} class=\"bde-loop-item-sizer ee-post-sizer\"></$tag>";
}

/**
 * @param \WP_Query $query
 * @param array $settings
 * @return array{id: int, title: string}[]
 */
function getTermsOnCurrentPage($query, $settings) {
    $termsInPosts = [];
    $taxonomy = $settings['type'];
    $hideUncategorized = $settings['hide_uncategorized'];
    $sort = $settings['sort_by'];

    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            /** @var int $id */
            $id = get_the_ID();

            /** @var \WP_Term[] $postTerms */
            $postTerms = wp_get_post_terms($id, $taxonomy);

            foreach ($postTerms as $postTerm) {
                $ids = array_column($termsInPosts, 'id');

                if (!in_array($postTerm->term_id, $ids)) {
                    $termsInPosts[] = [
                        'id' => $postTerm->term_id,
                        'title' => $postTerm->name,
                        'count' => $postTerm->count,
                        'term_order' => (int) $postTerm->term_order,
                    ];
                }
            }
        endwhile;
    }

    if ($sort) {
        if ($sort === 'name-asc') {
            usort($termsInPosts, fn($a, $b) => $a['title'] <=> $b['title']);
        } else if ($sort === 'name-desc') {
            usort($termsInPosts, fn($a, $b) => $b['title'] <=> $a['title']);
        } else if ($sort === 'count') {
            usort($termsInPosts, fn($a, $b) => $b['count'] - $a['count']);
        } else if ($sort === 'term_order') {
            usort($termsInPosts, fn($a, $b) => $a['term_order'] - $b['term_order']);
        }
    }

    if ($hideUncategorized) {
        $termsInPosts = array_filter($termsInPosts, fn($term) => $term['id'] !== 1);
    }

    return $termsInPosts;
}

/**
 * @param FilterBar $filterbar
 * @param string $classes
 * @return string
 */
function getFilterAttributesForPost($filterbar, $classes = '') {
    /** @var int $id */
    $id = get_the_ID();
    $taxonomy = $filterbar['type'];
    $attrs = [];

    if ($classes) {
        $attrs['class'] = $classes;
    }

    if ($filterbar['enable']) {
        /** @var array $postTerms */
        $postTerms = get_the_terms($id, $taxonomy) ?: [];
        $filters = implode(',', array_column($postTerms, 'term_id'));

        if (shouldHideIsotopeItemOnPageLoad($filterbar)) {
            if ($classes) {
                $attrs['class'] .= ' initially-hidden';
            }

            $attrs['style'] = 'display: none;';
        }

        if ($filters) {
            $attrs['data-filters'] = $filters;
        }
    }

    $attrsHtml = join(
        ' ',
        array_map(function ($key) use ($attrs) {
            return $key . '="' . $attrs[$key] . '"';
        }, array_keys($attrs))
    );

    return $attrsHtml;
}

/**
 * @param FilterBar $filterbar
 * @param int|\WP_Post $post
 * @return bool
 */
function shouldHideIsotopeItemOnPageLoad($filterbar, $post = null) {
    $notAllCategory = $filterbar['activeTerm'] > 0;
    $inActiveTerm = has_term($filterbar['activeTerm'], $filterbar['type'], $post);
    return $notAllCategory && !$inActiveTerm;
}

/**
 * @param bool $shouldShowAllFilter
 * @param array{id: int, title: string}[] $terms
 * @return int
 */
function getActiveTerm($shouldShowAllFilter, $terms) {
    if ($shouldShowAllFilter) {
        return 0;
    } else {
        return $terms[0]['id'] ?? 0;
    }
}


/**
 * @param array $design
 */
function renderSwiperPagination($design) {
    $template = '{{ macros.AtomV1SwiperPaginationAndArrowsHtml(design) }}';
    echo Twig::getInstance()->runTwig($template, [
        'design' => $design,
    ]);
}


function renderSwiperContainer() {
    echo '<div class="breakdance-swiper-wrapper" data-swiper-id="%%ID%%">';
    echo '<div class="swiper">';
}

/**
 * @param array $design
 */
function closeSwiperContainer($design) {
    echo "</div>"; // close .swiper
    \Breakdance\LoopBuilder\renderSwiperPagination($design);
    echo "</div>"; // close /.breakdance-swiper-wrapper
}
