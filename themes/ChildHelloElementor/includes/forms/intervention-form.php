<?php
// Intervention Form (for one-on-one mentoring sessions)

function wcb_intervention_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_intervention']) && wp_verify_nonce($_POST['intervention_nonce'], 'submit_intervention')) {
        $result = wcb_handle_intervention_submission();
        if ($result['success']) {
            echo '<div class="form-success">✅ Intervention logged successfully!</div>';
        } else {
            echo '<div class="form-error">❌ Error: ' . $result['message'] . '</div>';
        }
    }
    
    // Get all users for staff selection
    $all_users = get_users(['role__in' => ['administrator', 'editor', 'author', 'contributor']]);

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
                    <?php foreach ($all_users as $user): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" 
                                  id="staff_<?php echo $user->ID; ?>" 
                                  name="staff_members_who_attended[]" 
                                  value="<?php echo $user->ID; ?>">
                            <label for="staff_<?php echo $user->ID; ?>">
                                <?php echo esc_html($user->display_name); ?>
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
    // Basic validation
    if (empty($_POST['intervention_date_']) || empty($_POST['student_involved'])) {
        return ['success' => false, 'message' => 'Please fill in all required fields'];
    }
    
    // Validate student exists
    $student_id = intval($_POST['student_involved']);
    $student = get_user_by('ID', $student_id);
    if (!$student) {
        return ['success' => false, 'message' => 'Invalid student selected'];
    }
    
    // Create session title
    $date = sanitize_text_field($_POST['intervention_date_']);
    $session_title = 'Mentoring Session - ' . $student->display_name . ' - ' . date('M j, Y', strtotime($date));
    
    // Create new session post
    $post_data = [
        'post_title' => $session_title,
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
        'meta_input' => [
            'staff_members_who_attended' => $_POST['staff_members_who_attended'], // Array of user IDs
            'intervention_date_' => $_POST['intervention_date_'],
            'duration' => $_POST['duration'],
            'meeting_location' => sanitize_text_field($_POST['meeting_location']),
            'student_involved' => intval($_POST['student_involved']),
            'other_attendees' => sanitize_textarea_field($_POST['other_attendees']),
            'debrief_event' => sanitize_textarea_field($_POST['debrief_event']),
            'selected_membership' => 1738 // Link to WCB Mentoring membership
        ]
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
function wcb_get_mentoring_program_members() {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Check if MemberPress transactions table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    if (!$table_exists) {
        return [];
    }

    // WBC Mentoring program ID
    $mentoring_program_id = 1738;

    // Get members who have active transactions for WBC Mentoring program
    // Use EXACT same logic as active-members-test.php
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

    return $mentoring_members;
}