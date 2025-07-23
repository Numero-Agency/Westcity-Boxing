<?php

namespace memberpress\coachkit\controllers;

use MeprAddonUpdates;
use stdClass;
use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\lib\Activity;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\models\Program;
use memberpress\coachkit\models\Student;
use memberpress\coachkit\lib\CtrlFactory;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * App Controller class
 */
class AppCtrl extends lib\BaseCtrl {

  /**
   * Load class hooks
   *
   * @return void
   */
  public function load_hooks() {
    add_action( 'plugins_loaded', array( $this, 'addon_updates' ) );
    add_action( 'admin_init', array( $this, 'install' ) ); // DB upgrade is handled automatically here
    add_filter( 'rewrite_rules_array', array( $this, 'custom_rewrite_rules' ) );
    add_filter( 'query_vars', array( $this, 'custom_query_vars' ) );
    add_action( 'template_include', array( $this, 'replace_templates' ) );
    add_action( 'the_content', array( $this, 'replace_content' ) );
    add_action( 'template_redirect', array( $this, 'redirect_profile' ) );

    add_action( 'custom_menu_order', array( $this, 'admin_menu_order' ), 12 );
    add_action( 'menu_order', array( $this, 'admin_menu_order' ), 12 );
    add_action( 'admin_notices', array( $this, 'coaching_page_warning' ) );
    add_action( 'mepr_display_options_tabs', array( $this, 'coaching_options_tab' ), 100 );
    add_action( 'mepr_display_options', array( $this, 'coaching_options' ), 100 );
    add_action( 'mepr-process-options', array( $this, 'store_options' ) );
    add_action( 'mepr_account_nav', array( $this, 'account_nav_tab' ), 99 );
    add_action( 'in_admin_header', array( $this, 'mp_admin_header' ), 0 );
    add_action( 'profile_update', array( $this, 'preserve_custom_roles' ), 10, 3 );
    add_action( 'widgets_init', array( $this, 'register_global_widget' ) );

    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_unwanted_styles' ), 9999 );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2 );
    add_filter( 'mepr-can-you-buy-me-override', array( $this, 'lock_membership_from_purchase' ), 10, 2 );
    add_action( 'mepr-account-is-active', array( $this, 'process_signup' ) );
    add_action( 'upgrader_process_complete', array( $this, 'flush_permalinks'), 10, 2 );
    // add_action( 'wp_head', array( $this, 'theme_style' ) );
    add_action( 'mepr-after-readylaunch-options', array( $this,'render_readylaunch_settings' ), 999 );
  }

  /**
   * Enable updates for this add-on.
   */
  public function addon_updates() {
    if(class_exists('MeprAddonUpdates')) {
      new MeprAddonUpdates(
        base\EDITION,
        base\PLUGIN_SLUG,
        'mpch_license_key',
        'MemberPress CoachKit',
        'Coaching features for MemberPress.'
      );
    }
  }

  public static function theme_style() {
    $mepr_options = \MeprOptions::fetch();

    $primary_color = ! empty( $mepr_options->design_primary_color ) ? $mepr_options->design_primary_color : '#06429e';
    $text_color    = AppHelper::get_contrast_color( $primary_color );

    if ( ! AppHelper::is_readylaunch() ) {
      return;
    }

    $html  = '<style type="text/css">';
    $html .= sprintf( '.mpch-account-nav, .mpch-site-header {background:%s!important}', $primary_color );
    $html .= sprintf( '.mpch-account-nav a:not(.active){color:rgba(%s)}', AppHelper::hex_to_rgb( $text_color, 0.7 ) );
    $html .= sprintf( '.mpch-site-header .separator{background-color:rgba(%s)}', AppHelper::hex_to_rgb( $text_color, 0.7 ) );
    $html .= sprintf( '.mpch-account-nav a:hover{color:%s}', $text_color );
    $html .= sprintf( '.mpch-site-header .profile-menu__arrow_down, .mpch-site-header .profile-menu__text {color:%s}', $text_color );
    $html .= sprintf( '.mpch-site-header .profile-menu__text--small{color:rgba(%s)}', AppHelper::hex_to_rgb( $text_color, 0.7 ) );
    $html .= '</style>';

    echo $html; // phpcs:ignore
  }

  /**
   * Register custom post type for all CPTs
   * Called from activation.php
   * Hook: register_activation_hook
   */
  public function register_all_cpts() {
    $program_ctrl = ProgramCtrl::fetch( true );
    $program_ctrl->register_post_type();
  }

  /**
   * Add user roles
   * Hook: register_activation_hook
   *
   * @return void
   */
  public function add_user_roles() {
    add_role(
      Coach::ROLE,
      esc_html_x( 'MemberPress Coach', 'ui', 'memberpress-coachkit' ),
      get_role( 'subscriber' )->capabilities
    );

    add_role(
      Student::ROLE,
      esc_html_x( 'MemberPress Student', 'ui', 'memberpress-coachkit' ),
      get_role( 'subscriber' )->capabilities
    );
  }

