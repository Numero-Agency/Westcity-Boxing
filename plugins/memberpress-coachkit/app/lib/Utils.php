<?php
namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

use memberpress\coachkit as base;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\emails\EnrollmentCapEmail;
use memberpress\coachkit\emails\MessageReceivedEmail;
use memberpress\coachkit\emails\ProgramStartedEmail;
use memberpress\coachkit\emails\ProgramCompletedEmail;

class Utils {
  public static function get_user_id_by_email( $email ) {
    if ( isset( $email ) && ! empty( $email ) ) {
      global $wpdb;
      $query = "SELECT ID FROM {$wpdb->users} WHERE user_email=%s";
      $query = $wpdb->prepare( $query, esc_sql( $email ) );
      return (int) $wpdb->get_var( $query );
    }

    return '';
  }

  public static function is_image( $filename ) {
    if ( ! file_exists( $filename ) ) {
      return false;
    }

    $file_meta = getimagesize( $filename );

    $image_mimes = array( 'image/gif', 'image/jpeg', 'image/png' );

    return in_array( $file_meta['mime'], $image_mimes );
  }

  public static function is_curl_enabled() {
    return function_exists( 'curl_version' );
  }

  public static function is_post_request() {
    return ( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' );
  }

  public static function base36_encode( $base10 ) {
    return base_convert( $base10, 10, 36 );
  }

  public static function base36_decode( $base36 ) {
    return base_convert( $base36, 36, 10 );
  }

  public static function is_date( $str ) {
    if ( ! is_string( $str ) ) {
      return false; }
    $d = strtotime( $str );
    return ( $d !== false );
  }

  public static function is_ip( $ip ) {
    // return preg_match('#^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$#',$ip);
    return ( (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) || (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) );
  }

  public static function is_url( $str ) {
    return preg_match( '/https?:\/\/[\w-]+(\.[\w-]{2,})*(:\d{1,5})?/', $str );
  }

  public static function is_email( $str ) {
    return preg_match( '/[\w\d._%+-]+@[\w\d.-]+\.[\w]{2,4}/', $str );
  }

  public static function is_phone( $str ) {
    return preg_match( '/\(?\d{3}\)?[- ]\d{3}-\d{4}/', $str );
  }

  public static function get_delim( $link ) {
    return ( ( preg_match( '#\?#', $link ) ) ? '&' : '?' );
  }

  public static function site_domain() {
    return preg_replace( '#^https?://(www\.)?([^\?\/]*)#', '$2', home_url() );
  }

  public static function is_logged_in_and_current_user( $user_id ) {
    $current_user = self::get_currentuserinfo();

    return ( self::is_user_logged_in() && ( $current_user->ID == $user_id ) );
  }

  public static function is_logged_in_and_an_admin() {
    return ( self::is_user_logged_in() && self::is_admin() );
  }

  public static function is_logged_in_and_a_subscriber() {
    return ( self::is_user_logged_in() && self::is_subscriber() );
  }

  public static function is_admin() {
    return current_user_can( 'administrator' );
  }

  public static function is_subscriber() {
    return ( current_user_can( 'subscriber' ) && ! current_user_can( 'contributor' ) );
  }

  public static function array_to_string( $my_array, $debug = false, $level = 0 ) {
    return self::object_to_string( $my_array );
  }

  public static function object_to_string( $object ) {
    return print_r( $object, true );
  }

  public static function replace_text_variables( $text, $variables ) {
    $patterns     = array();
    $replacements = array();

    foreach ( $variables as $var_key => $var_val ) {
      $patterns[]     = '/\{\$' . preg_quote( $var_key, '/' ) . '\}/';
      $replacements[] = preg_replace( '/\$/', '\\\$', $var_val ); // $'s must be escaped for some reason
    }

    $preliminary_text = preg_replace( $patterns, $replacements, $text );

    // Clean up any failed matches
    return preg_replace( '/\{\$.*?\}/', '', $preliminary_text );
  }

  public static function with_default( $variable, $default ) {
    if ( isset( $variable ) ) {
      if ( is_numeric( $variable ) ) {
        return $variable;
      } elseif ( ! empty( $variable ) ) {
        return $variable;
      }
    }

    return $default;
  }

  public static function format_float( $number, $num_decimals = 2 ) {
    return number_format( (float) $number, $num_decimals, '.', '' );
  }

  public static function is_subdir_install() {
    return preg_match( '#^https?://[^/]+/.+$#', home_url() );
  }

  public static function random_string( $length = 10, $lowercase = true, $uppercase = false, $symbols = false ) {
    $characters  = '0123456789';
    $characters .= $uppercase ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '';
    $characters .= $lowercase ? 'abcdefghijklmnopqrstuvwxyz' : '';
    $characters .= $symbols ? '@#*^%$&!' : '';
    $string      = '';
    $max_index   = strlen( $characters ) - 1;

    for ( $p = 0; $p < $length; $p++ ) {
      $string .= $characters[ mt_rand( 0, $max_index ) ];
    }

    return $string;
  }

  /* Keys to indexes, indexes to keys ... do it! */
  public static function array_invert( $invertable ) {
    $inverted = array();

    foreach ( $invertable as $key => $orray ) {
      foreach ( $orray as $index => $value ) {
        if ( ! isset( $inverted[ $index ] ) ) {
          $inverted[ $index ] = array(); }
        $inverted[ $index ][ $key ] = $value;
      }
    }

    return $inverted;
  }

  public static function blogurl() {
    return ( ( get_option( 'home' ) ) ? get_option( 'home' ) : get_option( 'siteurl' ) );
  }

  public static function siteurl() {
    return get_option( 'siteurl' );
  }

  public static function blogname() {
    return get_option( 'blogname' );
  }

  public static function blogdescription() {
    return get_option( 'blogdescription' );
  }

  public static function db_date_to_ts( $mysql_date ) {
    return strtotime( $mysql_date );
  }

  public static function ts_to_mysql_date( $ts, $format = 'Y-m-d H:i:s' ) {
    return gmdate( $format, $ts );
  }

  public static function db_now( $format = 'Y-m-d H:i:s' ) {
    return self::ts_to_mysql_date( time(), $format );
  }

  public static function db_lifetime() {
    return '0000-00-00 00:00:00';
  }

  public static function format_date_readable( $datetime ) {
    if ( empty( $datetime ) ) {
      return '';
    }

    return human_time_diff(
      strtotime( $datetime ),
      time()
    ) . ' ' . esc_html__( 'ago', 'memberpress-coachkit' );
  }

  public static function date_is_in_future( $date_string ) {
    $date_to_check = strtotime( $date_string );
    $current_date  = time(); // Get the current timestamp

    return $date_to_check > $current_date;
  }

  public static function error_log( $error ) {
    if ( is_wp_error( $error ) ) {
      $error = $error->get_error_message();
    } elseif ( is_array( $error ) || is_object( $error ) ) {
      $error = wp_json_encode( $error );
    }

    error_log( sprintf( __( "*** MemberPress CoachKit Error\n==========\n%s\n==========\n", 'memberpress-coachkit' ), $error ) );  // phpcs:disable WordPress.PHP.DevelopmentFunctions
  }

  public static function filter_array_keys( $sarray, $keys ) {
    $rarray = array();
    foreach ( $sarray as $key => $value ) {
      if ( in_array( $key, $keys ) ) {
        $rarray[ $key ] = $value;
      }
    }
    return $rarray;
  }

  public static function get_property( $class_name, $property ) {
    if ( ! class_exists( $class_name ) ) {
      return null;
    }

    if ( ! property_exists( $class_name, $property ) ) {
      return null;
    }

    $vars = get_class_vars( $class_name );

    return $vars[ $property ];
  }

  public static function get_constant( $class_name, $constant ) {
    if ( ! class_exists( $class_name ) ) {
      return null;
    }

    if ( ! defined( $class_name . '::' . $constant ) ) {
      return null;
    }

    $r    = new \ReflectionClass( $class_name );
    $vars = $r->getConstants();

    return $vars[ $constant ];
  }

  public static function get_static_property( $class_name, $property ) {
    $r = new \ReflectionClass( $class_name );
    return $r->getStaticPropertyValue( $property );
  }

  public static function is_associative_array( $arr ) {
    return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
  }

  public static function protocol() {
    if ( is_ssl() ||
    ( defined( 'MEPR_SECURE_PROXY' ) && // USER must define this in wp-config.php if they're doing HTTPS between the proxy
      isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) &&
      strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) == 'https' ) ) {
      return 'https';
    } else {
      return 'http';
    }
  }

  public static function is_ssl() {
    return ( self::protocol() === 'https' );
  }

  // Special handling for protocol
  public static function get_permalink( $id = 0, $leavename = false ) {
    $permalink = get_permalink( $id, $leavename );

    if ( self::is_ssl() ) {
      $permalink = preg_replace( '!^https?://!', 'https://', $permalink );
    }

    return $permalink;
  }

  public static function base_class_name( $class ) {
    if ( preg_match( '/([^\\\]*)$/', $class, $m ) ) {
      return $m[1];
    } else {
      return '';
    }
  }

  public static function snakecase( $str, $delim = '_' ) {
    // Search for '_-' then just lowercase and ensure correct delim
    if ( preg_match( '/[-_]/', $str ) ) {
      $str = preg_replace( '/[-_]/', $delim, $str );
    } else { // assume camel case
      $str = preg_replace( '/([A-Z])/', $delim . '$1', $str );
      $str = preg_replace( '/^' . preg_quote( $delim ) . '/', '', $str );
    }

    return strtolower( $str );
  }

  public static function model_for_controller( BaseCtrl $ctrl ) {
    $ctrl_class  = \get_class( $ctrl );
    $ctrl_name   = self::base_class_name( $ctrl_class );
    $model_name  = Inflector::singularize( $ctrl_name );
    $model_class = base\MODELS_NAMESPACE . "\\{$model_name}";

    if ( class_exists( $model_class ) ) {
      return $model_class;
    }

    return false;
  }

  public static function controller_for_model( BaseModel $model ) {
    $model_class = \get_class( $model );
    $model_name  = self::base_class_name( $model_class );
    $ctrl_name   = Inflector::pluralize( $model_name );
    $ctrl_class  = base\CTRLS_NAMESPACE . "\\{$ctrl_name}";

    if ( class_exists( $ctrl_class ) ) {
      return $ctrl_class;
    }

    return false;
  }

  /* PLUGGABLE FUNCTIONS AS TO NOT STEP ON OTHER PLUGINS' CODE */
  public static function get_currentuserinfo() {
    self::_include_pluggables( 'wp_get_current_user' );
    $current_user = wp_get_current_user();

    if ( isset( $current_user->ID ) && $current_user->ID > 0 ) {
      return new \WP_User( $current_user->ID );
    } else {
      return false;
    }
  }

  public static function get_userdata( $id ) {
    self::_include_pluggables( 'get_userdata' );
    $data = get_userdata( $id );
    // Handle the returned object for WordPress > 3.2
    if ( ! empty( $data->data ) ) {
      return $data->data;
    }
    return $data;
  }

  public static function get_user_by( $field = 'login', $value = null ) {
    self::_include_pluggables( 'get_user_by' );
    return get_user_by( $field, $value );
  }

  public static function get_userdatabylogin( $screenname ) {
    self::_include_pluggables( 'get_user_by' );
    $data = get_user_by( 'login', $screenname );
    // $data = get_userdatabylogin($screenname);
    // Handle the returned object for WordPress > 3.2
    if ( isset( $data->data ) && ! empty( $data->data ) ) {
      return $data->data;
    }
    return $data;
  }

  public static function get_user_admin_capability() {
    return apply_filters( base\SLUG_KEY . '-admin-capability', 'remove_users' );
  }

  public static function is_user_admin( $user_id = null ) {
    $cap = self::get_user_admin_capability();

    if ( empty( $user_id ) ) {
      return self::current_user_can( $cap );
    } else {
      return user_can( $user_id, $cap );
    }
  }

  public static function current_user_can( $role ) {
    self::_include_pluggables( 'wp_get_current_user' );
    return current_user_can( $role );
  }

  public static function minutes( $n = 1 ) {
    return $n * 60;
  }

  public static function hours( $n = 1 ) {
    return $n * self::minutes( 60 );
  }

  public static function days( $n = 1 ) {
    return $n * self::hours( 24 );
  }

  public static function weeks( $n = 1 ) {
    return $n * self::days( 7 );
  }

  public static function months( $n, $base_ts = false, $backwards = false, $day_num = false ) {
    $base_ts = empty( $base_ts ) ? time() : $base_ts;

    $month_num  = gmdate( 'n', $base_ts );
    $day_num    = ( (int) $day_num < 1 || (int) $day_num > 31 ) ? gmdate( 'j', $base_ts ) : $day_num;
    $year_num   = gmdate( 'Y', $base_ts );
    $hour_num   = gmdate( 'H', $base_ts );
    $minute_num = gmdate( 'i', $base_ts );
    $second_num = gmdate( 's', $base_ts );

    // We're going to use the FIRST DAY of month for our calc date, then adjust the day of month when we're done
    // This allows us to get the correct target month first, then set the right day of month afterwards
    try {
      $calc_date = new \DateTime( "{$year_num}-{$month_num}-1 {$hour_num}:{$minute_num}:{$second_num}", new \DateTimeZone( 'UTC' ) );
    } catch ( Exception $e ) {
      return 0;
    }

    if ( $backwards ) {
      $calc_date->modify( "-{$n} month" );
    } else {
      $calc_date->modify( "+{$n} month" );
    }

    $days_in_new_month = $calc_date->format( 't' );

    // Now that we have the right month, let's get the right day of month
    if ( $days_in_new_month < $day_num ) {
      $calc_date->modify( 'last day of this month' );
    } elseif ( $day_num > 1 ) {
      $add_days = ( $day_num - 1 ); // $calc_date is already at the first day of the month, so we'll minus one day here
      $calc_date->modify( "+{$add_days} day" );
    }

    // If $backwards is true, this will most likely be a negative number so we'll use abs()
    return abs( $calc_date->getTimestamp() - $base_ts );
  }


  public static function years( $n, $year_timestamp = false, $backwards = false ) {
    $year_timestamp = empty( $year_timestamp ) ? time() : $year_timestamp;
    $seconds        = 0;

    // If backward we start in the previous year
    if ( $backwards ) {
      $year_timestamp -= self::days( (int) date( 't', $year_timestamp ) );
    }

    for ( $i = 0; $i < $n; $i++ ) {
      $seconds += $year_seconds = self::days( 365 + (int) date( 'L', $year_timestamp ) );
      // We want the years going into the past
      if ( $backwards ) {
        $year_timestamp -= $year_seconds;
      } else { // We want the years going into the past
        $year_timestamp += $year_seconds;
      }
    }

    return $seconds;
  }

  public static function wp_mail( $recipient, $subject, $message, $header ) {
    self::_include_pluggables( 'wp_mail' );

    // Let's get rid of the pretty TO's -- causing too many problems
    // mbstring?
    if ( extension_loaded( 'mbstring' ) ) {
      if ( mb_strpos( $recipient, '<' ) !== false ) {
        $recipient = mb_substr( $recipient, ( mb_strpos( $recipient, '<' ) + 1 ), -1 );
      }
    } else {
      if ( strpos( $recipient, '<' ) !== false ) {
        $recipient = substr( $recipient, ( strpos( $recipient, '<' ) + 1 ), -1 );
      }
    }

    return wp_mail( $recipient, $subject, $message, $header );
  }

  public static function is_user_logged_in() {
    self::_include_pluggables( 'is_user_logged_in' );
    return is_user_logged_in();
  }

  public static function get_avatar( $id, $size ) {
    self::_include_pluggables( 'get_avatar' );
    return get_avatar( $id, $size );
  }

  public static function collect( $items ) {
    return new Collection( $items );
  }

  public static function wp_hash_password( $password_str ) {
    self::_include_pluggables( 'wp_hash_password' );
    return wp_hash_password( $password_str );
  }

  public static function wp_generate_password( $length, $special_chars ) {
    self::_include_pluggables( 'wp_generate_password' );
    return wp_generate_password( $length, $special_chars );
  }

  public static function wp_redirect( $location, $status = 302 ) {
    self::_include_pluggables( 'wp_redirect' );
    return wp_redirect( $location, $status );
  }

  public static function wp_salt( $scheme = 'auth' ) {
    self::_include_pluggables( 'wp_salt' );
    return wp_salt( $scheme );
  }

  public static function check_ajax_referer( $slug, $param ) {
    self::_include_pluggables( 'check_ajax_referer' );
    return check_ajax_referer( $slug, $param );
  }

  public static function check_admin_referer( $slug, $param ) {
    self::_include_pluggables( 'check_admin_referer' );
    return check_admin_referer( $slug, $param );
  }

  public static function send_enrollment_notice( $program, $product ) {
    $params = AppHelper::get_enrollment_email_params( $program, $product );
    self::send_notices( $program, null, EnrollmentCapEmail::class, $params );
  }

  public static function send_program_started_notice( $program, $product, $enrollment ) {
    $mepr_options = \MeprOptions::fetch();
    if ( ! isset($mepr_options->emails[ ProgramStartedEmail::class ]) || ! $mepr_options->emails[ ProgramStartedEmail::class ]['enabled'] ) {
      return false;
    }
    $params = AppHelper::get_enrollment_email_params( $program, $product, $enrollment );
    self::send_notices( $enrollment, null, ProgramStartedEmail::class, $params );
  }

  public static function send_program_completed_notice( $program, $enrollment ) {
    $mepr_options = \MeprOptions::fetch();
    if ( ! isset($mepr_options->emails[ ProgramCompletedEmail::class ]) || ! $mepr_options->emails[ ProgramCompletedEmail::class ]['enabled'] ) {
      return false;
    }
    $params = AppHelper::get_enrollment_email_params( $program, null, $enrollment );
    self::send_notices( $program, null, ProgramCompletedEmail::class, $params );
  }

  public static function send_message_received_notice( $sender_id, array $participants, $room_uuid ) {
    $mepr_options = \MeprOptions::fetch();
    if ( ! isset($mepr_options->emails[ MessageReceivedEmail::class ]) || ! $mepr_options->emails[ MessageReceivedEmail::class ]['enabled'] ) {
      return false;
    }

    $sender = new \MeprUser( $sender_id );
    $params = array_merge( \MeprUsersHelper::get_email_params( $sender ), [ 'message_url' => AppHelper::get_room_url( $room_uuid ) ] );

    add_filter( 'mepr_bkg_email_jobs_enabled', '__return_true' );

    foreach ( $participants as $participant ) {
      if ( absint( $participant->user_id ) === absint( $sender_id ) ) { continue; }
      $recipient = new \MeprUser( $participant->user_id );
      self::send_notices( $recipient, MessageReceivedEmail::class, null, $params );
    }

    add_filter( 'mepr_bkg_email_jobs_enabled', '__return_false' );
  }

  /** EMAIL NOTICE METHODS **/
  public static function send_notices( $obj, $user_class = null, $admin_class = null, $params = array(), $force = false ) {
    if ( $obj instanceof Enrollment ) {
      $usr = new \MeprUser( $obj->student_id );
    } elseif ( $obj instanceof \MeprUser ) {
      $usr = $obj;
    } else {
      $usr = new \Mepruser();
    }

    $disable_email = apply_filters( 'mepr_send_email_disable', false, $obj, $user_class, $admin_class );
    try {
      if ( ! is_null( $user_class ) && false == $disable_email ) {
        $uemail     = \MeprEmailFactory::fetch( $user_class );
        $uemail->to = $usr->formatted_email();

        if ( $force ) {
          $uemail->send( $params );
        } else {
          $uemail->send_if_enabled( $params );
        }
      } elseif ( ! is_null( $admin_class ) && false == $disable_email ) {
        $aemail = \MeprEmailFactory::fetch( $admin_class );
        if ( $force ) {
          $aemail->send( $params );
        } else {
          $aemail->send_if_enabled( $params );
        }
      }
    } catch ( \Exception $e ) {
      // Fail silently for now
      self::error_log( $e->getMessage() );
    }
  }

  public static function get_edit_post_link( $post = 0, $context = 'display' ) {
    $post = get_post( $post );

    if ( ! $post ) {
      return;
    }

    $action           = '&amp;action=edit';
    $post_type_object = get_post_type_object( $post->post_type );

    if ( ! $post_type_object ) {
      return;
    }

    $link = '';
    $link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );

    /**
     * Filters the post edit link.
     *
     * @since 2.3.0
     *
     * @param string $link    The edit link.
     * @param int    $post_id Post ID.
     * @param string $context The link context. If set to 'display' then ampersands
     *                        are encoded.
     */
    return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
  }



  public static function _include_pluggables( $function_name ) {
    if ( ! function_exists( $function_name ) ) {
      require_once ABSPATH . WPINC . '/pluggable.php';
    }
  }
}
