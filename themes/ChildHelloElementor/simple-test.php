<?php
require_once('../../../wp-load.php');

global $wpdb;
$txn_table = $wpdb->prefix . 'mepr_transactions';

// Get first 3 active members
$active_members = $wpdb->get_results("
    SELECT DISTINCT u.ID, u.display_name, u.user_email
    FROM {$wpdb->users} u
    JOIN {$txn_table} t ON u.ID = t.user_id
    WHERE t.status IN ('confirmed', 'complete')
    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    AND t.product_id NOT IN (1738, 1932)
    AND u.user_login != 'bwgdev'
    ORDER BY u.display_name
    LIMIT 3
");

echo "=== TEST MEMBERS FOUND ===\n";
echo "Found " . count($active_members) . " test members:\n\n";

foreach ($active_members as $member) {
    echo "Name: {$member->display_name}\n";
    echo "Email: {$member->user_email}\n";

    // Get their membership
    $membership = $wpdb->get_row($wpdb->prepare("
        SELECT p.post_title as membership_name
        FROM {$txn_table} t
        LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ORDER BY t.created_at DESC
        LIMIT 1
    ", $member->ID));

    if ($membership) {
        echo "Membership: {$membership->membership_name}\n";

        // Determine group
        $group = determine_member_group($membership->membership_name);
        echo "Detected Group: {$group}\n";

        // Get group link
        $group_links = [
            'Mini Cadet Boys (9-11 Years) Group 1' => 'https://westcityboxing.nz/plans/mini-cadet-boys-9-11-years-group-1/',
            'Cadet Boys Group 1' => 'https://westcityboxing.nz/plans/cadet-boys-group-1/',
            'Cadet Boys Group 2' => 'https://westcityboxing.nz/plans/cadet-boys-group-2/',
            'Youth Boys Group 1' => 'https://westcityboxing.nz/plans/youth-boys-group-1/',
            'Youth Boys Group 2' => 'https://westcityboxing.nz/plans/youth-boys-group-2/',
            'Mini Cadets Girls Group 1' => 'https://westcityboxing.nz/plans/mini-cadets-girls-group-1/',
            'Youth Girls Group 1' => 'https://westcityboxing.nz/plans/youth-girls-group-1/'
        ];

        $group_link = isset($group_links[$group]) ? $group_links[$group] : 'No link found';
        echo "Group Link: {$group_link}\n";
    }

    echo "---\n";
}

function determine_member_group($membership_name) {
    // Handle waitlist memberships by removing "Waitlist" and finding the base group
    $clean_name = str_replace(' Waitlist', '', $membership_name);

    echo "DEBUG: Clean name: '{$clean_name}'\n";

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
                echo "DEBUG: Matched '{$keyword}' in '{$clean_name}'\n";
                return $group;
            }
        }
    }

    echo "DEBUG: No match found for '{$clean_name}'\n";
    return 'Unknown Group';
}
?>