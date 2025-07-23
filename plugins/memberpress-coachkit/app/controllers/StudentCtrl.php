<?php

namespace memberpress\coachkit\controllers;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use memberpress\coachkit\lib;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\models\Note;
use memberpress\coachkit\lib\Activity;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\lib\StudentsTable;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\helpers\ProgramHelper;
use memberpress\coachkit\helpers\StudentHelper;
use memberpress\coachkit\models\StudentProgress;

/**
 * Controller for Program
 */
class StudentCtrl extends lib\BaseCtrl {

  /**
   * Load class hooks here
   *
   * @return void
   */
  public function load_hooks() {
    $hook = 'mp-coachkit_page_memberpress-coachkit-students';

    add_action( "load-{$hook}", array( $this, 'add_screen_options' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    add_action( 'wp_ajax_filter_users', array( $this, 'handle_filter_users' ) );
    add_action( 'wp_ajax_filter_programs', array( $this, 'handle_filter_programs' ) );
    add_action( 'wp_ajax_add_student', array( $this, 'handle_add_student' ) );
    add_action( 'wp_ajax_new_student', array( $this, 'handle_new_student' ) );
    add_action( 'wp_ajax_add_student_to_group', array( $this, 'handle_add_student_to_group' ) );
    add_action( 'wp_ajax_create_note', array( $this, 'handle_create_student_note' ) );
    add_action( 'wp_ajax_update_note', array( $this, 'handle_update_student_note' ) );
    add_action( 'wp_ajax_trash_note', array( $this, 'handle_trash_student_note' ) );
    add_action( 'wp_ajax_filter_students', array( $this, 'handle_filter_students' ) );
    add_action( 'wp_ajax_filter_students_by_group', array( $this, 'handle_filter_students_by_group' ) );
    add_action( 'wp_ajax_unenroll_student', array( $this, 'handle_unenroll_student' ) );
    add_action( 'mpch_enrollment_removed', array( $this, 'delete_student_progress' ) );
    add_action( 'mpch_enrollment_created', array( $this, 'add_student_role' ) );
    add_action( 'mpch_table_controls_search', array( $this, 'table_search_box' ) );

    // add_action( 'set_user_role', [ $this, 'manage_coach_caps' ], 10, 3 );
    add_filter( 'set_screen_option_mp_students_perpage', array( $this, 'setup_screen_options' ), 10, 3 );
    add_filter( "manage_{$hook}_columns", array( $this, 'get_columns' ), 0 );
  }

  /**
   * Coaches listing
   *
   * @return void
   */
  public function listing() {
    $action = ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification

    if ( 'edit' === $action ) {
      $this->display_edit_form();
    } else {
      $this->display_list();
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

    $id      = wp_unslash( sanitize_text_field( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
    $student = new Student( $id );

    if ( empty( $student->ID ) ) {
      $this->display_list();
      return;
    }

    $programs = ProgramHelper::get_mapped_program_with_groups( '', $student->ID );

    $program_list  = View::get_string( '/admin/students/program-list', array( 'programs' => $programs->items ) );
    $program_count = sprintf( esc_html__( 'Showing %1$d-%2$d of %3$d programs', 'memberpress-coachkit' ), 1, count( $programs->items ), $programs->total );

    $enrollments          = $student->get_enrollment_data()->all();
    $memberships          = $student->get_active_membership_data()->all();
    $notes                = $student->get_notes_data()->map(function ( $note ) {
      return StudentHelper::get_note_row_html( $note );
    })->all();
    $recent_activities    = Activity::get_recent_activities( 1, [], $student->ID );
    $from_activities_page = false;

    if ( isset( $_SERVER['HTTP_REFERER'] ) && admin_url( 'admin.php?page=memberpress-coachkit' ) === $_SERVER['HTTP_REFERER'] ) {
      $from_activities_page = true;
      $activities_page_url  = admin_url( 'admin.php?page=memberpress-coachkit' );
    }

    $edit_user_url = get_edit_user_link( $id );
    $label_for_coaches = AppHelper::get_label_for_client(true);
    $label_for_coach = AppHelper::get_label_for_client();
    $label_for_client = AppHelper::get_label_for_client();

    View::render( '/admin/students/enroll-student-form', get_defined_vars() );
    View::render( '/admin/students/edit-student', get_defined_vars() );
  }

  /**
   * Displays the table
   *
   * @param string $message message
   * @param array  $errors errors
   * @return void
   */
  public function display_list( $message = '', $errors = array() ) {
    $screen = get_current_screen();

    $list_table = new StudentsTable( $screen, $this->get_columns() );
    $list_table->prepare_items();

    $errors = View::get_string( '/admin/errors', compact( 'errors', 'message' ) );

    $firstname_str              = Student::FIRST_NAME_STR;
    $lastname_str               = Student::LAST_NAME_STR;
    $email_str                  = Student::EMAIL_STR;
    $username_str               = Student::USERNAME_STR;
    $send_user_notification_str = Student::SEND_USER_NOTIFICATION_STR;

    // Get non-students for 'add student' modal
    $query = new \WP_User_Query(
      array(
        'role__not_in' => array( Student::ROLE, Coach::ROLE ),
        'number'       => 8,
      )
    );

    $users       = $query->results;
    $total       = $query->total_users;
    $users_count = sprintf( esc_html__( 'Showing %1$d-%2$d of %3$d users', 'memberpress-coachkit' ), 1, count( $users ), $total );

    $users_list = View::get_string( '/admin/students/users-list', compact( 'users' ) );
    $label_for_clients = AppHelper::get_label_for_client(true);
    $label_for_client = AppHelper::get_label_for_client();

    View::render( '/admin/students/table', compact( 'message', 'list_table', 'errors', 'label_for_clients' ) );
    View::render( '/admin/students/add-student', compact( 'users_list', 'users_count', 'label_for_client' ) );
    View::render( '/admin/students/new-student', get_defined_vars() );
  }

  /**
   * Gets a list of columns.
   *
   * @return array
   */
  public function get_columns() {
    $cols = array(
      'id'          => __( 'Id', 'memberpress-coachkit' ),
      'title'       => AppHelper::get_label_for_client(),
      'email'       => esc_html__( 'Email', 'memberpress-coachkit' ),
      'programs'    => esc_html__( 'Programs', 'memberpress-coachkit' ),
      'last_active' => esc_html__( 'Last Active', 'memberpress-coachkit' ),
      'created_at'  => esc_html__( 'Created', 'memberpress-coachkit' ),
    );

    return \MeprHooks::apply_filters( 'mepr-coaching-students-cols', $cols );
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
      'label'   => __( 'Students', 'memberpress-coachkit' ),
      'default' => 10,
      'option'  => 'mp_students_perpage',
    );

    add_screen_option( $option, $args );
  }

  /**
   * Set up screen options
   *
   * @param string $status status
   * @param string $option option
   * @param string $value value
   * @return string
   */
  public function setup_screen_options( $status, $option, $value ) {
    if ( 'mp_students_perpage' === $option ) {
      return $value;
    }

    return $status;
  }

  /**
   * HAndle AJAX filter students request
   *
   * @return void
   */
  public function handle_filter_users() {

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    $search = isset( $_POST['search'] ) ? wp_unslash( sanitize_text_field( $_POST['search'] ) ) : '';

    $query = new \WP_User_Query(
      array(
        'role__not_in' => array( Student::ROLE, Coach::ROLE ),
        'number'       => 8,
        'search'       => '*' . $search . '*',
      )
    );

    $users       = $query->results;
    $total       = $query->total_users;
    $users_count = sprintf( esc_html__( 'Showing %1$d-%2$d of %3$d users', 'memberpress-coachkit' ), 1, count( $users ), $total );

    $users_list = View::get_string( '/admin/students/users-list', compact( 'users' ) );

    wp_send_json_success(
      array(
        'users' => $users_list,
        'count' => $users_count,
      )
    );
  }

  /**
   * Handle AJAX filter students request
   *
   * @return void
   */
  public function handle_filter_students_by_group() {

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    $group_id = isset( $_POST['group_id'] ) ? wp_unslash( absint( $_POST['group_id'] ) ) : 0;

    $students = AppHelper::get_students_content($group_id);

    wp_send_json_success( $students );
  }

  /**
   * HAndle AJAX filter students request
   *
   * @return void
   */
  public function handle_filter_programs() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    $search     = isset( $_POST['search'] ) ? wp_unslash( sanitize_text_field( $_POST['search'] ) ) : '';
    $student_id = isset( $_POST['student_id'] ) ? wp_unslash( absint( $_POST['student_id'] ) ) : '';
    $programs   = ProgramHelper::get_mapped_program_with_groups( $search, $student_id );

    $program_count = sprintf( esc_html__( 'Showing %1$d-%2$d of %3$d programs', 'memberpress-coachkit' ), 1, count( $programs->items ), $programs->total );
    $program_list  = View::get_string( '/admin/students/program-list', array( 'programs' => $programs->items ) );

    wp_send_json_success(
      array(
        'programs' => $program_list,
        'count'    => $program_count,
      )
    );
  }

  /**
   * Handle AJAX add coach request
   *
   * @return void
   */
  public function handle_add_student() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $student_id = isset( $_POST['student_id'] ) ? wp_unslash( sanitize_text_field( $_POST['student_id'] ) ) : 0;
    $user       = new \WP_User( $student_id );
    if ( empty( $user->ID ) ) {
      wp_send_json_error( esc_html__( 'Please select a user to proceed.', 'memberpress-coachkit' ) );
    }

    $student = new Student( $student_id );
    if ( $student->ID > 0 ) {
      wp_send_json_error( sprintf(esc_html__( 'User is already a %s', 'memberpress-coachkit' ), AppHelper::get_label_for_client() ));
    }

    $student->load_user_data_by_user_id( $student_id );

    try {
      $student->store();
    } catch ( \Throwable $th ) {
      wp_send_json_error( $th->getMessage() );
    }

    // translators:
    wp_send_json_success(
      array(
        'message' => sprintf(
          '%s %s <a href="%s">%s</a>',
          $student->first_name,
          sprintf( esc_html__( 'added as %s.', 'memberpress-coachkit' ), AppHelper::get_label_for_client() ),
          StudentHelper::get_profile_url( $student->ID ),
          __( 'Edit', 'memberpress-coachkit' )
        ),
      )
    );
  }

  /**
   * Handle AJAX new coach request
   *
   * @return void
   */
  public function handle_new_student() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $form = isset( $_POST['form'] ) ? wp_unslash( lib\Validate::sanitize( $_POST['form'] ) ) : '';
    $data = wp_list_pluck( $form, 'value', 'name' );

    $student                         = new Student();
    $student->first_name             = isset( $data['first_name'] ) ? $data['first_name'] : '';
    $student->last_name              = isset( $data['last_name'] ) ? $data['last_name'] : '';
    $student->user_email             = isset( $data['user_email'] ) ? $data['user_email'] : '';
    $student->user_login             = isset( $data['user_login'] ) ? $data['user_login'] : $student->user_email;
    $student->send_user_notification = isset( $data['send_user_notification'] ) ? true : false;

    $student->rec->user_pass = '';

    try {
      $student_id = $student->store();
    } catch ( \Throwable $th ) {
      wp_send_json_error( $th->getMessage() );
    }

    // translators:
    wp_send_json_success( sprintf( '%s %s <a href="%s">%s</a>', $student->first_name, __( 'successfully added as a student.', 'memberpress-coachkit' ), StudentHelper::get_profile_url( $student_id ), __( 'Edit Student', 'memberpress-coachkit' ) ) );
  }

  /**
   * Handle AJAX add new student to group request
   *
   * @return void
   */
  public function handle_add_student_to_group() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $student_id = isset( $_POST['student_id'] ) ? wp_unslash( sanitize_text_field( $_POST['student_id'] ) ) : 0;
    $group_id   = isset( $_POST['group_id'] ) ? wp_unslash( sanitize_text_field( $_POST['group_id'] ) ) : 0;

    $group = new Group( $group_id );

    if ( false === $group->accepting_enrollments_from_student( $student_id ) ) {
      wp_send_json_error( sprintf( esc_html__( 'Problem adding %s to Cohort.', 'memberpress-coachkit' ), AppHelper::get_label_for_client() ) );
      return;
    }

    $enrollment             = new Enrollment();
    $enrollment->student_id = $student_id;
    $enrollment->group_id   = $group->id;
    $enrollment->start_date = $group->get_start_date();
    $enrollment->program_id = $group->program_id;
    $enrollment->txn_id     = 0;
    $enrollment->created_at = Utils::ts_to_mysql_date( time() );
    $enrollment->features   = implode( ',', [ 'messaging' ] );
    $enrollment_id          = $enrollment->store();

    if ( is_wp_error( $enrollment_id ) ) {
      wp_send_json_error( $enrollment_id->get_error_message() );
    }

    wp_send_json_success( sprintf( esc_html__( '%s successfully added to Cohort.', 'memberpress-coachkit' ), AppHelper::get_label_for_client() ) );
  }

