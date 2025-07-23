<?php

namespace memberpress\coachkit\controllers;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\models\Habit;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\models\StudentProgress;
use memberpress\coachkit\models\Milestone as Milestone;
use memberpress\coachkit\helpers\ProgramHelper as ProgramHelper;
use memberpress\downloads\models\File;

/**
 * Controller for Program
 */
class ProgramCtrl extends lib\BaseCptCtrl {


  /**
   * Load class hooks here
   *
   * @return void
   */
  public function load_hooks() {
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    add_action( 'save_post', array( $this, 'save_postdata' ) );
    add_action( 'mepr-product-options-tabs', array( $this, 'coaching_product_options_tab' ) );
    add_action( 'mepr-product-options-pages', array( $this, 'coaching_product_options_content' ) );
    add_action( 'wp_ajax_update_group', array( $this, 'handle_save_group' ) );
    add_action( 'wp_ajax_remove_group', array( $this, 'handle_remove_group' ) );
    add_action( 'wp_ajax_fetch_group', array( $this, 'handle_fetch_group' ) );
    add_action( 'wp_ajax_complete_milestone', array( $this, 'handle_complete_milestone' ) );
    add_action( 'wp_ajax_complete_habit', array( $this, 'handle_complete_habit' ) );
    add_action( 'wp_ajax_download_attachment', array( $this, 'handle_download_attachment' ) );
    add_action( 'wp_ajax_save_group_appointments', array( $this, 'handle_save_group_appointments' ) );
    add_action( 'delete_post', array( $this, 'delete_program_data' ), 10, 2 );
    add_action( 'mepr-membership-save-meta', array( $this, 'save_program_pool' ) );
    add_action( 'pre_post_update', array( $this, 'prevent_coach_updates' ), 10, 2 );
    add_action( 'manage_' . Program::CPT . '_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
    add_filter( 'manage_' . Program::CPT . '_posts_columns', array( $this, 'set_custom_columns' ) );
    add_action( 'add_meta_boxes_' . Program::CPT, array( $this, 'change_publish_meta_box' ) );
    add_action( 'wp_ajax_filter_downloads', [ $this, 'handle_filter_downloads' ] );

    add_action( 'mpch_after_milestone_completion', array( $this, 'student_progress_check' ), 10, 2 );
    add_action( 'mpch_after_habit_completion', array( $this, 'student_progress_check' ), 10, 2 );

    add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 1 );
    add_filter( 'bulk_actions-edit-' . Program::CPT, array( $this, 'remove_bulk_actions_trash' ) );
    add_action( 'wp_trash_post', array( $this, 'restrict_post_deletion' ), 10, 1 );
    add_action( 'before_delete_post', array( $this, 'restrict_post_deletion' ), 10, 1 );

    $this->ctaxes = array();
  }

