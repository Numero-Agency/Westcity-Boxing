<?php
namespace memberpress\coachkit\emails;

use memberpress\coachkit\lib\BaseEmail;
use memberpress\coachkit\helpers\AppHelper;

if ( ! defined( 'ABSPATH' ) ) {die( 'You are not allowed to call this page directly.' );}

/**
 * EnrollmentCapEmail class for sending claim gift emails.
 */
class ProgramCompletedEmail extends BaseEmail {

  /**
   * Set the default enabled, title, subject & body.
   *
   * @param array $args Optional arguments.
   */
  public function set_defaults( $args = array() ) {
    $this->title       = __( '<b>Program Completed</b> Notice', 'memberpress-coachkit' );
    $this->description = __( 'This email is sent when a user completes a program.', 'memberpress-coachkit' );
    $this->ui_order    = 1;

    $enabled         = true;
    $use_template    = true;
    $this->show_form = true;

    $mepr_options = \MeprOptions::fetch();
    $this->to     = $mepr_options->admin_email_addresses;

    $subject = __( '** Program Completed: {$program_name}', 'memberpress-coachkit' );
    $body    = $this->body_partial();

    $this->defaults  = compact( 'enabled', 'subject', 'body', 'use_template' );
    $this->variables = AppHelper::get_enrollment_email_vars();

    $this->test_vars = array(
      'program_name'          => __( 'Financial Freedom Blueprint', 'memberpress-coachkit' ),
      'membership_name'       => __( 'Gold Membership', 'memberpress-coachkit' ),
      'remaining_enrollments' => 3,
      'edit_program_url'      => home_url(),
    );
  }
}