  /**
   * Add "MemberPress Coach" role to all ADMINS
   * Hook: register_activation_hook
   *
   * @return void
   */
  public function assign_coaching_role_to_admins() {
    $admin_users = get_users( [ 'capability' => 'manage_options' ] );
    foreach ( $admin_users as $user ) {
      $user->add_role( Coach::ROLE );
    }
  }

  /**
   * Remove "MemberPress Coach" role from all ADMINS
   *
   * @hook: register_deactivation_hook
   * @return void
   */
  public function remove_coaching_role_from_admins() {
    $admin_users = get_users( [ 'capability' => 'manage_options' ] );
    foreach ( $admin_users as $user ) {
      $user->remove_role( Coach::ROLE );
    }
  }

  /**
   * Set up menu
   *
   * @hook: plugins_loaded
   * @return void
   */
  public static function setup_menus() {
    $app = self::fetch();
    add_action( 'admin_menu', array( $app, 'menu' ), 99 );
  }

  /**
   * Add custom rewrite rules for pretty coaching page URLs
   *
   * @param array $rules
   * @return array
   */
  public function custom_rewrite_rules( $rules ) {
    $mepr_options     = \MeprOptions::fetch();
    $coaching_page_id = $mepr_options->coaching_page_id;
    $slug             = $coaching_page_id ? get_post_field( 'post_name', $coaching_page_id ) : ''; // Get the slug of the selected base page

    if ( empty( $coaching_page_id ) || empty( $slug ) ) {
      return $rules;
    }

    // $label_for_client = AppHelper::get_label_for_client();
    $label_for_clients = AppHelper::get_label_for_client(true);

    $new_rules = array(
      $slug . '/?$'                     => 'index.php?page_id=' . $coaching_page_id . '&coaching=1',
      $slug . '/messages/?$'            => 'index.php?page_id=' . $coaching_page_id . '&coaching_messages=1',
      $slug . '/messages/([^/]+)/?$'    => 'index.php?page_id=' . $coaching_page_id . '&coaching_message=$matches[1]',
      $slug . '/notifications/?$'       => 'index.php?page_id=' . $coaching_page_id . '&coaching_notifications=1',
      $slug . '/enrollments/?$'         => 'index.php?page_id=' . $coaching_page_id . '&coaching_enrollments=1',
      $slug . '/enrollments/([^/]+)/?$' => 'index.php?page_id=' . $coaching_page_id . '&coaching_enrollment=$matches[1]',
      $slug . '/'.strtolower($label_for_clients).'/?$'            => 'index.php?page_id=' . $coaching_page_id . '&coaching_students=1',
      $slug . '/'.strtolower($label_for_clients).'/([^/]+)/?$'    => 'index.php?page_id=' . $coaching_page_id . '&coaching_student=$matches[1]',
    );

    return $new_rules + $rules;
  }

  /**
   * Add query vars
   *
   * @param array $vars
   * @return array
   */
  public function custom_query_vars( $vars ) {
    $vars[] = 'coaching';
    $vars[] = 'coaching_messages';
    $vars[] = 'coaching_message';
    $vars[] = 'coaching_enrollments';
    $vars[] = 'coaching_enrollment';
    $vars[] = 'coaching_notifications';
    $vars[] = 'coaching_students';
    $vars[] = 'coaching_student';
    return $vars;
  }

