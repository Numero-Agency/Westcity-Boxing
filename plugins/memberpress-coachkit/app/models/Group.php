<?php
namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Db;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\models\Milestone;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * The Group Model.
 *
 * @property int $id
 * @property string $title
 * @property string $start_date The start date, if type is dynamic it is 0000-00-00
 * @property string $end_date The end date, if type is dynamic it is 0000-00-00
 */
#[\AllowDynamicProperties]
class Group extends lib\BaseMetaModel {

  const TITLE_STR                 = base\SLUG_KEY . '_group_title';
  const ALLOW_ENROLLMENT_CAP_STR  = 'mpch_allow_enrollment_cap';
  const ALLOW_APPOINTMENTS_STR    = 'mpch_allow_appointments';
  const ENROLLMENT_CAP_STR        = 'mpch_enrollment_cap';
  const TYPE_STR                  = 'mpch_group_type';
  const COACH_ID_STR              = 'mpch_group_coach';
  const PROGRAM_STR               = 'mpch_group_program';
  const START_DATE_STR            = 'mpch_group_start_date';
  const END_DATE_STR              = 'mpch_group_end_date';
  const STUDENT_LIMIT_STR         = 'mpch_group_student_limit';
  const COURSES_STR               = 'mpch_group_course_id';
  const STATUS_STR                = 'mpch_group_status';
  const DOWNLOADS_STR             = 'mpch_group_download_id';
  const APPOINTMENT_URL_STR       = 'mpch_appointment_url';

  /**
   * Constructor, used to load the model
   *
   * @param int|array|object $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    parent::__construct( $obj );
    $this->initialize(
      array(
        'id'                   => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'title'                => array(
          'default' => null,
          'type'    => 'string',
        ),
        'coach_id'             => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'program_id'           => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'status'               => array(
          'default' => null,
          'type'    => 'string',
        ),
        'type'                 => array(
          'default' => 'dynamic',
          'type'    => 'string',
        ),
        'enrollment_cap'       => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'allow_enrollment_cap' => array(
          'default' => 0,
          'type'    => 'boolean',
        ),
        'start_date'           => array(
          'default' => null,
          'type'    => 'datetime',
        ),
        'end_date'             => array(
          'default' => null,
          'type'    => 'datetime',
        ),
        'allow_appointments'   => array(
          'default' => 0,
          'type'    => 'boolean',
        ),
        'appointment_url'   => array(
          'default' => '',
          'type'    => 'string',
        )
      ),
      $obj
    );
  }


  /**
   * Return Coach object
   *
   * @return MeprUser User object.
   */
  public function coach() {
    return new Coach( $this->coach_id );
  }

  /**
   * Return Program object
   *
   * @return Program Program object.
   */
  public function program() {
    return new Program( $this->program_id );
  }

  /**
   * Find all by program
   *
   * @param integer $program_id program id
   * @param bool    $instance whether to return object or instances
   * @return Collection Collection of Habit objects ordered by position
   */
  public static function find_all_by_program( $program_id, $instance = true ) {
    $db = new lib\Db();

    $records = $db->get_records( $db->groups, compact( 'program_id' ) );

    if ( false === $instance ) {
      return Utils::collect( $records );
    }

    $groups = array();
    foreach ( $records as $rec ) {
      $group                    = new Group( $rec->id );
      $group->has_date_conflict = $group->check_fixed_date_conflict_with_milestones();
      $groups[]                 = $group;
    }

    return Utils::collect( $groups );
  }

  /**
   * Checks for conflicts between fixed date and program milestones.
   *
   * If the date is dynamic, it exits early and returns false.
   *
   * @return bool Returns false if date is dynamic or does not conflict with program milestones, true otherwise.
   */
  public function check_fixed_date_conflict_with_milestones() {
    // If date is dynamic, exit
    if ( 'dynamic' === $this->type ) {
      return false;
    }

    $program         = $this->program();
    $completion_date = $program->completion_date_by_milestones( $this->start_date );

    if ( gmdate( 'Y-m-d', strtotime( $this->end_date ) ) === $completion_date ) {
      return false;
    }

    return true;
  }


