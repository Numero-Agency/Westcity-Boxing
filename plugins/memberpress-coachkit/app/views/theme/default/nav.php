<?php if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
} ?>

<div class="mp_wrapper mpch-container">
  <div id="mpch-account-nav" class="space-x-2">
    <span class="mepr-nav-item <?php echo esc_attr( $active_class( 'coaching' ) ); ?>">
      <a href="<?php echo esc_url( $profile_url ); ?>" id="mepr-account-home"><?php esc_html_e( 'Account', 'memberpress-coachkit' ); ?></a>
    </span>
    <?php if ( $is_coach ) : ?>
      <span class="mepr-nav-item <?php echo esc_attr( $active_class( 'coaching_students' ) ); ?>">
      <a href="<?php echo esc_url( $student_url ); ?>" id="mepr-coaching-students"><?php echo esc_html( $label_for_clients ); ?></a>
      </span>
    <?php elseif ( $is_student ) : ?>
      <span class="mepr-nav-item <?php echo esc_attr( $active_class( 'coaching_enrollments' ) ); ?>">
      <a href="<?php echo esc_url( $enrollment_url ); ?>" id="mepr-coaching-enrollments"><?php esc_html_e( 'Enrollments', 'memberpress-coachkit' ); ?></a>
    </span>
    <?php endif; ?>
    <?php if ( $view->enable_messaging ) { ?>
    <span class="mepr-nav-item <?php echo esc_attr( $active_class( 'coaching_messages' ) ); ?>">
      <a href="<?php echo esc_url( $messages_url ); ?>" id="mepr-coaching-messages"><?php esc_html_e( 'Messages', 'memberpress-coachkit' ); ?></a>
    </span>
    <?php } ?>
    <?php if ( ! $view->is_readylaunch ) { ?>
      <span class="mepr-nav-item <?php echo esc_attr( $active_class( 'coaching_notifications' ) ); ?>">
        <a href="<?php echo esc_url( $notifications_url ); ?>" id="mepr-coaching-notifications"><?php esc_html_e( 'Notifications', 'memberpress-coachkit' ); ?></a>
      </span>
    <?php } ?>
  </div>
</div>

<?php
if ( isset( $_REQUEST['errors'] ) ) {
  $errors = [ esc_html( sanitize_text_field( wp_unslash( $_REQUEST['errors'] ) ) ) ];
  View::render( '/shared/errors', get_defined_vars() );
}