  /**
   * Replace default coaching page template with readylaunch templates
   *
   * @param string $template
   * @return string
   */
  public function replace_templates( $template ) {
    global $wpdb;

    if ( ! AppHelper::is_readylaunch() ) {
      return $template;
    }

    $coaching               = AppHelper::get_query_var_with_fallback( 'coaching' );
    $coaching_messages      = AppHelper::get_query_var_with_fallback( 'coaching_messages' );
    $coaching_message       = AppHelper::get_query_var_with_fallback( 'coaching_message' );
    $coaching_enrollments   = AppHelper::get_query_var_with_fallback( 'coaching_enrollments' );
    $coaching_enrollment    = AppHelper::get_query_var_with_fallback( 'coaching_enrollment' );
    $coaching_notifications = AppHelper::get_query_var_with_fallback( 'coaching_notifications' );
    $coaching_students      = AppHelper::get_query_var_with_fallback( 'coaching_students' );
    $coaching_student       = AppHelper::get_query_var_with_fallback( 'coaching_student' );

    if ( ! AppHelper::is_coaching_page() ) {
      return $template;
    }

    $options = AppHelper::get_options();
    $mepr_options = \MeprOptions::fetch();
    $is_coach     = AppHelper::user_has_role( Coach::ROLE );
    $is_student   = AppHelper::user_has_role( Student::ROLE );

    if ( $is_coach ) {
      $current_user = new Coach( get_current_user_id() );
      $groups       = $current_user->get_groups();
      AppHelper::prevent_dual_roles($current_user->ID);
    } elseif ( $is_student ) {
      $current_user = new Student( get_current_user_id() );
      $groups       = $current_user->get_groups();
    } else {
      $current_user = new \MeprUser( get_current_user_id() );
      $groups       = array();
    }

    $current_user_id   = $current_user->ID;
    $profile_url       = $mepr_options->account_page_url();
    $enrollment_url    = AppHelper::get_enrollment_page_url();
    $messages_url      = AppHelper::get_messages_page_url();
    $notifications_url = AppHelper::get_notifications_page_url();
    $logout_url        = \MeprUtils::logout_url();
    $students_url      = AppHelper::get_students_url();

    $active_class = function ( $var ) {
      if ( 'coaching_enrollments' === $var && get_query_var( 'coaching_enrollment' ) ) {
        return 'bg-black bg-opacity-50 text-white active';
      }
      if ( 'coaching_students' === $var && get_query_var( 'coaching_student' ) ) {
        return 'bg-black bg-opacity-50 text-white active';
      }
      return get_query_var( $var ) ? 'bg-black bg-opacity-50 text-white active' : '';
    };

    $name             = AppHelper::get_fullname();
    $photo_url        = get_avatar_url( $current_user_id );
    $logo_url         = wp_get_attachment_url( $mepr_options->design_logo_img );
    $email            = $current_user->user_email;
    $enable_messaging = $options->enable_messaging;

    if(count($groups) > 3){
      $groups = array_slice($groups, 0, 3);
    }

    $data = (object) array(
      'header'         => View::get_string( 'theme/partials/header' ),
      'footer'         => View::get_string( 'theme/partials/footer', get_defined_vars() ),
      'navbar'         => View::get_string( 'theme/partials/navbar', get_defined_vars() ),
      'sidebar'        => View::get_string( 'theme/readylaunch/sidebar', get_defined_vars() ),
      'snackbar'       => View::get_string( 'theme/partials/snackbar' ),
      'arrow_url'      => base\IMAGES_URL . '/arrow-down.svg',
      'is_readylaunch' => AppHelper::is_readylaunch(),
    );

    if ( $coaching ) {
      $template = View::file( 'theme/readylaunch/profile' );
    } elseif ( $coaching_enrollments ) { // plural
      $student           = new Student( $current_user_id );
      $data->enrollments = $student->get_enrollment_data( [ 'milestones', 'public_url' ] )->all();
      $template          = View::file( 'theme/readylaunch/enrollments' );
    } elseif ( $coaching_enrollment ) { // single
      $enrollment_id    = absint( $coaching_enrollment );
      $data->enrollment = AppHelper::get_enrollment_content( $enrollment_id );
      if ( $enrollment_id > 0 ) {
        $template = View::file( 'theme/readylaunch/single-enrollment' );
      }
    } elseif ( $coaching_messages || $coaching_message ) {
      $data->messages = array( 'rooms' => [], 'contacts' => [] );
      if ( $is_coach || $is_student ) {
        $data->messages = AppHelper::get_messages_content( $coaching_message );
      }

      $template = View::file( 'theme/readylaunch/messages' );
    } elseif ( $coaching_notifications ) {
      $notifications_url = AppHelper::get_notifications_page_url();
      if ( $is_coach ) {
        $data->notifications = Activity::get_notifications_for_coach( $current_user_id );
      } else {
        $data->notifications = Activity::get_notifications_for_user( $current_user_id );
      }
      $template = View::file( 'theme/readylaunch/notifications' );
    } elseif ( $coaching_students ) {
      $coach                = new Coach( $current_user_id );
      $data->groups         = $coach->get_groups();
      $students             = AppHelper::get_students_content();
      $data->students       = $students['records'];
      $data->students_count = $students['count'];
      $data->get_group_id   = isset( $_GET['group_id'] ) ? $_GET['group_id'] : null;
      $template             = View::file( 'theme/readylaunch/students' );
    } elseif ( $coaching_student ) { // single
      $student_id          = absint( $coaching_student );
      $data->student       = AppHelper::get_student_content( $student_id );
      $data->student_notes = array(
        'notes'      => $data->student->notes,
        'student_id' => $coaching_student,
      );
      if ( $student_id > 0 ) {
        $template = View::file( 'theme/readylaunch/single-student' );
      }
    }
    set_query_var( 'coaching-data', $data );
    set_query_var( 'options', $options );

    return $template;
  }

