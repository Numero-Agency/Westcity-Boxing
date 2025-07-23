<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Db;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\helpers\StudentHelper;
use memberpress\coachkit\models\StudentProgress;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The Student Model.
 *
 * @property int $ID                       The unique identifier for the student.
 * @property mixed $first_name             The first name of the student.
 * @property mixed $last_name              The last name of the student.
 * @property mixed $user_login             The login username of the student.
 * @property mixed $user_nicename          The nice name for the user.
 * @property mixed $user_email             The email address of the student.
 * @property mixed $user_url               The URL associated with the student (if any).
 * @property mixed $user_pass              The password for the student's account.
 * @property mixed $user_message           A message related to the user (if applicable).
 * @property mixed $user_registered        The date and time when the user registered.
 * @property mixed $user_activation_key    The activation key for user account confirmation.
 * @property mixed $user_status            The status of the user account (active, inactive, etc.).
 * @property mixed $signup_notice_sent     Indicates whether a signup notice has been sent.
 * @property mixed $display_name           The display name of the student.
 * @property mixed $send_user_notification Whether to send a notification to the user.
 */
class Student extends lib\BaseModel {
  const ID_STR                     = 'ID';
  const FIRST_NAME_STR             = 'first_name';
  const LAST_NAME_STR              = 'last_name';
  const USERNAME_STR               = 'user_login';
  const EMAIL_STR                  = 'user_email';
  const PASSWORD_STR               = 'user_pass';
  const SEND_USER_NOTIFICATION_STR = 'send_user_notification';
  const USER_MESSAGE_STR           = 'mepr_user_message';
  const UUID_STR                   = 'uuid';
  const NONCE_STR                  = 'mepr_users_nonce';
  const ROLE                       = base\SLUG_KEY . '-memberpress-student';

  /**
   * Used to prevent welcome notification from sending multiple times
   *
   * @var string
   */
  public static $signup_notice_sent_str = 'signup_notice_sent';

  /**
   * Defaults to loading by id
   *
   * @param int $id coach id
   */
  public function __construct( $id = null ) {
    $this->attrs = array();
    $this->initialize_new_user(); // A bit redundant I know - But this prevents a nasty error when Standards = STRICT in PHP
    $this->load_user_data_by_id( $id );
  }

  /**
   * Load user by ID, loads if user is a coach
   *
   * @param int|null $id user id
   * @return void
   */
  public function load_user_data_by_id( $id = null ) {
    if ( empty( $id ) || ! is_numeric( $id ) ) {
      $this->initialize_new_user();
    } else {
      $wp_user_obj = Utils::get_user_by( 'id', $id );
      if ( $wp_user_obj instanceof \WP_User && in_array( self::ROLE, $wp_user_obj->roles, true ) ) {
        $this->load_wp_user( $wp_user_obj );
        $this->load_meta();
      } else {
        $this->initialize_new_user();
      }
    }

    // This must be here to ensure that we don't pull an encrypted
    // password, encrypt it a second time and store it
    unset( $this->user_pass );
  }

  /**
   * Load user by User ID, loads whether or not user is a coach
   *
   * @param int|null $id user id
   * @return void
   */
  public function load_user_data_by_user_id( $id = null ) {
    if ( empty( $id ) || ! is_numeric( $id ) ) {
      $this->initialize_new_user();
    } else {
      $wp_user_obj = Utils::get_user_by( 'id', $id );
      if ( $wp_user_obj instanceof \WP_User ) {
        $this->load_wp_user( $wp_user_obj );
        $this->load_meta();
      } else {
        $this->initialize_new_user();
      }
    }

    // This must be here to ensure that we don't pull an encrypted
    // password, encrypt it a second time and store it
    unset( $this->user_pass );
  }

