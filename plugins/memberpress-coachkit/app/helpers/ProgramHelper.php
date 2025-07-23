<?php
namespace memberpress\coachkit\helpers;

use WP_Post;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\models\Habit;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\CheckIn;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Milestone;
use memberpress\coachkit\models\Enrollment;
use memberpress\downloads\helpers\Files;
use memberpress\downloads\models\File;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * Program Helper
 */
class ProgramHelper {

  /**
   * Get HTML for provided milestones
   *
   * @param array $milestones Array of milestone pbjects
   * @return string
   */
  public static function milestones_html( $milestones = array() ) {
    $html   = array();
    $html[] = View::get_string( '/admin/program/milestone-empty', get_defined_vars() );

    if ( ! empty( $milestones ) ) {
      foreach ( $milestones as $index => $milestone ) {
        $index++;
        $html[] = self::milestone_metabox_string( $milestone, $index );
      }
    }

    return $html;
  }

  /**
   * Get milestone string
   *
   * @param Milestone  $milestone Object
   * @param string|int $index milestone position
   * @return string
   */
  public static function milestone_metabox_string( Milestone $milestone, $index = '{index}' ) {
    $course_ids     = array_filter( explode( ',', $milestone->courses ) );
    $download_ids   = array_filter( explode( ',', $milestone->downloads ) );
    $checkins       = $milestone->checkins()->all();

    if ( ! is_plugin_active( 'memberpress-downloads/main.php' ) ){
      $download_ids = [];
    }

    if ( ! is_plugin_active( 'memberpress-courses/main.php' ) ){
      $course_ids = [];
    }

    $courses_html   = self::courses_html( $index, $milestone, $course_ids );
    $downloads_html = self::downloads_html( $index, $milestone, $download_ids );
    $checkins_html  = self::checkins_html( $index, $milestone );

    $uuid_str           = sprintf( 'mpch-milestones[%s][%s]', $index, Milestone::UUID_STR );
    $title_str          = sprintf( 'mpch-milestones[%s][%s]', $index, Milestone::TITLE_STR );
    $timing_str         = sprintf( 'mpch-milestones[%s][%s]', $index, Milestone::TIMING_STR );
    $due_length_str     = sprintf( 'mpch-milestones[%s][%s]', $index, Milestone::DUE_LENGTH_STR );
    $due_unit_str       = sprintf( 'mpch-milestones[%s][%s]', $index, Milestone::DUE_UNIT_STR );
    $enable_checkin_str = sprintf( 'mpch-milestones[%s][%s]', $index, Milestone::ENABLE_CHECKIN_STR );

    $footer_css = empty( $course_ids ) && empty( $download_ids ) && empty( $checkins ) ? 'hidden' : '';
    return View::get_string( '/admin/milestone/milestone-row', get_defined_vars() );
  }

  /**
   * Get HTML for milestone courses
   *
   * @param string    $index
   * @param Milestone $milestone single Milestone object
   * @param array     $course_ids
   * @return string
   */
  public static function courses_html( string $index, Milestone $milestone, array $course_ids ) {
    $courses = array();

    foreach ( $course_ids as $course_id ) {
      $courses[] = get_post( $course_id );
    }

    $wrapper_css   = empty( $courses ) ? 'hidden' : '';
    $course_id_str = sprintf( 'mpch-milestones[%s][%s][]', $index, Milestone::COURSES_STR );

    $result = View::get_string( '/admin/milestone/metabox-course', get_defined_vars() );

    return $result;
  }

  /**
   * Get HTML for habit courses
   *
   * @param string          $index
   * @param Milestone|Habit $resource
   * @param array           $download_ids
   *
   * @return string
   */
  public static function downloads_html( string $index, object $resource, array $download_ids ) {
    $downloads = self::get_file_downloads_data($download_ids);

    $wrapper_css = empty( $downloads ) ? 'hidden' : '';

    $download_id_str = sprintf( 'mpch-milestones[%s][%s][]', $index, $resource::DOWNLOADS_STR );
    $result          = View::get_string( '/admin/milestone/metabox-download', get_defined_vars() );

    if ( $resource instanceof Habit ) {
      $download_id_str = sprintf( 'mpch-habits[%s][%s][]', $index, $resource::DOWNLOADS_STR );
      $result          = View::get_string( '/admin/habits/metabox-download', get_defined_vars() );
    }

    return $result;
  }

  /**
   * Get HTML for a Check In
   *
   * @param string $index
   * @param object $resource Milestone object
   * @return string
   */
  public static function checkins_html( string $index, object $resource ) {
    return self::checkin_metabox_html( $resource, false, $index );
  }

