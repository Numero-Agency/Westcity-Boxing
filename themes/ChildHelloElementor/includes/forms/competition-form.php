<?php
/**
 * Competition Form (ACF Post Type Version)
 * Form for logging competition events as 'competition' post type with ACF fields
 */

function wcb_competition_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_competition']) && wp_verify_nonce($_POST['competition_nonce'], 'submit_competition')) {
        $result = wcb_handle_competition_submission();
        if ($result['success']) {
            echo '<div class="form-success">✅ Competition logged successfully! <a href="' . esc_url(get_permalink($result['post_id'])) . '" target="_blank">View Competition</a></div>';
        } else {
            echo '<div class="form-error">❌ Error: ' . esc_html($result['message']) . '</div>';
        }
    }

    // Get only users with Competitive Team membership for student selection
    $competitive_team_id = 1932;
    $users = wcb_get_competitive_team_members();

    ob_start();
    ?>
    <div class="wcb-form-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-awards"></span> Competition Form</h2>
            <p>Log a new competition event below</p>
        </div>

        <?php if (empty($users)): ?>
            <div class="form-info">
                <p><strong>ℹ️ No Competitive Team Members Found</strong></p>
                <p>To log a competition, you need students who are part of the Competitive Team program. Please add students to the Competitive Team membership first.</p>
            </div>
        <?php endif; ?>

        <form method="post" class="competition-form"<?php echo empty($users) ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
            <?php wp_nonce_field('submit_competition', 'competition_nonce'); ?>
            <div class="form-group">
                <label for="event_name">Event Name *</label>
                <input type="text" name="event_name" id="event_name" required>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" name="event_date" id="event_date" required>
            </div>
            <div class="form-group">
                <label for="where_was_it_hosted">Where was it hosted? *</label>
                <input type="text" name="where_was_it_hosted" id="where_was_it_hosted" required>
            </div>
            <div class="form-group">
                <label>Students involved * <small>(Competitive Team members only)</small></label>
                <?php if (empty($users)): ?>
                    <div class="students-selection-empty">
                        <p>No competitive team members found</p>
                    </div>
                <?php else: ?>
                    <div class="students-selection-container">
                        <div class="students-checkboxes">
                            <?php foreach ($users as $user): ?>
                            <label class="student-checkbox">
                                <span class="student-name"><?php echo esc_html($user->display_name); ?></span>
                                <input type="checkbox" 
                                       name="students_involved[]" 
                                       value="<?php echo esc_attr($user->ID); ?>" 
                                       data-name="<?php echo esc_attr($user->display_name); ?>"
                                       class="student-checkbox-input">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="selected-students-info">
                            <small>Selected: <span id="selected-count">0</span> student(s)</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="who_else_attended">Who else attended?</label>
                <input type="text" name="who_else_attended" id="who_else_attended">
            </div>
            
            <!-- Dynamic Results Section -->
            <div id="students-results-section" class="form-group" style="display: none;">
                <label class="results-section-title">
                    <span class="dashicons dashicons-chart-bar"></span>
                    Individual Student Results *
                </label>
                <div class="results-info">
                    <small>Enter the wins and losses for each selected student</small>
                </div>
                <div id="student-results-container">
                    <!-- Dynamic student result inputs will be added here -->
                </div>
            </div>
            <div class="form-group">
                <label for="highlights">Highlights</label>
                <textarea name="highlights" id="highlights"></textarea>
            </div>
            <button type="submit" name="submit_competition" class="btn-primary">Log Competition</button>
        </form>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentCheckboxes = document.querySelectorAll('.student-checkbox-input');
        const selectedCountElement = document.getElementById('selected-count');
        const resultsSection = document.getElementById('students-results-section');
        const resultsContainer = document.getElementById('student-results-container');
        const submitButton = document.querySelector('button[name="submit_competition"]');
        
        // Update form when checkboxes change
        studentCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateStudentResults();
                updateSelectedCount();
                updateSubmitButton();
            });
        });
        
        function updateSelectedCount() {
            const selectedCount = document.querySelectorAll('.student-checkbox-input:checked').length;
            selectedCountElement.textContent = selectedCount;
        }
        
        function updateStudentResults() {
            const selectedStudents = document.querySelectorAll('.student-checkbox-input:checked');
            resultsContainer.innerHTML = '';
            
            if (selectedStudents.length > 0) {
                resultsSection.style.display = 'block';
                
                selectedStudents.forEach(function(checkbox) {
                    const studentId = checkbox.value;
                    const studentName = checkbox.getAttribute('data-name');
                    
                    const studentResultDiv = document.createElement('div');
                    studentResultDiv.className = 'student-result-item';
                    studentResultDiv.innerHTML = `
                        <div class="student-result-header">
                            <span class="student-name-label">${studentName}</span>
                        </div>
                        <div class="student-result-inputs">
                            <div class="result-input-group">
                                <label for="wins_${studentId}">Wins</label>
                                <input type="number" 
                                       name="student_results[${studentId}][wins]" 
                                       id="wins_${studentId}" 
                                       min="0" 
                                       value="0" 
                                       required 
                                       class="result-input">
                            </div>
                            <div class="result-input-group">
                                <label for="losses_${studentId}">Losses</label>
                                <input type="number" 
                                       name="student_results[${studentId}][losses]" 
                                       id="losses_${studentId}" 
                                       min="0" 
                                       value="0" 
                                       required 
                                       class="result-input">
                            </div>
                        </div>
                    `;
                    resultsContainer.appendChild(studentResultDiv);
                });
            } else {
                resultsSection.style.display = 'none';
            }
        }
        
        function updateSubmitButton() {
            const selectedCount = document.querySelectorAll('.student-checkbox-input:checked').length;
            if (selectedCount === 0) {
                submitButton.disabled = true;
                submitButton.textContent = 'Select at least one student';
                submitButton.style.opacity = '0.6';
                submitButton.style.cursor = 'not-allowed';
            } else {
                submitButton.disabled = false;
                submitButton.textContent = 'Log Competition';
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
            }
        }
        
        // Initialize
        updateSelectedCount();
        updateSubmitButton();
    });
    </script>
    
    <style>
    .wcb-form-container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
    .form-header { text-align: center; margin-bottom: 30px; }
    .form-header h2 { margin: 0 0 10px 0; color: #2c3e50; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 2rem; }
    .form-header p { color: #666; font-size: 1.1rem; margin: 0; }
    .competition-form { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
    .competition-form .form-group { margin-bottom: 20px; }
    .competition-form label { display: block; font-weight: bold; margin-bottom: 6px; }
    .competition-form input, .competition-form select, .competition-form textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
    .competition-form textarea { min-height: 80px; }
    .competition-form .btn-primary { background: #e74c3c; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .competition-form .btn-primary:hover { background: #c0392b; }
    .form-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    .form-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    .form-info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #bee5eb; }
    
    /* Multi-student selection styles */
    .students-selection-container { background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; padding: 15px; }
    .students-checkboxes { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 10px; }
    .student-checkbox { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: white; border: 1px solid #e3e3e3; border-radius: 4px; cursor: pointer; transition: all 0.2s ease; }
    .student-checkbox:hover { background: #f0f8ff; border-color: #007cba; }
    .student-checkbox input[type="checkbox"] { margin: 0; flex-shrink: 0; width: max-content; }
    .student-name { font-size: 14px; font-weight: 400; max-width: calc(100% - 25px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .student-checkbox input[type="checkbox"]:checked ~ .student-name,
    .student-checkbox:has(input[type="checkbox"]:checked) .student-name { color: #007cba; font-weight: 600; }
    .selected-students-info { text-align: center; color: #666; }
    .students-selection-empty { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; text-align: center; }
    
    /* Results section styles */
    .results-section-title { display: flex; align-items: center; gap: 8px; font-weight: bold; margin-bottom: 8px; }
    .results-info { margin-bottom: 15px; }
    .results-info small { color: #666; font-style: italic; }
    #student-results-container { margin-bottom: 25px; }
    .student-result-item { background: white; border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 20px; }
    .student-result-header { margin-bottom: 12px; }
    .student-name-label { font-weight: bold; color: #2c3e50; font-size: 1rem; }
    .student-result-inputs { display: grid; grid-template-columns: 120px 120px; gap: 20px; justify-content: center; }
    .result-input-group { display: flex; flex-direction: column; align-items: center; }
    .result-input-group label { font-size: 13px; color: #555; margin-bottom: 6px; font-weight: 600; text-align: center; }
    .result-input { width: 80px; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; text-align: center; font-weight: 600; }
    .result-input:focus { border-color: #007cba; outline: none; box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1); }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .students-checkboxes { grid-template-columns: 1fr; }
        .student-result-inputs { grid-template-columns: 120px 120px; gap: 15px; justify-content: center; }
        .result-input { width: 70px; }
    }
    @media (max-width: 480px) {
        .student-result-inputs { grid-template-columns: 1fr; gap: 15px; justify-content: center; }
        .result-input-group { align-items: stretch; }
        .result-input { width: 100%; max-width: 120px; margin: 0 auto; }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_competition_form', 'wcb_competition_form_shortcode');

function wcb_handle_competition_submission() {
    // Duplicate prevention
    $user_identifier = get_current_user_id() ?: $_SERVER['REMOTE_ADDR'];
    $submission_key = 'wcb_competition_cooldown_' . md5($user_identifier);
    
    // Check if user submitted recently (within 5 seconds)
    if (get_transient($submission_key)) {
        return ['success' => false, 'message' => 'Please wait a moment before submitting another competition.'];
    }
    
    // Create content hash to prevent identical submissions
    $content_data = [
        'event_name' => $_POST['event_name'] ?? '',
        'event_date' => $_POST['event_date'] ?? '',
        'where_was_it_hosted' => $_POST['where_was_it_hosted'] ?? '',
        'students_involved' => $_POST['students_involved'] ?? [],
        'student_results' => $_POST['student_results'] ?? []
    ];
    $content_hash = md5(serialize($content_data));
    $content_key = 'wcb_competition_content_' . $content_hash;
    
    // Check if identical content was submitted recently (within 2 minutes)
    if (get_transient($content_key)) {
        return ['success' => false, 'message' => 'This competition appears to have been submitted already. Please check your competitions list.'];
    }
    
    // Set cooldowns
    set_transient($submission_key, true, 5);
    set_transient($content_key, true, 120);
    
    // Validate required fields more carefully
    $students_involved = $_POST['students_involved'] ?? [];
    $student_results = $_POST['student_results'] ?? [];
    
    if (empty($_POST['event_name']) || empty($_POST['event_date']) || empty($_POST['where_was_it_hosted']) || empty($students_involved)) {
        return ['success' => false, 'message' => 'Please fill in all required fields, including selecting at least one student'];
    }
    
    // Validate that results are provided for all selected students
    foreach ($students_involved as $student_id) {
        if (!isset($student_results[$student_id]) || 
            !isset($student_results[$student_id]['wins']) || 
            !isset($student_results[$student_id]['losses'])) {
            return ['success' => false, 'message' => 'Please provide wins and losses for all selected students'];
        }
    }

    $post_data = [
        'post_title' => sanitize_text_field($_POST['event_name']),
        'post_type' => 'competition',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    ];
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        return ['success' => false, 'message' => 'Failed to create competition'];
    }

    // Debug form submission data
    if (current_user_can('administrator')) {
        error_log('Competition Form Submission Debug:');
        error_log('POST Data: ' . print_r($_POST, true));
        error_log('Students Involved: ' . print_r($_POST['students_involved'], true));
        error_log('Student Results: ' . print_r($_POST['student_results'], true));
    }
    
    // Save basic ACF fields
    update_field('event_name', sanitize_text_field($_POST['event_name']), $post_id);
    update_field('event_date', sanitize_text_field($_POST['event_date']), $post_id);
    update_field('where_was_it_hosted', sanitize_text_field($_POST['where_was_it_hosted']), $post_id);
    update_field('who_else_attended', sanitize_text_field($_POST['who_else_attended']), $post_id);
    update_field('highlights', sanitize_textarea_field($_POST['highlights']), $post_id);
    
    // Save multiple students and their results
    $students_involved = array_map('intval', $_POST['students_involved']);
    $student_results = $_POST['student_results'];
    
    // Save students involved as array of user IDs
    update_field('students_involved', $students_involved, $post_id);
    
    // Calculate and save total wins/losses across all students
    $total_wins = 0;
    $total_losses = 0;
    $detailed_results = [];
    
    foreach ($students_involved as $student_id) {
        $wins = intval($student_results[$student_id]['wins']);
        $losses = intval($student_results[$student_id]['losses']);
        
        $total_wins += $wins;
        $total_losses += $losses;
        
        // Store detailed results for each student
        $detailed_results[] = [
            'student_id' => $student_id,
            'wins' => $wins,
            'losses' => $losses
        ];
    }
    
    // Save totals (for backward compatibility and summary)
    update_field('results_wins', $total_wins, $post_id);
    update_field('results_lost', $total_losses, $post_id);
    
    // Save detailed student results
    update_field('student_detailed_results', $detailed_results, $post_id);
    
    // Debug what was actually saved
    if (current_user_can('administrator')) {
        error_log('Saved Students Involved: ' . print_r(get_field('students_involved', $post_id), true));
        error_log('Saved Student Detailed Results: ' . print_r(get_field('student_detailed_results', $post_id), true));
        error_log('Saved Total Wins: ' . get_field('results_wins', $post_id));
        error_log('Saved Total Losses: ' . get_field('results_lost', $post_id));
    }

    return ['success' => true, 'post_id' => $post_id];
}

/**
 * Get users who have Competitive Team membership
 */
function wcb_get_competitive_team_members() {
    global $wpdb;

    $competitive_team_id = 1932;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get users with active Competitive Team transactions
    $competitive_users = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ", $competitive_team_id));

    return $competitive_users;
}