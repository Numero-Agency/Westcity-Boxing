<?php
/**
 * Fix Group 2 Mapping Issue
 * Members of Youth Boys Group 2 are getting Group 1 links due to mapping order
 */

// Allow access for fixing
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

// FIXED: More specific patterns first (Group 2 before Group 1)
function determine_member_group($membership_name) {
    // Handle waitlist memberships by removing "Waitlist" and finding the base group
    $clean_name = str_replace(' Waitlist', '', $membership_name);

    // IMPORTANT: Order matters! More specific patterns (with "Group 2") must come BEFORE general patterns
    $group_mappings = [
        // Group 2 mappings first (more specific)
        'Cadet Boys Group 2' => ['Cadet Boys (12-14 Years) Group 2', 'Cadet Boys Group 2'],
        'Youth Boys Group 2' => ['Youth Boys (15-18 Years) Group 2', 'Youth Boys Group 2'],

        // Group 1 mappings second (more general)
        'Mini Cadet Boys (9-11 Years) Group 1' => ['Mini Cadet Boys (9-11 Years)', 'Mini Cadet Boys'],
        'Cadet Boys Group 1' => ['Cadet Boys (12-14 Years)', 'Cadet Boys Group 1'],
        'Youth Boys Group 1' => ['Youth Boys (15-18 Years)', 'Youth Boys Group 1'],
        'Mini Cadets Girls Group 1' => ['Mini Cadet Girls (9-12 Years)', 'Mini Cadets Girls', 'Mini Cadet Girls'],
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

$txn_table = $wpdb->prefix . 'mepr_transactions';

echo "<h2>Fix Group 2 Mapping Issue</h2>";
echo "<p>Finding and fixing members who should be in Group 2 but are assigned to Group 1</p>";

// Find members with Youth Boys Group 2 or Cadet Boys Group 2 memberships
$group2_members = $wpdb->get_results("
    SELECT DISTINCT u.ID, u.display_name, u.user_email, um.meta_value as current_message,
           GROUP_CONCAT(p.post_title SEPARATOR ' | ') as memberships
    FROM {$wpdb->users} u
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    JOIN {$txn_table} t ON u.ID = t.user_id
    LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
    WHERE um.meta_key = 'mepr_user_message'
    AND um.meta_value LIKE '%<div style=%'
    AND t.status IN ('confirmed', 'complete')
    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    AND (p.post_title LIKE '%Group 2%' OR p.post_title LIKE '%(15-18 Years) Group 2%' OR p.post_title LIKE '%(12-14 Years) Group 2%')
    GROUP BY u.ID
    ORDER BY u.display_name
");

echo "<h3>Members with Group 2 Memberships Found: " . count($group2_members) . "</h3>";

$fixed_count = 0;

foreach ($group2_members as $member) {
    $memberships = explode(' | ', $member->memberships);
    $should_be_group2 = false;
    $correct_group = '';
    $correct_link = '';

    // Check each membership to see if it should be Group 2
    foreach ($memberships as $membership) {
        $determined_group = determine_member_group($membership);

        if ($determined_group === 'Youth Boys Group 2' || $determined_group === 'Cadet Boys Group 2') {
            $should_be_group2 = true;
            $correct_group = $determined_group;
            $correct_link = $group_links[$determined_group];
            break;
        }
    }

    if ($should_be_group2) {
        // Check if their current message has the wrong group link
        $current_has_wrong_link = false;
        if ($correct_group === 'Youth Boys Group 2' && strpos($member->current_message, 'youth-boys-group-1') !== false) {
            $current_has_wrong_link = true;
        } elseif ($correct_group === 'Cadet Boys Group 2' && strpos($member->current_message, 'cadet-boys-group-1') !== false) {
            $current_has_wrong_link = true;
        }

        if ($current_has_wrong_link) {
            // Update their message with correct Group 2 link
            $group_link_text = str_replace('https://westcityboxing.nz/plans/', '', $correct_link);
            $group_link_text = str_replace('/', '', $group_link_text);
            $group_link_text = ucwords(str_replace('-', ' ', $group_link_text));

            $personalized_message = str_replace('[GROUP_LINK]', $correct_link, $message_template);
            $personalized_message = str_replace('[GROUP_LINK_TEXT]', $group_link_text, $personalized_message);

            $result = update_user_meta($member->ID, 'mepr_user_message', $personalized_message);

            if ($result) {
                echo "<div style='color: green; margin: 2px 0; padding: 8px; background: #e8f5e8; border-radius: 5px;'>";
                echo "✅ <strong>{$member->display_name}</strong><br>";
                echo "<small>Fixed: Group 1 → <strong>{$correct_group}</strong><br>";
                echo "Memberships: " . implode(', ', $memberships) . "</small>";
                echo "</div>";
                $fixed_count++;
            } else {
                echo "<div style='color: red; margin: 2px 0; padding: 8px; background: #ffebee; border-radius: 5px;'>";
                echo "❌ <strong>{$member->display_name}</strong> - Update failed";
                echo "</div>";
            }
        } else {
            echo "<div style='color: blue; margin: 2px 0; padding: 8px; background: #e3f2fd; border-radius: 5px;'>";
            echo "ℹ️ <strong>{$member->display_name}</strong> - Already has correct {$correct_group} link";
            echo "</div>";
        }
    }
}

echo "<h3>Fix Results</h3>";
echo "<p><strong>Members with Group 2 memberships found:</strong> " . count($group2_members) . "</p>";
echo "<p><strong>Members fixed:</strong> {$fixed_count}</p>";

if ($fixed_count > 0) {
    echo "<h4>What Was Fixed:</h4>";
    echo "<ul>";
    echo "<li>✅ <strong>Youth Boys Group 2 members</strong> who had Group 1 links → Now have Group 2 links</li>";
    echo "<li>✅ <strong>Cadet Boys Group 2 members</strong> who had Group 1 links → Now have Group 2 links</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>🔧 Root Cause:</strong> The group mapping function was checking Group 1 patterns before Group 2 patterns. Since 'Youth Boys (15-18 Years) Group 2' contains 'Youth Boys (15-18 Years)', it was matching Group 1 first.</p>";

echo "<p><strong>✅ Solution:</strong> Reordered the mapping so Group 2 patterns are checked before Group 1 patterns.</p>";

echo "<p><strong>📋 Example:</strong></p>";
echo "<ul>";
echo "<li><strong>Before:</strong> 'Youth Boys (15-18 Years) Group 2' → Matched 'Youth Boys Group 1'</li>";
echo "<li><strong>After:</strong> 'Youth Boys (15-18 Years) Group 2' → Correctly matches 'Youth Boys Group 2'</li>";
echo "</ul>";
?>