<div id="enroll-student-form" class="mpch-modal mpch-modal-new-coach mfp-hide" data-student-id="<?php echo esc_attr( $student->ID ); ?>">
  <div class="mpch-modal__content">
    <div class="mpch-modal__header">
      <h3 class="mpch-modal__title"><?php printf( esc_html__( 'Add %s to Program', 'memberpress-coachkit' ), $label_for_client); ?></h3>
    </div>
    <div class="mpch-modal__body">

      <!-- Do not remove -->
      <div class="mpch-notice mpch-notice__error"></div>
      <div class="mpch-notice mpch-notice__success"></div>

      <div class="mpch-modal-search">
        <input type="text" class="mpch-modal-search__input" data-filter="programs" placeholder="Search programs...">
      </div>


      <div class="mpch-card">
        <?php echo $program_list; //phpcs:ignore ?>
      </div>
      <p class="mpch-pagination">
        <?php echo esc_html( $program_count ); ?>
      </p>

    </div>
    <div class="mpch-modal__footer">
      <button type="button" class="button button-primary mpch-modal__button" data-enroll="student">
        <svg class="" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
          <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <?php echo esc_html_x( 'Add', 'ui', 'memberpress-coachkit' ); ?>
      </button>
    </div>
  </div>
</div>
