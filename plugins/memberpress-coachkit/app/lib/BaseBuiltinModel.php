<?php
namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

/** Specific base class for Builtin Style models */
abstract class BaseBuiltinModel extends BaseModel {
  public $meta_attrs;
  /** Get all the meta attributes and default values */
  public function get_meta_attrs() {
    return (array) $this->meta_attrs;
  }
}

