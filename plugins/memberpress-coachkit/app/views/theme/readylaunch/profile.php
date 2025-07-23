<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package coaching
 */

$theme = get_query_var( 'coaching-data' );
?>

<?php echo $theme->header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<div x-data="{ open: false }" @keydown.window.escape="open = false">
  <?php echo $theme->sidebar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

  <div class="lg:pl-64 rtl:lg:pl-0 rtl:lg:pr-64">
    <?php echo $theme->navbar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <main class="py-10">
      <div class="px-4 sm:px-6 lg:px-8">

      </div>
    </main>
  </div>
</div>

<?php
echo $theme->footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
