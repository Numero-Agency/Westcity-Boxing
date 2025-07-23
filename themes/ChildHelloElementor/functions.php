<?php
/*This file is part of ChildHelloElementor, hello-elementor child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

if ( ! function_exists( 'suffice_child_enqueue_child_styles' ) ) {
	function ChildHelloElementor_enqueue_child_styles() {
	    // loading parent style
	    wp_register_style(
	      'parente2-style',
	      get_template_directory_uri() . '/style.css'
	    );

	    wp_enqueue_style( 'parente2-style' );
	    // loading child style
	    wp_register_style(
	      'childe2-style',
	      get_stylesheet_directory_uri() . '/style.css'
	    );
	    wp_enqueue_style( 'childe2-style');
	 }
}
add_action( 'wp_enqueue_scripts', 'ChildHelloElementor_enqueue_child_styles' );

// ================================
// WEST CITY BOXING CRM SYSTEM
// ================================

// Define constants
define('WCB_THEME_PATH', get_stylesheet_directory());
define('WCB_THEME_URL', get_stylesheet_directory_uri());
define('WCB_INCLUDES_PATH', WCB_THEME_PATH . '/includes');

// Auto-loader for our custom files
function wcb_load_files() {
    $files = [
        // Database
        'database/competitions-table.php',
        
        // Dashboard Components
        'dashboard/dashboard-stats.php',
        'dashboard/dashboard-sessions.php', 
        'dashboard/dashboard-students.php',
        'dashboard/dashboard-memberships.php',
        'dashboard/dashboard-referrals.php',
        
        // AJAX Handlers
        'ajax/student-search.php',
        'ajax/student-profile.php',
        
        // Shortcodes
        'shortcodes/single-session.php',
        'shortcodes/student-sessions.php',
        'shortcodes/student-table.php',
        'shortcodes/programs-table.php',
        'shortcodes/single-competition.php',
        'shortcodes/competitions-table.php',
        'shortcodes/community-class.php',
        'shortcodes/single-referral.php',
        
        // Forms
        'forms/class-session-form.php',
        'forms/intervention-form.php',
        'forms/competition-form.php',
        'forms/referral-form.php',
        
        // Authentication & Tracking
        'auth/login-sessions.php',
        'auth/user-tracking.php',
        
        // Styles & Scripts
        'styles/dashboard-styles.php'
    ];
    
    foreach ($files as $file) {
        $file_path = WCB_INCLUDES_PATH . '/' . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}
add_action('after_setup_theme', 'wcb_load_files');

// Register Custom Post Types
function wcb_register_custom_post_types() {
    // Community Session post type
    register_post_type('community_session', [
        'labels' => [
            'name' => 'Community Sessions',
            'singular_name' => 'Community Session',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Community Session',
            'edit_item' => 'Edit Community Session',
            'new_item' => 'New Community Session',
            'view_item' => 'View Community Session',
            'search_items' => 'Search Community Sessions',
            'not_found' => 'No community sessions found',
            'not_found_in_trash' => 'No community sessions found in trash'
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
        'show_in_rest' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => [
            'slug' => 'community-sessions',
            'with_front' => false
        ]
    ]);
    
    // Competition post type
    register_post_type('competition', [
        'labels' => [
            'name' => 'Competitions',
            'singular_name' => 'Competition',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Competition',
            'edit_item' => 'Edit Competition',
            'new_item' => 'New Competition',
            'view_item' => 'View Competition',
            'search_items' => 'Search Competitions',
            'not_found' => 'No competitions found',
            'not_found_in_trash' => 'No competitions found in trash'
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-awards',
        'supports' => ['title', 'editor', 'author', 'custom-fields', 'thumbnail'],
        'show_in_rest' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => [
            'slug' => 'competition',
            'with_front' => false
        ]
    ]);
    
    // Referral post type
    register_post_type('referral', [
        'labels' => [
            'name' => 'Referrals',
            'singular_name' => 'Referral',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Referral',
            'edit_item' => 'Edit Referral',
            'new_item' => 'New Referral',
            'view_item' => 'View Referral',
            'search_items' => 'Search Referrals',
            'not_found' => 'No referrals found',
            'not_found_in_trash' => 'No referrals found in trash'
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-admin-users',
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
        'show_in_rest' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => [
            'slug' => 'referrals',
            'with_front' => false
        ]
    ]);
}
add_action('init', 'wcb_register_custom_post_types');

// Flush rewrite rules when theme is activated
function wcb_flush_rewrite_rules() {
    wcb_register_custom_post_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'wcb_flush_rewrite_rules');

// Single consolidated script/style enqueuing function
function wcb_enqueue_assets() {
    // Ensure jQuery and jQuery UI are loaded
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tooltip');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css');
    
    // Dashboard CSS
    if (file_exists(WCB_THEME_PATH . '/assets/css/dashboard.css')) {
        wp_enqueue_style(
            'wcb-dashboard-css',
            WCB_THEME_URL . '/assets/css/dashboard.css',
            [],
            filemtime(WCB_THEME_PATH . '/assets/css/dashboard.css')
        );
    }
    
    // Forms CSS
    if (file_exists(WCB_THEME_PATH . '/includes/styles/forms.css')) {
        wp_enqueue_style(
            'wcb-forms-css',
            WCB_THEME_URL . '/includes/styles/forms.css',
            [],
            filemtime(WCB_THEME_PATH . '/includes/styles/forms.css')
        );
    }
    
    // Dashboard JS
    if (file_exists(WCB_THEME_PATH . '/assets/js/dashboard.js')) {
        wp_enqueue_script(
            'wcb-dashboard-js',
            WCB_THEME_URL . '/assets/js/dashboard.js',
            ['jquery', 'jquery-ui-core', 'jquery-ui-tooltip'],
            filemtime(WCB_THEME_PATH . '/assets/js/dashboard.js'),
            true
        );
        
        // Localize script for AJAX - use consistent nonce name
        wp_localize_script('wcb-dashboard-js', 'wcb_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcb_nonce'),
            'home_url' => home_url()
        ]);
    }
}
add_action('wp_enqueue_scripts', 'wcb_enqueue_assets');

// Contact Form 7 redirect (keep existing functionality)
add_action( 'wp_footer', 'mycustom_wp_footer' );
function mycustom_wp_footer() {
?>
<script>
document.addEventListener( 'wpcf7mailsent', function( event ) {
   location = '<?php echo home_url(); ?>/thank-you/';
}, false );
</script>
<?php
}

// Helper function for debugging (remove in production)
function wcb_debug_log($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

// Helper function to normalize ACF user fields that can return different formats
function wcb_normalize_user_field($field_value) {
    if (empty($field_value)) {
        return [];
    }
    
    // If it's already an array of IDs, return as is
    if (is_array($field_value)) {
        $normalized = [];
        foreach ($field_value as $value) {
            if (is_object($value) && isset($value->ID)) {
                $normalized[] = $value->ID;
            } elseif (is_array($value) && isset($value['ID'])) {
                $normalized[] = $value['ID'];
            } elseif (is_numeric($value)) {
                $normalized[] = intval($value);
            }
        }
        return $normalized;
    }
    
    // If it's a single user object
    if (is_object($field_value) && isset($field_value->ID)) {
        return [$field_value->ID];
    }
    
    // If it's a single user ID
    if (is_numeric($field_value)) {
        return [intval($field_value)];
    }
    
    // If it's a string that might be comma separated IDs
    if (is_string($field_value)) {
        $ids = explode(',', $field_value);
        $normalized = [];
        foreach ($ids as $id) {
            $id = trim($id);
            if (is_numeric($id)) {
                $normalized[] = intval($id);
            }
        }
        return $normalized;
    }
    
    return [];
}

// Helper function to get attendance data from ACF repeater and excused students field
function wcb_get_session_attendance($session_id) {
    // Try common field names for attendance repeater
    $possible_field_names = ['attendance', 'student_attendance', 'attendance_list', 'students'];
    
    $attendance_data = null;
    foreach ($possible_field_names as $field_name) {
        $data = get_field($field_name, $session_id);
        if (is_array($data) && !empty($data)) {
            $attendance_data = $data;
            break;
        }
    }
    
    $attended_students = [];
    $excused_students = [];
    
    // Process attendance repeater data
    if (is_array($attendance_data) && !empty($attendance_data)) {
        foreach ($attendance_data as $attendance_row) {
            // Try different possible sub-field names
            $student = null;
            $is_present = false;
            
            // Try different student field names
            if (isset($attendance_row['student'])) {
                $student = $attendance_row['student'];
            } elseif (isset($attendance_row['user'])) {
                $student = $attendance_row['user'];
            } elseif (isset($attendance_row['student_user'])) {
                $student = $attendance_row['student_user'];
            }
            
            // Try different present field names
            if (isset($attendance_row['present'])) {
                $is_present = $attendance_row['present'];
            } elseif (isset($attendance_row['attended'])) {
                $is_present = $attendance_row['attended'];
            } elseif (isset($attendance_row['is_present'])) {
                $is_present = $attendance_row['is_present'];
            }
            
            // Normalize student field
            $student_id = null;
            if (is_object($student) && isset($student->ID)) {
                $student_id = $student->ID;
            } elseif (is_array($student) && isset($student['ID'])) {
                $student_id = $student['ID'];
            } elseif (is_numeric($student)) {
                $student_id = intval($student);
            }
            
            if ($student_id && $is_present) {
                $attended_students[] = $student_id;
            }
        }
    }
    
    // Get excused students from separate ACF User field
    $possible_excused_field_names = [
        'excused_students', 
        'absent_students', 
        'excused', 
        'absent',
        'excused_users',
        'absent_users'
    ];
    
    foreach ($possible_excused_field_names as $field_name) {
        $excused_data = get_field($field_name, $session_id);
        if (!empty($excused_data)) {
            // Handle single user or array of users
            if (!is_array($excused_data)) {
                $excused_data = [$excused_data];
            }
            
            foreach ($excused_data as $user) {
                // Normalize user field - but handle the fact it returns an array
                $normalized_ids = wcb_normalize_user_field($user);
                if (!empty($normalized_ids)) {
                    foreach ($normalized_ids as $student_id) {
                        if ($student_id && !in_array($student_id, $attended_students)) {
                            $excused_students[] = $student_id;
                        }
                    }
                }
            }
            break; // Found the field, no need to try others
        }
    }
    
    // Remove duplicates
    $attended_students = array_unique($attended_students);
    $excused_students = array_unique($excused_students);
    
    return [
        'attended' => $attended_students,
        'excused' => $excused_students,
        'absent' => $excused_students, // Keep 'absent' key for backward compatibility
        'raw_data' => $attendance_data
    ];
}

// Register all shortcodes
function wcb_register_shortcodes() {
    // Dashboard shortcodes
    add_shortcode('dashboard_stats', 'dashboard_stats_shortcode');
    add_shortcode('student_sessions', 'student_sessions_shortcode');
    add_shortcode('single_session', 'single_session_shortcode');
    
    // Search and profile shortcodes
    add_shortcode('student_search', 'student_search_shortcode');
    add_shortcode('student_profile_container', 'student_profile_container_shortcode');
    add_shortcode('student_table', 'student_table_shortcode');
    
    // Community class shortcode
    add_shortcode('community_class', 'community_class_shortcode');
}
add_action('init', 'wcb_register_shortcodes');

// Register AJAX handlers for both logged in and non-logged in users
function wcb_register_ajax_handlers() {
    // Student search AJAX
    add_action('wp_ajax_wcb_search_students', 'wcb_ajax_search_students');
    add_action('wp_ajax_nopriv_wcb_search_students', 'wcb_ajax_search_students');
    
    // Student profile AJAX
    add_action('wp_ajax_wcb_load_student_profile', 'wcb_ajax_load_student_profile');
    add_action('wp_ajax_nopriv_wcb_load_student_profile', 'wcb_ajax_load_student_profile');
    
    // Student table AJAX
    add_action('wp_ajax_wcb_load_students_table', 'wcb_ajax_load_students_table');
    add_action('wp_ajax_nopriv_wcb_load_students_table', 'wcb_ajax_load_students_table');
}
add_action('init', 'wcb_register_ajax_handlers');

// Debug function to help with troubleshooting
function wcb_debug_shortcodes() {
    if (current_user_can('administrator') && isset($_GET['wcb_debug'])) {
        echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
        echo '<h3>WCB Debug Info</h3>';
        echo '<p><strong>Theme Path:</strong> ' . WCB_THEME_PATH . '</p>';
        echo '<p><strong>Includes Path:</strong> ' . WCB_INCLUDES_PATH . '</p>';
        echo '<p><strong>Dashboard CSS exists:</strong> ' . (file_exists(WCB_THEME_PATH . '/assets/css/dashboard.css') ? 'Yes' : 'No') . '</p>';
        echo '<p><strong>Dashboard JS exists:</strong> ' . (file_exists(WCB_THEME_PATH . '/assets/js/dashboard.js') ? 'Yes' : 'No') . '</p>';
        
        global $shortcode_tags;
        echo '<p><strong>Registered WCB Shortcodes:</strong></p><ul>';
        foreach (['dashboard_stats', 'student_search', 'student_profile_container'] as $shortcode) {
            echo '<li>' . $shortcode . ': ' . (isset($shortcode_tags[$shortcode]) ? 'Registered' : 'NOT registered') . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}
add_action('wp_footer', 'wcb_debug_shortcodes');

// Simple session management
function wcb_init_session() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'wcb_init_session');

// Admin notice for debugging
function wcb_admin_notice() {
    if (current_user_can('administrator') && isset($_GET['wcb_debug'])) {
        echo '<div class="notice notice-info"><p>WCB Debug mode is active. Add ?wcb_debug=1 to any page to see debug info.</p></div>';
    }
}
add_action('admin_notices', 'wcb_admin_notice');

// =======================
// MEMBERPRESS SAFE WRAPPER
// =======================

/**
 * Safe MemberPress helper class to avoid undefined function errors
 */