  /**
   * Replace content of selected coaching page
   *
   * @param string $content
   * @return string
   */
  public function replace_content( $content ) {
    global $post;

    $options = AppHelper::get_options();

    $coaching               = AppHelper::get_query_var_with_fallback( 'coaching' );
    $coaching_messages      = $options->enable_messaging ? AppHelper::get_query_var_with_fallback( 'coaching_messages' ) : false;
    $coaching_message       = AppHelper::get_query_var_with_fallback( 'coaching_message' );
    $coaching_enrollments   = AppHelper::get_query_var_with_fallback( 'coaching_enrollments' );
    $coaching_enrollment    = AppHelper::get_query_var_with_fallback( 'coaching_enrollment' );
    $coaching_notifications = AppHelper::get_query_var_with_fallback( 'coaching_notifications' );
    $coaching_students      = AppHelper::get_query_var_with_fallback( 'coaching_students' );
    $coaching_student       = AppHelper::get_query_var_with_fallback( 'coaching_student' );

    // If not loading coaching content, exit
    if ( false == AppHelper::is_coaching_page() ) {
      return $content;
    }

    // If not logged in, show unauthorized
    if ( ! Utils::is_user_logged_in() ) {
      $content = do_shortcode( \MeprRulesCtrl::unauthorized_message( $post ) );
      return $content;
    }

    ob_start();

    $options = AppHelper::get_options();
    $mepr_options      = \MeprOptions::fetch();
    $current_user_id   = get_current_user_id();
    $profile_url       = $mepr_options->account_page_url();
    $enrollment_url    = AppHelper::get_enrollment_page_url();
    $messages_url      = AppHelper::get_messages_page_url();
    $notifications_url = AppHelper::get_notifications_page_url();
    $student_url       = AppHelper::get_students_url();
    $active_class      = function ( $var ) {
      if ( 'coaching_enrollments' === $var && get_query_var( 'coaching_enrollment' ) ) {
        return 'font-bold';
      }
      return get_query_var( $var ) ? 'font-bold' : '';
    };

    $view = (object) array(
      'snackbar'         => View::get_string( 'theme/partials/snackbar' ),
      'arrow_url'        => base\IMAGES_URL . '/arrow-down.svg',
      'is_readylaunch'   => AppHelper::is_readylaunch(),
      'enable_messaging' => $options->enable_messaging,
    );

    $is_coach   = AppHelper::user_has_role( Coach::ROLE );
    $is_student = AppHelper::user_has_role( Student::ROLE );
    $label_for_clients = AppHelper::get_label_for_client(true);

    if($is_coach){
      AppHelper::prevent_dual_roles($current_user_id);
    }

    View::render( '/theme/default/nav', get_defined_vars() );

    if ( $coaching_enrollments ) {
      $view->enrollments = AppHelper::get_enrollments_content();
      View::render( '/theme/default/enrollments', get_defined_vars() );
    } elseif ( $coaching_enrollment ) { // single
      $enrollment_id    = absint( $coaching_enrollment );
      $view->enrollment = AppHelper::get_enrollment_content( $enrollment_id );
      if ( $enrollment_id > 0 ) {
        View::render( '/theme/default/single-enrollment', get_defined_vars() );
      }
    } elseif ( $coaching_messages || $coaching_message ) {
      $view->messages = AppHelper::get_messages_content( $coaching_message );
      View::render( '/theme/default/messages', get_defined_vars() );
    } elseif ( $coaching_notifications ) {
      $notifications_url = AppHelper::get_notifications_page_url();
      if ( $is_coach ) {
        $view->notifications = Activity::get_notifications_for_coach( $current_user_id );
      } else {
        $view->notifications = Activity::get_notifications_for_user( $current_user_id );
      }
      View::render( '/theme/default/notifications', get_defined_vars() );
    } elseif ( $coaching_students ) {
      $students             = AppHelper::get_students_content();
      $coach                = new Coach( $current_user_id );
      $view->groups         = $coach->get_groups();
      $view->students       = $students['records'];
      $view->students_count = $students['count'];
      $view->get_group_id   = isset( $_GET['group_id'] ) ? $_GET['group_id'] : null;
      View::render( '/theme/default/students', get_defined_vars() );
    } elseif ( $coaching_student ) { // single
      $student_id          = absint( $coaching_student );
      $view->student       = AppHelper::get_student_content( $student_id );
      $view->student_notes = array(
        'notes'      => $view->student->notes,
        'student_id' => $coaching_student,
      );

      if ( $student_id > 0 ) {
        View::render( '/theme/default/single-student', get_defined_vars() );
      }
    }
    return ob_get_clean();
  }

  public function redirect_profile() {
    $coaching            = AppHelper::get_query_var_with_fallback( 'coaching' );;
    $coaching_enrollment = AppHelper::get_query_var_with_fallback( 'coaching_enrollment' );;
    $coaching_student    = AppHelper::get_query_var_with_fallback( 'coaching_student' );;

    $mepr_options = \MeprOptions::fetch();
    $account_url  = $mepr_options->account_page_url();

    // If not logged in, redirect to account page
    if ( AppHelper::is_coaching_page() && ! is_user_logged_in() ) {
      wp_safe_redirect( $account_url );
      die;
    }

    // If student can't view a single enrollment page, redirect to all enrollments
    if ( $coaching_enrollment ) {
      $enrollment = new Enrollment( $coaching_enrollment );
      if ( absint( $enrollment->student_id ) !== get_current_user_id() ) {
        wp_safe_redirect( AppHelper::get_enrollment_page_url() );
        die;
      }
    }

    // If user can't view a single student profile, redirect
    if ( $coaching_student ) {
      $is_coach = AppHelper::user_has_role( Coach::ROLE );

      // Not a coach, not an admin
      if ( ! $is_coach && ! current_user_can( 'manage_options' ) ) {
        wp_safe_redirect( AppHelper::get_enrollment_page_url() );
        die;
      }

      // You're a coach but not the coach of this student
      if ( $is_coach ) {
        $e = Enrollment::is_student_enrolled_with_coach( $coaching_student, get_current_user_id() );
        if ( ! $e ) {
          wp_safe_redirect( AppHelper::get_students_url() );
          die;
        }
      }
    }

    // If on coaching profile page, redirect to enrollments or students
    if ( $coaching ) {

      if ( AppHelper::user_has_role( Coach::ROLE ) ) {
        wp_safe_redirect( AppHelper::get_students_url() );
        die;
      }

      wp_safe_redirect( AppHelper::get_enrollment_page_url() );
      die;
    }
  }

