<div class="mpch-metabox mpch-metabox__habit ui-sortable" data-uuid="<?php echo esc_attr( $habit->uuid ); ?>">
  <div class="mpch-metabox__header ui-sortable-handle" data-toggle="habit">
    <h3 class="mpch-metabox__title">
      <?php
      esc_html_e( 'Habit ', 'memberpress-coachkit' );
      printf( '<span>%s</span>', esc_html( $index ) );
      ?>
    </h3>
    <button class="mpch-metabox__button --delete" type="button" data-toggle data-action="remove-habit" style="display: none;"><?php echo esc_html_x( 'Remove', 'ui', 'memberpress-coachkit' ); ?></button>
  </div>
  <div class="mpch-metabox__content">

    <div class="mpch-metabox__row">
      <div class="mpch-metabox__column">
        <label><?php echo esc_html_x( 'Habit Name', 'ui', 'memberpress-coachkit' ); ?></label>
        <input type="text" name="<?php echo esc_attr( $title_str ); ?>" value="<?php echo esc_attr( $habit->title ); ?>" id="">
      </div>
    </div>

    <div class="mpch-metabox__row --space-x-2">
      <div class="mpch-metabox__column --w-1/5">
        <label for=""><?php echo esc_html_x( 'Repeat', 'ui', 'memberpress-coachkit' ); ?></label>
        <select name="<?php echo esc_attr( $repeat_interval_str ); ?>" class="mpch-habit-interval">
          <option value="daily" data-unit="<?php echo esc_attr_x( 'day(s)', 'ui', 'memberpress-coachkit' ); ?>" <?php selected( $habit->repeat_interval, 'daily' ); ?>><?php echo esc_html_x( 'Daily', 'ui', 'memberpress-coachkit' ); ?></option>
          <option value="weekly" data-unit="<?php echo esc_attr_x( 'week(s)', 'ui', 'memberpress-coachkit' ); ?>" <?php selected( $habit->repeat_interval, 'weekly' ); ?>><?php echo esc_html_x( 'Weekly', 'ui', 'memberpress-coachkit' ); ?></option>
        </select>
      </div>
      <div class="mpch-metabox__column --flex-shrink-2">
        <label for="" class="invisible"><?php echo esc_html_x( 'Goal Due', 'ui', 'memberpress-coachkit' ); ?></label>
        <div class="mpch-habit__interval space-x">
          <span><?php echo esc_html_x( 'Every', 'ui', 'memberpress-coachkit' ); ?></span>
          <input name="<?php echo esc_attr( $repeat_length_str ); ?>" type="number" min="1" value="<?php echo esc_attr( $habit->repeat_length ); ?>">
          <span class="mpch-habit-interval-unit">day(s)</span>
          <span><?php echo esc_html_x( 'on:', 'ui', 'memberpress-coachkit' ); ?></span>
        </div>
      </div>
      <div class="mpch-metabox__column --w-12/2">
        <label for="" class="invisible"><?php echo esc_html_x( 'Repeat Days', 'ui', 'memberpress-coachkit' ); ?></label>

        <div class="mpch-habit__days">
          <?php
          foreach ( $repeat_days as $key => $day ) {
            ?>
            <input type="checkbox" name='<?php echo esc_attr( $repeat_days_str ) . '[]'; ?>' value="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $repeat_days_str . '-' . $key ); ?>" <?php echo ( in_array( $key, explode( ',', $habit->repeat_days ) ) ) ? 'checked="checked"' : ''; ?> autocomplete="off">
            <label for="<?php echo esc_attr( $repeat_days_str . '-' . $key ); ?>"><?php echo esc_html( $day ); ?></label>
            <?php
          }
          ?>
        </div>
      </div>
    </div>

    <!-- <div class="mpch-metabox__row">
      <div class="mpch-metabox__column --w-1/3 --flex-grow-0">
        <label for=""><?php // echo esc_html_x( 'Start Habit', 'ui', 'memberpress-coachkit' ); ?></label>
        <select name="<?php // echo esc_attr( $timing_str ); ?>" id="">
          <option value=""><?php // echo esc_html_x( 'Select', 'ui', 'memberpress-coachkit' ); ?></option>
          <option value="after_program_starts" <?php // selected( $habit->timing, 'after_program_starts' ); ?>><?php // echo esc_html_x( 'After Program Starts', 'ui', 'memberpress-coachkit' ); ?></option>
        </select>
      </div>
    </div> -->

    <div class="space-x-2 mpch-metabox__row">
      <label>
        <input type="checkbox" name="<?php echo esc_attr( $enable_checkin_str ); ?>" id="" data-action="add-habit-checkin" <?php checked( $habit->enable_checkin, '1' ); ?> value="1">
        <?php echo esc_html_x( 'Check In', 'ui', 'memberpress-coachkit' ); ?>
      </label>
    </div>

    <div class="mpch-metabox__row">
      <div class="mpch-metabox__button-wrapper">
        <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) )  : ?>
        <button class="mpch-metabox__button --secondary" type="button" data-toggle="popper" aria-haspopup="true">
          <span><?php echo esc_html_x( 'Add New', 'ui', 'memberpress-coachkit' ); ?></span>
          <span class="dashicons dashicons-arrow-down-alt2"></span>
        </button>
        <nav class="mepr-tooltip-content" data-content="popper" role="menu">
          <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) )  : ?>
          <a href="#" data-action="toggle-download" data-mfp-src="#download-popup"><?php echo esc_html_x( 'Download', 'ui', 'memberpress-coachkit' ); ?></a>
          <?php endif ?>
        </nav>
        <?php endif ?>
      </div>
    </div>

  </div>

  <div class="mpch-metabox__footer <?php echo esc_attr( $footer_css ); ?>">
    <?php
    echo $checkins_html; // phpcs:ignore WordPress.Security.EscapeOutput
    echo $downloads_html; // phpcs:ignore WordPress.Security.EscapeOutput
    ?>
  </div>

  <input type="hidden" name="<?php echo esc_attr( $uuid_str ); ?>" value="<?php echo esc_attr( $habit->uuid ); ?>">
</div>
