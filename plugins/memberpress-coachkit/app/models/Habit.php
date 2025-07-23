<?php
namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\lib\Collection;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * The Habit Model.
 */
class Habit extends lib\BaseModel {
  const SLUG                = 'habit';
  const TITLE_STR           = 'title';
  const TIMING_STR          = 'timing';
  const REPEAT_LENGTH_STR   = 'repeat_length';
  const REPEAT_INTERVAL_STR = 'repeat_interval';
  const REPEAT_DAYS_STR     = 'repeat_days';
  const ENABLE_CHECKIN_STR  = 'enable_checkin';
  const DOWNLOADS_STR       = 'downloads';
  const UUID_STR            = 'uuid';

  /**
   * Constructor, used to load the model
   *
   * @param int|array|object $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    $this->initialize(
      array(
        'id'              => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'title'           => array(
          'default' => null,
          'type'    => 'string',
        ),
        'timing'          => array(
          'default' => 'after_program_starts',
          'type'    => 'string',
        ),
        'repeat_length'   => array(
          'default' => 1,
          'type'    => 'integer',
        ),
        'repeat_interval' => array(
          'default' => null,
          'type'    => 'string',
        ),
        'repeat_days'     => array(
          'default' => '1,2,3,4,5,6,7',
          'type'    => 'string',
        ),
        'program_id'      => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'downloads'       => array(
          'default' => null,
          'type'    => 'string',
        ),
        'position'        => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'enable_checkin'  => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'uuid'            => array(
          'default' => '{uuid}',
          'type'    => 'string',
        ),
        'created_at'      => array(
          'default' => null,
          'type'    => 'datetime',
        ),
      ),
      $obj
    );
  }

  /**
   * Used to validate the habit object
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
   * @param bool    $instance whether to return Habit instance if true or StdClass if false
   * @return Collection Collection of Habit objects ordered by position
   */
  public static function find_all_by_program( $program_id, $instance = true ) {
    $db = new lib\Db();

    $records = $db->get_records( $db->habits, compact( 'program_id' ), 'position' );

    if ( false === $instance ) {
      return Utils::collect( $records );
    }

    $habits = array();
    foreach ( $records as $rec ) {
      $habits[] = new Habit( $rec->id );
    }

    return Utils::collect( $habits );
  }

  /**
   * Used to create or update the habit record
   *
   * @param bool $validate Validate before storing, default true
   * @return int|\WP_Error Habit ID, or WP_Error on validation error
   */
  public function store( $validate = true ) {
    if ( $validate ) {
      try {
        $this->validate();
      } catch ( lib\ValidationException $e ) {
        return new \WP_Error( get_class( $e ), $e->getMessage() );
      }
    }

    // Avoid duplicate habits in the database
    if ( isset( $this->uuid ) && empty( $this->id ) ) {
      $db    = new lib\Db();
      $habit = $db->get_one_record( $db->habits, array( 'uuid' => $this->uuid ) );

      if ( \is_object( $habit ) && isset( $habit->id ) ) {
        $this->id = $habit->id;
      }
    }

    $this->repeat_length = $this->repeat_length < 1 ? 1 : $this->repeat_length;

    if ( isset( $this->id ) && (int) $this->id > 0 ) {
      $this->update();
    } else {
      $this->id = self::create( $this );
    }

    /**
     * Fires after habit is stored.
     *
     * @param int     $habit_id Habit ID.
     * @param Habit $habit   Habit object.
     */
    do_action( 'after_save_habit', $this->id, $this );

    return $this->id;
  }

  /**
   * Find checkins by milestone
   *
   * @return Collection
   */
  public function checkins() {
    return new Collection( CheckIn::find_all_by_habit( $this->id ) );
  }


  /**
   * Used to create the habit record
   *
   * @param Habit $habit Object
   * @return int habit id
   */
  public static function create( $habit ) {
    $db    = new lib\Db();
    $attrs = $habit->get_values();

    return $db->create_record( $db->habits, $attrs );
  }

  /**
   * Get all habits
   *
   * @return array habits
   */
  public static function all() {
    return parent::get_all( 'position' );
  }

  /**
   * Used to update the habit record
   *
   * @return int The habit ID
   */
  private function update() {
    $db    = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->habits, $this->id, $attrs );
  }

  /**
   * Destroy the habit
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();

    return $db->delete_records( $db->habits, array( 'id' => $this->id ) );
  }

  /**
   * Destroy multiple habits
   *
   * @param Habit[] $habits array of habits
   * @return void
   */
  public static function destroy_many( array $habits ) {
    foreach ( $habits as $habit ) {
      $habit->destroy();
    }
  }

}