  /**
   * Dequeues unwanted stylesheets except 'dashicons', 'admin-bar', and 'query-monitor'.
   */
  public function dequeue_unwanted_styles() {
    if ( ! AppHelper::is_coaching_page() || ! AppHelper::is_readylaunch() ) {
      return;
    }

    global $wp_styles;

    foreach ( $wp_styles->queue as $handle ) {
      $styles = [ 'wp-block-library', 'wp-block-library-theme', 'mp-theme', 'mpch-theme-rl', 'dashicons', 'admin-bar', 'query-monitor', 'mpdl-fontello-styles' ];
      // Keep only the desired stylesheets
      if ( ! in_array( $handle, $styles, true ) ) {
        wp_dequeue_style( $handle );     // Dequeue the unwanted style
        wp_deregister_style( $handle );  // Deregister the unwanted style
      }
    }

    add_filter('et_use_dynamic_css', function () {
      return false;
    });
  }

  /**
   * Enqueue scripts
   *
   * @return void
   */
  public function enqueue_scripts() {
    if ( ! AppHelper::is_coaching_page() ) {
      return;
    }

    $upload_dir = wp_get_upload_dir();
    $current_user_id = get_current_user_id();
    $current_user = new \MeprUser( $current_user_id );

    $theme_data = array(
      'nonce'        => array(
        'theme'   => wp_create_nonce( base\SLUG_KEY . '_theme' ),
        'message' => wp_create_nonce( base\SLUG_KEY . '_message' . $current_user_id ),
        'notif'   => wp_create_nonce( base\SLUG_KEY . '_notif' . $current_user_id ),
        'student' => wp_create_nonce( base\SLUG_KEY . '_students' ),
      ),
      'user' => [
        'id' => $current_user->ID,
        'name' => $current_user->full_name(),
        'role' => AppHelper::user_has_role( Coach::ROLE ) ? 'coach' : 'student'
      ],
      'current_user' => $current_user->full_name(),
      'current_user_id' => $current_user_id,
      'ajaxurl'      => admin_url( 'admin-ajax.php' ),
      'tz'           => date_default_timezone_get(),
      'mimes'        => array_values( AppHelper::message_mime_type() ),
      'imagesURL'    => base\IMAGES_URL,
      'basedir'      => $upload_dir['basedir'],
      'baseurl'      => $upload_dir['baseurl'],
      'messagesUrl'  => AppHelper::get_messages_page_url(),
    );

    if ( AppHelper::is_readylaunch() ) {
      wp_enqueue_style( 'mpch-theme-rl', base\CSS_URL . '/theme-rl.css', array( 'dashicons' ), base\VERSION );
      $this->add_css_variables();
    } else {
      wp_enqueue_style( 'mpch-theme-default', base\CSS_URL . '/theme.css', array( 'dashicons' ), base\VERSION );
    }

    wp_enqueue_script( 'mpch-alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.12.1/dist/cdn.min.js', array(), '3.12.1', true );
    wp_enqueue_script( 'mpch-popper', 'https://unpkg.com/@popperjs/core@2', array(), '2.0', true );
    wp_enqueue_script( 'mpch-tippy', 'https://unpkg.com/tippy.js@6', array(), '6.0', true );
    if ( is_plugin_active( 'memberpress-downloads/main.php' ) ){
      wp_enqueue_style('mpdl-fontello-styles', \memberpress\downloads\FONTS_URL.'/fontello/css/fontello.css', array(), base\VERSION);
    }
    wp_enqueue_script( 'mpch-theme', base\JS_URL . '/theme.js', array( 'wp-util' ), '1.0', true );
    wp_localize_script( 'mpch-theme', 'themeData', $theme_data );
        wp_enqueue_style('mpdl-fontello-styles', base\FONTS_URL.'/fontello/css/fontello.css', array(), base\VERSION);

  }

  /**
   * CSS Variables here to replace the PHP theming
   *
   * @return void
   */
  public function add_css_variables() {
    $mepr_options  = \MeprOptions::fetch();
    $primary_color = ! empty( $mepr_options->design_primary_color ) ? $mepr_options->design_primary_color : '#06429e';
    $text_color    = AppHelper::get_contrast_color( $primary_color );

    // Define your CSS variables here
    $css_variables = '
      :root {
        --mpch-primary-color: ' . $primary_color . ';
        --mpch-primary-text-color: ' . $text_color . ';
        --mpch-primary-text-color-with-opacity: ' . AppHelper::hex_to_rgb( $text_color, 0.7 ) . ';
      }
    ';

    // Enqueue the CSS with your variables
    wp_add_inline_style( 'mpch-theme-rl', $css_variables );
  }

