<?php
namespace memberpress\coachkit\models;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Habit;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;

/**
 * The Program Model.
 */
class Program extends lib\BaseCptModel {
  const CPT               = 'mpch-program';
  const CPT_PLURAL        = 'mpch-programs';
  const METABOX           = 'mpch-program-tabs';
  const NONCE_STR         = 'mpch-program-nonce';
  const PAGE_TEMPLATE_STR = 'mpch-program-page-template';
  const PAGE_STATUS_STR   = 'mpch-program-page-status';
  const PERMALINK_SLUG    = 'program';
  const
  PRODUCT_META            = '_mpch-programs';

  /**
   * Constructor, used to load the model
   *
   * @param mixed $obj ID, array or object to load from.
   */
  public function __construct( $obj = null ) {
    parent::__construct( $obj );
    $this->load_cpt(
      $obj,
      self::CPT,
      array(
        'status' => array(
          'default' => 'enabled',
          'type'    => 'string',
        ),
      )
    );
  }

  /**
   * Retrieve all programs from the database.
   *
   * This method retrieves all programs from the database
   * that are in the 'publish' status.
   *
   * @return Collection|array An array or collection of program objects with their IDs and titles.
   */
  public static function all() {
    global $wpdb;

    $query = $wpdb->prepare("
        SELECT ID, post_title
          FROM {$wpdb->posts}
         WHERE post_type=%s
           AND post_status=%s
      ",
      self::CPT,
      'publish'
    );

    $programs = Utils::collect( $wpdb->get_results( $query ) );
    return $programs;
  }

  /**
   * Get all milestones related to this program
   *
   * @return Collection|array An array or collection of milestone objects.
   */
  public function milestones() {
    return Milestone::find_all_by_program( $this->ID );
  }

  /**
   * Get all habits related to this program
   *
   * @return Collection|array An array or collection of habit objects.
   */
  public function habits() {
    return Habit::find_all_by_program( $this->ID );
  }

  /**
   * Get all groups related to this program
   *
   * @return Collection collection of groups found
   */
  public function groups() {
    return Group::find_all_by_program( $this->ID );
  }

  // public static function get_habits( $program_id, $args = [] ) {

  // global $wpdb;
  // $db = new lib\Db();

  // $q = $wpdb->prepare("
  // SELECT *
  // FROM {$db->habits}
  // WHERE program_id = %d",
  // $program_id
  // ); // WPCS: unprepared SQL OK.

  // Check if additional arguments are provided
  // if ( ! empty( $args ) && is_array( $args ) ) {
  // Loop through the arguments and add them to the query
  // foreach ( $args as $key => $value ) {
  // $q .= $wpdb->prepare( " AND $key = %s", $value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
  // }
  // }

  // $habits = $wpdb->get_results( $q ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
  // return $habits;
  // }


  /**
   * Calculate completion date based on milestones
   *
   * @param string $start_date
   * @return string
   */
  public function completion_date_by_milestones( $start_date ) {
    $milestones = $this->milestones()->all();
    $start_date = new \DateTime( $start_date );

    foreach ( $milestones as $milestone ) {
      $start_date->modify( '+' . $milestone->due_length . ' ' . $milestone->due_unit );
    }

    return $start_date->format( 'Y-m-d' );
  }

  /**
   * Calculate expected completion date for a milestone
   *
   * @param Enrollment $enrollment
   * @param Milestone  $milestone
   * @return string
   */
  public function due_date_for_milestone( $enrollment, $milestone ) {
    $milestones  = $this->milestones()->all();
    $due_date_ts = strtotime( $enrollment->start_date );

    foreach ( $milestones as $_milestone ) {
      $due_date_ts = $_milestone->get_due_date( $due_date_ts );
      if ( absint( $milestone->id ) === absint( $_milestone->id ) ) {
        break;
      }
    }

    return AppHelper::format_date( gmdate( 'Y-m-d H:i:s', $due_date_ts ) );
  }

  /**
   * Count remaining enrollments based on total capacity and current enrollments.
   *
   * @return int|null Remaining enrollments.
   */
  public function count_remaining_enrollments() {
    $groups = Utils::collect( $this->groups() );

    // if at least one group has unlimited seats
    $has_unlimited_seats = $groups->some(function ( $group ) {
      return ! $group->allow_enrollment_cap;
    });

    if ( $has_unlimited_seats ) {
      return null;
    }

    $total = $groups->reduce(function ( $slots, $group ) {
      return $slots + $group->count_remaining_enrollments();
    }, 0);

    return $total;
  }

  /**
   * Get the next group open for enrollment
   *
   * @param Collection $groups Groups collection
   * @param int        $student_id Student user ID
   * @return Group|null
   */
  public function next_available_group( Collection $groups, $student_id = 0 ) {
    return $groups
      ->filter(function ( $group ) use ( $student_id ) {
        return $group->accepting_enrollments_from_student( $student_id );
      })
      ->map(function ( $group ) {
        $group->enrollments = count( $group->get_enrollments() );
        return $group;
      })
      ->sort_preserve_order(function( $group ) {
        return $group->enrollments;
      })
      ->first();
  }

}