  /**
   * Get milestone string
   *
   * @param Milestone|Habit $resource Checkins
   * @param bool            $blank whether to get string if no checkin is found
   * @param string|int      $index milestone position
   * @return string
   */
  public static function checkin_metabox_html( $resource, $blank = false, $index = '{index}' ) {
    $slug     = $resource instanceof Milestone ? 'mpch-milestones' : 'mpch-habits';
    $checkins = (array) $resource->checkins()->all();

    $title_str   = sprintf( $slug . '[%s][checkins][%s]', $index, CheckIn::QUESTION_STR );
    $channel_str = sprintf( $slug . '[%s][checkins][%s]', $index, CheckIn::CHANNEL_STR );
    $wrapper_css = empty( $checkins ) || ! $resource->enable_checkin ? 'hidden' : '';
    $result      = '';

    $label_for_client = AppHelper::get_label_for_client();

    if ( empty( $checkins ) && $blank ) {
      $checkin = new CheckIn();
      $result  = View::get_string( '/admin/milestone/metabox-checkin', get_defined_vars() );
    };

    foreach ( $checkins as $checkin ) {
      $result .= View::get_string( '/admin/milestone/metabox-checkin', get_defined_vars() );
    }

    return $result;
  }

  /**
   * Get HTML for all habits
   *
   * @param array $habits Array of habit objects
   * @return string
   */
  public static function habits_html( $habits = array() ) {
    $html   = array();
    $html[] = View::get_string( '/admin/habits/empty', get_defined_vars() );

    if ( ! empty( $habits ) ) {
      foreach ( $habits as $index => $habit ) {
        $index++;
        $html[] = self::habit_metabox_string( $habit, $index );
      }
    }

    return $html;
  }

  /**
   * Get habit string
   *
   * @param Habit      $habit Object
   * @param string|int $index habit position
   * @return string
   */
  public static function habit_metabox_string( Habit $habit, $index = '{index}' ) {
    $download_ids = array_filter( explode( ',', $habit->downloads ) );
    $checkins     = $habit->checkins()->all();

    $downloads_html = self::downloads_html( $index, $habit, $download_ids );
    $checkins_html  = self::checkins_html( $index, $habit );

    $footer_css          = empty( $courses_id ) && empty( $download_ids ) ? 'hidden' : '';
    $title_str           = sprintf( 'mpch-habits[%s][%s]', $index, Habit::TITLE_STR );
    $timing_str          = sprintf( 'mpch-habits[%s][%s]', $index, Habit::TIMING_STR );
    $repeat_days_str     = sprintf( 'mpch-habits[%s][%s]', $index, Habit::REPEAT_DAYS_STR );
    $repeat_length_str   = sprintf( 'mpch-habits[%s][%s]', $index, Habit::REPEAT_LENGTH_STR );
    $repeat_interval_str = sprintf( 'mpch-habits[%s][%s]', $index, Habit::REPEAT_INTERVAL_STR );
    $enable_checkin_str  = sprintf( 'mpch-habits[%s][%s]', $index, Habit::ENABLE_CHECKIN_STR );
    $uuid_str            = sprintf( 'mpch-habits[%s][%s]', $index, Habit::UUID_STR );

    // In line with ISO-8601 standard
    // 1 for Monday, 2 for Tuesday, 3 for Wednesday, 4 for Thursday, 5 for Friday, 6 for Saturday, 7 for Sunday
    $repeat_days = array(
      '1' => 'M',
      '2' => 'T',
      '3' => 'W',
      '4' => 'T',
      '5' => 'F',
      '6' => 'S',
      '7' => 'S',
    );

    $footer_css = empty( $download_ids ) && empty( $checkins ) ? 'hidden' : '';
    return View::get_string( '/admin/habits/habit-row', get_defined_vars() );
  }


  /**
   * Get HTML for provided groups
   *
   * @param Collection $groups Array of group pbjects
   * @return string
   */
  public static function group_rows_html( Collection $groups ) {

    if ( $groups->empty() ) {
      return;
    }

    $html = $groups->reduce(function ( string $html, Group $group, int $index ) {
      $index++;
      return $html .= self::group_string( $group, $index );
    }, '');

    return $html;
  }

