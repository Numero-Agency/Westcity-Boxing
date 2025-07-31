<?php
// Single Session Display Component

function single_session_shortcode($atts) {
    $atts = shortcode_atts([
        'session_id' => '',
        'class' => 'wcb-single-session',
        'show_students' => 'true',
        'show_notes' => 'true',
        'show_edit_link' => 'true'
    ], $atts);
    
    // Get session ID from URL if not provided
    $session_id = $atts['session_id'];
    if (empty($session_id)) {
        $session_id = get_the_ID();
    }
    
    if (empty($session_id)) {
        return '<div class="error">No session ID provided</div>';
    }
    
    $session = get_post($session_id);
    if (!$session || $session->post_type !== 'session_log') {
        return '<div class="error">Session not found or invalid session type</div>';
    }
    
    // Get session type using helper function
    $session_type_data = wcb_get_session_type($session_id);
    $session_type = $session_type_data['name'];
    $session_type_slug = $session_type_data['slug'];
    
    // Check if this is an intervention session
    $is_intervention = (strtolower($session_type) === 'mentoring' || $session_type_slug === 'mentoring');
    
    if ($is_intervention) {
        // Handle intervention session data
        $date = get_field('intervention_date_', $session_id);
        $duration = get_field('duration', $session_id);
        $meeting_location = get_field('meeting_location', $session_id);
        $student_involved = get_field('student_involved', $session_id);
        $staff_members_who_attended = get_field('staff_members_who_attended', $session_id);
        $other_attendees = get_field('other_attendees', $session_id);
        $debrief_event = get_field('debrief_event', $session_id);
        
        // For mentoring sessions, class name is "WCB Mentoring"
        $class_name = 'WCB Mentoring';
        
        // Get student info
        $student = $student_involved ? get_user_by('ID', $student_involved) : null;
        
        // Get staff members info - handle both old format (user IDs) and new format (text names)
        $staff_members = [];
        if (is_array($staff_members_who_attended)) {
            foreach ($staff_members_who_attended as $staff_item) {
                if (is_numeric($staff_item)) {
                    // Old format: user ID
                    $staff_user = get_user_by('ID', $staff_item);
                    if ($staff_user) {
                        $staff_members[] = (object) [
                            'display_name' => $staff_user->display_name,
                            'user_email' => $staff_user->user_email,
                            'is_user' => true
                        ];
                    }
                } else {
                    // New format: text name
                    $staff_members[] = (object) [
                        'display_name' => $staff_item,
                        'user_email' => 'Coach',
                        'is_user' => false
                    ];
                }
            }
        }
        
        // Format date
        $formatted_date = $date ? date('d/m/Y', strtotime($date)) : 'Unknown Date';

        // Format time - try dedicated time fields first
        $session_time = get_field('session_time', $session_id);
        $start_time = get_field('start_time', $session_id);

        $formatted_time = '';
        if ($session_time) {
            $formatted_time = date('g:i A', strtotime($session_time));
        } elseif ($start_time) {
            $formatted_time = date('g:i A', strtotime($start_time));
        } elseif ($date && strpos($date, ':') !== false) {
            // Only extract time if date field actually contains time
            $formatted_time = date('g:i A', strtotime($date));
        }

        // Don't show midnight time
        if ($formatted_time === '12:00 AM') {
            $formatted_time = '';
        }
        
        // Format duration (duration is stored in minutes)
        $formatted_duration = $duration ? $duration . ' minutes' : '';
        
    } else {
        // Handle regular session data
        $date = get_field('session_date', $session_id);
        $membership_id = get_field('selected_membership', $session_id);
        $associated_student = get_field('associated_student', $session_id);
        $session_notes = get_field('session_notes', $session_id);
        
        // Get attendance data using helper function
        $attendance_data = wcb_get_session_attendance($session_id);
        $attended_students = $attendance_data['attended'];
        $excused_students = $attendance_data['excused'];
        
        // Handle associated student (might be object or ID)
        if (is_object($associated_student) && isset($associated_student->ID)) {
            $associated_student = $associated_student->ID;
        } elseif (is_array($associated_student) && isset($associated_student['ID'])) {
            $associated_student = $associated_student['ID'];
        } elseif (is_numeric($associated_student)) {
            $associated_student = intval($associated_student);
        } else {
            $associated_student = null;
        }
        
        // Get class name and total member count
        $class_name = 'Unknown Class';
        $total_group_members = 0;

        // Check for new format (selected_group) first
        $selected_group = get_field('selected_group', $session_id);
        if ($selected_group) {
            $group_post = get_post($selected_group);
            if ($group_post) {
                $class_name = $group_post->post_title;
                // Get total members in this group
                $total_group_members = wcb_get_group_member_count($selected_group);
            }
        } elseif ($membership_id) {
            // Fallback to old format (selected_membership)
            $membership_post = get_post($membership_id);
            if ($membership_post) {
                $class_name = $membership_post->post_title;
                // For old format, we don't have easy access to member count
                $total_group_members = count($attended_students); // Fallback to attended count
            }
        }
        
        // Format date
        $formatted_date = $date ? date('d/m/Y', strtotime($date)) : 'Unknown Date';

        // Format time - try dedicated time fields first
        $session_time = get_field('session_time', $session_id);
        $start_time = get_field('start_time', $session_id);

        $formatted_time = '';
        if ($session_time) {
            $formatted_time = date('g:i A', strtotime($session_time));
        } elseif ($start_time) {
            $formatted_time = date('g:i A', strtotime($start_time));
        } elseif ($date && strpos($date, ':') !== false) {
            // Only extract time if date field actually contains time
            $formatted_time = date('g:i A', strtotime($date));
        }

        // Don't show midnight time
        if ($formatted_time === '12:00 AM') {
            $formatted_time = '';
        }
    }
    
    // Debug: Log what we're getting from ACF
    if (current_user_can('administrator')) {
        error_log('Session ID: ' . $session_id);
        error_log('Session Type: ' . $session_type . ' (slug: ' . $session_type_slug . ')');
        error_log('Is Intervention: ' . ($is_intervention ? 'Yes' : 'No'));
        
        if ($is_intervention) {
            error_log('Student Involved: ' . print_r($student_involved, true));
            error_log('Staff Members: ' . print_r($staff_members_who_attended, true));
        } else {
            error_log('Attendance Data: ' . print_r($attendance_data, true));
            error_log('Associated Student: ' . print_r($associated_student, true));
        }
    }

    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="single-session-<?php echo $session_id; ?>">
        <!-- Session Header -->
        <div class="session-header">
            <div class="session-title-section">
                <h1 class="session-title"><?php echo esc_html($class_name); ?></h1>
                <div class="session-meta">
                    <span class="session-date"><span class="dashicons dashicons-calendar-alt"></span> <?php echo get_the_date('d/m/Y', $session); ?></span>
                    <?php if ($formatted_time): ?>
                    <span class="session-time"><span class="dashicons dashicons-clock"></span> <?php echo esc_html($formatted_time); ?></span>
                    <?php endif; ?>
                    <?php if ($is_intervention && $formatted_duration): ?>
                    <span class="session-duration"><span class="dashicons dashicons-backup"></span> <?php echo esc_html($formatted_duration); ?></span>
                    <?php endif; ?>
                    <span class="session-type-badge <?php echo esc_attr($session_type_slug); ?>">
                        <?php echo esc_html($is_intervention ? 'Mentoring' : $session_type); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($atts['show_edit_link'] === 'true' && current_user_can('edit_posts')): ?>
            <div class="session-actions">
                <a href="https://westcityboxing.local/staff-dashboard/" 
                   class="btn-dashboard-modern">Dashboard</a>
                <a href="<?php echo admin_url('post.php?post=' . $session_id . '&action=edit'); ?>" 
                   class="btn-edit-modern">Edit Session</a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Session Details -->
        <div class="session-content">
            <?php if (current_user_can('administrator') && isset($_GET['debug'])): ?>
            <!-- Debug Information -->
            <div class="session-section">
                <h3 class="section-title">🐛 Debug Information</h3>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 12px;">
                    <p><strong>Session Type:</strong> <?php echo esc_html($session_type . ' (' . $session_type_slug . ')'); ?></p>
                    <p><strong>Is Intervention:</strong> <?php echo $is_intervention ? 'Yes' : 'No'; ?></p>
                    
                    <?php if ($is_intervention): ?>
                        <p><strong>Student Involved:</strong> <?php echo esc_html(print_r($student_involved, true)); ?></p>
                        <p><strong>Staff Members:</strong> <?php echo esc_html(print_r($staff_members_who_attended, true)); ?></p>
                        <p><strong>All Intervention Fields:</strong></p>
                        <pre><?php echo esc_html(print_r([
                            'intervention_date_' => $date,
                            'duration' => $duration,
                            'meeting_location' => $meeting_location,
                            'student_involved' => $student_involved,
                            'staff_members_who_attended' => $staff_members_who_attended,
                            'other_attendees' => $other_attendees,
                            'debrief_event' => $debrief_event
                        ], true)); ?></pre>
                    <?php else: ?>
                        <p><strong>Processed Attended Students:</strong> <?php echo esc_html(print_r($attended_students, true)); ?></p>
                        <p><strong>Processed Excused Students:</strong> <?php echo esc_html(print_r($excused_students, true)); ?></p>
                        <p><strong>Associated Student:</strong> <?php echo esc_html(print_r($associated_student, true)); ?></p>
                        <p><strong>Helper Function Raw Data:</strong></p>
                        <pre><?php echo esc_html(print_r($attendance_data['raw_data'], true)); ?></pre>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($is_intervention): ?>
                <!-- Intervention Session Layout - Two Columns -->
                <div class="intervention-layout">
                    <!-- Left Column: Session Attendees -->
                    <div class="intervention-attendees-column">
                        <h3 class="section-title"><span class="dashicons dashicons-groups"></span> Session Attendees</h3>
                        
                        <!-- Summary Stats -->
                        <div class="attendee-summary">
                            <div class="summary-item">
                                <div class="summary-icon"><span class="dashicons dashicons-admin-users"></span></div>
                                <div class="summary-content">
                                    <div class="summary-number">1</div>
                                    <div class="summary-label">Student</div>
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-icon"><span class="dashicons dashicons-businessman"></span></div>
                                <div class="summary-content">
                                    <div class="summary-number"><?php echo count($staff_members); ?></div>
                                    <div class="summary-label">Staff</div>
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-icon"><span class="dashicons dashicons-chart-bar"></span></div>
                                <div class="summary-content">
                                    <div class="summary-number"><?php echo count($staff_members) + ($student ? 1 : 0); ?></div>
                                    <div class="summary-label">Total</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Student Card -->
                        <?php if ($student): ?>
                        <div class="attendee-section">
                            <h4 class="column-header">Student</h4>
                            <div class="attendee-card student-card">
                                <div class="attendee-avatar">
                                    <div class="avatar-placeholder">
                                        <span class="dashicons dashicons-admin-users"></span>
                                    </div>
                                </div>
                                <div class="attendee-info">
                                    <div class="attendee-name"><?php echo esc_html($student->display_name); ?></div>
                                    <div class="attendee-email"><?php echo esc_html($student->user_email); ?></div>
                                    <div class="attendee-role">Student</div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Staff Cards -->
                        <?php if (!empty($staff_members)): ?>
                        <div class="attendee-section">
                            <h4 class="column-header">Staff Members</h4>
                            <?php foreach ($staff_members as $staff_member): ?>
                            <div class="attendee-card staff-card">
                                <div class="attendee-avatar">
                                    <div class="avatar-placeholder staff-avatar">
                                        <span class="dashicons dashicons-businessman"></span>
                                    </div>
                                </div>
                                <div class="attendee-info">
                                    <div class="attendee-name"><?php echo esc_html($staff_member->display_name); ?></div>
                                    <div class="attendee-email"><?php echo esc_html($staff_member->user_email); ?></div>
                                    <div class="attendee-role">Staff Member</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Other Attendees -->
                        <?php if (!empty($other_attendees)): ?>
                        <div class="attendee-section">
                            <h4 class="column-header">Other Attendees</h4>
                            <div class="other-attendees-card">
                                <?php echo wp_kses_post(nl2br($other_attendees)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Right Column: Session Information -->
                    <div class="intervention-info-column">
                        <h3 class="section-title"><span class="dashicons dashicons-info"></span> Session Information</h3>
                        
                        <!-- Session Details Grid -->
                        <div class="session-details-grid">
                            <div class="detail-item">
                                <div class="detail-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
                                <div class="detail-content">
                                    <div class="detail-label">Date</div>
                                    <div class="detail-value"><?php echo esc_html($formatted_date); ?></div>
                                </div>
                            </div>
                            
                            <?php if ($formatted_time): ?>
                            <div class="detail-item">
                                <div class="detail-icon"><span class="dashicons dashicons-clock"></span></div>
                                <div class="detail-content">
                                    <div class="detail-label">Time</div>
                                    <div class="detail-value"><?php echo esc_html($formatted_time); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($formatted_duration): ?>
                            <div class="detail-item">
                                <div class="detail-icon"><span class="dashicons dashicons-backup"></span></div>
                                <div class="detail-content">
                                    <div class="detail-label">Duration</div>
                                    <div class="detail-value"><?php echo esc_html($formatted_duration); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($meeting_location): ?>
                            <div class="detail-item">
                                <div class="detail-icon"><span class="dashicons dashicons-location"></span></div>
                                <div class="detail-content">
                                    <div class="detail-label">Meeting Location</div>
                                    <div class="detail-value"><?php echo esc_html($meeting_location); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <div class="detail-icon"><span class="dashicons dashicons-category"></span></div>
                                <div class="detail-content">
                                    <div class="detail-label">Session Type</div>
                                    <div class="detail-value">
                                        <span class="session-type-badge intervention">Mentoring</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon"><span class="dashicons dashicons-tag"></span></div>
                                <div class="detail-content">
                                    <div class="detail-label">Session ID</div>
                                    <div class="detail-value">#<?php echo $session_id; ?></div>
                                </div>
                            </div>
                            

                        </div>
                    </div>
                </div>
                
                <!-- Full Width Intervention Debrief Section -->
                <?php if ($atts['show_notes'] === 'true' && !empty($debrief_event)): ?>
                <div class="intervention-debrief-section">
                    <h3 class="section-title"><span class="dashicons dashicons-clipboard"></span> Mentoring Debrief</h3>
                    <div class="debrief-content">
                        <?php echo wp_kses_post($debrief_event); ?>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Regular Session Layout - Two Columns -->
                <div class="regular-session-layout">
                    <!-- Left Column: Session Attendees -->
                    <div class="session-attendees-column">
                        <h3 class="section-title"><span class="dashicons dashicons-groups"></span> Session Attendees</h3>
                        
                        <!-- Attendance Summary Stats -->
                        <div class="attendee-summary">
                            <?php if ($associated_student): ?>
                                <div class="summary-item">
                                    <div class="summary-icon"><span class="dashicons dashicons-admin-users"></span></div>
                                    <div class="summary-content">
                                        <div class="summary-number">1</div>
                                        <div class="summary-label">1-on-1 Session</div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="summary-item">
                                    <div class="summary-icon"><span class="dashicons dashicons-admin-users"></span></div>
                                    <div class="summary-content">
                                        <div class="summary-number"><?php echo count($attended_students); ?></div>
                                        <div class="summary-label">Present</div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-icon"><span class="dashicons dashicons-chart-bar"></span></div>
                                    <div class="summary-content">
                                        <div class="summary-number"><?php echo $total_group_members; ?></div>
                                        <div class="summary-label">Total</div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-icon"><span class="dashicons dashicons-chart-pie"></span></div>
                                    <div class="summary-content">
                                        <div class="summary-number">
                                            <?php
                                            $attendance_percentage = $total_group_members > 0 ? round((count($attended_students) / $total_group_members) * 100) : 0;
                                            echo $attendance_percentage . '%';
                                            ?>
                                        </div>
                                        <div class="summary-label">Attendance</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Present Students -->
                        <?php if (!$associated_student && !empty($attended_students)): ?>
                        <div class="attendee-section">
                            <h4 class="column-header">Present Students (<?php echo count($attended_students); ?>)</h4>
                            <div class="scrollable-attendees">
                                <?php foreach ($attended_students as $student_id): ?>
                                    <?php $student_user = get_user_by('ID', $student_id); ?>
                                    <?php if ($student_user): ?>
                                    <div class="attendee-card student-card">
                                        <div class="attendee-avatar">
                                            <div class="avatar-placeholder">
                                                <span class="dashicons dashicons-admin-users"></span>
                                            </div>
                                        </div>
                                                                            <div class="attendee-info">
                                        <div class="attendee-name"><?php echo esc_html($student_user->display_name); ?></div>
                                    </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Instructor Section -->
                        <div class="attendee-section">
                            <h4 class="column-header">Instructor</h4>
                            <?php
                            // Get instructor from ACF field - now stored as text name
                            $instructor_field = get_field('instructor', $session_id);

                            // Debug instructor field if enabled
                            if (current_user_can('administrator') && isset($_GET['debug'])) {
                                error_log('Instructor Debug for Session ID: ' . $session_id);
                                error_log('ACF Instructor Field: ' . print_r($instructor_field, true));
                                error_log('Post Author: ' . $session->post_author);
                            }

                            // Handle both old format (user ID) and new format (text name)
                            $instructor_name = '';
                            $instructor_user = null;

                            if (!empty($instructor_field)) {
                                // Check if it's a numeric user ID (old format)
                                if (is_numeric($instructor_field)) {
                                    $instructor_user = get_user_by('ID', $instructor_field);
                                    if ($instructor_user) {
                                        $instructor_name = $instructor_user->display_name;
                                    }
                                }
                                // Check if it's an array with user data (old format)
                                elseif (is_array($instructor_field) && isset($instructor_field[0])) {
                                    if (is_object($instructor_field[0]) && isset($instructor_field[0]->ID)) {
                                        $instructor_user = $instructor_field[0];
                                        $instructor_name = $instructor_user->display_name;
                                    } elseif (is_numeric($instructor_field[0])) {
                                        $instructor_user = get_user_by('ID', $instructor_field[0]);
                                        if ($instructor_user) {
                                            $instructor_name = $instructor_user->display_name;
                                        }
                                    }
                                }
                                // Check if it's an object with user data (old format)
                                elseif (is_object($instructor_field) && isset($instructor_field->ID)) {
                                    $instructor_user = $instructor_field;
                                    $instructor_name = $instructor_user->display_name;
                                }
                                // It's a text name (new format) - this should be the main case now
                                elseif (is_string($instructor_field) && !empty(trim($instructor_field))) {
                                    $instructor_name = trim($instructor_field);
                                    $instructor_user = null; // No user object for text names
                                }
                            }

                            // Only fallback to post author if absolutely no instructor found
                            if (empty($instructor_name)) {
                                $instructor_user = get_user_by('ID', $session->post_author);
                                if ($instructor_user) {
                                    $instructor_name = $instructor_user->display_name;
                                }
                            }

                            if (current_user_can('administrator') && isset($_GET['debug'])) {
                                error_log('Final Instructor Name: ' . $instructor_name);
                                error_log('Instructor User Object: ' . ($instructor_user ? 'Found' : 'Not found'));
                                error_log('Raw Instructor Field: ' . print_r($instructor_field, true));
                                error_log('Raw Instructor Field Type: ' . gettype($instructor_field));
                                error_log('Is Empty Check: ' . (empty($instructor_field) ? 'Yes' : 'No'));
                            }
                            ?>
                            <?php if (!empty($instructor_name)): ?>
                            <div class="attendee-card instructor-card">
                                <div class="attendee-avatar">
                                    <div class="avatar-placeholder staff-avatar">
                                        <span class="dashicons dashicons-businessman"></span>
                                    </div>
                                </div>
                                <div class="attendee-info">
                                    <div class="attendee-name"><?php echo esc_html($instructor_name); ?></div>
                                    <?php if ($instructor_user && $instructor_user->user_email): ?>
                                    <div class="attendee-email"><?php echo esc_html($instructor_user->user_email); ?></div>
                                    <?php else: ?>
                                    <div class="attendee-email">Coach</div>
                                    <?php endif; ?>
                                    <div class="attendee-role">Instructor</div>
                                </div>
                            </div>
                            <?php else: ?>
                            <p class="no-instructor">No instructor assigned</p>
                            <?php endif; ?>
                        </div>

                        <!-- 1-on-1 Student -->
                        <?php if ($associated_student): ?>
                        <div class="attendee-section">
                            <h4 class="column-header">Student</h4>
                            <?php $student_user = get_user_by('ID', $associated_student); ?>
                            <?php if ($student_user): ?>
                            <div class="attendee-card student-card">
                                <div class="attendee-avatar">
                                    <div class="avatar-placeholder">
                                        <span class="dashicons dashicons-admin-users"></span>
                                    </div>
                                </div>
                                <div class="attendee-info">
                                    <div class="attendee-name"><?php echo esc_html($student_user->display_name); ?></div>
                                    <div class="attendee-email"><?php echo esc_html($student_user->user_email); ?></div>
                                    <div class="attendee-role">1-on-1 Student</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        

                        
                        <?php if (!$associated_student && empty($attended_students)): ?>
                        <div class="no-students">
                            <p>No student attendance recorded for this session.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Right Column: Session Information -->
                    <div class="session-info-column">
                        
                        <!-- Session Information Section -->
                        <div class="session-details-box">
                            <h3 class="section-title"><span class="dashicons dashicons-info"></span> Session Information</h3>
                            
                            <!-- Session Details Grid -->
                            <div class="session-details-grid">
                                <div class="detail-item">
                                    <div class="detail-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
                                    <div class="detail-content">
                                        <div class="detail-label">Date</div>
                                        <div class="detail-value"><?php echo get_the_date('M j, Y', $session); ?></div>
                                    </div>
                                </div>
                                
                                <?php if ($formatted_time): ?>
                                <div class="detail-item">
                                    <div class="detail-icon"><span class="dashicons dashicons-clock"></span></div>
                                    <div class="detail-content">
                                        <div class="detail-label">Time</div>
                                        <div class="detail-value"><?php echo esc_html($formatted_time); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <div class="detail-icon"><span class="dashicons dashicons-category"></span></div>
                                    <div class="detail-content">
                                        <div class="detail-label">Session Type</div>
                                        <div class="detail-value">
                                            <span class="session-type-badge <?php echo esc_attr($session_type_slug); ?>">
                                                <?php echo esc_html($session_type); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-icon"><span class="dashicons dashicons-groups"></span></div>
                                    <div class="detail-content">
                                        <div class="detail-label">Class/Program</div>
                                        <div class="detail-value"><?php echo esc_html($class_name); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-icon"><span class="dashicons dashicons-tag"></span></div>
                                    <div class="detail-content">
                                        <div class="detail-label">Session ID</div>
                                        <div class="detail-value">#<?php echo $session_id; ?></div>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Full Width Session Notes Section -->
                <?php if ($atts['show_notes'] === 'true'): ?>
                <div class="session-notes-section-full">
                    <h3 class="section-title"><span class="dashicons dashicons-clipboard"></span> Session Notes</h3>
                    <div class="notes-content-full">
                        <?php
                        // Get all the session note fields based on exact ACF field names
                        $conversations = get_field('what_conversations_have_you_had_with_a_young_person', $session_id);
                        $interventions_concerns = get_field('interventionsconcerns_you_have_for_a_young_person', $session_id);
                        $student_intervention = get_field('which_student_did_you_have_an_intervention_or_concern_for', $session_id);
                        $note_to_manager = get_field('note_to_manager', $session_id);
                        $health_safety = get_field('health_and_safety', $session_id);
                        $general_notes = get_field('session_notes', $session_id);
                        
                        // Debug: Check all possible field variations
                        if (current_user_can('administrator') && isset($_GET['debug'])) {
                            error_log('Session Notes Debug for Session ID: ' . $session_id);
                            
                            // Try multiple field name variations including the exact ACF field names
                            $field_variations = [
                                'what_conversations_have_you_had_with_a_young_person',
                                'interventionsconcerns_you_have_for_a_young_person',
                                'which_student_did_you_have_an_intervention_or_concern_for',
                                'note_to_manager',
                                'health_and_safety',
                                'conversations_with_students',
                                'conversations_with_young_person', 
                                'conversations_young_person',
                                'student_conversations',
                                'interventions_concerns',
                                'interventions_concerns_young_person',
                                'interventions_and_concerns',
                                'student_for_intervention',
                                'student_intervention_concern', 
                                'intervention_student',
                                'manager_note',
                                'manager_notes',
                                'health_safety',
                                'health_safety_observations',
                                'session_notes'
                            ];
                            
                            foreach ($field_variations as $field_name) {
                                $value = get_field($field_name, $session_id);
                                if (!empty($value)) {
                                    error_log("Field '{$field_name}': " . print_r($value, true));
                                }
                            }
                        }
                        ?>
                        
                        <?php if (!empty($conversations)): ?>
                        <div class="note-section">
                            <h4 class="note-title">
                                <span class="dashicons dashicons-admin-comments"></span>
                                What Conversations have you had with a young person
                            </h4>
                            <div class="note-content">
                                <?php echo wp_kses_post(nl2br($conversations)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($interventions_concerns)): ?>
                        <div class="note-section">
                            <h4 class="note-title">
                                <span class="dashicons dashicons-warning"></span>
                                Interventions/concerns you have for a young person
                            </h4>
                            <div class="note-content">
                                <?php echo wp_kses_post(nl2br($interventions_concerns)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($student_intervention)): ?>
                        <div class="note-section">
                            <h4 class="note-title">
                                <span class="dashicons dashicons-admin-users"></span>
                                Student for Intervention/Concern
                            </h4>
                            <div class="note-content">
                                <?php 
                                // Debug the student intervention field
                                if (current_user_can('administrator') && isset($_GET['debug'])) {
                                    error_log('Student Intervention Field Debug: ' . print_r($student_intervention, true));
                                }
                                
                                // Handle different possible data formats
                                if (is_array($student_intervention)) {
                                    // If it's an array, try to get the first element or look for ID
                                    if (isset($student_intervention[0])) {
                                        $intervention_user = is_object($student_intervention[0]) ? $student_intervention[0] : get_user_by('ID', $student_intervention[0]);
                                    } elseif (isset($student_intervention['ID'])) {
                                        $intervention_user = get_user_by('ID', $student_intervention['ID']);
                                    } else {
                                        $intervention_user = null;
                                    }
                                } elseif (is_object($student_intervention) && isset($student_intervention->display_name)) {
                                    $intervention_user = $student_intervention;
                                } elseif (is_numeric($student_intervention)) {
                                    $intervention_user = get_user_by('ID', $student_intervention);
                                } else {
                                    $intervention_user = null;
                                }
                                
                                if ($intervention_user && isset($intervention_user->display_name)) {
                                    echo esc_html($intervention_user->display_name);
                                } else {
                                    echo 'No student specified';
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($note_to_manager)): ?>
                        <div class="note-section">
                            <h4 class="note-title">
                                <span class="dashicons dashicons-businessman"></span>
                                Note to Manager
                            </h4>
                            <div class="note-content">
                                <?php echo wp_kses_post(nl2br($note_to_manager)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($health_safety)): ?>
                        <div class="note-section">
                            <h4 class="note-title">
                                <span class="dashicons dashicons-shield"></span>
                                Health and Safety
                            </h4>
                            <div class="note-content">
                                <?php echo wp_kses_post(nl2br($health_safety)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($general_notes)): ?>
                        <div class="note-section">
                            <h4 class="note-title">
                                <span class="dashicons dashicons-edit"></span>
                                General Session Notes
                            </h4>
                            <div class="note-content">
                                <?php echo wp_kses_post($general_notes); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (empty($conversations) && empty($interventions_concerns) && empty($student_intervention) && empty($note_to_manager) && empty($health_safety) && empty($general_notes)): ?>
                        <div class="no-notes">
                            <p>No session notes recorded for this session.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    /* Modern Minimalistic Black & White Styles - Matching Student Table */
    .wcb-single-session {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    /* ================================
       SESSION HEADER STYLES
       ================================ */
    .session-header {
        background: #000000;
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .session-title {
        margin: 0 0 12px 0;
        font-size: 24px !important;
        font-weight: 600;
        color: white !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .session-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
        opacity: 0.9;
    }
    
    .session-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }
    
    .session-meta span .dashicons {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .btn-edit-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.15);
        color: white !important;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        border-radius: 8px;
        cursor: pointer;
        min-width: 140px;
    }
    
    .btn-edit-modern:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.6);
        color: white !important;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    
    .btn-edit-modern:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-dashboard-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        background: rgba(33, 150, 243, 0.15);
        color: #2196f3 !important;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border: 2px solid rgba(33, 150, 243, 0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        border-radius: 8px;
        cursor: pointer;
        min-width: 140px;
        margin-right: 12px;
    }
    
    .btn-dashboard-modern:hover {
        background: rgba(33, 150, 243, 0.25);
        border-color: rgba(33, 150, 243, 0.6);
        color: #2196f3 !important;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(33, 150, 243, 0.2);
    }
    
    .btn-dashboard-modern:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.15);
    }
    
        /* ================================
       SESSION CONTENT & SECTIONS
       ================================ */
    .session-content {
        padding: 24px;
        background: white;
    }
    
    .session-section {
        margin-bottom: 32px;
    }
    
    .session-section:last-child {
        margin-bottom: 0;
    }
    
    .section-title {
        background: #f8f9fa;
        padding: 16px 20px;
        margin: 0 0 20px 0;
        font-size: 14px !important;
        font-weight: 700;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .section-title .dashicons {
        font-size: 14px;
        color: #666666;
        height: auto;
    }
    
    /* ================================
       ATTENDANCE SUMMARY STATS
       ================================ */
    .attendance-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card.present {
        background: #f8f9fa;
        border-color: #000000;
        border-width: 1px;
    }
    
    .stat-card.excused {
        background: #f8f9fa;
        border-color: #666666;
        border-width: 1px;
    }
    
    .stat-card.total {
        background: #f8f9fa;
        border-color: #333333;
        border-width: 1px;
    }
    
    .stat-card.one-on-one {
        border-color: #000000;
        border-width: 2px;
    }
    
    .stat-card.intervention {
        border-color: #856404;
        border-width: 2px;
    }
    
    .stat-card.staff {
        border-color: #333333;
        border-width: 2px;
    }
    
    .stat-card h4 {
        margin: 0 0 8px 0;
        font-size: 24px;
        font-weight: 600;
        color: #000000;
    }
    
    .stat-card p {
        margin: 0;
        color: #666666;
        font-weight: 500;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* ================================
       STUDENT LISTS & ATTENDANCE
       ================================ */
    .student-lists {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
    }
    
    .student-list {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 0;
    }
    
    .student-list .column-header {
        border: none;
        border-bottom: 2px solid #e5e5e5;
        margin: 0;
        border-radius: 0;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        font-size: 14px !important;
        font-weight: 700;
        letter-spacing: 0.8px;
    }
    
    .student-grid {
        padding: 0;
        border: none;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        overflow: hidden;
    }
    
    .student-card {
        background: white;
        padding: 12px 16px;
        border-bottom: 1px solid #f1f1f1;
        transition: all 0.2s ease;
        border-left: none;
        border-right: none;
        margin: 0;
    }
    
    .student-card:last-child {
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
    }
    
    .student-card:hover {
        background-color: #fafafa;
        transform: translateX(4px);
    }
    
    .student-card.excused {
        opacity: 0.7;
        background-color: #f8f9fa;
    }
    
    .student-name {
        font-weight: 600;
        color: #000000;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .student-email {
        color: #666666;
        font-size: 13px;
        font-weight: 400;
    }
    
    .session-notes-content {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 20px;
        line-height: 1.6;
        color: #000000;
    }
    
    .session-notes-content h4 {
        margin-top: 0;
        margin-bottom: 16px;
        color: #000000;
        font-size: 18px;
        font-weight: 600;
    }
    
    /* Modern Session Info Styling (for regular sessions) */
    
    /* Modern Session Notes Styling (for regular sessions) */
    .session-notes-section-modern {
        margin-top: 32px;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .session-notes-section-modern .section-title {
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .notes-content-modern {
        padding: 24px;
        background: white;
        line-height: 1.6;
        color: #333333;
        font-size: 14px;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        margin-top: -20px;
    }
    
    .notes-content-modern h1,
    .notes-content-modern h2,
    .notes-content-modern h3,
    .notes-content-modern h4,
    .notes-content-modern h5,
    .notes-content-modern h6 {
        color: #000000;
        margin-top: 0;
        margin-bottom: 12px;
        font-weight: 600;
    }
    
    .notes-content-modern p {
        margin-bottom: 16px;
        color: #333333;
    }
    
    .notes-content-modern ul,
    .notes-content-modern ol {
        margin-bottom: 16px;
        padding-left: 20px;
    }
    
    .notes-content-modern li {
        margin-bottom: 8px;
        color: #333333;
    }
    
    .session-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid rgba(0,0,0,0.1);
        background: white;
        color: #000000;
    }
    
    .session-type-badge.mentoring {
        background: #E0DAFD;
        color: #856404;
        border-color: #856404;
    }
    
    /* ================================
       INTERVENTION SESSION LAYOUT
       ================================ */
    .intervention-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-top: 24px;
    }
    
    .intervention-attendees-column,
    .intervention-info-column,
    .session-info-column {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    /* ================================
       REGULAR SESSION LAYOUT
       ================================ */
    .regular-session-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-top: 24px;
    }
    
    .session-attendees-column {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    /* Scrollable Attendees */
    .scrollable-attendees {
        max-height: 400px;
        overflow-y: auto;
        border-top: none;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
    }
    
    /* Instructor Box */
    .instructor-box {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    /* Session Details Box */
    .session-details-box {
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }
    
    /* Session Notes Section (Column) */
    .session-notes-section-column {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .notes-content-column {
        padding: 24px;
        background: white;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        margin-top: -20px;
    }
    
    /* Session Information Section (Full Width) */
    .session-info-section-full {
        margin-top: 32px;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .session-info-section-full .section-title {
        background: #f8f9fa;
        margin: 0 0 20px 0;
        padding: 16px 20px;
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .session-info-content-full {
        padding: 24px;
        background: white;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        margin-top: -20px;
    }
    
    .session-info-content-full .session-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
    }
    
    /* Instructor Details */
    .instructor-details {
        padding: 0 24px 24px 24px;
    }
    
    .instructor-details .attendee-card {
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        margin-bottom: 0;
    }
    
    /* Attendee Summary */
    .attendee-summary {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        padding: 20px 24px 0 24px;
    }
    
    .summary-item {
        flex: 1;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .summary-icon {
        font-size: 18px;
        line-height: 1;
        color: #666666;
    }
    
    .summary-icon .dashicons {
        font-size: 18px;
    }
    
    .summary-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .summary-number {
        font-size: 20px;
        font-weight: 600;
        color: #000000;
        line-height: 1;
    }
    
    .summary-label {
        font-size: 12px;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    /* Attendee Sections */
    .attendee-section {
        margin-bottom: 24px;
        padding: 0 24px;
    }
    
    .attendee-section:last-child {
        margin-bottom: 24px;
    }
    
    /* Column Headers (matching dashboard table style) */
    .column-header {
        background: #f8f9fa;
        color: #000000;
        padding: 16px 20px;
        margin: 0;
        text-align: left;
        font-weight: 700;
        border: 1px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-size: 14px !important;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    /* Attendee Cards */
    .attendee-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: white;
        border: 1px solid #e5e5e5;
        border-top: none;
        margin-bottom: 0;
        transition: all 0.2s ease;
    }
    
    .attendee-section:last-child .attendee-card:last-child {
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
    }
    
    .attendee-card:first-child {
        border-top: 1px solid #e5e5e5;
    }
    
    .attendee-card:not(:last-child) {
        border-bottom: 1px solid #f1f1f1;
    }
    
    .attendee-section {
        margin-bottom: 24px;
    }
    
    .attendee-card:hover {
        background: #fafafa;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    
    .attendee-avatar {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
    }
    
    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: #f0f0f0;
        border: 1px solid #e5e5e5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666666;
    }
    
    .avatar-placeholder .dashicons {
        font-size: 14px;
    }
    
    .staff-avatar {
        background: #333333;
        color: white;
        border-color: #333333;
    }
    
    .staff-avatar .dashicons {
        color: white;
    }
    
    .attendee-info {
        flex: 1;
        min-width: 0;
    }
    
    .attendee-name {
        font-weight: 600;
        color: #000000;
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .attendee-email {
        color: #666666;
        font-size: 13px;
        margin-bottom: 2px;
        word-break: break-word;
    }
    
    .attendee-role {
        color: #999999;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 500;
    }
    
    /* Other Attendees Card */
    .other-attendees-card {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-top: none;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        padding: 16px;
        line-height: 1.5;
        color: #333333;
    }
    
    /* Session Details Grid */
    .session-details-grid {
        padding: 24px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }
    
    .detail-item:hover {
        background: #f0f0f0;
    }
    
    .detail-icon {
        font-size: 16px;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
        color: #666666;
    }
    
    .detail-icon .dashicons {
        font-size: 16px;
    }
    
    .detail-content {
        flex: 1;
        min-width: 0;
    }
    
    .detail-label {
        font-size: 12px;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    
    .detail-value {
        color: #000000;
        font-weight: 500;
        font-size: 14px;
        word-break: break-word;
    }
    
    /* Session Notes Section */
    .session-notes-section {
        padding: 24px;
        border-top: 1px solid #e5e5e5;
        margin-top: 24px;
    }
    
    .notes-title {
        font-size: 14px !important;
        font-weight: 600;
        margin-bottom: 12px;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .notes-content {
        background: white;
        border: 1px solid #e5e5e5;
        padding: 16px;
        line-height: 1.6;
        color: #333333;
    }
    
    /* Full Width Intervention Debrief Section */
    .intervention-debrief-section {
        margin-top: 32px;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .intervention-debrief-section .section-title {
        background: #f8f9fa;
        margin: 0 0 20px 0;
        padding: 16px 20px;
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .debrief-content {
        padding: 24px;
        background: white;
        line-height: 1.6;
        color: #333333;
        font-size: 14px;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        margin-top: -20px;
    }
    
    .debrief-content h1,
    .debrief-content h2,
    .debrief-content h3,
    .debrief-content h4,
    .debrief-content h5,
    .debrief-content h6 {
        color: #000000;
        margin-top: 0;
        margin-bottom: 12px;
        font-weight: 600;
    }
    
    .debrief-content p {
        margin-bottom: 16px;
        color: #333333;
    }
    
    .debrief-content ul,
    .debrief-content ol {
        margin-bottom: 16px;
        padding-left: 20px;
    }
    
    .debrief-content li {
        margin-bottom: 8px;
        color: #333333;
    }
    
    /* Full Width Session Notes Section (Regular Sessions) */
    .session-notes-section-full {
        margin-top: 32px;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .session-notes-section-full .section-title {
        background: #f8f9fa;
        margin: 0 0 20px 0;
        padding: 16px 20px;
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .notes-content-full {
        padding: 24px;
        background: white;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        margin-top: -20px;
    }
    
    .note-section {
        margin-bottom: 24px;
        padding: 20px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
    }
    
    .note-section:last-child {
        margin-bottom: 0;
    }
    
    .note-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px 0;
        font-size: 14px !important;
        font-weight: 600;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .note-title .dashicons {
        font-size: 14px;
        color: #666666;
    }
    
    .note-content {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 16px;
        line-height: 1.6;
        color: #333333;
        font-size: 14px;
    }
    
    .note-content p {
        margin-bottom: 12px;
        color: #333333;
    }
    
    .note-content p:last-child {
        margin-bottom: 0;
    }
    
    .no-notes {
        text-align: center;
        color: #666666;
        padding: 40px;
        background: #f8f9fa;
        font-style: italic;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
    }
    
    .no-students {
        text-align: center;
        color: #666666;
        padding: 40px;
        background: #f8f9fa;
        font-style: italic;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
    }
    
    .error {
        text-align: center;
        color: #000000;
        background: #f8f9fa;
        padding: 20px;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
    }
    
    /* ================================
       RESPONSIVE DESIGN
       ================================ */
    @media (max-width: 768px) {
        .session-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .session-title {
            font-size: 20px !important;
        }
        
        .session-meta {
            gap: 12px;
            font-size: 13px;
            justify-content: flex-start;
        }
        
        .session-meta span .dashicons {
            font-size: 14px;
        }
        
        .btn-edit-modern {
            padding: 10px 20px;
            font-size: 13px;
            letter-spacing: 0.5px;
            min-width: 120px;
        }
        
        .session-content {
            padding: 16px;
        }
        
        .student-lists {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .attendance-stats {
            grid-template-columns: 1fr;
        }
        
        .session-info-modern .detail-item {
            padding: 10px 12px;
        }
        
        .notes-content-modern {
            padding: 16px;
        }
        
        .section-title {
            font-size: 14px !important;
        }
        
        /* Intervention Layout Mobile */
        .intervention-layout {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        /* Regular Session Layout Mobile */
        .regular-session-layout {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        .attendee-summary {
            padding: 0 16px;
            gap: 8px;
        }
        
        .summary-item {
            padding: 12px;
        }
        
        .attendee-section {
            padding: 0 16px;
        }
        
        .session-details-grid {
            padding: 16px;
            gap: 12px;
        }
        
        .session-notes-section {
            padding: 16px;
        }
        
        .instructor-box {
            margin-bottom: 16px;
        }
        
        .instructor-details {
            padding: 0 16px 16px 16px;
        }
        
        .notes-content-full {
            padding: 16px;
        }
        
        .notes-content-column {
            padding: 16px;
        }
        
        .session-info-content-full {
            padding: 16px;
        }
        
        .session-info-content-full .session-details-grid {
            grid-template-columns: 1fr;
        }
        
        .note-section {
            padding: 16px;
        }
        
        .attendee-card {
            padding: 12px;
        }
        
        .attendee-avatar {
            width: 40px;
            height: 40px;
        }
    }
    
    @media (max-width: 600px) {
        .session-meta {
            flex-direction: column;
            gap: 8px;
        }
        
        .btn-edit-modern {
            padding: 8px 16px;
            font-size: 12px;
            min-width: 100px;
        }
        
        .student-lists {
            gap: 16px;
        }
        
        .attendance-stats {
            gap: 12px;
        }
        
        .session-info-modern .detail-item {
            gap: 8px;
        }
        
        /* Small screen intervention adjustments */
        .attendee-summary {
            flex-direction: column;
            gap: 8px;
        }
        
        .summary-item {
            flex-direction: row;
            text-align: left;
            padding: 12px;
        }
        
        .summary-content {
            flex-direction: row;
            align-items: center;
            gap: 8px;
        }
        
        .summary-number {
            font-size: 18px;
        }
        
        .attendee-section {
            margin-bottom: 20px;
        }
        
        .column-header {
            padding: 10px 12px;
            font-size: 12px !important;
        }
        
        .attendee-card {
            padding: 12px;
        }
        
        .notes-content-full {
            padding: 12px;
        }
        
        .notes-content-column {
            padding: 12px;
        }
        
        .session-info-content-full {
            padding: 12px;
        }
        
        .note-section {
            padding: 12px;
            margin-bottom: 16px;
        }
        
        .note-title {
            font-size: 12px !important;
        }
        
        .note-content {
            padding: 12px;
        }
        
        .instructor-box {
            margin-bottom: 12px;
        }
        
        .instructor-details {
            padding: 0 12px 12px 12px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('single_session', 'single_session_shortcode');


