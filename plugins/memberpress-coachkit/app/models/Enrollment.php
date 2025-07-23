<?php

namespace memberpress\coachkit\models;

use memberpress\coachkit\lib\Db;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\lib\Events;
use memberpress\coachkit\lib\Activity;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\helpers\AppHelper;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * The Enrollment Model.
 *
 * @property int $id
 * @property int $student_id
 * @property int $group_id
 * @property string $start_date
 */
class Enrollment extends lib\BaseModel {
  const SLUG = 'mpch-enrollments';

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
        'group_id'   => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'program_id' => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'txn_id'     => array(
          'default' => 0,
          'type'    => 'integer',
        ),
        'features'   => array(
          'default' => '',
          'type'    => 'string',
        ),
        'start_date' => array(
          'default' => null,
          'type'    => 'string',
        ),
        'end_date'   => array(
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
    lib\Validate::not_empty( $this->student_id, AppHelper::get_label_for_client() );
    lib\Validate::not_empty( $this->group_id, esc_html__( 'Group', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->program_id, esc_html__( 'Program', 'memberpress-coachkit' ) );
    lib\Validate::not_empty( $this->start_date, esc_html__( 'Start Date', 'memberpress-coachkit' ) );
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
   * Return Student object
   *
   * @return Student Program object.
   */
  public function student() {
    return new Student( $this->student_id );
  }

  /**
   * Create or update new enrollment
   *
   * @param boolean $validate whether to validate
   * @return int|\WP_Error
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
       * Fires after enrollment is created the first time.
       *
       * @param Enrollment $enrollment   Enrollment object.
       */
      do_action( 'mpch_enrollment_created', $this );
    }

    if ( empty( $this->id ) ) {
      return new \WP_Error( 'db-error', esc_html__( 'Error creating enrollment', 'memberpress-coachkit' ) );
    } else {
      /**
       * Fires after enrollment is created or updated.
       *
       * @param Enrollment $enrollment   Enrollment object.
       */
      do_action( 'mpch_enrollment_updated', $this );
    }

    return $this->id;
  }

  /**
   * Create the enrollment record
   *
   * @param Enrollment $enrollment Object
   * @return int enrollment id
   */
  public function create( $enrollment ) {
    $db    = new lib\Db();
    $attrs = $enrollment->get_values();
    return $db->create_record( $db->enrollments, $attrs );
  }

  /**
   * Update the enrollment record
   *
   * @return int The enrollment ID
   */
  private function update() {
    $db    = new lib\Db();
    $attrs = $this->get_values();
    return $db->update_record( $db->enrollments, $this->id, $attrs );
  }

  /**
   * Destroy the group
   *
   * @return int|false Returns number of rows affected or false
   */
  public function destroy() {
    $db = new lib\Db();
    $query = $db->delete_records( $db->enrollments, array( 'id' => $this->id ) );

    if( $query ){
      /**
       * Fires after enrollment is created the first time.
       *
       * @param Enrollment $enrollment   Enrollment object.
       */
      do_action( 'mpch_enrollment_removed', $this );
    }

    return $query;
  }

  public function group() {
    return new Group( $this->group_id );
  }

  /**
   * Determines if students can track their progress in the group based on various conditions.
   *
   * @return bool Returns true if students can track progress, false otherwise.
   */
  public function can_students_track_progress() {
    $group = $this->group();
    return $group->can_students_document_progress( $this );
  }

  /**
   * Check if student is enrolled with coach
   *
   * @return array
   */
  public static function is_student_enrolled_with_coach( $student_id, $coach_id ) {
    global $wpdb;
    $db = Db::fetch();

    $sql = $wpdb->prepare("
      SELECT e.*
      FROM {$db->enrollments} AS e
      INNER JOIN {$db->groups} AS g ON e.group_id = g.id
      WHERE e.student_id = %d AND g.coach_id = %d
      LIMIT 1", $student_id, $coach_id);

    $enrollment = $wpdb->get_row( $sql );
    return is_object( $enrollment ) ? $enrollment : false;
  }

  /**
   * Retrieve data for a program completion message based on the progress ID.
   *
   * @param int $enrollment_id The ID of the student progress record.
   *
   * @return stdClass|false An object containing program completion data, or false if no data is found.
   */
  public static function get_program_started_message_data( $enrollment_id ) {
    global $wpdb;
    $db = lib\Db::fetch();

    $sql = $wpdb->prepare("
      SELECT u.display_name, p.post_title as title, en.student_id, CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value) as name
      FROM {$db->enrollments} en
      LEFT JOIN {$wpdb->posts} AS p ON en.program_id = p.ID
      LEFT JOIN {$wpdb->users} AS u ON u.ID = en.student_id
      LEFT JOIN {$wpdb->usermeta} as pm_first_name ON pm_first_name.user_id = en.student_id AND pm_first_name.meta_key='first_name'
      LEFT JOIN {$wpdb->usermeta} as pm_last_name ON pm_last_name.user_id = en.student_id AND pm_last_name.meta_key='last_name'
      WHERE en.id = %d",
      $enrollment_id
    ); // WPCS: unprepared SQL OK.

    $result = $wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
    return $result;
  }
}
