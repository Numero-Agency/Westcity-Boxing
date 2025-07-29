<?php
/**
 * Family Membership System Setup
 * Creates database structure and post types for family memberships
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create Family Membership Database Tables
 */
function wcb_create_family_membership_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Family Memberships Table
    $family_memberships_table = $wpdb->prefix . 'wcb_family_memberships';
    
    $sql_family = "CREATE TABLE $family_memberships_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        parent_user_id bigint(20) UNSIGNED NOT NULL,
        membership_product_id bigint(20) UNSIGNED NOT NULL,
        family_name varchar(255) NOT NULL,
        max_children int(11) NOT NULL DEFAULT 2,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY parent_user_id (parent_user_id),
        KEY membership_product_id (membership_product_id),
        KEY status (status)
    ) $charset_collate;";
    
    // Family Children Table
    $family_children_table = $wpdb->prefix . 'wcb_family_children';
    
    $sql_children = "CREATE TABLE $family_children_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        family_id mediumint(9) NOT NULL,
        child_name varchar(255) NOT NULL,
        child_age int(11) DEFAULT NULL,
        child_email varchar(255) DEFAULT NULL,
        assigned_program_id bigint(20) UNSIGNED DEFAULT NULL,
        program_group varchar(255) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY family_id (family_id),
        KEY assigned_program_id (assigned_program_id),
        KEY status (status),
        FOREIGN KEY (family_id) REFERENCES $family_memberships_table(id) ON DELETE CASCADE
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_family);
    dbDelta($sql_children);
    
    // Add version option to track database updates
    add_option('wcb_family_membership_db_version', '1.0');
}

/**
 * Register Family Membership Custom Post Type
 */
function wcb_register_family_membership_post_type() {
    register_post_type('family_membership', [
        'labels' => [
            'name' => 'Family Memberships',
            'singular_name' => 'Family Membership',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Family Membership',
            'edit_item' => 'Edit Family Membership',
            'new_item' => 'New Family Membership',
            'view_item' => 'View Family Membership',
            'search_items' => 'Search Family Memberships',
            'not_found' => 'No family memberships found',
            'not_found_in_trash' => 'No family memberships found in trash'
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'users.php',
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'manage_options',
            'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options',
            'publish_posts' => 'manage_options',
            'read_private_posts' => 'manage_options',
        ],
        'supports' => ['title', 'custom-fields'],
        'has_archive' => false,
        'rewrite' => false,
    ]);
}

/**
 * Add ACF Fields for Family Membership
 */
function wcb_add_family_membership_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key' => 'group_family_membership',
            'title' => 'Family Membership Details',
            'fields' => [
                [
                    'key' => 'field_parent_user',
                    'label' => 'Parent User',
                    'name' => 'parent_user',
                    'type' => 'user',
                    'required' => 1,
                    'role' => ['subscriber', 'customer', 'member'],
                ],
                [
                    'key' => 'field_memberpress_product',
                    'label' => 'MemberPress Product',
                    'name' => 'memberpress_product',
                    'type' => 'post_object',
                    'required' => 1,
                    'post_type' => ['memberpressproduct'],
                ],
                [
                    'key' => 'field_max_children',
                    'label' => 'Maximum Children',
                    'name' => 'max_children',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 2,
                    'min' => 1,
                    'max' => 10,
                ],
                [
                    'key' => 'field_family_status',
                    'label' => 'Family Status',
                    'name' => 'family_status',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => [
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ],
                    'default_value' => 'active',
                ],
                [
                    'key' => 'field_children_details',
                    'label' => 'Children Details',
                    'name' => 'children_details',
                    'type' => 'repeater',
                    'sub_fields' => [
                        [
                            'key' => 'field_child_name',
                            'label' => 'Child Name',
                            'name' => 'child_name',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_child_age',
                            'label' => 'Child Age',
                            'name' => 'child_age',
                            'type' => 'number',
                            'min' => 3,
                            'max' => 18,
                        ],
                        [
                            'key' => 'field_assigned_program',
                            'label' => 'Assigned Program',
                            'name' => 'assigned_program',
                            'type' => 'post_object',
                            'post_type' => ['memberpressproduct'],
                            'allow_null' => 1,
                        ],
                        [
                            'key' => 'field_program_group',
                            'label' => 'Program Group',
                            'name' => 'program_group',
                            'type' => 'select',
                            'choices' => [
                                'mini_cadet_boys' => 'Mini Cadet Boys',
                                'cadet_boys_1' => 'Cadet Boys Group 1',
                                'cadet_boys_2' => 'Cadet Boys Group 2',
                                'youth_boys_1' => 'Youth Boys Group 1',
                                'youth_boys_2' => 'Youth Boys Group 2',
                                'mini_cadet_girls' => 'Mini Cadet Girls',
                                'youth_girls_1' => 'Youth Girls Group 1',
                            ],
                            'allow_null' => 1,
                        ],
                        [
                            'key' => 'field_child_status',
                            'label' => 'Child Status',
                            'name' => 'child_status',
                            'type' => 'select',
                            'choices' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ],
                            'default_value' => 'active',
                        ],
                    ],
                    'min' => 1,
                    'layout' => 'table',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'family_membership',
                    ],
                ],
            ],
        ]);
    }
}

