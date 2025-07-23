<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit as base;
use memberpress\coachkit\lib;
use memberpress\coachkit\helpers;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The Note Model.
 *
 * @property int $id
 * @property int $student_id
 * @property int $group_id
 * @property string $start_date
 */
class Note extends lib\BaseModel {
  const SLUG = 'mpch-notes';

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
        'student_id' => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'coach_id'   => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'note'       => array(
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
   * Used to validate the milestone object
   *
   * @throws lib\ValidationException On validation failure
   */
  public function validate() {
    lib\Validate::not_empty( $this->student_id, helpers\AppHelper::get_label_for_client() );
    lib\Validate::not_empty( $this->coach_id, esc_html__( 'Sender', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->note, esc_html__( 'Note', 'memberpress-coachkit' ) );
  }

  /**
   * Create or update new note
   *
   * @param boolean $validate whether to validate
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

      if ( empty( $this->id ) ) {
        return new \WP_Error( 'db-error', esc_html__( 'Error creating note', 'memberpress-coachkit' ) );
      } else {
        /**
         * Fires after note is stored.
         *
         * @param Note $note   Note object.
         */
        do_action( 'mpch_note_created', $this );
      }
    }

    return $this->id;
  }

  /**
   * Create the note record
   *
   * @param Note $note Object
   * @return int note id
   */
  public function create( $note ) {
    $db    = new lib\Db();
    $attrs = $note->get_values();
    return $db->create_record( $db->notes, $attrs );
  }

  /**
   * Update the note record
   *
   * @return int The note ID
   */
  private function update() {
    $db    = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->notes, $this->id, $attrs );
  }

  /**
   * Destroy the note
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    return $db->delete_records( $db->notes, array( 'id' => $this->id ) );
  }

  public function group() {
    return new Group( $this->group_id );
  }
}
