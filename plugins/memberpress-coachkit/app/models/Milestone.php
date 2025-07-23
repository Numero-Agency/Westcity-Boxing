<?php
namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\lib\Collection;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * The Milestone Model.
 */
class Milestone extends lib\BaseModel {

  const SLUG               = 'milestone';
  const TITLE_STR          = 'title';
  const TIMING_STR         = 'timing';
  const DUE_LENGTH_STR     = 'due_length';
  const DUE_UNIT_STR       = 'due_unit';
  const ENABLE_CHECKIN_STR = 'enable_checkin';
  const COURSES_STR        = 'courses';
  const DOWNLOADS_STR      = 'downloads';
  const UUID_STR           = 'uuid';

  /**
   * Constructor, used to load the model
   *
   * @param int|array|object $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    $this->initialize(
      array(
        'id'             => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'title'          => array(
          'default' => null,
          'type'    => 'string',
        ),
        'timing'         => array(
          'default' => 'after_program_starts',
          'type'    => 'string',
        ),
        'due_length'     => array(
          'default' => 1,
          'type'    => 'integer',
        ),
        'due_unit'       => array(
          'default' => null,
          'type'    => 'string',
        ),
        'program_id'     => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'downloads'      => array(
          'default' => null,
          'type'    => 'string',
        ),
        'courses'        => array(
          'default' => null,
          'type'    => 'string',
        ),
        'position'       => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'enable_checkin' => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'uuid'           => array(
          'default' => '{uuid}',
          'type'    => 'string',
        ),
        'created_at'     => array(
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
    lib\Validate::not_empty( $this->uuid, 'uuid' );
  }

  /**
   * Find all by program
   *
   * @param integer $program_id program id
   * @param bool    $instance whether to return Milestone instance if true or StdClass if false
   * @return Collection|array An array or collection of program objects with their IDs and titles.
   */
  public static function find_all_by_program( $program_id, $instance = true ) {
    $db = new lib\Db();

    $records = $db->get_records( $db->milestones, compact( 'program_id' ), 'position' );

    if ( false === $instance ) {
      return Utils::collect( $records );
    }

    $milestones = array();
    foreach ( $records as $rec ) {
      $milestones[] = new Milestone( $rec->id );
    }

    return Utils::collect( $milestones );
  }

  /**
   * Used to create or update the milestone record
   *
   * @param bool $validate Validate before storing, default true
   * @return int|\WP_Error Milestone ID, or WP_Error on validation error
   */
  public function store( $validate = true ) {
    if ( $validate ) {
      try {
        $this->validate();
      } catch ( lib\ValidationException $e ) {
        return new \WP_Error( get_class( $e ), $e->getMessage() );
      }
    }

    // Avoid duplicate milestones in the database
    if ( isset( $this->uuid ) && empty( $this->id ) ) {
      $db        = new lib\Db();
      $milestone = $db->get_one_record( $db->milestones, array( 'uuid' => $this->uuid ) );

      if ( \is_object( $milestone ) && isset( $milestone->id ) ) {
        $this->id = $milestone->id;
      }
    }

    // Format some fields
    $this->due_length = $this->due_length < 1 ? 1 : $this->due_length;

    if ( isset( $this->id ) && (int) $this->id > 0 ) {
      $this->update();
    } else {
      $this->id = self::create( $this );
    }

    /**
     * Fires after milestone is stored.
     *
     * @param int     $milestone_id Milestone ID.
     * @param Milestone $milestone   Milestone object.
     */
    do_action( 'after_save_milestone', $this->id, $this );

    return $this->id;
  }

  public function get_due_date( $start_time_ts = null ) {
    if ( is_null( $start_time_ts ) ) { $start_time_ts = time(); }
    $due_date_ts = $start_time_ts;

    switch ( $this->due_unit ) {
      case 'month':
        $due_date_ts += Utils::months( $this->due_length, $start_time_ts );
        break;
      case 'week':
        $due_date_ts += Utils::weeks( $this->due_length, $start_time_ts );
        break;
      case 'day':
        $due_date_ts += Utils::days( $this->due_length, $start_time_ts );
        break;

      default:
        break;
    }

    return $due_date_ts;
  }

  /**
   * Used to create the milestone record
   *
   * @param Milestone $milestone Object
   * @return int milestone id
   */
  public static function create( $milestone ) {
    $db    = new lib\Db();
    $attrs = $milestone->get_values();

    return $db->create_record( $db->milestones, $attrs );
  }


  /**
   * Look for previous and current milestone intervals
   * Generates nested DATE_ADD of each milestone's due date
   *
   * @param Milestone $milestone
   * @return string
   */
  public static function generate_milestones_date_intervals_sql( Milestone $milestone ) {
    global $wpdb;
    $db = lib\Db::fetch();

    $query = $wpdb->prepare("
      SELECT due_length, due_unit
      FROM {$db->milestones}
      WHERE position <= %d
      AND program_id = %d",
      $milestone->position,
      $milestone->program_id
    ); // WPCS: unprepared SQL OK.

    $milestones       = $wpdb->get_results( $query, ARRAY_A );
    $nested_intervals = '';
    foreach ( $milestones as $index => $milestone ) {
      $nested_intervals = ( $index > 0 ? "DATE_ADD($nested_intervals, " : 'DATE_ADD(e.start_date, ' ) . "INTERVAL {$milestone['due_length']} {$milestone['due_unit']})";
    }

    return $nested_intervals;
  }

  /**
   * Get all milestones
   *
   * @return array milestones
   */
  public static function all() {
    return parent::get_all( 'position' );
  }

  /**
   * Used to update the milestone record
   *
   * @return int The milestone ID
   */
  private function update() {
    $db    = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->milestones, $this->id, $attrs );
  }

  /**
   * Destroy the milestone
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();

    /**
     * Fires before a milestone is deleted.
     *
     * @param int     $milestone_id Post ID.
     * @param Milestone $milestone   Milestone object.
     */
    do_action( 'before_delete_milestone', $this->id, $this );

    return $db->delete_records( $db->milestones, array( 'id' => $this->id ) );
  }

  /**
   * Destroy multiple milestones
   *
   * @param Milestone[] $milestones array of milestones
   * @return void
   */
  public static function destroy_many( array $milestones ) {
    foreach ( $milestones as $milestone ) {
      $milestone->destroy();
    }
  }

  /**
   * Find checkins by milestone
   *
   * @return Collection
   */
  public function checkins() {
    return Utils::collect( CheckIn::find_all_by_milestone( $this->id ) );
  }

}