/**
 * Initialize Family Membership System
 */
function wcb_init_family_membership_system() {
    // Create database tables
    wcb_create_family_membership_tables();
    
    // Register post type
    wcb_register_family_membership_post_type();
    
    // Add ACF fields
    wcb_add_family_membership_acf_fields();
}

// Hook into WordPress initialization
add_action('init', 'wcb_register_family_membership_post_type');
add_action('init', 'wcb_add_family_membership_acf_fields');

// Create tables on activation
register_activation_hook(__FILE__, 'wcb_create_family_membership_tables');

/**
 * Helper Functions
 */

/**
 * Get family membership by parent user ID
 */
function wcb_get_family_membership_by_parent($parent_user_id) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'wcb_family_memberships';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE parent_user_id = %d AND status = 'active'",
        $parent_user_id
    ));
}

/**
 * Get children for a family membership
 */
function wcb_get_family_children($family_id) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'wcb_family_children';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE family_id = %d AND status = 'active' ORDER BY child_name",
        $family_id
    ));
}

/**
 * Check if user has active family membership
 */
function wcb_user_has_family_membership($user_id) {
    $family = wcb_get_family_membership_by_parent($user_id);
    return !empty($family);
}

/**
 * Get available program groups
 */
function wcb_get_program_groups() {
    return [
        'mini_cadet_boys' => 'Mini Cadet Boys',
        'cadet_boys_1' => 'Cadet Boys Group 1',
        'cadet_boys_2' => 'Cadet Boys Group 2',
        'youth_boys_1' => 'Youth Boys Group 1',
        'youth_boys_2' => 'Youth Boys Group 2',
        'mini_cadet_girls' => 'Mini Cadet Girls',
        'youth_girls_1' => 'Youth Girls Group 1',
    ];
}

/**
 * Get all children linked to a parent using your existing active members system
 */
function wcb_get_parent_children($parent_user_id) {
    // Use the same logic as active-members-test.php to get active members
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get WCB Mentoring membership ID to exclude
    $wcb_mentoring_id = 1738;

    // Get all active members (same query as active-members-test.php)
    $active_members = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id != {$wcb_mentoring_id}
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ");

    // Get children linked to this parent
    $linked_children_ids = get_user_meta($parent_user_id, 'wcb_linked_children', true);

    if (empty($linked_children_ids) || !is_array($linked_children_ids)) {
        return [];
    }

    // Filter active members to only include linked children
    $children = [];
    foreach ($active_members as $member) {
        if (in_array($member->ID, $linked_children_ids)) {
            $children[] = $member;
        }
    }

    return $children;
}

