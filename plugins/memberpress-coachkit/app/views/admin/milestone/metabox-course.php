<div class="mpch-metabox-white mpch-metabox-course <?php echo esc_attr( $wrapper_css ); ?>">
  <div class="mpch-metabox-white__header mpch-metabox__header ui-sortable-handle" data-action="collapse-course">
    <h3 class="mpch-metabox__title">
      <?php
      esc_html_e( 'Courses', 'memberpress-coachkit' );
      ?>
    </h3>
  </div>
  <div class="mpch-metabox__content">
    <div class="mpch-metabox__row">
      <div class="mpch-metabox__column">
        <ul class="mpch-list-group --theme-input">
          <template> <!-- Do not delete. JS will use this to populate the list -->
            <li class="mpch-list-group__item">
              <span class="mpch-list-group__title">{course_title}</span>
              <button class="mpch-metabox__button --delete" type="button" data-action="remove-milestone-course"><?php echo esc_html_x( 'Delete', 'ui', 'memberpress-coachkit' ); ?></button>
              <input type="hidden" value="{course_id}" name="<?php echo esc_attr( $course_id_str ); ?>">
            </li>
          </template>
          <?php foreach ( $courses as $course ) { ?>
            <li class="mpch-list-group__item">
              <span class="mpch-list-group__title"><?php echo esc_html( $course->post_title ); ?></span>
              <button class="mpch-metabox__button --delete" type="button" data-action="remove-milestone-course"><?php echo esc_html_x( 'Delete', 'ui', 'memberpress-coachkit' ); ?></button>
              <input type="hidden" value="<?php echo esc_attr( $course->ID ); ?>" name="<?php echo esc_attr( $course_id_str ); ?>">
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>

  </div>
</div>
