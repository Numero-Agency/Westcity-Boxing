<?php
namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * The CheckIn Model.
 */
#[\AllowDynamicProperties]
class CheckIn extends lib\BaseModel {

  const QUESTION_STR          = 'question';
  const REMINDER_INTERVAL_STR = 'reminder_interval';
  const REMINDER_TIME_STR     = 'reminder_time';
  const CHANNEL_STR           = 'channel';

  /**
   * Constructor, used to load the model
   *
   * @param int|array|object $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    $this->initialize(
      array(
        'id'           => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'question'     => array(
          'default' => null,
          'type'    => 'string',
        ),
        'channel'      => array(
          'default' => 'email',
          'type'    => 'string',
        ),
        'milestone_id' => array(
          'default' => null,
          'type'    => 'integer',
        ),
        'habit_id'     => array(
          'default' => null,
          'type'    => 'integer',
        ),
      ),
      $obj
    );

    $this->event_actions = array(
      'mepr-event-after_program_starts',
      'mepr-event-after_previous_milestone',
    );
  }

  /**
   * Used to validate the checkin object
   *
   * @throws lib\ValidationException On validation failure
   */
  public function validate() {
    lib\Validate::not_empty( $this->question, 'question' );
  }

  /**
   * Find all by milestone
   *
   * @param int $milestone_id milestone id
   * @return CheckIn[] Array of CheckIn objects ordered by position
   */
  public static function find_all_by_milestone( $milestone_id ) {
    $db           = new lib\Db();
    $milestone_id = empty( $milestone_id ) ? -1 : $milestone_id;

    $records = $db->get_records( $db->checkins, [
      'milestone_id' => $milestone_id,
    ] );

    $checkins = array();
    foreach ( $records as $rec ) {
      $checkins[] = new CheckIn( $rec->id );
    }

    return $checkins;
  }

  /**
   * Find all by habit
   *
   * @param int $habit_id habit id
   * @return CheckIn[] Array of CheckIn objects ordered by position
   */
  public static function find_all_by_habit( $habit_id ) {
    $db       = new lib\Db();
    $habit_id = empty( $habit_id ) ? -1 : $habit_id;

    $records = $db->get_records( $db->checkins, [
      'habit_id' => $habit_id,
    ] );

    $checkins = array();
    foreach ( $records as $rec ) {
      $checkins[] = new CheckIn( $rec->id );
    }

    return $checkins;
  }

  /**
   * Find checkin by UUID string
   *
   * @param string $uuid string
   * @return object plain object, not the CheckIn class
   */
  public static function find_by_uuid( $uuid ) {
    $db      = new lib\Db();
    $checkin = $db->get_one_record( $db->checkins, array( 'uuid' => $uuid ) );

    if ( ! is_object( $checkin ) ) {
      $checkin     = new \stdClass();
      $checkin->id = 0;
    }

    return $checkin;
  }

  /**
   * Used to create or update the checkin record
   *
   * @param bool $validate Validate before storing, default true
   * @return int|\WP_Error CheckIn ID, or WP_Error on validation error
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
   * Used to create the checkin record
   *
   * @param CheckIn $checkin Object
   * @return int checkin id
   */
  public static function create( $checkin ) {
    $db    = new lib\Db();
    $attrs = $checkin->get_values();

    return $db->create_record( $db->checkins, $attrs, false );
  }

  /**
   * Get all checkins
   *
   * @return array checkins
   */
  public static function all() {
    return parent::get_all( 'position' );
  }

