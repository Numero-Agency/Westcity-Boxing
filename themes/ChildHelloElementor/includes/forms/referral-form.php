<?php
// Referral Form - Clean Version

// Safe meta update wrapper
if (!function_exists('wcb_update_meta_field')) {
    function wcb_update_meta_field($post_id, $field, $value) {
        if (function_exists('update_field')) {
            return update_field($field, $value, $post_id);
        }
        return update_post_meta($post_id, $field, $value);
    }
}

// Referral form shortcode
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
                        <label for="contact_phone">Contact Phone Number *</label>
                        <input type="tel" id="contact_phone" name="contact_phone" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="contact_email">Contact Email Address *</label>
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
                
                <!-- Address Information Section -->
                <div class="form-section">
                    <h3>Address Information</h3>
                    
                    <div class="form-row">
                        <label for="address">Address *</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <label for="suburb">Suburb *</label>
                        <input type="text" id="suburb" name="suburb" required>
                    </div>
                </div>
                
                <!-- Additional Information Section -->
                <div class="form-section">
                    <h3>Additional Information</h3>
                    
                    <div class="form-row">
                        <label for="medical_information">Medical Information</label>
                        <textarea id="medical_information" name="medical_information" rows="4" 
                            placeholder="Any medical conditions or mental health information..."></textarea>
                    </div>
                    
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
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="4" 
                            placeholder="e.g. hobbies, interests, goals, motivation for referral..."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <label for="referral_date">Referral Date *</label>
                        <input type="date" id="referral_date" name="referral_date" required>
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
             box-sizing: border-box;
             font-family: inherit;
             background-color: #ffffff !important;
             color: #333333 !important;
             cursor: text !important;
             pointer-events: auto !important;
             opacity: 1 !important;
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
         
         /* Ensure fields are editable - override any conflicting styles */
         .wcb-form-container input,
         .wcb-form-container textarea,
         .wcb-form-container select {
             -webkit-user-select: text !important;
             -moz-user-select: text !important;
             -ms-user-select: text !important;
             user-select: text !important;
             pointer-events: auto !important;
             cursor: text !important;
             background-color: white !important;
             color: #333 !important;
         }
         
          .wcb-form-container select {
              cursor: pointer !important;
          }
          
          /* Override browser default validation styling completely */
          .wcb-form-container input,
          .wcb-form-container select,
          .wcb-form-container textarea {
              -webkit-appearance: none !important;
              -moz-appearance: none !important;
              appearance: none !important;
          }
          
          /* Remove browser's red outline on invalid fields */
          .wcb-form-container input:invalid,
          .wcb-form-container select:invalid,
          .wcb-form-container textarea:invalid {
              box-shadow: none !important;
              outline: none !important;
          }
         
          /* Remove any readonly styling */
          .wcb-form-container input:not([readonly]),
          .wcb-form-container textarea:not([readonly]),
          .wcb-form-container select:not([disabled]) {
              background-color: white !important;
              color: #333 !important;
              opacity: 1 !important;
          }
          
          /* Prevent red borders on required fields until user interacts */
          .wcb-form-container input:required:invalid,
          .wcb-form-container select:required:invalid,
          .wcb-form-container textarea:required:invalid {
              border-color: #ddd !important;
              box-shadow: none !important;
          }
          
          /* Only show validation styling after user has interacted with field */
          .wcb-form-container input:required:invalid.user-interacted,
          .wcb-form-container select:required:invalid.user-interacted,
          .wcb-form-container textarea:required:invalid.user-interacted {
              border-color: #e74c3c !important;
              box-shadow: 0 0 5px rgba(231, 76, 60, 0.3) !important;
          }
          
          /* Valid fields get green styling only after interaction */
          .wcb-form-container input:required:valid.user-interacted,
          .wcb-form-container select:required:valid.user-interacted,
          .wcb-form-container textarea:required:valid.user-interacted {
              border-color: #27ae60 !important;
              box-shadow: 0 0 5px rgba(39, 174, 96, 0.3) !important;
          }
         </style>
         
         <script>
         // Ensure form fields are editable on page load
         document.addEventListener('DOMContentLoaded', function() {
             console.log('Referral form loaded - checking field editability...');
             
             const formFields = document.querySelectorAll('.wcb-referral-form input, .wcb-referral-form select, .wcb-referral-form textarea');
             
          formFields.forEach(function(field, index) {
              // Remove any readonly or disabled attributes that might be set
              field.removeAttribute('readonly');
              field.removeAttribute('disabled');
              
              // Ensure proper styling
              field.style.backgroundColor = 'white';
              field.style.color = '#333';
              field.style.opacity = '1';
              field.style.pointerEvents = 'auto';
              field.style.cursor = field.tagName.toLowerCase() === 'select' ? 'pointer' : 'text';
              
              // Add user interaction tracking for validation styling
              field.addEventListener('focus', function() {
                  console.log('Field focused:', field.name || field.id, 'Type:', field.type);
                  this.classList.add('user-interacted');
              });
              
              field.addEventListener('input', function() {
                  console.log('Field input detected:', field.name || field.id);
                  this.classList.add('user-interacted');
              });
              
              field.addEventListener('blur', function() {
                  this.classList.add('user-interacted');
              });
              
              field.addEventListener('change', function() {
                  this.classList.add('user-interacted');
              });
          });
             
             console.log('Found', formFields.length, 'form fields. All should now be editable.');
             
             // Additional check for any potential blocking elements
             const formContainer = document.querySelector('.wcb-form-container');
             if (formContainer) {
                 formContainer.style.pointerEvents = 'auto';
                 console.log('Form container pointer events set to auto');
             }
         });
         </script>
         
         <?php
         return ob_get_clean();
    }
}

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
            return ['success' => false, 'message' => 'Please enter a valid email address'];
        }
        
        if (!empty($_POST['parent_email']) && !filter_var($_POST['parent_email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid parent/guardian email address'];
        }
        
        // Create referral title
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $referral_date = sanitize_text_field($_POST['referral_date']);
        $referral_title = "Referral: {$first_name} {$last_name} - " . date('M j, Y', strtotime($referral_date));
        
        // Create new referral post
        $post_data = [
            'post_title' => $referral_title,
            'post_type' => 'referral',
            'post_status' => 'publish',
            'post_content' => ''
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return ['success' => false, 'message' => 'Failed to create referral'];
        }
        
        // Save form fields
        $fields_to_save = [
            'first_name', 'last_name', 'date_of_birth', 'ethnicity', 'gender',
            'contact_phone', 'contact_email', 'parent_name', 'parent_phone', 'parent_email',
            'medical_information', 'address', 'suburb', 'referrer_name', 'agency',
            'referrer_contact', 'notes', 'referral_date'
        ];
        
        foreach ($fields_to_save as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_textarea_field($_POST[$field]);
                wcb_update_meta_field($post_id, $field, $value);
            }
        }
        
        // Set referral status
        wcb_update_meta_field($post_id, 'referral_status', 'pending');
        
        // Send notification
        $notification_result = wcb_send_referral_notifications($post_id, $_POST);
        
        return [
            'success' => true, 
            'post_id' => $post_id,
            'notifications' => $notification_result
        ];
    }
}

