<?php
/**
 * Check Multiple Membership Members - Verify correct group assignment
 */

// Allow access for checking
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

global $wpdb;

echo "<h2>Check Multiple Membership Members</h2>";

// Get members with multiple memberships who have personalized messages
$txn_table = $wpdb->prefix . 'mepr_transactions';

$multiple_membership_members = $wpdb->get_results("
    SELECT u.ID, u.display_name, u.user_email, um.meta_value as message,
           COUNT(t.id) as membership_count,
           GROUP_CONCAT(p.post_title SEPARATOR ' | ') as memberships
    FROM {$wpdb->users} u
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    JOIN {$txn_table} t ON u.ID = t.user_id
    LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
    WHERE um.meta_key = 'mepr_user_message'
    AND um.meta_value != ''
    AND um.meta_value NOT LIKE '%Test%'
    AND t.status IN ('confirmed', 'complete')
    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    GROUP BY u.ID
    HAVING membership_count > 1
    ORDER BY membership_count DESC
");

echo "<p><strong>Members with multiple memberships:</strong> " . count($multiple_membership_members) . "</p>";

if (!empty($multiple_membership_members)) {
    echo "<h3>Analyzing Multiple Membership Cases:</h3>";

    $competitive_filtered = 0;
    $regular_groups_found = 0;

    foreach ($multiple_membership_members as $member) {
        $memberships = explode(' | ', $member->memberships);
        $competitive_memberships = [];
        $regular_memberships = [];

        // Categorize memberships
        foreach ($memberships as $membership) {
            $is_competitive = false;
            $competitive_keywords = [
                'competitive', 'competition', 'comp team', 'boxing team',
                'mentoring', 'mentor', 'coaching'
            ];

            foreach ($competitive_keywords as $keyword) {
                if (stripos($membership, $keyword) !== false) {
                    $is_competitive = true;
                    break;
                }
            }

            if ($is_competitive) {
                $competitive_memberships[] = $membership;
            } else {
                $regular_memberships[] = $membership;
            }
        }

        // Determine which group they should be in
        $assigned_group = 'Unknown';
        $group_link_found = '';

        if (!empty($regular_memberships)) {
            // Check each regular membership against our 7 groups
            foreach ($regular_memberships as $reg_membership) {
                if (stripos($reg_membership, 'Mini Cadet Boys') !== false) {
                    $assigned_group = 'Mini Cadet Boys (9-11 Years) Group 1';
                    $group_link_found = 'https://westcityboxing.nz/plans/mini-cadet-boys-9-11-years-group-1/';
                    break;
                } elseif (stripos($reg_membership, 'Cadet Boys') !== false && stripos($reg_membership, 'Group 2') !== false) {
                    $assigned_group = 'Cadet Boys Group 2';
                    $group_link_found = 'https://westcityboxing.nz/plans/cadet-boys-group-2/';
                    break;
                } elseif (stripos($reg_membership, 'Cadet Boys') !== false) {
                    $assigned_group = 'Cadet Boys Group 1';
                    $group_link_found = 'https://westcityboxing.nz/plans/cadet-boys-group-1/';
                    break;
                } elseif (stripos($reg_membership, 'Youth Boys') !== false && stripos($reg_membership, 'Group 2') !== false) {
                    $assigned_group = 'Youth Boys Group 2';
                    $group_link_found = 'https://westcityboxing.nz/plans/youth-boys-group-2/';
                    break;
                } elseif (stripos($reg_membership, 'Youth Boys') !== false) {
                    $assigned_group = 'Youth Boys Group 1';
                    $group_link_found = 'https://westcityboxing.nz/plans/youth-boys-group-1/';
                    break;
                } elseif (stripos($reg_membership, 'Mini Cadets Girls') !== false || stripos($reg_membership, 'Mini Cadet Girls') !== false) {
                    $assigned_group = 'Mini Cadets Girls Group 1';
                    $group_link_found = 'https://westcityboxing.nz/plans/mini-cadets-girls-group-1/';
                    break;
                } elseif (stripos($reg_membership, 'Youth Girls') !== false) {
                    $assigned_group = 'Youth Girls Group 1';
                    $group_link_found = 'https://westcityboxing.nz/plans/youth-girls-group-1/';
                    break;
                }
            }
        }

        // Check if their message contains the correct group link
        $message_contains_correct_link = false;
        if (!empty($group_link_found) && strpos($member->message, $group_link_found) !== false) {
            $message_contains_correct_link = true;
        }

        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;'>";
        echo "<h4>{$member->display_name} ({$member->user_email})</h4>";

        echo "<div style='display: flex; gap: 20px; margin-bottom: 10px;'>";
        echo "<div style='flex: 1;'>";
        echo "<strong>Competitive/Special:</strong><br>";
        if (!empty($competitive_memberships)) {
            echo "<span style='color: #d32f2f;'>• " . implode('<br>• ', $competitive_memberships) . "</span>";
            $competitive_filtered++;
        } else {
            echo "<em>None</em>";
        }
        echo "</div>";

        echo "<div style='flex: 1;'>";
        echo "<strong>Regular Groups:</strong><br>";
        if (!empty($regular_memberships)) {
            echo "<span style='color: #2e7d32;'>• " . implode('<br>• ', $regular_memberships) . "</span>";
            $regular_groups_found++;
        } else {
            echo "<em>None found</em>";
        }
        echo "</div>";
        echo "</div>";

        echo "<div style='margin-top: 10px; padding: 10px; background: white; border-radius: 5px;'>";
        echo "<strong>Expected Group:</strong> <span style='color: #1976d2; font-weight: bold;'>{$assigned_group}</span><br>";
        echo "<strong>Expected Link:</strong> <span style='color: #1976d2;'>{$group_link_found}</span><br>";
        echo "<strong>Message Correct:</strong> <span style='" . ($message_contains_correct_link ? "color: #2e7d32; font-weight: bold;" : "color: #d32f2f; font-weight: bold;") . "'>" . ($message_contains_correct_link ? "✅ YES" : "❌ NO") . "</span>";
        echo "</div>";

        // Show a snippet of their message to verify
        if (strpos($member->message, '<a href=') !== false) {
            preg_match('/<a href="([^"]*)"[^>]*>([^<]*)<\/a>/', $member->message, $matches);
            if (!empty($matches)) {
                echo "<div style='margin-top: 10px; padding: 8px; background: #e3f2fd; border-radius: 3px;'>";
                echo "<strong>Actual Link in Message:</strong> <a href='{$matches[1]}' target='_blank' style='color: #1976d2;'>{$matches[2]}</a>";
                echo "</div>";
            }
        }

        echo "</div>";
    }

    echo "<h3>Summary:</h3>";
    echo "<ul>";
    echo "<li><strong>Members with competitive/special memberships:</strong> {$competitive_filtered}</li>";
    echo "<li><strong>Members with regular group memberships:</strong> {$regular_groups_found}</li>";
    echo "</ul>";

} else {
    echo "<p>No members with multiple memberships found</p>";
}

echo "<hr>";
echo "<p><strong>Analysis Complete:</strong> This script checks if members with multiple memberships (like Competitive Team + regular groups) were correctly assigned to their regular program groups and received the appropriate message links.</p>";
?>