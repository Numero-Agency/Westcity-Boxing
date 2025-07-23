<?php
/*
Plugin Name: MemberPress Elementor Content Protection
Plugin URI: https://memberpress.com/
Description: Elementor integration to protect content with MemberPress.
Version: 1.0.9
Author: Caseproof, LLC
Author URI: https://caseproof.com/
Text Domain: memberpress-elementor
Copyright: 2004-2024, Caseproof, LLC
*/

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

if(is_plugin_active('memberpress/memberpress.php')) {
  define('MPELEMENTOR_PLUGIN_SLUG', 'memberpress-elementor/memberpress-elementor.php');
  define('MPELEMENTOR_PLUGIN_NAME', 'memberpress-elementor');
  define('MPELEMENTOR_EDITION', MPELEMENTOR_PLUGIN_NAME);
  define('MPELEMENTOR_PATH', dirname(__DIR__) . '/' . MPELEMENTOR_PLUGIN_NAME);

  // Load Addon
  require_once MPELEMENTOR_PATH . '/MpElementor.php';
  new MpElementor;
}