// Send email notifications
if (!function_exists('wcb_send_referral_notifications')) {
    function wcb_send_referral_notifications($post_id, $form_data) {
        $first_name = sanitize_text_field($form_data['first_name']);
        $last_name = sanitize_text_field($form_data['last_name']);
        
        // Get admin emails
        $admin_emails = [get_option('admin_email')];
        
        // Get all administrator users
        $admin_users = get_users(['role' => 'administrator']);
        foreach ($admin_users as $admin) {
            if (!in_array($admin->user_email, $admin_emails)) {
                $admin_emails[] = $admin->user_email;
            }
        }
        
        // Add custom notification emails from settings
        $wcb_notification_emails = get_option('wcb_referral_notification_emails', '');
        if (!empty($wcb_notification_emails)) {
            $additional_emails = array_map('trim', explode(',', $wcb_notification_emails));
            foreach ($additional_emails as $email) {
                if (is_email($email) && !in_array($email, $admin_emails)) {
                    $admin_emails[] = $email;
                }
            }
        }
        
        $admin_emails = array_filter(array_unique($admin_emails), 'is_email');
        
        $result = ['admin_sent' => false];
        
        // Prepare email
        $subject = "New Referral Submitted: {$first_name} {$last_name}";
        $admin_url = admin_url("post.php?post={$post_id}&action=edit");
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #2c3e50; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>🥊 New Referral Submitted</h1>
            </div>
            
            <div style='padding: 30px; background: #f8f9fa;'>
                <div style='background: white; padding: 25px; border-radius: 8px;'>
                    <h2>Young Person Details</h2>
                    <p><strong>Name:</strong> {$first_name} {$last_name}</p>
                    <p><strong>Date of Birth:</strong> " . esc_html($form_data['date_of_birth']) . "</p>
                    <p><strong>Contact:</strong> " . esc_html($form_data['contact_phone']) . " | " . esc_html($form_data['contact_email']) . "</p>
                    <p><strong>Address:</strong> " . esc_html($form_data['address']) . ", " . esc_html($form_data['suburb']) . "</p>
                    
                    <div style='margin-top: 30px; text-align: center;'>
                        <a href='{$admin_url}' style='background: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>View Full Referral</a>
                    </div>
                </div>
            </div>
        </div>";
        
        // Send emails
        if (!empty($admin_emails)) {
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: West City Boxing <' . get_option('admin_email') . '>'
            ];
            
            foreach ($admin_emails as $admin_email) {
                $sent = wp_mail($admin_email, $subject, $message, $headers);
                if ($sent) {
                    $result['admin_sent'] = true;
                }
            }
        }
        
        return $result;
    }
}

// Register shortcode
add_action('init', function() {
    add_shortcode('wcb_referral_form', 'wcb_referral_form_shortcode');
});

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