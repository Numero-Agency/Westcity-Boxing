<?php

/**
 * Global function to load views in template files
 *
 * @param string $name
 * @param array  $variables
 * @return void
 */
function mpch_load_template( $name, $variables = [] ) {
  memberpress\coachkit\lib\View::render( $name, $variables );
}

/**
 * Global function to get the string content of a view.
 *
 * @param string $name
 * @param array  $variables
 * @return void
 */
function mpch_images( $name ) {
  return memberpress\coachkit\IMAGES_URL .'/'. $name;
}

function mpch_get_string( $name, $variables = [] ) {
  return memberpress\coachkit\lib\View::get_string( $name, $variables );
}
