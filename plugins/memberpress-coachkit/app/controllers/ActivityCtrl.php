<?php

namespace memberpress\coachkit\controllers;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use stdClass;
use memberpress\coachkit\lib;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Activity;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\lib\ActivityTable;
use memberpress\coachkit\helpers\CoachHelper;
use memberpress\coachkit\helpers\StudentHelper;

/**
 * Controller for Program
 */
class ActivityCtrl extends lib\BaseCtrl {
  /**
   * Load class hooks here
   *
   * @return void
   */
  public function load_hooks() {
    $hook = 'toplevel_page_memberpress-coachkit';

    add_filter( 'set_screen_option_mp_activity_perpage', array( $this, 'setup_screen_options' ), 10, 3 );
    add_filter( "manage_{$hook}_columns", array( $this, 'get_columns' ), 0 );
    add_action( "load-{$hook}", array( $this, 'add_screen_options' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    add_action( 'wp_ajax_load_more_activities', array( $this, 'handle_load_more_activities' ) );
    add_action( 'wp_ajax_load_filtered_activities', array( $this, 'handle_load_filtered_activities' ) );
    add_action( 'wp_ajax_filter_notifications', array( $this, 'handle_filter_notifications' ) );
    add_action( 'mpch_enrollment_created', array( $this, 'record_program_started' ) );
    // add_action( 'mpch_enrollment_removed', array( $this, 'record_enrollment_removed' ) );
    add_action( 'mpch_message_created', array( $this, 'record_message_created' ) );
    add_action( 'mpch_note_created', array( $this, 'record_note_created' ) );
    add_action( 'mpch_attachment_downloaded', array( $this, 'record_attachment_downloaded' ), 0, 2 );
    add_action( 'mpch_progress_created', array( $this, 'record_milestone_completed' ) );
    add_action( 'mpch_progress_created', array( $this, 'record_habit_completed' ) );
  }

  /**
   * Acitivty listing
   *
   * @return void
   */
  public function listing() {
    $action = ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification

    if ( 'edit' === $action ) {
      $this->display_edit_form();
    } else {
      $this->display_student_progress_list();
    }
  }

  /**
   * Displays the table
   *
   * @return void
   */
  public function display_edit_form() {

    if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
      $this->display_list();
      return;
    }

    $id    = wp_unslash( sanitize_text_field( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
    $coach = new Coach( $id );

    if ( empty( $coach->ID ) ) {
      $this->display_list();
      return;
    }

    CoachHelper::render_edit_form( $coach );
  }

  /**
   * Displays enrollment table
   *
   * @param  string $message message
   * @param  array  $errors  errors
   * @return void
   */
  public function display_student_progress_list( $message = '', $errors = array() ) {
    $screen = get_current_screen();

    $list_table = new ActivityTable( $screen, $this->get_columns() );
    $list_table->prepare_items();

    $errors = View::get_string( '/admin/errors', compact( 'errors', 'message' ) );

    $firstname_str              = Coach::FIRST_NAME_STR;
    $lastname_str               = Coach::LAST_NAME_STR;
    $email_str                  = Coach::EMAIL_STR;
    $username_str               = Coach::USERNAME_STR;
    $send_user_notification_str = Coach::SEND_USER_NOTIFICATION_STR;

    $args = array(
      'role' => Coach::ROLE,
    );

    $coaches           = Coach::all();
    $programs          = Program::all()->get_items();
    $recent_activities = Activity::get_recent_activities();
    $activity_types    = array_map( 'ucwords', str_replace( '-', ' ', Activity::$events ) );
    $get_coach         = isset( $_GET['coach'] ) ? sanitize_text_field( wp_unslash( $_GET['coach'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $get_program       = isset( $_GET['program'] ) ? sanitize_text_field( wp_unslash( $_GET['program'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

    $label_for_coaches = AppHelper::get_label_for_coach(true);
    $label_for_coach = AppHelper::get_label_for_coach();
    $label_for_client = AppHelper::get_label_for_client();
    $label_for_clients = AppHelper::get_label_for_client(true);

    View::render( '/admin/enrollment/table', get_defined_vars() );
  }

  /**
   * Gets a list of columns.
   *
   * @return array
   */
  public function get_columns() {
    $cols = array(
      'student'  => AppHelper::get_label_for_client(),
      'habit'    => esc_html__( '', 'memberpress-coachkit' ),
      'started'  => esc_html__( 'Started', 'memberpress-coachkit' ),
      'program'  => esc_html__( 'Program', 'memberpress-coachkit' ),
      'progress' => esc_html__( 'Progress', 'memberpress-coachkit' ),
    );

    return \MeprHooks::apply_filters( 'mepr-coaching-coaches-cols', $cols );
  }

  /**
   * Add screen options
   *
   * @return void
   */
  public function add_screen_options() {
    add_screen_option( 'layout_columns' );

    $option = 'per_page';

    $args = array(
      'label'   => __( 'Activity', 'memberpress-coachkit' ),
      'default' => 10,
      'option'  => 'mp_activity_perpage',
    );

    add_screen_option( $option, $args );
  }

  /**
   * Set up screen options
   *
   * @param  string $status status
   * @param  string $option option
   * @param  string $value  value
   * @return string
   */
  public function setup_screen_options( $status, $option, $value ) {
    if ( 'mp_activity_perpage' === $option ) {
      return $value;
    }

    return $status;
  }

  public function handle_load_more_activities() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_activity', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $page              = isset( $_POST['page'] ) ? wp_unslash( sanitize_text_field( $_POST['page'] ) ) : 0;
    $activities        = isset( $_POST['activity'] ) ? wp_unslash( sanitize_text_field( $_POST['activity'] ) ) : '';
    $recent_activities = Activity::get_recent_activities( $page, [ $activities ] );

    wp_send_json_success( $recent_activities, $activities );
  }

  public function handle_load_filtered_activities() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_activity', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $page              = isset( $_POST['page'] ) ? wp_unslash( sanitize_text_field( $_POST['page'] ) ) : 0;
    $activity          = isset( $_POST['activity'] ) ? wp_unslash( sanitize_text_field( $_POST['activity'] ) ) : '';
    $activity          = str_replace( ' ', '-', strtolower( $activity ) );
    $recent_activities = Activity::get_recent_activities( $page, [ $activity ] );

    wp_send_json_success( $recent_activities );
  }

  public static function handle_filter_notifications() {
    $user_id = get_current_user_id();

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_notif' . $user_id, 'security' );
    $events = isset( $_POST['filters'] ) ? (array) lib\Validate::sanitize( wp_unslash( $_POST['filters'] ) ) : array();
    $page   = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;

    // Remove "false" values
    $events = array_filter($events, function ( $value ) {
      return 'false' !== $value;
    });

    // Re-index the array
    $events = array_keys( $events );

    if ( AppHelper::user_has_role( Coach::ROLE ) ) {
      $recent_activities = Activity::get_notifications_for_coach( $user_id, $page, $events );
    } else {
      $recent_activities = Activity::get_notifications_for_user( $user_id, $page, $events );
    }
    wp_send_json_success( $recent_activities );
  }

  public function record_program_started( $enrollment ) {
    $student = new Student( $enrollment->student_id );
    Activity::record(
      Activity::PROGRAM_STARTED_STR,
      $enrollment
    );
  }

  public function record_enrollment_removed( $enrollment ) {
    $student = new Student( $enrollment->student_id );
    Activity::record(
      Activity::UNENROLLED_STR,
      $enrollment,
      [
        'name' => $student->fullname,
        'profile_url' => StudentHelper::get_profile_url( $student->ID ),
        'program' => $enrollment->program()->post_title
      ]
    );
  }

  public function record_message_created( $message ) {
    // Record event
    $event_id = Activity::record(
      Activity::MESSAGE_CREATED_STR,
      $message
    );
  }

  public function record_note_created( $note ) {
    Activity::record(
      Activity::NOTE_CREATED_STR,
      $note,
      array(
        'student_id' => $note->student_id,
        'coach_id'   => $note->coach_id,
      )
    );
  }

  public function record_milestone_completed( $progress ) {
    if ( $progress->milestone_id > 0 ) {
      Activity::record(
        'milestone-completed',
        $progress
      );
    }
  }

  public function record_habit_completed( $progress ) {
    if ( $progress->habit_id > 0 ) {
      Activity::record(
        'habit-completed',
        $progress
      );
    }
  }

  public function record_attachment_downloaded( $user, $attachment_id ) {
    Activity::record(
      Activity::ATTACHMENT_DOWNLOAD_STR,
      $user,
      array(
        'attachment_id' => $attachment_id,
      )
    );
  }

  /**
   * Enqueue admin scripts
   *
   * @return void
   */
  public function admin_enqueue_scripts() {
    global $current_screen;

    if ( stripos( $current_screen->id, 'memberpress-coachkit' ) !== false ) {
      $activity_json = array(
        'nonce' => wp_create_nonce( base\SLUG_KEY . '_activity' ),
      );

      wp_enqueue_style( base\SLUG_KEY, base\CSS_URL . '/admin.css', array(), base\VERSION );

      \wp_enqueue_script( base\SLUG_KEY . '-activity', base\JS_URL . '/admin-activity.js', array( 'wp-util' ), true, base\VERSION );
      wp_localize_script( base\SLUG_KEY . '-activity', 'ActivityUSER', $activity_json );
      \wp_enqueue_script( base\SLUG_KEY, base\JS_URL . '/admin-shared.js', array(), true, base\VERSION );

      wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', false, '1.0', 'all' );
      wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '1.0', true );
    }
  }

}
