<?php

namespace memberpress\coachkit\controllers;

use memberpress\coachkit as base;
use memberpress\coachkit\lib\Utils;
use memberpress\coachkit\lib as lib;
use memberpress\coachkit\models\Room;
use memberpress\coachkit\models\Group;
use memberpress\coachkit\models\Message;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\RoomParticipants;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}


/**
 * App Controller class
 */
class MessageCtrl extends lib\BaseCtrl {


  /**
   * Load class hooks
   *
   * @return void
   */
  public function load_hooks() {
    add_action( 'wp_ajax_create_room', array( $this, 'handle_create_room' ) );
    add_action( 'wp_ajax_send_message', array( $this, 'handle_send_message' ) );
    add_action( 'wp_ajax_load_more', array( $this, 'handle_load_more' ) );
    add_action( 'wp_ajax_get_room_messages', array( $this, 'handle_get_room_messages' ) );
    add_action( 'wp_ajax_async_upload', array( $this, 'handle_async_upload' ) );
    add_action( 'wp_ajax_async_delete', array( $this, 'handle_async_delete' ) );

    add_action( 'mpch_enrollment_created', array( $this, 'add_user_to_message_room' ) );
  }

  /**
   * Gets new messages for room
   */
  public function handle_get_room_messages() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_message' . get_current_user_id(), 'security' );

    $current_user_id = get_current_user_id();

    $rooms               = Room::get_rooms_for_user( $current_user_id );
    $rooms_with_messages = Room::get_rooms_with_messages( $rooms );

