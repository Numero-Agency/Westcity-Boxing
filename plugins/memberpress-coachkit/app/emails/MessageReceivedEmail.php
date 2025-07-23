<?php
namespace memberpress\coachkit\emails;

use memberpress\coachkit\lib\BaseEmail;
use memberpress\coachkit\helpers\AppHelper;

if ( ! defined( 'ABSPATH' ) ) {die( 'You are not allowed to call this page directly.' );}

/**
 * EnrollmentCapEmail class for sending claim gift emails.
 */
class MessageReceivedEmail extends BaseEmail {

  /**
   * Set the default enabled, title, subject & body.
   *
   * @param array $args Optional arguments.
   */
  public function set_defaults( $args = array() ) {
    $this->title       = __( '<b>Direct Message Received</b> Notice', 'memberpress-coachkit' );
    $this->description = __( 'This email is sent to a user when a new direct message (DM) is received .', 'memberpress-coachkit' );
    $this->ui_order    = 1;

    $enabled         = true;
    $use_template    = true;
    $this->show_form = true;

    // $mepr_options = \MeprOptions::fetch();
    // $this->to     = $mepr_options->admin_email_addresses;

    $subject = __( '** New Direct Message Received', 'memberpress-coachkit' );
    $body    = $this->body_partial();

    $this->defaults  = compact( 'enabled', 'subject', 'body', 'use_template' );
    $this->variables = AppHelper::get_enrollment_email_vars();

    $this->test_vars = array(
      'sender_name' => __( 'John Doe', 'memberpress-coachkit' ),
      'message_url' => home_url(),
    );
  }
}
