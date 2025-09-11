<?php
/**
 * Diagnose Member Messages - Check current message status
 */

// Allow access for diagnosis
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

global $wpdb;

echo "<h2>Diagnose Member Messages</h2>";

// Get all members with personalized messages
$all_members_with_messages = $wpdb->get_results("
    SELECT DISTINCT u.ID, u.display_name, u.user_email, um.meta_value as message
    FROM {$wpdb->users} u
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE um.meta_key = 'mepr_user_message'
    AND um.meta_value != ''
    AND um.meta_value NOT LIKE '%Test%'
    ORDER BY u.display_name
");

echo "<p><strong>Total members with personalized messages:</strong> " . count($all_members_with_messages) . "</p>";

// Count by format type
$old_format_count = 0;
$new_format_count = 0;
$other_format_count = 0;

foreach ($all_members_with_messages as $member) {
    if (strpos($member->message, '<div style=') !== false) {
        $new_format_count++;
    } elseif (strpos($member->message, '[GROUP_LINK]') !== false) {
        $old_format_count++;
    } else {
        $other_format_count++;
    }
}

echo "<h3>Message Format Breakdown:</h3>";
echo "<ul>";
echo "<li><strong>New HTML format:</strong> {$new_format_count} members</li>";
echo "<li><strong>Old placeholder format:</strong> {$old_format_count} members</li>";
echo "<li><strong>Other formats:</strong> {$other_format_count} members</li>";
echo "</ul>";

// Show sample of old format messages
if ($old_format_count > 0) {
    echo "<h3>Sample Old Format Messages (need updating):</h3>";
    $old_format_samples = array_filter($all_members_with_messages, function($member) {
        return strpos($member->message, '[GROUP_LINK]') !== false;
    });

    $sample_count = min(5, count($old_format_samples));
    for ($i = 0; $i < $sample_count; $i++) {
        $member = $old_format_samples[$i];
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; background: #f9f9f9;'>";
        echo "<strong>{$member->display_name}</strong> ({$member->user_email})<br>";
        echo "<pre style='background: white; padding: 10px; margin-top: 5px; font-size: 12px;'>" . substr($member->message, 0, 200) . "...</pre>";
        echo "</div>";
    }
}

// Show sample of new format messages
if ($new_format_count > 0) {
    echo "<h3>Sample New Format Messages (already updated):</h3>";
    $new_format_samples = array_filter($all_members_with_messages, function($member) {
        return strpos($member->message, '<div style=') !== false;
    });

    $sample_count = min(3, count($new_format_samples));
    for ($i = 0; $i < $sample_count; $i++) {
        $member = $new_format_samples[$i];
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; background: #f9f9f9;'>";
        echo "<strong>{$member->display_name}</strong> ({$member->user_email})<br>";
        echo "<div style='background: white; padding: 10px; margin-top: 5px; font-size: 12px; max-height: 150px; overflow-y: auto;'>" . $member->message . "</div>";
        echo "</div>";
    }
}

echo "<hr>";
echo "<h3>Members by Group (from previous bulk update):</h3>";

// Get group statistics from the previous bulk update
$txn_table = $wpdb->prefix . 'mepr_transactions';

$group_stats = $wpdb->get_results("
    SELECT
        CASE
            WHEN p.post_title LIKE '%Mini Cadet Boys%' THEN 'Mini Cadet Boys (9-11 Years) Group 1'
            WHEN p.post_title LIKE '%Cadet Boys%' AND p.post_title LIKE '%Group 2%' THEN 'Cadet Boys Group 2'
            WHEN p.post_title LIKE '%Cadet Boys%' THEN 'Cadet Boys Group 1'
            WHEN p.post_title LIKE '%Youth Boys%' AND p.post_title LIKE '%Group 2%' THEN 'Youth Boys Group 2'
            WHEN p.post_title LIKE '%Youth Boys%' THEN 'Youth Boys Group 1'
            WHEN p.post_title LIKE '%Mini Cadets Girls%' THEN 'Mini Cadets Girls Group 1'
            WHEN p.post_title LIKE '%Youth Girls%' THEN 'Youth Girls Group 1'
            ELSE 'Other'
        END as group_name,
        COUNT(DISTINCT u.ID) as member_count
    FROM {$wpdb->users} u
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    JOIN {$txn_table} t ON u.ID = t.user_id
    LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
    WHERE um.meta_key = 'mepr_user_message'
    AND um.meta_value != ''
    AND um.meta_value NOT LIKE '%Test%'
    AND t.status IN ('confirmed', 'complete')
    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    GROUP BY group_name
    ORDER BY member_count DESC
");

echo "<ul>";
foreach ($group_stats as $stat) {
    if ($stat->group_name != 'Other') {
        echo "<li><strong>{$stat->group_name}:</strong> {$stat->member_count} members</li>";
    }
}
echo "</ul>";

// Check for members with multiple memberships
echo "<h3>Members with Multiple Memberships:</h3>";

$multiple_membership_members = $wpdb->get_results("
    SELECT u.ID, u.display_name, u.user_email, COUNT(t.id) as membership_count,
           GROUP_CONCAT(p.post_title SEPARATOR ', ') as memberships
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
    LIMIT 10
");

echo "<p><strong>Members with multiple memberships:</strong> " . count($multiple_membership_members) . "</p>";

if (!empty($multiple_membership_members)) {
    echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;'>";
    foreach ($multiple_membership_members as $member) {
        echo "<div style='margin: 5px 0; padding: 5px; background: #f0f0f0;'>";
        echo "<strong>{$member->display_name}</strong><br>";
        echo "<small>{$member->memberships}</small>";
        echo "</div>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Summary:</strong></p>";
echo "<ul>";
echo "<li>Total members with personalized messages: " . count($all_members_with_messages) . "</li>";
echo "<li>Members with new HTML format: {$new_format_count}</li>";
echo "<li>Members with old placeholder format: {$old_format_count}</li>";
echo "<li>Members with multiple memberships: " . count($multiple_membership_members) . "</li>";
echo "</ul>";
?>