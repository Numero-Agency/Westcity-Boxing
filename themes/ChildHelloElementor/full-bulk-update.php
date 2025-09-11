<?php
/**
 * Full Bulk Update MemberPress Account Messages - All 192 Active Members
 */

// Allow access for bulk update
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

// Skip admin check for bulk update
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

function get_bulk_update_members() {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get ALL active members from the 7 defined groups
    $active_members = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id NOT IN (1738, 1932) -- Exclude mentoring and competitive
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
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

// Get all active members
$all_members = get_bulk_update_members();

echo "<h2>Full Bulk Update - All Active Members</h2>";
echo "<p>Processing " . count($all_members) . " active members for bulk message update</p>";

if (!empty($all_members)) {
    echo "<h3>Processing Members...</h3>";
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;'>";

    $updated_count = 0;
    $errors = [];
    $group_stats = [];

    foreach ($all_members as $member) {
        $group_link = $member['group_link'];

        if (empty($group_link)) {
            $errors[] = "No group link found for {$member['name']} ({$member['group']})";
            echo "<div style='color: red; margin: 2px 0;'>❌ {$member['name']} - No link found ({$member['group']})</div>";
            continue;
        }

        // Replace placeholder with actual group link
        $personalized_message = str_replace('[GROUP_LINK]', $group_link, $message_template);

        // Update the user meta
        $result = update_user_meta($member['user_id'], 'mepr_user_message', $personalized_message);

        if ($result) {
            // Track group statistics
            if (!isset($group_stats[$member['group']])) {
                $group_stats[$member['group']] = 0;
            }
            $group_stats[$member['group']]++;

            echo "<div style='color: green; margin: 2px 0;'>✅ {$member['name']} - {$member['group']}</div>";
            $updated_count++;
        } else {
            $errors[] = "Failed to update {$member['name']}";
            echo "<div style='color: red; margin: 2px 0;'>❌ {$member['name']} - Update failed</div>";
        }
    }

    echo "</div>";

    echo "<h3>Final Results</h3>";
    echo "<p><strong>Successfully updated:</strong> {$updated_count} out of " . count($all_members) . " members</p>";

    if (!empty($group_stats)) {
        echo "<h4>Updates by Group:</h4>";
        echo "<ul>";
        foreach ($group_stats as $group => $count) {
            echo "<li><strong>{$group}:</strong> {$count} members</li>";
        }
        echo "</ul>";
    }

    if (!empty($errors)) {
        echo "<h4>Errors (" . count($errors) . "):</h4>";
        echo "<div style='max-height: 200px; overflow-y: auto; background: #f8f8f8; padding: 10px; border: 1px solid #ccc;'>";
        foreach (array_slice($errors, 0, 10) as $error) { // Show first 10 errors
            echo "<div>{$error}</div>";
        }
        if (count($errors) > 10) {
            echo "<div>... and " . (count($errors) - 10) . " more errors</div>";
        }
        echo "</div>";
    }

    echo "<hr>";
    echo "<h3>Verification</h3>";
    echo "<p>After running this script:</p>";
    echo "<ol>";
    echo "<li><strong>Database check:</strong> " . ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'mepr_user_message' AND meta_value != 'Test' AND meta_value != ''")) . " members now have personalized messages</li>";
    echo "<li><strong>Spot check:</strong> Log in as a few test members to verify their account pages show the personalized messages</li>";
    echo "<li><strong>Link verification:</strong> Click a few group links to ensure they work correctly</li>";
    echo "</ol>";

} else {
    echo "<p>No active members found to update</p>";
}

echo "<hr>";
echo "<p><strong>⚠️  IMPORTANT:</strong> This script has processed all active members. Please verify the results before considering this complete.</p>";
echo "<p><strong>Next steps:</strong> Test member logins and verify the personalized messages appear correctly in their account pages.</p>";
?>