<?php
namespace memberpress\coachkit\lib;

use memberpress\coachkit as base;

if ( ! defined( 'ABSPATH' ) ) {die( 'You are not allowed to call this page directly.' );}

// class EmailToException extends \Exception { }
// class EmailFromException extends \Exception { }

abstract class BaseEmail extends \MeprBaseEmail {
  // It's a requirement for base classes to define these
  public $title, $description, $defaults, $variables, $to, $headers, $show_form, $ui_order, $test_vars;


  public function get_stored_field( $fieldname ) {
    $mepr_options = \MeprOptions::fetch();
    $classname    = get_class( $this );

    $default = isset( $this->defaults[ $fieldname ] ) ? $this->defaults[ $fieldname ] : false;

    if ( ! isset( $mepr_options->emails[ $classname ] ) or ! isset( $mepr_options->emails[ $classname ][ $fieldname ] ) ) { return $default; }

    return $mepr_options->emails[ $classname ][ $fieldname ];
  }

  public function field_name( $field = 'enabled', $id = false ) {
    $mepr_options = \MeprOptions::fetch();
    $classname    = get_class( $this );

    if ( $id ) {
      return $mepr_options->emails_str . '-' . $this->dashed_name() . '-' . $field;
    } else {        return $mepr_options->emails_str . '[' . $classname . '][' . $field . ']';
    }
  }

  public function dashed_name() {
    $classname = get_class( $this );
    $tag       = preg_replace( '/\B([A-Z])/', '-$1', $classname );
    $tag       = str_replace( [ '\\', '/' ], '-', $tag );
    return strtolower( $tag );
  }

  public function view_name() {
    $classname = get_class( $this );
    preg_match( '/([^\\\]*)$/', $classname, $m );
    $file_name = $m[1];
    $view      = preg_replace( '/^Mepr(.*)Email$/', '$1', $file_name );
    $view      = preg_replace( '/\B([A-Z])/', '-$1', $view );
    return strtolower( $view );
  }

  /**
   * This is the most important part here to determine the content of the default email
   *
   * @param array $vars
   * @return string
   */
  public function body_partial( $vars = array() ) {
    ob_start();
    require_once base\VIEWS_PATH . '/emails/' . $this->view_name() . '.php';
    $view = ob_get_clean();
    return $view;
  }

}