/**
 * Get child's membership status and renewal info using your existing system
 */
function wcb_get_child_membership_status($child_user) {
    global $wpdb;
    $user_id = $child_user->ID;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get user's most recent active transaction (same logic as active-members-test.php)
    $wcb_mentoring_id = 1738;

    $transaction = $wpdb->get_row($wpdb->prepare("
        SELECT t.*, p.post_title as product_name
        FROM {$txn_table} t
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND t.product_id != %d
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ORDER BY t.created_at DESC
        LIMIT 1
    ", $user_id, $wcb_mentoring_id));

    // Default status
    $status = [
        'status' => 'no_membership',
        'status_text' => 'No Active Membership',
        'program_name' => 'Not Enrolled',
        'expires_date' => null,
        'renewal_url' => '/membership-plans/'
    ];

    if ($transaction) {
        $expires_at = $transaction->expires_at;
        $now = current_time('mysql');

        // Determine status based on expiry
        if ($expires_at && $expires_at !== '0000-00-00 00:00:00') {
            $expires_timestamp = strtotime($expires_at);
            $now_timestamp = strtotime($now);

            if ($expires_timestamp > $now_timestamp) {
                $days_until_expiry = ceil(($expires_timestamp - $now_timestamp) / (60 * 60 * 24));

                if ($days_until_expiry <= 7) {
                    $status['status'] = 'expiring';
                    $status['status_text'] = 'Expires Soon';
                } else {
                    $status['status'] = 'active';
                    $status['status_text'] = 'Active';
                }
                $status['expires_date'] = $expires_at;
            } else {
                $status['status'] = 'expired';
                $status['status_text'] = 'Expired';
                $status['expires_date'] = $expires_at;
            }
        } else {
            // Lifetime membership or manual payment
            $payment_type = wcb_detect_payment_type($transaction);
            if ($payment_type === 'manual') {
                $status['status'] = 'needs_activation';
                $status['status_text'] = 'Needs Activation';
            } else {
                // Check if it's an active Stripe subscription
                $subscription_info = wcb_get_stripe_subscription_info($transaction->user_id, $transaction->product_id);
                if ($subscription_info) {
                    $status['status'] = 'active_subscription';
                    $status['status_text'] = 'Active Subscription';
                    $status['subscription_info'] = $subscription_info;
                } else {
                    $status['status'] = 'active';
                    $status['status_text'] = 'Active (Lifetime)';
                }
            }
        }

        // Get the product and determine group
        $product_post = get_post($transaction->product_id);
        if ($product_post) {
            $status['renewal_url'] = get_permalink($product_post->ID);

            // Get the group this membership belongs to
            $group_info = wcb_get_membership_group_info($transaction->product_id);
            $status['payment_type'] = wcb_detect_payment_type($transaction);

            if ($group_info) {
                $status['group_name'] = $group_info['group_name'];
                $status['group_url'] = $group_info['group_url'];
                $status['billing_options'] = $group_info['billing_options'];

                // For manual payments, show group name instead of specific membership
                if ($status['payment_type'] === 'manual') {
                    $status['program_name'] = $group_info['group_name'];
                } else {
                    $status['program_name'] = $transaction->product_name;
                }
            } else {
                $status['program_name'] = $transaction->product_name;
            }
        } else {
            $status['program_name'] = $transaction->product_name;
        }
    }

    return $status;
}

/**
 * Link a child to a parent account (must be an active member)
 */
function wcb_link_child_to_parent($parent_user_id, $child_identifier) {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $wcb_mentoring_id = 1738;

    // Find child user by email, username, or display name
    $child_user = get_user_by('email', $child_identifier);
    if (!$child_user) {
        $child_user = get_user_by('login', $child_identifier);
    }
    if (!$child_user) {
        // Try to find by display name
        $users = get_users([
            'search' => $child_identifier,
            'search_columns' => ['display_name']
        ]);
        if (!empty($users)) {
            $child_user = $users[0];
        }
    }

    if (!$child_user) {
        return ['success' => false, 'message' => 'No user found with that email, username, or name.'];
    }

    // Check if this user is an active member (same logic as active-members-test.php)
    $is_active_member = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE u.ID = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id != %d
        AND u.user_login != 'bwgdev'
    ", $child_user->ID, $wcb_mentoring_id));

    if (!$is_active_member) {
        return ['success' => false, 'message' => $child_user->display_name . ' is not currently an active member.'];
    }

    // Get current linked children
    $linked_children = get_user_meta($parent_user_id, 'wcb_linked_children', true);
    if (!is_array($linked_children)) {
        $linked_children = [];
    }

    // Add child if not already linked
    if (!in_array($child_user->ID, $linked_children)) {
        $linked_children[] = $child_user->ID;
        update_user_meta($parent_user_id, 'wcb_linked_children', $linked_children);

        // Also add parent reference to child
        update_user_meta($child_user->ID, 'wcb_parent_user', $parent_user_id);

        return ['success' => true, 'message' => $child_user->display_name . ' has been linked to your account!'];
    }

    return ['success' => false, 'message' => $child_user->display_name . ' is already linked to your account.'];
}

/**
 * Parent Invitation System Functions
 */

/**
 * Create parent invitation
 */
function wcb_create_parent_invitation($parent_name, $parent_email, $children_names = '', $personal_message = '') {
    global $wpdb;

    // Create unique token
    $token = wp_generate_password(32, false);

    // Store invitation in database
    $table_name = $wpdb->prefix . 'wcb_parent_invitations';

    $result = $wpdb->insert(
        $table_name,
        [
            'parent_name' => $parent_name,
            'parent_email' => $parent_email,
            'children_names' => $children_names,
            'personal_message' => $personal_message,
            'invitation_token' => $token,
            'status' => 'pending',
            'invited_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
    );

    if ($result === false) {
        return false;
    }

    return $token;
}

/**
 * Get parent invitation by token
 */
function wcb_get_parent_invitation_by_token($token) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcb_parent_invitations';

    $invitation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE invitation_token = %s",
        $token
    ), ARRAY_A);

    return $invitation;
}

