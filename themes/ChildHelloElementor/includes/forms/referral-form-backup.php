<?php
// Referral Form

// Safe meta update wrapper: uses ACF update_field when available, falls back to update_post_meta
if (!function_exists('wcb_update_meta_field')) {
    function wcb_update_meta_field($post_id, $field, $value) {
        if (function_exists('update_field')) {
            return update_field($field, $value, $post_id);
        }
        return update_post_meta($post_id, $field, $value);
    }
}
if (!function_exists('wcb_referral_form_shortcode')) {
function wcb_referral_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_referral']) && wp_verify_nonce($_POST['referral_nonce'], 'submit_referral')) {
        $result = wcb_handle_referral_submission();
        if ($result['success']) {
            $notification_msg = '';
            if (isset($result['notifications']['admin_sent']) && $result['notifications']['admin_sent']) {
                $notification_msg = '<br><small>📧 Staff have been notified via email.</small>';
            }
            echo '<div class="form-success">✅ Referral submitted successfully!' . $notification_msg . '</div>';
        } else {
            echo '<div class="form-error">❌ Error: ' . $result['message'] . '</div>';
        }
    }
    
    // Get users for referrer selection
    $users = get_users(['role__in' => ['administrator', 'editor', 'instructor', 'contributor']]);
    
    // No ethnicity options needed - using text field now
    
    // Gender options
    $gender_options = [
        'Male' => 'Male',
        'Female' => 'Female',
        'Non-binary' => 'Non-binary',
        'Prefer not to say' => 'Prefer not to say'
    ];
    
    ob_start();
    ?>
    <div class="wcb-form-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-admin-users"></span> Referral Form</h2>
            <p>Complete this form to refer a young person to our program</p>
        </div>
        
        <form method="post" class="wcb-referral-form">
            <?php wp_nonce_field('submit_referral', 'referral_nonce'); ?>
            
            <!-- Young Person Details Section -->
            <div class="form-section">
                <h3>Young Person Details</h3>
                
                <div class="form-row">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-row">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-row">
                    <label for="date_of_birth">Date of Birth *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                    <small class="field-description">dd/mm/yyyy</small>
                </div>
                
                <div class="form-row">
                    <label for="ethnicity">Ethnicity *</label>
                    <input type="text" id="ethnicity" name="ethnicity" required placeholder="e.g. Māori, Pacific Islander, European/Pākehā, Asian, etc.">
                </div>
                
                <div class="form-row">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select an option</option>
                        <?php foreach ($gender_options as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="contact_phone">Contact Phone Number for Young person *</label>
                    <input type="tel" id="contact_phone" name="contact_phone" required>
                </div>
                
                <div class="form-row">
                    <label for="contact_email">Contact Email Address for Young person *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
            </div>
            
            <!-- Parent/Guardian Details Section -->
            <div class="form-section">
                <h3>Parent/Guardian Details</h3>
                
                <div class="form-row">
                    <label for="parent_name">Parent/Guardian's Name *</label>
                    <input type="text" id="parent_name" name="parent_name" required>
                </div>
                
                <div class="form-row">
                    <label for="parent_phone">Parent/Guardian's Phone Number *</label>
                    <input type="tel" id="parent_phone" name="parent_phone" required>
                </div>
                
                <div class="form-row">
                    <label for="parent_email">Parent/Guardian's Email Address</label>
                    <input type="email" id="parent_email" name="parent_email">
                </div>
            </div>
            
            <!-- Medical & Address Information Section -->
            <div class="form-section">
                <h3>Medical & Address Information</h3>
                
                <div class="form-row">
                    <label for="medical_information">Medical Information</label>
                    <textarea id="medical_information" name="medical_information" rows="4" 
                        placeholder="If the young person has any current medical condition or mental health conditions please state below."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="address">Address *</label>
                    <textarea id="address" name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <label for="suburb">Suburb *</label>
                    <input type="text" id="suburb" name="suburb" required>
                </div>
            </div>
            
            <!-- Family & Safety Information Section -->
            <div class="form-section">
                <h3>Family & Safety Information</h3>
                
                <div class="form-row">
                    <label for="whanau_history">Whanau History</label>
                    <textarea id="whanau_history" name="whanau_history" rows="4" 
                        placeholder="Provide relevant family history information..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="protective_factors">Protective Factors</label>
                    <textarea id="protective_factors" name="protective_factors" rows="4" 
                        placeholder="e.g. Supportive family, positive peer relationships, engagement in activities..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="staff_safety_check">Staff Safety Check</label>
                    <textarea id="staff_safety_check" name="staff_safety_check" rows="4" 
                        placeholder="Please let us know if there are any dangers, e.g. safe to go to home..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="people_in_home">People living in the home</label>
                    <textarea id="people_in_home" name="people_in_home" rows="4" 
                        placeholder="Names and relationship to young person (e.g siblings, family others)..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="risk_factors">Risk Factors</label>
                    <textarea id="risk_factors" name="risk_factors" rows="4" 
                        placeholder="e.g. family gang involvement, peer group influences, substance abuse..."></textarea>
                </div>
            </div>
            
            <!-- Referrer Information Section -->
            <div class="form-section">
                <h3>Referrer Information</h3>
                
                <div class="form-row">
                    <label for="referrer_name">Name of Referrer</label>
                    <input type="text" id="referrer_name" name="referrer_name">
                </div>
                
                <div class="form-row">
                    <label for="agency">Agency</label>
                    <input type="text" id="agency" name="agency">
                </div>
                
                <div class="form-row">
                    <label for="referrer_contact">Referrer Contact Information</label>
                    <textarea id="referrer_contact" name="referrer_contact" rows="3" 
                        placeholder="Phone, email, and any other contact details..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="other_agencies">Other Agencies Involved</label>
                    <textarea id="other_agencies" name="other_agencies" rows="3" 
                        placeholder="List other agencies currently working with this young person..."></textarea>
                </div>
            </div>
            
            <!-- Additional Information Section -->
            <div class="form-section">
                <h3>Additional Information</h3>
                
                <div class="form-row">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4" 
                        placeholder="e.g. hobbies or interests, goals, motivation for referral..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="referral_date">Referral Date *</label>
                    <input type="date" id="referral_date" name="referral_date" required>
                    <small class="field-description">dd/mm/yyyy</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="submit_referral" class="btn-submit">
                    Submit Referral
                </button>
                <a href="/dashboard" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    
    <style>
    .wcb-form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .wcb-referral-form {
        max-width: 100%;
    }
    
    .form-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .form-header h2 {
        color: #2c3e50;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .form-header h2 .dashicons {
        font-size: 24px;
    }
    
    .form-section {
        margin-bottom: 35px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .form-section h3 {
        margin: 0 0 20px 0;
        color: #2c3e50;
        font-size: 18px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }
    
    .form-row {
        margin-bottom: 20px;
    }
    
    .form-row label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #2c3e50;
    }
    
    .form-row input[type="text"],
    .form-row input[type="email"],
    .form-row input[type="tel"],
    .form-row input[type="date"],
    .form-row select,
    .form-row textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
        font-family: inherit;
    }
    
    /* Prevent invalid styling until user interacts */
    .form-row input:invalid:not(:focus):not(.touched),
    .form-row select:invalid:not(:focus):not(.touched),
    .form-row textarea:invalid:not(:focus):not(.touched) {
        border-color: #ddd;
    }
    
    .form-row input:invalid.touched,
    .form-row select:invalid.touched,
    .form-row textarea:invalid.touched {
        border-color: #e74c3c;
    }
    
    .form-row input:focus,
    .form-row select:focus,
    .form-row textarea:focus {
        outline: none;
        border-color: #3498db;
    }
    
    .form-row textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .field-description {
        font-style: italic;
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .form-actions {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #eee;
    }
    
    .btn-submit,
    .btn-cancel {
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        margin: 0 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-submit {
        background-color: #27ae60;
        color: white;
    }
    
    .btn-submit:hover {
        background-color: #219a52;
    }
    
    .btn-cancel {
        background-color: #95a5a6;
        color: white;
    }
    
    .btn-cancel:hover {
        background-color: #7f8c8d;
        text-decoration: none;
    }
    
    .form-success {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }
    
    .form-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .wcb-form-container {
            padding: 15px;
        }
        
        .form-section {
            padding: 20px;
        }
        
        .form-header h2 {
            font-size: 20px;
        }
        
        .form-section h3 {
            font-size: 16px;
        }
        
        .form-row input,
        .form-row select,
        .form-row textarea {
            padding: 10px 14px;
            font-size: 14px;
        }
        
        .btn-submit,
        .btn-cancel {
            padding: 10px 25px;
            font-size: 14px;
            margin: 5px;
        }
    }
    </style>
    
    <script>
    // Add touched class to fields when user interacts with them
    document.addEventListener('DOMContentLoaded', function() {
        const formFields = document.querySelectorAll('.wcb-referral-form input, .wcb-referral-form select, .wcb-referral-form textarea');
        
        formFields.forEach(function(field) {
            // Add touched class when field loses focus
            field.addEventListener('blur', function() {
                this.classList.add('touched');
            });
            
            // Also add touched class when user starts typing
            field.addEventListener('input', function() {
                this.classList.add('touched');
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}
}
add_action('init', function() {
    add_shortcode('wcb_referral_form', 'wcb_referral_form_shortcode');
});

// Handle referral form submission
if (!function_exists('wcb_handle_referral_submission')) {
function wcb_handle_referral_submission() {
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'date_of_birth', 'ethnicity', 'gender', 'contact_phone', 'contact_email', 'parent_name', 'parent_phone', 'address', 'suburb', 'referral_date'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'message' => 'Please fill in all required fields'];
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address for the young person'];
    }
    
    if (!empty($_POST['parent_email']) && !filter_var($_POST['parent_email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address for the parent/guardian'];
    }
    
    // Create referral title
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $referral_date = sanitize_text_field($_POST['referral_date']);
    $referral_title = "Referral: {$first_name} {$last_name} - " . date('M j, Y', strtotime($referral_date));
    
    // Create new referral post
    $post_data = [
        'post_title' => $referral_title,
        'post_type' => 'referral', // You may need to register this custom post type
        'post_status' => 'publish',
        'post_content' => '' // We'll use custom fields for content
    ];
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return ['success' => false, 'message' => 'Failed to create referral'];
    }
    
    // Save all form fields as custom fields
    $fields_to_save = [
        'first_name', 'last_name', 'date_of_birth', 'ethnicity', 'gender',
        'contact_phone', 'contact_email', 'parent_name', 'parent_phone', 'parent_email',
        'medical_information', 'address', 'suburb', 'whanau_history', 'protective_factors',
        'staff_safety_check', 'people_in_home', 'risk_factors', 'referrer_name', 'agency',
        'referrer_contact', 'other_agencies', 'notes', 'referral_date'
    ];
    
    foreach ($fields_to_save as $field) {
        if (isset($_POST[$field])) {
            $value = is_array($_POST[$field]) ? $_POST[$field] : sanitize_textarea_field($_POST[$field]);
            wcb_update_meta_field($post_id, $field, $value);
        }
    }
    
    // Set referral status
    wcb_update_meta_field($post_id, 'referral_status', 'pending');
    
    // Send notification emails
    $notification_result = wcb_send_referral_notifications($post_id, $_POST);
    
    // Log notification status for debugging
    if ($notification_result['admin_sent']) {
        wcb_debug_log("Referral notification sent to admin for: {$first_name} {$last_name}");
    } else {
        wcb_debug_log("Failed to send admin notification for referral: {$first_name} {$last_name}");
    }
    
    return [
        'success' => true, 
        'post_id' => $post_id,
        'notifications' => $notification_result
    ];
}
}

// Send email notifications for referral submissions
if (!function_exists('wcb_send_referral_notifications')) {
function wcb_send_referral_notifications($post_id, $form_data) {
    $first_name = sanitize_text_field($form_data['first_name']);
    $last_name = sanitize_text_field($form_data['last_name']);
    $referrer_name = sanitize_text_field($form_data['referrer_name']);
    $agency = sanitize_text_field($form_data['agency']);
    $referral_date = sanitize_text_field($form_data['referral_date']);
    
    // Get admin email addresses (multiple admins can be notified)
    $admin_emails = [];
    
    // Method 1: Get site admin email
    $admin_emails[] = get_option('admin_email');
    
    // Method 2: Get all administrator users
    $admin_users = get_users(['role' => 'administrator']);
    foreach ($admin_users as $admin) {
        if (!in_array($admin->user_email, $admin_emails)) {
            $admin_emails[] = $admin->user_email;
        }
    }
    
    // Method 3: Add specific West City Boxing emails if configured
    $wcb_notification_emails = get_option('wcb_referral_notification_emails', '');
    if (!empty($wcb_notification_emails)) {
        $additional_emails = array_map('trim', explode(',', $wcb_notification_emails));
        foreach ($additional_emails as $email) {
            if (is_email($email) && !in_array($email, $admin_emails)) {
                $admin_emails[] = $email;
            }
        }
    }
    
    // Remove empty emails and ensure uniqueness
    $admin_emails = array_filter(array_unique($admin_emails), 'is_email');
    
    $result = [
        'admin_sent' => false,
        'referrer_sent' => false,
        'admin_emails' => $admin_emails
    ];
    
    // Prepare admin notification email
    $admin_subject = "New Referral Submitted: {$first_name} {$last_name}";
    $admin_message = wcb_get_admin_notification_email_content($form_data, $post_id);
    
    // Send to all admin emails
    if (!empty($admin_emails)) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: West City Boxing <' . get_option('admin_email') . '>'
        ];
        
        foreach ($admin_emails as $admin_email) {
            $sent = wp_mail($admin_email, $admin_subject, $admin_message, $headers);
            if ($sent) {
                $result['admin_sent'] = true;
            }
        }
    }
    
    // Send confirmation email to referrer (if they provided contact info)
    if (!empty($form_data['referrer_contact']) && !empty($referrer_name)) {
        // Try to extract email from referrer contact info
        $referrer_email = wcb_extract_email_from_contact($form_data['referrer_contact']);
        
        if ($referrer_email) {
            $referrer_subject = "Referral Confirmation: {$first_name} {$last_name}";
            $referrer_message = wcb_get_referrer_confirmation_email_content($form_data);
            
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: West City Boxing <' . get_option('admin_email') . '>'
            ];
            
            $result['referrer_sent'] = wp_mail($referrer_email, $referrer_subject, $referrer_message, $headers);
        }
    }
    
    return $result;
}
}

