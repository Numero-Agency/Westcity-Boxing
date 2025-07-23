<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Db;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\helpers\AppHelper;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The Coach User Model.
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
class Coach extends lib\BaseModel {

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
  const ROLE                       = base\SLUG_KEY . '-memberpress-coach';

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


  public static function all( $args = [] ) {
    global $wpdb;

    $sql = "
    SELECT u.ID, u.display_name, CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value) as name
      FROM {$wpdb->users} as u
        LEFT JOIN {$wpdb->usermeta} as pm_first_name ON pm_first_name.user_id = u.ID AND pm_first_name.meta_key='first_name'
        LEFT JOIN {$wpdb->usermeta} as pm_last_name ON pm_last_name.user_id = u.ID AND pm_last_name.meta_key='last_name'
        LEFT JOIN {$wpdb->usermeta} as um
          ON u.ID = um.user_id
      WHERE um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE %s
      ORDER BY name
    ";

    $sql     = $wpdb->prepare( $sql, '%' . $wpdb->esc_like( self::ROLE ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $coaches = $wpdb->get_results( $sql ); // // phpcs:ignore WordPress.DB.PreparedSQL

    foreach ( $coaches as &$coach ) {
      $coach->name = empty( trim( $coach->name ) ) ? $coach->display_name : $coach->name;
    }

    return $coaches;
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

    $args   = array();
    $args[] = "um.meta_key = '{$wpdb->prefix}capabilities'";
    $args[] = 'um.meta_value LIKE "%' . $wpdb->esc_like( self::ROLE ) . '%"';

    if ( isset( $params['r_program'] ) && $params['r_program'] != 'all' && is_numeric( $params['r_program'] ) ) {
      $args[] = $wpdb->prepare( "u.ID IN (
        SELECT DISTINCT coach_id
        FROM {$db->groups}
        WHERE program_id = %d
      )", $params['r_program'] );
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
      "LEFT JOIN {$wpdb->usermeta} AS um ON u.ID = um.user_id",
      "LEFT JOIN {$wpdb->usermeta} AS pm_first_name ON pm_first_name.user_id = u.ID AND pm_first_name.meta_key='first_name'",
      "LEFT JOIN {$wpdb->usermeta} AS pm_last_name ON pm_last_name.user_id = u.ID AND pm_last_name.meta_key='last_name'",
      "/* IMPORTANT */ JOIN {$mepr_db->members} AS m ON m.user_id=u.ID",
      // "LEFT JOIN {$db->groups} AS grp ON grp.coach_id=u.ID",
      "LEFT JOIN {$mepr_db->events} AS last_login ON m.last_login_id=last_login.id",
    );

    return Db::list_table( $cols, "{$wpdb->users} AS u", $joins, $args, $order_by, $order, $paged, $search, $perpage );
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

      if ( ! empty( $maybe_user->ID ) ) { // User with this email, so update
        throw new lib\CreateException( esc_html__( 'User exists already', 'memberpress-coachkit' ) );
      } else { // Insert the user
        $id = wp_insert_user( (array) $this->rec );
      }
    }

    if ( is_wp_error( $id ) ) {
      throw new lib\CreateException( sprintf( __( 'Error saving %s: %s', 'memberpress-coachkit' ), AppHelper::get_label_for_coach(), $id->get_error_message() ) );
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

  public function destroy() {}

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
    return (
      $this->ID > 0 &&
      current_user_can( 'manage_options' )
    );
  }

  public static function get_programs( $id ) {
    global $wpdb;
    $db = new lib\Db();

    $q = $wpdb->prepare("
      SELECT DISTINCT program_id
      FROM {$db->groups}
      WHERE coach_id = %d
      ",
      $id
    );

    $programs = $wpdb->get_results( $q );

    return $programs;
  }

  public function get_active_students() {
    global $wpdb;
    $db = new lib\Db();

    $q = $wpdb->prepare("
      SELECT *
      FROM {$db->enrollments}
      WHERE group_id IN (
        SELECT id
        FROM {$db->groups}
        WHERE coach_id = %d
      );
      ",
      $this->ID
    );

    // Get all the enrollments for a coach
    $enrollments = AppHelper::collect( $wpdb->get_results( $q ) );

    // check if the enrollment's group is active
    $active_students = $enrollments->reduce(function ( $bag, $e ) {
      $group = new Group( $e->group_id );
      if ( $group->is_active() ) {
        $bag[] = $e;
      }
      return $bag;
    }, []);

    return $active_students;
  }

  /**
   * Get coach groups
   *
   * @return array
   */
  public function get_groups() {
    global $wpdb;
    $db = Db::fetch();

    $sql = $wpdb->prepare("
      SELECT g.*, p.post_title AS program
      FROM {$db->groups} AS g
      JOIN {$wpdb->posts} AS p ON g.program_id = p.ID
      WHERE g.coach_id = %d", $this->ID);

    $groups = $wpdb->get_results( $sql );

    foreach ( $groups as $group ) {
      $group->url = AppHelper::get_student_group_url( $group->id );
    }

    return $groups;
  }

  /**
   * Get coach students
   *
   * @return array
   */
  public function students() {
    $groups = Student::all( [ 'coach_id' => $this->ID ] );
    return $groups;
  }
}
