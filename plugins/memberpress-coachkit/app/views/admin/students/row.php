<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $records ) ) {
  foreach ( $records as $key => $rec ) {
    ?>
    <tr id="record_<?php echo esc_attr( $rec->id ); ?>">
    <?php
    foreach ( $columns as $column_name => $column_display_name ) {

      // Style attributes for each col
      $class = "class=\"{$column_name} column-{$column_name}\"";
      $style = '';
      if ( in_array( $column_name, $hidden, true ) ) {
        $style = ' style="display:none;"';
      }
      $attributes = $class . $style;

      if ( 'title' === $column_name ) {
        $data = sprintf( '<strong><a href="%s" title="%s">%s</a></strong>', esc_url( $rec->edit_link ), esc_attr__( "View member's profile", 'memberpress-coachkit' ), esc_html( $rec->$column_name ) );
      } else {
        $data = esc_html( $rec->$column_name );
      }

      printf( '<td %s>%s</td>', wp_kses_data( $attributes ), wp_kses_post( $data ) );

    }
    ?>
    </tr>
    <?php
  } //End foreach
} //End if
