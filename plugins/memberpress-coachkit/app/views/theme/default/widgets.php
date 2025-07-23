<div class="mpch-widgets mt-4 md:mt-auto px-4 sm:px-6 lg:px-8">
  <?php if ( is_active_sidebar( 'mpch_general_footer_widget' ) ) : ?>
    <div id="mpch-general-footer-widget" class="mpch-general-footer-widget widget-area py-4" role="complementary">
      <?php dynamic_sidebar( 'mpch_general_footer_widget' ); ?>
    </div>
  <?php endif; ?>
  <?php if ( is_active_sidebar( 'mepr_rl_global_footer' ) ) : ?>
    <div id="mepr-rl-global-footer-widget" class="mepr-rl-global-footer-widget widget-area py-4" role="complementary">
      <?php dynamic_sidebar( 'mepr_rl_global_footer' ); ?>
    </div>
  <?php endif; ?>
</div>