// Generate admin notification email content
if (!function_exists('wcb_get_admin_notification_email_content')) {
function wcb_get_admin_notification_email_content($form_data, $post_id) {
    $first_name = esc_html($form_data['first_name']);
    $last_name = esc_html($form_data['last_name']);
    $referrer_name = esc_html($form_data['referrer_name']);
    $agency = esc_html($form_data['agency']);
    $referral_date = esc_html($form_data['referral_date']);
    $admin_url = admin_url("post.php?post={$post_id}&action=edit");
    
    $message = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: #2c3e50; color: white; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>🥊 New Referral Submitted</h1>
        </div>
        
        <div style='padding: 30px; background: #f8f9fa;'>
            <div style='background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #2c3e50; margin-top: 0;'>Young Person Details</h2>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 8px 0; font-weight: bold; width: 30%;'>Name:</td><td style='padding: 8px 0;'>{$first_name} {$last_name}</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Date of Birth:</td><td style='padding: 8px 0;'>" . esc_html($form_data['date_of_birth']) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Gender:</td><td style='padding: 8px 0;'>" . esc_html($form_data['gender']) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Ethnicity:</td><td style='padding: 8px 0;'>" . esc_html($form_data['ethnicity']) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Phone:</td><td style='padding: 8px 0;'>" . esc_html($form_data['contact_phone']) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Email:</td><td style='padding: 8px 0;'>" . esc_html($form_data['contact_email']) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Address:</td><td style='padding: 8px 0;'>" . esc_html($form_data['address']) . ", " . esc_html($form_data['suburb']) . "</td></tr>
                </table>
                
                <h3 style='color: #2c3e50; margin-top: 25px;'>Parent/Guardian</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 8px 0; font-weight: bold; width: 30%;'>Name:</td><td style='padding: 8px 0;'>" . esc_html($form_data['parent_name']) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Phone:</td><td style='padding: 8px 0;'>" . esc_html($form_data['parent_phone']) . "</td></tr>";
    
    if (!empty($form_data['parent_email'])) {
        $message .= "<tr><td style='padding: 8px 0; font-weight: bold;'>Email:</td><td style='padding: 8px 0;'>" . esc_html($form_data['parent_email']) . "</td></tr>";
    }
    
    $message .= "
                </table>
                
                <h3 style='color: #2c3e50; margin-top: 25px;'>Referrer Information</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 8px 0; font-weight: bold; width: 30%;'>Referrer:</td><td style='padding: 8px 0;'>{$referrer_name}</td></tr>";
    
    if (!empty($agency)) {
        $message .= "<tr><td style='padding: 8px 0; font-weight: bold;'>Agency:</td><td style='padding: 8px 0;'>{$agency}</td></tr>";
    }
    
    if (!empty($form_data['referrer_contact'])) {
        $message .= "<tr><td style='padding: 8px 0; font-weight: bold;'>Contact:</td><td style='padding: 8px 0;'>" . nl2br(esc_html($form_data['referrer_contact'])) . "</td></tr>";
    }
    
    $message .= "
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Referral Date:</td><td style='padding: 8px 0;'>{$referral_date}</td></tr>
                </table>";
    
    // Add important notes/medical info if provided
    if (!empty($form_data['medical_information']) || !empty($form_data['risk_factors']) || !empty($form_data['staff_safety_check'])) {
        $message .= "<h3 style='color: #e74c3c; margin-top: 25px;'>⚠️ Important Information</h3>";
        
        if (!empty($form_data['medical_information'])) {
            $message .= "<p><strong>Medical Information:</strong><br>" . nl2br(esc_html($form_data['medical_information'])) . "</p>";
        }
        
        if (!empty($form_data['risk_factors'])) {
            $message .= "<p><strong>Risk Factors:</strong><br>" . nl2br(esc_html($form_data['risk_factors'])) . "</p>";
        }
        
        if (!empty($form_data['staff_safety_check'])) {
            $message .= "<p><strong>Staff Safety Check:</strong><br>" . nl2br(esc_html($form_data['staff_safety_check'])) . "</p>";
        }
    }
    
    $message .= "
                <div style='margin-top: 30px; padding: 20px; background: #e8f6f3; border-radius: 6px; text-align: center;'>
                    <a href='{$admin_url}' style='background: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;'>📝 View Full Referral in Admin</a>
                </div>
            </div>
        </div>
        
        <div style='background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px;'>
            <p style='margin: 0;'>West City Boxing | Automated Referral Notification</p>
        </div>
    </div>";
    
    return $message;
}
}

