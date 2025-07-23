<?php
/*
Plugin Name: MemberPress CoachKit
Plugin URI: http://www.memberpress.com/
Description: Coaching features for MemberPress.
Version: 1.0.12
Author: Caseproof, LLC
Author URI: http://caseproof.com/
Requires at least: 5.9
Requires PHP: 7.2
Text Domain: memberpress-coachkit
Copyright: 2004-2022, Caseproof, LLC
*/

namespace memberpress\coachkit;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' ); }

/**
 * Returns current plugin version.
 *
 * @return string Plugin version
 */
function plugin_info($field) {
  static $plugin_folder, $plugin_file;

  if(!isset($plugin_folder) or !isset($plugin_file)) {
    if(!function_exists('get_plugins')) {
      require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    }

    $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
    $plugin_file = basename((__FILE__));
  }

  if(isset($plugin_folder[$plugin_file][$field])) {
    return $plugin_folder[$plugin_file][$field];
  }

  return '';
}

// Plugin Information from the plugin header declaration
define(__NAMESPACE__ . '\ROOT_NAMESPACE', __NAMESPACE__);
define(__NAMESPACE__ . '\VERSION', plugin_info('Version'));
define(__NAMESPACE__ . '\DISPLAY_NAME', plugin_info('Name'));
define(__NAMESPACE__ . '\AUTHOR', plugin_info('Author'));
define(__NAMESPACE__ . '\AUTHOR_URI', plugin_info('AuthorURI'));
define(__NAMESPACE__ . '\DESCRIPTION', plugin_info('Description'));

use \memberpress\coachkit\lib as lib,
    \memberpress\coachkit\controllers as ctrl;

if((defined('TESTS_RUNNING') && TESTS_RUNNING) || is_plugin_active('memberpress/memberpress.php')) {
  define(__NAMESPACE__ . '\CTRLS_NAMESPACE', __NAMESPACE__ . '\controllers');
  define(__NAMESPACE__ . '\ADMIN_CTRLS_NAMESPACE', __NAMESPACE__ . '\controllers\admin');
  define(__NAMESPACE__ . '\HELPERS_NAMESPACE', __NAMESPACE__ . '\helpers');
  define(__NAMESPACE__ . '\MODELS_NAMESPACE', __NAMESPACE__ . '\models');
  define(__NAMESPACE__ . '\EMAILS_NAMESPACE', __NAMESPACE__ . '\emails');
  define(__NAMESPACE__ . '\LIB_NAMESPACE', __NAMESPACE__ . '\lib');
  define(__NAMESPACE__ . '\PLUGIN_SLUG', 'memberpress-coachkit/main.php');
  define(__NAMESPACE__ . '\PLUGIN_NAME', 'memberpress-coachkit');
  define(__NAMESPACE__ . '\SLUG_KEY', 'mpch');
  define(__NAMESPACE__ . '\EDITION', PLUGIN_NAME);
  define(__NAMESPACE__ . '\PATH', dirname(__DIR__) . '/' . PLUGIN_NAME);
  define(__NAMESPACE__ . '\CTRLS_PATH', PATH . '/app/controllers');
  define(__NAMESPACE__ . '\ADMIN_CTRLS_PATH', PATH . '/app/controllers/admin');
  define(__NAMESPACE__ . '\HELPERS_PATH', PATH . '/app/helpers');
  define(__NAMESPACE__ . '\MODELS_PATH', PATH . '/app/models');
  define(__NAMESPACE__ . '\EMAILS_PATH', PATH . '/app/emails');
  define(__NAMESPACE__ . '\LIB_PATH', PATH . '/app/lib');
  define(__NAMESPACE__ . '\CONFIG_PATH', PATH . '/app/config');
  define(__NAMESPACE__ . '\VIEWS_PATH', PATH . '/app/views');
  define(__NAMESPACE__ . '\IMAGES_PATH', PATH . '/public/images');
  define(__NAMESPACE__ . '\URL', plugins_url('/' . PLUGIN_NAME));
  define(__NAMESPACE__ . '\JS_URL', URL . '/public/js');
  define(__NAMESPACE__ . '\CSS_URL', URL . '/public/css');
  define(__NAMESPACE__ . '\IMAGES_URL', URL . '/public/images');
  define(__NAMESPACE__ . '\FONTS_URL', URL . '/public/fonts');
  define(__NAMESPACE__ . '\DB_VERSION', 3.1);

  // Autoload all the requisite classes
  function autoloader($class_name) {
    // Only load classes belonging to this plugin.
    if(0 === strpos($class_name, __NAMESPACE__)) {
      preg_match('/([^\\\]*)$/', $class_name, $m);

      $file_name = $m[1];
      $filepath = '';

      if(0 === strpos($class_name, LIB_NAMESPACE . '\Base/')) {
        $filepath = LIB_PATH . "/{$file_name}.php";
      }
      else if(0 === strpos($class_name, ADMIN_CTRLS_NAMESPACE)) {
        $filepath = ADMIN_CTRLS_PATH . "/{$file_name}.php";
      }
      else if(0 === strpos($class_name, CTRLS_NAMESPACE)) {
        $filepath = CTRLS_PATH . "/{$file_name}.php";
      }
      else if(0 === strpos($class_name, HELPERS_NAMESPACE)) {
        $filepath = HELPERS_PATH . "/{$file_name}.php";
      }
      else if(preg_match('/' . preg_quote(LIB_NAMESPACE) . '\\\.*Exception/', $class_name)) {
        $filepath = LIB_PATH . "/Exception.php";
      }
      else if(0 === strpos($class_name, MODELS_NAMESPACE)) {
        $filepath = MODELS_PATH . "/{$file_name}.php";
      }
      else if(0 === strpos($class_name, EMAILS_NAMESPACE)) {
        $filepath = EMAILS_PATH . "/{$file_name}.php";
      }
      else if(0 === strpos($class_name, LIB_NAMESPACE)) {
        $filepath = LIB_PATH . "/{$file_name}.php";
      }

      if(file_exists($filepath)) {
        require_once($filepath);
      }
    }
  }

  // if __autoload is active, put it on the spl_autoload stack
  if( is_array(spl_autoload_functions()) &&
      in_array('__autoload', spl_autoload_functions())) {
    spl_autoload_register('__autoload');
  }

  // Add functions
  if( file_exists(LIB_PATH . "/functions.php") ){
    require_once(LIB_PATH . "/functions.php");
  }

  // Add the autoloader
  spl_autoload_register(__NAMESPACE__ . '\autoloader');

  // Instansiate Ctrls
  lib\CtrlFactory::all();

  // Setup screens
  ctrl\AppCtrl::setup_menus();

  register_activation_hook(PLUGIN_SLUG, function() { require_once(LIB_PATH . "/activation.php"); });
  register_deactivation_hook(PLUGIN_SLUG, function() { require_once(LIB_PATH . "/deactivation.php"); });
}