  public function add_defer_attribute( $tag, $handle ) {
    if ( 'mpch-alpine' === $handle ) { // Replace 'custom-script' with your script handle
      $tag = str_replace( ' src', ' defer src', $tag );
    }
    return $tag;
  }

  /**
   * Enroll student after they purchase membership
   *
   * @param \MeprTransaction $transaction MemberPress Transaction Object
   * @return void
   */
  public function process_signup( \MeprTransaction $transaction ) {
    $product           = $transaction->product();
    $assigned_programs = Utils::collect(
      maybe_unserialize( get_post_meta( $product->ID, Program::PRODUCT_META, true ) )
    );

    $enrollment = Enrollment::get_one( [ 'txn_id' => $transaction->id ] );
    if ( absint( $transaction->id ) > 0 && $enrollment ) {
      return;
    }

    if ( $assigned_programs->empty() ) {
      return;
    }

    $enrollment_limit_notice = false;

    $assigned_programs->each(function ( $item ) use ( $transaction, $product, $enrollment_limit_notice ) {

      if ( ! isset( $item['program_id'] ) ) {
        return;
      }  // return is same as continue here

      $program  = new Program( $item['program_id'] );
      $features = array('messaging');
      $group    = $program->next_available_group( $program->groups(), $transaction->user_id );

      if ( ! $group ) {
        Utils::error_log( esc_html__( 'Problem adding student to cohort', 'memberpress-coachkit' ) );
        return;
      }

      $enrollment             = new Enrollment();
      $enrollment->student_id = $transaction->user_id;
      $enrollment->group_id   = $group->id;
      $enrollment->start_date = $group->get_start_date();
      $enrollment->program_id = $group->program_id;
      $enrollment->txn_id     = $transaction->id;
      $enrollment->created_at = Utils::ts_to_mysql_date( time() );
      $enrollment->features   = implode( ',', $features );
      $enrollment_id          = $enrollment->store();

      if ( is_wp_error( $enrollment_id ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
          Utils::error_log( $enrollment_id->get_error_message() );
        }
      } else {
        Utils::send_program_started_notice( $program, $product, $enrollment );

        $remaining_enrollments = $program->count_remaining_enrollments();
        if ( is_numeric( $remaining_enrollments ) && $remaining_enrollments <= 3 ) {
          Utils::send_enrollment_notice( $program, $product );
        }
      }
    });
  }

  public  static function flush_permalinks($upgrader_object, $options){
    if ( $options['action'] !== 'update' || $options['type'] !== 'plugin' || ! isset( $options['plugins'] ) ) {
      return;
    }

    $plugin = 'memberpress-coachkit/main.php';
    $version = '1.0.5';

    // Check if the updated plugin is memberpress-coachkit and version is 1.0.5
    if ( in_array( $plugin, $options['plugins'] ) ) {
      $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
      if ( $plugin_data && isset( $plugin_data['Version'] ) && $plugin_data['Version'] === $version ) {
        flush_rewrite_rules();
      }
    }
  }

  /**
   * Restrictions on purchasing a program membership
   *
   * @param bool         $bool
   * @param \MeprProduct $product
   * @return bool
   */
  public function lock_membership_from_purchase( $bool, $product ) {

    $programs = Utils::collect(
      maybe_unserialize( get_post_meta( $product->ID, Program::PRODUCT_META, true ) )
    );

    // remove empty programs
    $programs = $programs->filter(function ( $program ) {
      return isset( $program['program_id'] ) && absint( $program['program_id'] ) > 0;
    });

    if ( $programs->empty() ) {
      return $bool;
    }

    // true, if at least one program has a group that is not accepting enrollments
    $is_any_program_closed = $programs->some(function ( $program ) {
      if ( ! isset( $program['program_id'] ) ) {
        return;
      } // return same as continue here
      $program = new Program( $program['program_id'] );
      $groups  = Utils::collect( $program->groups() );

      // returns true if all groups are not accepting enrollments
      return $groups->every(function ( $group ) {
        return ! $group->accepting_enrollments();
      });
    });

    if ( $is_any_program_closed ) {
      return false;
    }

    return $bool;
  }

  /**
   * Adds the "CoachKit" tab to the MemberPress settings page.
   *
   * @return void
   */
  public function account_nav_tab() {
      $mepr_options     = \MeprOptions::fetch();
      $coaching_page_id = $mepr_options->coaching_page_id;

    if ( ! $coaching_page_id || empty( $coaching_page_id ) ) {
      return;
    }

     $coaching_url = AppHelper::get_coaching_url();
    ?>
    <span class="mepr-nav-item mepr-courses <?php \MeprAccountHelper::active_nav( \apply_filters( 'mpch-account-nav-coaching-active-name', 'coaching' ) ); ?>">
      <a href="<?php echo esc_url( $coaching_url ); ?>" id="coaching">
        <?php esc_html_e( 'Coaching', 'memberpress-coachkit' ); ?>
      </a>
    </span>
    <?php
  }


