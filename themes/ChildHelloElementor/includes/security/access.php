<?php
/**
 * Access control for the CRM.
 *
 * Single source of truth for "who is staff" plus the guards used by every
 * CRM AJAX handler, shortcode and the REST / front-end lockdown of private
 * CRM post types (referrals, session logs, competitions, schools, families).
 *
 * Staff = anyone who can edit_posts, or has the 'coach' role.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Is the given (or current) user CRM staff?
 */
function wcb_is_staff($user = null) {
    $user = $user ? $user : wp_get_current_user();

    if (!$user || !$user->exists()) {
        return false;
    }

    return user_can($user, 'edit_posts') || in_array('coach', (array) $user->roles, true);
}

/**
 * Can the current visitor use the session-logging forms
 * (class sessions, community sessions, competitions, interventions)?
 *
 * Staff always can. The 'wcb_can_log_sessions' filter lets an alternative
 * gate (e.g. a gym access code) grant access without a WordPress login.
 */
function wcb_can_log_sessions() {
    if (wcb_is_staff()) {
        return true;
    }

    // Coaches currently log sessions on these pages without logging in, so
    // they stay open to logged-out visitors until the client agrees an access
    // method (gym access code or coach logins). Lock them by setting the
    // option 'wcb_lock_session_forms' to 1, or hook the filter below.
    $open_by_default = !get_option('wcb_lock_session_forms');

    return (bool) apply_filters('wcb_can_log_sessions', $open_by_default);
}

/**
 * Verify the shared AJAX nonce. Accepts 'nonce' from POST or GET.
 */
function wcb_ajax_nonce_is_valid() {
    $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
    return (bool) wp_verify_nonce($nonce, 'wcb_nonce');
}

/**
 * Stop an AJAX request unless the current user is staff.
 */
function wcb_ajax_require_staff() {
    if (!wcb_is_staff()) {
        wp_send_json_error(['message' => 'You do not have permission to do that.'], 403);
    }
}

/**
 * Stop an AJAX request unless the visitor may use the session-logging forms.
 */
function wcb_ajax_require_session_logger() {
    if (!wcb_can_log_sessions()) {
        wp_send_json_error(['message' => 'You do not have permission to do that.'], 403);
    }
}

/**
 * Message shown by staff-only shortcodes to everyone else.
 */
function wcb_staff_only_notice() {
    if (is_user_logged_in()) {
        return '<div class="access-denied">You do not have permission to view this page.</div>';
    }

    return '<div class="access-denied">Please <a href="' . esc_url(wp_login_url(get_permalink())) . '">log in</a> to view this page.</div>';
}

/**
 * Post types and taxonomies that hold private CRM data.
 */
function wcb_private_post_types() {
    return ['referral', 'session_log', 'community_session', 'competition', 'family_membership', 'school', 'schools'];
}

function wcb_private_taxonomies() {
    return ['session_type', 'school'];
}

/**
 * Keep private CRM types out of front-end search results for everyone.
 * (Does not affect wp-admin list screens.)
 */
add_filter('register_post_type_args', function ($args, $post_type) {
    if (in_array($post_type, wcb_private_post_types(), true)) {
        $args['exclude_from_search'] = true;
    }
    return $args;
}, 20, 2);

/**
 * Block the REST API for private CRM types unless the user is staff.
 * Staff keep full access, so the block editor keeps working for them.
 * Also stops logged-out visitors listing user accounts.
 */
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    if (null !== $result) {
        return $result;
    }

    $route = untrailingslashit($request->get_route());

    if (strpos($route, '/wp/v2/users') === 0 && !is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', ['status' => 401]);
    }

    if (wcb_is_staff()) {
        return $result;
    }

    $bases = [];
    foreach (wcb_private_post_types() as $type) {
        $obj = get_post_type_object($type);
        if ($obj) {
            $bases[] = $obj->rest_base ? $obj->rest_base : $type;
        }
    }
    foreach (wcb_private_taxonomies() as $tax) {
        $obj = get_taxonomy($tax);
        if ($obj) {
            $bases[] = $obj->rest_base ? $obj->rest_base : $tax;
        }
    }

    foreach (array_unique($bases) as $base) {
        if ($route === '/wp/v2/' . $base || strpos($route, '/wp/v2/' . $base . '/') === 0) {
            return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', ['status' => rest_authorization_required_code()]);
        }
    }

    return $result;
}, 10, 3);

/**
 * Keep private CRM types out of the core and Yoast XML sitemaps.
 */
add_filter('wp_sitemaps_post_types', function ($post_types) {
    foreach (wcb_private_post_types() as $type) {
        unset($post_types[$type]);
    }
    return $post_types;
});

add_filter('wp_sitemaps_taxonomies', function ($taxonomies) {
    foreach (wcb_private_taxonomies() as $tax) {
        unset($taxonomies[$tax]);
    }
    return $taxonomies;
});

add_filter('wpseo_sitemap_exclude_post_type', function ($excluded, $post_type) {
    return in_array($post_type, wcb_private_post_types(), true) ? true : $excluded;
}, 10, 2);

add_filter('wpseo_sitemap_exclude_taxonomy', function ($excluded, $taxonomy) {
    return in_array($taxonomy, wcb_private_taxonomies(), true) ? true : $excluded;
}, 10, 2);

/**
 * Shortcodes that show private CRM data: staff only.
 */
function wcb_staff_only_shortcodes() {
    return [
        'dashboard_stats', 'staff_dashboard_stats', 'dashboard_students',
        'student_table', 'student_search', 'student_profile_container', 'student_sessions',
        'programs_table', 'community_class', 'wcb_community_class',
        'single_session', 'single_referral', 'wcb_single_competition', 'wcb_competitions_table',
        'wcb_referrals_dashboard', 'all_sessions_list', 'active_users_list', 'user_login_history',
        'debug_memberpress', 'wcb_bulk_expire_tool', 'parent_invitation_form',
    ];
}

/**
 * Session-logging form shortcodes: staff, or whoever wcb_can_log_sessions() allows.
 * Submissions are processed inside these shortcodes, so gating them also
 * blocks unauthorised submissions.
 */
function wcb_session_logger_shortcodes() {
    return ['wcb_class_session_form', 'wcb_community_session_form', 'wcb_competition_form', 'wcb_intervention_form'];
}

add_filter('pre_do_shortcode_tag', function ($output, $tag) {
    if (false !== $output) {
        return $output;
    }

    if (in_array($tag, wcb_staff_only_shortcodes(), true) && !wcb_is_staff()) {
        return wcb_staff_only_notice();
    }

    if (in_array($tag, wcb_session_logger_shortcodes(), true) && !wcb_can_log_sessions()) {
        return (string) apply_filters('wcb_session_logger_denied_output', wcb_staff_only_notice());
    }

    return $output;
}, 10, 2);
