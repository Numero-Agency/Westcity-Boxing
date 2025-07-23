<?php
namespace memberpress\coachkit\emails;

use memberpress\coachkit\lib\BaseEmail;
use memberpress\coachkit\helpers\AppHelper;

if ( ! defined( 'ABSPATH' ) ) {die( 'You are not allowed to call this page directly.' );}

class ProgramHabitEmail extends BaseEmail {
  /**
   * Set the default enabled, title, subject & body
   *
   * @param array $args
   * @return void
   */
  public function set_defaults( $args = array() ) {
    $this->title       = __( '<b>Habit Check-in</b> Notice', 'memberpress-coachkit' );
    $this->description = __( 'This email is sent to the user when triggered.', 'memberpress-coachkit' );
    $this->ui_order    = 0;
    $this->show_form   = true;

    $enabled      = true;
    $use_template = true;

    $subject = __( '**  Complete Your Habit Check-in: {$title}', 'memberpress-coachkit' );
    $body    = $this->body_partial();

    $this->defaults  = compact( 'enabled', 'subject', 'body', 'use_template' );
    $this->variables = AppHelper::get_checkin_email_vars();

    $this->test_vars = array(
      'title'                => __( 'Daily Expense Tracking', 'memberpress-coachkit' ),
      'checkin_question'     => __( 'Have you tracked expenses today?', 'memberpress-coachkit' ),
      'program_title'        => __( 'Financial Freedom Blueprint', 'memberpress-coachkit' ),
      'checkin_complete_url' => home_url(),
    );
  }
}
