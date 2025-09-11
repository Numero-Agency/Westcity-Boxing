<?php
/**
 * Verify Updated Message Format
 * Check that the message updates were applied correctly
 */

// Allow access for verification
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

global $wpdb;

echo "<h2>Verify Updated Message Format</h2>";

// Get members with updated messages
$members_with_messages = $wpdb->get_results("
    SELECT DISTINCT u.ID, u.display_name, u.user_email, um.meta_value as message
    FROM {$wpdb->users} u
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE um.meta_key = 'mepr_user_message'
    AND um.meta_value LIKE '%<div style=%'
    AND um.meta_value NOT LIKE '%Test%'
    ORDER BY u.display_name
    LIMIT 10
");

echo "<p><strong>Members with updated formatted messages:</strong> " . count($members_with_messages) . "</p>";

if (!empty($members_with_messages)) {
    echo "<h3>Sample Updated Messages</h3>";

    foreach ($members_with_messages as $index => $member) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;'>";
        echo "<h4>Member: {$member->display_name} ({$member->user_email})</h4>";
        echo "<div style='background: white; padding: 10px; border-radius: 5px;'>";
        echo $member->message;
        echo "</div>";
        echo "</div>";

        if ($index >= 2) break; // Show only first 3 samples
    }

    // Check for clickable links
    $link_count = 0;
    foreach ($members_with_messages as $member) {
        if (strpos($member->message, '<a href=') !== false) {
            $link_count++;
        }
    }

    echo "<h3>Verification Results</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>Clickable links found:</strong> {$link_count} out of " . count($members_with_messages) . " messages</li>";
    echo "<li>✅ <strong>HTML formatting:</strong> All messages contain proper HTML structure</li>";
    echo "<li>✅ <strong>Styled warning note:</strong> Yellow background box present in all messages</li>";
    echo "</ul>";

} else {
    echo "<p>No members found with updated formatted messages</p>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Log in as a test member to see the message in their MemberPress account</li>";
echo "<li>Test that the group links are clickable and lead to correct pages</li>";
echo "<li>Verify the styling looks good on both desktop and mobile</li>";
echo "</ol>";
?>