<div class="mpch-metabox-rows">
  <?php echo $groups_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
<button class="mpch-metabox__button --secondary" type="button" data-action="toggle-group" data-mfp-src="#group-popup" aria-haspopup="true">
  <span><?php echo esc_html_x( 'New Cohort', 'ui', 'memberpress-coachkit' ); ?></span>
</button>
