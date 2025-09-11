<?php
/**
 * Update Message Format for Successfully Updated Members
 * Updates the 205 members who got messages in the previous bulk update
 * with improved formatting, clickable links, and styled note
 */

// Allow access for bulk update
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

global $wpdb;

// New message template with improved formatting
$message_template = '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
    <p style="margin-bottom: 20px;">Dear Members,</p>

    <p style="margin-bottom: 20px;">To complete your membership registration and set up your billing cycle, please follow these steps:</p>

    <ol style="margin-bottom: 20px; padding-left: 20px;">
        <li style="margin-bottom: 10px;"><strong>Step 1</strong> - Go to the <a href="[GROUP_LINK]" style="color: #007cba; text-decoration: none; font-weight: bold;" target="_blank">[GROUP_LINK_TEXT]</a></li>
        <li style="margin-bottom: 10px;"><strong>Step 2</strong> - Select your preferred Billing Cycle</li>
        <li style="margin-bottom: 10px;"><strong>Step 3</strong> - Fill in all required information
            <br><span style="color: #d32f2f; font-weight: bold;">• Important: For the email field, use the same email address you use to log into the member portal (your student email)</span></li>
        <li style="margin-bottom: 10px;"><strong>Step 4</strong> - Select Credit/Debit Card as your payment method and complete the online payment</li>
        <li style="margin-bottom: 10px;"><strong>Step 5</strong> - Once payment is processed, your paid subscription will appear in the member portal alongside your existing entry</li>
    </ol>

    <p style="margin-bottom: 20px;">After completing these steps, you\'ll see your new paid subscription reflected in your member portal, confirming your active membership status. Please note that payments will be automatically charged according to the billing cycle you selected.</p>

    <p style="margin-bottom: 20px;">Thank you for your prompt attention to this matter!</p>

    <p style="margin-bottom: 20px;"><strong>West City Academy</strong><br>
    <a href="mailto:admin@westcityboxing.nz" style="color: #007cba;">admin@westcityboxing.nz</a></p>

    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin-top: 20px;">
        <p style="margin: 0; font-weight: bold; color: #856404;"><strong>⚠️ Please note:</strong> You may currently see an active subscription in your member portal showing "Lifetime" in the Date column and "Free for a Month" or "Free" in the Terms column. This is the manual activation we completed to add you to our system. However, you still need to complete your online payment to fully activate your membership.</p>
    </div>
</div>';

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

function get_successfully_updated_members() {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get members who have personalized messages (from previous bulk update)
    $members_with_messages = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.display_name, u.user_email, um.meta_value as current_message
        FROM {$wpdb->users} u
        JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE um.meta_key = 'mepr_user_message'
        AND um.meta_value != ''
        AND um.meta_value NOT LIKE '%Test%'
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id NOT IN (1738, 1932) -- Exclude mentoring and competitive
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ");

    $members_with_groups = [];

    foreach ($members_with_messages as $member) {
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

            if (!empty($group_link)) {
                $members_with_groups[] = [
                    'user_id' => $member->ID,
                    'name' => $member->display_name,
                    'email' => $member->user_email,
                    'membership' => $membership->membership_name,
                    'group' => $member_group,
                    'group_link' => $group_link,
                    'current_message' => $member->current_message
                ];
            }
        }
    }

    return $members_with_groups;
}

// Get members who were successfully updated in the previous bulk update
$members_to_update = get_successfully_updated_members();

echo "<h2>Update Message Format - Successfully Updated Members</h2>";
echo "<p>Processing " . count($members_to_update) . " members for message format update</p>";

if (!empty($members_to_update)) {
    echo "<h3>Processing Members...</h3>";
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;'>";

    $updated_count = 0;
    $errors = [];
    $group_stats = [];

    foreach ($members_to_update as $member) {
        $group_link = $member['group_link'];
        $group_link_text = str_replace('https://westcityboxing.nz/plans/', '', $group_link);
        $group_link_text = str_replace('/', '', $group_link_text);
        $group_link_text = ucwords(str_replace('-', ' ', $group_link_text));

        // Replace placeholders with actual values
        $personalized_message = str_replace('[GROUP_LINK]', $group_link, $message_template);
        $personalized_message = str_replace('[GROUP_LINK_TEXT]', $group_link_text, $personalized_message);

        // Update the user meta
        $result = update_user_meta($member['user_id'], 'mepr_user_message', $personalized_message);

        if ($result) {
            // Track group statistics
            if (!isset($group_stats[$member['group']])) {
                $group_stats[$member['group']] = 0;
            }
            $group_stats[$member['group']]++;

            echo "<div style='color: green; margin: 2px 0;'>✅ {$member['name']} - {$member['group']} - Updated</div>";
            $updated_count++;
        } else {
            $errors[] = "Failed to update {$member['name']}";
            echo "<div style='color: red; margin: 2px 0;'>❌ {$member['name']} - Update failed</div>";
        }
    }

    echo "</div>";

    echo "<h3>Final Results</h3>";
    echo "<p><strong>Successfully updated:</strong> {$updated_count} out of " . count($members_to_update) . " members</p>";

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
        foreach ($errors as $error) {
            echo "<div>{$error}</div>";
        }
        echo "</div>";
    }

    echo "<hr>";
    echo "<h3>Message Preview</h3>";
    echo "<div style='border: 1px solid #ddd; padding: 15px; background: #f9f9f9; border-radius: 5px;'>";
    echo "<p><strong>Sample message for a member:</strong></p>";
    $sample_message = str_replace('[GROUP_LINK]', 'https://westcityboxing.nz/plans/youth-boys-group-1/', $message_template);
    $sample_message = str_replace('[GROUP_LINK_TEXT]', 'Youth Boys Group 1', $sample_message);
    echo $sample_message;
    echo "</div>";

} else {
    echo "<p>No members found with existing personalized messages to update</p>";
}

echo "<hr>";
echo "<p><strong>⚠️  IMPORTANT:</strong> This script has updated the message format for all successfully updated members. Please verify the results by logging in as a test member.</p>";
echo "<p><strong>Features of the new format:</strong></p>";
echo "<ul>";
echo "<li>✅ Clickable group links with proper styling</li>";
echo "<li>✅ Bold warning note at the bottom</li>";
echo "<li>✅ Improved typography and spacing</li>";
echo "<li>✅ Responsive design with max-width</li>";
echo "<li>✅ Color-coded important information</li>";
echo "</ul>";
?>