  /**
   * Used to validate the milestone object
   *
   * @throws lib\ValidationException On validation failure
   */
  public function validate() {
    lib\Validate::not_empty( $this->title, 'Cohort name' );
    lib\Validate::not_empty( $this->type, 'Cohort type' );
    lib\Validate::not_empty( $this->status, 'status' );
    lib\Validate::not_empty( $this->coach_id, 'coach' );
    lib\Validate::not_empty( $this->program_id, 'program' );
  }

  /**
   * Get the real start date
   *
   * @return string
   */
  public function get_start_date() {
    if ( 'dynamic' === $this->type ) {
      return Utils::ts_to_mysql_date( time() );
    }

    return $this->start_date;
  }

  /**
   * Used to create or update the group record
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

    if ( isset( $this->id ) && (int) $this->id > 0 ) {
      $this->update();
    } else {
      $this->id = self::create( $this );
    }
    /**
     * Fires after group is stored.
     *
     * @param int   $group_id Group ID.
     * @param Group $group   Group object.
     */
    do_action( 'after_save_group', $this->id, $this );

    return $this->id;
  }

  /**
   * Used to create the group record
   *
   * @param Group $group Object
   * @return int group id
   */
  public function create( $group ) {
    $db    = new lib\Db();
    $attrs = $group->get_values();
    return $db->create_record( $db->groups, $attrs );
  }

  /**
   * Used to update the group record
   *
   * @return int The group ID
   */
  private function update() {
    $db    = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->groups, $this->id, $attrs );
  }

  public function get_enrollments() {
    return Enrollment::get_all( '', '', array( 'group_id' => $this->id ) );
  }

  /**
   * Accepting enrollments if
   * 1. Status is open AND
   * 2. Start date is in the future (if type is not dynamic)
   * 3. No enrollment cap or enrollments not exceeded cap
   */
  public function accepting_enrollments() {
    return (
      'open' === $this->status &&
      ( 'dynamic' === $this->type || strtotime( $this->start_date ) >= strtotime( wp_date( 'Y-m-d' ) ) ) &&
      ( ! $this->enrollment_cap_exceeded() )
    );
  }

  /**
   * Accepting enrollments from students if
   * 1. groups is accepting enrollments AND
   * 2. not previously enrolled in the program
   *
   * @param int $student_id Student user ID
   * @return bool
   */
  public function accepting_enrollments_from_student( int $student_id = 0 ) {
    $student_id = empty( $student_id ) ? get_current_user_id() : $student_id;

    $enrollment_not_found = null === Enrollment::get_one(
      array(
        'student_id' => $student_id,
        'program_id' => $this->program_id,
      )
    );

    return (
      $this->accepting_enrollments() &&
      $enrollment_not_found
    );
  }

  /**
   * Check if the enrollment cap for this group is exceeded.
   *
   * @return bool True if the enrollment cap is exceeded, false otherwise.
   */
  public function enrollment_cap_exceeded() {
    if ( ! $this->allow_enrollment_cap ) {
      return false;
    }

    $enrollments = Enrollment::get_count(
      array(
        'group_id' => $this->id,
      )
    );

    return $enrollments >= $this->enrollment_cap;
  }

  public function count_remaining_enrollments() {
    if ( ! $this->accepting_enrollments() ) {
      return 0;
    }

    $enrollments = Enrollment::get_count(
      array(
        'group_id' => $this->id,
      )
    );

    return absint( $this->enrollment_cap - $enrollments );
  }

  /**
   * This function checks if the group is open and if the program start date allows progress tracking.
   * Useful to prevent tracking if group type is fixed and program starts in the future
   *
   * @param Enrollment $enrollment
   * @return bool Returns true if students can track progress, false otherwise.
   */
  public function can_students_document_progress( Enrollment $enrollment ) {
    $group_start_date = strtotime( $this->start_date );
    $today            = strtotime( wp_date( 'Y-m-d' ) );

    // If group is not open, exit
    if ( 'open' !== $this->status ) { return false;}

    // If program starts in the future, exit
    if ( 'fixed' === $this->type && $group_start_date > $today ) {
      return false;
    }

    // If program ended already, exit
    if ( 'fixed' === $this->type && strtotime( $this->end_date ) < $today ) {
      return false;
    }

    return true;
  }


  public function is_active() {
    return $this->status === 'open';
  }

  public static function all( $args ) {
    $db      = Db::fetch();
    $records = $db->get_records( $db->groups, $args );
    return $records;
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
