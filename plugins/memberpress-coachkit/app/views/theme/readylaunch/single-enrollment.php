<?php

/**
 * The template for displaying all enrollments
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package coaching
 */

$view = get_query_var( 'coaching-data' );
$options = get_query_var( 'options' );
?>

<?php echo $view->header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<div x-data="{ open: false }" @keydown.window.escape="open = false">
  <?php echo $view->sidebar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  ?>

  <div class="lg:pl-64 rtl:lg:pl-0 rtl:lg:pr-64">
    <?php echo $view->navbar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>

  <?php
  if ( $view->enrollment->group->starts_in_future ) { ?>
    <div class="bg-yellow-50 border-t-4 border-yellow-500 rounded-b text-yellow-900 px-4 py-3 shadow-md" role="alert">
      <div class="flex">
        <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
          </svg></div>
        <div>
          <p class="font-bold"><?php esc_html_e( 'Program Commencing Soon', 'memberpress-coachkit' ); ?></p>
          <p class="text-sm"><?php printf( '%s <strong>%s</strong>.', esc_html__( 'The program is scheduled to begin on', 'memberpress-coachkit' ), esc_html( $view->enrollment->group->start_date ) ); ?></p>
        </div>
      </div>
    </div>
  <?php } ?>
    <main class="mpch-main-content flex flex-col py-10">
      <?php mpch_load_template( 'theme/default/single-enrollment', get_defined_vars() ); ?>
      <?php mpch_load_template( 'theme/default/widgets', get_defined_vars() ); ?>
    </main>
  </div>
</div>


<?php
echo $view->snackbar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $view->footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
