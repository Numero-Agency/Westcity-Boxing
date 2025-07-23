<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' ); }

use memberpress\coachkit\controllers as ctrl;
/**
* Must call register_all_cpts before flush_rewrite_rules!
* Called on activation from hook: register_activation_hook
*/
$app_ctrl = ctrl\AppCtrl::fetch();
$app_ctrl->register_all_cpts();
$app_ctrl->add_user_roles();
$app_ctrl->assign_coaching_role_to_admins();
flush_rewrite_rules();