  /**
   * Admin Menu callback function
   *
   * @return void
   */
  public function menu() {
    self::admin_separator();
    $coach_ctrl    = CtrlFactory::fetch( 'CoachCtrl' );
    $student_ctrl  = CtrlFactory::fetch( 'StudentCtrl' );
    $activity_ctrl = CtrlFactory::fetch( 'ActivityCtrl' );

    add_menu_page(
      esc_html__( 'CoachKit', 'memberpress-coachkit' ),
      esc_html__( 'MP CoachKit™', 'memberpress-coachkit' ),
      'manage_options',
      base\PLUGIN_NAME,
      '',
      AppHelper::coaching_icon()
    );

    add_submenu_page(
      base\PLUGIN_NAME,
      esc_html__( 'Activity', 'memberpress-coachkit' ),
      esc_html__( 'Activity', 'memberpress-coachkit' ),
      'manage_options',
      base\PLUGIN_NAME,
      array( $activity_ctrl, 'listing' )
    );

    // Remove Programs submenu and re-add
    remove_submenu_page( base\PLUGIN_NAME, 'edit.php?post_type=' . Program::CPT );
    add_submenu_page(
      base\PLUGIN_NAME,
      esc_html__( 'Programs', 'memberpress-coachkit' ),
      esc_html__( 'Programs', 'memberpress-coachkit' ),
      'manage_options',
      '/edit.php?post_type=' . Program::CPT,
      ''
    );

    add_submenu_page(
      base\PLUGIN_NAME,
      AppHelper::get_label_for_coach(true),
      AppHelper::get_label_for_coach(true),
      'manage_options',
      base\PLUGIN_NAME . '-coaches',
      array( $coach_ctrl, 'listing' )
    );

    add_submenu_page(
      base\PLUGIN_NAME,
      AppHelper::get_label_for_client(true),
      AppHelper::get_label_for_client(true),
      'manage_options',
      base\PLUGIN_NAME . '-students',
      array( $student_ctrl, 'listing' )
    );

    do_action( base\SLUG_KEY . '_menu' );
  }

  public static function update_user_custom_role( $user_id ) {
    // Get the user object
    $user = get_user_by( 'ID', $user_id );
    if ( $user && in_array( Coach::ROLE, $user->roles ) ) {
      $user->add_role( Coach::ROLE );
    }

    if ( $user && in_array( Student::ROLE, $user->roles ) ) {
      $user->add_role( Student::ROLE );
    }
  }

  /**
   * Preserve Custom Roles on User Profile Update
   *
   * This function is hooked to the "profile_update" action in WordPress and is responsible
   * for preserving custom roles when a coach's or student's profile is updated.
   *
   * @param int      $user_id      The ID of the user whose profile is being updated.
   * @param \WP_User $old_user_data The user data object of the user before the update.
   * @param array    $userdata     An array of user data that is being updated.
   */
  public function preserve_custom_roles( $user_id, $old_user_data, $userdata ) {
    // Get the user's existing roles
    $existing_roles = (array) $old_user_data->roles;

    // Reassign custom roles to the user
    foreach ( $existing_roles as $role ) {
      if ( Coach::ROLE === $role || Student::ROLE === $role ) {
        $user = new \WP_User( $user_id );
        $user->add_role( $role );
      }
    }
  }

  /**
   * Register global widget on Coaching pages
   *
   * @return void
   */
  public function register_global_widget() {
    if (AppHelper::is_readylaunch()) {
      register_sidebar([
        'name'          => _x('ReadyLaunch™️ CoachKit™️ Footer', 'ui', 'memberpress-coachkit'),
        'description'   => __( 'Widgets in this area will be shown under the main content of ReadyLaunch Coaching pages.', 'memberpress-coachkit' ),
        'id'            => 'mpch_general_footer_widget',
        'before_widget' => '<div>',
        'after_widget'  => '</div>',
        'before_title'  => '<h2>',
        'after_title'   => '</h2>',
      ]);
    }
  }

  /**
   * Add a separator to the WordPress admin menus
   */
  public static function admin_separator() {
    global $menu;

    // Prevent duplicate separators when no core menu items exist
    if ( ! lib\Utils::is_user_admin() ) {
      return;
    }

    $menu[] = array( '', 'read', 'separator-' . base\PLUGIN_NAME, '', 'wp-menu-separator ' . base\PLUGIN_NAME );
  }

