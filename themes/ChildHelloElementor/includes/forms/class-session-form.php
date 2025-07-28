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
    
    // Get groups for dropdown using proven logic from active-members-test.php
    $groups = wcb_get_all_groups();
    
    // Get schools for dropdown
    $schools = get_terms([
        'taxonomy' => 'school', // Your school taxonomy
        'hide_empty' => false
    ]);
    
    // Note: Members will be loaded dynamically via AJAX when a group is selected
    
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
                <label for="selected_group">Class/Program *</label>
                <select id="selected_group" name="selected_group" onchange="loadGroupMembers()">
                    <option value="">Select Class/Program</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group->ID; ?>">
                            <?php echo esc_html($group->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="group-info" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; display: none;">
                    <small id="group-member-count" style="color: #6c757d;"></small>
                </div>
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
            
            <!-- 5. Attendance List (Dynamic based on selected group) -->
            <div class="form-row">
                <label>Attendance</label>
                <div class="attendance-section">
                    <p class="field-description">Select students who attended this session:</p>
                    <div id="attendance-loading" style="display: none; padding: 20px; text-align: center; color: #6c757d;">
                        <span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span>
                        Loading members...
                    </div>
                    <div id="attendance-empty" style="padding: 20px; text-align: center; color: #6c757d; background: #f8f9fa; border-radius: 4px;">
                        Please select a class/program above to load members for attendance tracking.
                    </div>
                    <div id="attendance-grid" class="checkbox-grid" style="display: none;">
                        <!-- Members will be loaded here via AJAX -->
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
                    <!-- Options will be populated when a group is selected -->
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
        const groupSelect = document.getElementById('selected_group');
        const schoolSelect = document.getElementById('selected_school');

        if (!classType) return;

        if (classType.value === 'School') {
            schoolSelection.style.display = 'block';
            classSelection.style.display = 'none';
            schoolSelect.required = true;
            groupSelect.required = false;
            // Clear attendance when switching to school
            clearAttendanceList();
        } else if (classType.value === 'Class') {
            schoolSelection.style.display = 'none';
            classSelection.style.display = 'block';
            schoolSelect.required = false;
            groupSelect.required = true;
            // Show empty state when switching to class
            showAttendanceEmpty();
        }
    }

    function loadGroupMembers() {
        const groupSelect = document.getElementById('selected_group');
        const groupId = groupSelect.value;

        if (!groupId) {
            showAttendanceEmpty();
            clearStudentSelect();
            hideGroupInfo();
            return;
        }

        showAttendanceLoading();

        // Make AJAX request to load group members
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'wcb_load_group_members',
                group_id: groupId,
                nonce: '<?php echo wp_create_nonce('wcb_load_group_members'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateAttendanceList(data.data.members);
                populateStudentSelect(data.data.members);
                showGroupInfo(data.data.group_name, data.data.member_count);
            } else {
                showAttendanceError(data.data || 'Failed to load members');
            }
        })
        .catch(error => {
            console.error('Error loading group members:', error);
            showAttendanceError('Failed to load members');
        });
    }

    function showAttendanceLoading() {
        document.getElementById('attendance-loading').style.display = 'block';
        document.getElementById('attendance-empty').style.display = 'none';
        document.getElementById('attendance-grid').style.display = 'none';
    }

    function showAttendanceEmpty() {
        document.getElementById('attendance-loading').style.display = 'none';
        document.getElementById('attendance-empty').style.display = 'block';
        document.getElementById('attendance-grid').style.display = 'none';
    }

    function showAttendanceError(message) {
        document.getElementById('attendance-loading').style.display = 'none';
        document.getElementById('attendance-empty').style.display = 'block';
        document.getElementById('attendance-empty').innerHTML = '<span style="color: #dc3545;">Error: ' + message + '</span>';
        document.getElementById('attendance-grid').style.display = 'none';
    }

    function populateAttendanceList(members) {
        const grid = document.getElementById('attendance-grid');
        grid.innerHTML = '';

        members.forEach(member => {
            const label = document.createElement('label');
            label.className = 'checkbox-item';
            label.innerHTML = `
                <input type="checkbox" name="attendance_list[]" value="${member.id}">
                ${member.name}
            `;
            grid.appendChild(label);
        });

        document.getElementById('attendance-loading').style.display = 'none';
        document.getElementById('attendance-empty').style.display = 'none';
        document.getElementById('attendance-grid').style.display = 'block';
    }

    function populateStudentSelect(members) {
        const select = document.getElementById('which_student_did_you_have_an_intervention_or_concern_for');
        select.innerHTML = '<option value="">Select Student (if applicable)</option>';

        members.forEach(member => {
            const option = document.createElement('option');
            option.value = member.id;
            option.textContent = member.name;
            select.appendChild(option);
        });
    }

    function clearStudentSelect() {
        const select = document.getElementById('which_student_did_you_have_an_intervention_or_concern_for');
        select.innerHTML = '<option value="">Select Student (if applicable)</option>';
    }

    function clearAttendanceList() {
        document.getElementById('attendance-grid').innerHTML = '';
        showAttendanceEmpty();
        clearStudentSelect();
        hideGroupInfo();
    }

    function showGroupInfo(groupName, memberCount) {
        const info = document.getElementById('group-info');
        const countText = document.getElementById('group-member-count');
        countText.textContent = `${memberCount} active members in ${groupName}`;
        info.style.display = 'block';
    }

    function hideGroupInfo() {
        document.getElementById('group-info').style.display = 'none';
    }

    // Add CSS for spinning animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
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
    
    if ($_POST['class_type'] === 'Class' && empty($_POST['selected_group'])) {
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
    
    // Save school or group based on type
    if ($class_type === 'School') {
        wp_set_object_terms($post_id, intval($_POST['selected_school']), 'school');
    } else {
        update_field('selected_group', intval($_POST['selected_group']), $post_id);
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

// AJAX handler for loading group members
function wcb_ajax_load_group_members() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_load_group_members')) {
        wp_die('Security check failed');
    }

    $group_id = intval($_POST['group_id']);

    if (!$group_id) {
        wp_send_json_error('Invalid group ID');
    }

    // Get group information
    $group = get_post($group_id);
    if (!$group || $group->post_type !== 'memberpressgroup') {
        wp_send_json_error('Group not found');
    }

    // Use the exact same logic as active-members-test.php to get group members
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Check if MemberPress transactions table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    if (!$table_exists) {
        wp_send_json_error('MemberPress not properly configured');
    }

    // Get memberships in this group using the proven function
    $group_memberships = wcb_get_group_memberships($group_id);

    if (empty($group_memberships)) {
        wp_send_json_success([
            'group_name' => $group->post_title,
            'member_count' => 0,
            'members' => []
        ]);
    }

    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

    // Get members who have active transactions for memberships in this group
    // Use EXACT same query as active-members-test.php
    $group_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ", ...$membership_ids));

    // Format members for frontend
    $members = [];
    foreach ($group_members as $member) {
        $members[] = [
            'id' => $member->ID,
            'name' => $member->display_name,
            'email' => $member->user_email
        ];
    }

    wp_send_json_success([
        'group_name' => $group->post_title,
        'member_count' => count($members),
        'members' => $members
    ]);
}

// Register AJAX handlers
add_action('wp_ajax_wcb_load_group_members', 'wcb_ajax_load_group_members');
add_action('wp_ajax_nopriv_wcb_load_group_members', 'wcb_ajax_load_group_members');