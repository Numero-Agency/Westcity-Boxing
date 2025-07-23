<?php
namespace memberpress\coachkit\helpers;

use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\models\Room;
use memberpress\coachkit\lib\Activity;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Group;
use memberpress\downloads\models\File;
use memberpress\coachkit\lib\Collection;
use memberpress\coachkit\models\Message;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\helpers\ProgramHelper;
use memberpress\coachkit\models\StudentProgress;
use memberpress\coachkit\models\RoomParticipants;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/**
 * App Helper
 */
class AppHelper {

  /**
   * Get dashboard icon data image
   *
   * @return string
   */
  public static function coaching_icon() {

    ob_start();
    require base\IMAGES_PATH . '/coaching-icon.svg';
    $icon = ob_get_clean();

    return 'data:image/svg+xml;base64,' . base64_encode( $icon ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
  }

  public static function get_default_coaching_options() {
    return (object) array(
      'coaching_slug'    => '',
      'enable_messaging' => true,
      'label_for_client' => esc_html__( 'Client', 'memberpress-coachkit' ),
      'label_for_clients' => esc_html__( 'Clients', 'memberpress-coachkit' ),
      'label_for_coach' => esc_html__( 'Coach', 'memberpress-coachkit' ),
      'label_for_coaches' => esc_html__( 'Coaches', 'memberpress-coachkit' ),
    );
  }

  public  static function get_label_for_coach($plural = false){
    $options = self::get_options();
    return $plural ? $options->label_for_coaches : $options->label_for_coach;
  }

  public  static function get_label_for_client($plural = false){
    $options = self::get_options();
    return $plural ? $options->label_for_clients : $options->label_for_client;
  }

  public  static function get_options(){
    $default_options = (array) AppHelper::get_default_coaching_options();
    $options = (array) get_option( 'mpch-options' );
    $options = (object) array_merge($default_options, $options);

    return $options;
  }

  /**
   * Formats date
   *
   * @param [type]      $datetime The date string
   * @param mixed       $default Default value
   * @param null|string $format PHP date format
   * @return string
   */
  public static function format_date( $datetime, $default = null, $format = null ) {
    if ( is_null( $default ) ) { $default = __( 'Unknown', 'memberpress-coachkit' ); }
    if ( is_null( $format ) ) { $format = get_option( 'date_format' ); } //Gets WP date format option
    if ( empty( $datetime ) || preg_match( '#^0000-00-00#', $datetime ) ) { return $default; }

    $ts     = strtotime( $datetime );
    $offset = get_option( 'gmt_offset' ); // Gets WP timezone offset option

    return date_i18n( $format, ( $ts + Utils::hours( $offset ) ), false ); // return a translatable date in the WP locale options
  }


  /**
   * Determine if we are on coaching page or subpage
   *
   * @return boolean
   */
  public static function is_coaching_page() {
    $coaching_query_vars = array(
      'coaching',
      'coaching_messages',
      'coaching_message',
      'coaching_enrollments',
      'coaching_enrollment',
      'coaching_notifications',
      'coaching_students',
      'coaching_student',
    );

    $mepr_options = \MeprOptions::fetch();
    $coaching_page_id = $mepr_options->coaching_page_id;

    if(isset($_GET['page_id']) && $coaching_page_id == $_GET['page_id']){
      return true;
    }

    foreach ( $coaching_query_vars as $query_var ) {
      $value = get_query_var( $query_var );
      if ( $value || '0' === $value ) {
        return true;
      }
    }

    return false;
  }

  /**
   * Prevent a user from having both Coach and Student roles
   *
   * @param int $user_id
   * @return void
   */
  public static function prevent_dual_roles($user_id){
    $user = new \WP_User($user_id);
    if (in_array(Coach::ROLE, $user->roles) && in_array(Student::ROLE, $user->roles)) {
      $user->remove_role(Student::ROLE);
    }
  }

  /**
   * Determine if we are on coaching page or subpage
   *
   * @return boolean
   */
  public static function is_readylaunch() {
    $mepr_options = \MeprOptions::fetch();
    return $mepr_options->rl_enable_coaching_template;
  }

  /**
   * Get coaching slug
   *
   * @return string
   */
  public static function coaching_slug() {
    $mepr_options     = \MeprOptions::fetch();
    $coaching_page_id = $mepr_options->coaching_page_id;
    $slug             = $coaching_page_id ? get_post_field( 'post_name', $coaching_page_id ) : ''; // Get the slug of the selected base page
    return $slug;
  }

  /**
   * Get full name of the current user
   *
   * @return string
   */
  public static function get_fullname( $user = null ) {
    $name = '';
    $user = is_null( $user ) ? wp_get_current_user() : $user;

    if ( ! empty( $user->first_name ) ) {
      $name = $user->first_name;
    }

    if ( ! empty( $user->last_name ) ) {
      if ( empty( $name ) ) {
        $name = $user->last_name;
      } else {
        $name .= " {$user->last_name}";
      }
    }

    if ( empty( $name ) ) {
      $name = $user->display_name;
    }

    return $name;
  }

  /**
   * Format a due time string
   *
   * @param int    $number Number of units
   * @param string $unit   Unit of time (e.g., day, week)
   * @return string
   */
  public static function format_due_time( $number, $unit ) {
    $formatted_number = $number . ' ' . ucfirst( $unit );
    if ( $number > 1 ) {
      $formatted_number .= 's';
    }
    return $formatted_number;
  }

  /**
   * Create a collection from an array
   *
   * @param array $items Array of items
   * @return Collection
   */
  public static function collect( $items ) {
    return new Collection( $items );
  }

  /**
   * Get content related to students
   *
   * @param int $group_id Group ID
   * @return array
   */
  public static function get_students_content( $group_id = 0 ) {
    $user_id  = get_current_user_id();
    if( empty($group_id )){
      $group_id = isset( $_GET['group_id'] ) ? wp_unslash( absint( $_GET['group_id'] ) ) : '';
    }

    $params = array(
      'r_coach' => get_current_user_id(),
    );

    if ( $group_id > 0 ) {
      $params['r_group'] = $group_id;
    }

    $students = Student::list_table( '', '', '', '', 'any', 10, $params );
    $records  = Utils::collect( $students['results'] );

    // Get records
    $records = $records->map(function ( $rec ) {
      $active_programs = Utils::collect( Student::get_active_programs( $rec->ID ) );

      // Check if student is off track in any of the programs
      $off_track = $active_programs->any(function ( $program ) use ( $rec ) {
        return false === StudentProgress::is_student_on_track_for_program( $rec->ID, $program->program_id );
      });

      return (object) array(
        'id'          => $rec->ID,
        'name'        => empty( trim( $rec->name ) ) ? $rec->display_name : $rec->name,
        'email'       => $rec->email,
        'programs'    => $active_programs->count(),
        'last_active' => AppHelper::format_date( $rec->last_login_date, __( 'Never', 'memberpress-coachkit' ) ),
        'created_at'  => AppHelper::format_date( $rec->registered, __( 'Never', 'memberpress-coachkit' ) ),
        'public_url'  => AppHelper::get_student_public_url( $rec->ID ),
        'off_track'   => $off_track,
      );
    })->all();

    return [
      'records' => $records,
      'count'   => $students['count'],
    ];
  }

  /**
   * Get content related to messages
   *
   * @param int $user_id User ID (optional, defaults to current user)
   * @return array
   */
  public static function get_messages_content( $active_room = '' ) {
    $user_id  = get_current_user_id();
    $contacts = Utils::collect( Message::get_recipient_list( $user_id ) );
    $contacts = $contacts->map(function ( $contact ) {
      if( in_array($contact->type, array('student', 'coach')) ){
        $name                = trim( $contact->first_name . ' ' . $contact->last_name );
        $contact->name       = empty( $name ) ? $contact->display_name : $name;
        $contact->avatar_url = get_avatar_url( $contact->id );
        $contact->type       = ucfirst( $contact->type );
      } else {

      }
      return $contact;
    });

    $rooms               = Room::get_rooms_for_user( $user_id );
    $rooms_with_messages = Room::get_rooms_with_messages( $rooms );

    // This is useful when student already has messages but is now disenrolled from all programs.
    // Student will no longer have contacts, and we want to hide messages.
    $hide_messages = $contacts->empty() && AppHelper::user_has_role( Student::ROLE );

    return [
      'contacts'    => $contacts->all(),
      'rooms'       => $rooms_with_messages,
      'active_room' => $active_room,
      'hideMessages' => $hide_messages,
    ];
  }

  public static function get_enrollments_content() {
    $current_user    = wp_get_current_user();
    $current_user_id = $current_user->ID;
    $student         = new Student( $current_user_id );
    return $student->get_enrollment_data( [ 'milestones', 'public_url' ] )->all();
  }

  public static function get_enrollment_content( $e_id ) {
    $enrollment      = new Enrollment( $e_id );
    $enrollment_data = $enrollment->get_values();
    $program         = new Program( $enrollment_data['program_id'] );
    $group           = new Group( $enrollment_data['group_id'] );

    $enrollment = (object) [
      'id'         => $enrollment->id,
      'title'      => $program->post_title,
      'coach'      => $group->coach()->fullname,
      'milestones' => StudentProgress::get_milestones_with_status( $enrollment->id, $enrollment->program_id )
        ->map(function ( $m ) use ( $program, $enrollment ) {
          // $m->due       = $program->total_due_length_for_milestone( $m );
          $m->due       = $program->due_date_for_milestone( $enrollment, $m );
          $downloads    = array_filter( explode( ',', $m->downloads ) );
          $m->downloads = ProgramHelper::get_file_downloads_data($downloads);
          $courses    = array_filter( explode( ',', $m->courses ) );
          $m->courses = [];
          foreach ( $courses as $course_id ) {
            $post    = class_exists( '\memberpress\courses\models\Course' ) ? new \memberpress\courses\models\Course( $course_id ) : get_post( $course_id );
            $user_id = get_current_user_id();

            $m->courses[] = (object) [
              'ID'        => $post->ID,
              'title'     => $post->post_title,
              'permalink' => $post->permalink,
              'progress'  => class_exists( '\memberpress\courses\models\Course' ) ? $post->user_progress( $user_id ) : '',
            ];
          }

          return $m;
        })->all(),
      'habits'     => StudentProgress::get_habits_with_user_progress( $enrollment->id, $enrollment->program_id )
        ->map(function ( $h ) use ( $enrollment ) {
          $h->due_dates       = StudentProgress::get_due_dates_for_habit( $h, $enrollment );
          $h->completed_dates = StudentProgress::get_completed_habit_dates( $h, $enrollment, $h->due_dates );

          $downloads    = array_filter( explode( ',', $h->downloads ) );
          $h->downloads = ProgramHelper::get_file_downloads_data($downloads);

          return $h;
        })->all(),
      'group'      => (object) array(
        'start_date'       => 'fixed' === $group->type ? self::format_date( $group->start_date ) : 'dynamic',
        'starts_in_future' => 'fixed' === $group->type && strtotime( $group->start_date ) > strtotime( wp_date( 'Y-m-d' ) ) ? true : false,
        'appointment_url' => $group->appointment_url
      ),
    ];
    return $enrollment;
  }

  public static function get_student_content( $student_id ) {
    $student      = new Student( $student_id );
    $student_data = (object) array(
      'name'              => $student->fullname,
      'enrollments'       => $student->get_enrollment_data( array(), get_current_user_id() )->all(),
      'memberships'       => $student->get_active_membership_data()->all(),
      'notes'             => $student->get_notes_data()->map(function ( $note ) {
        return StudentHelper::transform_note( $note );
      })->all(),
      'recent_activities' => Activity::get_recent_activities( 1, array(), $student->ID ),
    );

    return $student_data;
  }

  public static function get_coaching_url() {
    $mepr_options      = \MeprOptions::fetch();
    $coaching_page_id  = $mepr_options->coaching_page_id;
    return untrailingslashit( get_permalink( $coaching_page_id ) );
  }

  public static function get_enrollment_page_url() {
    $coaching_page_url = self::get_coaching_url();

    // Check if permalinks are plain
    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( [ 'action' => 'enrollments' ], $coaching_page_url );
    }

    return trailingslashit( $coaching_page_url ) . 'enrollments';
  }

