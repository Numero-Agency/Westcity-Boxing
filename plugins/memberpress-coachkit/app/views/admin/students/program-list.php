<div class="accordion">
  <!-- <ul class="mpch-list-group list-group-flush"> -->
  <?php
  if ( $programs ) { ?>
    <?php foreach ( $programs as $id => $program ) {; ?>
      <div class="accordion__item">
        <div class="accordion__title">
          <!-- <span class="accordion__arrow "> -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="accordion__arrow">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
          <!-- </span> -->
          <span class="accordion__title-text"><?php echo esc_html( ucfirst( $program->title ) ); ?></span>
        </div>
        <!-- <div class="accordion__content"> -->
        <ul class="accordion__content student-group-modal">
          <?php
          foreach ( $program->groups as $key => $group ) { ?>
            <li class="student-group-modal__list">

              <label <?php if ( ! $group->open ) { echo 'disabled data-hover="popper"'; } ?> class="student-group-modal__link space-x-2" data-select-student>
                <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M6 7C7.93437 7 9.5 5.43437 9.5 3.5C9.5 1.56562 7.93437 0 6 0C4.06563 0 2.5 1.56562 2.5 3.5C2.5 5.43437 4.06563 7 6 7ZM8.4 8H8.14062C7.49062 8.3125 6.76875 8.5 6 8.5C5.23125 8.5 4.5125 8.3125 3.85938 8H3.6C1.6125 8 0 9.6125 0 11.6V12.5C0 13.3281 0.671875 14 1.5 14H10.5C11.3281 14 12 13.3281 12 12.5V11.6C12 9.6125 10.3875 8 8.4 8ZM15 7C16.6562 7 18 5.65625 18 4C18 2.34375 16.6562 1 15 1C13.3438 1 12 2.34375 12 4C12 5.65625 13.3438 7 15 7ZM16.5 8H16.3813C15.9469 8.15 15.4875 8.25 15 8.25C14.5125 8.25 14.0531 8.15 13.6187 8H13.5C12.8625 8 12.275 8.18437 11.7594 8.48125C12.5219 9.30312 13 10.3938 13 11.6V12.8C13 12.8687 12.9844 12.9344 12.9812 13H18.5C19.3281 13 20 12.3281 20 11.5C20 9.56562 18.4344 8 16.5 8Z" fill="#8D8F94" />
                </svg>
                <input type="radio" name="mpch-student-group-id" value="<?php echo esc_attr( $group->id ); ?>" <?php if ( ! $group->open ) { echo 'disabled="disabled"'; } ?>>
                <span><?php echo esc_html( ucfirst( $group->title ) . ' (' . $group->coach . ')' ); ?></span>
                <?php if ( ! $group->open ) { ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="" width="20" height="14">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                  </svg>
                <?php } ?>
              </label>

                <div data-content="popper" class="mepr-tooltip-content" style="max-width: 320px;">
                  <p><?php echo esc_html_x( 'Not accepting enrollment. This cohort is at full capacity. Please go to the Program page to edit the cohort settings.', 'ui', 'memberpress-coachkit' ); ?></p>
                </div>

              <!-- <a class="student-group-modal__link space-x-2" href="#0" >
                <span><?php // echo esc_html( ucfirst( $group->title ) ); ?></span>
              </a> -->
            </li>
          <?php }
          ?>
        </ul>
        <!-- </div> -->
      </div>
      <?php
    }
  } else {
    printf( '<li class="mpch-list-group__item">%s</li>', esc_html__( 'No programs found', 'memberpress-coachkit' ) );
  } ?>
</div>
