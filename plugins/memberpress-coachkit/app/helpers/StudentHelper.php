<?php
namespace memberpress\coachkit\helpers;

use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Student;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * Coach Helper
 */
class StudentHelper {


  public static function get_profile_url( $student_id ) {
    $url = add_query_arg(
      array(
        'page'   => 'memberpress-coachkit-students',
        'action' => 'edit',
        'id'     => $student_id,
      ),
      admin_url( 'admin.php' )
    );

    return $url;
  }

  public static function get_note_row_html( $note ) {
    $author               = new \MeprUser( $note->coach_id );
    $author_name          = $author->get_full_name();
    $author_profile_url   = get_edit_profile_url( $note->coach_id );
    $author_thumbnail_url = get_avatar_url( $note->coach_id );

    $time = Utils::format_date_readable( $note->created_at );
    return View::get_string( '/admin/notes/row', get_defined_vars() );
  }


  public static function transform_note( $note ) {
    $author = new \MeprUser( $note->coach_id );

    return (object) array(
      'note'          => $note->note,
      'id'            => $note->id,
      'author_id'     => $note->coach_id,
      'name'          => $author->get_full_name(),
      'profile_url'   => get_edit_profile_url( $note->coach_id ),
      'thumbnail_url' => get_avatar_url( $note->coach_id ),
      'time'          => Utils::format_date_readable( $note->created_at ),
    );
  }
}
