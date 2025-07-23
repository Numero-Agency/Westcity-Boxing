<div id="coaching" class="mepr-options-hidden-pane">
  <div class="mpch-settings">

    <div class="mpch-settings-card">
      <h3 class="mpch-settings-card__title"><?php esc_html_e( 'Messaging', 'memberpress-coachkit' ); ?></h3>
      <div class="mpch-settings-form-row items-center">
        <label class="switch mpch-settings-form-switch">
          <input id="mpch_options_enable_messaging" name="mpch-options[enable_messaging]" class="" type="checkbox" value="1" <?php checked( 1, $options->enable_messaging ); ?>>
          <span class="slider round"></span>
        </label>

        <p class="mpch-settings-form-label">
          <?php esc_html_e( 'Enable messaging', 'memberpress-coachkit' ); ?>
        </p>
      </div>
    </div>


    <div class="mpch-settings-card">
      <h3 class="mpch-settings-card__title"><?php esc_html_e( 'Label Customizations', 'memberpress-coachkit' ); ?></h3>
      <div class="space-y-3">
        <div class="">
          <label for="" class="mpch-settings-form-label"><?php esc_html_e( 'Label for Client', 'memberpress-coaching', 'memberpress-coachkit' ); ?></label>
          <div class="space-y-2">
            <input name="mpch-options[label_for_client]" type="text" value="<?php echo esc_html( $options->label_for_client ); ?>" placeholder="<?php esc_html_e( 'Singular', 'memberpress-coaching', 'memberpress-coachkit' ); ?>" class="regular-text ltr">
            <input name="mpch-options[label_for_clients]" type="text" value="<?php echo esc_html( $options->label_for_clients ); ?>" placeholder="<?php esc_html_e( 'Plural', 'memberpress-coaching', 'memberpress-coachkit' ); ?>" class="regular-text ltr">
          </div>
        </div>

        <div class="">
          <label for="" class="mpch-settings-form-label"><?php esc_html_e( 'Label for Coach', 'memberpress-coaching', 'memberpress-coachkit' ); ?></label>
          <div class="space-y-2">
            <input name="mpch-options[label_for_coach]" type="text" value="<?php echo esc_html( $options->label_for_coach ); ?>" placeholder="<?php esc_html_e( 'Singular', 'memberpress-coaching', 'memberpress-coachkit' ); ?>" class="regular-text ltr">
            <input name="mpch-options[label_for_coaches]" type="text" value="<?php echo esc_html( $options->label_for_coaches ); ?>" placeholder="<?php esc_html_e( 'Plural', 'memberpress-coaching', 'memberpress-coachkit' ); ?>" class="regular-text ltr">
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
