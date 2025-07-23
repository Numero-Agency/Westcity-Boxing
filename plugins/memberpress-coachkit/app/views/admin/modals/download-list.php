<ul class="mpch-list-group list-group-flush">
  <?php

  foreach ( $attachments as $attachment ) {
    printf(
      '<li class="mpch-list-group__item">
        <label for="mpch-milestone-add-download-%1$d">
          %3$s
          <input type="checkbox" class="visuallyhidden" name="mpch-milestone-add-download[]" data-download-id="%1$d" data-download-title="%2$s" id="mpch-milestone-add-download-%1$d" value="%1$d" data-action="highlight-download" data-download-thumbnail="%4$s"> %2$s
        </label>
      </li>',
      esc_html( $attachment->ID ),
      esc_html( $attachment->post_title ),
      (\preg_match('/image\/\w+/', $attachment->filetype)) ? '<img src="'.$attachment->thumb_url.'" class="mpch-list-group__thumb" />' : '<i class="'.$attachment->file_thumb.' mpdl-icon mpch-icon large"></i>',
      esc_url_raw( wp_get_attachment_image_url( $attachment->ID, 'thumbnail', true ) )
    );
  }

  if ( empty( $attachments ) ) {
    printf( '<li class="mpch-list-group__item">%s</li>', esc_html__( 'No attachments found. Please add attachments and try again.', 'memberpress-coachkit' ) );
  }

  ?>
</ul>