  /**
   * Get HTML for provided groups
   *
   * @param Group $group group Object
   * @return string
   */
  public static function new_group_html( Group $group, $post_id ) {
    $html                     = '';
    $title_str                = Group::TITLE_STR;
    $allow_appointments_str   = Group::ALLOW_APPOINTMENTS_STR;
    $allow_enrollment_cap_str = Group::ALLOW_ENROLLMENT_CAP_STR;
    $appointment_url_str      = Group::APPOINTMENT_URL_STR;
    $enrollment_cap_str       = Group::ENROLLMENT_CAP_STR;
    $type_str                 = Group::TYPE_STR;
    $coach_id_str             = Group::COACH_ID_STR;
    $start_date_str           = Group::START_DATE_STR;
    $end_date_str             = Group::END_DATE_STR;
    $student_limit_str        = Group::STUDENT_LIMIT_STR;
    $status_str               = Group::STATUS_STR;
    $start_date_fo            = $end_date_fo = null;

    if ( ! empty( $group->start_date ) ) {
      if ( Utils::db_lifetime() === $group->start_date ) {
        $group->start_date = '';
      } else {
        $group->start_date = gmdate( 'Y-m-d', lib\Utils::db_date_to_ts( $group->start_date ) );
        $start_date_fo = date_i18n( 'F d, Y', lib\Utils::db_date_to_ts( $group->start_date ) );
      }
    }

    if ( ! empty( $group->end_date ) ) {
      if ( Utils::db_lifetime() === $group->end_date ) {
        $group->end_date = '';
      } else {
        $group->end_date = gmdate( 'Y-m-d', lib\Utils::db_date_to_ts( $group->end_date ) );
        $end_date_fo = date_i18n( 'F d, Y', lib\Utils::db_date_to_ts( $group->end_date ) );
      }
    }

    $args = array(
      'role'     => base\SLUG_KEY . '-memberpress-coach',
      'meta_key' => 'first_name',
      'orderby'  => 'meta_value',
    );

    $coaches = get_users( $args );

    $program          = $group->program();
    $program_ongoing  = Enrollment::get_one( [ 'program_id' => $program->ID ] );
    $enrollment_count = Enrollment::get_count( [
      'program_id' => $program->ID,
      'group_id'   => $group->id,
    ] );

    $label_for_client = AppHelper::get_label_for_client();
    $label_for_coach = AppHelper::get_label_for_coach();
    $label_for_clients = AppHelper::get_label_for_client(true);
    $html .= View::get_string( '/admin/groups/group-modal', get_defined_vars() );

    return $html;
  }

  /**
   * Get group string
   *
   * @param Group      $group Object
   * @param string|int $index group position
   * @return string
   */
  public static function group_string( Group $group, $index = '{index}' ) {
    $label_for_coach = AppHelper::get_label_for_coach();
    return View::get_string( '/admin/groups/row', get_defined_vars() );
  }

  /**
   * Get courses
   *
   * @return array
   */
  public static function get_courses() {
    $courses = array();
    if ( class_exists( '\memberpress\courses\models\Course' ) ) {
      $args = array(
        'post_type'      => \memberpress\courses\models\Course::$cpt,
        'posts_per_page' => '-1',
        'order'          => 'DESC',
      );

      $query   = new \WP_Query( $args );
      $courses = $query->posts;
    }
    return $courses;
  }

  /**
   * Get attachments
   *
   * @return array
   */
  public static function get_attachments() {

    if ( ! is_plugin_active( 'memberpress-downloads/main.php' ) ){
      return [];
    }

    $files = AppHelper::get_files();
    $attachments = self::get_file_downloads_data($files);

    return $attachments;
  }

  /**
   * Remove milestone/habit not in the array
   *
   * @param Program    $program current program
   * @param Collection $milestones Milestones or Habits collection
   * @param Collection $old_milestones Old Milestones or Habits
   */
  public static function remove_missing_objects( $program, Collection $old_milestones, $milestones = array() ) {
    $milestones_uuids = $milestones->pluck( 'uuid' )->all();

    // Destroy milestones removed in the UI
    $old_milestones->each(function ( $milestone ) use ( $milestones_uuids ) {
      if ( ! in_array( $milestone->uuid, $milestones_uuids, true ) ) {
        $milestone->destroy();
      }
    });

  }

  /**
   * Remove all milestones for this program
   *
   * @param Program $program current program
   */
  public static function remove_milestones( $program ) {
    $milestones = $program->milestones();

    // Remove milestones that were removed in the UI
    $milestones->each(function ( $milestone ) {
        $milestone->destroy();
    });
  }

  /**
   * Remove all groups for this program
   *
   * @param Program $program current program
   */
  public static function remove_groups( $program ) {
    $groups = $program->groups()->all();

    // Remove groups that were removed in the UI
    foreach ( $groups as $group ) {
      $group->destroy();
    }
  }

  /**
   * All coach capabilities for Program CPT
   *
   * @return array
   */
  public static function get_coach_caps() {
    return [
      'read_' . Program::CPT,
      'edit_' . Program::CPT,
      'edit_' . Program::CPT_PLURAL,
      'edit_others_' . Program::CPT_PLURAL,
      'edit_published_' . Program::CPT_PLURAL,
      'publish_' . Program::CPT_PLURAL,
      'read_private_' . Program::CPT_PLURAL,
      'delete_' . Program::CPT,
      'delete_' . Program::CPT_PLURAL,
    ];
  }

