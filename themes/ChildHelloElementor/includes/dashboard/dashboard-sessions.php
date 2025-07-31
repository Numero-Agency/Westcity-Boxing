<?php
// Sessions List Component

function all_sessions_list_shortcode() {
    $sessions = get_posts([
        'post_type' => 'session_log',
        'numberposts' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    ob_start();
    ?>
    <div class="all-sessions-container">
        <div class="sessions-header">
            <div class="sessions-title-section">
                <h3><span class="dashicons dashicons-clipboard"></span> All Sessions Log</h3>
                <span class="sessions-count"><?php echo count($sessions); ?> sessions total</span>
            </div>
            <div class="sessions-filter-actions">
                <a href="https://westcityboxing.local/class-session-log/" 
                   class="btn-log-simple btn-log-class">Log Class Session</a>
                <a href="https://westcityboxing.local/intervention-session-log/" 
                   class="btn-log-simple btn-log-mentoring">Log Mentoring Session</a>
                <select id="session-type-filter">
                    <option value="">All Session Types</option>
                    <option value="class-session">Class Sessions</option>
                    <option value="school-session">School Sessions</option>
                    <option value="mentoring-intervention">Interventions</option>
                </select>
            </div>
        </div>
        
        <?php if($sessions): ?>
        
        <?php if (current_user_can('administrator') && isset($_GET['debug'])): ?>
        <!-- Debug Information for Taxonomy Detection -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-family: monospace; font-size: 12px;">
            <h4>🐛 Taxonomy Debug Information</h4>
            <?php 
            // Show detected taxonomy
            $detected_taxonomy = wcb_get_session_type_taxonomy();
            echo '<p><strong>🎯 Auto-detected taxonomy:</strong> <span style="color: green; font-weight: bold;">' . $detected_taxonomy . '</span></p>';
            
            // Get first session for testing
            $test_session = $sessions[0];
            $test_session_id = $test_session->ID;
            $session_type_data = wcb_get_session_type($test_session_id);
            echo '<p><strong>🔍 Test session #' . $test_session_id . ' type:</strong> ' . $session_type_data['name'] . ' (' . $session_type_data['slug'] . ')</p>';
            
            // Check all available taxonomies for session_log post type
            $available_taxonomies = get_object_taxonomies('session_log', 'objects');
            echo '<p><strong>📋 Available taxonomies for session_log:</strong></p>';
            echo '<ul>';
            foreach ($available_taxonomies as $tax_slug => $tax_object) {
                $marker = ($tax_slug === $detected_taxonomy) ? ' ✅ <strong>(SELECTED)</strong>' : '';
                echo '<li>' . $tax_slug . ' (' . $tax_object->labels->name . ')' . $marker . '</li>';
            }
            echo '</ul>';
            ?>
        </div>
        <?php endif; ?>
        
        <div class="sessions-table-container">
            <table class="sessions-table" id="sessions-table">
                <thead>
                    <tr>
                        <th><span class="dashicons dashicons-calendar-alt"></span> Date</th>
                        <th><span class="dashicons dashicons-category"></span> Type</th>
                        <th><span class="dashicons dashicons-groups"></span> Class/Program</th>
                        <th><span class="dashicons dashicons-yes"></span> Present</th>
                        <th><span class="dashicons dashicons-chart-bar"></span> Total</th>
                        <th><span class="dashicons dashicons-chart-pie"></span> Attendance</th>
                        <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sessions as $session): ?>
                        <?php 
                        // Get session data with error checking
                        $session_id = $session->ID;
                        $date = get_field('session_date', $session_id);
                        $intervention_date = get_field('intervention_date_', $session_id);

                        // If no session_date found, try alternative methods
                        if (empty($date)) {
                            // Try raw meta value
                            $date = get_post_meta($session_id, 'session_date', true);
                        }

                        // Check for both old and new field names for backward compatibility
                        $membership_id = get_field('selected_membership', $session_id); // Old format
                        $group_id = get_field('selected_group', $session_id); // New format from updated form

                        $associated_student = get_field('associated_student', $session_id);
                        $student_involved = get_field('student_involved', $session_id); // For interventions
                        
                        // Use the new helper function to get real attendance data
                        $attendance_data = wcb_get_session_attendance($session_id);
                        $attended_students = $attendance_data['attended'];
                        $excused_students = $attendance_data['excused'];
                        
                        // Get session type first to determine logic flow
                        $session_type_data = wcb_get_session_type($session_id);
                        $session_type_raw = $session_type_data['name'];
                        $session_type_slug = $session_type_data['slug'];
                        
                        // Get class name from membership or group
                        $class_name = 'Unknown Class';

                        // Try new format first (selected_group)
                        if ($group_id) {
                            $group_post = get_post($group_id);
                            if($group_post) {
                                $class_name = $group_post->post_title;
                            }
                        }
                        // Fallback to old format (selected_membership)
                        elseif ($membership_id) {
                            $membership_post = get_post($membership_id);
                            if($membership_post) {
                                $class_name = $membership_post->post_title;
                            }
                        }
                        
                        // Get class type field for proper display
                        $class_type = get_field('class_type', $session_id);

                        // Debug info for administrators
                        if (current_user_can('administrator') && isset($_GET['debug'])) {
                            echo "<!-- Session ID: $session_id, Type: $session_type_raw, Class Type: $class_type, Group ID: $group_id, Membership ID: $membership_id, Date: '$date', Intervention Date: '$intervention_date' -->";
                        }

                        // Determine display name based on taxonomy + class type
                        if ($session_type_raw === 'Class' && $class_type === 'School') {
                            $session_type = 'School Session';
                            $session_type_slug = 'school-session';
                        } elseif ($session_type_raw === 'Class' && $class_type === 'Class') {
                            $session_type = 'Class Session';
                            $session_type_slug = 'class-session';
                        } elseif ($session_type_raw === 'Mentoring') {
                            $session_type = 'Mentoring';
                            $session_type_slug = 'mentoring-intervention';
                        } else {
                            $session_type = $session_type_raw; // Fallback
                        }
                        
                        // Format date - use intervention_date for mentoring sessions
                        if ($session_type_raw === 'Mentoring' && !empty($intervention_date)) {
                            $date_timestamp = strtotime($intervention_date);
                            if ($date_timestamp) {
                                $formatted_date = date('d/m/Y', $date_timestamp);
                            } else {
                                $formatted_date = date('d/m/Y', strtotime($session->post_date));
                            }
                        } elseif (!empty($date)) {
                            // Handle both datetime-local format (2024-03-15T14:30) and regular date formats
                            $date_timestamp = strtotime($date);
                            if ($date_timestamp && $date_timestamp > 0) {
                                $formatted_date = date('d/m/Y', $date_timestamp);
                            } else {
                                // Try to parse datetime-local format manually
                                if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/', $date, $matches)) {
                                    $formatted_date = $matches[3] . '/' . $matches[2] . '/' . $matches[1];
                                } else {
                                    // Fallback to post creation date
                                    $formatted_date = date('d/m/Y', strtotime($session->post_date));
                                }
                            }
                        } else {
                            // Fallback to post creation date
                            $formatted_date = date('d/m/Y', strtotime($session->post_date));
                        }
                        
                        // Calculate totals and student info based on session type
                        if ($session_type_raw === 'Mentoring' && $student_involved) {
                            // Intervention session - use student_involved field
                            $total_present = 1;
                            $total_excused = 0;
                            $total_students = 1;
                            
                            // Get student info for intervention
                            $student_id = intval($student_involved);
                            $student_info = '';
                            if ($student_id) {
                                $student = get_user_by('ID', $student_id);
                                $student_info = $student ? $student->display_name : 'Unknown Student';
                            }
                        } elseif($associated_student) {
                            // 1-on-1 session (non-intervention)
                            $total_present = 1;
                            $total_excused = 0;
                            $total_students = 1;
                            
                            // Get student info for 1-on-1
                            $student_id = null;
                            if (is_object($associated_student) && isset($associated_student->ID)) {
                                $student_id = $associated_student->ID;
                            } elseif (is_array($associated_student) && isset($associated_student['ID'])) {
                                $student_id = $associated_student['ID'];
                            } elseif (is_numeric($associated_student)) {
                                $student_id = intval($associated_student);
                            }
                            
                            $student_info = '';
                            if ($student_id) {
                                $student = get_user_by('ID', $student_id);
                                $student_info = $student ? $student->display_name : 'Unknown Student';
                            }
                        } else {
                            // Group session - use real attendance data and group member count
                            $total_present = count($attended_students);
                            $total_excused = count($excused_students);

                            // Get actual group member count for new format sessions
                            if ($group_id) {
                                $total_students = wcb_get_group_member_count($group_id);
                            } else {
                                // Fallback for old format sessions
                                $total_students = $total_present + $total_excused;
                            }

                            $student_info = '';
                        }
                        ?>
                        <tr data-session-type="<?php echo esc_attr($session_type_slug); ?>">
                            <td class="session-date">
                                <div class="date-display">
                                    <span class="date-main"><?php echo esc_html($formatted_date); ?></span>
                                    <?php
                                    // Show time for regular sessions, not for interventions (which typically don't have specific times)
                                    if($session_type_raw !== 'Mentoring'):
                                        // Try to get time from dedicated time fields
                                        $session_time = get_field('session_time', $session_id);
                                        $start_time = get_field('start_time', $session_id);

                                        $display_time = '';
                                        if ($session_time) {
                                            $display_time = date('g:i A', strtotime($session_time));
                                        } elseif ($start_time) {
                                            $display_time = date('g:i A', strtotime($start_time));
                                        } elseif ($date && strpos($date, ':') !== false) {
                                            // Only extract time if date field actually contains time
                                            $display_time = date('g:i A', strtotime($date));
                                        }

                                        if ($display_time && $display_time !== '12:00 AM'): ?>
                                    <span class="date-time"><?php echo $display_time; ?></span>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="session-type-badge <?php echo esc_attr($session_type_slug); ?>">
                                    <?php echo esc_html($session_type); ?>
                                </span>
                            </td>
                            <td class="session-class">
                                <div class="class-info">
                                    <strong><?php echo esc_html($class_name); ?></strong>
                                    <?php if($student_info): ?>
                                        <div class="student-info">Student: <?php echo esc_html($student_info); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="present-count">
                                <div class="attendance-count present">
                                    <span class="count-number"><?php echo $total_present; ?></span>
                                    <span class="count-label">Present</span>
                                </div>
                            </td>
                            <td class="total-count">
                                <div class="attendance-count total">
                                    <span class="count-number"><?php echo $total_students; ?></span>
                                    <span class="count-label">Total</span>
                                </div>
                            </td>
                            <td class="attendance-percentage">
                                <div class="attendance-count percentage">
                                    <span class="count-number">
                                        <?php
                                        $attendance_percentage = $total_students > 0 ? round(($total_present / $total_students) * 100) : 0;
                                        echo $attendance_percentage . '%';
                                        ?>
                                    </span>
                                    <span class="count-label">Attendance</span>
                                </div>
                            </td>
                            <td class="session-actions">
                                <div class="action-buttons">
                                    <a href="<?php echo get_permalink($session_id); ?>" class="btn-view" title="View Details">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </a>
                                    <a href="<?php echo admin_url('post.php?post=' . $session_id . '&action=edit'); ?>" class="btn-edit" title="Edit Session">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#session-type-filter').on('change', function() {
                var filterValue = $(this).val();
                var rows = $('#sessions-table tbody tr');
                
                if(filterValue === '') {
                    rows.show();
                } else {
                    rows.hide();
                    rows.filter('[data-session-type="' + filterValue + '"]').show();
                }
            });
        });
        </script>
        
        <?php else: ?>
        <div class="no-sessions">
            <div class="no-sessions-icon"><span class="dashicons dashicons-clipboard"></span></div>
            <h3>No sessions logged yet</h3>
            <p>Start tracking your coaching sessions to see detailed analytics here.</p>
            <a href="<?php echo admin_url('post-new.php?post_type=session_log'); ?>" class="btn-create-session">Log First Session</a>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
    /* Modern Minimalistic Black & White Sessions Table */
    .all-sessions-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: white;
        border: 1px solid #e5e5e5;
        overflow: hidden;
        margin-bottom: 40px;
    }
    
    /* Force override browser table defaults */
    .all-sessions-container table {
        border: none !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }
    
    .all-sessions-container th,
    .all-sessions-container td {
        border: none !important;
        margin: 0 !important;
        padding: 16px 20px !important;
        border-bottom: 1px solid #f1f1f1 !important;
    }
    
    .all-sessions-container th {
        border-bottom: 1px solid #e5e5e5 !important;
    }
    
    .sessions-header {
        background: #000000;
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .sessions-title-section h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    
    .sessions-title-section h3 .dashicons {
        font-size: 20px;
        color: white;
    }
    
    .sessions-count {
        font-size: 14px;
        color: white;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .sessions-filter-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .sessions-filter-actions select {
        padding: 8px 12px;
        border: 1px solid #e5e5e5;
        background: white;
        color: #000000;
        font-size: 14px;
        outline: none;
        min-width: 160px;
        border-radius: 4px;
    }
    
    .btn-log-simple {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border-radius: 4px;
        transition: all 0.2s ease;
        white-space: nowrap;
        border: none;
    }
    
    .btn-log-class {
        background: #4caf50;
        color: white !important;
    }
    
    .btn-log-class:hover {
        background: #45a049;
        color: white !important;
        text-decoration: none;
    }
    
    .btn-log-mentoring {
        background: #9c27b0;
        color: white !important;
    }
    
    .btn-log-mentoring:hover {
        background: #8e24aa;
        color: white !important;
        text-decoration: none;
    }
    
    .sessions-table-container {
        overflow-x: auto;
        background: white;
        border: none;
        border-radius: 0;
    }
    
    .sessions-table-container table {
        border: none !important;
        border-collapse: collapse !important;
    }
    
    .sessions-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        font-size: 14px;
        min-width: 900px;
        border: none;
        border-spacing: 0;
        table-layout: auto;
    }
    
    .sessions-table th {
        background: #f8f9fa;
        color: #000000;
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        border: none;
        border-bottom: 1px solid #e5e5e5;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: top;
    }
    
    .sessions-table th .dashicons {
        font-size: 16px;
        margin-right: 6px;
        vertical-align: middle;
        color: #666666;
    }
    
    .sessions-table td {
        padding: 16px 20px;
        border: none;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        color: #000000;
        background: white;
        text-align: left;
    }
    
    .sessions-table tr:hover {
        background: #fafafa;
    }
    
    .sessions-table tr:hover td {
        background: #fafafa;
    }
    
    /* Date Column */
    .date-display {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .date-main {
        font-weight: 600;
        color: #000000;
    }
    
    .date-time {
        font-size: 12px;
        color: #666666;
    }
    
    /* Session Type Badges */
    .session-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid rgba(0,0,0,0.1);
        white-space: nowrap;
        background-color: #C4ECFF;
    }
    
    .session-type-badge.class-session {
        background: #D1E2FF !important;
        color: #000000;
        border: 1px solid #000000 !important;
    }
    
    .session-type-badge.mentoring-intervention {
        background: #E0DAFD;
        color: #000000;
    }
    
    .session-type-badge.referral {
        background: #f8f9fa;
        color: #000000;
        border-color: #E0DAFD!important;
    }
    
    .session-type-badge.unknown {
        background: #f8f9fa;
        color: #000000;
    }
    
    /* Class Info */
    .class-info strong {
        color: #000000;
        display: block;
        margin-bottom: 4px;
        font-weight: 600;
    }
    
    .student-info {
        font-size: 12px;
        color: #666666;
        font-style: italic;
    }
    
    /* Attendance Counts */
    .attendance-count {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    
    .count-number {
        font-size: 18px;
        font-weight: 700;
        line-height: 1;
        color: #000000;
    }
    
    .count-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #666666;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .btn-view,
    .btn-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        background: #3498db;
        color: white !important;
        text-decoration: none;
        border-radius: 6px;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn-view:hover {
        background: #2980b9;
        color: white;
        text-decoration: none;
    }
    
    .btn-edit {
        background: #f39c12;
    }
    
    .btn-edit:hover {
        background: #e67e22;
        color: white;
        text-decoration: none;
    }
    
    /* No Sessions State */
    .no-sessions {
        text-align: center;
        padding: 60px 30px;
        color: #666666;
        background: white;
    }
    
    .no-sessions-icon {
        margin-bottom: 20px;
    }
    
    .no-sessions-icon .dashicons {
        font-size: 48px;
        color: #666666;
    }
    
    .no-sessions h3 {
        margin: 0 0 16px 0;
        font-size: 24px;
        color: #000000;
        font-weight: 600;
    }
    
    .no-sessions p {
        margin: 0 0 24px 0;
        color: #666666;
        font-size: 16px;
    }
    
    .btn-create-session {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 24px;
        background: #000000;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
    }
    
    .btn-create-session:hover {
        background: #333333;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .sessions-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .sessions-title-section h3 {
            font-size: 16px;
        }
        
        .sessions-filter-actions {
            gap: 8px;
        }
        
        .btn-log-simple {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .sessions-table th,
        .sessions-table td {
            padding: 12px 16px;
            font-size: 12px;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 4px;
        }
        
        .btn-view,
        .btn-edit {
            padding: 6px 12px;
            font-size: 11px;
        }
        
        .count-number {
            font-size: 16px;
        }
        
        .count-label {
            font-size: 9px;
        }
        
        .no-sessions h3 {
            font-size: 20px;
        }
        
        .no-sessions p {
            font-size: 14px;
        }
    }
    
    @media (max-width: 600px) {
        .sessions-table th:nth-child(4),
        .sessions-table td:nth-child(4),
        .sessions-table th:nth-child(5),
        .sessions-table td:nth-child(5) {
            display: none;
        }
        
        .sessions-table th,
        .sessions-table td {
            padding: 10px 12px;
        }
        
        .sessions-table th .dashicons {
            font-size: 14px;
            margin-right: 4px;
        }
        
        .sessions-filter-actions {
            flex-direction: column;
            gap: 8px;
            align-items: stretch;
        }
        
        .btn-log-simple {
            padding: 6px 10px;
            font-size: 11px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('all_sessions_list', 'all_sessions_list_shortcode');
