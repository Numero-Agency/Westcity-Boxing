<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $rows ) ) {
  foreach ( $rows as $row ) {
    ?>
    <tr>
      <td class="student column-student has-row-actions column-primary">
        <a href="<?php echo esc_url( $row->student_url ); ?>"><?php echo esc_html( $row->student_name ); ?></a>
        <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
      </td>
      <td data-colname="Habit">
        <?php echo $row->off_track ? '<span class="mpch-badge --danger">' . esc_html__( 'Off Track', 'memberpress-coachkit' ) . '</span>' : ''; ?>
      </td>
      <td data-colname="Started">
        <?php echo esc_html( $row->started ); ?>
      </td>
      <td data-colname="Program">
        <?php printf( '%s </br> (%s)', esc_html( $row->program ), esc_html( $row->coach ) ); ?>
      </td>
      <td data-colname="Progress">
        <div class="flex-center">
          <div class="mpch-progress">
            <div class="mpch-progress__bar --success" style="width: <?php echo esc_attr( $row->progress ); ?>%;"></div>
          </div>
          <span class="mpch-progress__percent"><?php printf( '%s%%', esc_attr( $row->progress ) ); ?></span>
        </div>
        <?php // echo esc_html( $row->progress ) ?>
      </td>
    </tr>
    <?php
  }
} //End if
