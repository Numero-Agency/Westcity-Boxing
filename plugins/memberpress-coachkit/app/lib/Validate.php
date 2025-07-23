<?php
namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

use memberpress\courses as base;

class Validate {
  public static function not_null( $var, $field = '' ) {
    if ( is_null( $var ) ) {
      throw new ValidationException( sprintf( __( '%s must not be empty', 'memberpress-coachkit' ), ucfirst( $field ) ) );
    }
  }

  public static function not_empty( $var, $field = '' ) {
    if ( $var === '' || $var === '0' || $var === 0 || $var === false ) {
      throw new ValidationException( sprintf( __( '%s must not be empty', 'memberpress-coachkit' ), ucfirst( $field ) ) );
    }
  }

  public static function not_empty_string( $var, $field = '' ) {
    if ( ! is_string( $var ) || $var === '' ) {
      throw new ValidationException( sprintf( __( '%s must not be empty', 'memberpress-coachkit' ), ucfirst( $field ) ) );
    }
  }

  public static function is_bool( $var, $field = '' ) {
    if ( ! is_bool( $var ) && $var != 0 && $var != 1 ) {
      throw new ValidationException( sprintf( __( '%s must be true or false', 'memberpress-coachkit' ), ucfirst( $field ) ) );
    }
  }

  public static function is_array( $var, $field = '' ) {
    if ( ! is_array( $var ) ) {
      throw new ValidationException( sprintf( __( '%s must be an array', 'memberpress-coachkit' ), ucfirst( $field ) ) );
    }
  }

  public static function is_in_array( $var, $lookup, $field = '' ) {
    if ( is_array( $lookup ) && ! in_array( $var, $lookup ) ) {
      throw new ValidationException( sprintf( __( '%1$s must be %2$s NOT %3$s', 'memberpress-coachkit' ), $field, implode( ' ' . __( 'or', 'memberpress-coachkit' ) . ' ', $lookup ), $var ) );
    }
  }

  public static function is_url( $var, $field = '' ) {
    if ( ! Utils::is_url( $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid url', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function is_currency( $var, $min = 0.00, $max = null, $field = '' ) {
    if ( ! is_numeric( $var ) || $var < $min || ( ! is_null( $max ) && $var > $max ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid representation of currency', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function is_numeric( $var, $min = 0, $max = null, $field = '' ) {
    if ( ! is_numeric( $var ) || $var < $min || ( ! is_null( $max ) && $var > $max ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid number', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function is_email( $var, $field = '' ) {
    if ( ! Utils::is_email( $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid email', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function is_phone( $var, $field = '' ) {
    if ( ! Utils::is_phone( $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid phone number', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function is_ip_addr( $var, $field = '' ) {
    if ( ! Utils::is_ip( $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid IP Address', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function is_date( $var, $field = '' ) {
    if ( ! Utils::is_date( $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid date', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  // Pretty much all we can do here is make sure it's a number and not empty
  public static function is_timestamp( $var, $field = '' ) {
    if ( empty( $var ) || ! is_numeric( $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must be a valid timestamp', 'memberpress-coachkit' ), $field, $var ) );
    }
  }

  public static function regex( $pattern, $var, $field = '' ) {
    if ( ! preg_match( $pattern, $var ) ) {
      throw new ValidationException( sprintf( __( '%1$s (%2$s) must match the regex pattern: %3$s', 'memberpress-coachkit' ), $field, $var, $pattern ) );
    }
  }

  public static function sanitize( $var ) {
    if ( is_array( $var ) ) {
      return array_map( self::class . '::sanitize', $var );
    } else {
      return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
  }

}