  /**
   * Move our custom separator above our admin menu
   *
   * @param array $menu_order Menu Order
   * @return array Modified menu order
   */
  public static function admin_menu_order( $menu_order ) {
    if ( ! $menu_order ) {
      return true;
    }

    if ( ! is_array( $menu_order ) ) {
      return $menu_order;
    }

    // Initialize our custom order array
    $new_menu_order = array();

    // Menu values
    $first_sep   = 'separator1';
    $custom_menus = [base\PLUGIN_NAME];

    // Loop through menu order and do some rearranging
    foreach ( $menu_order as $item ) {
      // Position MemberPress CoachKit™️ menu below MP Courses menu
      if ( $first_sep === $item ) {
        // Add our custom menus
        foreach ( $custom_menus as $custom_menu ) {
          if ( array_search( $custom_menu, $menu_order, true ) ) {
            $new_menu_order[] = $custom_menu;
          }
        }

        // Add the appearance separator
        $new_menu_order[] = $first_sep;

        // Skip our menu items down below
      } elseif ( ! in_array( $item, $custom_menus, true ) ) {
        $new_menu_order[] = $item;
      }
    }

    // Return our custom order
    return $new_menu_order;
  }

  public static function coaching_page_warning() {
    $mepr_options     = \MeprOptions::fetch();
    $coaching_page_id = $mepr_options->coaching_page_id;

    if ( empty( $mepr_options->coaching_page_id ) || ! $mepr_options->coaching_page_id ) {
      View::render( '/admin/notices/coaching_page_warning', get_defined_vars() );
    }
  }

  /**
   * Render CoachKit settings tab.
   *
   * @return void
   */
  public function coaching_options_tab() {
    View::render( 'admin/options/coaching-tab' );
  }

  /**
   * Render CoachKit settings page.
   *
   * @return void
   */
  public function coaching_options() {
    $options = AppHelper::get_options();

    $calendly_logo_url = base\IMAGES_URL . '/calendly-logo.png';

    View::render( 'admin/options/coaching-options', compact( 'options', 'calendly_logo_url' ) );
  }

  /**
   * Enqueue scripts
   *
   * @param string $hook hook
   * @return void
   */
  public function enqueue_admin_scripts( $hook ) {

    global $current_screen;
    $hook = 'memberpress_page_memberpress-options';
    if ( $hook === $current_screen->id ) {
      \wp_enqueue_style( base\SLUG_KEY, base\CSS_URL . '/admin.css', array( 'mpch-jquery-magnific-popup' ), base\VERSION );
      \wp_enqueue_script( base\SLUG_KEY, base\JS_URL . '/admin-shared.js', array(), true, base\VERSION );
    }
  }

  /**
   * Saves the "Courses" data after Options page is updated
   *
   * @return void
   */
  public function store_options() {
    if ( lib\Utils::is_post_request() ) {

      $values = isset($_POST['mpch-options']) ? wp_unslash($_POST['mpch-options']) : array(); // phpcs:ignore

      $options = new stdClass();

      $options->coaching_slug    = isset( $values['coaching_slug'] ) ? sanitize_text_field( $values['coaching_slug'] ) : '';
      $options->enable_messaging = isset( $values['enable_messaging'] ) ? sanitize_key( $values['enable_messaging'] ) : false;
      $options->label_for_client = isset( $values['label_for_client'] ) ? sanitize_text_field( $values['label_for_client'] ) : '';
      $options->label_for_clients = isset( $values['label_for_clients'] ) ? sanitize_text_field( $values['label_for_clients'] ) : '';
      $options->label_for_coach = isset( $values['label_for_coach'] ) ? sanitize_text_field( $values['label_for_coach'] ) : '';
      $options->label_for_coaches = isset( $values['label_for_coaches'] ) ? sanitize_text_field( $values['label_for_coaches'] ) : '';

      // Do not store empty value for these labels
      $options->label_for_client = empty($options->label_for_client) ? esc_html__('Client', 'memberpress-coaching', 'memberpress-coachkit') : $options->label_for_client;
      $options->label_for_clients = empty($options->label_for_clients) ? esc_html__('Clients', 'memberpress-coaching', 'memberpress-coachkit') : $options->label_for_clients;
      $options->label_for_coach = empty($options->label_for_coach) ? esc_html__('Coach', 'memberpress-coaching', 'memberpress-coachkit') : $options->label_for_coach;
      $options->label_for_coaches = empty($options->label_for_coaches) ? esc_html__('Clients', 'memberpress-coaching', 'memberpress-coachkit') : $options->label_for_coaches;

      update_option( 'mpch-options', $options );
    }
  }

  public static function mp_admin_header() {
    global $current_screen;
    if ( preg_match( '/^(mpch-)/', $current_screen->post_type ) || preg_match( '/memberpress-coachkit/', $current_screen->id ) ) {
      $coachkit_logo = base\IMAGES_URL . '/coachkit-logo.png';
      View::render( 'admin/common/mp-header', compact( 'coachkit_logo' ) );
    }
  }

  /********* INSTALL PLUGIN ***********/
  public function install() {
    $db = lib\Db::fetch();
    $db->upgrade();
  }

  public function render_readylaunch_settings() {
    $data = array(
      'mepr_options' => \MeprOptions::fetch()
    );
    View::render( 'admin/settings/readylaunch', $data );
  }
}
