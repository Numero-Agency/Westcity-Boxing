<?php
/**
 * Parent Registration System
 * Allows staff to invite parents and parents to register for family dashboard access
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent Invitation Form Shortcode (for staff)
 */
function wcb_parent_invitation_form_shortcode($atts) {
    // Check if user has permission (admin or staff)
    if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
        return '<div class="wcb-notice wcb-notice-error">You do not have permission to invite parents.</div>';
    }
    
    ob_start();
    ?>
    
    <div class="wcb-parent-invitation-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-email-alt"></span> Invite Parent to Family Dashboard</h2>
            <p>Send an invitation to a parent to access the family dashboard</p>
        </div>
        
        <form id="parent-invitation-form" class="wcb-session-form">
            <div class="form-row">
                <label for="parent_name">Parent's Full Name *</label>
                <input type="text" id="parent_name" name="parent_name" required placeholder="Enter parent's full name">
            </div>
            
            <div class="form-row">
                <label for="parent_email">Parent's Email Address *</label>
                <input type="email" id="parent_email" name="parent_email" required placeholder="Enter parent's email address">
            </div>
            
            <div class="form-row">
                <label for="children_names">Children's Names</label>
                <textarea id="children_names" name="children_names" placeholder="Enter the names of their children (one per line)" rows="4"></textarea>
                <small>Optional: List the children's names to help the parent identify their accounts</small>
            </div>
            
            <div class="form-row">
                <label for="personal_message">Personal Message (Optional)</label>
                <textarea id="personal_message" name="personal_message" placeholder="Add a personal message to the invitation email" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <span class="dashicons dashicons-email-alt"></span>
                    Send Invitation
                </button>
                <button type="button" class="btn-cancel" onclick="document.getElementById('parent-invitation-form').reset();">
                    <span class="dashicons dashicons-dismiss"></span>
                    Clear Form
                </button>
            </div>
        </form>
        
        <div id="invitation-result" style="margin-top: 20px;"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#parent-invitation-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.btn-submit');
            var $result = $('#invitation-result');
            
            // Get form data
            var formData = {
                action: 'wcb_send_parent_invitation',
                parent_name: $('#parent_name').val(),
                parent_email: $('#parent_email').val(),
                children_names: $('#children_names').val(),
                personal_message: $('#personal_message').val(),
                nonce: wcb_ajax.nonce
            };
            
            // Disable submit button
            $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Sending...');
            
            // Send AJAX request
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $result.html('<div class="wcb-notice wcb-notice-success">' + response.data.message + '</div>');
                        $form[0].reset();
                    } else {
                        $result.html('<div class="wcb-notice wcb-notice-error">Error: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $result.html('<div class="wcb-notice wcb-notice-error">Connection error. Please try again.</div>');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Send Invitation');
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}
add_shortcode('parent_invitation_form', 'wcb_parent_invitation_form_shortcode');

/**
 * Simple Parent Registration Form Shortcode (no token required)
 */
function wcb_parent_registration_form_shortcode($atts) {
    // Check if user is already logged in
    if (is_user_logged_in()) {
        return '<div class="wcb-notice wcb-notice-info">
            <p>You are already logged in!</p>
            <p><a href="/family-dashboard/" class="wcb-btn wcb-btn-primary">Go to Family Dashboard</a></p>
        </div>';
    }
    
    ob_start();
    ?>
    
    <div class="wcb-parent-registration-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-admin-users"></span> Parent Registration</h2>
            <p>Welcome to West City Boxing! Create your account to access the family dashboard.</p>
        </div>

        <form id="parent-registration-form" class="wcb-session-form">
            <div class="form-row">
                <label for="parent_name">Your Full Name *</label>
                <input type="text" id="parent_name" name="parent_name" required placeholder="Enter your full name">
            </div>

            <div class="form-row">
                <label for="parent_email">Email Address *</label>
                <input type="email" id="parent_email" name="parent_email" required placeholder="Enter your email address">
                <small>This will be your login email</small>
            </div>

            <div class="form-row">
                <label for="username">Choose a Username *</label>
                <input type="text" id="username" name="username" required placeholder="Enter your desired username">
                <small>This will be used to log into your account</small>
            </div>
            
            <div class="form-row">
                <label for="password">Choose a Password *</label>
                <input type="password" id="password" name="password" required placeholder="Enter a secure password">
                <small>Minimum 8 characters</small>
            </div>
            
            <div class="form-row">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
            </div>
            
            <div class="form-row">
                <label for="phone">Phone Number (Optional)</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <span class="dashicons dashicons-admin-users"></span>
                    Complete Registration
                </button>
            </div>
        </form>
        
        <div id="registration-result" style="margin-top: 20px;"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#parent-registration-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.btn-submit');
            var $result = $('#registration-result');
            
            // Validate passwords match
            var password = $('#password').val();
            var confirmPassword = $('#confirm_password').val();
            
            if (password !== confirmPassword) {
                $result.html('<div class="wcb-notice wcb-notice-error">Passwords do not match.</div>');
                return;
            }
            
            if (password.length < 8) {
                $result.html('<div class="wcb-notice wcb-notice-error">Password must be at least 8 characters long.</div>');
                return;
            }
            
            // Get form data
            var formData = {
                action: 'wcb_simple_parent_registration',
                parent_name: $('#parent_name').val(),
                parent_email: $('#parent_email').val(),
                username: $('#username').val(),
                password: password,
                phone: $('#phone').val(),
                nonce: wcb_ajax.nonce
            };
            
            // Disable submit button
            $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Creating Account...');
            
            // Send AJAX request
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $result.html('<div class="wcb-notice wcb-notice-success">' + response.data.message + '</div>');
                        
                        // Redirect to family dashboard after 2 seconds
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 2000);
                    } else {
                        $result.html('<div class="wcb-notice wcb-notice-error">Error: ' + response.data + '</div>');
                        $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-admin-users"></span> Complete Registration');
                    }
                },
                error: function() {
                    $result.html('<div class="wcb-notice wcb-notice-error">Connection error. Please try again.</div>');
                    $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-admin-users"></span> Complete Registration');
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}
add_shortcode('parent_registration_form', 'wcb_parent_registration_form_shortcode');
