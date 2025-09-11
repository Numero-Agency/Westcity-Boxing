<?php
/**
 * Test Bulk Update MemberPress Account Messages - Process 3 Members Only
 */

// Allow access for testing
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

// Skip admin check for testing
// if (!current_user_can('administrator')) {
//     die('Admin access required');
// }

global $wpdb;

// Base message template
$message_template = 'Dear Members,

Please note: You may currently see an active subscription in your member portal showing "Lifetime" in the Date column and "Free for a Month" or "Free" in the Terms column. This is the manual activation we completed to add you to our system. However, you still need to complete your online payment to fully activate your membership.

To complete your membership registration and set up your billing cycle, please follow these steps:

Step 1 - Go to the [GROUP_LINK]
Step 2 - Select your preferred Billing Cycle
Step 3 - Fill in all required information
    • Important: For the email field, use the same email address you use to log into the member portal (your student email)
Step 4 - Select Credit/Debit Card as your payment method and complete the online payment
Step 5 - Once payment is processed, your paid subscription will appear in the member portal alongside your existing entry

After completing these steps, you\'ll see your new paid subscription reflected in your member portal, confirming your active membership status. Please note that payments will be automatically charged according to the billing cycle you selected.

Thank you for your prompt attention to this matter!

West City Academy
admin@westcityboxing.nz';

// Group link mappings
$group_links = [
    'Mini Cadet Boys (9-11 Years) Group 1' => 'https://westcityboxing.nz/plans/mini-cadet-boys-9-11-years-group-1/',
    'Cadet Boys Group 1' => 'https://westcityboxing.nz/plans/cadet-boys-group-1/',
    'Cadet Boys Group 2' => 'https://westcityboxing.nz/plans/cadet-boys-group-2/',
    'Youth Boys Group 1' => 'https://westcityboxing.nz/plans/youth-boys-group-1/',
    'Youth Boys Group 2' => 'https://westcityboxing.nz/plans/youth-boys-group-2/',
    'Mini Cadets Girls Group 1' => 'https://westcityboxing.nz/plans/mini-cadets-girls-group-1/',
    'Youth Girls Group 1' => 'https://westcityboxing.nz/plans/youth-girls-group-1/'
];

function determine_member_group($membership_name) {
    // Handle waitlist memberships by removing "Waitlist" and finding the base group
    $clean_name = str_replace(' Waitlist', '', $membership_name);

    $group_mappings = [
        'Mini Cadet Boys (9-11 Years) Group 1' => ['Mini Cadet Boys (9-11 Years)', 'Mini Cadet Boys'],
        'Cadet Boys Group 1' => ['Cadet Boys (12-14 Years)', 'Cadet Boys Group 1'],
        'Cadet Boys Group 2' => ['Cadet Boys (12-14 Years) Group 2', 'Cadet Boys Group 2'],
        'Youth Boys Group 1' => ['Youth Boys (15-18 Years)', 'Youth Boys Group 1'],
        'Youth Boys Group 2' => ['Youth Boys (15-18 Years) Group 2', 'Youth Boys Group 2'],
        'Mini Cadets Girls Group 1' => ['Mini Cadet Girls (9-12 Years)', 'Mini Cadets Girls'],
        'Youth Girls Group 1' => ['Youth Girls (13-18 Years)', 'Youth Girls Group 1']
    ];

    foreach ($group_mappings as $group => $keywords) {
        foreach ($keywords as $keyword) {
            if (stripos($clean_name, $keyword) !== false) {
                return $group;
            }
        }
    }

    return 'Unknown Group';
}

