<?php
// Referral Form

function wcb_referral_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_referral']) && wp_verify_nonce($_POST['referral_nonce'], 'submit_referral')) {
        $result = wcb_handle_referral_submission();
        if ($result['success']) {
            echo '<div class="form-success">✅ Referral submitted successfully!</div>';
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
add_shortcode('wcb_referral_form', 'wcb_referral_form_shortcode');

// Handle referral form submission
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
            update_field($field, $value, $post_id);
        }
    }
    
    // Set referral status
    update_field('referral_status', 'pending', $post_id);
    
    return ['success' => true, 'post_id' => $post_id];
} 