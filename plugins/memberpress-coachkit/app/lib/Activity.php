<?php

namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use memberpress\coachkit\models\Habit;
use memberpress\coachkit\models\Message;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\models\Milestone;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\helpers\StudentHelper;
use memberpress\coachkit\models\Note;
use memberpress\coachkit\models\StudentProgress;

class Activity {

  const STUDENT_STR             = 'student';
  const USERS_STR               = 'users';
  const PROGRAM_STARTED_STR     = 'program-started';
  const MESSAGE_CREATED_STR     = 'message-created';
  const NOTE_CREATED_STR        = 'note-created';
  const PROGRAM_COMPLETED_STR   = 'program-completed';
  const MILESTONE_STARTED_STR   = 'milestone-started';
  const MILESTONE_COMPLETED_STR = 'milestone-completed';
  const HABIT_STARTED_STR       = 'habit-started';
  const HABIT_COMPLETED_STR     = 'habit-completed';
  const ATTACHMENT_DOWNLOAD_STR = 'attachment-downloaded';
  const UNENROLLED_STR          = 'student_unenrolled';

  /**
   * All student events, excluding checkin events
   *
   * @var array
   */
  public static $events = array(
    self::PROGRAM_STARTED_STR,
    self::MESSAGE_CREATED_STR,
    self::NOTE_CREATED_STR,
    self::PROGRAM_COMPLETED_STR,
    self::MILESTONE_STARTED_STR,
    self::MILESTONE_COMPLETED_STR,
    self::HABIT_STARTED_STR,
    self::HABIT_COMPLETED_STR,
    self::UNENROLLED_STR,
  );

  public static function record( $event, $obj, $args = '', $type = '' ) {
    // Nothing to record? Hopefully this stops some ghost duplicate reminders we are seeing
    // Gotta use ->rec here to avoid weird shiz from happening hopefully
    if ( ( ! isset( $obj->id ) || ! $obj->id ) && ( ! isset( $obj->ID ) || ! $obj->ID ) ) {
      return;
    }

    $mepr_db  = new \MeprDb();
    $e        = new \MeprEvent();
    $e->event = $event;
    $e->args  = $args;

    // Just turn objects into json for fun
    if ( is_array( $args ) || is_object( $args ) ) {
      $e->args = wp_json_encode( $args );
    }

    if ( $obj instanceof \MeprUser || $obj instanceof Student ) {
      $e->evt_id      = $obj->rec->ID;
      $e->evt_id_type = 'mpch-student';
    } elseif ( $obj instanceof Message ) {
      $e->evt_id      = $obj->rec->id;
      $e->evt_id_type = 'mpch-message';
    } elseif ( $obj instanceof Note ) {
      $e->evt_id      = $obj->rec->id;
      $e->evt_id_type = 'mpch-note';
    } elseif ( $obj instanceof StudentProgress ) {
      $e->evt_id      = $obj->rec->id;
      $e->evt_id_type = 'mpch-progress';
    } elseif ( $obj instanceof Enrollment ) {
      $e->evt_id      = $obj->id;
      $e->evt_id_type = $type ? $type : 'mpch-enrollment';
    } else {
      return;
    }

    $duplicate = $mepr_db->get_records($mepr_db->events, array(
      'event'       => $e->event,
      'args'        => $e->args,
      'evt_id'      => $e->evt_id,
      'evt_id_type' => $e->evt_id_type,
    ));

    if ( empty( $duplicate ) ) {
      return $e->store();
    }

    return null;
  }

