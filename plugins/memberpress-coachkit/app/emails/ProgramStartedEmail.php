<?php
namespace memberpress\coachkit\emails;

use memberpress\coachkit as base;
use memberpress\coachkit\lib\View;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\lib\BaseEmail;
use memberpress\coachkit\helpers\AppHelper;

if ( ! defined( 'ABSPATH' ) ) {die( 'You are not allowed to call this page directly.' );}

/**
 * ProgramStartedEmail class for sending claim gift emails.
 */
class ProgramStartedEmail extends BaseEmail {

  /**
   * Set the default enabled, title, subject & body.
   *
   * @param array $args Optional arguments.
   */
  public function set_defaults( $args = array() ) {
    $this->title       = __( '<b>Program Started</b> Notice', 'memberpress-coachkit' );
    $this->description = __( 'This email is sent when a user enrolls in a program.', 'memberpress-coachkit' );
    $this->ui_order    = 1;

    $enabled         = true;
    $use_template    = true;
    $this->show_form = true;

    $mepr_options = \MeprOptions::fetch();
    $this->to     = $mepr_options->admin_email_addresses;

    $subject = __( '** Program Started: {$program_name}', 'memberpress-coachkit' );
    $body    = $this->body_partial();

    $this->defaults  = compact( 'enabled', 'subject', 'body', 'use_template' );
    $this->variables = AppHelper::get_enrollment_email_vars();

    $this->test_vars = array(
      'program_name'     => __( 'Financial Freedom Blueprint', 'memberpress-coachkit' ),
      'membership_name'  => __( 'Gold Membership', 'memberpress-coachkit' ),
      'enrollee'         => __( 'John Doe', 'memberpress-coachkit' ),
      'edit_program_url' => home_url(),
    );
  }
}
