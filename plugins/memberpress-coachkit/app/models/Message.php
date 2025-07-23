<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\helpers\AppHelper;


if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The Message Model.
 *
 * @property int $id
 * @property int $student_id
 * @property int $group_id
 * @property string $start_date
 */
class Message extends lib\BaseModel {


  const SLUG = 'mpch-messages';

  /**
   * Constructor, used to load the model
   *
   * @param int|array|object $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    $this->initialize(
      array(
        'id'         => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'sender_id'  => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'room_id'    => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'message'    => array(
          'default' => null,
          'type'    => 'string',
        ),
        'created_at' => array(
          'default' => null,
          'type'    => 'string',
        ),
      ),
      $obj
    );
  }

  /**
   * Get conversations in a room
   *
   * @param int $room_id
   * @return array
   */
  public static function get_room_conversations( $room_id, $offset = 0 ) {
    global $wpdb;
    $db = new lib\Db();

    $per_page = 20; // Number of messages to display per page.
    // $offset   = ( $page - 1 ) * $per_page; // Calculate the offset.

    $query = $wpdb->prepare("
      SELECT SQL_CALC_FOUND_ROWS m.id, m.room_id, m.sender_id, CONCAT(umfn.meta_value, ' ', umln.meta_value) as sender, m.message, m.created_at,
      GROUP_CONCAT(CONCAT(a.path, '|', a.url, '|', a.type) SEPARATOR '\n') AS attachments,
      GROUP_CONCAT(umr.meta_value) AS role
      FROM {$db->messages} AS m
      LEFT JOIN {$db->message_attachments} AS a ON m.id = a.message_id
      JOIN {$wpdb->usermeta} as umfn ON umfn.user_id = m.sender_id AND umfn.meta_key='first_name'
      JOIN {$wpdb->usermeta} as umln ON umln.user_id = m.sender_id AND umln.meta_key='last_name'
      LEFT JOIN {$wpdb->usermeta} as umr ON umr.user_id = m.sender_id AND umr.meta_key='wp_capabilities'
      WHERE m.room_id = %d
      GROUP BY m.id, m.room_id, m.sender_id, m.message, m.created_at
      ORDER BY m.created_at DESC
      LIMIT %d OFFSET %d",
      $room_id, $per_page, $offset
    ); // WPCS: unprepared SQL OK.

    $messages    = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $total_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );
    return compact( 'messages', 'total_count' );
  }

  /**
   * Get messageable contacts ie fellow group students and coaches
   *
   * @param integer $user_id
   * @return array
   */
  public static function get_recipient_list( $user_id = 0 ) {
    global $wpdb;
    $db = new lib\Db();

    if ( ! $user_id ) {
      $user_id = get_current_user_id();
    }

    $is_coach   = AppHelper::user_has_role( Coach::ROLE );
    $is_student = AppHelper::user_has_role( Student::ROLE );
    $students   = [];
    $list       = [];

    if ( $is_student ) {
      // Get other students
      $query = $wpdb->prepare("
        SELECT DISTINCT e.student_id as id, um_first.meta_value AS first_name, um_last.meta_value AS last_name, u.display_name as display_name, 'student' AS type
        FROM {$db->enrollments} e
        LEFT JOIN {$wpdb->users} AS u ON u.ID = e.student_id
        JOIN {$db->enrollments} me ON e.program_id = me.program_id AND e.group_id = me.group_id
        JOIN {$wpdb->usermeta} um_first ON e.student_id = um_first.user_id AND um_first.meta_key = 'first_name'
        JOIN {$wpdb->usermeta} um_last ON e.student_id = um_last.user_id AND um_last.meta_key = 'last_name'
        WHERE me.student_id = %d AND e.student_id != %d
        AND FIND_IN_SET('messaging', e.features) > 0",
        $user_id, $user_id
      ); // WPCS: unprepared SQL OK.

      $students = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

      // Get coaches
      $query   = $wpdb->prepare("
        SELECT DISTINCT g.coach_id as id, um_first.meta_value AS first_name, um_last.meta_value AS last_name, u.display_name as display_name, 'coach' AS type
        FROM {$db->groups} g
        LEFT JOIN {$wpdb->users} AS u ON u.ID = g.coach_id
        JOIN {$db->enrollments} e ON g.program_id = e.program_id AND g.id = e.group_id
        JOIN {$wpdb->usermeta} um_first ON g.coach_id = um_first.user_id AND um_first.meta_key = 'first_name'
        JOIN {$wpdb->usermeta} um_last ON g.coach_id = um_last.user_id AND um_last.meta_key = 'last_name'
        WHERE e.student_id = %d
        AND FIND_IN_SET('messaging', e.features) > 0",
        $user_id
      ); // WPCS: unprepared SQL OK.
      $coaches = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

      $list = array_merge( $students, $coaches );

    } elseif ( $is_coach ) {
      // Get assigned students
      $query    = $wpdb->prepare("
        SELECT DISTINCT e.student_id as id, um_first.meta_value AS first_name, um_last.meta_value AS last_name, u.display_name as display_name, 'student' AS type
        FROM {$db->enrollments} e
        LEFT JOIN {$wpdb->users} AS u ON u.ID = e.student_id
        JOIN {$db->groups} g ON e.group_id = g.id
        JOIN {$wpdb->usermeta} um_first ON e.student_id = um_first.user_id AND um_first.meta_key = 'first_name'
        JOIN {$wpdb->usermeta} um_last ON e.student_id = um_last.user_id AND um_last.meta_key = 'last_name'
        WHERE g.coach_id = %d
        AND FIND_IN_SET('messaging', e.features) > 0",
        $user_id
      ); // WPCS: unprepared SQL OK.
      $students = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

      // Get groups
      $query    = $wpdb->prepare("
        SELECT DISTINCT g.id, g.title as name, p.post_title, 'group' AS type
        FROM {$db->groups} g
        LEFT JOIN {$wpdb->posts} AS p ON g.program_id = p.ID
        WHERE g.coach_id = %d",
        $user_id
      ); // WPCS: unprepared SQL OK.
      $groups = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

      $list = array_merge( $students, $groups );
    }

    return $list;
  }

  public function store( $validate = true ) {
    if ( $validate ) {
      try {
        $this->validate();
      } catch ( lib\ValidationException $e ) {
        return new \WP_Error( get_class( $e ), $e->getMessage() );
      }
    }

    if ( isset( $this->id ) && (int) $this->id > 0 ) {
      $this->update();
    } else {
      $this->id = self::create( $this );
    }

    if ( empty( $this->id ) ) {
      return new \WP_Error( 'db-error', esc_html__( 'Error creating message', 'memberpress-coachkit' ) );
    } else {
      /**
       * Fires after message is stored.
       *
       * @param Message $message   Message object.
       */
      do_action( 'mpch_message_created', $this );
    }

    return $this->id;
  }

  /**
   * Create the message record
   *
   * @param Message $message Object
   * @return int message id
   */
  public function create( $message ) {
    $db    = new lib\Db();
    $attrs = $message->get_values();
    return $db->create_record( $db->messages, $attrs );
  }

  /**
   * Update the message record
   *
   * @return int The message ID
   */
  private function update() {
     $db   = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->messages, $this->id, $attrs );
  }

  /**
   * Destroy the message
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    return $db->delete_records( $db->messages, array( 'id' => $this->id ) );
  }
}