  /**
   * Get capability to edit a program
   *
   * @return string
   */
  public static function get_edit_program_capability() {
    return apply_filters( base\SLUG_KEY . '-program-capability', 'edit_mpch-programs' );
  }

  public static function get_mapped_program_with_groups( $search = '', $student_id = 0 ) {
    global $wpdb;
    $db = lib\Db::fetch();

    $search         = wp_unslash( sanitize_text_field( $search ) );
    $posts_per_page = 8;

    $query = $wpdb->prepare(
      "SELECT SQL_CALC_FOUND_ROWS *
        FROM {$wpdb->posts} p
        LEFT JOIN {$db->enrollments} e ON e.program_id = p.ID AND e.student_id = %d
        WHERE p.post_type = %s
        AND p.post_status = 'publish'
        AND p.post_title LIKE %s
        AND e.program_id IS NULL
        LIMIT %d
      ",
      $student_id,
      Program::CPT,
      '%' . $wpdb->esc_like( $search ) . '%',
      $posts_per_page
    );

    $posts    = new Collection( $wpdb->get_results( $query ) );
    $total    = $wpdb->get_var( 'SELECT FOUND_ROWS();' );
    $programs = $posts->map(function ( $post ) {
      $program       = new Program( $post->ID );
      $item          = array();
      $item['title'] = $program->post_title;

      $item['groups'] = $program->groups()->map(function ( $group ) {
        $coach    = $group->coach();
        $coach_id = wp_unslash( absint( $_GET['id'] ) );
        return (object) array(
          'id'    => $group->id,
          'title' => $group->title,
          'open'  => $group->accepting_enrollments(),
          'coach' => $coach->fullname,
          'css'   => $coach->ID === $coach_id ? 'custom-disabled-one' : '',
        );
      })->all();
      return (object) $item;
    })->all();

    return (object) array(
      'items' => $programs,
      'total' => $total,
    );
  }


  /**
   * Select days of week
   *
   * @param date  $start_date
   * @param date  $end_date
   * @param int   $week_interval
   * @param array $selected_weekdays
   * @return array
   */
  public static function select_days_of_week( $start_date, $end_date, $week_interval, $selected_weekdays ) {
    $interval = new \DateInterval( 'P1D' );
    $period   = new \DatePeriod( $start_date, $interval, $end_date );

    $selected_days = [];
    $current_week  = $start_date->format( 'W' );
    $week_counter  = 0;

    foreach ( $period as $date ) {
      $week = $date->format( 'W' );

      if ( $week !== $current_week ) {
        $current_week = $week;
        $week_counter ++;
      }

      if ( 0 !== $week_counter % $week_interval ) {
        continue;
      }

      $day_of_week = (int) $date->format( 'N' );

      if ( in_array( $day_of_week, $selected_weekdays, true ) ) {
        $selected_days[] = $date->format( 'Y-m-d' );
      }
    }

    return $selected_days;
  }


  /**
   * Select days
   *
   * @param date  $start_date
   * @param date  $end_date
   * @param int   $day_interval
   * @param array $selected_days
   * @return array
   */
  public static function select_specific_days( $start_date, $end_date, $day_interval, $selected_days ) {
    // Adjust the end date by one day so that enddate is included in the result
    $end_date->modify( '+1 day' );

    $interval = new \DateInterval( 'P' . $day_interval . 'D' );
    $period   = new \DatePeriod( $start_date, $interval, $end_date );

    $selected_dates = [];

    if ( empty( $day_interval ) ) { return $selected_dates; }

    foreach ( $period as $date ) {
      $day_of_week = (int) $date->format( 'N' );
      if ( in_array( $day_of_week, $selected_days, true ) ) {
        $selected_dates[] = $date->format( 'Y-m-d' );
      }
    }

    return $selected_dates;
  }

  public  static function get_file_downloads_data(array $ids){
    if ( ! is_plugin_active( 'memberpress-downloads/main.php' ) ){
      return array();
    }

    $downloads = array();

    foreach ( $ids as $id ) {
      $file = new File( $id );
      $downloads[] = (object) [
        'ID' => $file->ID,
        'url' => $file->url(),
        'post_title' => $file->post_title,
        'filetype' => $file->filetype,
        'thumb_url' => $file->thumb_url(),
        'file_thumb' => Files::file_thumb($file->filetype),
      ];
    }

    return $downloads;
  }


}
