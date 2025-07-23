<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}
?>

<div class="wrap">
  <h2><?php echo esc_html( $label_for_coaches ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=memberpress-coachkit-coaches&action=new' ) ); ?>" class="add-new-h2" data-action="add-coach-modal" data-mfp-src="#add-coach-form"><?php esc_html_e( 'Add New', 'memberpress-coachkit' ); ?></a></h2>

  <?php
  echo $errors; // phpcs:ignore WordPress.Security.EscapeOutput
  ?>

  <?php $list_table->display(); ?>
</div>
