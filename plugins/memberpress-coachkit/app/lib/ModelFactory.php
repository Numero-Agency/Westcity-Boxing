<?php
namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

use memberpress\coachkit as base;

/** Get a specific model from a name string. */
class ModelFactory {
  public static function fetch( $model, $id ) {
    $class = base\MODELS_NAMESPACE . '\\' . Inflector::classify( $model );

    if ( ! class_exists( $class ) ) {
      return new WP_Error( sprintf( __( 'A model for %s wasn\'t found', 'memberpress-coachkit' ), $model ) );
    }

    // We'll let the autoloader handle including files containing these classes
    $r   = new ReflectionClass( $class );
    $obj = $r->newInstanceArgs( array( $id ) );

    if ( isset( $obj->ID ) && $obj->ID <= 0 ) {
      return new WP_Error( sprintf( __( 'There was no %1$s with an id of %2$d found', 'memberpress-coachkit' ), $model, $obj->ID ) );
    } elseif ( isset( $obj->id ) && $obj->id <= 0 ) {
      return new WP_Error( sprintf( __( 'There was no %1$s with an id of %2$d found', 'memberpress-coachkit' ), $model, $obj->id ) );
    } elseif ( isset( $obj->term_id ) && $obj->term_id <= 0 ) {
      return new WP_Error( sprintf( __( 'There was no %1$s with an id of %2$d found', 'memberpress-coachkit' ), $model, $obj->term_id ) );
    }

    $objs[ $class ] = $obj;

    return $obj;
  }
}