  /**
   * Get recent activities
   *
   * @param integer $page
   * @param array   $events
   * @param int     $student_id
   * @return array
   */
  public static function get_recent_activities( $page = 1, $events = array(), $student_id = 0 ) {
    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    global $wpdb;

    $db      = Db::fetch();
    $mepr_db = new \MeprDb();

    $page           = $page;
    $items_per_page = 12; // Number of items to load per page
    $offset         = ( $page - 1 ) * $items_per_page;

    if ( empty( array_filter( $events ) ) ) {
      $events = self::$events;
    }

    $query = '';

    foreach ( $events as $key => $event ) {
      switch ( $event ) {
        case 'program-started':
        case 'program-completed':
          if ( ! empty( $query ) ) {
            $query .= ' UNION ALL ';
          }

          $query .= "
            SELECT ev.*
            FROM {$mepr_db->events} AS ev
            JOIN {$db->enrollments} AS en ON ev.evt_id = en.id
            WHERE ev.event = '{$event}'";

          if ( $student_id ) {
            // If student_id is provided, add the WHERE clause to filter by student_id.
            $query .= $wpdb->prepare( ' AND en.student_id = %d', $student_id );
          }
          break;

        case 'message-created':
          if ( ! empty( $query ) ) {
            $query .= ' UNION ALL ';
          }

          $query .= "
            SELECT ev.*
            FROM {$mepr_db->events} AS ev
            JOIN {$db->messages} AS m ON ev.evt_id = m.id
            LEFT JOIN {$wpdb->prefix}mpch_room_participants AS rp ON rp.room_id = m.room_id
            WHERE ev.event = '{$event}'
              AND m.sender_id = rp.user_id
          ";

          if ( $student_id ) {
            // If student_id is provided, add the WHERE clause to filter by student_id.
            $query .= $wpdb->prepare( ' AND rp.user_id = %d', $student_id );
          }
          break;

        case 'note-created':
          if ( ! empty( $query ) ) {
            $query .= ' UNION ALL ';
          }

          $query .= "
            SELECT ev.*
            FROM {$mepr_db->events} AS ev
            JOIN {$db->notes} AS n ON ev.evt_id = n.id
            WHERE ev.event = '{$event}'
          ";

          if ( $student_id ) {
            // If student_id is provided, add the WHERE clause to filter by student_id.
            $query .= $wpdb->prepare( ' AND n.student_id = %d', $student_id );
          }
          break;

        case 'habit-completed':
        case 'milestone-completed':
          if ( ! empty( $query ) ) {
            $query .= ' UNION ALL ';
          }

          $query .= "
            SELECT ev.*
            FROM {$mepr_db->events} AS ev
            JOIN {$db->student_progress} AS sp ON sp.id = ev.evt_id
            JOIN {$db->enrollments} AS en ON en.id = sp.enrollment_id
            WHERE ev.event = '{$event}'
          ";
          if ( $student_id ) {
            // If student_id is provided, add the WHERE clause to filter by student_id.
            $query .= $wpdb->prepare( ' AND en.student_id = %d', $student_id );
          }
          break;

        default:
          // $query = ""; // Handle the case when the event type doesn't match any of the above.
          break;
      }
    }

    $query   = $wpdb->prepare( "SELECT * FROM ({$query}) AS combined_result ORDER BY combined_result.id DESC LIMIT %d OFFSET %d", $items_per_page, $offset );

    $results = $wpdb->get_results( $query );

    // Get the total count of rows
    $total_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );
    $max_pages   = ceil( $total_count / $items_per_page );

