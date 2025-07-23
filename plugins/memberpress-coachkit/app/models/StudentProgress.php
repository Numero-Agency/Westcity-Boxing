<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Habit;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\helpers\ProgramHelper;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The StudentProgress Model.
 *
 * @property int $id
 * @property int $student_id
 * @property int $group_id
 * @property string $start_date
 */
class StudentProgress extends lib\BaseModel {

  const SLUG = 'mpch-student-progress';

  /**
   * Constructor, used to load the model
   *
   * @param int|array|object $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    $this->initialize(
      array(
        'id'            => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'enrollment_id' => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'milestone_id'  => array(
          'default' => null,
          'type'    => 'integer',
        ),
        'habit_id'      => array(
          'default' => null,
          'type'    => 'integer',
        ),
        'habit_date'    => array(
          'default' => null,
          'type'    => 'date',
        ),
        // 'group_id'    => array(
        // 'default' => 0,
        // 'type'    => 'integer',
        // ),
        // 'student_id'    => array(
        // 'default' => 0,
        // 'type'    => 'integer',
        // ),
        'created_at'    => array(
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
    // lib\Validate::not_empty( $this->group_id, esc_html__( 'Group', 'memberpress-coachkit' ) );
    // lib\Validate::not_empty( $this->student_id, esc_html__( 'Student', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->enrollment_id, esc_html__( 'Enrollment', 'memberpress-coachkit' ) );
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
      /**
       * Fires after student progress is stored.
       *
       * @param StudentProgress $progress   StudentProgress object.
       */
      do_action( 'mpch_progress_created', $this );
    }

    if ( empty( $this->id ) ) {
      return new \WP_Error( 'db-error', esc_html__( 'Error creating student progress', 'memberpress-coachkit' ) );
    }

    return $this->id;
  }

  /**
   * Create the student progress record
   *
   * @param  StudentProgress $progress Object
   * @return int student progress id
   */
  public static function create( $progress ) {
    $db    = new lib\Db();
    $attrs = $progress->get_values();
    return $db->create_record( $db->student_progress, $attrs );
  }

  /**
   * Complete a habit for a student's progress.
   *
   * This static method creates a student progress record for completing a habit.
   *
   * @param int        $habit_id     The ID of the habit being completed.
   * @param string     $date         The date on which the habit was completed.
   * @param Enrollment $enrollment  The enrollment associated with the student.
   *
   * @return bool True if the habit completion was successfully recorded; otherwise, false.
   */
  public static function complete_habit( $habit_id, $habit_date, Enrollment $enrollment ) {
    // Create a new StudentProgress instance
    $progress = new StudentProgress();

    // Set properties for the StudentProgress record
    $progress->habit_id      = $habit_id;
    $progress->enrollment_id = $enrollment->id;
    // $progress->student_id    = $enrollment->student_id;
    // $progress->program_id    = $enrollment->program_id;
    // $progress->group_id      = $enrollment->group_id;
    $progress->habit_date = $habit_date;

    // Store the StudentProgress record and get the progress ID
    $record_status = $progress->store();

    // Check if the progress ID is numeric to confirm successful storage
    if ( is_numeric( $record_status ) ) {
      return true;
    } elseif ( is_wp_error( $record_status ) ) {
      Utils::error_log( $record_status );
      return false;
    } else {
      return false;
    }
  }

  /**
   * Complete a milestone for a student's progress.
   *
   * This static method creates a student progress record for completing a milestone.
   *
   * @param int        $milestone_id The ID of the milestone being completed.
   * @param Enrollment $enrollment  The enrollment associated with the student.
   *
   * @return bool True if the milestone completion was successfully recorded; otherwise, false.
   */
  public static function complete_milestone( $milestone_id, Enrollment $enrollment ) {
    // Create a new StudentProgress instance
    $progress = new StudentProgress();

    // Set properties for the StudentProgress record
    $progress->milestone_id  = $milestone_id;
    $progress->enrollment_id = $enrollment->id;
    // $progress->student_id    = $enrollment->student_id;
    // $progress->program_id    = $enrollment->program_id;
    // $progress->group_id      = $enrollment->group_id;

    // Store the StudentProgress record and get the progress ID
    $progress_id = $progress->store();

    // Check if the progress ID is numeric to confirm successful storage
    if ( is_numeric( $progress_id ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Get progress percentage of a student for a program, based on milestones
   *
   * @param int $program_id
   * @param int $enrollment_id
   * @return float
   */
  public static function progress_percentage( $program_id, $enrollment_id ) {
    global $wpdb;
    $db = new lib\Db();

    $query = $wpdb->prepare(
      "SELECT (COUNT(DISTINCT sp.milestone_id) / m.milestone_count) * 100 AS milestone_percentage
      FROM {$db->student_progress} sp JOIN (
        SELECT COUNT(*) AS milestone_count
        FROM {$db->milestones}
        WHERE program_id = %d
        GROUP BY program_id
      ) m ON 1=1
      WHERE sp.enrollment_id = %d AND sp.milestone_id > 0
      GROUP BY m.milestone_count",
      $program_id,
      $enrollment_id
    ); // WPCS: unprepared SQL OK.

    $percentage = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return round( absint( $percentage ) );
  }

  /**
   * Checks if a student is on track for a program, based on habits
   * If the last three due habits are not completed,you're off track
   *
   * @param int $student_id
   * @param int $program_id
   * @return boolean
   */
  public static function is_student_on_track_for_program( $student_id, $program_id ) {

    $on_track = true;
    $habits   = Habit::find_all_by_program( $program_id )->all();

    $enrollment = Enrollment::get_one([
      'student_id' => $student_id,
      'program_id' => $program_id,
    ]);

    if ( ! $enrollment ) {
      return false;
    }

    foreach ( $habits as $habit ) {
      if ( ! self::is_student_on_track_for_habit( $habit, $enrollment, $student_id ) ) {
        $on_track = false;
        break;
      }
    }

    return $on_track;
  }

  /**
   * Checks if a student is on track for a specific habit within a program.
   *
   * @param Habit      $habit The Habit object to check.
   * @param Enrollment $enrollment The Enrollment object for the student.
   * @param int        $student_id The ID of the student.
   *
   * @return bool Returns true if the student is on track for the habit, false otherwise.
   */
  public static function is_student_on_track_for_habit( $habit, $enrollment, $student_id ) {
    // Get due dates for the habit
    $due_dates    = self::get_due_dates_for_habit( $habit, $enrollment );
    $current_date = wp_date( 'Y-m-d' );

    // Make sure it's in the past
    $past_dates = [];
    foreach ( $due_dates as $date ) {
      if ( $date < $current_date ) {
        $past_dates[] = $date;
      }
    }

    // We limit to three or continue
    if ( count( $past_dates ) >= 3 ) {
      $past_dates = array_slice( $past_dates, -3 );
    } else {
      return true;
    }

    // Check if any due dates have progress records
    return self::is_habit_completed_on_any_date( $past_dates, $enrollment->id, $student_id, $habit->id );
  }

  /**
   * Checks if a student has completed all habits
   *
   * @param int $student_id
   * @param int $program_id
   * @return boolean
   */
  public static function has_student_completed_all_habits( $student_id, $program_id ) {
    $on_track = 'true';
    $habits   = Habit::find_all_by_program( $program_id );

    $enrollment = Enrollment::get_one([
      'student_id' => $student_id,
      'program_id' => $program_id,
    ]);

    $current_date = wp_date( 'Y-m-d' );

    return $habits->none(function( $habit ) use ( $enrollment ) {
      $due_dates = self::get_due_dates_for_habit( $habit, $enrollment );

      // return true if habit has not been completed
      return false === self::is_habit_completed_on_all_dates( $due_dates, $enrollment->id, $enrollment->student_id, $habit->id );
    });
  }

  /**
   * Retrieve the dates when a habit needs to be completed
   *
   * @param object     $habit
   * @param Enrollment $enrollment
   * @return array
   */
  public static function get_due_dates_for_habit( object $habit, Enrollment $enrollment ) {
    $program    = new Program( $habit->program_id );
    $start_date = new \DateTime( $enrollment->start_date );
    $end_date   = new \DateTime( AppHelper::get_completion_date( $enrollment, $program ) );
    $days       = array_map( 'absint', explode( ',', $habit->repeat_days ) );

    switch ( $habit->repeat_interval ) {
      case 'daily':
        $dates = ProgramHelper::select_specific_days( $start_date, $end_date, $habit->repeat_length, $days );
        break;

      case 'weekly':
        $dates = ProgramHelper::select_days_of_week( $start_date, $end_date, $habit->repeat_length, $days );
        break;

      default:
        $dates = [];
        break;
    }

    return $dates;
  }

  public static function get_completed_habit_dates( object $habit, $enrollment, $due_dates = [] ) {
    global $wpdb;
    $db = lib\Db::fetch();

    if ( empty( $due_dates ) ) {
      $due_dates = self::get_due_dates_for_habit( $habit, $enrollment );
    }
    $due_dates = sprintf( "'%s'", implode( "','", $due_dates ) );

    if ( ! empty( $due_dates ) && $due_dates !== "''" ) {
      $sql = $wpdb->prepare(
        "SELECT habit_date AS date
          FROM {$db->student_progress} sp
          WHERE habit_date IN ({$due_dates}) AND
          enrollment_id = %d AND
          habit_id =  %s",
        $enrollment->id,
        $habit->id
      );
    } else {
      return array();
    }

    // Execute the query
    $results = $wpdb->get_col( $sql );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $results;
  }

  /**
   * Check student progress for the given dates
   * TODO: Check if this is just mere duplicate of get_completed_habit_dates()
   *
   * @param array $dates
   * @param int   $enrollment_id
   * @param int   $habit_id
   * @return bool
   */
  public static function get_completed_dates_count_for_habit( array $dates, $enrollment_id, $habit_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    // Prepare the query placeholders
    $num_dates = count( $dates );
    $dates     = sprintf( "'%s'", implode( "','", $dates ) );

    // Prepare the query
    $query = $wpdb->prepare(
      "SELECT COUNT(*) AS progress_date
        FROM {$db->student_progress}
      WHERE enrollment_id = %d
        AND habit_id = %d
        AND habit_date IN ({$dates}
    )",
      $enrollment_id,
      $habit_id
    ); // WPCS: unprepared SQL OK.

    // Run the query
    return $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
  }

  /**
   * Check student progress for all the given dates
   *
   * @param array $dates
   * @param int   $enrollment_id
   * @param int   $student_id
   * @param int   $habit_id
   * @return bool
   */
  public static function is_habit_completed_on_all_dates( array $dates, $enrollment_id, $student_id, $habit_id ) {
    $completed_dates_count = (int) self::get_completed_dates_count_for_habit( $dates, $enrollment_id, $habit_id );
    return $completed_dates_count >= count( $dates );
  }

  /**
   * Checks if a habit is completed for any of the given dates.
   *
   * @param array $dates An array of dates to check.
   * @param int   $enrollment_id The enrollment ID.
   * @param int   $student_id The student ID.
   * @param int   $habit_id The habit ID to check.
   * @return bool True if the habit is completed for any of the given dates, false otherwise.
   */
  public static function is_habit_completed_on_any_date( array $dates, $enrollment_id, $student_id, $habit_id ) {
    $completed_dates_count = (int) self::get_completed_dates_count_for_habit( $dates, $enrollment_id, $habit_id );
    return $completed_dates_count >= 1;
  }

  /**
   * Retrieve data for a milestone completion message based on the progress ID.
   *
   * @param int $progress_id The ID of the student progress record.
   *
   * @return stdClass|false An object containing milestone completion data, or false if no data is found.
   */
  public static function get_milestone_completion_message_data( $progress_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    $sql = $wpdb->prepare("
      SELECT m.title, u.display_name, en.student_id, CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value) as name
      FROM {$db->student_progress} sp
      JOIN {$db->milestones} AS m ON m.id = sp.milestone_id
      LEFT JOIN {$db->enrollments} en ON en.id = sp.enrollment_id
      LEFT JOIN {$wpdb->users} AS u ON u.ID = en.student_id
      LEFT JOIN {$wpdb->usermeta} as pm_first_name ON pm_first_name.user_id = en.student_id AND pm_first_name.meta_key='first_name'
      LEFT JOIN {$wpdb->usermeta} as pm_last_name ON pm_last_name.user_id = en.student_id AND pm_last_name.meta_key='last_name'
      WHERE sp.id = %d",
      $progress_id
    ); // WPCS: unprepared SQL OK.

    $result = $wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
    return $result;
  }

  /**
   * Retrieve data for a habit completion message based on the progress ID.
   *
   * @param int $progress_id The ID of the student progress record.
   *
   * @return stdClass|false An object containing habit completion data, or false if no data is found.
   */
  public static function get_habit_completion_message_data( $progress_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    $sql = $wpdb->prepare("
      SELECT h.title, u.display_name, en.student_id, CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value) as name
      FROM {$db->student_progress} sp
      JOIN {$db->habits} AS h ON h.id = sp.habit_id
      LEFT JOIN {$db->enrollments} en ON en.id = sp.enrollment_id
      LEFT JOIN {$wpdb->users} AS u ON u.ID = en.student_id
      LEFT JOIN {$wpdb->usermeta} as pm_first_name ON pm_first_name.user_id = en.student_id AND pm_first_name.meta_key='first_name'
      LEFT JOIN {$wpdb->usermeta} as pm_last_name ON pm_last_name.user_id = en.student_id AND pm_last_name.meta_key='last_name'
      WHERE sp.id = %d",
      $progress_id
    ); // WPCS: unprepared SQL OK.

    $result = $wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
    return $result;
  }


  /**
   * Retrieves milestones along with their respective statuses.
   *
   * @param int $enrollment_id
   * @param int $program_id
   * @return Collection
   */
  public static function get_milestones_with_status( $enrollment_id, $program_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    // Prepare the query
    $query = $wpdb->prepare(
      "SELECT m.id, m.title, m.due_length, m.due_unit, m.courses, m.downloads,
      CASE
        WHEN p.milestone_id IS NOT NULL THEN %s
        ELSE %s
      END AS status
      FROM {$db->milestones} AS m
      LEFT JOIN {$db->student_progress} AS p
        ON m.id = p.milestone_id AND p.enrollment_id = %d
      WHERE m.program_id = %d",
      esc_html__( 'Complete', 'memberpress-coachkit' ),
      esc_html__( 'In Progress', 'memberpress-coachkit' ),
      $enrollment_id,
      $program_id
    ); // WPCS: unprepared SQL OK.

    $results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return Utils::collect( $results );
  }

  /**
   * Retrieve habits with progress for a specific enrollment and program.
   *
   * @param int $enrollment_id The enrollment ID.
   * @param int $program_id The program ID.
   * @return Collection A collection of habits with progress information.
   */
  public static function get_habits_with_user_progress( $enrollment_id, $program_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    // Prepare the query
    $query = $wpdb->prepare(
      "SELECT DISTINCT h.id, h.title, h.repeat_interval, h.repeat_length, h.repeat_days, h.downloads, h.program_id
      FROM {$db->habits} AS h
      LEFT JOIN {$db->student_progress} AS sp
        ON h.id = sp.habit_id AND sp.enrollment_id = %d
      WHERE h.program_id = %d",
      $enrollment_id,
      $program_id
    ); // WPCS: unprepared SQL OK.

    $results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return Utils::collect( $results );
  }

  /**
   * Get enrollment's last update
   *
   * @param int $enrollment_id
   * @return string
   */
  public static function get_last_updated( $enrollment_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    // Prepare the query
    $query = $wpdb->prepare(
      "SELECT created_at
        FROM {$db->student_progress}
      WHERE enrollment_id = %d
      ORDER BY created_at DESC
      LIMIT 1",
      $enrollment_id
    ); // WPCS: unprepared SQL OK.

    $results = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $results;
  }

  /**
   * Get enrollment
   *
   * @return Enrollment
   */
  public function get_enrollment() {
    $enrollment = new Enrollment( $this->enrollment_id );
    return $enrollment;
  }

  /**
   * Update the student progress record
   *
   * @return int The student progress ID
   */
  private function update() {
     $db   = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->student_progress, $this->id, $attrs );
  }

  /**
   * Destroy the group
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    return $db->delete_records( $db->student_progress, array( 'id' => $this->id ) );
  }
}
