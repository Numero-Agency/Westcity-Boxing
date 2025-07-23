<?php

namespace memberpress\coachkit\controllers;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use memberpress\coachkit\lib;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib\Activity;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\models\Habit;
use memberpress\coachkit\models\CheckIn;
use memberpress\coachkit\models\Milestone;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\emails\ProgramHabitEmail;
use memberpress\coachkit\emails\ProgramMilestoneEmail;

/**
 * Controller for Program
 */
class CheckInCtrl extends lib\BaseCtrl {

  /**
   * Load class hooks here
   *
   * @return void
   */
  public function load_hooks() {
    add_action( 'mpch_after_save_milestone_post_request', array( $this, 'save_checkins' ), 10, 2 );
    add_action( 'mpch_after_save_habit_post_request', array( $this, 'save_checkins' ), 10, 2 );
    add_action( 'mpch_checkins_worker', array( $this, 'worker' ) );
    add_filter( 'cron_schedules', array( $this, 'intervals' ) );
    add_action( 'mpch_enrollment_created', array( $this, 'schedule_checkins' ) );

    $c = new CheckIn();
    foreach ( $c->event_actions as $e ) {
      add_action( $e, array( $this, 'send_checkin_emails' ) );
    }
  }

  public function schedule_checkins( $enrollment ) {
    // TODO: Get only checkins related to this enrollment
    $checkins = CheckIn::get_all();

    if ( ! empty( $checkins ) ) {
      foreach ( $checkins as $checkin ) {
        $checkin = new Checkin( $checkin->id );
        if ( ! empty( $checkin->id ) ) {
          $this->schedule_checkin( $checkin->id );
        } else {
          $this->unschedule_checkin( $checkin->id );
        }
      }
    }
  }

  public function schedule_checkin( int $checkin_id ) {
    // Stop zombie cron jobs in their tracks here
    $checkin = $this->get_valid_checkin( $checkin_id );

    if ( false === $checkin ) {
      $this->unschedule_checkin( $checkin_id );
      return;
    }

    $args = array( $checkin_id );

    if ( ! wp_next_scheduled( 'mpch_checkins_worker', $args ) ) {
      wp_schedule_event(
        time(),
        'mpch_checkins_worker_interval',
        'mpch_checkins_worker',
        $args
      );
    }
  }

  public function unschedule_checkin( $id ) {
    $args      = array( $id );
    $timestamp = wp_next_scheduled( 'mpch_checkins_worker', $args );
    wp_unschedule_event( $timestamp, 'mpch_checkins_worker', $args );
  }


  public function worker( $checkin_id ) {
    $checkin = $this->get_valid_checkin( $checkin_id );

    if ( false !== $checkin ) {
      @set_time_limit( 0 ); // unlimited run time
      $run_limit = Utils::minutes( 0.1 ); // limit to 10 minutes

      if ( isset( $checkin->milestone_id ) && ! empty( $checkin->milestone_id ) ) {
        $milestone = new Milestone( $checkin->milestone_id );

        // Event name will be the same no matter what we're doing here
        $event = $milestone->timing;
        $args  = [
          'type'         => 'milestone',
          'milestone_id' => $checkin->milestone_id,
        ];

        while ( $this->run_time() < $run_limit ) {
          $obj = null;
          switch ( $milestone->timing ) {
            case 'after_program_starts':
              $enrollment_id = $checkin->get_enrollment_for_due_milestone_checkin( $milestone );
              if ( $enrollment_id ) {
                $args['checkin_id'] = $checkin->id;
                $obj                = new Enrollment( $enrollment_id ); // we need the actual model
              }
              break;
            case 'after_previous_milestone':
              $enrollment_id = $checkin->get_enrollment_for_milestone_checkin_after_previous_due( $milestone );
              if ( $enrollment_id ) {
                $args['checkin_id'] = $checkin->id;
                $obj                = new Enrollment( $enrollment_id ); // we need the actual model
              }
              break;
            default:
              $this->unschedule_checkin( $checkin_id );
              break;
          }
          if ( isset( $obj ) ) {
            // We just catch the hooks from these events
            Activity::record( $event, $obj, $args, 'mpch-milestone-checkin' );
          } else {
            break; // break out of the while loop
          }
        } //End while
      } elseif ( isset( $checkin->habit_id ) && ! empty( $checkin->habit_id ) ) {
        $habit = new Habit( $checkin->habit_id );

        // Event name will be the same no matter what we're doing here
        $event = $habit->timing;
        $args  = [
          'type'     => 'habit',
          'habit_id' => $checkin->habit_id,
        ];

        while ( $this->run_time() < $run_limit ) {
          $obj = null;
          switch ( $habit->timing ) {
            case 'after_program_starts':
              $enrollment_id = $checkin->get_enrollment_for_due_habit_checkin( $habit );
              if ( $enrollment_id ) {
                $args['checkin_id'] = $checkin->id;
                $obj                = new Enrollment( $enrollment_id ); // we need the actual model
              }
              break;
            default:
              $this->unschedule_checkin( $checkin_id );
              break;
          }
          if ( isset( $obj ) ) {
            // We just catch the hooks from these events
            Activity::record( $event, $obj, $args, 'mpch-habit-checkin' );
          } else {
            break; // break out of the while loop
          }
        } //End while
      }
    }
  }

