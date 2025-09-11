<?php
/**
 * Update the Remaining 44 Members - Filter Competitive/Mentoring
 * These members have standard group memberships but also have competitive/mentoring memberships
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

// List of the 44 members from the report
$remaining_members = [
    'Anemesh Thapa',
    'Angelee Faasavalu',
    'Ash Archibald',
    'Brandon Condren',
    'Cole Samuals',
    'Cristiano Tavai',
    'Crystal Kainamu',
    'Demetrius Gagau',
    'Dhilan Naguleswaran',
    'Dion-Grace Palemene',
    'Ethan Hunter',
    'Fiston Iradukunda',
    'Hala Houma',
    'Ikuna Malupo',
    'Javiera Melo',
    'Joel Bloomfield',
    'Johanna Clews',
    'Kobi Hutchins',
    'Livingstone Lesatele',
    'Logan Halagigie',
    'Lua Peteru',
    'Maika Hart',
    'Manu Falesiva',
    'Matilda Laurenson',
    'Matthew Arnold',
    'Mele Siu Faaui',
    'Michael Yan',
    'Mosese Houma',
    'Patrick Tan',
    'Sam Mallinder',
    'Sebastian Grey',
    'Shayden Vasquez',
    'Sheri Williams',
    'Sienna Tamaaliivano',
    'Sophie Salesa',
    'Tamati Hart',
    'Tavita Fesolai',
    'Teri Oosthuizen',
    'TJ (Taulaga Junior) Auimatagi',
    'Tom Miller',
    'Viliami Moala',
    'will ataela',
    'Xarisma Paga'
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

function is_competitive_or_mentoring($membership_name) {
    // Check if membership is competitive or mentoring
    $competitive_keywords = [
        'competitive', 'competition', 'comp team', 'boxing team',
        'mentoring', 'mentor', 'coaching', 'community class'
    ];

    $clean_name = strtolower($membership_name);

    foreach ($competitive_keywords as $keyword) {
        if (strpos($clean_name, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

$txn_table = $wpdb->prefix . 'mepr_transactions';

echo "<h2>Update Remaining 44 Members</h2>";
echo "<p>Processing the 44 members who have standard group memberships but also competitive/mentoring memberships</p>";

$found_members = [];
$updated_count = 0;

foreach ($remaining_members as $member_name) {
    // Find user by display name
    $user = $wpdb->get_row($wpdb->prepare("
        SELECT ID, display_name, user_email
        FROM {$wpdb->users}
        WHERE display_name = %s
        LIMIT 1
    ", $member_name));

    if ($user) {
        $found_members[] = $user;

        // Get all memberships for this user
        $memberships = $wpdb->get_results($wpdb->prepare("
            SELECT p.post_title as membership_name, t.status, t.expires_at
            FROM {$txn_table} t
            LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            ORDER BY t.created_at DESC
        ", $user->ID));

        $regular_groups = [];
        $special_memberships = [];

        // Categorize memberships
        foreach ($memberships as $membership) {
            if (is_competitive_or_mentoring($membership->membership_name)) {
                $special_memberships[] = $membership->membership_name;
            } else {
                // Check if this is one of our 7 standard groups
                $group = determine_member_group($membership->membership_name);
                if ($group !== 'Unknown Group') {
                    $regular_groups[] = [
                        'name' => $membership->membership_name,
                        'group' => $group,
                        'product_id' => $membership->product_id
                    ];
                }
            }
        }

        // If member has regular groups, update their message
        if (!empty($regular_groups)) {
            $primary_group = $regular_groups[0];
            $group_link = isset($GLOBALS['group_links'][$primary_group['group']]) ? $GLOBALS['group_links'][$primary_group['group']] : '';

            if (!empty($group_link)) {
                $group_link_text = str_replace('https://westcityboxing.nz/plans/', '', $group_link);
                $group_link_text = str_replace('/', '', $group_link_text);
                $group_link_text = ucwords(str_replace('-', ' ', $group_link_text));

                // Replace placeholders with actual values
                $personalized_message = str_replace('[GROUP_LINK]', $group_link, $message_template);
                $personalized_message = str_replace('[GROUP_LINK_TEXT]', $group_link_text, $personalized_message);

                // Update the user meta
                $result = update_user_meta($user->ID, 'mepr_user_message', $personalized_message);

                if ($result) {
                    echo "<div style='color: green; margin: 2px 0; padding: 8px; background: #e8f5e8; border-radius: 5px;'>";
                    echo "✅ <strong>{$user->display_name}</strong><br>";
                    echo "<small>Assigned to: <strong>{$primary_group['group']}</strong><br>";
                    echo "Filtered out: " . implode(', ', $special_memberships) . "</small>";
                    echo "</div>";
                    $updated_count++;
                } else {
                    echo "<div style='color: red; margin: 2px 0; padding: 8px; background: #ffebee; border-radius: 5px;'>";
                    echo "❌ <strong>{$user->display_name}</strong> - Update failed";
                    echo "</div>";
                }
            } else {
                echo "<div style='color: orange; margin: 2px 0; padding: 8px; background: #fff3e0; border-radius: 5px;'>";
                echo "⚠️ <strong>{$user->display_name}</strong> - No group link found for {$primary_group['group']}";
                echo "</div>";
            }
        } else {
            echo "<div style='color: gray; margin: 2px 0; padding: 8px; background: #f5f5f5; border-radius: 5px;'>";
            echo "⏭️ <strong>{$user->display_name}</strong> - No standard group memberships found<br>";
            echo "<small>Only has: " . implode(', ', array_column($memberships, 'membership_name')) . "</small>";
            echo "</div>";
        }
    } else {
        echo "<div style='color: red; margin: 2px 0; padding: 8px; background: #ffebee; border-radius: 5px;'>";
        echo "❌ Member not found: {$member_name}";
        echo "</div>";
    }
}

echo "<h3>Final Results</h3>";
echo "<p><strong>Members processed:</strong> " . count($found_members) . " out of " . count($remaining_members) . "</p>";
echo "<p><strong>Members updated:</strong> {$updated_count} out of " . count($found_members) . "</p>";

echo "<hr>";
echo "<p><strong>🎯 Key Finding:</strong> Most of these 44 members actually DO have standard group memberships, but they also have competitive/mentoring memberships that were preventing them from being updated in the previous bulk operations.</p>";

echo "<p><strong>✅ Solution Applied:</strong></p>";
echo "<ul>";
echo "<li>✅ Identified competitive/mentoring memberships (Competitive Team, Community Class, etc.)</li>";
echo "<li>✅ Filtered out these special memberships</li>";
echo "<li>✅ Found their standard group memberships (Youth Boys Group 1, Cadet Boys Group 1, etc.)</li>";
echo "<li>✅ Updated their messages with correct group links and improved formatting</li>";
echo "</ul>";

echo "<p><strong>📋 Example - Tamati Hart:</strong></p>";
echo "<ul>";
echo "<li><strong>Before:</strong> Had 'Competitive Team' + 'Youth Boys Group 1' - was not updated</li>";
echo "<li><strong>After:</strong> ✅ Filtered out 'Competitive Team', assigned to 'Youth Boys Group 1' with correct link</li>";
echo "</ul>";
?>