class WCB_MemberPress_Helper {
    
    /**
     * Check if MemberPress is active and available
     */
    public static function is_memberpress_active() {
        return class_exists('MeprUser') && class_exists('MeprSubscription');
    }
    
    /**
     * Safely get user active subscriptions (updated to use proper MemberPress subscription method)
     */
    public static function get_user_subscriptions($user_id) {
        if (!self::is_memberpress_active()) {
            return [];
        }
        
        try {
            $mepr_user = new MeprUser($user_id);
            $subscription_titles = $mepr_user->get_active_subscription_titles();
            return !empty($subscription_titles) ? explode(', ', $subscription_titles) : [];
        } catch (Exception $e) {
            error_log('WCB MemberPress Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get formatted membership info for display (updated to show active subscriptions)
     */
    public static function get_membership_display($user_id) {
        // Simple direct database query for subscriptions
        global $wpdb;
        
        // Check if MemberPress tables exist
        $subs_table = $wpdb->prefix . 'mepr_subscriptions';
        $products_table = $wpdb->prefix . 'posts';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$subs_table'") == $subs_table;
        
        if ($table_exists) {
            try {
                // Query for active subscriptions
                $subscription_query = "
                    SELECT p.post_title 
                    FROM $subs_table s
                    JOIN $products_table p ON s.product_id = p.ID
                    WHERE s.user_id = %d 
                    AND s.status = 'active'
                    AND p.post_status = 'publish'
                ";
                
                $subscription_titles = $wpdb->get_col($wpdb->prepare($subscription_query, $user_id));
                
                if (!empty($subscription_titles)) {
                    return implode(', ', $subscription_titles);
                }
            } catch (Exception $e) {
                error_log('WCB Subscription Query Error: ' . $e->getMessage());
            }
        }
        
        // Fallback to MemberPress API if available
        if (self::is_memberpress_active()) {
            try {
                if (class_exists('MeprUser')) {
                    $mepr_user = new MeprUser($user_id);
                    if (method_exists($mepr_user, 'get_active_subscription_titles')) {
                        $subscription_titles = $mepr_user->get_active_subscription_titles();
                        if (!empty($subscription_titles)) {
                            return $subscription_titles;
                        }
                    }
                }
                
                // Try active memberships as fallback using correct MemberPress API
                if (class_exists('MeprUser')) {
                    $mepr_user = new MeprUser($user_id);
                    if (method_exists($mepr_user, 'active_product_subscriptions')) {
                        $active_memberships = $mepr_user->active_product_subscriptions();
                        if (!empty($active_memberships)) {
                            $membership_names = array_map(function($membership) {
                                return get_the_title($membership->product_id);
                            }, $active_memberships);
                            return implode(', ', $membership_names);
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('WCB MemberPress API Error: ' . $e->getMessage());
            }
        }
        
        // Final fallback based on user roles
        $user = get_user_by('ID', $user_id);
        if ($user && !empty($user->roles)) {
            if (in_array('member', $user->roles) || in_array('customer', $user->roles)) {
                return 'Active Member';
            }
        }
        
        return 'No Active Membership';
    }
    
    /**
     * Safely get waitlist count from MemberPress transactions
     */
    public static function get_waitlist_count() {
        global $wpdb;
        
        // Check if MemberPress tables exist
        $table_name = $wpdb->prefix . 'mepr_transactions';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists || !self::is_memberpress_active()) {
            return 3; // Fallback number
        }
        
        try {
            $waitlist_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(DISTINCT user_id) 
                FROM {$wpdb->prefix}mepr_transactions 
                WHERE status IN (%s, %s)
            ", 'pending', 'failed'));
            
            return $waitlist_count ? intval($waitlist_count) : 3;
        } catch (Exception $e) {
            error_log('WCB MemberPress DB Error: ' . $e->getMessage());
            return 3; // Fallback
        }
    }
    
    /**
     * Check if MemberPress database tables exist
     */
    public static function memberpress_tables_exist() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mepr_transactions';
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    }
}

// Helper function to automatically detect the correct session type taxonomy
function wcb_get_session_type_taxonomy() {
    static $taxonomy_name = null;
    
    if ($taxonomy_name !== null) {
        return $taxonomy_name;
    }
    
    // Get all available taxonomies for session_log post type
    $available_taxonomies = get_object_taxonomies('session_log');
    
    // Common patterns for session type taxonomies
    $possible_names = [
        'session-type',
        'session_type', 
        'session-types',
        'session_types',
        'sessiontype',
        'sessiontypes',
        'session_log_type',
        'session_log_types'
    ];
    
    // Look for exact matches first
    foreach ($possible_names as $name) {
        if (in_array($name, $available_taxonomies)) {
            $taxonomy_name = $name;
            return $taxonomy_name;
        }
    }
    
    // Look for partial matches (in case ACF created something like 'session_type_xyz')
    foreach ($available_taxonomies as $tax) {
        if (strpos($tax, 'session') !== false && (strpos($tax, 'type') !== false || strpos($tax, 'types') !== false)) {
            $taxonomy_name = $tax;
            return $taxonomy_name;
        }
    }
    
    // Default fallback
    $taxonomy_name = 'session-type';
    return $taxonomy_name;
}

// Helper function to get session type for a session
function wcb_get_session_type($session_id) {
    $taxonomy = wcb_get_session_type_taxonomy();
    $session_terms = get_the_terms($session_id, $taxonomy);
    
    if ($session_terms && !is_wp_error($session_terms)) {
        return [
            'name' => $session_terms[0]->name,
            'slug' => $session_terms[0]->slug,
            'taxonomy' => $taxonomy
        ];
    }
    
    return [
        'name' => 'Unknown',
        'slug' => 'unknown',
        'taxonomy' => $taxonomy
    ];
}

// Create competition page on theme activation
function wcb_create_competition_page() {
    // Check if page already exists
    $existing_page = get_page_by_path('competition-view');
    
    if (!$existing_page) {
        // Create the competition page
        $page_data = array(
            'post_title' => 'Competition View',
            'post_content' => '[wcb_single_competition]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'competition-view'
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id) {
            update_option('wcb_competition_page_id', $page_id);
        }
    }
}
add_action('after_switch_theme', 'wcb_create_competition_page');

// Add query var for competition_id
function wcb_add_competition_query_vars($vars) {
    $vars[] = 'competition_id';
    return $vars;
}
add_filter('query_vars', 'wcb_add_competition_query_vars');

// Handle competition page template
function wcb_handle_competition_template($template) {
    if (is_page('competition-view')) {
        // This is the competition page, let it handle the shortcode
        return $template;
    }
    return $template;
}
add_filter('template_include', 'wcb_handle_competition_template');

// Manual function to clear competition database (for testing)
function wcb_clear_competitions_database() {
    if (current_user_can('manage_options')) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        $wpdb->query("DELETE FROM $table_name");
        wp_redirect(add_query_arg('cleared', '1', wp_get_referer()));
        exit;
    }
}
add_action('wp_ajax_wcb_clear_competitions', 'wcb_clear_competitions_database');

// Manual function to create competition page (for testing)
function wcb_manual_create_competition_page() {
    if (current_user_can('manage_options')) {
        wcb_create_competition_page();
        wp_redirect(add_query_arg('page_created', '1', wp_get_referer()));
        exit;
    }
}
add_action('wp_ajax_wcb_create_competition_page', 'wcb_manual_create_competition_page');

// Create test competition for debugging
function wcb_create_test_competition() {
    if (current_user_can('manage_options')) {
        // Include database helper
        if (!class_exists('WCB_Competition_DB')) {
            wp_die('Competition database helper not available');
        }
        
        // Create test competition
        $competition_data = [
            'event_name' => 'Test Competition',
            'event_date' => date('Y-m-d'),
            'event_location' => 'Test Location',
            'supporters' => 'Test supporters',
            'results_wins' => 3,
            'results_losses' => 1,
            'highlights' => 'Test highlights',
            'conversations' => 'Test conversations',
            'created_by' => get_current_user_id()
        ];
        
        $competition_id = WCB_Competition_DB::insert_competition($competition_data);
        
        if ($competition_id) {
            wp_redirect(add_query_arg('test_created', $competition_id, wp_get_referer()));
        } else {
            wp_redirect(add_query_arg('test_failed', '1', wp_get_referer()));
        }
        exit;
    }
}
add_action('wp_ajax_wcb_create_test_competition', 'wcb_create_test_competition');
