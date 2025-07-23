<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}
$search       = ( isset( $_REQUEST['search'] ) && ! empty( $_REQUEST['search'] ) ) ? sanitize_text_field( stripslashes( $_REQUEST['search'] ) ) : '';
$perpage      = ( isset( $_REQUEST['perpage'] ) && ! empty( $_REQUEST['perpage'] ) ) ? (int) $_REQUEST['perpage'] : 10;
$search_field = ( isset( $_REQUEST['search-field'] ) && ! empty( $_REQUEST['search-field'] ) ) ? sanitize_text_field( $_REQUEST['search-field'] ) : '';
?>

<p class="search-box">
  <?php do_action( 'mpch_table_controls_search', $search, $perpage ); ?>
  <span class="search-fields">
    <span><?php esc_html_e( 'Search', 'memberpress-coachkit' ); ?></span>
    <input id="cspf-table-search" value="<?php echo $search; ?>" style="line-height: 1.4;" />

    <input id="cspf-table-search-submit" class="button" type="submit" value="<?php esc_html_e( 'Search', 'memberpress-coachkit' ); ?>" />
    <?php
    if ( isset( $_REQUEST['search'] ) || isset( $_REQUEST['search-filter'] ) ) {
      $uri = $_SERVER['REQUEST_URI'];
      $uri = preg_replace( '/[\?&]search=[^&]*/', '', $uri );
      $uri = preg_replace( '/[\?&]search-field=[^&]*/', '', $uri );
      ?>
      <a href="<?php echo $uri; ?>">[x]</a>
      <?php
    }
    ?>
  </span>
</p>

<div class="cspf-tablenav-spacer">&nbsp;</div>