/**
 * Complete parent registration
 */
function wcb_complete_parent_registration($token, $username, $password, $phone = '') {
    global $wpdb;

    // Get invitation
    $invitation = wcb_get_parent_invitation_by_token($token);
    if (!$invitation || $invitation['status'] !== 'pending') {
        return ['success' => false, 'message' => 'Invalid or expired invitation'];
    }

    // Check if username already exists
    if (username_exists($username)) {
        return ['success' => false, 'message' => 'Username already exists. Please choose a different username.'];
    }

    // Check if email already exists
    if (email_exists($invitation['parent_email'])) {
        return ['success' => false, 'message' => 'An account with this email already exists.'];
    }

    // Create user account
    $user_id = wp_create_user($username, $password, $invitation['parent_email']);

    if (is_wp_error($user_id)) {
        return ['success' => false, 'message' => $user_id->get_error_message()];
    }

    // Update user profile
    wp_update_user([
        'ID' => $user_id,
        'display_name' => $invitation['parent_name'],
        'first_name' => explode(' ', $invitation['parent_name'])[0],
        'last_name' => substr($invitation['parent_name'], strpos($invitation['parent_name'], ' ') + 1)
    ]);

    // Add phone number if provided
    if ($phone) {
        update_user_meta($user_id, 'phone', $phone);
    }

    // Mark invitation as completed
    $table_name = $wpdb->prefix . 'wcb_parent_invitations';
    $wpdb->update(
        $table_name,
        ['status' => 'completed', 'completed_at' => current_time('mysql')],
        ['invitation_token' => $token],
        ['%s', '%s'],
        ['%s']
    );

    // Log the user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    return ['success' => true, 'message' => 'Registration completed successfully! Redirecting to your family dashboard...', 'user_id' => $user_id];
}

