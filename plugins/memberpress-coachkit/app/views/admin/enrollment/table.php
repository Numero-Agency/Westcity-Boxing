<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}
?>

<div class="wrap">
    <h1 class="mpch-admin-page-heading"><?php esc_html_e( 'Activity', 'memberpress-coachkit' ); ?>
    <select class="mpch-select2-data" style="min-width: 15rem; font-size: 14px">
      <option><?php printf( esc_html__( 'All %s', 'memberpress-coachkit' ), $label_for_clients); ?></option>
    </select>
    </h1>
  <?php
  echo $errors; // phpcs:ignore WordPress.Security.EscapeOutput
  ?>
  <div class="mpch-postbox__container mpch-activity-page mt-4">

    <div class="mpch-postbox">
      <div class="mpch-student-progress">
        <div class="mpch-metabox-white ui-sortable ui-sortable-handle">
          <div class="mpch-metabox-white__header" data-action="toggle-milestone">
            <h3 class="mpch-metabox__title"><?php printf( esc_html__( '%s Progress', 'memberpress-coachkit' ), $label_for_client); ?></h3>
          </div>
          <div class="mpch-metabox__content p-0">
            <div class="mpch-student-progress__filter-nav space-x-2">

              <select name="" id="" data-submit-query="coach">
                <option value="all"><?php printf( esc_html__( 'All %s', 'memberpress-coachkit' ), $label_for_coaches); ?></option>
                <?php
                foreach ( $coaches as $coach ) {
                  printf( '<option %s value="%d">%s</option>', selected( $coach->ID, $get_coach ), esc_attr( $coach->ID ), esc_html( $coach->name ) );
                }
                ?>
              </select>

              <select name="" id="" data-submit-query="program">
                <option value="all"><?php echo esc_html_x( 'All Programs', 'ui', 'memberpress-coachkit' ); ?></option>
                <?php
                foreach ( $programs as $program ) {
                  printf( '<option %s value="%d">%s</option>', selected( $program->ID, $get_program ), esc_attr( $program->ID ), esc_html( $program->post_title ) );
                }
                ?>
              </select>

            </div>
            <table class="widefat fixed striped table-view-list wp-list-table">
              <thead>
                <tr><?php $list_table->print_column_headers(); ?></tr>
              </thead>

              <tbody id="the-list">
                <?php $list_table->display_rows_or_placeholder(); ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="tablenav bottom">
          <?php
          $list_table->pagination( 'bottom' );
          ?>
        </div>
      </div>

    </div>


    <div class="mpch-postbox mpch-postbox__sidebar">
      <div class="mpch-recent-activities">
        <div class="mpch-metabox-white ui-sortable ui-sortable-handle" data-uuid="f67d670e-a9a2-4c81-a628-9e632165794f">
          <div class="mpch-metabox-white__header" data-action="toggle-milestone">
            <h3 class="mpch-metabox__title"><?php echo esc_html_x( 'Recent Activities', 'ui', 'memberpress-coachkit' ); ?></h3>
          </div>
          <div class="mpch-metabox__content custom-scrollbar">

            <div class="mpch-recent-activities__filter-nav">
              <select name="" id="activity-filter">
                <option value=""><?php echo esc_html_x( 'All Updates', 'ui', 'memberpress-coachkit' ); ?></option>
                <?php
                foreach ( $activity_types as $type ) {
                  printf( '<option value="%s">%s</option>', esc_attr( $type ), esc_html( str_replace( '-', ' ', $type ) ) );
                } ?>
              </select>
            </div>

            <div class="mpch-recent-activities__rows">
              <?php
              foreach ( $recent_activities['activities'] as $key => $activity ) {
                printf( '<div class="mpch-recent-activities__entry space-x-2"><p>%s</p><p class="mpch-recent-activities__time">%s</p></div>', wp_kses_post( $activity->message ), esc_html( $activity->date ) );
                // printf( '<div class="mpch-recent-activities__entry">%s</div>', wp_kses_post( $activity->message ) );
              } ?>
            </div>

            <div class="flex-center">
              <button id="activity-load-more-button" data-page="1" class="button button-link <?php echo $recent_activities['max_pages'] > 1 ? '' : 'hidden'; ?>">
                <?php echo esc_html_x( 'Load More', 'ui', 'memberpress-coachkit' ); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>



  </div>
</div>