  protected function initialize_new_user() {
    if ( ! isset( $this->attrs ) || ! is_array( $this->attrs ) ) {
      $this->attrs = array();
    }

    $u = array(
      'ID'                  => null,
      'first_name'          => null,
      'last_name'           => null,
      'user_login'          => null,
      'user_nicename'       => null,
      'user_email'          => null,
      'user_url'            => null,
      'user_pass'           => null,
      'user_message'        => null,
      'user_registered'     => null,
      'user_activation_key' => null,
      'user_status'         => null,
      'signup_notice_sent'  => null,
      'display_name'        => null,
      'send_user_notification' => null,
    );

    // Initialize user_meta variables
    foreach ( $this->attrs as $var ) {
      $u[ $var ] = null;
    }

    $this->rec = (object) $u;

    return $this->rec;
  }

  public function load_wp_user( $wp_user_obj ) {
    $this->rec->ID                  = $wp_user_obj->ID;
    $this->rec->user_login          = $wp_user_obj->user_login;
    $this->rec->user_nicename       = ( isset( $wp_user_obj->user_nicename ) ) ? $wp_user_obj->user_nicename : '';
    $this->rec->user_email          = $wp_user_obj->user_email;
    $this->rec->user_url            = ( isset( $wp_user_obj->user_url ) ) ? $wp_user_obj->user_url : '';
    $this->rec->user_pass           = $wp_user_obj->user_pass;
    $this->rec->user_message        = stripslashes( $wp_user_obj->user_message );
    $this->rec->user_registered     = $wp_user_obj->user_registered;
    $this->rec->user_activation_key = ( isset( $wp_user_obj->user_activation_key ) ) ? $wp_user_obj->user_activation_key : '';
    $this->rec->user_status         = ( isset( $wp_user_obj->user_status ) ) ? $wp_user_obj->user_status : '';
    // We don't need this, and as of WP 3.9 -- this causes wp_update_user() to wipe users role/caps!!!
    // $this->rec->role = (isset($wp_user_obj->role))?$wp_user_obj->role:'';
    $this->rec->display_name = ( isset( $wp_user_obj->display_name ) ) ? $wp_user_obj->display_name : '';
  }

  public function load_meta() {
    $this->rec->first_name         = get_user_meta( $this->ID, self::FIRST_NAME_STR, true );
    $this->rec->last_name          = get_user_meta( $this->ID, self::LAST_NAME_STR, true );
    $this->rec->signup_notice_sent = get_user_meta( $this->ID, self::$signup_notice_sent_str, true );
    $this->rec->user_pass          = get_user_meta( $this->ID, self::PASSWORD_STR, true );
    $this->rec->user_message       = get_user_meta( $this->ID, self::USER_MESSAGE_STR, true );
    $this->rec->uuid               = $this->load_uuid();
  }