  public function get_valid_checkin( $id ) {
    // if the remider_id is empty then forget it
    if ( empty( $id ) ) { return false; }

    $checkin = new Checkin( $id );

    // ID is empty? fail
    if ( empty( $checkin->id ) ) { return false; }
    return $checkin;
  }

  private function run_time() {
    static $start_time;

    if ( ! isset( $start_time ) ) {
      $start_time = time();
    }

    return ( time() - $start_time );
  }

  /** CRON SPECIFIC METHODS **/
  public function intervals( $schedules ) {
    $schedules['mpch_checkins_worker_interval'] = array(
      'interval' => Utils::minutes( 45 ),
      'display'  => __( 'MemberPress Checkins Worker', 'memberpress-coachkit' ),
    );

    return $schedules;
  }

  /**
   * Save checkin
   *
   * @param Milestone|Habit $resource Milestone or Habit object
   * @param array           $data sanitized post data
   * @return void
   */
  public function save_checkins( $resource, array $data ) {
    if ( ! isset( $data['checkins'] ) || empty( $data['checkins'] ) ) {
      return;
    }

    if ( $resource instanceof Milestone ) {
      $checkin = Checkin::get_one( [ 'milestone_id' => $resource->id ] );
    } elseif ( $resource instanceof Habit ) {
      $checkin = Checkin::get_one( [ 'habit_id' => $resource->id ] );
    }

    // Make sure the milestone or habit does not already have a checkin
    if ( ! isset( $checkin->id ) || empty( $checkin->id ) ) {
      $checkin = new CheckIn();
    }

    $checkin->question = $data['checkins'][ CheckIn::QUESTION_STR ];
    $checkin->channel  = isset( $data['checkins'][ CheckIn::CHANNEL_STR ] ) ? $data['checkins'][ CheckIn::CHANNEL_STR ] : 'email';

    if ( $resource instanceof Milestone ) {
      $checkin->milestone_id = $resource->id;
    } elseif ( $resource instanceof Habit ) {
      $checkin->habit_id = $resource->id;
    }

    try {
      $checkin->store();
    } catch ( \Throwable $th ) {
      return new \WP_Error( get_class( $th ), $th->getMessage() );
    }
  }

  /**
   * Send checkin emails
   *
   * @param object $event
   * @return void
   */
  public function send_checkin_emails( $event ) {
    $disable_email = false; // Do not send the emails if this gets set to true
    $args          = json_decode( $event->args, true );
    $options       = get_option( 'mpch-options', AppHelper::get_default_coaching_options() );
    $mepr_options  = \MeprOptions::fetch();

    if ( ! isset( $args['checkin_id'] ) ) { return; }

    $checkin    = $this->get_valid_checkin( $args['checkin_id'] );
    $enrollment = new Enrollment( $event->evt_id );

    if ( false === $checkin || empty( $enrollment->id ) ) { return; } // fail silently if checkin is invalid
    if ( false === $enrollment->can_students_track_progress() ) { return; }

    $usr     = new \MeprUser( $enrollment->student_id );
    $group   = new Group( $enrollment->group_id );
    $program = $group->program();

    if ( isset( $checkin->milestone_id ) && ! empty( $checkin->milestone_id ) ) {
      if ( ! isset($mepr_options->emails[ ProgramMilestoneEmail::class ]) || ! $mepr_options->emails[ ProgramMilestoneEmail::class ]['enabled'] ) {
        return;
      }
      if ( 'mpch-milestone-checkin' !== $event->evt_id_type ) { return; }
      $milestone = new Milestone( $checkin->milestone_id );
      $title     = $milestone->title;
      $params    = array_merge( AppHelper::get_checkin_email_params( $checkin, $milestone, $program, $enrollment ), \MeprUsersHelper::get_email_params( $usr ) );

      switch ( $milestone->timing ) {
        case 'after_program_starts':
        case 'after_previous_milestone':
          $uclass = ProgramMilestoneEmail::class;
          $aclass = '';
          break;
        default:
          $uclass = $aclass = '';
      }
      $disable_email = apply_filters( "mpch_{$milestone->time}_checkin_disable", $disable_email, $checkin, $event );
    } elseif ( isset( $checkin->habit_id ) && ! empty( $checkin->habit_id ) ) {
      if ( ! isset($mepr_options->emails[ ProgramHabitEmail::class ]) || ! $mepr_options->emails[ ProgramHabitEmail::class ]['enabled'] ) {
        return;
      }
      if ( 'mpch-habit-checkin' !== $event->evt_id_type ) { return; }
      $habit  = new Habit( $checkin->habit_id );
      $title  = $habit->title;
      $params = array_merge( AppHelper::get_checkin_email_params( $checkin, $habit, $program, $enrollment ), \MeprUsersHelper::get_email_params( $usr ) );

      switch ( $habit->timing ) {
        case 'after_program_starts':
          $uclass = ProgramHabitEmail::class;
          $aclass = '';
          break;
        default:
          $uclass = $aclass = '';
      }
      $disable_email = apply_filters( "mpch_{$habit->time}_checkin_disable", $disable_email, $checkin, $event );
    }

    if ( ! $disable_email ) {
      Utils::send_notices( $enrollment, $uclass, $aclass, $params );
    }
  }

}
