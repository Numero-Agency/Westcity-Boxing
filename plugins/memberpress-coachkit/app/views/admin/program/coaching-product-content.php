<div class="product_options_page coaching">
  <div class="mpch-metabox mpch-program-pool ui-sortable ui-sortable-handle">
    <div class="mpch-metabox__header">
      <h3 class="mpch-metabox__title">
        <?php esc_html_e( 'Programs Pool', 'memberpress-coachkit' ); ?>
      </h3>
    </div>
    <div class="mpch-metabox__content">

      <?php if ( empty( $all_programs ) ) { ?>
        <p><?php echo esc_html__( 'No coaching programs found. Please add coaching programs to continue', 'memberpress-coachkit' ); ?></p>
      <?php } else { ?>
        <p><?php printf( esc_html_x( 'Assign new %s to a cohort within each of the Programs listed below.', 'ui', 'memberpress-coachkit' ), strtolower($label_for_clients)); ?></p>

        <template>
          <div class="mpch-metabox__row mpch-program-pool__row space-x-2">
            <div class="mpch-metabox__column">
              <select name="mpch-programs[{index}][program_id]" id="">
                <?php
                foreach ( $all_programs as $id => $title ) {
                  ?>
                  <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></option>
                  <?php
                }
                ?>
              </select>
            </div>
            <div class="mpch-metabox__column">
              <button class="mpch-metabox__button --delete" type="button" data-action="remove-membership-program">Delete</button>
            </div>
          </div>
        </template>

        <?php
        foreach ( $product_programs as $index => $product_program ) {
          ?>
          <div class="mpch-metabox__row mpch-program-pool__row space-x-2">
            <div class="mpch-metabox__column">
              <select name="mpch-programs[<?php echo esc_attr( $index ); ?>][program_id]">
              <option value=""><?php esc_html_e( 'Select Program', 'memberpress-coachkit' ); ?></option>
                <?php
                foreach ( $all_programs as $id => $title ) {
                  ?>
                  <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $product_program['id'] ); ?>><?php echo esc_html( $title ); ?></option>
                  <?php
                }
                ?>
              </select>
            </div>
            <div class="mpch-metabox__column">
              <button class="mpch-metabox__button --delete" type="button" data-action="remove-membership-program"><?php esc_html_e( 'Delete', 'memberpress-coachkit' ); ?></button>
            </div>
          </div>
          <?php
        }
        ?>
        <div class="mpch-metabox__row">
          <div class="mpch-metabox__button-wrapper">
            <button class="mpch-metabox__button --secondary" type="button" data-action="add-membership-program" aria-haspopup="true">
              <span><?php esc_html_e( 'Add New', 'memberpress-coachkit' ); ?></span>
            </button>
          </div>
        </div>
      <?php } ?>

    </div>
  </div>
</div>
