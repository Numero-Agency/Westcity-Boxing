<div class="wrap">
  <h1 class="wp-heading-inline"><?php echo esc_html( $coach->fullname ); ?></h1>
  <a href="<?php echo esc_url( $edit_user_url ); ?>" class="page-title-action"><?php esc_html_e( 'Edit User', 'memberpress-coachkit' ); ?></a>
  <a href="#0" class="page-title-action" data-action="assign-coach-modal" data-mfp-src="#assign-coach-form">Assign Cohort</a>
  <hr class="wp-header-end">
  <form name="post" action="post.php" method="post" id="post">
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">

        <div class="postbox-container">
          <div id="normal-sortables">
            <div class="postbox mpch-coach-metabox">
              <div class="postbox-header">
                <h2><?php echo esc_html_x( 'Cohorts', 'ui', 'memberpress-coachkit' ); ?></h2>
              </div>
              <div class="mpch-coach-metabox__inside">
                <table class="widefat fixed striped table-view-list wp-list-table">
                  <thead>
                    <tr>
                      <th scope="col" id="title" class="mpch-coach-metabox__th column-title column-primary"><?php echo esc_html_x( 'Name', 'ui', 'memberpress-coachkit' ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html_x( 'Assigned', 'ui', 'memberpress-coachkit' ); ?></th>
                      <th scope="col" class="mpch-coach-metabox__th column-date"><?php printf( esc_html__( 'Active %s', 'memberpress-coachkit' ), $label_for_clients); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  if ( empty( $groups ) ) {
                    printf( '<tr><td colspan="3">%s</td></tr>', esc_html__( 'No cohorts found.', 'memberpress-coachkit' ) );
                  }
                  foreach ( $groups as $key => $group ) { ?>
                      <tr>
                        <td class="title column-title has-row-actions column-primary">
                          <?php // echo esc_html( $group->title ); ?>
                          <?php printf( '%s (%s)', esc_html( $group->title ), esc_html( $group->program ) ); ?>
                          <button type="button" class="toggle-row"><span class="screen-reader-text"><?php echo esc_html_x( 'Show more details', 'ui', 'memberpress-coachkit' ); ?></span></button>
                        </td>
                        <td class="assigned column-assigned" data-colname="Assigned"><?php echo esc_html( $group->created_at ); ?></td>
                        <td class="active-students column-active-students" data-colname="Active students"><?php echo esc_html( $group->active_students ); ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="postbox mpch-coach-metabox">
              <div class="postbox-header">
                <h2><?php printf( esc_html__( 'Top 5 Active %s', 'memberpress-coachkit' ), $label_for_clients); ?></h2>
              </div>
              <div class="mpch-coach-metabox__inside">
                <table class="widefat fixed striped table-view-list wp-list-table">
                  <thead>
                      <tr>
                        <th scope="col" id="title" class="mpch-coach-metabox__th column-title column-primary"><?php echo esc_html( $label_for_client ); ?></th>
                        <th scope="col" class="mpch-coach-metabox__th column-date"><span class="hidden"><?php echo esc_html_x( 'Status', 'ui', 'memberpress-coachkit' ); ?></span></th>
                        <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html_x( 'Last Update', 'ui', 'memberpress-coachkit' ); ?></th>
                        <th scope="col" class="mpch-coach-metabox__th column-date"><?php echo esc_html_x( 'Progress', 'ui', 'memberpress-coachkit' ); ?></th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php
                    if ( empty( $students ) ) {
                      printf( '<tr><td colspan="4">%s</td></tr>', esc_html__( 'No students found.', 'memberpress-coachkit' ) );
                    }
                    foreach ( $students as $key => $student ) { ?>
                      <tr>
                        <td class="title column-title has-row-actions column-primary">
                          <a href="<?php echo esc_url( $student->url ); ?>"><?php echo esc_html( $student->name ); ?></a>
                          <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                        </td>
                        <td class="status column-status" data-colname="Status">
                        <?php echo $student->off_track ? '<span class="mpch-badge --danger">' . esc_html__( 'Off Track', 'memberpress-coachkit' ) . '</span>' : ''; ?>
                        </td>
                        <td class="last-update column-last-update" data-colname="Last Update"><?php echo esc_html( $student->last_update ); ?></td>
                        <td class="progress column-progress" data-colname="Progress">
                          <div class="flex-center">
                            <div class="mpch-progress">
                              <div class="mpch-progress__bar --success" style="width: <?php echo esc_attr( $student->progress ); ?>%;"></div>
                            </div>
                            <span class="mpch-progress__percent"><?php echo esc_html( $student->progress ); ?>%</span>
                          </div>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
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
