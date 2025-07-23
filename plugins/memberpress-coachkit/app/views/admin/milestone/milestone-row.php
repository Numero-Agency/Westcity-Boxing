<div class="mpch-metabox ui-sortable" data-uuid="<?php echo esc_attr( $milestone->uuid ); ?>">
  <div class="mpch-metabox__header ui-sortable-handle" data-action="toggle-milestone">
    <h3 class="mpch-metabox__title">
      <?php
      esc_html_e( 'Milestone ', 'memberpress-coachkit' );
      printf( '<span>%s</span>', esc_html( $index ) );
      ?>
    </h3>
    <button class="mpch-metabox__button --delete" type="button" data-toggle data-action="remove-milestone" style="display: none;"><?php echo esc_html_x( 'Remove', 'ui', 'memberpress-coachkit' ); ?></button>
  </div>
  <div class="mpch-metabox__content">

    <div class="mpch-metabox__row">
      <div class="mpch-metabox__column">
        <label><?php echo esc_html_x( 'Goal Name', 'ui', 'memberpress-coachkit' ); ?></label>
        <input type="text" name="<?php echo esc_attr( $title_str ); ?>" value="<?php echo esc_attr( $milestone->title ); ?>">
      </div>
    </div>

    <div class="space-x-2 mpch-metabox__row">
      <div class="mpch-metabox__column --w-1/6">
        <label for=""><?php echo esc_html_x( 'Goal Due', 'ui', 'memberpress-coachkit' ); ?></label>
        <input name="<?php echo esc_attr( $due_length_str ); ?>" type="number" min="1" value="<?php echo esc_attr( $milestone->due_length ); ?>">
      </div>
      <div class="mpch-metabox__column --w-1/5">
        <label class="invisible" for=""><?php echo esc_html_x( 'Interval', 'ui', 'memberpress-coachkit' ); ?></label>
        <select name="<?php echo esc_attr( $due_unit_str ); ?>">
          <option value="day" <?php selected( $milestone->due_unit, 'day' ); ?>><?php echo esc_html_x( 'Days', 'ui', 'memberpress-coachkit' ); ?></option>
          <option value="week" <?php selected( $milestone->due_unit, 'week' ); ?>><?php echo esc_html_x( 'Weeks', 'ui', 'memberpress-coachkit' ); ?></option>
          <option value="month" <?php selected( $milestone->due_unit, 'month' ); ?>><?php echo esc_html_x( 'Months', 'ui', 'memberpress-coachkit' ); ?></option>
        </select>
      </div>
      <div class="mpch-metabox__column --w-1/3">
        <label class="invisible" for=""><?php echo esc_html_x( 'Start Goal', 'ui', 'memberpress-coachkit' ); ?></label>
        <select name="<?php echo esc_attr( $timing_str ); ?>" class="mpch-metabox__start-goal text-select">
          <option value="after_program_starts"<?php selected( $milestone->timing, 'after_program_starts' ); ?>>
            <?php echo esc_html_x( 'After Program Starts', 'ui', 'memberpress-coachkit' ); ?>
          </option>
          <option value="after_previous_milestone" <?php selected( $milestone->timing, 'after_previous_milestone' ); ?>>
            <?php echo esc_html_x( 'After Previous Milestone is due', 'ui', 'memberpress-coachkit' ); ?>
          </option>
        </select>
      </div>
    </div>

    <div class="space-x-2 mpch-metabox__row">
      <label>
        <input type="checkbox" name="<?php echo esc_attr( $enable_checkin_str ); ?>" data-action="add-milestone-checkin" <?php checked( $milestone->enable_checkin, '1' ); ?> value="1">
        <?php echo esc_html_x( 'Check In', 'ui', 'memberpress-coachkit' ); ?>
      </label>
    </div>

    <div class="mpch-metabox__row">
      <div class="mpch-metabox__button-wrapper">
        <?php if ( is_plugin_active( 'memberpress-courses/main.php' ) || is_plugin_active( 'memberpress-downloads/main.php' ) )  : ?>
        <button class="mpch-metabox__button --secondary" type="button" data-toggle="popper" aria-haspopup="true">
          <span><?php echo esc_html_x( 'Add New', 'ui', 'memberpress-coachkit' ); ?></span>
          <span class="dashicons dashicons-arrow-down-alt2"></span>
        </button>
        <nav class="mepr-tooltip-content" data-content="popper" role="menu">
          <?php if ( is_plugin_active( 'memberpress-courses/main.php' ) )  : ?>
          <a href="#0" data-action="toggle-course" data-mfp-src="#course-popup"><?php echo esc_html_x( 'Course', 'ui', 'memberpress-coachkit' ); ?></a>
          <?php endif ?>
          <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) )  : ?>
          <a href="#0" data-action="toggle-download" data-mfp-src="#download-popup"><?php echo esc_html_x( 'Download', 'ui', 'memberpress-coachkit' ); ?></a>
          <?php endif ?>
        </nav>
        <?php endif ?>
      </div>
    </div>

  </div>
  <button class="mpch-metabox__more mpch-metabox__button" type="button" data-action="add-milestone">
    <i class="mp-icon mp-icon-plus-circled mp-32"></i>
  </button>

  <div class="mpch-metabox__footer <?php echo esc_attr( $footer_css ); ?>">
    <?php echo $courses_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo $downloads_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo $checkins_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>

  <input type="hidden" name="<?php echo esc_attr( $uuid_str ); ?>" value="<?php echo esc_attr( $milestone->uuid ); ?>">
</div>
