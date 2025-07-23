<div class="mpch-metabox-white mpch-metabox-checkin <?php echo esc_attr( $wrapper_css ); ?>">
  <div class="mpch-metabox-white__header mpch-metabox__header ui-sortable-handle flex-between" data-action="collapse-checkin">
    <h3 class="mpch-metabox__title">
      <?php
      esc_html_e( 'Check In', 'memberpress-coachkit' );
      ?>
    </h3>
    <span class="dashicons dashicons-no-alt" data-toggle data-action="remove-checkin"></span>
  </div>
  <div class="mpch-metabox__content">
    <div class="mpch-metabox__row">
      <div class="mpch-metabox__column">

        <div class="mpch-metabox__row">
          <div class="mpch-metabox__column">
            <label><?php printf( esc_html_x( '%s Question', 'ui', 'memberpress-coachkit' ), $label_for_client ); ?></label>
            <input type="text" name="<?php echo esc_attr( $title_str ); ?>" value="<?php echo esc_attr( $checkin->question ); ?>" id="">
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