/**
 * Send parent invitation email
 */
function wcb_send_parent_invitation_email($parent_name, $parent_email, $token, $children_names = '', $personal_message = '') {
    $registration_url = home_url('/parent-registration/?token=' . $token);

    $subject = 'Invitation to West City Boxing Family Dashboard';

    $message = "Hi {$parent_name},\n\n";
    $message .= "You've been invited to access the West City Boxing Family Dashboard!\n\n";

    if (!empty($children_names)) {
        $message .= "This will allow you to manage memberships for:\n";
        foreach (explode("\n", $children_names) as $child_name) {
            if (trim($child_name)) {
                $message .= "• " . trim($child_name) . "\n";
            }
        }
        $message .= "\n";
    }

    if (!empty($personal_message)) {
        $message .= "Personal message from our team:\n";
        $message .= $personal_message . "\n\n";
    }

    $message .= "To complete your registration and access your family dashboard, please click the link below:\n";
    $message .= $registration_url . "\n\n";
    $message .= "This link will expire in 7 days.\n\n";
    $message .= "If you have any questions, please contact us.\n\n";
    $message .= "Best regards,\n";
    $message .= "West City Boxing Team";

    return wp_mail($parent_email, $subject, $message);
}

/**
 * Create parent invitations table
 */
function wcb_create_parent_invitations_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wcb_parent_invitations';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        parent_name varchar(255) NOT NULL,
        parent_email varchar(255) NOT NULL,
        children_names text,
        personal_message text,
        invitation_token varchar(255) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        invited_by bigint(20) UNSIGNED NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        completed_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY invitation_token (invitation_token),
        KEY parent_email (parent_email),
        KEY status (status)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create table on theme activation
add_action('after_setup_theme', 'wcb_create_parent_invitations_table');

/**
 * Get membership group information based on product name (simpler approach)
 */
function wcb_get_membership_group_info($product_id) {
    // Get the product
    $product = get_post($product_id);
    if (!$product) {
        return null;
    }

    $product_name = $product->post_title;

    // Map product names to groups based on your membership structure
    $group_mappings = [
        // Mini Cadet Boys patterns
        'Mini Cadet Boys' => [
            'group_name' => 'Mini Cadet Boys (9-11 Years) Group 1',
            'group_url' => 'https://westcityboxing.local/plans/mini-cadet-boys-9-11-years-group-1/'
        ],

        // Cadet Boys Group 1 patterns
        'Cadet Boys (12-14 Years) Group 1' => [
            'group_name' => 'Cadet Boys Group 1',
            'group_url' => 'https://westcityboxing.local/plans/cadet-boys-group-1/'
        ],

        // Cadet Boys Group 2 patterns
        'Cadet Boys (12-14 Years) Group 2' => [
            'group_name' => 'Cadet Boys Group 2',
            'group_url' => 'https://westcityboxing.local/plans/cadet-boys-group-2/'
        ],

        // Youth Boys Group 1 patterns
        'Youth Boys (15-18 Years) Group 1' => [
            'group_name' => 'Youth Boys Group 1',
            'group_url' => 'https://westcityboxing.local/plans/youth-boys-group-1/'
        ],

        // Youth Boys Group 2 patterns
        'Youth Boys (15-18 Years) Group 2' => [
            'group_name' => 'Youth Boys Group 2',
            'group_url' => 'https://westcityboxing.local/plans/youth-boys-group-2/'
        ],

        // Mini Cadets Girls patterns
        'Mini Cadet Girls' => [
            'group_name' => 'Mini Cadets Girls Group 1',
            'group_url' => 'https://westcityboxing.local/plans/mini-cadets-girls-group-1/'
        ],

        // Youth Girls patterns
        'Youth Girls (13-18 Years) Group 1' => [
            'group_name' => 'Youth Girls Group 1',
            'group_url' => 'https://westcityboxing.local/plans/youth-girls-group-1/'
        ]
    ];

    // Try to match the product name to a group
    foreach ($group_mappings as $pattern => $group_info) {
        if (strpos($product_name, $pattern) !== false) {
            return $group_info;
        }
    }

    // Fallback: try to extract group from product name
    if (strpos($product_name, 'Mini Cadet Boys') !== false) {
        return $group_mappings['Mini Cadet Boys'];
    } elseif (strpos($product_name, 'Cadet Boys') !== false && strpos($product_name, 'Group 1') !== false) {
        return $group_mappings['Cadet Boys (12-14 Years) Group 1'];
    } elseif (strpos($product_name, 'Cadet Boys') !== false && strpos($product_name, 'Group 2') !== false) {
        return $group_mappings['Cadet Boys (12-14 Years) Group 2'];
    } elseif (strpos($product_name, 'Youth Boys') !== false && strpos($product_name, 'Group 1') !== false) {
        return $group_mappings['Youth Boys (15-18 Years) Group 1'];
    } elseif (strpos($product_name, 'Youth Boys') !== false && strpos($product_name, 'Group 2') !== false) {
        return $group_mappings['Youth Boys (15-18 Years) Group 2'];
    } elseif (strpos($product_name, 'Mini Cadet Girls') !== false) {
        return $group_mappings['Mini Cadet Girls'];
    } elseif (strpos($product_name, 'Youth Girls') !== false) {
        return $group_mappings['Youth Girls (13-18 Years) Group 1'];
    }

    return null;
}

