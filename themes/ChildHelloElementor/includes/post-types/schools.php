<?php
/**
 * Schools admin editing.
 *
 * Schools are the `school` post type, registered in ACF (ACF > Post Types),
 * with their students in the ACF "Student Details" field group. The theme
 * used to register a second, duplicate `schools` type; those posts were
 * merged into `school` and that registration was removed.
 *
 * This file keeps the school edit screen simple and reliable:
 * - the classic editor, so the Title box is always at the top
 *   (the block editor's REST route for `school` clashes with the
 *   `school` taxonomy);
 * - no content editor box, just Title + Students;
 * - Breakdance switched off for private CRM post types, so "Add New School"
 *   never opens the page builder (which has no Title field).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Use the classic editor for schools.
 */
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
    return $post_type === 'school' ? false : $use_block_editor;
}, 100, 2);

/**
 * Schools don't use the content area: keep the screen to Title + Students.
 * Existing content is left untouched in the database.
 */
add_action('init', function () {
    if (post_type_exists('school')) {
        remove_post_type_support('school', 'editor');
    }
}, 99);

/**
 * Remove private CRM post types from Breakdance's editable post types.
 *
 * Breakdance reads the list from this option and, when it has never been
 * saved, falls back to every public post type, which included schools.
 */
function wcb_breakdance_exclude_crm_post_types($value) {
    $excluded = function_exists('wcb_private_post_types') ? wcb_private_post_types() : ['school'];

    $types = is_string($value) ? json_decode($value, true) : null;
    if (!is_array($types)) {
        $types = array_values(get_post_types(['public' => true]));
    }

    $types = array_values(array_diff($types, $excluded, ['attachment', 'product']));

    return wp_json_encode($types);
}
add_filter('option_breakdance_breakdance_settings_enabled_post_types', 'wcb_breakdance_exclude_crm_post_types');
add_filter('default_option_breakdance_breakdance_settings_enabled_post_types', 'wcb_breakdance_exclude_crm_post_types');