  /** Retrieve or generate the uuid depending on whether its in the database or not */
  public function load_uuid( $force = false ) {
    $uuid = get_user_meta( $this->ID, self::UUID_STR, true );

    if ( $force || empty( $uuid ) ) {
      $uuid = md5( base64_encode( uniqid() ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
      update_user_meta( $this->ID, self::UUID_STR, $uuid );
    }

    return $uuid;
  }

  public static function all( $type = 'objects', $args = array(), $order_by = '', $limit = '' ) {
    global $wpdb;

    $db = Db::fetch();

    if ( 'objects' === $type || 'models' === $type ) {
      $sql = "
      SELECT *
        FROM {$wpdb->users} as u
          LEFT JOIN {$wpdb->usermeta} AS um
            ON u.ID = um.user_id
        WHERE um.meta_key = '{$wpdb->prefix}capabilities'
          AND um.meta_value LIKE %s
      ";

      $sql     = $wpdb->prepare( $sql, '%' . $wpdb->esc_like( self::ROLE ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL
      $records = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL

      if ( 'objects' === $type ) {
        $users = $records;
      } elseif ( 'models' === $type ) {
        $users = array();
        foreach ( $records as $record ) {
          $users[] = new Coach( $record->ID );
        }
      }
    } elseif ( 'ids' === $type ) {
      $sql = "
      SELECT u.ID
        FROM {$wpdb->users} as u
          LEFT JOIN {$wpdb->usermeta} AS um
            ON u.ID = um.user_id
        WHERE um.meta_key = '{$wpdb->prefix}capabilities'
          AND um.meta_value LIKE %s
      ";

      $sql   = $wpdb->prepare( $sql, '%' . $wpdb->esc_like( self::ROLE ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
      $users = $wpdb->get_col( $sql ); // // phpcs:ignore WordPress.DB.PreparedSQL
    }

    return $users;
  }

  public static function list_table(
    $order_by = '',
    $order = '',
    $paged = '',
    $search = '',
    $search_field = 'any',
    $perpage = 10,
    $params = null,
    $include_fields = false
  ) {
    global $wpdb;
    $db      = Db::fetch();
    $mepr_db = \MeprDb::fetch();

    if ( is_null( $params ) ) {
      $params = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
    }

    if ( empty( $order_by ) ) {
      $order_by = 'registered';
      $order    = 'DESC';
    }

    $cols = array(
      'ID'              => 'u.ID',
      'username'        => 'u.user_login',
      'email'           => 'u.user_email',
      'name'            => "CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value)",
      'display_name'    => 'u.display_name',
      'first_name'      => 'pm_first_name.meta_value',
      'last_name'       => 'pm_last_name.meta_value',
      'last_login_date' => 'IFNULL(last_login.created_at, NULL)',
      'registered'      => 'u.user_registered',
    );

    $args = array();
    // $args[] = 'um.meta_key = "{$wpdb->prefix}capabilities"';
    $args[] = 'um.meta_value LIKE "%' . $wpdb->esc_like( self::ROLE ) . '%"';

    if ( isset( $params['r_coach'] ) && 'all' != $params['r_coach'] && is_numeric( $params['r_coach'] ) ) {
      $args[] = $wpdb->prepare( 'grp.coach_id = %d', $params['r_coach'] );
    }

    if ( isset( $params['r_group'] ) && 'all' != $params['r_group'] && is_numeric( $params['r_group'] ) ) {
      $args[] = $wpdb->prepare( 'grp.id = %d', $params['r_group'] );
    }

    if ( isset( $params['r_program'] ) && 'all' != $params['r_program'] && is_numeric( $params['r_program'] ) ) {
      $args[] = $wpdb->prepare( 'grp.program_id = %d', $params['r_program'] );
    }

    if ( is_multisite() ) {
      $args[] = $wpdb->prepare(
        "
          (SELECT COUNT(*)
             FROM {$wpdb->usermeta} AS um_cap
            WHERE um_cap.user_id=u.ID
              AND um_cap.meta_key=%s) > 0
        ",
        $wpdb->get_blog_prefix() . 'user_level'
      );
    }

    $joins = array(
      "LEFT JOIN {$wpdb->usermeta} AS um ON u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities'",
      "LEFT JOIN {$wpdb->usermeta} AS pm_first_name ON pm_first_name.user_id = u.ID AND pm_first_name.meta_key='first_name'",
      "LEFT JOIN {$wpdb->usermeta} AS pm_last_name ON pm_last_name.user_id = u.ID AND pm_last_name.meta_key='last_name'",
      "/* IMPORTANT */ JOIN {$mepr_db->members} AS m ON m.user_id=u.ID",
      "LEFT JOIN {$db->enrollments} AS enrollment ON enrollment.student_id=u.ID",
      "LEFT JOIN {$db->groups} AS grp ON enrollment.group_id = grp.id",
      "LEFT JOIN {$mepr_db->events} AS last_login ON m.last_login_id=last_login.id",
    );

    return Db::list_table( $cols, "{$wpdb->users} AS u", $joins, $args, $order_by, $order, $paged, $search, $perpage, true );
  }

  public function mgm_fullname() {
    return AppHelper::get_fullname( $this->user() );
  }

  public function user() {
    return new \WP_User( $this->ID );
  }

  /**
   * Used to validate the milestone object
   *
   * @throws lib\ValidationException On validation failure
   */
  public function validate() {
    lib\Validate::not_empty( $this->first_name, esc_html__( 'First Name', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->last_name, esc_html__( 'Last Name', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->user_email, esc_html__( 'Email', 'memberpress-coachkit' ) );
    lib\Validate::is_email( $this->user_email, esc_html__( 'Email', 'memberpress-coachkit' ) );
  }


  /**
   * Store a coach
   * if ID, update user and maybe add coach role
   * if no ID, create user and add coach role
   *
   * @param bool $validate Validate before storing, default true
   * @throws lib\CreateException Exception
   * @return int|\WP_Error Milestone ID, or WP_Error on validation error
   */
  public function store( $validate = true ) {

    if ( $validate ) {
      try {
        $this->validate();
      } catch ( lib\ValidationException $e ) {
        throw new lib\CreateException( $e->getMessage() );
      }
    }

    if ( isset( $this->ID ) && ! is_null( $this->ID ) ) {
      $id = wp_update_user( (array) $this->rec );
    } else {
      // Check if the email is already in use
      $maybe_user = get_user_by( 'email', $this->user_email );

      if ( ! empty( $maybe_user->ID ) ) { // User with this email, throw error
        throw new lib\CreateException( esc_html__( 'User exists already', 'memberpress-coachkit' ) );
      } else { // Insert the user
        $id = wp_insert_user( (array) $this->rec );
      }
    }

    if ( is_wp_error( $id ) ) {
      throw new lib\CreateException( sprintf( __( 'Error saving %s: %s', 'memberpress-coachkit' ), AppHelper::get_label_for_client(), $id->get_error_message() ) );
    } else {
      // If user was just created, send user notification
      if ( ! $this->ID && $this->send_user_notification ) {
        wp_new_user_notification( $id, null, 'user' );
      }
      $this->rec->ID = $id;
    }

    if ( ! in_array( self::ROLE, (array) $this->user()->roles, true ) ) {
      $this->user()->add_role( self::ROLE );
    }

    return $id;
  }


  /**
   * Get enrollment data
   *
   * @param array $extra contains features you may optionally want to load
   * @return Collection
   */
  public function get_enrollment_data( $extra = array(), $coach_id = 0 ) {
    global $wpdb;
    $db = Db::fetch();

    $sql = "
    SELECT e.*, p.post_title, CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value) as coach_name, g.appointment_url as group_appt_url
    FROM {$db->enrollments} AS e
    JOIN {$wpdb->posts} AS p ON e.program_id = p.ID
    ";

    if ( $coach_id ) {
      $joins[] = $wpdb->prepare( "JOIN {$db->groups} AS g ON g.id = e.group_id AND g.coach_id = %d", $coach_id );
    } else {
      $joins[] = "JOIN {$db->groups} AS g ON g.id = e.group_id";
    }

    $joins[] = "JOIN {$wpdb->usermeta} as pm_first_name ON pm_first_name.user_id = g.coach_id AND pm_first_name.meta_key='first_name'";
    $joins[] = "JOIN {$wpdb->usermeta} as pm_last_name ON pm_last_name.user_id = g.coach_id AND pm_last_name.meta_key='last_name'";

    // Add the WHERE condition for student_id
    $where[] = $wpdb->prepare( 'e.student_id = %d', $this->ID );

    // Combine the JOINs and WHERE conditions into the SQL query
    $sql .= implode( ' ', $joins );
    $sql .= ' WHERE ' . implode( ' AND ', $where );

    $enrollments = AppHelper::collect( $wpdb->get_results( $sql ) );

    $enrollments = $enrollments->map(function ( $e ) use ( $extra ) {
      return (object) array(
        'id'          => $e->id,
        'title'       => $e->post_title,
        'student_url' => StudentHelper::get_profile_url( $e->student_id ),
        'coach'       => $e->coach_name,
        'started'     => Utils::date_is_in_future( $e->start_date ) ? sprintf( '%s %s', esc_html__( 'Starts on', 'memberpress-coachkit' ), AppHelper::format_date( $e->start_date ) ) : Utils::format_date_readable( $e->start_date ),
        'progress'    => StudentProgress::progress_percentage( $e->program_id, $e->id ),
        'off_track'   => ! StudentProgress::is_student_on_track_for_program( $e->student_id, $e->program_id ),
        'milestones'  => in_array( 'milestones', $extra ) ? StudentProgress::get_milestones_with_status( $e->id, $e->program_id )->all() : [],
        'public_url'  => in_array( 'public_url', $extra ) ? AppHelper::get_single_enrollment_url( $e->id ) : '',
        'appointment_url'  => $e->group_appt_url,
      );
    });

    return $enrollments;
  }

  public function get_active_membership_data() {
    $subscriptions = new Collection( \MeprSubscription::get_all_active_by_user_id( $this->ID ) );
    $subscriptions = $subscriptions->map(function ( $sub ) {
      $sub     = isset( $sub->sub_type ) && trim( $sub->sub_type ) === 'transaction' ? new \MeprTransaction( $sub->id ) : new \MeprSubscription( $sub->id );
      $product = $sub->product();

      ob_start();
      \MeprProductsHelper::display_invoice( $product );
      $terms = ob_get_clean();

      return (object) array(
        'id'      => $sub->id,
        'product' => $product->post_title,
        'terms'   => $terms,
        'url'     => get_permalink( $product->ID ),
      );
    });

    return $subscriptions;
  }

  public function get_notes_data() {
    global $wpdb;
    $db = Db::fetch();

    $sql = $wpdb->prepare("
      SELECT *
      FROM {$db->notes} AS n
      WHERE n.student_id = %d ORDER BY created_at DESC", $this->ID);

    $notes = Utils::collect( $wpdb->get_results( $sql ) );
    return $notes;
  }

  public static function get_active_programs( $id ) {
    $db = lib\Db::fetch();

    $enrollments     = AppHelper::collect( $db->get_records( $db->enrollments, array( 'student_id' => $id ) ) );
    $active_programs = $enrollments->reduce(function ( $bag, $e ) {
      $group = new Group( $e->group_id );
      if ( $group->is_active() ) {
        $bag[] = $e;
      }
      return $bag;
    });

    return null === $active_programs ? array() : $active_programs;
  }

  /**
   * Has the user started the program?
   *
   * @param  integer $user_id
   * @param  integer $program_id
   * @return boolean Existence of UserProgress
   */
  public static function has_started_program( $user_id, $program_id ) {
    global $wpdb;
    $db = new lib\Db();

    $program    = new Program( $program_id );
    $lesson_ids = $db->prepare_array( '%d', $program->lessons( 'ids' ) );

    if ( empty( $lesson_ids ) ) {
      $lesson_ids = 0;
    }

    $q                      = $wpdb->prepare(
      "SELECT COUNT(*)
          FROM {$db->user_progress} AS up
         WHERE up.user_id = %d
           AND up.lesson_id IN ($lesson_ids)
           AND up.program_id = %d
      ",
      $user_id,
      $program->ID
    );
    $completed_lesson_count = $wpdb->get_var( $q );

    return ( $completed_lesson_count > 0 );
  }

  public function has_completed_program( Enrollment $enrollment ) {
    $milestone_progress = StudentProgress::progress_percentage( $enrollment->program_id, $enrollment->id );
    $habit_progress     = StudentProgress::has_student_completed_all_habits( $enrollment->student_id, $enrollment->program_id );
    return 100 === absint( $milestone_progress ) && true === $habit_progress;
  }

  public function get_groups() {
    global $wpdb;
    $db = Db::fetch();

    $sql = $wpdb->prepare("
      SELECT g.title AS title, p.post_title AS program, e.id AS enrollment_id
      FROM {$db->groups} AS g
      JOIN {$wpdb->posts} AS p ON g.program_id = p.ID
      JOIN {$db->enrollments} AS e ON g.id = e.group_id
      WHERE e.student_id = %d", $this->ID);

    $groups = $wpdb->get_results( $sql );

    foreach ( $groups as $group ) {
      $group->url = AppHelper::get_single_enrollment_url( $group->enrollment_id );
    }

    return $groups;
  }

  /**
   * Checks if coach is admin
   *
   * @param int $id user id
   * @return boolean
   */
  public static function is_admin( $id ) {
    $coach = new self( $id );
    return $coach->is_admin;
  }

  /**
   * Magic method that returns if coach is an admin
   *
   * @return boolean
   */
  public function mgm_is_admin() {
    return ( $this->ID > 0 &&
      current_user_can( 'manage_options' )
    );
  }

  public function mgm_id() {
    return $this->ID;
  }

  public function destroy() {     }
}