  /**
   * Enqueue admin scripts
   *
   * @return void
   */
  public function admin_enqueue_scripts() {
    global $current_screen;
    global $post;

    if ( Program::CPT === $current_screen->post_type && isset( $post->ID ) ) {

      $program_json = array(
        'milestone' => array(
          'max'     => 5,
          'blank'   => array(
            'row' => ProgramHelper::milestone_metabox_string( new Milestone() ),
          ),
          'nonce'   => wp_create_nonce( base\SLUG_KEY . '_add_milestone_metabox' ),
          'checkin' => ProgramHelper::checkin_metabox_html( new Milestone(), true ),
        ),
        'habit'     => array(
          'max'     => 5,
          'blank'   => array(
            'row' => ProgramHelper::habit_metabox_string( new Habit() ),
          ),
          'nonce'   => wp_create_nonce( base\SLUG_KEY . '_add_habit_metabox' ),
          'checkin' => ProgramHelper::checkin_metabox_html( new Habit(), true ),
        ),
        'group'     => array(
          'nonce' => wp_create_nonce( base\SLUG_KEY . '_group' ),
        ),
      );

      wp_register_style( 'mpch-jquery-magnific-popup', base\CSS_URL . '/vendor/magnific-popup.min.css', array(), '1.2.0' );
      wp_enqueue_style( base\SLUG_KEY, base\CSS_URL . '/admin.css', array( 'mpch-jquery-magnific-popup' ), base\VERSION );

      $wp_scripts = new \WP_Scripts();
      $ui         = $wp_scripts->query( 'jquery-ui-core' );
      $url        = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.css";
      wp_enqueue_style( 'jquery-ui-smoothness', $url, array(), base\VERSION );

      wp_register_script( 'mpch-popper', base\JS_URL . '/vendor/popper.min.js', array( 'jquery' ), '1.2.0', true );
      wp_register_script( 'mpch-jquery-magnific-popup', base\JS_URL . '/vendor/jquery.magnific-popup.min.js', array( 'jquery' ), '1.2.0', true );
      wp_register_script( base\SLUG_KEY . '-tooltip', base\JS_URL . '/tooltip.js', array( 'mpch-popper' ), base\VERSION, true );
      \wp_enqueue_script( base\SLUG_KEY, base\JS_URL . '/admin-program.js', array( base\SLUG_KEY . '-tooltip', 'jquery-ui-dialog', 'mpch-jquery-magnific-popup', 'jquery-ui-datepicker', 'wp-util' ), true, base\VERSION );
      \wp_enqueue_script( base\SLUG_KEY . '-coach', base\JS_URL . '/admin-coach.js', array( base\SLUG_KEY . '-tooltip', 'jquery-ui-dialog', 'mpch-jquery-magnific-popup', 'jquery-ui-datepicker' ), true, base\VERSION );
      wp_localize_script( base\SLUG_KEY, 'ProgramCPT', $program_json );
    }

    if ( \MeprProduct::$cpt === $current_screen->post_type ) {
      wp_enqueue_style( 'select2', base\CSS_URL . 'select2.min.css', false, base\VERSION, 'all' );
      wp_register_style( 'mpch-jquery-magnific-popup', base\CSS_URL . '/vendor/magnific-popup.min.css', array(), '1.2.0' );
      wp_enqueue_style( base\SLUG_KEY, base\CSS_URL . '/admin.css', array( 'mpch-jquery-magnific-popup' ), base\VERSION );
      wp_enqueue_script( 'mpch-select2', base\JS_URL . '/vendor/select2.min.js', array( 'jquery' ), base\VERSION, true );
      \wp_enqueue_script( base\SLUG_KEY, base\JS_URL . '/admin-program.js', array( 'wp-util' ), true, base\VERSION );
    }
  }


  /**
   * Register Program CPT
   *
   * @return void
   */
  public function register_post_type() {
    $this->cpt = (object) array(
      'slug'   => Program::CPT,
      'config' => array(
        'labels'               => array(
          'name'               => __( 'Programs', 'memberpress-coachkit' ),
          'singular_name'      => __( 'Program', 'memberpress-coachkit' ),
          'add_new'            => __( 'Add New', 'memberpress-coachkit' ),
          'add_new_item'       => __( 'Add New Program', 'memberpress-coachkit' ),
          'edit_item'          => __( 'Edit Program', 'memberpress-coachkit' ),
          'new_item'           => __( 'New Program', 'memberpress-coachkit' ),
          'view_item'          => __( 'View Program', 'memberpress-coachkit' ),
          'search_items'       => __( 'Search Programs', 'memberpress-coachkit' ),
          'not_found'          => __( 'No Programs found', 'memberpress-coachkit' ),
          'not_found_in_trash' => __( 'No Programs found in Trash', 'memberpress-coachkit' ),
          'parent_item_colon'  => __( 'Parent Program:', 'memberpress-coachkit' ),
        ),
        'public'               => false,
        'publicly_queryable'   => true,
        'show_ui'              => true,
        'show_in_rest'         => false,
        'show_in_menu'         => base\PLUGIN_NAME,
        // 'menu_position'       => 2,
        'has_archive'          => true,
        'capability_type'      => array( 'mpch-program', 'mpch-programs' ),
        'hierarchical'         => false,
        'register_meta_box_cb' => array( $this, 'add_meta_boxes' ),
        'rewrite'              => array(
          'slug'      => Program::PERMALINK_SLUG,
          'with_font' => false,
        ),
        'supports'             => array( 'title' ),
      ),
    );

    if ( ! empty( $this->ctaxes ) ) {
      $this->cpt->config['taxonomies'] = $this->ctaxes;
    }

    register_post_type( Program::CPT, $this->cpt->config );
  }


  public function remove_row_actions( $actions ) {
    if ( get_post_type() === Program::CPT ) {
      unset( $actions['trash'] );
      unset( $actions['inline hide-if-no-js'] );
    }
    return $actions;
  }


