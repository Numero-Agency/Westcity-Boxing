<div id="course-popup" class="mpch-modal mfp-hide">
  <div class="mpch-modal__content">
    <div class="mpch-modal__header">
      <h3 class="mpch-modal__title"><?php echo esc_html_x( 'Add Course to Milestone', 'ui', 'memberpress-coachkit' ); ?></h3>
    </div>
    <div class="mpch-modal__body">

      <div class="mpch-card">
        <ul class="mpch-list-group list-group-flush">
          <?php

          foreach ( $courses as $course ) {
            printf(
              '<li class="mpch-list-group__item">
                <label for="mpch-milestone-add-course-%1$d">
                  <input type="checkbox" name="mpch-milestone-add-course[]" data-course-id="%1$d" data-course-title="%2$s" id="mpch-milestone-add-course-%1$d" value="%1$d" data-action="highlight-course">%2$s
                </label>
              </li>',
              esc_html( $course->ID ),
              esc_html( $course->post_title )
            );
          }

          if ( empty( $courses ) ) {
            printf( '<li class="mpch-list-group__item">%s</li>', esc_html__( 'No courses found. Please add a course and try again.', 'memberpress-coachkit' ) );
          }

          ?>
        </ul>
      </div>
    </div>
    <div class="mpch-modal__footer">
      <button type="button" class="button button-primary mpch-modal__button" data-action="add-course">
        <svg class="" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
          <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <?php echo esc_html_x( 'Add Course', 'ui', 'memberpress-coachkit' ); ?>
      </button>
    </div>
  </div>
</div>
