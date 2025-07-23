<span class="filter-by">
  <label><?php esc_html_e( 'Filter by', 'memberpress-coachkit' ); ?></label>

  <select class="mepr_filter_field" id="r_program">
    <option value="all" <?php selected( $r_program, false ); ?>><?php esc_html_e( 'All Programs', 'memberpress-coachkit' ); ?></option>
    <?php foreach ( $programs as $program ) : ?>
      <option value="<?php echo esc_attr( $program->ID ); ?>" <?php selected( $program->ID, $r_program ); ?>><?php echo esc_html( $program->post_title ); ?></option>
    <?php endforeach; ?>
  </select>

  <input type="submit" id="mepr_search_filter" class="button" value="<?php esc_html_e( 'Go', 'memberpress-coachkit' ); ?>" />

  <?php
  if ( isset( $_REQUEST['r_program'] ) || isset( $_REQUEST['r_coach'] ) ) {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = preg_replace( '/[\?&]r_program=[^&]*/', '', $uri );
    $uri = preg_replace( '/[\?&]r_coach=[^&]*/', '', $uri );
    $uri = preg_replace( '/[\?&]gateway=[^&]*/', '', $uri );
    ?>
    <a href="<?php echo esc_url( $uri ); ?>">[x]</a>
    <?php
  }
  ?>
</span>

<?php /* esc_html_e('or', 'memberpress-coachkit'); */ ?>
