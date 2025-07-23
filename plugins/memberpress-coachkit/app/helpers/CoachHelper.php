<?php
namespace memberpress\coachkit\helpers;

use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\models\Habit;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\models\Milestone;
use memberpress\coachkit\models\StudentProgress;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * Coach Helper
 */
class CoachHelper {


  public static function get_profile_url( $coach_id ) {
    $url = add_query_arg(
      array(
        'page'   => 'memberpress-coachkit-coaches',
        'action' => 'edit',
        'id'     => $coach_id,
      ),
      admin_url( 'admin.php' )
    );

    return $url;
  }

  public static function render_edit_form( Coach $coach ) {
    $groups = AppHelper::collect( $coach->get_groups() );
    $groups = $groups->map(function ( $g ) use ( $coach ) {
      $g->created_at      = AppHelper::format_date( $g->created_at );
      $g->active_students = count( $coach->get_active_students() );
      return $g;
    })->all();

    $enrollments = AppHelper::collect( $coach->get_active_students() );
    $students    = $enrollments->map(function ( $e ) use ( $coach ) {
      $student = new Student( $e->student_id );

      return (object) [
        'name'        => $student->fullname,
        'url'         => StudentHelper::get_profile_url( $e->student_id ),
        'off_track'   => ! StudentProgress::is_student_on_track_for_program( $e->student_id, $e->program_id ),
        'progress'    => StudentProgress::progress_percentage( $e->program_id, $e->id ),
        'last_update' => Utils::format_date_readable( StudentProgress::get_last_updated( $e->id ) ),
      ];
    })->all();

    $students_count = count( $students );
    $students       = array_slice( $students, 0, 5 );
    $more_button    = $students_count > 5 ? true : false;

    // data for add coach to program
    $programs = ProgramHelper::get_mapped_program_with_groups();

    $program_list  = View::get_string( '/admin/coaches/program-list', array( 'programs' => $programs->items ) );
    $program_count = sprintf( esc_html__( 'Showing %1$d-%2$d of %3$d programs', 'memberpress-coachkit' ), 1, count( $programs->items ), $programs->total );
    $edit_user_url = get_edit_user_link( $coach->ID );

    $label_for_clients = AppHelper::get_label_for_client(true);
    $label_for_client = AppHelper::get_label_for_client();
    $label_for_coach = AppHelper::get_label_for_coach();

    View::render( '/admin/coaches/assign-coach-form', get_defined_vars() );
    View::render( '/admin/coaches/edit-coach', get_defined_vars() );
  }
}
