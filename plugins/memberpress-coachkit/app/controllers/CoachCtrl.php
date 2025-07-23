<?php

namespace memberpress\coachkit\controllers;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use memberpress\coachkit\lib;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\lib\CoachesTable;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\helpers\CoachHelper;
use memberpress\coachkit\helpers\ProgramHelper;

/**
 * Controller for Program
 */
class CoachCtrl extends lib\BaseCtrl {

  /**
   * Load class hooks here
   *
   * @return void
   */
  public function load_hooks() {
    $hook = 'mp-coachkit_page_memberpress-coachkit-coaches';

    add_action( "load-{$hook}", [ $this, 'add_screen_options' ] );
    add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
    add_action( 'wp_ajax_filter_coaches', [ $this, 'handle_filter_coaches' ] );
    add_action( 'wp_ajax_add_coach', [ $this, 'handle_add_coach' ] );
    add_action( 'wp_ajax_new_coach', [ $this, 'handle_new_coach' ] );
    add_action( 'wp_ajax_assign_coach_to_group', array( $this, 'handle_assign_coach_to_group' ) );
    // add_action( 'set_user_role', [ $this, 'manage_coach_caps' ], 10, 3 );
    add_action( 'admin_init', [ $this, 'add_coach_caps' ], 10 );
    add_filter( 'set_screen_option_mp_coaches_perpage', [ $this, 'setup_screen_options' ], 10, 3 );
    add_filter( "manage_{$hook}_columns", [ $this, 'get_columns' ], 0 );
    add_action( 'mpch_table_controls_search', array( $this, 'table_search_box' ) );
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

    $id    = wp_unslash( sanitize_text_field( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
    $coach = new Coach( $id );

    if ( empty( $coach->ID ) ) {
      $this->display_list();
      return;
    }

    CoachHelper::render_edit_form( $coach );
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

    $list_table = new CoachesTable( $screen, $this->get_columns() );
    $list_table->prepare_items();

    $errors = View::get_string( '/admin/errors', compact( 'errors', 'message' ) );

    $firstname_str              = Coach::FIRST_NAME_STR;
    $lastname_str               = Coach::LAST_NAME_STR;
    $email_str                  = Coach::EMAIL_STR;
    $username_str               = Coach::USERNAME_STR;
    $send_user_notification_str = Coach::SEND_USER_NOTIFICATION_STR;

    $coaches = Coach::all( 'ids' );
    $users   = get_users(
      array(
        'role__not_in' => Coach::ROLE,
        'number'       => 8,
      )
    );

    $users_list = View::get_string( '/admin/coaches/users-list', compact( 'users' ) );
    $label_for_coaches = AppHelper::get_label_for_coach(true);
    $label_for_coach = AppHelper::get_label_for_coach();

    View::render( '/admin/coaches/table', compact( 'message', 'list_table', 'errors', 'label_for_coaches' ) );
    View::render( '/admin/coaches/add-coach', compact( 'users_list', 'label_for_coach' ) );
    View::render( '/admin/coaches/new-coach', get_defined_vars() );
  }

  /**
   * Gets a list of columns.
   *
   * @return array
   */
  public function get_columns() {
    $cols = array(
      'id'              => __( 'Id', 'memberpress-coachkit' ),
      'title'           => AppHelper::get_label_for_coach(),
      'email'           => esc_html__( 'Email', 'memberpress-coachkit' ),
      'programs'        => esc_html__( 'Programs', 'memberpress-coachkit' ),
      'last_active'     => esc_html__( 'Last Active', 'memberpress-coachkit' ),
      'active_students' => sprintf( esc_html__( 'Active %s', 'memberpress-coachkit' ), AppHelper::get_label_for_client()),
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
      'label'   => AppHelper::get_label_for_coach(true),
      'default' => 10,
      'option'  => 'mp_coaches_perpage',
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
    if ( 'mp_coaches_perpage' === $option ) {
      return $value;
    }

    return $status;
  }

  /**
   * HAndle AJAX filter coaches request
   *
   * @return void
   */
  public function handle_filter_coaches() {

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_coach', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    $search = isset( $_POST['search'] ) ? wp_unslash( sanitize_text_field( $_POST['search'] ) ) : '';

    $users = get_users(
      array(
        'number'       => 8,
        'search'       => '*' . $search . '*',
        'role__not_in' => Coach::ROLE,
      )
    );

    $users_list = View::get_string( '/admin/coaches/users-list', compact( 'users' ) );

    wp_send_json_success( $users_list );
  }

  /**
   * Handle AJAX add coach request
   *
   * @return void
   */
  public function handle_add_coach() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_coach', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $coach_id = isset( $_POST['coach_id'] ) ? wp_unslash( sanitize_text_field( $_POST['coach_id'] ) ) : 0;
    $user     = new \WP_User( $coach_id );
    if ( empty( $user->ID ) ) {
      wp_send_json_error( esc_html__( 'Please select a user to proceed.', 'memberpress-coachkit' ) );
    }

    $coach = new Coach( $coach_id );
    if ( $coach->ID > 0 ) {
      wp_send_json_error( sprintf(esc_html__( 'User is already a %s', 'memberpress-coachkit' ), AppHelper::get_label_for_coach() ));
    }

    $coach->load_user_data_by_user_id( $coach_id );

    try {
      $coach->store();
    } catch ( \Throwable $th ) {
      wp_send_json_error( $th->getMessage() );
    }

    // translators:
    wp_send_json_success(
      array(
        'message' => sprintf(
          '%s %s <a href="%s">%s</a>',
          $coach->first_name,
          sprintf( esc_html__( 'added as %s.', 'memberpress-coachkit' ), AppHelper::get_label_for_coach() ),
          CoachHelper::get_profile_url( $coach->ID ),
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
  public function handle_new_coach() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_coach', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $form = isset( $_POST['form'] ) ? wp_unslash( lib\Validate::sanitize( $_POST['form'] ) ) : '';
    $data = wp_list_pluck( $form, 'value', 'name' );

    $coach                         = new Coach();
    $coach->first_name             = isset( $data['first_name'] ) ? $data['first_name'] : '';
    $coach->last_name              = isset( $data['last_name'] ) ? $data['last_name'] : '';
    $coach->user_email             = isset( $data['user_email'] ) ? $data['user_email'] : '';
    $coach->user_login             = isset( $data['user_login'] ) ? $data['user_login'] : $coach->user_email;
    $coach->send_user_notification = isset( $data['send_user_notification'] ) ? true : false;

    $coach->rec->user_pass = '';

    try {
      $coach_id = $coach->store();
    } catch ( \Throwable $th ) {
      wp_send_json_error( $th->getMessage() );
    }

    // translators:
    wp_send_json_success( sprintf( '%s %s %s. <a href="%s">%s</a>', $coach->first_name, __( 'successfully added as', 'memberpress-coachkit' ), AppHelper::get_label_for_coach(), CoachHelper::get_profile_url( $coach_id ), __( 'Edit', 'memberpress-coachkit' ) ) );
  }

  /**
   * Handle AJAX new coach request
   *
   * @return void
   */
  public function handle_assign_coach_to_group() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_coach', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'Cheating huh?', 'memberpress-coachkit' ) );
    }

    $coach_id = isset( $_POST['coach_id'] ) ? wp_unslash( sanitize_text_field( $_POST['coach_id'] ) ) : 0;
    $group_id = isset( $_POST['group_id'] ) ? wp_unslash( sanitize_text_field( $_POST['group_id'] ) ) : 0;

    $group = new Group( $group_id );

    if ( absint( $group->coach_id ) === absint( $coach_id ) ) {
      wp_send_json_error( sprintf( esc_html__( '%s already assigned to Cohort', 'memberpress-coachkit' ), AppHelper::get_label_for_coach() ) );
    }

    $group->coach_id = $coach_id;
    $group_id        = $group->store();

    if ( is_wp_error( $group_id ) ) {
      wp_send_json_error( $group_id->get_error_message() );
    }

    wp_send_json_success( sprintf( esc_html__( '%s successfully assigned to Cohort', 'memberpress-coachkit' ), AppHelper::get_label_for_coach() ) );
  }

  /**
   * Adds or removes Coach role
   *
   * @param integer $user_id The User ID
   * @param string  $role Current role
   * @param array   $old_roles Old roles
   * @return void
   */
  public function manage_coach_caps( int $user_id, string $role, array $old_roles ) {
    $user = new \WP_User( $user_id );

    // When user is now an admin
    if ( user_can( $user_id, 'manage_options' ) ) {
      $user->add_role( Coach::ROLE );
    }

    // When user is not admin
    if ( ! user_can( $user_id, 'manage_options' ) && Coach::ROLE !== $role ) {
      $user->remove_role( Coach::ROLE );
    }
  }

  /**
   * Add coach capabilities
   *
   * @return void
   */
  public function add_coach_caps() {
    $role = get_role( Coach::ROLE );

    if ( ! $role ) {
      return;
    }

    $caps = ProgramHelper::get_coach_caps();
    foreach ( $caps as $cap ) {
      $role->add_cap( $cap );
    }
  }

  public function table_search_box() {
    $db = new lib\Db();

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    if ( isset( $_REQUEST['page'] ) && 'memberpress-coachkit-coaches' === $_REQUEST['page'] ) {
      $r_program = ( isset( $_REQUEST['r_program'] ) ? $_REQUEST['r_program'] : false );
      $programs  = Program::all()->get_items();

      View::render( '/admin/coaches/search-box', compact( 'programs', 'r_program' ) );
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
  }

  /**
   * Enqueue admin scripts
   *
   * @return void
   */
  public function admin_enqueue_scripts() {
    global $current_screen;
    $hook = 'mp-coachkit_page_memberpress-coachkit-coaches';

    if ( $hook === $current_screen->id ) {
      $coach_json = array(
        'nonce' => wp_create_nonce( base\SLUG_KEY . '_coach' ),
      );

      wp_register_style( 'mpch-jquery-magnific-popup', base\CSS_URL . '/vendor/magnific-popup.min.css', array(), '1.2.0' );
      wp_enqueue_style( base\SLUG_KEY, base\CSS_URL . '/admin.css', array( 'mpch-jquery-magnific-popup' ), base\VERSION );

      wp_register_script( 'mpch-jquery-magnific-popup', base\JS_URL . '/vendor/jquery.magnific-popup.min.js', array( 'jquery' ), '1.2.0', true );
      wp_register_script( 'mepr-table-controls', MEPR_JS_URL . '/table_controls.js', array( 'jquery' ), MEPR_VERSION );
      \wp_enqueue_script( base\SLUG_KEY . '-coach', base\JS_URL . '/admin-coach.js', array( 'wp-util', 'mepr-table-controls', 'mpch-jquery-magnific-popup' ), true, base\VERSION );
      wp_localize_script( base\SLUG_KEY . '-coach', 'CoachUSER', $coach_json );
    }
  }

}
