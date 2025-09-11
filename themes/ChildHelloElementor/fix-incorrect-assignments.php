<?php
/**
 * Fix Incorrect Group Assignments for Multiple Membership Members
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

// Members that need correction based on the analysis
$corrections_needed = [
    [
        'email' => 'victoriandfamily@gmail.com', // Ashton Hodgson
        'correct_group' => 'Cadet Boys Group 2',
        'correct_link' => 'https://westcityboxing.nz/plans/cadet-boys-group-2/',
        'reason' => 'Has Cadet Boys Group 2 memberships but was assigned Group 1'
    ],
    [
        'email' => 'obmilo@icloud.com', // Milo O'Brien
        'correct_group' => 'Youth Boys Group 1',
        'correct_link' => 'https://westcityboxing.nz/plans/youth-boys-group-1/',
        'reason' => 'Has both Youth Boys and Mini Cadet memberships, should prioritize Youth Boys'
    ]
];

echo "<h2>Fix Incorrect Group Assignments</h2>";
echo "<p>Correcting group assignments for " . count($corrections_needed) . " members with multiple memberships</p>";

$updated_count = 0;

foreach ($corrections_needed as $correction) {
    // Find the user by email
    $user = get_user_by('email', $correction['email']);

    if ($user) {
        $group_link_text = str_replace('https://westcityboxing.nz/plans/', '', $correction['correct_link']);
        $group_link_text = str_replace('/', '', $group_link_text);
        $group_link_text = ucwords(str_replace('-', ' ', $group_link_text));

        // Replace placeholders with actual values
        $personalized_message = str_replace('[GROUP_LINK]', $correction['correct_link'], $message_template);
        $personalized_message = str_replace('[GROUP_LINK_TEXT]', $group_link_text, $personalized_message);

        // Update the user meta
        $result = update_user_meta($user->ID, 'mepr_user_message', $personalized_message);

        if ($result) {
            echo "<div style='color: green; margin: 2px 0; padding: 10px; background: #e8f5e8; border-radius: 5px;'>";
            echo "✅ <strong>{$user->display_name}</strong> ({$correction['email']})<br>";
            echo "<small>Updated to: <strong>{$correction['correct_group']}</strong><br>";
            echo "Reason: {$correction['reason']}</small>";
            echo "</div>";
            $updated_count++;
        } else {
            echo "<div style='color: red; margin: 2px 0; padding: 10px; background: #ffebee; border-radius: 5px;'>";
            echo "❌ <strong>{$user->display_name}</strong> ({$correction['email']}) - Update failed";
            echo "</div>";
        }
    } else {
        echo "<div style='color: orange; margin: 2px 0; padding: 10px; background: #fff3e0; border-radius: 5px;'>";
        echo "⚠️ User not found: {$correction['email']}";
        echo "</div>";
    }
}

echo "<h3>Correction Summary</h3>";
echo "<p><strong>Members corrected:</strong> {$updated_count} out of " . count($corrections_needed) . "</p>";

if ($updated_count > 0) {
    echo "<h4>Changes Made:</h4>";
    echo "<ul>";
    echo "<li>✅ <strong>Ashton Hodgson:</strong> Corrected from Cadet Boys Group 1 → Cadet Boys Group 2</li>";
    echo "<li>✅ <strong>Milo O'Brien:</strong> Corrected from Mini Cadet Boys Group 1 → Youth Boys Group 1</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Verification:</strong> After running this script, the corrected members should now have the proper group links in their MemberPress account messages.</p>";
?>