  /**
   * Create student note from WP Admin
   *
   * @return string
   */
  public function handle_create_student_note() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) && ! AppHelper::user_has_role( Coach::ROLE ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $student_id = isset( $_POST['student_id'] ) ? wp_unslash( sanitize_text_field( $_POST['student_id'] ) ) : 0;
    $message    = isset( $_POST['note'] ) ? wp_unslash( wp_kses_post( $_POST['note'] ) ) : 0;
    $frontend   = isset( $_POST['frontend'] ) ? true : false;

    $note             = new Note();
    $note->student_id = $student_id;
    $note->coach_id   = get_current_user_id();
    $note->note       = $message;
    $note->created_at = Utils::ts_to_mysql_date( time() );

    $note_id = $note->store();

    if ( is_wp_error( $note_id ) ) {
      wp_send_json_error( $note_id->get_error_message() );
    }

    $note = $frontend ? StudentHelper::transform_note( $note ) : StudentHelper::get_note_row_html( $note );

    wp_send_json_success( array(
      'note'    => $note,
      'message' => esc_html__( 'Note successfully created', 'memberpress-coachkit' ),
    ) );
  }

  /**
   * Create student note from WP Admin
   *
   * @return string
   */
  public function handle_update_student_note() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins and coaches can do this
    if ( ! current_user_can( 'manage_options' ) && ! AppHelper::user_has_role( Coach::ROLE ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $note_id  = isset( $_POST['note_id'] ) ? wp_unslash( sanitize_text_field( $_POST['note_id'] ) ) : 0;
    $message  = isset( $_POST['note'] ) ? wp_unslash( wp_kses_post( $_POST['note'] ) ) : 0;
    $frontend = isset( $_POST['frontend'] ) ? true : false;

    $note       = new Note( $note_id );
    $note->note = $message;

    // Only admins and authors can proceed
    if ( ! current_user_can( 'manage_options' ) && $note->coach_id != get_current_user_id() ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $note_id = $note->store();

    if ( is_wp_error( $note_id ) ) {
      wp_send_json_error( $note_id->get_error_message() );
    }

    $note = $frontend ? $note->get_values() : StudentHelper::get_note_row_html( $note );

    wp_send_json_success( array(
      'note'    => $note,
      'message' => esc_html__( 'Note successfully updated', 'memberpress-coachkit' ),
    ) );
  }

  /**
   * Create student note from WP Admin
   *
   * @return string
   */
  public function handle_trash_student_note() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    // Only admins and coaches can do this
    if ( ! current_user_can( 'manage_options' ) && ! AppHelper::user_has_role( Coach::ROLE ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $note_id = isset( $_POST['note_id'] ) ? wp_unslash( sanitize_text_field( $_POST['note_id'] ) ) : 0;
    $note    = new Note( $note_id );

    // Only admins and authors can proceed
    if ( ! current_user_can( 'manage_options' ) && $note->coach_id != get_current_user_id() ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $note_id = $note->destroy();

    if ( is_wp_error( $note_id ) ) {
      wp_send_json_error( $note_id->get_error_message() );
    }

    wp_send_json_success( array(
      'message' => esc_html__( 'Note successfully deleted', 'memberpress-coachkit' ),
    ) );
  }

  public function handle_filter_students() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_activity', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $query = isset( $_POST['q'] ) ? wp_unslash( sanitize_text_field( $_POST['q'] ) ) : '';

    // Set the user search parameters
    $users = new \WP_User_Query( array(
      'role__in'   => array( Student::ROLE ),
      'meta_query' => array(
        'relation' => 'OR',
        array(
          'key'     => 'first_name', // User meta field: first_name
          'value'   => $query,
          'compare' => 'LIKE',
        ),
        array(
          'key'     => 'last_name', // User meta field: last_name
          'value'   => $query,
          'compare' => 'LIKE',
        ),
      ),
    ) );

    $students = new Collection( $users->get_results() );

    $students = $students->map(function( $student ) {
      return array(
        'id'   => $student->ID,
        'text' => $student->first_name . ' ' . $student->last_name,
        'url'  => StudentHelper::get_profile_url( $student->ID ),
      );
    })->all();

    wp_send_json_success( $students );
  }

  /**
   * Add "MemberPress Student" role
   *
   * @param integer student_id Student user ID
   */
  public function add_student_role( $enrollment ) {
    if ( isset( $enrollment->student_id ) ) {
      $user = new \WP_User( $enrollment->student_id );
      $user->add_role( Student::ROLE );
    }
  }

  public function table_search_box() {
    $db = new lib\Db();

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    if ( isset( $_REQUEST['page'] ) && 'memberpress-coachkit-students' === $_REQUEST['page'] ) {
      $mepr_options = \MeprOptions::fetch();
      $r_coach      = ( isset( $_REQUEST['r_coach'] ) ? $_REQUEST['r_coach'] : false );
      $r_group      = ( isset( $_REQUEST['r_group'] ) ? $_REQUEST['r_group'] : false );
      $r_program    = ( isset( $_REQUEST['r_program'] ) ? $_REQUEST['r_program'] : false );

      $coaches  = Coach::all();
      $programs = Program::all()->get_items();
      $groups   = $db->get_records( $db->groups, array( 'coach_id' => $r_coach ) );

      $label_for_coaches = AppHelper::get_label_for_coach(true);
      $label_for_coach = AppHelper::get_label_for_coach();

      View::render( '/admin/students/search-box', get_defined_vars() );
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
  }

  /**
   * [Ajax] Handle unenrollment of students
   *
   * @return void
   */
  public function handle_unenroll_student() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    $enrollment_id = isset( $_POST['enrollment_id'] ) ? wp_unslash( absint( $_POST['enrollment_id'] ) ) : '';
    $enrollment = new Enrollment($enrollment_id);

    try {
      $enrollment->destroy();
      wp_send_json_success();
    } catch (\Throwable $th) {
      Utils::error_log($th->getMessage());
      wp_send_json_error(esc_html__('Error unenrolling student', 'memberpress-coaching', 'memberpress-coachkit'));
    }
  }

  /**
   * Deletes student progress when enrollment is deleted
   *
   * @param Enrollment $enrollment
   * @return void
   */
  public function delete_student_progress(Enrollment $enrollment) {
    Utils::collect( StudentProgress::get_all('', '', [
      'enrollment_id' => $enrollment->id
    ]))->map(function(StudentProgress $progress) {
      $progress->destroy();
    });
  }


  /**
   * Enqueue admin scripts
   *
   * @return void
   */
  public function admin_enqueue_scripts() {
    global $current_screen;
    $hook = 'mp-coachkit_page_memberpress-coachkit-students';

    if ( $hook === $current_screen->id ) {
      $student_json = array(
        'nonce' => wp_create_nonce( base\SLUG_KEY . '_students' ),
        'i10n' => [
          'del_enrollment' => esc_html__('Deleting Enrollments would cause the student to lose access to associated program. Are you sure you want to delete this enrollment?', 'memberpress-coaching', 'memberpress-coachkit'),
          ]
      );

      wp_register_style( 'mpch-jquery-magnific-popup', base\CSS_URL . '/vendor/magnific-popup.min.css', array(), '1.2.0' );
      wp_enqueue_style( base\SLUG_KEY, base\CSS_URL . '/admin.css', array( 'mpch-jquery-magnific-popup' ), base\VERSION );

      wp_register_script( 'popper', 'https://unpkg.com/@popperjs/core@2', array(), MEPR_VERSION, true );
      wp_register_script( 'mpch-jquery-magnific-popup', base\JS_URL . '/vendor/jquery.magnific-popup.min.js', array( 'jquery' ), '1.2.0', true );
      wp_register_script( 'mepr-table-controls', MEPR_JS_URL . '/table_controls.js', array( 'jquery' ), MEPR_VERSION );
      wp_register_script( base\SLUG_KEY . '-tooltip', base\JS_URL . '/tooltip.js', array( 'popper' ), MEPR_VERSION, true );
      \wp_enqueue_script( base\SLUG_KEY . '-student', base\JS_URL . '/admin-student.js', array( 'wp-util', base\SLUG_KEY . '-tooltip', 'mepr-table-controls', 'mpch-jquery-magnific-popup' ), true, base\VERSION );
      wp_enqueue_editor();
      wp_localize_script( base\SLUG_KEY . '-student', 'StudentUSER', $student_json );
    }
  }

}