/**
 * Detect if member pays via Stripe or manually
 */
function wcb_detect_payment_type($transaction) {
    global $wpdb;

    // Check if transaction has a gateway (non-empty gateway = Stripe, empty/manual = manual payment)
    $gateway = $transaction->gateway ?? '';

    // If gateway is empty or 'manual', it's a manual payment
    if (empty($gateway) || $gateway === 'manual') {
        return 'manual';
    }

    // If gateway has any value (like sz7gj0-4lm, stmm16-31, etc.), it's likely Stripe
    // Manual payments typically have empty gateway fields
    if (!empty($gateway)) {
        // Double-check by looking for active subscriptions
        $subscription_table = $wpdb->prefix . 'mepr_subscriptions';
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscription_table
             WHERE user_id = %d
             AND product_id = %d
             AND status = 'active'
             ORDER BY created_at DESC
             LIMIT 1",
            $transaction->user_id,
            $transaction->product_id
        ));

        // If there's an active subscription, it's definitely Stripe
        if ($subscription) {
            return 'stripe';
        }

        // If gateway exists but no active subscription, check if it's a one-time Stripe payment
        // or if subscription expired but was originally Stripe
        $subscription_history = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscription_table
             WHERE user_id = %d
             AND product_id = %d
             ORDER BY created_at DESC
             LIMIT 1",
            $transaction->user_id,
            $transaction->product_id
        ));

        if ($subscription_history) {
            return 'stripe';
        }
    }

    return 'manual';
}

/**
 * Get Stripe subscription information for a user/product
 */
