<?php
// Class Session Form (handles both Class and School types)

function wcb_class_session_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_class_session']) && wp_verify_nonce($_POST['class_session_nonce'], 'submit_class_session')) {
        $result = wcb_handle_class_session_submission();
        if ($result['success']) {
            echo '<div class="form-success">✅ Session logged successfully!</div>';
        } else {
            echo '<div class="form-error">❌ Error: ' . $result['message'] . '</div>';
        }
    }
    
    // Get memberships for dropdown
    $memberships = get_posts([
        'post_type' => 'memberpressproduct',
        'numberposts' => -1,
        'post_status' => 'publish'
    ]);
    
    // Get schools for dropdown
    $schools = get_terms([
        'taxonomy' => 'school', // Your school taxonomy
        'hide_empty' => false
    ]);
    
    // Get all users for attendance and instructor selection
    $users = get_users(['role__in' => ['subscriber', 'member', 'customer']]);
    
    // Get instructors (assuming they have a specific role or capability)
    $instructors = get_users(['role__in' => ['administrator', 'editor', 'instructor']]);
    
    ob_start();
    ?>
    <div class="wcb-form-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-groups"></span> Log Class Session</h2>
            <p>Record a class session at the gym or at a school</p>
        </div>
        
        <form method="post" class="wcb-session-form">
            <?php wp_nonce_field('submit_class_session', 'class_session_nonce'); ?>
            
            <!-- 1. Date of Session -->
            <div class="form-row">
                <label for="session_date">Date of Session *</label>
                <div class="date-input-wrapper">
                    <input type="datetime-local" id="session_date" name="session_date" required>
                </div>
            </div>
            
            <!-- 2. Class Type (Radio Buttons) -->
            <div class="form-row">
                <label>Class Type *</label>
                <div class="radio-group">
                    <label class="radio-item">
                        <input type="radio" name="class_type" value="Class" required onchange="toggleLocationFields()">
                        <span>Class</span>
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="class_type" value="School" required onchange="toggleLocationFields()">
                        <span>School</span>
                    </label>
                </div>
            </div>
            
            <!-- 3. Class Selection (shows when Class is selected) -->
            <div class="form-row" id="class-selection" style="display: none;">
                <label for="selected_membership">Class *</label>
                <select id="selected_membership" name="selected_membership">
                    <option value="">Select Class/Program</option>
                    <?php foreach ($memberships as $membership): ?>
                        <option value="<?php echo $membership->ID; ?>">
                            <?php echo esc_html($membership->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 4. School Selection (shows when School is selected) -->
            <div class="form-row" id="school-selection" style="display: none;">
                <label for="selected_school">Select School *</label>
                <select id="selected_school" name="selected_school">
                    <option value="middle-school-west-auckland">Middle School West Auckland</option>
                    <?php foreach ($schools as $school): ?>
                        <option value="<?php echo $school->term_id; ?>">
                            <?php echo esc_html($school->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 5. Attendance List (Repeater-style) -->
            <div class="form-row">
                <label>Attendance</label>
                <div class="attendance-section">
                    <p class="field-description">Select students who attended this session:</p>
                    <div class="checkbox-grid">
                        <?php foreach ($users as $user): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="attendance_list[]" value="<?php echo $user->ID; ?>">
                                <?php echo esc_html($user->display_name); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- 6. Instructor -->
            <div class="form-row">
                <label for="instructor">Instructor</label>
                <select id="instructor" name="instructor">
                    <option value="">Select Instructor</option>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?php echo $instructor->ID; ?>">
                            <?php echo esc_html($instructor->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 7. What Conversations have you had with a young person -->
            <div class="form-row">
                <label for="what_conversations_have_you_had_with_a_young_person">What Conversations have you had with a young person</label>
                <textarea id="what_conversations_have_you_had_with_a_young_person" name="what_conversations_have_you_had_with_a_young_person" rows="6" 
                    placeholder="Describe any conversations you had with students during this session..."></textarea>
            </div>
            
            <!-- 8. Interventions/concerns you have for a young person -->
            <div class="form-row">
                <label for="interventionsconcerns_you_have_for_a_young_person">Interventions/concerns you have for a young person</label>
                <textarea id="interventionsconcerns_you_have_for_a_young_person" name="interventionsconcerns_you_have_for_a_young_person" rows="6" 
                    placeholder="Note any interventions needed or concerns observed during this session..."></textarea>
            </div>
            
            <!-- 9. Which student did you have an intervention or concern for -->
            <div class="form-row">
                <label for="which_student_did_you_have_an_intervention_or_concern_for">Which student did you have an intervention or concern for</label>
                <select id="which_student_did_you_have_an_intervention_or_concern_for" name="which_student_did_you_have_an_intervention_or_concern_for">
                    <option value="">Select Student (if applicable)</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user->ID; ?>">
                            <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 10. Note to Manager -->
            <div class="form-row">
                <label for="note_to_manager">Note to Manager</label>
                <textarea id="note_to_manager" name="note_to_manager" rows="4" 
                    placeholder="Any important information or updates for the manager..."></textarea>
            </div>
            
            <!-- 11. Health and Safety -->
            <div class="form-row">
                <label for="health_and_safety">Health and Safety</label>
                <textarea id="health_and_safety" name="health_and_safety" rows="4" 
                    placeholder="Any health and safety observations or incidents..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="submit_class_session" class="btn-submit">
                    Log Session
                </button>
                <a href="/dashboard/sessions" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
    function toggleLocationFields() {
        const classType = document.querySelector('input[name="class_type"]:checked');
        const classSelection = document.getElementById('class-selection');
        const schoolSelection = document.getElementById('school-selection');
        const classSelect = document.getElementById('selected_membership');
        const schoolSelect = document.getElementById('selected_school');
        
        if (!classType) return;
        
        if (classType.value === 'School') {
            schoolSelection.style.display = 'block';
            classSelection.style.display = 'none';
            schoolSelect.required = true;
            classSelect.required = false;
        } else if (classType.value === 'Class') {
            schoolSelection.style.display = 'none';
            classSelection.style.display = 'block';
            schoolSelect.required = false;
            classSelect.required = true;
        }
    }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_class_session_form', 'wcb_class_session_form_shortcode');

// Handle class session form submission
function wcb_handle_class_session_submission() {
    // Validate required fields
    if (empty($_POST['session_date']) || empty($_POST['class_type'])) {
        return ['success' => false, 'message' => 'Please fill in all required fields'];
    }
    
    // Validate specific requirements based on class type
    if ($_POST['class_type'] === 'School' && empty($_POST['selected_school'])) {
        return ['success' => false, 'message' => 'Please select a school'];
    }
    
    if ($_POST['class_type'] === 'Class' && empty($_POST['selected_membership'])) {
        return ['success' => false, 'message' => 'Please select a class/program'];
    }
    
    // Create session title
    $class_type = sanitize_text_field($_POST['class_type']);
    $session_title = $class_type . ' Session - ' . date('M j, Y', strtotime($_POST['session_date']));
    
    // Create new session post
    $post_data = [
        'post_title' => $session_title,
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'post_content' => '' // We'll use custom fields for content
    ];
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return ['success' => false, 'message' => 'Failed to create session'];
    }
    
    // Save basic fields
    update_field('session_date', sanitize_text_field($_POST['session_date']), $post_id);
    update_field('class_type', $class_type, $post_id);
    
    // Save school or membership based on type
    if ($class_type === 'School') {
        wp_set_object_terms($post_id, intval($_POST['selected_school']), 'school');
    } else {
        update_field('selected_membership', intval($_POST['selected_membership']), $post_id);
    }
    
    // Save attendance list (as repeater-style data)
    if (!empty($_POST['attendance_list'])) {
        $attendance_data = [];
        foreach ($_POST['attendance_list'] as $user_id) {
            $attendance_data[] = [
                'student' => intval($user_id),
                'status' => 'present'
            ];
        }
        update_field('attendance_list', $attendance_data, $post_id);
        
        // Also save in the old format for backward compatibility
        $attended = array_map('intval', $_POST['attendance_list']);
        update_field('attended_students', $attended, $post_id);
    }
    
    // Save instructor
    if (!empty($_POST['instructor'])) {
        update_field('instructor', intval($_POST['instructor']), $post_id);
    }
    
    // Save text area fields
    if (!empty($_POST['what_conversations_have_you_had_with_a_young_person'])) {
        update_field('what_conversations_have_you_had_with_a_young_person', 
            sanitize_textarea_field($_POST['what_conversations_have_you_had_with_a_young_person']), $post_id);
    }
    
    if (!empty($_POST['interventionsconcerns_you_have_for_a_young_person'])) {
        update_field('interventionsconcerns_you_have_for_a_young_person', 
            sanitize_textarea_field($_POST['interventionsconcerns_you_have_for_a_young_person']), $post_id);
    }
    
    if (!empty($_POST['note_to_manager'])) {
        update_field('note_to_manager', 
            sanitize_textarea_field($_POST['note_to_manager']), $post_id);
    }
    
    // Save intervention concern student
    if (!empty($_POST['which_student_did_you_have_an_intervention_or_concern_for'])) {
        update_field('which_student_did_you_have_an_intervention_or_concern_for', 
            intval($_POST['which_student_did_you_have_an_intervention_or_concern_for']), $post_id);
    }
    
    // Save health and safety
    if (!empty($_POST['health_and_safety'])) {
        update_field('health_and_safety', sanitize_textarea_field($_POST['health_and_safety']), $post_id);
    }
    
    // Set session type taxonomy to "Class"
    wp_set_object_terms($post_id, 'Class', 'session_type');
    
    return ['success' => true, 'post_id' => $post_id];
}