<?php
/**
 * Create Test Family Membership Data
 * Run this once to create test data for development
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create test family membership data
 */
function wcb_create_test_family_data() {
    global $wpdb;
    
    // Check if we're in a development environment
    if (!current_user_can('administrator')) {
        wp_die('Only administrators can create test data');
    }
    
    // Get current user (admin)
    $current_user_id = get_current_user_id();
    
    // Check if test data already exists
    $existing_family = wcb_get_family_membership_by_parent($current_user_id);
    if ($existing_family) {
        echo '<div class="notice notice-info"><p>Test family membership already exists for your account.</p></div>';
        return;
    }
    
    // Get the family plan membership product
    $family_products = get_posts([
        'post_type' => 'memberpressproduct',
        'numberposts' => -1,
        'meta_query' => [
            [
                'key' => 'post_title',
                'value' => 'Family Plan',
                'compare' => 'LIKE'
            ]
        ]
    ]);
    
    // If no family plan exists, create a dummy product ID
    $family_product_id = !empty($family_products) ? $family_products[0]->ID : 999;
    
    // Create family membership record
    $family_table = $wpdb->prefix . 'wcb_family_memberships';

    // Debug: Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$family_table'");
    if (!$table_exists) {
        echo '<div class="notice notice-error">
            <p><strong>Error:</strong> Table ' . $family_table . ' does not exist!</p>
            <p>Please create the database tables first.</p>
        </div>';
        return;
    }
    
    $family_result = $wpdb->insert(
        $family_table,
        [
            'parent_user_id' => $current_user_id,
            'membership_product_id' => $family_product_id,
            'family_name' => 'Test Family',
            'max_children' => 3,
            'status' => 'active'
        ],
        [
            '%d',
            '%d',
            '%s',
            '%d',
            '%s'
        ]
    );

    if ($family_result === false) {
        echo '<div class="notice notice-error">
            <p><strong>Failed to create test family membership.</strong></p>
            <p><strong>Debug Info:</strong></p>
            <ul>
                <li>Table: ' . $family_table . '</li>
                <li>User ID: ' . $current_user_id . '</li>
                <li>Product ID: ' . $family_product_id . '</li>
                <li>Database Error: ' . $wpdb->last_error . '</li>
                <li>Last Query: ' . $wpdb->last_query . '</li>
            </ul>
        </div>';
        return;
    }
    
    $family_id = $wpdb->insert_id;
    
    // Create test children
    $children_table = $wpdb->prefix . 'wcb_family_children';
    
    $test_children = [
        [
            'child_name' => 'Alex Smith',
            'child_age' => 8,
            'child_email' => '',
            'program_group' => 'cadet_boys_1'
        ],
        [
            'child_name' => 'Emma Smith',
            'child_age' => 12,
            'child_email' => 'emma@example.com',
            'program_group' => 'youth_girls_1'
        ]
    ];
    
    foreach ($test_children as $child_data) {
        $wpdb->insert(
            $children_table,
            [
                'family_id' => $family_id,
                'child_name' => $child_data['child_name'],
                'child_age' => $child_data['child_age'],
                'child_email' => $child_data['child_email'],
                'program_group' => $child_data['program_group'],
                'status' => 'active'
            ],
            [
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );
    }
    
    echo '<div class="notice notice-success">
        <p><strong>Test family membership created successfully!</strong></p>
        <ul>
            <li>Family ID: ' . $family_id . '</li>
            <li>Parent: ' . wp_get_current_user()->display_name . '</li>
            <li>Max Children: 3</li>
            <li>Children Added: 2 (Alex Smith - Cadet Boys 1, Emma Smith - Youth Girls 1)</li>
        </ul>
        <p><a href="/family-registration/" class="button button-primary">View Family Registration Form</a></p>
    </div>';
}

/**
 * Remove test family membership data
 */
function wcb_remove_test_family_data() {
    global $wpdb;
    
    if (!current_user_can('administrator')) {
        wp_die('Only administrators can remove test data');
    }
    
    $current_user_id = get_current_user_id();
    
    // Get family membership
    $family = wcb_get_family_membership_by_parent($current_user_id);
    
    if (!$family) {
        echo '<div class="notice notice-info"><p>No test family membership found for your account.</p></div>';
        return;
    }
    
    // Remove children first (due to foreign key constraint)
    $children_table = $wpdb->prefix . 'wcb_family_children';
    $wpdb->delete($children_table, ['family_id' => $family->id], ['%d']);
    
    // Remove family membership
    $family_table = $wpdb->prefix . 'wcb_family_memberships';
    $wpdb->delete($family_table, ['id' => $family->id], ['%d']);
    
    echo '<div class="notice notice-success"><p>Test family membership data removed successfully!</p></div>';
}

/**
 * Admin page for managing test data
 */
function wcb_family_test_data_admin_page() {
    ?>
    <div class="wrap">
        <h1>Family Membership Test Data</h1>
        
        <?php
        if (isset($_POST['create_test_data'])) {
            wcb_create_test_family_data();
        }
        
        if (isset($_POST['remove_test_data'])) {
            wcb_remove_test_family_data();
        }
        ?>
        
        <div class="card">
            <h2>Create Test Family Membership</h2>
            <p>This will create a test family membership for your admin account with 2 sample children.</p>
            
            <form method="post">
                <p>
                    <input type="submit" name="create_test_data" class="button button-primary" value="Create Test Data">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>Remove Test Data</h2>
            <p>This will remove all test family membership data for your account.</p>
            
            <form method="post">
                <p>
                    <input type="submit" name="remove_test_data" class="button button-secondary" value="Remove Test Data">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>Current Family Membership Status</h2>
            <?php
            $current_user_id = get_current_user_id();
            $family = wcb_get_family_membership_by_parent($current_user_id);
            
            if ($family) {
                $children = wcb_get_family_children($family->id);
                echo '<p><strong>Status:</strong> Active family membership found</p>';
                echo '<p><strong>Family ID:</strong> ' . $family->id . '</p>';
                echo '<p><strong>Max Children:</strong> ' . $family->max_children . '</p>';
                echo '<p><strong>Current Children:</strong> ' . count($children) . '</p>';
                
                if (!empty($children)) {
                    echo '<h4>Children:</h4><ul>';
                    foreach ($children as $child) {
                        echo '<li>' . esc_html($child->child_name) . ' (' . esc_html($child->program_group) . ')</li>';
                    }
                    echo '</ul>';
                }
            } else {
                echo '<p><strong>Status:</strong> No family membership found</p>';
            }
            ?>
        </div>
        
        <div class="card">
            <h2>Test Family Dashboard Functions</h2>
            <?php
            if (isset($_POST['test_link_child'])) {
                $test_email = sanitize_text_field($_POST['test_email']);
                if ($test_email) {
                    echo '<h4>Testing Link Child Function:</h4>';
                    $result = wcb_link_child_to_parent(get_current_user_id(), $test_email);
                    echo '<pre>' . print_r($result, true) . '</pre>';
                }
            }
            ?>

            <form method="post">
                <p>
                    <label>Test Email/Username:</label><br>
                    <input type="text" name="test_email" placeholder="Enter email or username to test" style="width: 300px;">
                </p>
                <p>
                    <input type="submit" name="test_link_child" class="button" value="Test Link Child Function">
                </p>
            </form>

            <p><a href="/family-dashboard/" class="button">View Family Dashboard</a></p>

            <h4>Debug Payment Types</h4>
            <?php
            if (isset($_POST['debug_payments'])) {
                $current_user_id = get_current_user_id();
                $children = wcb_get_parent_children($current_user_id);

                echo '<div style="background: #f0f0f0; padding: 15px; margin: 10px 0; font-family: monospace;">';
                echo '<strong>Payment Type Debug:</strong><br><br>';

                foreach ($children as $child) {
                    $membership_status = wcb_get_child_membership_status($child);

                    // Get raw transaction data for debugging
                    global $wpdb;
                    $txn_table = $wpdb->prefix . 'mepr_transactions';
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
                    ", $child->ID, $wcb_mentoring_id));

                    echo '<strong>' . $child->display_name . ':</strong><br>';
                    echo '- Status: ' . $membership_status['status'] . '<br>';
                    echo '- Status Text: ' . $membership_status['status_text'] . '<br>';
                    echo '- Payment Type: ' . ($membership_status['payment_type'] ?? 'Not set') . '<br>';
                    echo '- Program: ' . $membership_status['program_name'] . '<br>';

                    if ($transaction) {
                        echo '- Transaction Gateway: ' . ($transaction->gateway ?? 'Not set') . '<br>';
                        echo '- Transaction ID: ' . $transaction->id . '<br>';
                        echo '- Product ID: ' . $transaction->product_id . '<br>';
                    }

                    // Check subscriptions
                    $subscription_table = $wpdb->prefix . 'mepr_subscriptions';
                    $subscriptions = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM $subscription_table WHERE user_id = %d AND product_id = %d ORDER BY created_at DESC LIMIT 3",
                        $child->ID,
                        $transaction ? $transaction->product_id : 0
                    ));

                    if ($subscriptions) {
                        echo '- Subscriptions found: ' . count($subscriptions) . '<br>';
                        foreach ($subscriptions as $sub) {
                            echo '  - Sub Gateway: ' . $sub->gateway . ' (Status: ' . $sub->status . ')<br>';
                        }
                    } else {
                        echo '- No subscriptions found<br>';
                    }

                    if (isset($membership_status['subscription_info'])) {
                        echo '- Subscription Info: ' . print_r($membership_status['subscription_info'], true) . '<br>';
                    }
                    echo '<br>';
                }
                echo '</div>';
            }
            ?>

            <form method="post">
                <p>
                    <input type="submit" name="debug_payments" class="button" value="Debug Payment Types">
                    <input type="submit" name="debug_groups" class="button" value="Debug Group Memberships">
                </p>
            </form>

            <?php
            if (isset($_POST['debug_groups'])) {
                echo '<div style="background: #f0f0f0; padding: 15px; margin: 10px 0; font-family: monospace;">';
                echo '<strong>Group Membership Debug:</strong><br><br>';

                // Check all groups
                $groups = get_posts([
                    'post_type' => 'memberpressgroup',
                    'numberposts' => -1,
                    'post_status' => 'publish'
                ]);

                foreach ($groups as $group) {
                    echo '<strong>Group: ' . $group->post_title . ' (ID: ' . $group->ID . ')</strong><br>';

                    // Method 1: Check meta query
                    $group_memberships_meta = get_posts([
                        'post_type' => 'memberpressproduct',
                        'numberposts' => -1,
                        'post_status' => 'publish',
                        'meta_query' => [
                            [
                                'key' => '_mepr_product_group',
                                'value' => $group->ID,
                                'compare' => '='
                            ]
                        ]
                    ]);

                    echo 'Method 1 (_mepr_product_group meta): ' . count($group_memberships_meta) . ' memberships<br>';
                    foreach ($group_memberships_meta as $membership) {
                        echo '  - ' . $membership->post_title . ' (ID: ' . $membership->ID . ')<br>';
                    }

                    // Method 2: Check all memberships and their group meta
                    $all_memberships = get_posts([
                        'post_type' => 'memberpressproduct',
                        'numberposts' => -1,
                        'post_status' => 'publish'
                    ]);

                    echo 'Method 2 (checking all memberships for group ' . $group->ID . '):<br>';
                    foreach ($all_memberships as $membership) {
                        $membership_group = get_post_meta($membership->ID, '_mepr_product_group', true);
                        if ($membership_group == $group->ID) {
                            echo '  - ' . $membership->post_title . ' (Group Meta: ' . $membership_group . ')<br>';
                        }
                    }

                    // Method 3: Check if memberships contain group name in title
                    echo 'Method 3 (title contains group name):<br>';
                    foreach ($all_memberships as $membership) {
                        if (strpos($membership->post_title, $group->post_title) !== false) {
                            echo '  - ' . $membership->post_title . '<br>';
                        }
                    }

                    echo '<br>';
                }
                echo '</div>';
            }
            ?>

            <h4>Test Email System</h4>
            <?php
            if (isset($_POST['test_email'])) {
                $test_email = sanitize_email($_POST['test_email_address']);
                if ($test_email) {
                    $subject = 'Test Email from West City Boxing';
                    $message = 'This is a test email to verify email functionality.';
                    $sent = wp_mail($test_email, $subject, $message);

                    if ($sent) {
                        echo '<div style="color: green;">✅ Test email sent successfully to ' . $test_email . '</div>';
                    } else {
                        echo '<div style="color: red;">❌ Failed to send test email</div>';
                    }
                }
            }
            ?>

            <form method="post">
                <p>
                    <label>Test Email Address:</label><br>
                    <input type="email" name="test_email_address" placeholder="Enter email to test" style="width: 300px;">
                </p>
                <p>
                    <input type="submit" name="test_email" class="button" value="Send Test Email">
                </p>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Add admin menu for test data
 */
function wcb_add_family_test_admin_menu() {
    add_submenu_page(
        'users.php',
        'Family Test Data',
        'Family Test Data',
        'manage_options',
        'family-test-data',
        'wcb_family_test_data_admin_page'
    );
}
add_action('admin_menu', 'wcb_add_family_test_admin_menu');
