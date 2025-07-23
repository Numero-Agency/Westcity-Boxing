<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Group;


if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The StudentProgress Model.
 *
 * @property int $id
 * @property int $room_id
 * @property int $user_id
 * @property string $created_at
 */
class Room extends lib\BaseModel {

  // const SLUG = 'mpch-student-progress';

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
        'group_id'    => array(
          'default' => null,
          'type'    => 'string',
        ),
        'uuid'    => array(
          'default' => '',
          'type'    => 'string',
        ),
        'type'    => array(
          'default' => '',
          'type'    => 'one_to_one',
        ),
        'created_at' => array(
          'default' => null,
          'type'    => 'datetime',
        ),
      ),
      $obj
    );
  }

  /**
   * Used to validate the milestone object
   *
   * @throws lib\ValidationException On validation failure
   */
  public function validate() {
    lib\Validate::not_empty( $this->room_id, esc_html__( 'uuid', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->user_id, esc_html__( 'type', 'memberpress-coachkit' ) );
  }

  public static function get_next_room_id( $type = 'one_to_one', $group_id = '' ) {
    $uuid36 = wp_generate_uuid4();
    $uuid32 = str_replace( '-', '', $uuid36 );

    $room = new Room([
      'uuid' => sanitize_text_field($uuid32),
      'group_id' => $group_id ? absint( $group_id ) : null,
      'type' => sanitize_text_field($type),
      'created_at' => Utils::ts_to_mysql_date( time() ),
    ]);

    return $room->store();
  }

  /**
   * Returns rooms for the participants
   * Modify this query if you want it to be strict
   */
  public static function room_exists_for_participants( array $participants ) {
    global $wpdb;

    $db              = new lib\Db();
    $participant_ids = sprintf( "'%s'", implode( "','", $participants ) );

    $query = $wpdb->prepare("
      SELECT room.id
      FROM {$db->room_participants} rp
      JOIN {$db->rooms} room ON rp.room_id = room.id
      WHERE rp.user_id IN ($participant_ids) AND room.type != 'group'
      GROUP BY rp.room_id
      HAVING COUNT(DISTINCT rp.user_id) = %d", count( $participants )
    ); // WPCS: unprepared SQL OK.
    $room  = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return $room;
  }

  /**
   * Returns rooms for the participants
   * Modify this query if you want it to be strict
   */
  public static function room_exists_for_group( $group_id ) {
    global $wpdb;
    $db = new lib\Db();

    $query = $wpdb->prepare("
      SELECT id
      FROM {$db->rooms}
      WHERE group_id = %d", $group_id
    ); // WPCS: unprepared SQL OK.
    $room  = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return $room;
  }

  /**
   * Get rooms for a specific user, along with other participants in each room.
   *
   * This function retrieves the rooms a user is a participant in, along with the user IDs
   * of other participants in each room.
   *
   * @param int $user_id The user ID for whom to retrieve rooms and participants.
   *
   * @return array An array of objects representing the rooms and participants.
   */
  public static function get_rooms_for_user( $user_id ) {
    global $wpdb;

    $db = new lib\Db();

    $query = $wpdb->prepare("
      SELECT
        r.uuid,
        r.type,
        r.group_id,
        rp1.room_id,
        rp1.user_id AS user_id,
        GROUP_CONCAT(rp2.user_id) AS other_user_ids,
        MAX(m.created_at) AS latest_message_time
      FROM {$db->room_participants} rp1
      INNER JOIN {$db->room_participants} rp2 ON rp1.room_id = rp2.room_id
      INNER JOIN {$db->rooms} r ON r.id = rp1.room_id
      LEFT JOIN {$db->messages} m ON r.id = m.room_id
      WHERE rp1.user_id = %d
      GROUP BY rp1.room_id, rp1.user_id
      ORDER BY latest_message_time DESC", $user_id
    ); // WPCS: unprepared SQL OK.

    $rooms = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return $rooms;
  }

  public static function get_rooms_with_messages( $rooms ) {
    $rooms_with_messages = array_map(function ( $room ) {
      $other_participants = explode( ',', $room->other_user_ids );
      $other_user  = new \MeprUser( $other_participants[0] ); // Take the first name, currently supporting one user

      $conversations = Message::get_room_conversations( $room->room_id );
      foreach ( $conversations['messages'] as &$conversation ) {
        if ( $conversation->attachments ) {
          $attachments_array = array_map(
            function ( $attachment ) {
              list($path, $url, $type) = array_map( 'trim', explode( '|', $attachment ) );
              return [
                'path' => $path,
                'url'  => $url,
                'type' => $type,
              ];
            },
            explode( "\n", $conversation->attachments )
          );

          $conversation->attachments = $attachments_array;
        } else {
          $conversation->attachments = null;
        }
        $role = (array) maybe_unserialize( $conversation->role );
        $conversation->role = array_key_exists(Coach::ROLE, $role) ? 'coach' : 'student' ;
      }

      return (object) [
        'room_id'       => $room->room_id,
        'uuid'          => $room->uuid,
        'recipient'     => self::get_room_label( $room, $other_participants ),
        'recipient_id'  => $other_user->ID,
        'conversations' => $conversations['messages'],
        'count'         => $conversations['total_count'],
      ];
    }, $rooms);

    return $rooms_with_messages;
  }

  public static function get_room_label( $room, $other_participants ){
    $label = '';
    $current_user_id = get_current_user_id();
    $other_participants = Utils::collect( array_unique($other_participants) );

    switch ($room->type) {
      case 'one_to_one':
        $other_user_id  =  $other_participants->filter(function ($p_id) use($current_user_id) {
          return $p_id != $current_user_id;
        })->first();

        $other_user  =  new \MeprUser( $other_user_id );
        $label = $other_user->full_name();
        break;

      case 'group':
        $group  = new Group( $room->group_id );
        $label = $group->title .' - '. $group->program()->post_title;
        break;
      default:
        $label = '';
        break;
    }

    return $label;
  }

  /**
   * Create or update new enrollment
   *
   * @param  boolean $validate whether to validate
   * @return int
   */
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

    return $this->id;
  }


  /**
   * Create the student progress record
   *
   * @param  StudentProgress $progress Object
   * @return int student progress id
   */
  public function create( $progress ) {
    $db    = new lib\Db();
    $attrs = $progress->get_values();
    return $db->create_record( $db->rooms, $attrs );
  }

  /**
   * Destroy the group
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    return $db->delete_records( $db->groups, array( 'id' => $this->id ) );
  }
}
