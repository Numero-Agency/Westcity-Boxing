<div class="wrap">
  <?php if ( $from_activities_page ) { ?>
    <p>
      <?php printf( '<a href="%s">%s</a>', esc_url( $activities_page_url ), esc_html__( 'Return to activities page', 'memberpress-coachkit' ) ); ?>
    </p>
  <?php } ?>
  <h1 class="wp-heading-inline"><?php echo esc_html( $student->fullname ); ?></h1>
  <a href="<?php echo esc_url( $edit_user_url ); ?>" class="page-title-action"><?php esc_html_e( 'Edit User', 'memberpress-coachkit' ); ?></a>
  <a href="#0" class="page-title-action" data-trigger="enroll-student-modal" data-mfp-src="#enroll-student-form"><?php esc_html_e( 'Assign to Cohort', 'memberpress-coachkit' ); ?></a>
  <hr class="wp-header-end">
  <form name="post" action="post.php" method="post" id="post">
    <div id="poststuff">

      <div class="mpch-postbox__container">
        <div class="mpch-postbox">

          <div id="normal-sortables">

            <div class="postbox mpch-coach-metabox">
              <div class="postbox-header">
                <h2><?php echo esc_html_x( 'Enrollments', 'ui', 'memberpress-coachkit' ); ?></h2>
              </div>
              <div class="mpch-coach-metabox__inside">
                <table class="widefat fixed striped table-view-list wp-list-table">
                  <thead>
                    <tr>
                      <th scope="col" id="title" class="mpch-coach-metabox__th column-title column-primary"><?php echo esc_html_x( 'Name', 'ui', 'memberpress-coachkit' ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><span class="hidden"><?php echo esc_html_x( 'Status', 'ui', 'memberpress-coachkit' ); ?></span></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html_x( 'Started', 'ui', 'memberpress-coachkit' ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html( $label_for_coach ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html_x( 'Progress', 'ui', 'memberpress-coachkit' ); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if ( empty( $enrollments ) ) {
                      printf( '<tr><td colspan="5">%s</td></tr>', esc_html__( 'No enrollments found.', 'memberpress-coachkit' ) );
                    }
                    ?>
                    <?php
                    foreach ( $enrollments as $enrollment ) { ?>
                      <tr id="record_<?php echo esc_attr($enrollment->id); ?>">
                        <td class="title column-title has-row-actions column-primary">
                          <?php echo esc_html( $enrollment->title ); ?>
                          <button type="button" class="toggle-row"><span class="screen-reader-text"><?php echo esc_html_x( 'Show more details', 'ui', 'memberpress-coachkit' ); ?></span></button>
                          <div class="mepr-row-actions" style="visibility: hidden;">
                            <a href="" class="remove-enrollment-row" title="<?php _e('Delete Enrollment', 'memberpress-coachkit'); ?>" data-value="<?php echo $enrollment->id; ?>"><?php _e('Unenroll', 'memberpress-coachkit'); ?></a>
                          </div>
                        </td>
                        <td class="status column-status" data-colname="Status">
                          <?php echo $enrollment->off_track ? '<span class="mpch-badge --danger">' . esc_html__( 'Off Track', 'memberpress-coachkit' ) . '</span>' : ''; ?> </td>
                        <td class="started column-started" data-colname="Started"><?php echo esc_html( $enrollment->started ); ?></td>
                        <td class="last-coach column-last-coach" data-colname="Coach"><?php echo esc_html( $enrollment->coach ); ?></td>
                        <td class="progress column-progress" data-colname="Progress">
                          <div class="flex-center">
                            <div class="mpch-progress">
                              <div class="mpch-progress__bar --success" style="width: <?php echo esc_html( $enrollment->progress ); ?>%;"></div>
                            </div>
                            <span class="mpch-progress__percent"><?php echo esc_html( $enrollment->progress ); ?>%</span>
                          </div>
                        </td>
                      </tr>
                    <?php }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="postbox mpch-coach-metabox">
              <div class="postbox-header">
                <h2><?php echo esc_html_x( 'Active Memberships', 'ui', 'memberpress-coachkit' ); ?></h2>
              </div>
              <div class="mpch-coach-metabox__inside">
                <table class="widefat fixed striped table-view-list wp-list-table">
                  <thead>
                    <tr>
                      <th scope="col" id="id" class="mpch-coach-metabox__th column-primary"><?php echo esc_html_x( 'ID', 'ui', 'memberpress-coachkit' ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-membership"><?php echo esc_html_x( 'Membership', 'ui', 'memberpress-coachkit' ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html_x( 'Term', 'ui', 'memberpress-coachkit' ); ?></th>
                      <!-- <th scope="col" class="mpch-coach-metabox__th column-date"><?php // echo esc_html_x( 'URL', 'ui', 'memberpress-coachkit' ); ?></th> -->
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    if ( empty( $memberships ) ) {
                      printf( '<tr><td colspan="4" class="p-2">%s</td></tr>', esc_html__( 'No active memberships found.', 'memberpress-coachkit' ) );
                    }
                    ?>

                    <?php
                    foreach ( $memberships as $membership ) { ?>
                      <tr>
                        <td class="column-id has-row-actions column-primary">
                          <?php echo esc_html( $membership->id ); ?>
                          <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                        </td>
                        <td class="program column-membership " data-colname="Membership">
                          <a href="<?php echo esc_url( $membership->url ); ?>"><?php echo esc_html( $membership->product ); ?></a>
                        </td>
                        <td class="assigned column-assigned" data-colname="Assigned"><?php echo wp_kses_post( $membership->terms ); ?></td>
                        <!-- <td class="column-assigned" data-colname="URL"><?php echo esc_html( $membership->url ); ?></td> -->
                      </tr>
                    <?php }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="postbox mpch-notes-metabox">
              <div class="postbox-header">
                <h2><?php echo esc_html_x( 'Notes', 'ui', 'memberpress-coachkit' ); ?></h2>
              </div>
              <div class="mpch-coach-metabox__inside">

                <div class="mpch-notes__content">
                  <table class="widefat fixed striped table-view-list notes wp-list-table notes-box">
                    <tbody>
                      <?php if ( empty( $notes ) ) {
                        printf( '<tr><td>%s</td></tr>', esc_html__( 'No notes found.', 'memberpress-coachkit' ) );
                      }
                      foreach ( $notes as $note ) {
                        echo wp_kses_post( $note );
                      } ?>
                    </tbody>
                  </table>
                </div>

                <?php // _WP_Editors::editor_settings();
                ?>
                <div class="mpch-notes__editor hidden">
                  <?php
                  $content            = '';
                  $custom_editor_id   = 'add-note';
                  $custom_editor_name = 'editorname';
                  $args               = array(
                    'media_buttons' => false, // This setting removes the media button.
                    // 'textarea_name' => $custom_editor_name, // Set custom name.
                    'textarea_rows' => get_option( 'default_post_edit_rows', 10 ), // Determine the number of rows.
                    'tinymce'       => false, // Remove view as HTML button.
                  );
                  wp_editor( $content, $custom_editor_id, $args );
                  ?>
                </div>

                <p class="hide-if-no-js mpch-notes__create-buttons hidden">
                  <button type="button" data-trigger="create-note" data-student-id="<?php echo esc_attr( $_GET['id'] ); ?>" class="button primary button-primary">Create Note</button>
                  <button type="button" data-trigger="hide-note-editor" class="button">Cancel</button>
                </p>

                <p class="hide-if-no-js mpch-notes__button-new">
                  <button type="button" data-trigger="add-new-note" class="button">Add Note</button>
                </p>

              </div>
            </div>
          </div>

        </div>
        <div class="mpch-postbox mpch-postbox__sidebar">
          <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">

            <div id="classic-editor-switch-editor" class="postbox">
              <div class="postbox-header">
                <h2 class="hndle ui-sortable-handle">Recent Activities</h2>
              </div>
              <div class="inside mpch-recent-activities__wrapper custom-scrollbar">
                <div class="mpch-recent-activities__rows">
                  <?php
                  if ( empty( $recent_activities['activities'] ) ) {
                    printf( '<span>%s</span>', esc_html__( 'No recent activities', 'memberpress-coachkit' ) );
                  }

                  foreach ( $recent_activities['activities'] as $key => $activity ) {
                    printf( '<div class="mpch-recent-activities__entry space-x-2"><p class="truncate">%s</p><p class="mpch-recent-activities__time">%s</p></div>', wp_kses_post( $activity->message ), esc_html( $activity->date ) );
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- /post-body -->
      <br class="clear">
    </div>
    <!-- /poststuff -->
  </form>
</div>
