<?php
require_once('../../../wp-load.php');

global $wpdb;

// Get the first 3 members and check their messages
$test_members = $wpdb->get_results("
    SELECT u.ID, u.display_name, u.user_email, um.meta_value as message
    FROM {$wpdb->users} u
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_user_message'
    WHERE u.user_login != 'bwgdev'
    ORDER BY u.display_name
    LIMIT 3
");

echo "=== MESSAGE VERIFICATION FOR TEST MEMBERS ===\n\n";

foreach ($test_members as $member) {
    echo "Member: {$member->display_name} ({$member->user_email})\n";

    if (empty($member->message)) {
        echo "❌ No message found\n";
    } elseif ($member->message === 'Test') {
        echo "⚠️  Still has 'Test' message\n";
    } else {
        echo "✅ Has personalized message\n";

        // Check if it contains the group link
        if (strpos($member->message, 'https://westcityboxing.nz/plans/') !== false) {
            echo "✅ Contains group link\n";

            // Extract the link for verification
            preg_match('/https:\/\/westcityboxing\.nz\/plans\/[^\s]+/', $member->message, $matches);
            if (!empty($matches)) {
                echo "📎 Link: {$matches[0]}\n";
            }
        } else {
            echo "❌ No group link found in message\n";
        }

        // Show first 150 characters as preview
        $preview = substr(strip_tags($member->message), 0, 150);
        echo "📝 Preview: {$preview}...\n";
    }

    echo "---\n";
}

// Count total updated messages
$total_updated = $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$wpdb->usermeta}
    WHERE meta_key = 'mepr_user_message'
    AND meta_value != 'Test'
    AND meta_value != ''
");

echo "\n=== SUMMARY ===\n";
echo "Total members with updated messages: {$total_updated}\n";
echo "Total members with 'Test' message: " . ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'mepr_user_message' AND meta_value = 'Test'")) . "\n";
?>