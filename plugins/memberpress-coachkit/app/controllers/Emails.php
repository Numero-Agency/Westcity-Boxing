<?php
namespace memberpress\coachkit\controllers;

if ( ! defined( 'ABSPATH' ) ) {die( 'You are not allowed to call this page directly.' );}

use memberpress\coachkit as base;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models as models;
use memberpress\coachkit\helpers as helpers;
use memberpress\coachkit\emails\ProgramHabitEmail;
use memberpress\coachkit\emails\EnrollmentCapEmail;
use memberpress\coachkit\emails\ProgramStartedEmail;
use memberpress\coachkit\emails\MessageReceivedEmail;
use memberpress\coachkit\emails\ProgramCompletedEmail;
use memberpress\coachkit\emails\ProgramMilestoneEmail;

class Emails extends lib\BaseCtrl {
  public function load_hooks() {
    add_filter( 'mepr_display_emails', array( $this, 'add_emails' ), 10, 3 );
  }

  /**
   * @param mixed $vars
   *
   * @return [type]
   */
  public function add_email_vars( $vars ) {
    $vars[] = 'gift_coupon';
    $vars[] = 'gift_note';
    $vars[] = 'gift_url';

    return $vars;
  }

  /**
   * Add email params to transaction
   *
   * @param mixed $params
   * @param mixed $txn
   *
   * @return [type]
   */
  public function add_email_params( $params, $txn ) {
    $product = $txn->product();
    // $program
    $params['gifter_name'] = isset( $_POST['gifter_name'] ) && ! empty( $_POST['gifter_name'] ) ? sanitize_text_field( $_POST['gifter_name'] ) : '';
    // $params['giftee_name']  = isset( $_POST['giftee_name'] ) && ! empty( $_POST['giftee_name'] ) ? sanitize_text_field( $_POST['giftee_name'] ) : '';
    // $params['giftee_email'] = isset( $_POST['giftee_email'] ) && is_email( $_POST['giftee_email'] ) ? sanitize_email( $_POST['giftee_email'] ) : '';
    // $params['gift_note']    = isset( $_POST['gift_note'] ) && ! empty( $_POST['gift_note'] ) ? \sprintf( '<div style="margin-bottom: 20px;"><em>%s</em></div>', \wp_kses_post( \stripslashes( \wpautop( $_POST['gift_note'] ) ) ) ) : '';
    // $params['gift_coupon']  = $gift->get_coupon();
    // $params['gift_url']     = $gift->claim_url();

    return $params;
  }

  /**
   * @return string
   */
  public static function set_email_defaults() {
    check_ajax_referer( 'set_email_defaults', 'set_email_defaults_nonce' );

    if ( ! \MeprUtils::is_mepr_admin() ) {
      die( __( 'You do not have access.', 'memberpress-coachkit' ) );
    }

    if ( ! isset( $_POST['e'] ) ) {
      die( __( 'Email couldn\'t be set to default', 'memberpress-coachkit' ) );
    }

    if ( ! isset( $_POST['a'] ) ) { $_POST['a'] = array(); }

    try {
      $email = lib\EmailFactory::fetch( $_POST['e'], 'BaseEmail', $_POST['a'] );
    } catch ( lib\InvalidEmailException $e ) {
      die( json_encode( array( 'error' => $e->getMessage() ) ) );
    }

    die( json_encode( array(
      'subject' => $email->default_subject(),
      'body'    => $email->default_body(),
    ) ) );
  }



  public static function enqueue_scripts( $hook ) {
    global $current_screen;
    if ( $hook == 'memberpress_page_memberpress-options' || $current_screen->post_type == models\Reminder::$cpt ) {
      wp_enqueue_script( 'mpgft-emails', base\JS_URL . '/admin-emails.js', array(), base\VERSION, true );
    }
  }


  public function add_emails( $emails, $etype, $args ) {
    $coaching_emals = array();

    if ( 'MeprBaseOptionsAdminEmail' === $etype ) {
      $coaching_emals[ EnrollmentCapEmail::class ]    = new EnrollmentCapEmail();
      $coaching_emals[ ProgramStartedEmail::class ]   = new ProgramStartedEmail();
      $coaching_emals[ ProgramCompletedEmail::class ] = new ProgramCompletedEmail();
    }

    if ( 'MeprBaseOptionsUserEmail' === $etype ) {
      $coaching_emals[ ProgramHabitEmail::class ]     = new ProgramHabitEmail();
      $coaching_emals[ ProgramMilestoneEmail::class ] = new ProgramMilestoneEmail();
      $coaching_emals[ MessageReceivedEmail::class ]  = new MessageReceivedEmail();
    }
    $emails = array_merge( $emails, $coaching_emals );

    return $emails;
  }


