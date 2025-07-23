<?php
use memberpress\coachkit\controllers as ctrl;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' ); }

flush_rewrite_rules();
$app_ctrl = ctrl\AppCtrl::fetch();
$app_ctrl->remove_coaching_role_from_admins();
