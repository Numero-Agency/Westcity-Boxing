<tr class="mpch-notes__note">
  <td class="author column-author" colspan="2">
    <div>
      <strong>
        <img alt="" src="<?php echo esc_url( $author_thumbnail_url ); ?>" class="avatar avatar-32 photo" height="32" width="32" loading="lazy" decoding="async">
        <a href="<?php echo esc_url( $author_profile_url ); ?>" rel="noopener noreferrer"><?php echo esc_html( $author_name ); ?></a>
      </strong>
      <br>
      <?php echo esc_html( $time ); ?>
    </div>
    <div class="mpch-notes__row-note">
      <span class="mpch-notes__static-note"><?php echo wp_kses_post( $note->note ); ?></span>

      <div class="wp-editor-container hidden">
        <textarea class="wp-editor-area" rows="5" cols="40"><?php echo wp_kses_post( $note->note ); ?></textarea>
      </div>

      <div class="row-actions">
        <span class="mpch-notes__edit-button" data-trigger="edit-note"><a href="#0" aria-label="Edit this note">Edit</a></span>
        <span class="trash"> | <a href="#0" data-trigger="trash-note" data-note-id="<?php echo esc_attr( $note->id ); ?>" class="delete vim-d vim-destructive aria-button-if-js" aria-label="Move this note to the Trash" role="button">Trash</a></span>
      </div>

      <p class="hide-if-no-js mpch-notes__update-buttons hidden">
        <button type="button" data-trigger="update-note" data-note-id="<?php echo esc_attr( $note->id ); ?>" data-student-id="<?php echo esc_attr( $_GET['id'] ); ?>" class="button button-primary">Update Note</button>
        <button type="button" data-trigger="hide-note-editor" class="button">Cancel</button>
      </p>
    </div>
  </td>
</tr>
