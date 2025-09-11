<?php
/**
 * Analyze the 44 Members Without Defined Group Memberships
 * Check what memberships they actually have and why they weren't updated
 */

// Allow access for analysis
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

require_once('../../../wp-load.php');

global $wpdb;

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

echo "<h2>Analyze Remaining 44 Members</h2>";
echo "<p>Checking memberships for " . count($remaining_members) . " members who weren't assigned to standard groups</p>";

$txn_table = $wpdb->prefix . 'mepr_transactions';

$found_members = [];
$not_found_members = [];
$members_with_messages = [];
$members_without_messages = [];

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

        // Check if they have a personalized message
        $message = get_user_meta($user->ID, 'mepr_user_message', true);
        if (!empty($message) && strpos($message, '[GROUP_LINK]') === false && strpos($message, '<div style=') !== false) {
            $members_with_messages[] = $user;
        } else {
            $members_without_messages[] = $user;
        }
    } else {
        $not_found_members[] = $member_name;
    }
}

echo "<h3>Analysis Results</h3>";
echo "<ul>";
echo "<li><strong>Members found in database:</strong> " . count($found_members) . " out of " . count($remaining_members) . "</li>";
echo "<li><strong>Members with updated messages:</strong> " . count($members_with_messages) . "</li>";
echo "<li><strong>Members without updated messages:</strong> " . count($members_without_messages) . "</li>";
echo "<li><strong>Members not found:</strong> " . count($not_found_members) . "</li>";
echo "</ul>";

if (!empty($not_found_members)) {
    echo "<h4>Members Not Found in Database:</h4>";
    echo "<div style='background: #fff3e0; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>";
    foreach ($not_found_members as $name) {
        echo "• {$name}<br>";
    }
    echo "</div>";
}

// Analyze memberships for found members
echo "<h3>Detailed Membership Analysis</h3>";
echo "<div style='max-height: 600px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>";

foreach ($found_members as $user) {
    // Get all memberships for this user
    $memberships = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title as membership_name, t.status, t.expires_at
        FROM {$txn_table} t
        LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        ORDER BY t.created_at DESC
    ", $user->ID));

    // Check current message status
    $current_message = get_user_meta($user->ID, 'mepr_user_message', true);
    $has_updated_message = (!empty($current_message) && strpos($current_message, '<div style=') !== false);

    echo "<div style='border: 1px solid #eee; padding: 10px; margin: 5px 0; background: " . ($has_updated_message ? '#e8f5e8' : '#ffebee') . ";'>";
    echo "<h4>{$user->display_name} ({$user->user_email})</h4>";

    echo "<div style='display: flex; gap: 20px; margin-bottom: 10px;'>";
    echo "<div style='flex: 1;'>";
    echo "<strong>Memberships:</strong><br>";
    if (!empty($memberships)) {
        foreach ($memberships as $membership) {
            $status_color = ($membership->status == 'complete') ? '#2e7d32' : '#d32f2f';
            echo "<span style='color: {$status_color};'>• {$membership->membership_name}</span><br>";
        }
    } else {
        echo "<em>No memberships found</em>";
    }
    echo "</div>";

    echo "<div style='flex: 1;'>";
    echo "<strong>Message Status:</strong><br>";
    if ($has_updated_message) {
        echo "<span style='color: #2e7d32; font-weight: bold;'>✅ Has updated message</span><br>";
        // Extract the group link from the message
        if (preg_match('/<a href="([^"]*)"[^>]*>([^<]*)<\/a>/', $current_message, $matches)) {
            echo "<small>Group: {$matches[2]}</small>";
        }
    } else {
        echo "<span style='color: #d32f2f; font-weight: bold;'>❌ No updated message</span>";
    }
    echo "</div>";
    echo "</div>";

    echo "</div>";
}

echo "</div>";

echo "<hr>";
echo "<h3>Summary & Recommendations</h3>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><strong>Review memberships:</strong> Check what specific programs these members are enrolled in</li>";
echo "<li><strong>Identify patterns:</strong> Look for common membership types that could be mapped to groups</li>";
echo "<li><strong>Create mappings:</strong> If these are valid programs, create corresponding plan pages and group mappings</li>";
echo "<li><strong>Manual updates:</strong> Consider manual updates for members with valid but unmapped programs</li>";
echo "</ol>";
?>