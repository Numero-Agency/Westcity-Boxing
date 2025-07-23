<div id="group-popup" class="mpch-modal mpch-modal__group --lg mfp-hide">
  <div class="mpch-modal__content">
    <div class="mpch-modal__header">
      <h3 class="mpch-modal__title"><?php echo esc_html_x( 'Cohort', 'ui', 'memberpress-coachkit' ); ?></h3>
    </div>
    <div class="mpch-modal__body">

      <!-- Do not remove -->
      <div class="mpch-notice mpch-notice__error"></div>

      <div class="mpch-metabox__row">
        <div class="mpch-metabox__column">
          <label class="mpch-modal__label"><?php echo esc_html_x( 'Cohort Name', 'ui', 'memberpress-coachkit' ); ?></label>
          <input type="text" name="<?php echo esc_attr( $title_str ); ?>" value="<?php echo esc_attr( $group->title ); ?>" id="">
        </div>
      </div>

      <div class="mpch-metabox__row">
        <div class="mpch-metabox__column">
          <div>
            <label class="mpch-modal__label"><?php echo esc_html_x( 'Enrollment cap', 'ui', 'memberpress-coachkit' ); ?></label>
            <label><input value="1" type="checkbox" name="<?php echo esc_attr( $allow_enrollment_cap_str ); ?>" <?php checked( $group->allow_enrollment_cap, 1 ); ?>><?php printf( esc_html_x( 'Limit no. of %s in Program', 'ui', 'memberpress-coachkit' ), strtolower($label_for_clients)); ?></label>
          </div>
        </div>
        <div class="mpch-metabox__column">
          <div>
            <label class="mpch-modal__label"><?php echo esc_html_x( 'Type', 'ui', 'memberpress-coachkit' ); ?></label>
            <div class="radio-group <?php echo $program_ongoing ? esc_attr( 'disabled' ) : ''; ?>">
              <input type="radio" id="option-one" value="dynamic" name="<?php echo esc_attr( $type_str ); ?>" <?php checked( $group->type, 'dynamic' ); ?> <?php echo $program_ongoing ? esc_attr( 'disabled' ) : ''; ?>>
              <label for="option-one">
                <span class="dashicons dashicons-yes"></span>
                <?php echo esc_html_x( 'Dynamic', 'ui', 'memberpress-coachkit' ); ?>
              </label>

              <input type="radio" id="option-two" value="fixed" name="<?php echo esc_attr( $type_str ); ?>" <?php checked( $group->type, 'fixed' ); ?> <?php echo $program_ongoing ? esc_attr( 'disabled' ) : ''; ?>>
              <label for="option-two">
                <span class="dashicons dashicons-yes"></span>
                <?php echo esc_html_x( 'Fixed', 'ui', 'memberpress-coachkit' ); ?>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="mpch-metabox__row">
        <div class="mpch-metabox__column">
          <div data-condition-field="<?php echo esc_attr( $allow_enrollment_cap_str ); ?>">
            <label class="mpch-modal__label"><?php printf( esc_html_x( '%s Limit', 'ui', 'memberpress-coachkit' ), $label_for_client); ?></label>
            <input type="number" min="<?php echo $enrollment_count; ?>" name="<?php echo esc_attr( $enrollment_cap_str ); ?>" value="<?php echo esc_attr( $group->enrollment_cap ); ?>" id="">
          </div>
        </div>
        <div class="mpch-metabox__column">
          <div class="mpch-metabox__row --space-x-2" data-condition="fixed" data-condition-field="<?php echo esc_attr( $type_str ); ?>">
            <div class="mpch-metabox__column">
              <label class="mpch-modal__label"><?php echo esc_html_x( 'Start Date', 'ui', 'memberpress-coachkit' ); ?></label>
              <input type="text" value="<?php echo esc_attr( $start_date_fo ); ?>" class="datepicker">
              <input type="hidden" name="<?php echo esc_attr( $start_date_str ); ?>" value="<?php echo esc_attr( $group->start_date ); ?>" class="datepicker-alt">
            </div>
            <div class="mpch-metabox__column">
              <label class="mpch-modal__label"><?php echo esc_html_x( 'End Date', 'ui', 'memberpress-coachkit' ); ?></label>
              <input type="text" value="<?php echo esc_attr( $end_date_fo ); ?>" class="datepicker">
              <input type="hidden" name="<?php echo esc_attr( $end_date_str ); ?>" value="<?php echo esc_attr( $group->end_date ); ?>" class="datepicker-alt">
            </div>
          </div>

          <div class="mpch-metabox__row --space-x-2" data-condition="dynamic" data-condition-field="<?php echo esc_attr( $type_str ); ?>">
            <p><?php printf( esc_html_x( 'Starts whenever %s enrolls into cohort', 'ui', 'memberpress-coachkit' ), strtolower($label_for_client)); ?></p>
          </div>
        </div>
      </div>

      <div class="mpch-metabox__row">
        <div class="mpch-metabox__column">
          <div>
            <label class="mpch-modal__label"><?php printf('%s', $label_for_coach); ?></label>
            <select name="<?php echo esc_attr( $coach_id_str ); ?>" id="">
              <option value=""><?php printf( esc_html_x( 'Select %s', 'ui', 'memberpress-coachkit' ), $label_for_coach ); ?></option>
              <?php foreach ( $coaches as $coach ) { ?>
                <option value="<?php echo esc_attr( $coach->ID ); ?>" <?php selected( $group->coach_id, $coach->ID ); ?>><?php echo esc_html( $coach->display_name ); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="mpch-metabox__column">
          <div>
            <label class="mpch-modal__label"><?php echo esc_html_x( 'Status', 'ui', 'memberpress-coachkit' ); ?></label>
            <select name="<?php echo esc_attr( $status_str ); ?>" id="">
              <option value=""><?php echo esc_html_x( 'Select Status', 'ui', 'memberpress-coachkit' ); ?></option>
              <option value="open" <?php selected( $group->status, 'open' ); ?>><?php echo esc_html_x( 'Open', 'ui', 'memberpress-coachkit' ); ?></option>
              <!--
              <option value="scheduled_accepting_enrollments" <?php // selected( $group->status, 'scheduled_accepting_enrollments' ); ?>><?php // echo esc_html_x( 'Scheduled, accepting enrollments', 'ui', 'memberpress-coachkit' ); ?></option>
              <option value="scheduled_not_accepting_enrollments" <?php // selected( $group->status, 'scheduled_not_accepting_enrollments' ); ?>><?php // echo esc_html_x( 'Scheduled, not accepting enrollments', 'ui', 'memberpress-coachkit' ); ?></option> -->
              <option value="closed" <?php selected( $group->status, 'closed' ); ?>><?php echo esc_html_x( 'Closed', 'ui', 'memberpress-coachkit' ); ?></option>
            </select>
          </div>
        </div>
      </div>

      <div class="mpch-metabox__row">
        <div class="mpch-metabox__column">
          <div>
            <label><input value="1" type="checkbox" name="<?php echo esc_attr( $allow_appointments_str ); ?>" <?php checked( $group->allow_appointments, 1 ); ?>><?php printf( esc_html_x( 'Allow %s Appointments', 'ui', 'memberpress-coachkit' ), $label_for_client); ?></label>
          </div>
        </div>
      </div>

      <div class="mpch-metabox__row" data-condition-field="<?php echo esc_attr( $allow_appointments_str ); ?>">
        <div class="mpch-metabox__column --w-1/2">
          <input type="text" name="<?php echo esc_attr( $appointment_url_str ); ?>" value="<?php echo esc_attr( $group->appointment_url ); ?>" placeholder="<?php echo esc_html_x( 'URL', 'ui', 'memberpress-coachkit' ); ?>">
        </div>
      </div>

    </div>
    <div class="mpch-modal__footer">

      <?php if ( $group->id > 0 ) { ?>
      <button class="mpch-metabox__button --delete" type="button" data-toggle="popper"><?php echo esc_html_x( 'Delete', 'ui', 'memberpress-coachkit' ); ?></button>
      <div class="mepr-tooltip-content" data-content="popper" data-placement="right">
        <p><?php echo esc_html_x( 'Are you sure you want to delete?', 'ui', 'memberpress-coachkit' ); ?></p>
        <div class="flex-center">
          <button class="mpch-metabox__button --delete" data-close-popper type="button"><?php echo esc_html_x( 'No', 'ui', 'memberpress-coachkit' ); ?></button>
          <button class="mpch-metabox__button button" data-action="remove-group" data-group-id="<?php echo esc_attr( $group->id ); ?>" type="button"><?php echo esc_html_x( 'Yes', 'ui', 'memberpress-coachkit' ); ?></button>
        </div>
        <div id="arrow" data-popper-arrow></div>
      </div>
      <?php } ?>

      <button type="button" class="button button-primary mpch-modal__button" data-modal-submit data-action="save-group" data-group-id="<?php echo esc_attr( $group->id ); ?>">
        <svg class="" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
          <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <?php echo $group->id > 0 ? esc_html_x( 'Update', 'ui', 'memberpress-coachkit' ) : esc_html_x( 'Save', 'ui', 'memberpress-coachkit' ); ?>
      </button>
    </div>
  </div>
</div>
