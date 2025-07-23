<div class="mpch-metabox mpch-metabox__group ui-sortable" data-group-id="<?php echo esc_attr( $group->id ); ?>">
  <div class="mpch-metabox__header ui-sortable-handle" data-action="collapse-group">
    <h3 class="mpch-metabox__title">
      <?php
        esc_html_e( 'Cohort', 'memberpress-coachkit' );
        printf( '<span>&nbsp; %s</span>', esc_html( $index ) );
      ?>
      <?php if ( $group->has_date_conflict ) { ?>

        <span id="mpch-tooltip-<?php echo esc_attr( $group->id ); ?>" class="mpch-tooltip">
          <span class="dashicons dashicons-warning warning-icon"></span>
          <span class="mepr-data-title mepr-hidden"><?php echo esc_html__( 'Conflict Detected', 'memberpress-coachkit' ); ?></span>
          <span class="mepr-data-info mepr-hidden"><?php echo esc_html__( 'Please ensure that the cohort\'s start date and end date are in sync with the accumulated periods represented by the milestones', 'memberpress-coachkit' ); ?></span>
        </span>

      <?php } ?>
      <span class="dashicons dashicons-arrow-down"></span>
    </h3>
  </div>

  <div class="mpch-metabox__content">
    <div class="mpch-metabox__row">
      <div class="mpch-metabox__column">
      <dl>
        <dt><?php echo esc_html_x( 'Cohort Name', 'ui', 'memberpress-coachkit' ); ?></dt>
        <dd>
          <?php echo esc_html( $group->title ); ?>
        </dd>
        <dt><?php echo esc_html($label_for_coach); ?></dt>
        <dd><?php echo esc_html( $group->coach()->fullname ); ?></dd>
      </dl>
      </div>
    </div>

    <div class="mpch-metabox__row">
      <div class="mpch-metabox__button-wrapper">
        <button class="mpch-metabox__button --secondary" data-action="edit-group" data-group-id="<?php echo esc_attr( $group->id ); ?>" data-mfp-src="#group-popup" type="button" aria-haspopup="true">
          <span>Edit </span>
        </button>
      </div>
    </div>

  </div>
</div>
