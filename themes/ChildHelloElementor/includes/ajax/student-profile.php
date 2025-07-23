<?php
// Student Profile Container Component

function student_profile_container_shortcode($atts) {
    $atts = shortcode_atts([
        'student_id' => '',
        'class' => 'wcb-student-profile-container',
        'show_sessions' => 'true',
        'show_memberships' => 'true',
        'sessions_limit' => '10'
    ], $atts);
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="wcb-student-profile-container">
        <div class="profile-placeholder">
            <div class="placeholder-content">
                <h3>👤 Student Profile</h3>
                <p>Use the student search above to select a student and view their profile here.</p>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Listen for student profile requests
        $(document).on('wcb:show-student-profile', function(e, studentId) {
            loadStudentProfile(studentId);
        });
        
        function loadStudentProfile(studentId) {
            if (!studentId) {
                return;
            }
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_student_profile',
                    student_id: studentId,
                    show_sessions: '<?php echo esc_js($atts['show_sessions']); ?>',
                    show_memberships: '<?php echo esc_js($atts['show_memberships']); ?>',
                    sessions_limit: '<?php echo esc_js($atts['sessions_limit']); ?>',
                    nonce: wcb_ajax.nonce
                },
                beforeSend: function() {
                    $('#wcb-student-profile-container').html('<div class="loading-profile">Loading student profile...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        $('#wcb-student-profile-container').html(response.data.html);
                    } else {
                        $('#wcb-student-profile-container').html('<div class="error">Error loading profile: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $('#wcb-student-profile-container').html('<div class="error">Failed to load student profile. Please try again.</div>');
                }
            });
        }
    });
    </script>
    
    <style>
    .wcb-student-profile-container {
        margin: 20px 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: white;
        min-height: 200px;
    }
    .profile-placeholder {
        padding: 40px;
        text-align: center;
        color: #666;
    }
    .placeholder-content h3 {
        margin: 0 0 15px 0;
        font-size: 18px;
    }
    .loading-profile {
        padding: 40px;
        text-align: center;
        color: #666;
    }
    .student-profile {
        padding: 0;
    }
    .profile-header {
        background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
        color: white;
        padding: 25px;
        border-radius: 8px 8px 0 0;
    }
    .profile-header h2 {
        margin: 0 0 10px 0;
        font-size: 24px;
    }
    .profile-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
        opacity: 0.9;
    }
    .profile-content {
        padding: 25px;
    }
    .profile-section {
        margin-bottom: 30px;
    }
    .profile-section:last-child {
        margin-bottom: 0;
    }
    .section-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #007cba;
        padding-bottom: 5px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    .info-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border-left: 4px solid #007cba;
    }
    .info-label {
        font-weight: bold;
        color: #333;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .info-value {
        color: #666;
        font-size: 16px;
    }
    .membership-item {
        background: #e7f3ff;
        border: 1px solid #b3d9ff;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 10px;
    }
    .membership-name {
        font-weight: bold;
        color: #007cba;
        margin-bottom: 5px;
    }
    .membership-status {
        font-size: 14px;
        color: #666;
    }
    .sessions-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .sessions-table th,
    .sessions-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .sessions-table th {
        background: #f8f9fa;
        font-weight: bold;
        color: #333;
    }
    .sessions-table tr:hover {
        background: #f8f9fa;
    }
    .session-type-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    .session-type-badge.class-session {
        background: #e7f5e7;
        color: #2d5a2d;
    }
    .session-type-badge.mentoring-intervention {
        background: #E0DAFD;
        color: #856404;
    }
    .session-type-badge.referral {
        background: #f8d7da;
        color: #721c24;
    }
    .no-sessions {
        text-align: center;
        color: #666;
        padding: 20px;
        font-style: italic;
    }
    .error {
        color: #d63638;
        padding: 20px;
        text-align: center;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('student_profile_container', 'student_profile_container_shortcode');

// AJAX Handler for loading student profile
function wcb_ajax_load_student_profile() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $student_id = intval($_POST['student_id']);
    $show_sessions = $_POST['show_sessions'] === 'true';
    $show_memberships = $_POST['show_memberships'] === 'true';
    $sessions_limit = intval($_POST['sessions_limit']) ?: 10;
    
    if (!$student_id) {
        wp_send_json_error('Invalid student ID');
        return;
    }
    
    $user = get_user_by('ID', $student_id);
    if (!$user) {
        wp_send_json_error('Student not found');
        return;
    }
    
    ob_start();
    ?>
    <div class="student-profile">
        <div class="profile-header">
            <h2><?php echo esc_html($user->display_name); ?></h2>
            <div class="profile-meta">
                <span>📧 <?php echo esc_html($user->user_email); ?></span>
                <span>📅 Member since <?php echo date('M j, Y', strtotime($user->user_registered)); ?></span>
                <span>🆔 ID: <?php echo $student_id; ?></span>
            </div>
        </div>
        
        <div class="profile-content">
            <!-- Basic Information -->
            <div class="profile-section">
                <h3 class="section-title">📋 Basic Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo esc_html($user->user_login); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Display Name</div>
                        <div class="info-value"><?php echo esc_html($user->display_name); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo esc_html($user->user_email); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value"><?php echo esc_html(implode(', ', $user->roles)); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if ($show_memberships): ?>
            <!-- Memberships -->
            <div class="profile-section">
                <h3 class="section-title">🎫 Memberships</h3>
                <?php
                // Get subscription display using safe helper
                $membership_display = WCB_MemberPress_Helper::get_membership_display($student_id);
                
                if ($membership_display !== 'No Active Membership'): ?>
                    <div class="membership-item">
                        <div class="membership-name"><?php echo esc_html($membership_display); ?></div>
                        <div class="membership-status">Active subscription</div>
                    </div>
                <?php else: ?>
                    <div class="info-item">
                        <div class="info-value">No active memberships found</div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($show_sessions): ?>
            <!-- Session History -->
            <div class="profile-section">
                <h3 class="section-title">📚 Recent Sessions</h3>
                <?php
                // Get sessions where this student attended or was associated
                // Use a more comprehensive approach to find sessions
                $all_sessions = get_posts([
                    'post_type' => 'session_log',
                    'numberposts' => -1, // Get all sessions first
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                
                $sessions = [];
                foreach ($all_sessions as $session) {
                    $session_id = $session->ID;
                    
                    // Get attendance data using helper function
                    $attendance_data = wcb_get_session_attendance($session_id);
                    $attended_students = $attendance_data['attended'];
                    $excused_students = $attendance_data['excused'];
                    
                    // Handle associated student
                    $associated_student_raw = get_field('associated_student', $session_id);
                    $associated_student = null;
                    if (is_object($associated_student_raw) && isset($associated_student_raw->ID)) {
                        $associated_student = $associated_student_raw->ID;
                    } elseif (is_array($associated_student_raw) && isset($associated_student_raw['ID'])) {
                        $associated_student = $associated_student_raw['ID'];
                    } elseif (is_numeric($associated_student_raw)) {
                        $associated_student = intval($associated_student_raw);
                    }
                    
                    // Check if this student is associated with the session
                    $is_associated = ($associated_student && $associated_student == $student_id);
                    
                    // Check if this student is in the attended or excused lists
                    $is_attended = in_array($student_id, $attended_students);
                    $is_excused = in_array($student_id, $excused_students);
                    
                    // Include session if student is associated, attended, or marked excused
                    if ($is_associated || $is_attended || $is_excused) {
                        $sessions[] = $session;
                        
                        // Limit to the requested number
                        if (count($sessions) >= $sessions_limit) {
                            break;
                        }
                    }
                }
                
                if (!empty($sessions)): ?>
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Class/Program</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <?php 
                        $date = get_field('session_date', $session->ID);
                        $membership_id = get_field('selected_membership', $session->ID);
                        
                        // Get class name
                        $class_name = 'Unknown Class';
                        if ($membership_id) {
                            $membership_post = get_post($membership_id);
                            if ($membership_post) {
                                $class_name = $membership_post->post_title;
                            }
                        }
                        
                        // Get session type using helper function
                        $session_type_data = wcb_get_session_type($session->ID);
                        $session_type = $session_type_data['name'];
                        $session_type_slug = $session_type_data['slug'];
                        
                        // Check attendance status - use data from above loop iteration
                        $attendance_status = 'Unknown';
                        
                        if ($is_associated) {
                            $attendance_status = 'Present (1-on-1)';
                        } elseif ($is_attended) {
                            $attendance_status = 'Present';
                        } elseif ($is_excused) {
                            $attendance_status = 'Excused';
                        }
                        
                        $formatted_date = $date ? date('M j, Y', strtotime($date)) : 'Unknown Date';
                        ?>
                        <tr>
                            <td><?php echo esc_html($formatted_date); ?></td>
                            <td>
                                <span class="session-type-badge <?php echo esc_attr($session_type_slug); ?>">
                                    <?php echo esc_html($session_type); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($class_name); ?></td>
                            <td><?php echo esc_html($attendance_status); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-sessions">No sessions found for this student</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'student_name' => $user->display_name
    ]);
}
add_action('wp_ajax_wcb_load_student_profile', 'wcb_ajax_load_student_profile');
add_action('wp_ajax_nopriv_wcb_load_student_profile', 'wcb_ajax_load_student_profile');
