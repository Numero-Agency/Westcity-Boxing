<?php
// Intervention Form (for one-on-one mentoring sessions)

function wcb_intervention_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_intervention']) && wp_verify_nonce($_POST['intervention_nonce'], 'submit_intervention')) {
        $result = wcb_handle_intervention_submission();
        if ($result['success']) {
            // Show popup success message and redirect
            echo '
            <div id="success-popup" class="wcb-success-popup">
                <div class="popup-content">
                    <div class="popup-icon">✅</div>
                    <h3>Intervention Logged Successfully!</h3>
                    <p>Your intervention session has been recorded and saved.</p>
                    <p class="redirect-message">Refreshing form...</p>
                    <div class="popup-loader"></div>
                </div>
            </div>
            <style>
                .wcb-success-popup {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                }
                .popup-content {
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    text-align: center;
                    max-width: 400px;
                    width: 90%;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                }
                .popup-icon {
                    font-size: 48px;
                    margin-bottom: 20px;
                    display: block;
                }
                .popup-content h3 {
                    margin: 0 0 15px 0;
                    font-size: 24px;
                    font-weight: 600;
                    color: #000000;
                }
                .popup-content p {
                    margin: 0 0 10px 0;
                    font-size: 16px;
                    color: #666666;
                    line-height: 1.5;
                }
                .redirect-message {
                    font-weight: 500;
                    color: #007bff !important;
                    margin-top: 20px !important;
                }
                .popup-loader {
                    width: 30px;
                    height: 30px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #007bff;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 20px auto 0;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @media (max-width: 768px) {
                    .popup-content {
                        padding: 30px 20px;
                        margin: 20px;
                    }
                    .popup-icon {
                        font-size: 40px;
                    }
                    .popup-content h3 {
                        font-size: 20px;
                    }
                    .popup-content p {
                        font-size: 14px;
                    }
                }
            </style>
            <script>
                // Redirect to same page after 3 seconds (prevents form resubmission)
                setTimeout(function() {
                    window.location.href = window.location.pathname;
                }, 3000);
            </script>';
            return; // Stop further execution
        } else {
            echo '<div class="form-error">❌ Error: ' . $result['message'] . '</div>';
        }
    }
    
    // Define staff members list
    $staff_members = [
        'Xarisma Paga',
        'Dion Tafa',
        'Hala Houma',
        'Jasmin bunton',
        'Zarah Kumar',
        'Sebastian Grey',
        'Matthew Grey',
        'Shamil Kumar'
    ];

    // Get students who are part of WBC Mentoring program (ID: 1738) using proven logic
    $mentoring_students = wcb_get_mentoring_program_members();
    
    ob_start();
    ?>
    <div class="wcb-form-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-admin-users"></span> Log Intervention Session</h2>
            <p>Record a one-on-one mentoring session with a student</p>
        </div>
        
        <form method="post" class="wcb-session-form">
            <?php wp_nonce_field('submit_intervention', 'intervention_nonce'); ?>
            
            <div class="form-row">
                <label>Staff Members Who Attended *</label>
                <div class="checkbox-group">
                    <?php foreach ($staff_members as $index => $staff_member): ?>
                        <div class="checkbox-item">
                            <input type="checkbox"
                                  id="staff_<?php echo $index; ?>"
                                  name="staff_members_who_attended[]"
                                  value="<?php echo esc_attr($staff_member); ?>">
                            <label for="staff_<?php echo $index; ?>">
                                <?php echo esc_html($staff_member); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small>Select all staff members who attended the intervention</small>
            </div>
            
            <div class="form-row">
                <label for="intervention_date_">Intervention Date *</label>
                <input type="date" id="intervention_date_" name="intervention_date_" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-row">
                <label for="duration">Duration (minutes) *</label>
                <input type="number" id="duration" name="duration" min="1" max="480" required placeholder="60">
                <small>Enter the duration of the intervention in minutes</small>
            </div>
            
            <div class="form-row">
                <label for="meeting_location">Meeting Location *</label>
                <input type="text" id="meeting_location" name="meeting_location" required 
                    placeholder="e.g. Gym office, School counselor office, Community center">
            </div>
            
            <div class="form-row">
                <label for="student_involved">Student Involved *</label>
                <select id="student_involved" name="student_involved" required>
                    <option value="">Select Student</option>
                    <?php if (!empty($mentoring_students)): ?>
                        <?php foreach ($mentoring_students as $student): ?>
                            <option value="<?php echo $student->ID; ?>">
                                <?php echo esc_html($student->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No students found in WBC Mentoring program</option>
                    <?php endif; ?>
                </select>
                <small>Only students enrolled in the WBC Mentoring program are shown</small>
            </div>
            
            <div class="form-row">
                <label for="other_attendees">Who Else Attended?</label>
                <textarea id="other_attendees" name="other_attendees" rows="3" 
                    placeholder="List any other attendees (parents, siblings, other staff, etc.)"></textarea>
            </div>
            
            <div class="form-row">
                <label for="debrief_event">Debrief of Event *</label>
                <textarea id="debrief_event" name="debrief_event" rows="6" required 
                    placeholder="Provide a detailed debrief of the intervention session - what was discussed, outcomes, concerns, next steps, etc."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="submit_intervention" class="btn-submit">
                    Log Intervention
                </button>
                <a href="/dashboard/sessions" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    
    <style>
    .checkbox-group {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 4px;
        background: #f9f9f9;
    }
    .checkbox-item {
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }
    .checkbox-item input[type="checkbox"] {
        margin-right: 8px;
        margin-top: 0;
    }
    .checkbox-item label {
        margin: 0;
        cursor: pointer;
        font-weight: normal;
    }
    .wcb-session-form small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-style: italic;
    }
    </style>
    
    <script>
    // Validate that at least one staff member is selected
    document.querySelector('.wcb-session-form').addEventListener('submit', function(e) {
        const form = this;
        
        // Add attempted class for validation styling
        form.classList.add('attempted');
        
        const staffCheckboxes = document.querySelectorAll('input[name="staff_members_who_attended[]"]:checked');
        if (staffCheckboxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one staff member who attended the intervention.');
            return false;
        }
        
        // Check if form is valid
        if (!form.checkValidity()) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
    });
    
    // Remove validation styling when user starts typing/selecting
    document.querySelectorAll('.form-row input, .form-row select, .form-row textarea').forEach(function(field) {
        field.addEventListener('input', function() {
            if (this.form.classList.contains('attempted') && this.validity.valid) {
                this.style.borderColor = '#27ae60';
            }
        });
        
        field.addEventListener('focus', function() {
            this.style.borderColor = '#000';
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_intervention_form', 'wcb_intervention_form_shortcode');

// Handle intervention form submission
function wcb_handle_intervention_submission() {
    // Simple duplicate prevention - just prevent rapid submissions
    $user_identifier = get_current_user_id() ?: $_SERVER['REMOTE_ADDR'];
    $submission_key = 'wcb_intervention_cooldown_' . md5($user_identifier);

    // Check if user submitted recently (within 5 seconds)
    if (get_transient($submission_key)) {
        return ['success' => false, 'message' => 'Please wait a moment before submitting another intervention.'];
    }

    // Set submission cooldown for 5 seconds
    set_transient($submission_key, true, 5);

    // Basic validation
    if (empty($_POST['intervention_date_']) || empty($_POST['student_involved'])) {
        return ['success' => false, 'message' => 'Please fill in all required fields'];
    }
    
    // Get student info - handle both user IDs and referral IDs
    $student_involved = sanitize_text_field($_POST['student_involved']);
    $student_display_name = '';
    $is_referral = false;
    $referral_id = null;
    $user_id = null;
    
    if (strpos($student_involved, 'referral_') === 0) {
        // This is a referral participant (not a WP user)
        $is_referral = true;
        $referral_id = intval(str_replace('referral_', '', $student_involved));
        
        // Validate referral exists and is processed
        $referral = get_post($referral_id);
        if (!$referral || $referral->post_type !== 'referral') {
            return ['success' => false, 'message' => 'Invalid referral selected'];
        }
        
        $referral_status = get_field('referral_status', $referral_id) ?: get_post_meta($referral_id, 'referral_status', true);
        if ($referral_status !== 'processed') {
            return ['success' => false, 'message' => 'This referral has not been processed yet'];
        }
        
        // Get display name from referral
        $first_name = get_field('first_name', $referral_id) ?: get_post_meta($referral_id, 'first_name', true);
        $last_name = get_field('last_name', $referral_id) ?: get_post_meta($referral_id, 'last_name', true);
        $student_display_name = trim($first_name . ' ' . $last_name);
        
    } else {
        // This is a regular WP user (MemberPress member)
        $user_id = intval($student_involved);
        $student = get_user_by('ID', $user_id);
        if (!$student) {
            return ['success' => false, 'message' => 'Invalid student selected'];
        }
        $student_display_name = $student->display_name;
    }
    
    // Create session title
    $date = sanitize_text_field($_POST['intervention_date_']);
    $session_title = 'Mentoring Session - ' . $student_display_name . ' - ' . date('M j, Y', strtotime($date));
    
    // Prepare meta input
    $meta_input = [
        'staff_members_who_attended' => array_map('sanitize_text_field', $_POST['staff_members_who_attended']),
        'intervention_date_' => $_POST['intervention_date_'],
        'duration' => $_POST['duration'],
        'meeting_location' => sanitize_text_field($_POST['meeting_location']),
        'student_involved' => $student_involved, // Store as-is (either user ID or referral_123)
        'student_display_name' => $student_display_name, // Store display name for easy reference
        'is_referral_participant' => $is_referral ? 'yes' : 'no',
        'other_attendees' => sanitize_textarea_field($_POST['other_attendees']),
        'debrief_event' => sanitize_textarea_field($_POST['debrief_event']),
        'selected_membership' => 1738 // Link to WCB Mentoring membership
    ];
    
    // If it's a referral, also store the referral_id for easy linking
    if ($is_referral && $referral_id) {
        $meta_input['referral_participant_id'] = $referral_id;
    }
    
    // Create new session post
    $post_data = [
        'post_title' => $session_title,
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'post_author' => get_current_user_id() ?: 1,
        'meta_input' => $meta_input
    ];
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return ['success' => false, 'message' => 'Failed to create session log'];
    }
    
    // Set the session type taxonomy to "Mentoring"
    wp_set_object_terms($post_id, 'mentoring', 'session_type');

    return ['success' => true, 'message' => 'Intervention logged successfully', 'post_id' => $post_id];
}

// Function to get students who are part of WBC Mentoring program
// Also includes referrals with status "processed" (they don't need a MemberPress membership)
function wcb_get_mentoring_program_members() {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    $all_members = [];

    // PART 1: Get MemberPress members with active WBC Mentoring transactions
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    if ($table_exists) {
        // WBC Mentoring program ID
        $mentoring_program_id = 1738;

        // Get members who have active transactions for WBC Mentoring program
        $mentoring_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID, u.display_name, u.user_email
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            AND u.user_login != 'bwgdev'
            ORDER BY u.display_name
        ", $mentoring_program_id));
        
        foreach ($mentoring_members as $member) {
            $all_members[] = (object) [
                'ID' => $member->ID,
                'display_name' => $member->display_name,
                'user_email' => $member->user_email,
                'type' => 'member'
            ];
        }
    }
    
    // PART 2: Get referrals with status "processed" 
    // These are young people referred to the mentoring program who don't have a MemberPress membership
    
    // Build list of existing member emails to check for duplicates
    $existing_emails = [];
    foreach ($all_members as $member) {
        if (!empty($member->user_email)) {
            $existing_emails[] = strtolower($member->user_email);
        }
    }
    
    // Query for processed referrals - check both lowercase and capitalized
    $processed_referrals = get_posts([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'referral_status',
                'value' => 'processed',
                'compare' => '='
            ],
            [
                'key' => 'referral_status',
                'value' => 'Processed',
                'compare' => '='
            ]
        ]
    ]);
    
    $referrals_added = 0;
    $referrals_skipped = 0;
    
    foreach ($processed_referrals as $referral) {
        $first_name = function_exists('get_field') ? get_field('first_name', $referral->ID) : null;
        $first_name = $first_name ?: get_post_meta($referral->ID, 'first_name', true);
        
        $last_name = function_exists('get_field') ? get_field('last_name', $referral->ID) : null;
        $last_name = $last_name ?: get_post_meta($referral->ID, 'last_name', true);
        
        $contact_email = function_exists('get_field') ? get_field('contact_email', $referral->ID) : null;
        $contact_email = $contact_email ?: get_post_meta($referral->ID, 'contact_email', true);
        
        // Skip if email matches an existing member (duplicate check)
        if (!empty($contact_email) && in_array(strtolower($contact_email), $existing_emails)) {
            $referrals_skipped++;
            continue;
        }
        
        $display_name = trim($first_name . ' ' . $last_name);
        
        if (!empty($display_name)) {
            $all_members[] = (object) [
                'ID' => 'referral_' . $referral->ID,  // Prefix to distinguish from user IDs
                'display_name' => $display_name . ' (Referral)',
                'user_email' => $contact_email ?: '',
                'type' => 'referral',
                'referral_id' => $referral->ID
            ];
            $referrals_added++;
        }
    }
    
    wcb_debug_log("wcb_get_mentoring_program_members: Added {$referrals_added} referrals, skipped {$referrals_skipped} duplicates");
    
    // Sort all members by display name
    usort($all_members, function($a, $b) {
        return strcasecmp($a->display_name, $b->display_name);
    });

    return $all_members;
}

// Helper function to get referral details by ID
function wcb_get_referral_participant($referral_id) {
    $referral = get_post($referral_id);
    if (!$referral || $referral->post_type !== 'referral') {
        return null;
    }
    
    $first_name = get_field('first_name', $referral_id) ?: get_post_meta($referral_id, 'first_name', true);
    $last_name = get_field('last_name', $referral_id) ?: get_post_meta($referral_id, 'last_name', true);
    
    return (object) [
        'ID' => 'referral_' . $referral_id,
        'display_name' => trim($first_name . ' ' . $last_name),
        'referral_id' => $referral_id
    ];
}

/**
 * Helper function to get student info from a mentoring session
 * Handles both regular users (by ID) and referral participants (referral_123 format)
 * 
 * @param int $session_id The session post ID
 * @return array|null Array with 'name', 'email', 'is_referral', 'id' or null if not found
 */
function wcb_get_mentoring_student_info($session_id) {
    // First try to get the stored display name (fastest)
    $stored_name = get_field('student_display_name', $session_id) ?: get_post_meta($session_id, 'student_display_name', true);
    $is_referral = get_field('is_referral_participant', $session_id) ?: get_post_meta($session_id, 'is_referral_participant', true);
    
    // Get the student_involved field
    $student_involved = get_field('student_involved', $session_id);
    if (empty($student_involved)) {
        $student_involved = get_post_meta($session_id, 'student_involved', true);
    }
    
    if (empty($student_involved)) {
        return null;
    }
    
    // Check if it's a referral participant (format: referral_123)
    if (is_string($student_involved) && strpos($student_involved, 'referral_') === 0) {
        $referral_id = intval(str_replace('referral_', '', $student_involved));
        
        // If we have a stored name, use it
        if (!empty($stored_name)) {
            return [
                'name' => $stored_name,
                'email' => '',
                'is_referral' => true,
                'id' => 'referral_' . $referral_id,
                'referral_id' => $referral_id
            ];
        }
        
        // Otherwise look up from the referral post
        $referral = get_post($referral_id);
        if ($referral && $referral->post_type === 'referral') {
            $first_name = get_field('first_name', $referral_id) ?: get_post_meta($referral_id, 'first_name', true);
            $last_name = get_field('last_name', $referral_id) ?: get_post_meta($referral_id, 'last_name', true);
            $contact_email = get_field('contact_email', $referral_id) ?: get_post_meta($referral_id, 'contact_email', true);
            
            return [
                'name' => trim($first_name . ' ' . $last_name) ?: 'Unknown Referral',
                'email' => $contact_email ?: '',
                'is_referral' => true,
                'id' => 'referral_' . $referral_id,
                'referral_id' => $referral_id
            ];
        }
        
        return [
            'name' => 'Unknown Referral',
            'email' => '',
            'is_referral' => true,
            'id' => 'referral_' . $referral_id,
            'referral_id' => $referral_id
        ];
    }
    
    // It's a regular user ID
    $user_id = intval($student_involved);
    $user = get_user_by('ID', $user_id);
    
    if ($user) {
        return [
            'name' => $user->display_name,
            'email' => $user->user_email,
            'is_referral' => false,
            'id' => $user_id,
            'user_id' => $user_id
        ];
    }
    
    // If we have stored name but couldn't find user, still return it
    if (!empty($stored_name)) {
        return [
            'name' => $stored_name,
            'email' => '',
            'is_referral' => ($is_referral === 'yes'),
            'id' => $user_id
        ];
    }
    
    return null;
}