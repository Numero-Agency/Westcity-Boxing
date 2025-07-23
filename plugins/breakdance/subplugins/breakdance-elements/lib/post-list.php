<?php

namespace Breakdance\LoopBuilder;

function loopPosts($loop, $filterbar, $propertiesData, $actionData) {
    $postTag = $postProps['tag'] ?? 'article';

    while ($loop->have_posts()) : $loop->the_post();
        $post = get_post();
        $itemClasses = getItemClasses($post, 'post', $actionData);
        $attrs = getFilterAttributesForPost($filterbar, $itemClasses);

        renderPost(
            $actionData,
            $postTag,
            $attrs,
            $propertiesData
        );
    endwhile;
}

function renderPost($actionData, $postTag, $attrs, $propertiesData)
{
    $post = get_post();

    $postProps = $propertiesData['content']['post'];
    $openNewTab = ($postProps['open_in_new_tab'] ?? false) ? 'target="_blank"' : '';

    // image
    $imageDisable = (bool)($postProps['image']['disable'] ?? false);
    $imageSize = (string)($postProps['image']['size'] ?? 'full');
    $fallbackImageData = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1NDAiIGhlaWdodD0iNTQwIiB2aWV3Qm94PSIwIDAgMTQwIDE0MCI+PHBhdGggZD0iTTAgMGgxNDB2MTQwSDB6IiBmaWxsPSIjZTVlN2ViIi8+PHBhdGggZD0iTTg4IDgyLjQ2SDUxLjh2LTQuNTJsNi43NC02Ljc0YTEuMTMgMS4xMyAwIDAxMS42IDBsNS4yMyA1LjIzIDEyLjc2LTEyLjc3YTEuMTMgMS4xMyAwIDAxMS42IDBMODggNzEuOTF6IiBmaWxsPSIjZTVlN2ViIi8+PHBhdGggZD0iTTg5LjQ4IDUyLjMySDUwLjI5YTQuNTIgNC41MiAwIDAwLTQuNTIgNC41MlY4NGE0LjUzIDQuNTMgMCAwMDQuNTIgNC41MmgzOS4xOUE0LjUyIDQuNTIgMCAwMDk0IDg0VjU2Ljg0YTQuNTIgNC41MiAwIDAwLTQuNTItNC41MnptLTMzLjE2IDUuMjdhNS4yOCA1LjI4IDAgMTEtNS4yNyA1LjI4IDUuMjcgNS4yNyAwIDAxNS4yNy01LjI4ek04OCA4Mi40Nkg1MS44di00LjUybDYuNzQtNi43NGExLjEzIDEuMTMgMCAwMTEuNiAwbDUuMjMgNS4yMyAxMi43Ni0xMi43N2ExLjEzIDEuMTMgMCAwMTEuNiAwTDg4IDcxLjkxeiIgZmlsbD0iI2QxZDVkYiIvPjwvc3ZnPg==";
    $fallbackImage = (string)($postProps['image']['fallback_image']['url'] ?? $fallbackImageData);

    $imagePositionClass = '';
    $imagePosition = $propertiesData['design']['post']['image']['position'] ?? false;
    if (($imagePosition)) {
      $imagePositionClass = "bde-loop-item__image-". $imagePosition ." ee-posts-image-" . $imagePosition;
    }

    // title datas
    $titleDisable = (bool)($postProps['title']['disable'] ?? false);
    $titleTag = (string)($postProps['title']['tag'] ?? 'h3');

    // meta datas
    $metaDisable = (bool)($postProps['meta']['disable'] ?? false);
    $metaLink = (bool)($postProps['meta']['link'] ?? false);

    // Content
    $contentDisable = (bool)($postProps['content']['disable'] ?? false);
    $contentType = (string)($postProps['content']['type'] ?? 'excerpt');
    $excerptLength = (int)($postProps['content']['excerpt_length'] ?? '');

    // button
    $buttonDisable = (bool)($postProps['button']['disable'] ?? false);
    $buttonText = (string)($postProps['button']['button_text'] ?? 'Read more');

    // taxonomy
    $taxoDisable = (bool)($postProps['taxonomy']['disable'] ?? false);

    $taxoType = (string)($postProps['taxonomy']['type'] ?? '');
    $taxoCount = $postProps['taxonomy']['count'] ?? 0;
    $taxoLink = (bool)($postProps['taxonomy']['link'] ?? false);

    do_action("breakdance_before_loop_item", $post, 'post', $actionData);
    do_action("breakdance_posts_list_before_post", $actionData);
    ?>
    <<?php echo $postTag; ?> <?php echo $attrs; ?>>
      <?php if (!($imageDisable)) { ?>
      <?php $thumbnail = get_the_post_thumbnail_url(get_the_ID(), $imageSize); ?>
    <a class="bde-loop-item__image-link ee-post-image-link <?php echo $imagePositionClass; ?>" href="<?php the_permalink(); ?>" aria-label="<?php the_title(); ?>">
      <div class="bde-loop-item__image ee-post-image">
          <?php if ($thumbnail) {
              the_post_thumbnail($imageSize);
          } else { ?> <img src="<?php echo $fallbackImage; ?>" width="540" height="540" alt=""><?php } ?>
      </div>
    </a>
  <?php } ?>

      <?php do_action("breakdance_posts_list_after_image", $actionData); ?>

    <div class="bde-loop-item__wrap ee-post-wrap">
      <?php do_action("breakdance_posts_list_inside_wrap_start", $actionData); ?>

      <?php if (!($titleDisable)) { ?>
    <<?php echo $titleTag ?> class="ee-post-title">
    <a class="bde-loop-item__title-link ee-post-title-link" href="<?php the_permalink(); ?>" <?php echo $openNewTab; ?>>
        <?php the_title(); ?>
    </a>
    </<?php echo $titleTag ?>>
  <?php }

      do_action("breakdance_posts_list_after_title", $actionData);

      if (!($metaDisable)) { ?>
        <div class="bde-loop-item__post-meta ee-post-meta">
            <?php
            $postPropsMetaMeta = $postProps['meta']['meta'] ?? [];
            foreach ($postPropsMetaMeta as $postMeta) {
                if ($postMeta == 'author') { ?>
                    <?php if ($metaLink) { ?>
                    <span class="bde-loop-item__meta-item bde-loop-item__meta-author ee-post-meta-author ee-post-meta-item"><?php the_author_posts_link(); ?></span>
                    <?php } else { ?>
                    <span class="bde-loop-item__meta-item bde-loop-item__meta-author ee-post-meta-author ee-post-meta-item"><?php the_author(); ?></span>
                    <?php } ?>
                <?php } else if ($postMeta == 'comment') { ?>
                    <?php if ($metaLink) { ?>
                    <span class="bde-loop-item__meta-comments bde-loop-item__meta-item ee-post-meta-comments ee-post-meta-item">
                                          <a href="<?php comments_link(); ?>" <?php echo $openNewTab; ?>>
                                              <?php comments_number(); ?>
                                          </a>
                                      </span>
                    <?php } else { ?>
                    <span class="bde-loop-item__comments bde-loop-item__meta-item ee-post-meta-comments ee-post-meta-item"><?php comments_number(); ?></span>
                    <?php } ?>
                <?php } else if ($postMeta == 'date') { ?>
                    <?php if ($metaLink) { ?>
                    <span class="bde-loop-item__meta-date bde-loop-item__meta-item ee-post-meta-date ee-post-meta-item">
                                          <a href="<?php echo get_day_link(get_post_time('Y'), get_post_time('m'), get_post_time('j')); ?>" <?php echo $openNewTab; ?>>
                                              <?php the_time(get_option('date_format')); ?>
                                          </a>
                                      </span>
                    <?php } else { ?>
                    <span class="bde-loop-item__meta-date bde-loop-item__meta-item ee-post-meta-date ee-post-meta-item">
                                          <?php the_time(get_option('date_format')); ?>
                                      </span>
                    <?php } ?>
                <?php }
            } ?>

        </div>
      <?php }

      do_action("breakdance_posts_list_after_meta", $actionData);

      if (!($taxoDisable)) {

          if ($taxoLink) {
              $terms = get_the_terms(get_the_ID(), $taxoType);
              if ($terms && !is_wp_error($terms)) {
                  $term_links = [];
                  foreach (array_slice($terms, 0, $taxoCount) as $term) {
                      $term_links[] = '<li class="bde-loop-item__tax-item ee-post-taxonomy-item"><a href="' . esc_attr(get_term_link($term->slug, $taxoType)) . '"' . $openNewTab . '>' . __($term->name) . '</a></li>';
                  }
                  $all_terms = join($term_links);
                  echo '<ul class="ee-post-taxonomy">' . __($all_terms) . '</ul>';
              }
          } else {
              $terms = get_the_terms(get_the_ID(), $taxoType);
              if ($terms && !is_wp_error($terms)) {
                  $term_links = [];
                  foreach (array_slice($terms, 0, $taxoCount) as $term) {
                      $term_links[] = '<li class="bde-loop-item__tax-item ee-post-taxonomy-item">' . __($term->name) . '</li>';
                  }
                  $all_terms = join($term_links);
                  echo '<ul class="bde-loop-item__post-tax ee-post-taxonomy">' . __($all_terms) . '</ul>';
              }
          }
      }

      do_action("breakdance_posts_list_after_tax", $actionData);

      if (!($contentDisable)) { ?>
        <div class="bde-loop-item__content ee-post-content">
            <?php
            if ($contentType == "excerpt") {
                if ($excerptLength == true) {
                    $content = get_the_excerpt();
                    echo wp_trim_words($content, $excerptLength);
                } else {
                    the_excerpt();
                }
            } else {
                the_content();
            } ?>
        </div>
      <?php }

      do_action("breakdance_posts_list_after_content", $actionData);

      if (!($buttonDisable)) {

          $buttonContent['text'] = $buttonText;
          $buttonContent['link'] = get_the_permalink();

          echo \Breakdance\Elements\AtomV1Button\render($buttonContent, 'bde-loop-item__button ee-post-button', $propertiesData['design']['post']['button'] ?? [], 'secondary');
      }

      ?>

      <?php do_action("breakdance_posts_list_inside_wrap_end", $actionData); ?>

    </div>

    </<?php echo $postTag; ?>>

    <?php
    do_action("breakdance_posts_list_after_post", $actionData);
    do_action("breakdance_after_loop_item", $post, 'post', $actionData);
}