  public static function get_single_enrollment_url( $enrollment_id ) {
    $enrollments_url = self::get_enrollment_page_url();
    $coaching_page_url = self::get_coaching_url();

    // Check if permalinks are plain
    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( [ 'action' => 'enrollment', 'id' => $enrollment_id ], $coaching_page_url );
    }

    // For non-plain permalinks, return URL with friendly structure
    return trailingslashit( $enrollments_url ) . $enrollment_id;
  }

  public static function get_student_group_url( $group_id ) {
    $slug = self::coaching_slug();
    $label_for_clients = strtolower(AppHelper::get_label_for_client(true));

    $url  = home_url( $slug . '/'.$label_for_clients.'/?group_id=' . $group_id );

    return $url;
  }

  public static function get_messages_page_url() {
    $coaching_page_url = self::get_coaching_url();

    // Check if permalinks are plain
    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( [ 'action' => 'messages' ], $coaching_page_url );
    }

    return trailingslashit( $coaching_page_url ) . 'messages';
  }

  public static function get_room_url( $uuid ) {
    return self::get_messages_page_url() . '/' . $uuid;
  }

  public static function get_notifications_page_url() {
    $coaching_page_url = self::get_coaching_url();

    // Check if permalinks are plain
    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( [ 'action' => 'notifications' ], $coaching_page_url );
    }

    return trailingslashit( $coaching_page_url ) . 'notifications';
  }

  public static function get_students_url() {
    $coaching_page_url = self::get_coaching_url();
    $label_for_clients = strtolower(AppHelper::get_label_for_client(true));

    // Check if permalinks are plain
    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( [ 'action' => 'students' ], $coaching_page_url );
    }

    return trailingslashit( $coaching_page_url ) . $label_for_clients;
  }

  public static function get_student_public_url( $student_id ) {
    $students_url = self::get_students_url();
    $coaching_page_url = self::get_coaching_url();

    // Check if permalinks are plain
    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( [ 'action' => 'student', 'id' => $student_id ], $coaching_page_url );
    }

    return trailingslashit( $students_url ) . $student_id;
  }

  public static function get_query_var_with_fallback( $var, $action = '' ) {
    $mepr_options = \MeprOptions::fetch();
    $coaching_page_id = $mepr_options->coaching_page_id;
    $value = get_query_var( $var );
    $action = strtolower( str_replace( 'coaching_', '', $var ) );
    $page_id = isset( $_GET['page_id'] ) ? wp_unslash( absint( $_GET['page_id'] ) ) : '';
    $get_action = isset( $_GET['action'] ) ? strtolower( wp_unslash( $_GET['action'] ) ) : '';
    $id_set = isset( $_GET['id'] );

    if ( ! empty( $value ) ) {
      return $value;
    }

    if ( empty( $page_id ) ) {
      return false;
    }

    if ( $var === 'coaching' && ! isset( $_GET['action'] ) && $page_id === $coaching_page_id ) {
      return true;
    }

    if ( $get_action === $action ){
      return $id_set ? absint( wp_unslash($_GET['id']) ) : true;
    }

    return false;
  }

  public static function message_mime_type() {
    return apply_filters('mpch_message_mime_type', [
      'mp4'     => 'video/mp4',  // mp4 video
      'mov'     => 'video/quicktime', // MOV video
      'jpg'     => 'image/jpeg', // jpg image
      'jpeg'    => 'image/jpeg', // jpeg image
      'png'     => 'image/png',  // png image
      'gif'     => 'image/gif',  // gif image
      'doc'     => 'application/msword',  // doc document
      'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // docx document
      'rtf'     => 'application/rtf',  // rtf document
      'txt'     => 'text/plain',  // txt document
      'odt'     => 'application/vnd.oasis.opendocument.text',  // odt document
      'xls'     => 'application/vnd.ms-excel',  // xls spreadsheet
      'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',  // xlsx spreadsheet
      'csv'     => 'text/csv',  // csv spreadsheet
      'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',  // ods spreadsheet
      'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',  // ots spreadsheet
      'pdf'     => 'application/pdf',  // pdf document
      'zip'     => 'application/zip',  // zip file
      'ppt'     => 'application/vnd.ms-powerpoint',  // ppt presentation
      'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',  // pptx presentation
      'key'     => 'application/vnd.apple.keynote',  // Apple Keynote document
      'pages'   => 'application/vnd.apple.pages',  // Apple Pages document
      'numbers' => 'application/vnd.apple.numbers',
    ]);
  }

  public static function get_completion_date( $enrollment, $program ) {
    $group = $enrollment->group();
    if ( 'fixed' === $group->type ) {
      return $group->end_date;
    }

    return $program->completion_date_by_milestones( $enrollment->start_date );
  }

  public static function get_enrollment_email_params( $program, $product = null, $enrollment = null ) {

    if ( $enrollment ) {
      $student = new Student( $enrollment->student_id );
    }

    return array(
      'program_name'          => $program->post_title,
      'membership_name'       => $product ? $product->post_title : '',
      'remaining_enrollments' => $program->count_remaining_enrollments(),
      'edit_program_url'      => Utils::get_edit_post_link( $program->ID ),
      'enrollee'              => $enrollment ? $student->fullname : '',
    );
  }

  public static function get_files() {
    global $wpdb;

    $q = $wpdb->prepare("
        SELECT ID
          FROM {$wpdb->posts}
         WHERE post_type=%s
           AND post_status=%s
        ORDER BY post_title ASC
      ",
      File::$cpt,
      'publish'
    );

    $ids = $wpdb->get_col($q);

    $file_downloads = array();
    foreach($ids as $id) {
      $file_downloads[] = new File($id);
    }

    return $file_downloads;
  }

  public static function get_enrollment_email_vars() {
    return array(
      'program_name',
      'membership_name',
      'remaining_enrollments',
      'edit_program_url',
    );
  }

  public static function get_checkin_email_params( $checkin, $resource, $program, $enrollment ) {
    $params = array(
      'title'                => $resource->title,
      'checkin_question'     => $checkin->question,
      'program_title'        => $program->post_title,
      'checkin_complete_url' => self::get_single_enrollment_url( $enrollment->id ),
    );

    return $params;
  }

  public static function get_checkin_email_vars() {
    return array(
      'title',
      'checkin_question',
      'program_title',
      'checkin_complete_url',
    );
  }

  public static function user_has_role( $target_role, $current_user = null ) {
    if ( ! $current_user ) {
      $current_user = wp_get_current_user();
    }

    if ( in_array( $target_role, $current_user->roles ) ) {
      return true;
    }

    return false;
  }

  public static function get_contrast_color( $hex_color ) {
    $hex_color     = trim( $hex_color );
    $tmp_hex_color = trim( $hex_color, '#' );
    if ( ! ctype_xdigit( $tmp_hex_color ) ) { // Validate HEX code.
      $hex_color = '#FFFFFF'; // Fallback to white color.
    }

    // hex_color RGB
    $r1 = hexdec( substr( $hex_color, 1, 2 ) );
    $g1 = hexdec( substr( $hex_color, 3, 2 ) );
    $b1 = hexdec( substr( $hex_color, 5, 2 ) );

    // Black RGB
    $black_color    = '#000000';
    $r2_black_color = hexdec( substr( $black_color, 1, 2 ) );
    $g2_black_color = hexdec( substr( $black_color, 3, 2 ) );
    $b2_black_color = hexdec( substr( $black_color, 5, 2 ) );

    // Calc contrast ratio
    $l1 = 0.2126 * pow( $r1 / 255, 2.2 ) +
    0.7152 * pow( $g1 / 255, 2.2 ) +
    0.0722 * pow( $b1 / 255, 2.2 );

    $l2 = 0.2126 * pow( $r2_black_color / 255, 2.2 ) +
    0.7152 * pow( $g2_black_color / 255, 2.2 ) +
    0.0722 * pow( $b2_black_color / 255, 2.2 );

    $contrast_ratio = 0;
    if ( $l1 > $l2 ) {
      $contrast_ratio = (int) ( ( $l1 + 0.05 ) / ( $l2 + 0.05 ) );
    } else {
      $contrast_ratio = (int) ( ( $l2 + 0.05 ) / ( $l1 + 0.05 ) );
    }

    // If contrast is more than 5, return black color
    if ( $contrast_ratio > 5 ) {
      return '#000000';
    } else {
      // if not, return white color.
      return '#FFFFFF';
    }
  }

  public static function hex_to_rgb( $hex, $alpha = false ) {
    $hex      = str_replace( '#', '', $hex );
    $length   = strlen( $hex );
    $rgb['r'] = hexdec( $length == 6 ? substr( $hex, 0, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 0, 1 ), 2 ) : 0 ) );
    $rgb['g'] = hexdec( $length == 6 ? substr( $hex, 2, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 1, 1 ), 2 ) : 0 ) );
    $rgb['b'] = hexdec( $length == 6 ? substr( $hex, 4, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 2, 1 ), 2 ) : 0 ) );
    if ( $alpha ) {
      $rgb['a'] = $alpha;
    }
    return implode( ',', $rgb );
  }

}
// if it's a group, run three events
//
