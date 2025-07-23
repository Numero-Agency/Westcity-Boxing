<?php
namespace memberpress\coachkit\lib;

use memberpress\coachkit as base;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

/**
 * View class for handling view paths and rendering views.
 */
class View extends \MeprView {

  /**
   * Get the view paths.
   *
   * @return array Array of view paths.
   */
  public static function paths() {
    $paths = array();

    $template_path   = get_template_directory();
    $stylesheet_path = get_stylesheet_directory();

    // Put child theme's first if one's being used
    if ( $stylesheet_path !== $template_path ) {
      $paths[] = "{$stylesheet_path}/memberpress/coaching";
    }

    $paths[] = "{$template_path}/memberpress/coaching";
    $paths[] = base\VIEWS_PATH;

    return \MeprHooks::apply_filters( 'mepr_view_paths', $paths );
  }

  /**
   * Get the string content of a view.
   *
   * @param string $slug   The view slug.
   * @param array  $vars   Array of variables to pass to the view.
   * @param array  $paths  Array of view paths.
   *
   * @return string|null The view content.
   */
  public static function get_string( $slug, $vars = array(), $paths = array() ) {
    if ( empty( $paths ) ) {
      $paths = self::paths();
    }

    $template_part_slug = 'memberpress/coaching' . dirname( $slug );
    $template_part_name = basename( $slug );

    do_action( "get_template_part_{$template_part_slug}", $template_part_slug, $template_part_name ); // bypass MeprHooks for this one

    extract( $vars, EXTR_SKIP );

    $file = self::file( $slug, $paths );
    if ( ! $file ) { return null; }

    ob_start();
    require $file;
    $view = ob_get_clean();

    $view = \MeprHooks::apply_filters( 'mepr_view_get_string_' . $slug, $view, $vars ); // Slug specific filter
    $view = \MeprHooks::apply_filters( 'mepr_view_get_string', $view, $slug, $vars ); // General filter

    return $view;
  }

  /**
   * Get the file path of a view.
   *
   * @param string $slug   The view slug.
   * @param array  $paths  Array of view paths.
   *
   * @return string|false The file path or false if not found.
   */
  public static function file( $slug, $paths = array() ) {
    $paths = ( empty( $paths ) ? self::paths() : $paths );
    $find  = $slug . '.php';

    if ( ! preg_match( '#^/#', $find ) ) { $find = '/' . $find; }

    foreach ( $paths as $path ) {
      if ( file_exists( $path . $find ) ) {
        return $path . $find;
      }
    }

    return false;
  }

  /**
   * Render a view.
   *
   * @param string $slug   The view slug.
   * @param array  $vars   Array of variables to pass to the view.
   * @param array  $paths  Array of view paths.
   *
   * @return string|null The rendered view content.
   */
  public static function render( $slug, $vars = array(), $paths = array() ) {
    $view = self::get_string( $slug, $vars, $paths );

    if ( ! is_null( $view ) ) {
      echo $view;
    }

    return $view;
  }
}