function wcb_get_stripe_subscription_info($user_id, $product_id) {
    global $wpdb;

    $subscription_table = $wpdb->prefix . 'mepr_subscriptions';

    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $subscription_table
         WHERE user_id = %d
         AND product_id = %d
         AND status = 'active'
         AND gateway LIKE '%stripe%'
         ORDER BY created_at DESC
         LIMIT 1",
        $user_id,
        $product_id
    ));

    if (!$subscription) {
        return null;
    }

    // Get subscription details
    $period_type = get_post_meta($product_id, '_mepr_product_period_type', true);
    $period = get_post_meta($product_id, '_mepr_product_period', true);
    $price = get_post_meta($product_id, '_mepr_product_price', true);

    // Calculate next payment date
    $next_payment = null;
    if ($subscription->expires_at && $subscription->expires_at !== '0000-00-00 00:00:00') {
        $next_payment = $subscription->expires_at;
    }

    // Format billing frequency
    $billing_frequency = 'Unknown';
    if ($period_type === 'weeks') {
        $billing_frequency = $period == 1 ? 'Weekly' : "Every {$period} weeks";
    } elseif ($period_type === 'months') {
        $billing_frequency = $period == 1 ? 'Monthly' : "Every {$period} months";
    } elseif ($period_type === 'years') {
        $billing_frequency = $period == 1 ? 'Yearly' : "Every {$period} years";
    }

    return [
        'subscription_id' => $subscription->id,
        'next_payment' => $next_payment,
        'amount' => $price,
        'frequency' => $billing_frequency,
        'status' => $subscription->status,
        'gateway' => $subscription->gateway
    ];
}

/**
 * Get session count for a specific child
 */
function wcb_get_child_session_count($child_id) {
    global $wpdb;

    // Get child details
    $child_table = $wpdb->prefix . 'wcb_family_children';
    $child = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $child_table WHERE id = %d",
        $child_id
    ));

    if (!$child) {
        return 0;
    }

    // Count sessions where this child attended
    // This is a simplified version - you might need to adjust based on your session storage
    $session_count = 0;

    // Check regular session logs
    $sessions = get_posts([
        'post_type' => 'session_log',
        'numberposts' => -1,
        'meta_query' => [
            [
                'key' => 'attendees',
                'value' => $child->child_name,
                'compare' => 'LIKE'
            ]
        ]
    ]);

    $session_count += count($sessions);

    return $session_count;
}

/**
 * Get recent sessions for a child
 */
function wcb_get_child_recent_sessions($child_id, $limit = 5) {
    global $wpdb;

    // Get child details
    $child_table = $wpdb->prefix . 'wcb_family_children';
    $child = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $child_table WHERE id = %d",
        $child_id
    ));

    if (!$child) {
        return [];
    }

    // Get recent sessions
    $sessions = get_posts([
        'post_type' => 'session_log',
        'numberposts' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => [
            [
                'key' => 'attendees',
                'value' => $child->child_name,
                'compare' => 'LIKE'
            ]
        ]
    ]);

    $recent_sessions = [];
    foreach ($sessions as $session) {
        $recent_sessions[] = (object) [
            'session_date' => $session->post_date,
            'session_time' => get_field('session_time', $session->ID) ?: 'Time TBA',
            'session_id' => $session->ID
        ];
    }

    return $recent_sessions;
}

/**
 * Get child progress data
 */
function wcb_get_child_progress($child_id) {
    // This is a placeholder - you can implement actual progress tracking
    // For now, return sample data based on session count
    $session_count = wcb_get_child_session_count($child_id);

    if ($session_count >= 20) {
        return [
            'percentage' => 80,
            'level' => 'Advanced'
        ];
    } elseif ($session_count >= 10) {
        return [
            'percentage' => 60,
            'level' => 'Intermediate'
        ];
    } elseif ($session_count >= 5) {
        return [
            'percentage' => 40,
            'level' => 'Beginner+'
        ];
    } else {
        return [
            'percentage' => 20,
            'level' => 'Beginner'
        ];
    }
}

/**
 * Get family monthly cost
 */
function wcb_get_family_monthly_cost($family_membership) {
    // Get the MemberPress product
    $product = get_post($family_membership->membership_product_id);

    if (!$product) {
        return 0;
    }

    // Get product price (this is simplified - you might need to get actual MemberPress pricing)
    $price = get_post_meta($product->ID, '_mepr_product_price', true);

    // Convert weekly to monthly if needed
    $period = get_post_meta($product->ID, '_mepr_product_period_type', true);

    if ($period === 'weeks') {
        return $price * 4.33; // Average weeks per month
    }

    return $price ?: 0;
}