// Generate referrer confirmation email content
if (!function_exists('wcb_get_referrer_confirmation_email_content')) {
function wcb_get_referrer_confirmation_email_content($form_data) {
    $first_name = esc_html($form_data['first_name']);
    $last_name = esc_html($form_data['last_name']);
    $referrer_name = esc_html($form_data['referrer_name']);
    
    $message = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: #27ae60; color: white; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>✅ Referral Confirmation</h1>
        </div>
        
        <div style='padding: 30px; background: #f8f9fa;'>
            <div style='background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <p>Hi {$referrer_name},</p>
                
                <p>Thank you for submitting a referral for <strong>{$first_name} {$last_name}</strong> to West City Boxing.</p>
                
                <div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <p style='margin: 0; color: #155724;'><strong>✓ Your referral has been successfully received and will be reviewed by our team.</strong></p>
                </div>
                
                <p>Our team will review the referral and contact you or the young person's family as appropriate to discuss next steps.</p>
                
                <p>If you have any questions or need to provide additional information, please contact us:</p>
                
                <ul>
                    <li><strong>Email:</strong> info@westcityboxing.nz</li>
                    <li><strong>Phone:</strong> [Insert phone number]</li>
                </ul>
                
                <p>Thank you for helping connect young people with our program.</p>
                
                <p>Best regards,<br>
                <strong>West City Boxing Team</strong></p>
            </div>
        </div>
        
        <div style='background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px;'>
            <p style='margin: 0;'>West City Boxing | Building Strong Communities</p>
        </div>
    </div>";
    
    return $message;
}
}