  /**
   * Used to update the checkin record
   *
   * @return int The checkin ID
   */
  private function update() {
    $db    = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->checkins, $this->id, $attrs );
  }

  /**
   * Get the ID of the next enrollment for a milestone with a timing of 'after_program_starts'.
   *
   * This function retrieves the ID of the next enrollment associated with a given milestone
   * when the milestone's timing is set to 'after_program_starts'.
   *
   * @param Milestone $milestone The Milestone object for which to find the next enrollment.
   * @return integer
   */
  public function get_enrollment_for_due_milestone_checkin( Milestone $milestone ) {
    global $wpdb;
    $db      = lib\Db::fetch();
    $mepr_db = new \MeprDb();

    $query = $wpdb->prepare(
      // Just select the actual enrollment id
      "SELECT e.id FROM {$db->enrollments} AS e " .
      "LEFT JOIN {$db->groups} AS g ON e.group_id = g.id " .
      "JOIN {$db->milestones} AS m ON %d = m.id " .
      "LEFT JOIN {$db->student_progress} AS sp ON e.id = sp.enrollment_id AND m.id = sp.milestone_id " .

      // Make sure that only enrollments that are
      // complete or (confirmed and in a free trial) get picked up
      'WHERE g.program_id = %d ' .

      // Ensure we grab enrollments that are after the trigger period
      "AND DATE_ADD(
                e.start_date,
                INTERVAL {$milestone->due_length} {$milestone->due_unit}
              ) <= %s " .

      // Give it a 2 day buffer period so we don't send for really old enrollments
      "AND DATE_ADD(
                DATE_ADD(
                  e.start_date,
                  INTERVAL {$milestone->due_length} {$milestone->due_unit}
                ),
                INTERVAL 2 DAY
              ) >= %s " .

      // Don't send this twice yo ... for this user
      "AND ( SELECT ev.id
                  FROM {$mepr_db->events} AS ev
                  WHERE ev.evt_id=e.id
                   AND ev.evt_id_type='mpch-milestone-checkin'
                   AND ev.event=%s
                 LIMIT 1
              ) IS NULL " .

      // Select the oldest enrollment
      'ORDER BY e.start_date ASC LIMIT 1',
      $milestone->id,
      $milestone->program_id,
      Utils::db_now(),
      Utils::db_now(),
      $milestone->timing
    ); // WPCS: unprepared SQL OK.
    // Utils::error_log( $query ); // If you want to see the SQL dump
    $res = $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $res;
  }

  /**
   * Retrieves the ID of the next enrollment for a habit check-in.
   *
   * This function queries the database to find the next enrollment associated with a habit check-in event
   * based on certain conditions, such as the habit's repeat interval, days, and program ID.
   *
   * @param Habit $habit The Habit object for which to find the next enrollment.
   *
   * @return int|false The ID of the next enrollment, or false if none is found.
   */
  public function get_enrollment_for_due_habit_checkin( Habit $habit ) {
    global $wpdb;
    $db      = lib\Db::fetch();
    $mepr_db = new \MeprDb();

    $query = $wpdb->prepare(
      "SELECT e.id
      FROM {$db->enrollments} AS e
      LEFT JOIN {$db->groups} AS g ON e.group_id = g.id
      JOIN {$db->habits} AS h ON %1d = h.id
      LEFT JOIN {$db->student_progress} AS sp ON e.id = sp.enrollment_id AND h.id = sp.habit_id AND DATE(sp.habit_date) = CURDATE()
      WHERE g.program_id = %d
      AND (
          (h.repeat_interval = 'daily' AND DATEDIFF(NOW(), e.start_date) %% h.repeat_length = 0 AND FIND_IN_SET(WEEKDAY(NOW()) + 1, h.repeat_days))
          OR (h.repeat_interval = 'weekly' AND FLOOR(DATEDIFF(NOW(), e.start_date) / 7) %% h.repeat_length = 0 AND FIND_IN_SET(WEEKDAY(NOW()) + 1, h.repeat_days))
      )
      AND ( SELECT ev.id
        FROM {$mepr_db->events} AS ev
        WHERE ev.evt_id=e.id
          AND ev.evt_id_type='mpch-habit-checkin'
          AND ev.event=%s
          AND DATE(ev.created_at) = CURDATE()
        LIMIT 1
      ) IS NULL
      AND sp.id IS NULL
      ORDER BY e.start_date ASC LIMIT 1",
      $habit->id,
      $habit->program_id,
      $habit->timing
    ); // WPCS: unprepared SQL OK.

    $res = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    // var_dump( $query ); // If you want to see the SQL dump
    // Utils::error_log( $query ); // If you want to see the SQL dump
    return $res;
  }

  /**
   * Get enrollment for due milestone checkin that is due after previous milestones are due
   *
   * @param Milestone $milestone
   * @return integer
   */
  public function get_enrollment_for_milestone_checkin_after_previous_due( Milestone $milestone ) {

    // Get intervals of previous milestones
    $intervals = Milestone::generate_milestones_date_intervals_sql( $milestone );

    global $wpdb;
    $db      = lib\Db::fetch();
    $mepr_db = new \MeprDb();
    $now     = Utils::db_now();

    $query = $wpdb->prepare(
      // Just select the actual enrollment id
      'SELECT e.id ' .
      "FROM {$db->enrollments} AS e " .
      "LEFT JOIN {$db->groups} AS g ON e.group_id = g.id " .
      'WHERE g.program_id = %d ' .

      // Ensure we grab enrollments that are after the trigger period
      'AND %1s <= "%s" ' . //phpcs:ignore

      // Give it a 2 day buffer period so we don't send for really old enrollments
      'AND DATE_ADD(
                %1s,
                INTERVAL 2 DAY
              ) >= "%s" ' . //phpcs:ignore

      // Don't send this twice yo ... for this user
      "AND ( SELECT ev.id
                  FROM {$mepr_db->events} AS ev
                WHERE ev.evt_id=e.id
                  AND ev.evt_id_type='mpch-milestone-checkin'
                   AND ev.event=%s
                 LIMIT 1
              ) IS NULL " .

      // Select the oldest enrollment
      'ORDER BY e.start_date ASC LIMIT 1',
      $milestone->program_id,
      $intervals,
      $now,
      $intervals,
      $now,
      $milestone->timing
    );  // WPCS: unprepared SQL OK.

    $res = $wpdb->get_var( $query ); // phpcs:ignore
    // var_dump( $query ); // If you want to see the SQL dump
    // Utils::error_log( $query ); If you want to see the SQL dump
    return $res;
  }

  /**
   * Destroy the checkin
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    return $db->delete_records( $db->checkins, array( 'id' => $this->id ) );
  }

  /**
   * Destroy multiple checkins
   *
   * @param CheckIn[] $checkins array of checkins
   * @return void
   */
  public static function destroy_many( array $checkins ) {
    foreach ( $checkins as $checkin ) {
      $checkin->destroy();
    }
  }

}