    $activities = self::prepare_activities( AppHelper::collect( $results ), $student_id );
    return compact( 'activities', 'max_pages', 'total_count' );
  }

  public static function get_notifications_for_user( $student_id, $page = 1, $events_cat = array( 'message', 'program', 'milestone', 'habit' ) ) {
    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    global $wpdb;

    $db      = Db::fetch();
    $mepr_db = new \MeprDb();

    $page           = $page;
    $items_per_page = 12; // Number of items to load per page
    $offset         = ( $page - 1 ) * $items_per_page;

    $query = '';

    if ( in_array( 'program', $events_cat ) ) {
      // First select for 'program' or when $events_cat is empty.
      $query .= $wpdb->prepare("
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->enrollments} AS en ON ev.evt_id = en.id
        WHERE ev.evt_id_type = 'mpch-enrollment' AND en.student_id = %d
      ", $student_id);
    }

    if ( in_array( 'message', $events_cat ) ) {
      // Second select for 'message' or when $events_cat is empty.
      if ( ! empty( $query ) ) {
        $query .= ' UNION ALL ';
      }
      $query .= $wpdb->prepare("
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->messages} AS m ON ev.evt_id = m.id
        LEFT JOIN {$wpdb->prefix}mpch_room_participants AS rp ON rp.room_id = m.room_id
        WHERE rp.user_id = %d
          AND ev.evt_id_type = 'mpch-message'
          AND m.sender_id != rp.user_id
      ", $student_id);
    }

    if ( in_array( 'milestone', $events_cat ) ) {
      // Third select for 'milestone' or 'habit' or when $events_cat is empty.
      if ( ! empty( $query ) ) {
        $query .= ' UNION ALL ';
      }
      $query .= $wpdb->prepare("
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->student_progress} AS sp ON sp.id = ev.evt_id
        JOIN {$db->enrollments} AS en ON en.id = sp.enrollment_id
        WHERE ev.event LIKE '%milestone%' AND en.student_id = %d
      ", $student_id);
    }

    if ( in_array( 'habit', $events_cat ) ) {
      // Fourth select for 'habit' or 'habit' or when $events_cat is empty.
      if ( ! empty( $query ) ) {
        $query .= ' UNION ALL ';
      }
      $query .= $wpdb->prepare("
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->student_progress} AS sp ON sp.id = ev.evt_id
        JOIN {$db->enrollments} AS en ON en.id = sp.enrollment_id
        WHERE ev.event LIKE '%habit%' AND en.student_id = %d
      ", $student_id);
    }

    $query   = $wpdb->prepare( "SELECT * FROM ({$query}) AS combined_result ORDER BY combined_result.id DESC LIMIT %d OFFSET %d", $items_per_page, $offset );
    $results = $wpdb->get_results( $query );

    // Get the total count of rows
    $total_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );
    $max_pages   = ceil( $total_count / $items_per_page );

    $activities = self::prepare_activities( AppHelper::collect( $results ), $student_id, true );
    return compact( 'activities', 'max_pages', 'total_count' );
  }

  public static function get_notifications_for_coach( $coach_id, $page = 1, $events_cat = array( 'message', 'program', 'milestone', 'habit' ) ) {
    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    global $wpdb;

    $db      = Db::fetch();
    $mepr_db = new \MeprDb();

    $q = $wpdb->prepare("
      SELECT student_id
      FROM {$db->enrollments}
      WHERE group_id IN (
        SELECT id
        FROM {$db->groups}
        WHERE coach_id = %d
      );
      ",
      $coach_id
    );

    // Get all the enrollments for a coach
    $coach_students     = $wpdb->get_col( $q );
    $coach_students_ids = sprintf( "'%s'", implode( "','", $coach_students ) );

    $page           = $page;
    $items_per_page = 100; // Number of items to load per page
    $offset         = ( $page - 1 ) * $items_per_page;

    $query = '';

    if ( in_array( 'program', $events_cat ) ) {
      // First select for 'program' or when $events_cat is empty.
      $query .= "
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->enrollments} AS en ON ev.evt_id = en.id
        JOIN {$db->groups} AS gr ON gr.id = en.group_id
        WHERE ev.evt_id_type = 'mpch-enrollment' AND en.student_id IN ($coach_students_ids)
        AND gr.coach_id = $coach_id
      ";
    }

    if ( in_array( 'message', $events_cat ) ) {
      // Second select for 'message' or when $events_cat is empty.
      if ( ! empty( $query ) ) {
        $query .= ' UNION ALL ';
      }
      $query .= "
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->messages} AS m ON ev.evt_id = m.id
        LEFT JOIN {$wpdb->prefix}mpch_room_participants AS rp ON rp.room_id = m.room_id
        WHERE rp.user_id = $coach_id
          AND ev.evt_id_type = 'mpch-message'
          AND m.sender_id IN ($coach_students_ids)
      ";
    }

    if ( in_array( 'milestone', $events_cat ) ) {
      // Third select for 'milestone' or 'habit' or when $events_cat is empty.
      if ( ! empty( $query ) ) {
        $query .= ' UNION ALL ';
      }
      $query .= "
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->student_progress} AS sp ON sp.id = ev.evt_id
        JOIN {$db->enrollments} AS en ON en.id = sp.enrollment_id
        WHERE ev.event LIKE '%milestone%' AND en.student_id IN ($coach_students_ids)
      ";
    }

    if ( in_array( 'habit', $events_cat ) ) {
      // Fourth select for 'habit' or 'habit' or when $events_cat is empty.
      if ( ! empty( $query ) ) {
        $query .= ' UNION ALL ';
      }
      $query .= "
        SELECT ev.*
        FROM {$mepr_db->events} AS ev
        JOIN {$db->student_progress} AS sp ON sp.id = ev.evt_id
        JOIN {$db->enrollments} AS en ON en.id = sp.enrollment_id
        WHERE ev.event LIKE '%habit%' AND en.student_id IN ($coach_students_ids)
      ";
    }

    $query   = $wpdb->prepare( "SELECT * FROM ({$query}) AS combined_result ORDER BY combined_result.id DESC LIMIT %d OFFSET %d", $items_per_page, $offset );
    $results = $wpdb->get_results( $query );

    // Get the total count of rows
    $total_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );
    $max_pages   = ceil( $total_count / $items_per_page );

    $activities = self::prepare_activities( AppHelper::collect( $results ), 0, true );
    return compact( 'activities', 'max_pages' );
  }

  public static function prepare_activities( $results, $student_id, $notificaton = false ) {
    $current_user_id = get_current_user_id();
    $activities      = $results->reduce( function ( $basket, $activity ) use ( $notificaton, $student_id ) {

      $new_activity = array();
      $args         = json_decode( $activity->args );
      switch ( $activity->event ) {
        case self::PROGRAM_STARTED_STR:
          $message = Enrollment::get_program_started_message_data( $activity->evt_id );
          $name    = empty( trim( $message->name ) ) ? $message->display_name : $message->name;

          if ( is_admin() ) {
            $new_activity['message'] = sprintf( '<a href="%s">%s</a> %s "%s" %s', StudentHelper::get_profile_url( $message->student_id ), $name, esc_html__( 'started', 'memberpress-coachkit' ), $message->title, esc_html__( 'program', 'memberpress-coachkit' ) );
          } else {
            $new_activity['message'] = sprintf( '%s %s "%s" %s', $name, esc_html__( 'started', 'memberpress-coachkit' ), $message->title, esc_html__( 'program', 'memberpress-coachkit' ) );
          }
          break;
        case 'message-created':
          $message     = Message::get_one( $activity->evt_id );
          $sender      = new \MeprUser( $message->sender_id );
          $sender_name = $sender->full_name();
          if ( $notificaton && $student_id != $message->sender_id ) {
            $new_activity['message'] = "$sender_name sent you a message";
          } else {
            $new_activity['message'] = "$sender_name sent a message";
          }
          break;
        case 'note-created':
          if ( ! isset( $args->coach_id ) || empty( $args->coach_id ) ) {
            $new_activity['message'] = 'Note added';
          } else {
            $sender                  = new \MeprUser( $args->coach_id );
            $sender_name             = $sender->full_name();
            $new_activity['message'] = "$sender_name added a note";
          }
          break;
        case 'attachment-downloaded':
          $student                 = new Student( $activity->evt_id );
          $attachment              = get_post( $args->attachment_id );
          $new_activity['message'] = "$student->fullname downloaded " . pathinfo( $attachment->post_title, PATHINFO_FILENAME );
          break;
        case self::HABIT_COMPLETED_STR:
          $message = StudentProgress::get_habit_completion_message_data( $activity->evt_id );

          if ( $message ) {
            $name = empty( trim( $message->name ) ) ? $message->display_name : $message->name;
            if ( is_admin() ) {
              $new_activity['message'] = sprintf( '<a href="%s">%s</a> %s "%s" %s', StudentHelper::get_profile_url( $message->student_id ), $name, esc_html__( 'completed', 'memberpress-coachkit' ), $message->title, esc_html__( 'habit', 'memberpress-coachkit' ) );
            } else {
              $new_activity['message'] = sprintf( '%s %s "%s" %s', $name, esc_html__( 'completed', 'memberpress-coachkit' ), $message->title, esc_html__( 'habit', 'memberpress-coachkit' ) );
            }
          }
          break;
        case self::MILESTONE_COMPLETED_STR:
          $message = StudentProgress::get_milestone_completion_message_data( $activity->evt_id );
          if ( $message ) {
            $name = empty( trim( $message->name ) ) ? $message->display_name : $message->name;
            if ( is_admin() ) {
              $new_activity['message'] = sprintf( '<a href="%s">%s</a> %s "%s" %s', StudentHelper::get_profile_url( $message->student_id ), $name, esc_html__( 'completed', 'memberpress-coachkit' ), $message->title, esc_html__( 'milestone', 'memberpress-coachkit' ) );
            } else {
              $new_activity['message'] = sprintf( '%s %s "%s" %s', $name, esc_html__( 'completed', 'memberpress-coachkit' ), $message->title, esc_html__( 'milestone', 'memberpress-coachkit' ) );
            }
          }
          break;
        case self::UNENROLLED_STR:
          // $args = Enrollment::get_program_started_message_data( $activity->evt_id );
          // $name    = empty( trim( $message->name ) ) ? $message->display_name : $message->name;
          $name = $args['name'];
          $program = $args['program'];
          $profile_url = $args['profile_url'];

          if ( is_admin() ) {
            $new_activity['message'] = sprintf( '<a href="%s">%s</a> %s "%s" %s', esc_url( $profile_url ), esc_html( $name ), esc_html__( 'unenrolled from', 'memberpress-coachkit' ), esc_html( $program ), esc_html__( 'program', 'memberpress-coachkit' ) );
          } else {
            $new_activity['message'] = sprintf( '%s %s %s "%s" %s', esc_html( $name ), esc_html__( 'unenrolled from', 'memberpress-coachkit' ), esc_html( $program ), esc_html__( 'program', 'memberpress-coachkit' ) );
          }
          break;
        default:
          break;
      }

      if ( isset( $new_activity['message'] ) && ! empty( $new_activity['message'] ) ) {
        $new_activity['event'] = $activity->event;
        $new_activity['date']  = human_time_diff(
          strtotime( $activity->created_at ),
          strtotime( current_time( 'mysql' ) )
        ) . ' ' . esc_html__( 'ago', 'memberpress-coachkit' );
        $basket[]              = (object) $new_activity;
      }

      return $basket;
    }, []);
    return $activities;
  }

  /**
   * Get events for  CoachKit add-on
   *
   * @return Collection
   */
  public static function list_table(
    $order_by = '',
    $order = '',
    $paged = '',
    $search = '',
    $search_field = 'any',
    $perpage = 10,
    $params = null,
    $include_fields = false
  ) {

    global $wpdb;
    $db      = Db::fetch();
    $mepr_db = \MeprDb::fetch();

    if ( is_null( $params ) ) {
      $params = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
    }

    if ( empty( $order_by ) ) {
      $order_by = 'created_at';
      $order    = 'DESC';
    }

    $cols = array(
      'id'                   => 'e.id',
      'student_name'         => "CONCAT(pm_first_name.meta_value, ' ', pm_last_name.meta_value)",
      'student_display_name' => 'u.display_name',
      'student_id'           => 'e.student_id',
      'group_id'             => 'e.group_id',
      'coach_id'             => 'g.coach_id',
      'started'              => 'e.start_date',
      'program'              => 'post.post_title',
      'program_id'           => 'post.ID',
      'created_at'           => 'e.created_at',
    );

    $args   = array();
    $args[] = "((pm_first_name.meta_key = 'first_name' AND pm_first_name.meta_value IS NOT NULL) OR (pm_last_name.meta_key = 'last_name' AND pm_last_name.meta_value IS NOT NULL))";

    if ( isset( $_GET['coach'] ) && 'all' != $_GET['coach'] && is_numeric( $_GET['coach'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      $coach_id = sanitize_text_field( wp_unslash( $_GET['coach'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      $args[]   = $wpdb->prepare( 'g.coach_id = %d', $coach_id );
    }

    if ( isset( $_GET['program'] ) && 'all' != $_GET['program'] && is_numeric( $_GET['program'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      $program_id = sanitize_text_field( wp_unslash( $_GET['program'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      $args[]     = $wpdb->prepare( 'post.ID = %d', $program_id );
    }

    $joins = array(
      "LEFT JOIN {$wpdb->users} AS u ON u.ID = e.student_id",
      "LEFT JOIN {$wpdb->usermeta} AS pm_first_name ON pm_first_name.user_id = e.student_id AND pm_first_name.meta_key='first_name'",
      "LEFT JOIN {$wpdb->usermeta} AS pm_last_name ON pm_last_name.user_id = e.student_id AND pm_last_name.meta_key='last_name'",
      "LEFT JOIN {$db->groups} AS g ON g.id = e.group_id",
      "LEFT JOIN {$wpdb->posts} AS post ON e.program_id = post.ID",
    );

    return Db::list_table( $cols, "{$db->enrollments} AS e", $joins, $args, $order_by, $order, $paged, $search, $perpage );
  }

}
