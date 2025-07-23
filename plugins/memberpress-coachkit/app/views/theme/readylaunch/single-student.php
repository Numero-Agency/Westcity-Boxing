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

    <main class="mpch-main-content flex flex-col py-10 jljljl">
      <?php mpch_load_template( 'theme/default/single-student', get_defined_vars() ); ?>
      <?php mpch_load_template( 'theme/default/widgets', get_defined_vars() ); ?>
    </main>
  </div>
</div>


<?php
echo $view->snackbar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $view->footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
