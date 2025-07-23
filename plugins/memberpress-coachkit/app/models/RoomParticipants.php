<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;


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
class RoomParticipants extends lib\BaseModel {

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
        'room_id'    => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'user_id'    => array(
          'default' => 0,
          'type'    => 'integer',
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
    lib\Validate::not_empty( $this->room_id, esc_html__( 'room_id', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->user_id, esc_html__( 'user_id', 'memberpress-coachkit' ) );
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

  public static function is_user_in_room( $room_id, $user_id ) {
    return self::get_one( compact( 'room_id', 'user_id' ) );
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
    return $db->create_record( $db->room_participants, $attrs );
  }

  /**
   * Destroy the group
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    return $db->delete_records( $db->room_participants, array( 'id' => $this->id ) );
  }
}
