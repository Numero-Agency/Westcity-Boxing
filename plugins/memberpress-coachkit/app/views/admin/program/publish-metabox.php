<div class="submitbox" id="submitpost">
  <div id="major-publishing-actions">

  <div id="delete-action">
    <?php
    if ( $can_update ) :
      if ( current_user_can( 'delete_post', $post_id ) ) {
        if ( ! EMPTY_TRASH_DAYS ) {
          $delete_text = __( 'Delete permanently', 'memberpress-coachkit' );
        } else {
          $delete_text = __( 'Move to Trash', 'memberpress-coachkit' );
        }
        ?>
        <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post_id ); ?>"><?php echo $delete_text; ?></a>
        <?php
      }
    endif;
    ?>
  </div>

    <div id="publishing-action">
      <span class="spinner"></span>
      <?php
      if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) || 0 === $post_id ) {
        if ( $can_publish ) :
          if ( ! empty( $post->post_date_gmt ) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) :
            ?>
            <input name="original_publish" type="hidden" id="original_publish" value="<?php echo esc_attr_x( 'Schedule', 'post action/button label', 'memberpress-coachkit' ); ?>" />
            <?php submit_button( _x( 'Schedule', 'post action/button label', 'memberpress-coachkit' ), 'primary large', 'publish', false ); ?>
            <?php
          else :
            ?>
            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'memberpress-coachkit' ); ?>" />
            <?php submit_button( __( 'Publish', 'memberpress-coachkit' ), 'primary large', 'publish', false ); ?>
            <?php
          endif;
        else :
          ?>
          <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review', 'memberpress-coachkit' ); ?>" />
          <?php submit_button( __( 'Submit for Review', 'memberpress-coachkit' ), 'primary large', 'publish', false ); ?>
          <?php
        endif;
      } else {
        if ( $can_update ) :
          ?>
          <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'memberpress-coachkit' ); ?>" />
          <?php submit_button( __( 'Update', 'memberpress-coachkit' ), 'primary large', 'save', false, array( 'id' => 'publish' ) ); ?>
          <?php
        else :
          ?>
        <span id="mpch-tooltip-<?php echo $post_id; ?>" class="mpch-tooltip">
          <span><i class="mp-icon mp-icon-info-circled mp-16"></i></span>
          <span class="mepr-data-title mepr-hidden"><?php echo esc_html__( 'Program Status', 'memberpress-coachkit' ); ?></span>
          <span class="mepr-data-info mepr-hidden"><?php printf( esc_html_x( 'This program is actively running with enrolled %s. As a result, updates to this program are restricted, and it can be considered "Read-Only" for the time being', 'ui', 'memberpress-coachkit' ), strtolower($label_for_clients)); ?></span>
        </span>
          <button type="button" class="button button-secondary" disabled><?php echo esc_html__( 'In Progress', 'memberpress-coachkit' ); ?></button>
          <?php
        endif;
      }
      ?>
    </div>
    <div class="clear"></div>
  </div>
</div>