  // Callback function to remove "Move to Trash" bulk action
  public function remove_bulk_actions_trash( $actions ) {
    unset( $actions['trash'] ); // Remove the "Move to Trash" action
    return $actions;
  }


  public function restrict_post_deletion( $post_id ) {
    if ( get_post_type( $post_id ) === Program::CPT ) {
      $enrollment = Enrollment::get_one( [ 'program_id' => $post_id ] );
      if ( $enrollment && $enrollment->id ) {
        wp_die( esc_html__( 'You are not allowed to trash programs.', 'memberpress-coachkit' ) );
      }
    }
  }

  /**
   * Add metaboxes
   *
   * @return void
   */
  public function add_meta_boxes() {
    global $post_id;
    $program = new Program( $post_id );

    add_meta_box(
      Program::METABOX,
      esc_html__( 'Program Tabs', 'memberpress-coachkit' ),
      array( $this, 'render_program_tabs' ),
      Program::CPT,
      'normal',
      'high',
      array( 'program' => $program )
    );

    add_meta_box(
      base\SLUG_KEY . '-group-metabox',
      esc_html__( 'Cohorts', 'memberpress-coachkit' ),
      array( $this, 'render_group_tabs' ),
      Program::CPT,
      'side',
      'default',
      array( 'program' => $program )
    );
  }

  /**
   * Main Program Metabox
   *
   * @param object $program the program object
   * @param array  $args args from add_meta_box()
   * @return void
   */
  public function render_group_tabs( $program, $args ) {
    $all_groups  = Group::find_all_by_program( $program->ID );
    $groups_html = ProgramHelper::group_rows_html( $all_groups );

    View::render( '/admin/groups/list', get_defined_vars() );
  }

  /**
   * Main Program Metabox
   *
   * @param object $post the post object
   * @param array  $args args from add_meta_box()
   * @return void
   */
  public function render_program_tabs( $post, $args ) {
    $all_milestones = Milestone::find_all_by_program( $post->ID )->all();
    $milestones     = ProgramHelper::milestones_html( $all_milestones );

    $all_habits = Habit::find_all_by_program( $post->ID )->all();
    $habits     = ProgramHelper::habits_html( $all_habits );

    $enrollment       = Enrollment::get_one( [ 'program_id' => $post->ID ] );
    $in_progress       = $enrollment && $enrollment->id > 0 ? true : false;

    // Load the views
    View::render( '/admin/program/program-tabs', get_defined_vars() );
    View::render( '/admin/modals/group', get_defined_vars() );

    $courses = ProgramHelper::get_courses();
    View::render( '/admin/modals/course', get_defined_vars() );

    View::render( '/admin/modals/checkin', get_defined_vars() );

    $attachments = ProgramHelper::get_attachments();
    $download_list = View::get_string( '/admin/modals/download-list', get_defined_vars() );
    View::render( '/admin/modals/download', get_defined_vars() );
  }

  /**
   * Runs after Program post has been saved
   * Save milestones and habits
   *
   * @param integer $post_id the post id
   * @return void|int
   */
  public function save_postdata( $post_id ) {

    // Don't save if there are enrollments
    $enrollment = Enrollment::get_one( [ 'program_id' => $post_id ] );
    if ( $enrollment ) {
      return;
    }

    // Verify nonce
    if ( ! \wp_verify_nonce( isset( $_POST[ Program::NONCE_STR ] ) ? $_POST[ Program::NONCE_STR ] : '', Program::NONCE_STR . \wp_salt() ) ) {
      return $post_id;
    }

    // Skip ajax
    if ( defined( 'DOING_AJAX' ) ) {
      return;
    }

    $program = new Program( $post_id );

    $this->save_milestones( $post_id, $program );
    $this->save_habits( $post_id, $program );
  }

