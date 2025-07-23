<div id="new-coach-form" class="mpch-modal mpch-modal-new-coach mfp-hide">
  <div class="mpch-modal__content">
    <div class="mpch-modal__header">
      <h3 class="mpch-modal__title"><?php printf( esc_html__( 'New %s', 'memberpress-coachkit' ), $label_for_coach); ?></h3>
    </div>
    <div class="mpch-modal__body">

      <!-- Do not remove -->
      <div class="mpch-notice mpch-notice__error"></div>
      <div class="mpch-notice mpch-notice__success"></div>


      <table class="mpch-metabox__table">
        <tbody>
          <tr>
            <th>
              <label for="<?php echo esc_attr( $firstname_str ); ?>"><?php esc_html_e( 'First Name', 'memberpress-coachkit' ); ?></label>
            </th>
            <td>
              <input type="text" name="<?php echo esc_attr( $firstname_str ); ?>" id="<?php echo esc_attr( $firstname_str ); ?>">
            </td>
          </tr>
          <tr>
            <th>
              <label for="<?php echo esc_attr( $lastname_str ); ?>"><?php esc_html_e( 'Last Name', 'memberpress-coachkit' ); ?></label>
            </th>
            <td>
              <input type="text" name="<?php echo esc_attr( $lastname_str ); ?>" id="<?php echo esc_attr( $lastname_str ); ?>">
            </td>
          </tr>
          <tr>
            <th>
              <label for="<?php echo esc_attr( $email_str ); ?>"><?php esc_html_e( 'Email', 'memberpress-coachkit' ); ?></label>
            </th>
            <td>
              <input type="text" name="<?php echo esc_attr( $email_str ); ?>" id="<?php echo esc_attr( $email_str ); ?>">
            </td>
          </tr>
          <tr>
            <th>
              <label for="<?php echo esc_attr( $username_str ); ?>"><?php esc_html_e( 'Username', 'memberpress-coachkit' ); ?></label>
            </th>
            <td>
              <input type="text" name="<?php echo esc_attr( $username_str ); ?>" id="<?php echo esc_attr( $username_str ); ?>">
            </td>
          </tr>
          <tr>
            <th>
            </th>
            <td>
            <label>
              <input type="checkbox" name="<?php echo esc_attr( $send_user_notification_str ); ?>" id="<?php echo esc_attr( $send_user_notification_str ); ?>">
              <?php esc_html_e( 'Send login information', 'memberpress-coachkit' ); ?>
            </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mpch-modal__footer">
      <button class="button mpch-metabox__button --link" data-action="add-coach-modal" data-mfp-src="#add-coach-form"><?php printf( esc_html__( 'Add Existing %s', 'memberpress-coachkit' ), $label_for_coach); ?></button>
      <button type="button" class="button button-primary mpch-modal__button" data-action="new-coach">
        <svg class="" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
          <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <?php echo esc_html_x( 'Add', 'ui', 'memberpress-coachkit' ); ?>
      </button>
    </div>
  </div>
</div>
