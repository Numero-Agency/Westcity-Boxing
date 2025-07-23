<ul class="mpch-list-group list-group-flush">
  <?php
  if ( $users ) {
    foreach ( $users as $user ) {
      printf(
        '<li class="mpch-list-group__item">
                  <label for="mpch-add-coach-%1$d">
                    <input type="radio" name="mpch-coach-id[]" data-coach-id="%1$d" data-coach-title="%2$s" id="mpch-add-coach-%1$d" value="%1$d" data-toggle="check-coach">
                    <span>%2$s</span>
                  </label>
                </li>',
        esc_html( $user->ID ),
        esc_html( $user->first_name . ' ' . $user->last_name )
      );
    }
  } else {
    printf( '<li class="mpch-list-group__item">%s</li>', esc_html__( 'No users found', 'memberpress-coachkit' ) );
  }

  ?>
</ul>