  public static function send_test_email() {
    check_ajax_referer( 'send_test_email', 'send_test_email_nonce' );

    $mepr_options = \MeprOptions::fetch();

    if ( ! \MeprUtils::is_mepr_admin() ) {
      die( __( 'You do not have access to send a test email.', 'memberpress-coachkit' ) );
    }

    if ( ! isset( $_POST['e'] ) or ! isset( $_POST['s'] ) or ! isset( $_POST['b'] ) or ! isset( $_POST['t'] ) ) {
      die( __( 'Can\'t send your email ... refresh the page and try it again.', 'memberpress-coachkit' ) );
    }

    if ( ! isset( $_POST['a'] ) ) { $_POST['a'] = array(); }

    try {
      $email = lib\EmailFactory::fetch( $_POST['e'], 'BaseEmail', $_POST['a'] );
    } catch ( lib\InvalidEmailException $e ) {
      die( json_encode( array( 'error' => $e->getMessage() ) ) );
    }

    $email->to = $mepr_options->admin_email_addresses;

    $amount     = preg_replace( '~\$~', '\\\$',
      sprintf( '%s' . \MeprUtils::format_float( 15.15 ),
      stripslashes( $mepr_options->currency_symbol ) ) );
    $subtotal   = preg_replace( '~\$~', '\\\$',
      sprintf( '%s' . \MeprUtils::format_float( 15.00 ),
      stripslashes( $mepr_options->currency_symbol ) ) );
    $tax_amount = preg_replace( '~\$~', '\\\$',
      sprintf( '%s' . \MeprUtils::format_float( 0.15 ),
      stripslashes( $mepr_options->currency_symbol ) ) );

    $params = array_merge(
      array(
        'user_id'                   => 481,
        'user_login'                => 'johndoe',
        'username'                  => 'johndoe',
        'user_email'                => 'johndoe@example.com',
        'user_first_name'           => __( 'John', 'memberpress-coachkit' ),
        'user_last_name'            => __( 'Doe', 'memberpress-coachkit' ),
        'user_full_name'            => __( 'John Doe', 'memberpress-coachkit' ),
        'user_address'              => '<br/>' .
                         __( '111 Cool Avenue', 'memberpress-coachkit' ) . '<br/>' .
                         __( 'New York, NY 10005', 'memberpress-coachkit' ) . '<br/>' .
                         __( 'United States', 'memberpress-coachkit' ) . '<br/>',
        'usermeta:(.*)'             => __( 'User Meta Field: $1', 'memberpress-coachkit' ),
        'membership_type'           => __( 'Bronze Edition', 'memberpress-coachkit' ),
        'signup_url'                => home_url(),
        'product_name'              => __( 'Bronze Edition', 'memberpress-coachkit' ),
        'invoice_num'               => 718,
        'trans_num'                 => '9i8h7g6f5e',
        'trans_date'                => \MeprAppHelper::format_date( gmdate( 'c', time() ) ),
        'trans_expires_at'          => \MeprAppHelper::format_date( gmdate( 'c', time() + \MeprUtils::months( 1 ) ) ),
        'trans_gateway'             => __( 'Credit Card (Stripe)', 'memberpress-coachkit' ),
        'user_remote_addr'          => $_SERVER['REMOTE_ADDR'],
        'payment_amount'            => $amount,
        'subscr_num'                => '1a2b3c4d5e',
        'subscr_date'               => \MeprAppHelper::format_date( gmdate( 'c', time() ) ),
        'subscr_next_billing_at'    => \MeprAppHelper::format_date( gmdate( 'c', time() + \MeprUtils::months( 1 ) ) ),
        'subscr_expires_at'         => \MeprAppHelper::format_date( gmdate( 'c', time() + \MeprUtils::months( 1 ) ) ),
        'subscr_gateway'            => __( 'Credit Card (Stripe)', 'memberpress-coachkit' ),
        'subscr_terms'              => sprintf( __( '%s / month', 'memberpress-coachkit' ), $amount ),
        'subscr_cc_num'             => \MeprUtils::cc_num( '6710' ),
        'subscr_cc_month_exp'       => gmdate( 'm' ),
        'subscr_cc_year_exp'        => ( gmdate( 'Y' ) + 2 ),
        'subscr_update_url'         => $mepr_options->login_page_url(),
        'subscr_upgrade_url'        => $mepr_options->login_page_url(),
        'subscr_renew_url'          => $mepr_options->account_page_url() . '?action=subscriptions',
        'reminder_id'               => 28,
        'reminder_trigger_length'   => 2,
        'reminder_trigger_interval' => 'days',
        'reminder_trigger_timing'   => 'before',
        'reminder_trigger_event'    => 'sub-expires',
        'reminder_name'             => __( 'Subscription Expiring', 'memberpress-coachkit' ),
        'reminder_description'      => __( 'Subscription Expiring in 2 Days', 'memberpress-coachkit' ),
        'blog_name'                 => get_bloginfo( 'name' ),
        'payment_subtotal'          => $subtotal,
        'tax_rate'                  => '10%',
        'tax_amount'                => $tax_amount,
        'tax_desc'                  => __( 'Tax', 'memberpress-coachkit' ),
        'business_name'             => $mepr_options->attr( 'biz_name' ),
        'biz_name'                  => $mepr_options->attr( 'biz_name' ),
        'biz_address1'              => $mepr_options->attr( 'biz_address1' ),
        'biz_address2'              => $mepr_options->attr( 'biz_address2' ),
        'biz_city'                  => $mepr_options->attr( 'biz_city' ),
        'biz_state'                 => $mepr_options->attr( 'biz_state' ),
        'biz_postcode'              => $mepr_options->attr( 'biz_postcode' ),
        'biz_country'               => $mepr_options->attr( 'biz_country' ),
        'login_page'                => $mepr_options->login_page_url(),
        'account_url'               => $mepr_options->account_page_url(),
        'login_url'                 => $mepr_options->login_page_url(),
      ),
      $email->test_vars
    );

    $use_template = ( $_POST['t'] == 'true' );
    $email->send( $params, sanitize_text_field( wp_unslash( $_POST['s'] ) ), wp_kses_post( wp_unslash( $_POST['b'] ) ), $use_template );

    die( json_encode( array( 'message' => __( 'Your test email was successfully sent.', 'memberpress-coachkit' ) ) ) );
  }

}
