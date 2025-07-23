<div id="add-coach-form" class="mpch-modal mpch-modal-new-coach mfp-hide">
  <div class="mpch-modal__content">
    <div class="mpch-modal__header">
      <h3 class="mpch-modal__title"><?php printf( esc_html__( 'Add %s', 'memberpress-coachkit' ), $label_for_coach); ?></h3>
    </div>
    <div class="mpch-modal__body">

      <!-- Do not remove -->
      <div class="mpch-notice mpch-notice__error"></div>
      <div class="mpch-notice mpch-notice__success"></div>

      <div class="mpch-modal-search">
        <input type="text" class="mpch-modal-search__input" data-action="filter-coach" placeholder="Search users...">
      </div>

      <div class="mpch-card">
        <?php echo $users_list; //phpcs:ignore
        ?>
      </div>

    </div>
    <div class="mpch-modal__footer">
      <button class="button mpch-metabox__button --link" data-action="new-coach-modal" data-mfp-src="#new-coach-form"><?php printf( esc_html__( 'Add New %s', 'memberpress-coachkit' ), $label_for_coach); ?></button>
      <button type="button" class="button button-primary mpch-modal__button" data-action="add-coach">
        <svg class="" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
          <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <?php echo esc_html_x( 'Add', 'ui', 'memberpress-coachkit' ); ?>
      </button>
    </div>
  </div>
</div>