// Extract email address from contact information text
if (!function_exists('wcb_extract_email_from_contact')) {
function wcb_extract_email_from_contact($contact_text) {
    // Use regex to find email patterns in the contact text
    $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
    if (preg_match($pattern, $contact_text, $matches)) {
        return $matches[0];
    }
    return false;
}
}

// Add admin menu option to configure referral notification emails
add_action('admin_menu', 'wcb_add_referral_settings_page');

if (!function_exists('wcb_add_referral_settings_page')) {
function wcb_add_referral_settings_page() {
    add_options_page(
        'Referral Notifications',
        'Referral Notifications', 
        'manage_options',
        'wcb-referral-notifications',
        'wcb_referral_notifications_page'
    );
}
}

if (!function_exists('wcb_referral_notifications_page')) {
function wcb_referral_notifications_page() {
    if (isset($_POST['submit'])) {
        update_option('wcb_referral_notification_emails', sanitize_textarea_field($_POST['notification_emails']));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    $current_emails = get_option('wcb_referral_notification_emails', '');
    ?>
    <div class="wrap">
        <h1>Referral Notification Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Notification Email Addresses</th>
                    <td>
                        <textarea name="notification_emails" rows="4" cols="50" class="large-text"><?php echo esc_textarea($current_emails); ?></textarea>
                        <p class="description">
                            Enter email addresses that should receive referral notifications, separated by commas.<br>
                            Example: manager@westcityboxing.nz, coordinator@westcityboxing.nz<br>
                            Leave empty to only send to WordPress administrators.
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <h3>Current Notification Recipients</h3>
        <p>Referral notifications will be sent to:</p>
        <ul>
            <li><strong>WordPress Admin Email:</strong> <?php echo get_option('admin_email'); ?></li>
            <?php 
            $admin_users = get_users(['role' => 'administrator']);
            foreach ($admin_users as $admin): ?>
                <li><strong>Administrator:</strong> <?php echo esc_html($admin->display_name . ' (' . $admin->user_email . ')'); ?></li>
            <?php endforeach; ?>
            <?php if (!empty($current_emails)): 
                $additional_emails = array_map('trim', explode(',', $current_emails));
                foreach ($additional_emails as $email): 
                    if (is_email($email)): ?>
                        <li><strong>Additional:</strong> <?php echo esc_html($email); ?></li>
                    <?php endif;
                endforeach;
            endif; ?>
        </ul>
    </div>
    <?php
}
}