    wp_send_json_success( $rooms_with_messages );
  }

  /**
   * Posts new message
   */
  public function handle_send_message() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_message' . get_current_user_id(), 'security' );

    $attachments     = isset( $_POST['attachments'] ) ? wp_unslash( lib\Validate::sanitize( $_POST['attachments'] ) ) : array();
    $text            = isset( $_POST['message'] ) ? wp_unslash( wp_kses_post( $_POST['message'] ) ) : '';
    $room_id         = isset( $_POST['room_id'] ) ? wp_unslash( absint( $_POST['room_id'] ) ) : null;
    $room_uuid       = isset( $_POST['uuid'] ) ? wp_unslash( sanitize_text_field( $_POST['uuid'] ) ) : null;
    $current_user_id = get_current_user_id();

    // If participants cannot chat, exit
    if ( null == RoomParticipants::is_user_in_room( $room_id, $current_user_id ) ) {
      wp_send_json_error( esc_html__( 'Sorry you cannot post messages in this room', 'memberpress-coachkit' ) );
    }

    $db = lib\Db::fetch();

    $message             = new Message();
    $message->sender_id  = $current_user_id;
    $message->room_id    = $room_id;
    $message->message    = $text;
    $message->created_at = Utils::ts_to_mysql_date( time() );
    $message_id          = $message->store();

    // $sender = new \MeprUser( $message->sender_id );
    // $message = (object) $message->get_values();
    // $message->sender = $sender->full_name();

    if ( ! is_wp_error( $message_id ) && is_numeric( $message_id ) ) {
      foreach ( $attachments as $attachment ) {
        $db->update_record( $db->message_attachments, $attachment['id'], [ 'message_id' => $message_id ] );
      }

      $participants = RoomParticipants::get_all( '', '', [ 'room_id' => $room_id ] );
      Utils::send_message_received_notice( $current_user_id, $participants, $room_uuid );
    }

    wp_send_json_success( $message->get_values() );
  }

  public function handle_load_more() {
    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_message' . get_current_user_id(), 'security' );

    $room_id = isset( $_POST['room_id'] ) ? wp_unslash( absint( $_POST['room_id'] ) ) : null;
    $offset  = isset( $_POST['offset'] ) ? wp_unslash( absint( $_POST['offset'] ) ) : null;

    $conversations = Message::get_room_conversations( $room_id, $offset );
    foreach ( $conversations['messages'] as &$conversation ) {
      if ( $conversation->attachments ) {
        $attachments_array = array_map(
          function ( $attachment ) {
            list($path, $url, $type) = array_map( 'trim', explode( '|', $attachment ) );
            return [
              'path' => $path,
              'url'  => $url,
              'type' => $type,
            ];
          },
          explode( "\n", $conversation->attachments )
        );

        $conversation->attachments = $attachments_array;
      } else {
        $conversation->attachments = null;
      }
    }

    wp_send_json_success( array(
      'conversations' => $conversations['messages'],
      'count'         => $conversations['total_count'],
    ));
  }

  /**
   * Create new room and fetches all rooms
   */
  public function handle_create_room() {
    $current_user_id = get_current_user_id();

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_message' . $current_user_id, 'security' );

    $type = isset( $_POST['type'] ) ? wp_unslash( sanitize_text_field( $_POST['type'] ) ) : '';
    $participants = isset( $_POST['participants'] ) ? wp_unslash( lib\Validate::sanitize( $_POST['participants'] ) ) : '';
    $participants = AppHelper::collect( $participants );

    if ( empty( $participants ) ) {
      wp_send_json_error([
        'message' => esc_html__( 'No contact selected', 'memberpress-coachkit' ),
      ]);
    }

    if('group' === $type){
      $group_id = $participants->first();

      // Is this the group's coach
      $group = new Group( $group_id );
      if($group->coach_id != $current_user_id){
        wp_send_json_error([
          'message' => esc_html__( 'Sorry you cannot create room', 'memberpress-coachkit' ),
        ]);
      }

      // Does room exists
      $room_id = Room::room_exists_for_group( $group_id );
      if( is_numeric($room_id) ){
        wp_send_json_error([
          'message' => esc_html__( 'Room exists already', 'memberpress-coachkit' ),
        ]);
      }

      $room_id = Room::get_next_room_id( 'group', $group_id );
      $participants = Utils::collect($group->get_enrollments())->pluck('student_id');
      $participants->add( $current_user_id ); // add the coach
    }
    else{
      // Check if this participant is messageable
      $contacts                     = Utils::collect( Message::get_recipient_list( $current_user_id ) );
      $all_participants_in_contacts = $participants->every(function ( $participant_id ) use ( $contacts ) {
        return $contacts->filter(function ($contact) {
          return 'group' !== $contact->type;
        })->contains( 'id', $participant_id );
      });

      if ( ! $all_participants_in_contacts ) {
        wp_send_json_error([
          'message' => esc_html__( 'One or more participants are not available for messaging', 'memberpress-coachkit' ),
        ]);
      }

      // Check if participants have a room already
      $participants->add( $current_user_id );
      $room_id = Room::room_exists_for_participants( $participants->all() );

      if ( is_numeric( $room_id ) ) {
        wp_send_json_error([
          'message' => esc_html__( 'Room exists already', 'memberpress-coachkit' ),
        ]);
      }

      $room_id = Room::get_next_room_id();
    }

    // Add user to room
    $participants->each(function ( $participant ) use ( $room_id ) {
      $rp          = new RoomParticipants();
      $rp->user_id = $participant;
      $rp->room_id = $room_id;
      $rp->store();
    });

    // Now fetch all rooms, transform and return
    $rooms               = Room::get_rooms_for_user( $current_user_id );
    $rooms_with_messages = Room::get_rooms_with_messages( $rooms );

    wp_send_json_success([
      'room_id' => $room_id,
      'data'    => $rooms_with_messages,
      'message' => esc_html__( 'Room created', 'memberpress-coachkit' ),
    ]);
  }


  /**
   * Async upload of files to wp uploads folder
   */
  public function handle_async_upload() {
    $current_user_id = get_current_user_id();

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_message' . $current_user_id, 'security' );

    $db           = lib\Db::fetch();
    $uploadedfile = $_FILES['message_attachments'];

    // Check the maximum file size in bytes (for example, 5MB)
    $max_file_size      = 10 * 1024 * 1024; // 5MB, site ownersh should be able to change this
    $uploaded_file_size = $uploadedfile['size'];

    // Check if the uploaded file size exceeds the maximum allowed size
    if ( $uploaded_file_size > $max_file_size ) {
      wp_send_json_error( esc_html__( 'File size exceeds the maximum allowed size', 'memberpress-coachkit' ), 403 );
    }

    // Check if this user can post to this room
    $room_id = isset( $_POST['room_id'] ) ? wp_unslash( absint( $_POST['room_id'] ) ) : null;
    if ( null == RoomParticipants::is_user_in_room( $room_id, $current_user_id ) ) {
      wp_send_json_error( esc_html__( 'Sorry you cannot post messages in this room', 'memberpress-coachkit' ), 403 );
    }

    $upload_overrides = array(
      'test_form' => false,
      'mimes'     => AppHelper::message_mime_type(),
    );
    $uploaded_file    = wp_handle_upload( $uploadedfile, $upload_overrides );

    // Hand success or error
    if ( $uploaded_file && ! isset( $uploaded_file['error'] ) ) {

      $upload_dir    = wp_get_upload_dir();
      $attachment_id = $db->create_record( $db->message_attachments, [
        'sender_id'  => $current_user_id,
        'path'       => str_replace( $upload_dir['basedir'], '', $uploaded_file['file'] ),
        'url'        => str_replace( $upload_dir['baseurl'], '', $uploaded_file['url'] ),
        'type'       => $uploaded_file['type'],
        // 'attachment' => wp_json_encode( $uploaded_file ),
      ] );

      if ( ! $attachment_id ) {
        wp_send_json_error( 'Error', 403 );
      }

      wp_send_json_success( [
        'id'   => $attachment_id,
        'url'  => str_replace( $upload_dir['baseurl'], '', $uploaded_file['url'] ),
        'path' => str_replace( $upload_dir['basedir'], '', $uploaded_file['file'] ),
        'type' => $uploaded_file['type'],
      ] );
    } else {
      /*
      * Error generated by _wp_handle_upload()
      * @see _wp_handle_upload() in wp-admin/includes/file.php
      */
      wp_send_json_error( $uploaded_file['error'], 403 );
    }
  }

  /**
   * Async delete of files from wp uploads folder
   *
   * @return string // phpcs:ignore Squiz.Commenting.FunctionComment.InvalidNoReturn
   */
  public function handle_async_delete() {
    $current_user_id = get_current_user_id();

    // Verify nonce
    check_ajax_referer( base\SLUG_KEY . '_message' . $current_user_id, 'security' );

    $db = lib\Db::fetch();

    // Check if this user can post to this room
    $id      = isset( $_POST['file'] ) ? wp_unslash( absint( $_POST['file'] ) ) : null;
    $room_id = isset( $_POST['room_id'] ) ? wp_unslash( absint( $_POST['room_id'] ) ) : null;
    if ( null == RoomParticipants::is_user_in_room( $room_id, $current_user_id ) ) {
      wp_send_json_error( esc_html__( 'Sorry you cannot post messages in this room', 'memberpress-coachkit' ), 403 );
    }

    // Find attachment posted by the user
    $attachment = $db->get_col( $db->message_attachments, 'attachment', [
      'sender_id' => $current_user_id,
      'id'        => $id,
    ] );

    if ( is_wp_error( $attachment ) || ! is_array( $attachment ) || ! $attachment ) {
      wp_send_json_error( esc_html__( 'Error getting attachment', 'memberpress-coachkit' ), 403 );
    }

    $attachment = json_decode( $attachment[0] );

    if ( ! is_object( $attachment ) || ! $attachment->file ) {
      wp_send_json_error( esc_html__( 'Error getting attachment', 'memberpress-coachkit' ), 403 );
    }

    if ( ! file_exists( $attachment->file ) ) {
      wp_send_json_error( esc_html__( 'Error getting attachment', 'memberpress-coachkit' ), 403 );
    }

    // Delete file
    unlink( $attachment->file );

    // Delete row
    $attachment = $db->delete_records( $db->message_attachments, [
      'sender_id' => $current_user_id,
      'id'        => $id,
    ] );

    wp_send_json_success();
  }

  /**
   * Add student to message room after enrollment
   *
   * @param Enrollment $enrollment
   * @return void
   */
  public function add_user_to_message_room( $enrollment ) {
    $room_id = Room::room_exists_for_group( $enrollment->group_id );
    if( ! is_numeric($room_id) ){
      return;
    }

    // is student already in room?
    $already_added = RoomParticipants::is_user_in_room($room_id, $enrollment->student_id);
    if($already_added){ return; }

    $rp = new RoomParticipants();
    $rp->user_id = $enrollment->student_id;
    $rp->room_id = $room_id;
    $rp->store();
  }

}
