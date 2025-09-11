<?php
// Debug script to check date of birth data
require_once('../../../../wp-config.php');

global $wpdb;

// Check Xarisma Paga's data
$results = $wpdb->get_results("
    SELECT u.ID, u.display_name, u.user_email, um.meta_value 
    FROM {$wpdb->users} u 
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_date_of_birth' 
    WHERE u.display_name LIKE '%Xarisma%' OR u.display_name LIKE '%Paga%'
");

echo "=== Date of Birth Debug for Xarisma Paga ===\n";
foreach ($results as $result) {
    echo "ID: " . $result->ID . "\n";
    echo "Name: " . $result->display_name . "\n";
    echo "Email: " . $result->user_email . "\n";
    echo "DOB Value: \"" . $result->meta_value . "\"\n";
    echo "DOB Length: " . strlen($result->meta_value) . "\n";
    echo "DOB is_empty: " . (empty($result->meta_value) ? 'YES' : 'NO') . "\n";
    
    // Test the calculate_age_from_dob function
    require_once('includes/dashboard/dashboard-stats.php');
    $age = calculate_age_from_dob($result->meta_value);
    echo "Calculated Age: " . ($age !== null ? $age : 'NULL') . "\n";
    
    // Test date parsing
    $dob = date_create($result->meta_value);
    echo "Date Parse Success: " . ($dob ? 'YES' : 'NO') . "\n";
    if ($dob) {
        echo "Parsed Date: " . $dob->format('Y-m-d') . "\n";
    }
    
    echo "---\n";
}

// Also check all users with non-empty DOB to see format variations
echo "\n=== Sample DOB formats in database ===\n";
$sample_dobs = $wpdb->get_results("
    SELECT u.display_name, um.meta_value 
    FROM {$wpdb->users} u 
    JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_date_of_birth' 
    WHERE um.meta_value IS NOT NULL AND um.meta_value != ''
    LIMIT 10
");

foreach ($sample_dobs as $sample) {
    echo "Name: " . $sample->display_name . " | DOB: \"" . $sample->meta_value . "\"\n";
    $age = calculate_age_from_dob($sample->meta_value);
    echo "Calculated Age: " . ($age !== null ? $age : 'NULL') . "\n";
    echo "---\n";
}
?>