function get_test_members() {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get first 3 active members from the 7 defined groups
    $active_members = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id NOT IN (1738, 1932) -- Exclude mentoring and competitive
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
        LIMIT 3
    ");

    $members_with_groups = [];

    foreach ($active_members as $member) {
        // Get member's current active membership
        $membership = $wpdb->get_row($wpdb->prepare("
            SELECT p.post_title as membership_name, p.ID as membership_id
            FROM {$txn_table} t
            LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            ORDER BY t.created_at DESC
            LIMIT 1
        ", $member->ID));

        if ($membership) {
            // Determine which group this membership belongs to
            $member_group = determine_member_group($membership->membership_name);
            $group_link = isset($GLOBALS['group_links'][$member_group]) ? $GLOBALS['group_links'][$member_group] : '';

            $members_with_groups[] = [
                'user_id' => $member->ID,
                'name' => $member->display_name,
                'email' => $member->user_email,
                'membership' => $membership->membership_name,
                'group' => $member_group,
                'group_link' => $group_link
            ];
        }
    }

    return $members_with_groups;
}

// Get test members
$test_members = get_test_members();

echo "<h2>Test Bulk Update - 3 Members Only</h2>";
echo "<p>Testing the bulk update functionality with the first 3 active members</p>";

if (!empty($test_members)) {
    echo "<h3>Members to Update:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>Name</th><th>Email</th><th>Current Membership</th><th>Detected Group</th><th>Group Link</th></tr>";

    foreach ($test_members as $member) {
        echo "<tr>";
        echo "<td>{$member['name']}</td>";
        echo "<td>{$member['email']}</td>";
        echo "<td>{$member['membership']}</td>";
        echo "<td>{$member['group']}</td>";
        echo "<td><a href='{$member['group_link']}' target='_blank'>{$member['group_link']}</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3>Updating Messages...</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Name</th><th>Email</th><th>Group</th><th>Status</th><th>Preview</th></tr>";

    $updated_count = 0;
    $errors = [];

    foreach ($test_members as $member) {
        $group_link = $member['group_link'];

        if (empty($group_link)) {
            $errors[] = "No group link found for {$member['name']} ({$member['group']})";
            echo "<tr><td>{$member['name']}</td><td>{$member['email']}</td><td>{$member['group']}</td><td style='color: red;'>ERROR: No link</td><td>N/A</td></tr>";
            continue;
        }

        // Replace placeholder with actual group link
        $personalized_message = str_replace('[GROUP_LINK]', $group_link, $message_template);

        // Update the user meta
        $result = update_user_meta($member['user_id'], 'mepr_user_message', $personalized_message);

        if ($result) {
            // Get preview of first 100 characters
            $preview = substr(strip_tags($personalized_message), 0, 100) . '...';
            echo "<tr><td>{$member['name']}</td><td>{$member['email']}</td><td>{$member['group']}</td><td style='color: green;'>✅ Updated</td><td>{$preview}</td></tr>";
            $updated_count++;
        } else {
            $errors[] = "Failed to update {$member['name']}";
            echo "<tr><td>{$member['name']}</td><td>{$member['email']}</td><td>{$member['group']}</td><td style='color: red;'>❌ Failed</td><td>N/A</td></tr>";
        }
    }

    echo "</table>";

    echo "<h3>Test Summary</h3>";
    echo "<p><strong>Successfully updated:</strong> {$updated_count} out of " . count($test_members) . " test members</p>";

    if (!empty($errors)) {
        echo "<h4>Errors:</h4>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>{$error}</li>";
        }
        echo "</ul>";
    }

    echo "<hr>";
    echo "<h3>Verification</h3>";
    echo "<p>To verify the changes:</p>";
    echo "<ol>";
    echo "<li>Go to WordPress Admin → Users</li>";
    echo "<li>Find one of the test users above</li>";
    echo "<li>Check their profile and look for the 'Custom MemberPress Account Message' field</li>";
    echo "<li>The message should now contain their specific group link</li>";
    echo "</ol>";

    echo "<p><strong>Current message count in database:</strong></p>";
    $message_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'mepr_user_message' AND meta_value != 'Test'");
    echo "<p>Members with updated messages: {$message_count}</p>";

} else {
    echo "<p>No active members found to test with</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Verify the test results above</li>";
echo "<li>If everything looks good, we can proceed with the full bulk update of all 192 members</li>";
echo "<li>The full script will use the same logic but process all active members</li>";
echo "</ol>";
?>