  /**
   * Save program pool
   *
   * @param MeprProfuct $product MemberPress Product
   * @return void
   */
  public function save_program_pool( $product ) {
    $programs = isset( $_POST['mpch-programs'] ) ? lib\Validate::sanitize( wp_unslash( $_POST['mpch-programs'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    update_post_meta( $product->ID, Program::PRODUCT_META, maybe_serialize( $programs ) );
  }

  /**
   * Handle things before saving milestones
   *
   * @param array   $milestones milestones
   * @param Program $program current program
   * @return void
   */
  public function before_save_milestones( array $milestones, Program $program ) {
    $milestones     = Utils::collect( $milestones );
    $old_milestones = $program->milestones();
    ProgramHelper::remove_missing_objects( $program, $old_milestones, $milestones );
  }

  /**
   * Handle things before saving milestones
   * Note that nonce verification was done before calling this function
   *
   * @param int     $post_id post_id
   * @param Program $program current program
   * @return void
   */
  private function save_milestones( int $post_id, Program $program ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $milestones = isset( $_POST['mpch-milestones'] ) ? lib\Validate::sanitize( wp_unslash( $_POST['mpch-milestones'] ) ) : array();
    $milestones = array_filter( $milestones );

    $this->before_save_milestones( $milestones, $program );

    foreach ( $milestones as $order => $milestone_data ) {
      $milestone                 = new Milestone();
      $milestone->uuid           = isset( $milestone_data['uuid'] ) ? $milestone_data['uuid'] : '';
      $milestone->program_id     = $post_id;
      $milestone->title          = $milestone_data[ Milestone::TITLE_STR ];
      $milestone->timing         = 1 === $order ? 'after_program_starts' : 'after_previous_milestone';
      $milestone->due_length     = isset( $milestone_data[ Milestone::DUE_LENGTH_STR ] ) ? $milestone_data[ Milestone::DUE_LENGTH_STR ] : $milestone->due_length;
      $milestone->due_unit       = isset( $milestone_data[ Milestone::DUE_UNIT_STR ] ) ? $milestone_data[ Milestone::DUE_UNIT_STR ] : $milestone->due_unit;
      $milestone->enable_checkin = isset( $milestone_data[ Milestone::ENABLE_CHECKIN_STR ] ) ? $milestone_data[ Milestone::ENABLE_CHECKIN_STR ] : $milestone->enable_checkin;
      $milestone->position       = $order;

      if ( isset( $milestone_data[ Milestone::COURSES_STR ] ) ) {
        $courses            = (array) $milestone_data[ Milestone::COURSES_STR ];
        $milestone->courses = implode( ',', $courses );
      }

      if ( isset( $milestone_data[ Milestone::DOWNLOADS_STR ] ) ) {
        $downloads            = (array) $milestone_data[ Milestone::DOWNLOADS_STR ];
        $milestone->downloads = implode( ',', $downloads );
      }

      if ( is_wp_error( $milestone->store() ) ) {
        continue;
      }

      /**
       * Fires after milestone is stored.
       *
       * @param int     $milestone_data sanitized post data for milestone
       * @param Milestone $milestone   Milestone object.
       */
      do_action( 'mpch_after_save_milestone_post_request', $milestone, $milestone_data );
    }
  }

  /**
   * Handle things before saving habits
   * Note that nonce verification was done before calling this function
   *
   * @param int     $post_id post_id
   * @param Program $program current program
   * @return void
   */
  private function save_habits( int $post_id, Program $program ) {
    $habits = isset( $_POST['mpch-habits'] ) ? lib\Validate::sanitize( wp_unslash( $_POST['mpch-habits'] ) ) : array();
    $habits = array_filter( $habits );

    $this->before_save_habits( $habits, $program );

    foreach ( $habits as $order => $habit_data ) {
      if(empty($habit_data[ Habit::REPEAT_DAYS_STR ])) {
        $habit_data[ Habit::REPEAT_DAYS_STR ] = ['1','2','3','4','5','6','7'];
      }

      $repeat_days = array_filter( $habit_data[ Habit::REPEAT_DAYS_STR ] );
      $repeat_days = implode( ',', $repeat_days );

      $habit                  = new Habit();
      $habit->uuid            = isset( $habit_data['uuid'] ) ? $habit_data['uuid'] : '';
      $habit->program_id      = $post_id;
      $habit->title           = isset( $habit_data[ Habit::TITLE_STR ] ) ? $habit_data[ Habit::TITLE_STR ] : $habit->title;
      $habit->repeat_length   = isset( $habit_data[ Habit::REPEAT_LENGTH_STR ] ) ? $habit_data[ Habit::REPEAT_LENGTH_STR ] : $habit->repeat_length;
      $habit->repeat_days     = $repeat_days;
      $habit->repeat_interval = isset( $habit_data[ Habit::REPEAT_INTERVAL_STR ] ) ? $habit_data[ Habit::REPEAT_INTERVAL_STR ] : $habit->repeat_days;
      $habit->position        = isset( $order ) ? $order : '';
      $habit->enable_checkin  = isset( $habit_data[ Habit::ENABLE_CHECKIN_STR ] ) ? $habit_data[ Habit::ENABLE_CHECKIN_STR ] : $habit->enable_checkin;

      if ( isset( $habit_data[ Habit::DOWNLOADS_STR ] ) ) {
        $downloads        = (array) $habit_data[ Habit::DOWNLOADS_STR ];
        $habit->downloads = implode( ',', $downloads );
      }

      if ( is_wp_error( $habit->store() ) ) {
        continue;
      }

      /**
       * Fires after habit is stored.
       *
       * @param int     $habit_data sanitized post data for habit
       * @param Habit $habit   habit object.
       */
      do_action( 'mpch_after_save_habit_post_request', $habit, $habit_data );
    }
  }


  /**
   * Handle things before saving habits
   *
   * @param array   $habits habits
   * @param Program $program current program
   * @return void
   */
  public function before_save_habits( array $habits, Program $program ) {
    $habits     = new Collection( $habits );
    $old_habits = $program->habits();
    ProgramHelper::remove_missing_objects( $program, $old_habits, $habits );
  }


  /**
   * Handle creating or updating of group
   *
   * @return void
   */
  public function handle_save_group() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_group', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    $post = Utils::collect( wp_unslash($_POST['form']) )
    ->reduce( function($result, $entry) {
      if (Group::APPOINTMENT_URL_STR === $entry['name']) {
        $result[sanitize_key($entry['name'])] = esc_url_raw( $entry['value'] );
      } else {
        $result[sanitize_key($entry['name'])] = sanitize_text_field( $entry['value'] );
      }
      return $result;
    }, []);

    $program_id = isset( $_POST['program_id'] ) ? wp_unslash( sanitize_text_field( $_POST['program_id'] ) ) : 0;
    $group_id   = isset( $_POST['group_id'] ) ? wp_unslash( sanitize_text_field( $_POST['group_id'] ) ) : 0;

    $group                       = new Group( $group_id );
    $group->program_id           = $program_id;
    $group->title                = isset( $post[ Group::TITLE_STR ] ) ? $post[ Group::TITLE_STR ] : $group->title;
    $group->type                 = isset( $post[ Group::TYPE_STR ] ) ? $post[ Group::TYPE_STR ] : $group->type;
    $group->coach_id             = isset( $post[ Group::COACH_ID_STR ] ) ? $post[ Group::COACH_ID_STR ] : $group->coach_id;
    $group->allow_enrollment_cap = isset( $post[ Group::ALLOW_ENROLLMENT_CAP_STR ] ) ? $post[ Group::ALLOW_ENROLLMENT_CAP_STR ] : 0;
    $group->enrollment_cap       = isset( $post[ Group::ENROLLMENT_CAP_STR ] ) ? $post[ Group::ENROLLMENT_CAP_STR ] : $group->enrollment_cap;
    $group->allow_appointments   = isset( $post[ Group::ALLOW_APPOINTMENTS_STR ] ) ? $post[ Group::ALLOW_APPOINTMENTS_STR ] : '';
    $group->appointment_url      = isset( $post[ Group::APPOINTMENT_URL_STR ] ) ? esc_url($post[ Group::APPOINTMENT_URL_STR ]) : '';
    $group->start_date           = isset( $post[ Group::START_DATE_STR ] ) ? $post[ Group::START_DATE_STR ] : $group->start_date;
    $group->end_date             = isset( $post[ Group::END_DATE_STR ] ) ? $post[ Group::END_DATE_STR ] : $group->end_date;
    $group->status               = isset( $post[ Group::STATUS_STR ] ) ? $post[ Group::STATUS_STR ] : $group->status;

    if ( is_null( $group->start_date ) || empty( $group->start_date ) ) {
      $group->start_date = lib\Utils::db_lifetime();
    } else {
      $group->start_date = lib\Utils::ts_to_mysql_date( strtotime( $group->start_date ) );
    }

    if ( is_null( $group->end_date ) || empty( $group->end_date ) ) {
      $group->end_date = lib\Utils::db_lifetime();
    } else {
      $group->end_date = lib\Utils::ts_to_mysql_date( strtotime( $group->end_date ) );
    }

    // Make sure enrollment cap count is not lower than the current enrollment
    if ( $group->allow_enrollment_cap ) {
      $enrollment_count = Enrollment::get_count( [
        'program_id' => $group->program_id,
        'group_id'   => $group->id,
      ] );

      if ( $group->enrollment_cap < $enrollment_count ) {
        wp_send_json_error( esc_html__( 'Student Limit cannot be lower than current enrollments', 'memberpress-coachkit' ) );
      }
    }

    // If it's not valid URL
    if ( $group->allow_appointments && false == wp_http_validate_url($group->appointment_url) ) {
      wp_send_json_error( esc_html__( 'Appointment URL is not a valid url', 'memberpress-coachkit' ) );
    }

    $group = $group->store();

    if ( is_wp_error( $group ) ) {
      wp_send_json_error( $group->get_error_message() );
    }

    // Get groups
    $all_groups  = Group::find_all_by_program( $program_id );
    $groups_html = ProgramHelper::group_rows_html( $all_groups );

    wp_send_json_success( $groups_html );
  }

  /**
   * Handles removal of group
   *
   * @return void
   */
  public function handle_remove_group() {
     // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_group', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    // Delete group
    $group_id   = isset( $_POST['group_id'] ) ? wp_unslash( sanitize_text_field( $_POST['group_id'] ) ) : 0;
    $program_id = isset( $_POST['program_id'] ) ? wp_unslash( sanitize_text_field( $_POST['program_id'] ) ) : 0;
    $group      = Group::find( $group_id );
    $group->destroy();

    // Get groups
    $all_groups  = Group::find_all_by_program( $program_id );
    $groups_html = ProgramHelper::group_rows_html( $all_groups );

    wp_send_json_success( $groups_html );
  }

  /**
   * Handle AJAX fetching of group
   *
   * @return void
   */
  public function handle_fetch_group() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_group', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    $group_id = isset( $_POST['group_id'] ) ? wp_unslash( absint( $_POST['group_id'] ) ) : 0;
    $post_id  = isset( $_POST['post_id'] ) ? wp_unslash( absint( $_POST['post_id'] ) ) : 0;
    $group    = Group::find( $group_id );

    if ( false === $group ) {
      $group = new Group();
    }

    $data = ProgramHelper::new_group_html( $group, $post_id );
    echo wp_json_encode( $data );
    wp_die();
  }

  /**
   * Handle AJAX complete milestone task
   *
   * @return void
   */
  public function handle_complete_milestone() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_theme', 'security' );

    $enrollment_id = isset( $_POST['enrollment'] ) ? wp_unslash( absint( $_POST['enrollment'] ) ) : 0;
    $milestone_id  = isset( $_POST['milestone'] ) ? wp_unslash( absint( $_POST['milestone'] ) ) : 0;
    $enrollment    = new Enrollment( $enrollment_id );

    // Authorization check: Only admins and enrolled student can proceed
    $user_id = get_current_user_id();
    if ( $enrollment->student_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'You\'re not authorised to do this', 'memberpress-coachkit' ) );
    }

    // Check if program has started or is closed or ...
    if ( false === $enrollment->can_students_track_progress() ) {
      wp_send_json_error( esc_html__( 'You cannot track progress at this time', 'memberpress-coachkit' ) );
    }

    // Check for existing record
    $progress = StudentProgress::get_one([
      'milestone_id'  => $milestone_id,
      'enrollment_id' => $enrollment_id,
    ]);

    // Existing record found, exit
    if ( $progress instanceof StudentProgress ) {
      wp_send_json_error( esc_html__( 'You already completed this milestone', 'memberpress-coachkit' ) );
    }

    // Attempt to complete milestone
    if ( ! StudentProgress::complete_milestone( $milestone_id, $enrollment ) ) {
      wp_send_json_error( esc_html__( 'Error completing milestone', 'memberpress-coachkit' ) );
    }

    /**
     * Fires after milestone is completed.
     *
     * @param int     $milestone_id milestone id
     * @param Enrollment $enrollment   Enrollment object.
     */
    do_action( 'mpch_after_milestone_completion', $milestone_id, $enrollment );

    // Done, send success message
    wp_send_json_success( esc_html__( 'Successfully completed milestone', 'memberpress-coachkit' ) );
  }

  public function handle_complete_habit() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_theme', 'security' );

    $enrollment_id = isset( $_POST['enrollment'] ) ? wp_unslash( absint( $_POST['enrollment'] ) ) : 0;
    $habit_id      = isset( $_POST['habit_id'] ) ? wp_unslash( absint( $_POST['habit_id'] ) ) : 0;
    $date          = isset( $_POST['date'] ) ? wp_unslash( sanitize_text_field( $_POST['date'] ) ) : 0;
    $enrollment    = new Enrollment( $enrollment_id );

    // Authorization check: Only admins and enrolled student can proceed
    $user_id = get_current_user_id();
    if ( $enrollment->student_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( esc_html__( 'You\'re not authorised to do this', 'memberpress-coachkit' ) );
    }

    // Is the date one of the due dates
    $habit     = new Habit( $habit_id );
    $due_dates = StudentProgress::get_due_dates_for_habit( $habit, $enrollment );
    if ( ! in_array( $date, $due_dates ) ) {
      wp_send_json_error( esc_html__( 'Date cannot be found', 'memberpress-coachkit' ) );
    }

    // Is it already marked complete
    $progress = StudentProgress::get_one([
      'habit_id'      => $habit_id,
      'habit_date'    => $date,
      'enrollment_id' => $enrollment_id,
    ]);

    if ( $progress instanceof StudentProgress ) {
      wp_send_json_error( esc_html__( 'You already completed this milestone', 'memberpress-coachkit' ) );
    }

    if ( ! StudentProgress::complete_habit( $habit_id, $date, $enrollment ) ) {
      wp_send_json_error( esc_html__( 'Error completing habit', 'memberpress-coachkit' ) );
    }

    /**
     * Fires after habit is completed.
     *
     * @param int     $habit_id habit id
     * @param Enrollment $enrollment   Enrollment object.
     */
    do_action( 'mpch_after_habit_completion', $habit_id, $enrollment );

    // Done, send success message
    wp_send_json_success( esc_html__( 'Successfully completed habit', 'memberpress-coachkit' ) );
  }

  public function student_progress_check( $resource_id, $enrollment ) {
    if ( $enrollment->student()->has_completed_program( $enrollment ) ) {
      // Send Program Complete Email
      $program = new Program( $enrollment->program_id );
      Utils::send_program_completed_notice( $program, $enrollment );

      // TODO: Unschedule Checkins

    }
  }

  public function handle_download_attachment() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_theme', 'security' );

    $attachment_id = isset( $_POST['attachment_id'] ) ? wp_unslash( absint( $_POST['attachment_id'] ) ) : 0;
    $url           = wp_get_attachment_url( $attachment_id );
    $name          = get_the_title( $attachment_id );
    $student       = new \MeprUser( get_current_user_id() );
    // $student       = new Student( get_current_user_id() );

    /**
     * Fires after attachment is downloaded.
     *
     * @param MeprUser $student   MeprUser object.
     */
    do_action( 'mpch_attachment_downloaded', $student, $attachment_id );

    wp_send_json_success( compact( 'name', 'url' ) );
  }

  public function handle_save_group_appointments() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_students', 'security' );

    $group_id = isset( $_POST['group_id'] ) ? wp_unslash( absint( $_POST['group_id'] ) ) : 0;
    $allow_appointments = isset( $_POST['allow_appointments'] ) ? filter_var($_POST['allow_appointments'], FILTER_VALIDATE_BOOLEAN) : false;
    $appointment_url = isset( $_POST['appointment_url'] ) ? wp_unslash( esc_url_raw($_POST['appointment_url']) ) : false;

    // If it's not valid URL
    if ( $allow_appointments && false == wp_http_validate_url($appointment_url) ) {
      wp_send_json_error( esc_html__( 'Appointment URL is not a valid url', 'memberpress-coachkit' ) );
    }

    $group = new Group($group_id);
    $group->allow_appointments = $allow_appointments;
    $group->appointment_url = $appointment_url;
    $group->store();

    wp_send_json_success([
      'message' => esc_html__( 'Group appointments saved', 'memberpress-coachkit' )
    ]);
  }

  /**
   * Deletes milestones, habits and groups when program is deleted
   *
   * @param int      $post_id post id
   * @param \WP_Post $post WP Post object
   * @return void
   */
  public function delete_program_data( $post_id, $post ) {
    // For a specific post type program
    if ( Program::CPT !== $post->post_type ) {
      return;
    }

    $program = new Program( $post_id );

    try {
      ProgramHelper::remove_milestones( $program );
      ProgramHelper::remove_groups( $program );
    } catch ( \Exception $e ) {
      wp_die( 'Message: ' . esc_html( $e->getMessage() ) );
    }
  }

  /**
   * Earlier we granted "edit-others_mpch_programs" cap to coaches
   * This makes coaches able to view programs in the admin
   * But they shouldn't to be able to update programs
   *
   * @param int   $post_id post id
   * @param array $data post data
   * @return void
   */
  public function prevent_coach_updates( $post_id, $data ) {

    if ( Program::CPT !== $data['post_type'] ) {
      return;
    }

    if ( Coach::is_admin( get_current_user_id() ) ) {
      return;
    }

    wp_die( esc_html__( 'Sorry, but you cannot edit programs', 'memberpress-coachkit' ) );
  }

  /**
   * Sets custom columns for Program Table
   *
   * @param array $columns columns
   * @return array
   */
  public function set_custom_columns( $columns ) {
    unset( $columns['date'] );
    $columns[ base\SLUG_KEY . '-groups' ]      = esc_html__( 'Cohorts', 'memberpress-coachkit' );
    $columns[ base\SLUG_KEY . '-enrollments' ] = esc_html__( 'Enrollments', 'memberpress-coachkit' );
    $columns['date']                           = esc_html__( 'Date', 'memberpress-coachkit' );

    return $columns;
  }

  /**
   * Custom column output
   *
   * @param string $column column key
   * @param int    $post_id current post ID
   * @return void
   */
  public function custom_columns( $column, $post_id ) {
    switch ( $column ) {
      case base\SLUG_KEY . '-groups':
        echo esc_html( Group::get_count( array( 'program_id' => $post_id ) ) );
        break;

      case base\SLUG_KEY . '-enrollments':
        echo esc_html( Enrollment::get_count( array( 'program_id' => $post_id ) ) );
        break;
    }
  }

  /**
   * Add CoachKit tab to membership product page
   *
   * @return void
   */
  public function coaching_product_options_tab() {
    View::render( 'admin/program/coaching-product-tab' );
  }

  /**
   * Add coaching content to the tab added above
   *
   * @param \MeprProduct $product Product Object
   * @return void
   */
  public function coaching_product_options_content( $product ) {
    $all_programs = AppHelper::collect( Program::all()->get_items() );
    $all_programs = $all_programs->pluck( 'post_title', 'ID' )->all();

    $product_program_meta = maybe_unserialize( get_post_meta( $product->ID, Program::PRODUCT_META, true ) );
    $product_programs     = new Collection( $product_program_meta );
    $product_programs     = $product_programs->map(
      function ( $program ) {
        return array(
          'id'       => isset( $program['program_id'] ) ? $program['program_id'] : 0,
          'features' => array( 'messaging' ),
        );
      }
    )->all();

    $label_for_clients = AppHelper::get_label_for_client(true);

    View::render( 'admin/program/coaching-product-content', get_defined_vars() );
  }

  public function change_publish_meta_box() {
     remove_meta_box( 'submitdiv', Program::CPT, 'side' );
    add_meta_box( 'submitdiv', esc_html__( 'Publish', 'memberpress-coachkit' ), [ $this, 'custom_post_submit_meta_box' ], null, 'side' );
  }


  /**
   * HAndle AJAX filter coaches request
   *
   * @return void
   */
  public function handle_filter_downloads() {

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_group', 'security' );

    // Only admins can do this
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( 'Cheating huh?' );
    }

    $search = isset( $_POST['search'] ) ? wp_unslash( sanitize_text_field( $_POST['search'] ) ) : '';

    $posts = get_posts(
      array(
        'number'       => 8,
        's'       => $search,
        'post_type' => File::$cpt,
        'fields' => 'ids',
        'post_status' => 'publish',
        'numberposts' => 10
      )
    );

    $attachments = ProgramHelper::get_file_downloads_data($posts);
    $download_list = View::get_string( '/admin/modals/download-list', get_defined_vars() );
    wp_send_json_success( $download_list );
  }


  public function custom_post_submit_meta_box( $post, $args = array() ) {
    global $action;
    if ( $post->post_type !== Program::CPT ) {
      return;
    }

    $post_id          = (int) $post->ID;
    $post_type        = $post->post_type;
    $post_type_object = get_post_type_object( $post_type );
    $enrollment       = Enrollment::get_one( [ 'program_id' => $post_id ] );
    $can_publish      = current_user_can( $post_type_object->cap->publish_posts );
    $can_update       = $enrollment && $enrollment->id ? false : true;
    $label_for_clients = AppHelper::get_label_for_client(true);

    View::render( 'admin/program/publish-metabox', get_defined_vars() );
  }
}
