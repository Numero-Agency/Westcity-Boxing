<div class="mpch-metabox-white mpch-metabox-download <?php echo esc_attr( $wrapper_css ); ?>">
  <div class="mpch-metabox-white__header mpch-metabox__header ui-sortable-handle" data-action="collapse-download">
    <h3 class="mpch-metabox__title">
      <?php
      esc_html_e( 'Downloads', 'memberpress-coachkit' );
      ?>
    </h3>
  </div>
  <div class="mpch-metabox__content">
    <div class="mpch-metabox__row">
      <div class="mpch-metabox__column">
        <ul class="mpch-list-group --theme-input">
          <template> <!-- Do not delete. JS will use this to populate the list -->
            <li class="mpch-list-group__item">
              {download_thumbnail}
              <span class="mpch-list-group__title">{download_title}</span>
              <button class="mpch-metabox__button --delete" type="button" data-action="remove-download" data-id="{download_id}"><?php echo esc_html_x( 'Delete', 'ui', 'memberpress-coachkit' ); ?></button>
              <input type="hidden" value="{download_id}" name="<?php echo esc_attr( $download_id_str ); ?>">
            </li>
          </template>
          <?php foreach ( $downloads as $download ) { ?>
            <li class="mpch-list-group__item">
              <?php if((\preg_match('/image\/\w+/', $download->filetype))): ?>
                <img class="mpch-list-group__thumb" src="<?php echo esc_url_raw( $download->thumb_url ); ?>" />
              <?php else : ?>
                <i class="<?php echo esc_attr($download->file_thumb) ?> mpdl-icon mpch-icon large"></i>
              <?php endif; ?>
              <span class="mpch-list-group__title"><?php echo esc_html( $download->post_title ); ?></span>
              <button class="mpch-metabox__button --delete" type="button" data-action="remove-download" data-id="<?php echo esc_attr( $download->ID ); ?>"><?php echo esc_html_x( 'Delete', 'ui', 'memberpress-coachkit' ); ?></button>
              <input type="hidden" value="<?php echo esc_attr( $download->ID ); ?>" name="<?php echo esc_attr( $download_id_str ); ?>">
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>

  </div>
</div>
