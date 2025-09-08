<?php
/**
 * Single Competition Display Component
 * Displays a single competition record with all details
 */

function single_competition_shortcode($atts) {
    $atts = shortcode_atts([
        'competition_id' => '',
        'class' => 'wcb-single-competition',
        'show_edit_link' => 'true'
    ], $atts);
    
    // Get competition ID from various sources
    $competition_id = '';
    // 1. From shortcode attribute
    if (!empty($atts['competition_id'])) {
        $competition_id = intval($atts['competition_id']);
    }
    // 2. From URL parameter
    if (empty($competition_id) && isset($_GET['competition_id'])) {
        $competition_id = intval($_GET['competition_id']);
    }
    // 3. From current post ID (for single post pages)
    if (empty($competition_id)) {
        $current_post_id = get_the_ID();
        if ($current_post_id) {
            $current_post = get_post($current_post_id);
            if ($current_post && $current_post->post_type === 'competition') {
                $competition_id = $current_post_id;
            }
        }
    }
    if (empty($competition_id)) {
        return '<div class="error">No competition found. Please check the URL or contact support.</div>';
    }
    // Get competition from WordPress post type
    $competition_post = get_post($competition_id);
    if (!$competition_post || $competition_post->post_type !== 'competition') {
        return '<div class="error">Competition not found (ID: ' . $competition_id . ')</div>';
    }
    // Get competition data from ACF fields
    $event_name = $competition_post->post_title;
    $event_date = get_field('event_date', $competition_id);
    $where_was_it_hosted = get_field('where_was_it_hosted', $competition_id);
    $who_else_attended = get_field('who_else_attended', $competition_id);
    $results_wins = get_field('results_wins', $competition_id) ?: 0;
    $results_lost = get_field('results_lost', $competition_id) ?: 0;
    $highlights = get_field('highlights', $competition_id);
    $creator = get_user_by('ID', $competition_post->post_author);
    $creator_name = $creator ? $creator->display_name : 'Unknown';
    $created_date = $competition_post->post_date;
    $modified_date = $competition_post->post_modified;
    $formatted_date = $event_date ? date('l, F j, Y', strtotime($event_date)) : 'Unknown Date';
    
    // Get new multi-student data
    $students_involved = get_field('students_involved', $competition_id) ?: [];
    $student_detailed_results = get_field('student_detailed_results', $competition_id) ?: [];
    $legacy_student = get_field('student_involved', $competition_id); // For backward compatibility
    
    // Handle backward compatibility and data processing
    $processed_students = [];
    $total_matches = $results_wins + $results_lost;
    $win_percentage = $total_matches > 0 ? round(($results_wins / $total_matches) * 100, 1) : 0;
    
    // Process student data - handle both new and legacy formats
    if (!empty($students_involved) && !empty($student_detailed_results)) {
        // New multi-student format
        foreach ($student_detailed_results as $result) {
            if (isset($result['student_id'])) {
                $student_user = get_userdata($result['student_id']);
                if ($student_user) {
                    $processed_students[] = [
                        'user' => $student_user,
                        'wins' => intval($result['wins'] ?? 0),
                        'losses' => intval($result['losses'] ?? 0),
                        'total' => intval($result['wins'] ?? 0) + intval($result['losses'] ?? 0),
                        'win_rate' => (intval($result['wins'] ?? 0) + intval($result['losses'] ?? 0)) > 0 ? 
                                     round((intval($result['wins'] ?? 0) / (intval($result['wins'] ?? 0) + intval($result['losses'] ?? 0))) * 100, 1) : 0
                    ];
                }
            }
        }
    } elseif (!empty($legacy_student)) {
        // Legacy single-student format
        $student_user = null;
        if (is_array($legacy_student) && isset($legacy_student['display_name'])) {
            $student_user = (object) $legacy_student;
        } elseif (is_object($legacy_student) && isset($legacy_student->display_name)) {
            $student_user = $legacy_student;
        } elseif (is_numeric($legacy_student) && $legacy_student > 0) {
            $student_user = get_userdata($legacy_student);
        }
        
        if ($student_user) {
            $processed_students[] = [
                'user' => $student_user,
                'wins' => $results_wins,
                'losses' => $results_lost,
                'total' => $total_matches,
                'win_rate' => $win_percentage
            ];
        }
    }
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="single-competition-<?php echo $competition_id; ?>">
        <!-- Competition Header -->
        <div class="competition-header">
            <div class="competition-title-section">
                <h1 class="competition-title">Competition: <?php echo esc_html($event_name); ?></h1>
                <div class="competition-meta">
                    <span class="competition-date"><span class="dashicons dashicons-calendar-alt"></span> <?php echo esc_html($formatted_date); ?></span>
                    <span class="competition-location"><span class="dashicons dashicons-location"></span> <?php echo esc_html($where_was_it_hosted); ?></span>
                </div>
            </div>
            <?php if ($atts['show_edit_link'] === 'true' && (current_user_can('edit_posts') || get_current_user_id() == $competition_post->post_author)): ?>
            <div class="competition-actions">
                <a href="/staff-dashboard/" class="btn-dashboard-modern">Dashboard</a>
                <a href="<?php echo admin_url('post.php?post=' . $competition_id . '&action=edit'); ?>" class="btn-edit-modern">Edit Competition</a>
            </div>
            <?php endif; ?>
        </div>
        <div class="competition-content">
        <!-- Students Involved Section -->
        <div class="competition-section students-section">
            <h3 class="section-title">
                <span class="dashicons dashicons-admin-users"></span> 
                Students Involved 
                <?php if (count($processed_students) > 1): ?>
                    <span class="student-count">(<?php echo count($processed_students); ?> students)</span>
                <?php endif; ?>
            </h3>
            <?php if (!empty($processed_students)): ?>
                <div class="students-grid">
                    <?php foreach ($processed_students as $student_data): ?>
                        <div class="student-card">
                            <div class="student-header">
                                <div class="student-avatar">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </div>
                                <div class="student-info">
                                    <div class="student-name"><?php echo esc_html($student_data['user']->display_name); ?></div>
                                    <div class="student-email"><?php echo esc_html($student_data['user']->user_email); ?></div>
                                </div>
                            </div>
                            <div class="student-results">
                                <div class="student-result-item wins">
                                    <span class="result-number"><?php echo $student_data['wins']; ?></span>
                                    <span class="result-label">Wins</span>
                                </div>
                                <div class="student-result-item losses">
                                    <span class="result-number"><?php echo $student_data['losses']; ?></span>
                                    <span class="result-label">Losses</span>
                                </div>
                                <div class="student-result-item total">
                                    <span class="result-number"><?php echo $student_data['total']; ?></span>
                                    <span class="result-label">Total</span>
                                </div>
                                <div class="student-result-item win-rate">
                                    <span class="result-number"><?php echo $student_data['win_rate']; ?>%</span>
                                    <span class="result-label">Win Rate</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-students">
                    <p>No students specified for this competition.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Additional Attendees Section -->
        <?php if (!empty($who_else_attended)): ?>
        <div class="competition-section attendees-section">
            <h3 class="section-title"><span class="dashicons dashicons-groups"></span> Additional Attendees</h3>
            <div class="attendees-content">
                <div class="attendee-card others-card">
                    <div class="attendee-avatar">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="attendee-info">
                        <div class="attendee-label">Who Else Attended</div>
                        <div class="attendee-name"><?php echo esc_html($who_else_attended); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Aggregate Results Section -->
        <div class="competition-section results-section">
            <h3 class="section-title">
                <span class="dashicons dashicons-chart-bar"></span> 
                Competition Summary
                <?php if (count($processed_students) > 1): ?>
                    <span class="results-note">(Combined Results)</span>
                <?php endif; ?>
            </h3>
            <div class="results-grid">
                <div class="result-card win-card">
                    <div class="result-number"><?php echo esc_html($results_wins); ?></div>
                    <div class="result-label">Total Wins</div>
                </div>
                <div class="result-card loss-card">
                    <div class="result-number"><?php echo esc_html($results_lost); ?></div>
                    <div class="result-label">Total Losses</div>
                </div>
                <div class="result-card total-card">
                    <div class="result-number"><?php echo esc_html($total_matches); ?></div>
                    <div class="result-label">Total Matches</div>
                </div>
                <div class="result-card percentage-card">
                    <div class="result-number"><?php echo esc_html($win_percentage); ?>%</div>
                    <div class="result-label">Overall Win Rate</div>
                </div>
            </div>
            <?php if (count($processed_students) > 1): ?>
                <div class="results-info">
                    <p><strong>Note:</strong> Individual student results are shown above in the Students Involved section. These totals represent the combined performance of all <?php echo count($processed_students); ?> students in this competition.</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- Highlights Section -->
        <?php if (!empty($highlights)): ?>
        <div class="competition-section highlights-section">
            <h3 class="section-title"><span class="dashicons dashicons-star-filled"></span> Highlights</h3>
            <div class="highlights-content">
                <?php echo nl2br(esc_html($highlights)); ?>
            </div>
        </div>
        <?php endif; ?>
        <!-- Meta Info -->
        <div class="competition-section meta-section">
            <h3 class="section-title"><span class="dashicons dashicons-info"></span> Competition Details</h3>
            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Logged by:</span>
                    <span class="meta-value"><?php echo esc_html($creator_name); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Created:</span>
                    <span class="meta-value"><?php echo esc_html(date('F j, Y g:i A', strtotime($created_date))); ?></span>
                </div>
                <?php if ($modified_date !== $created_date): ?>
                <div class="meta-item">
                    <span class="meta-label">Last updated:</span>
                    <span class="meta-value"><?php echo esc_html(date('F j, Y g:i A', strtotime($modified_date))); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
    <style>
    .wcb-single-competition {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    .competition-header {
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
    .competition-title-section { flex: 1; }
    .competition-title {
        font-size: 28px!important;
        font-weight: 600;
        color: white !important;
        margin: 0 0 10px 0;
        line-height: 1.2;
    }
    .competition-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }
    .competition-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #cccccc;
        font-size: 14px;
    }
    .competition-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-dashboard-modern,
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
    .btn-dashboard-modern:hover,
    .btn-edit-modern:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.6);
        color: white !important;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    
    .btn-dashboard-modern:active,
    .btn-edit-modern:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-dashboard-modern {
        background: rgba(33, 150, 243, 0.15);
        color: #2196f3 !important;
        border: 2px solid rgba(33, 150, 243, 0.3);
        margin-right: 12px;
    }
    
    .btn-dashboard-modern:hover {
        background: rgba(33, 150, 243, 0.25);
        border-color: rgba(33, 150, 243, 0.6);
        color: #2196f3 !important;
        box-shadow: 0 8px 25px rgba(33, 150, 243, 0.2);
    }
    
    .btn-dashboard-modern:active {
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.15);
    }
    .competition-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
        padding: 24px;
    }
    .competition-section {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    .section-title {
        background: #f8f9fa;
        color: #000000;
        padding: 16px 20px;
        margin: 0;
        text-align: left;
        font-weight: 700;
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-size: 14px !important;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title .dashicons {
        font-size: 14px;
        color: #666666;
    }
    .attendees-grid,
    .attendees-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        padding: 20px;
    }
    
    /* Students Section Styles */
    .student-count {
        font-size: 12px;
        font-weight: 500;
        color: #666;
        background: rgba(255, 255, 255, 0.1);
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 10px;
    }
    
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        padding: 20px;
    }
    
    .student-card {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .student-card:hover {
        border-color: #007cba;
        box-shadow: 0 2px 8px rgba(0, 124, 186, 0.1);
    }
    
    .student-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: white;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .student-avatar {
        font-size: 24px;
        color: #007cba;
        background: #e3f2fd;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .student-info {
        flex: 1;
    }
    
    .student-name {
        font-size: 16px;
        font-weight: 600;
        color: #000;
        margin-bottom: 2px;
    }
    
    .student-email {
        font-size: 13px;
        color: #666;
    }
    
    .student-results {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #e5e5e5;
    }
    
    .student-result-item {
        background: white;
        padding: 12px 8px;
        text-align: center;
        transition: background-color 0.2s ease;
    }
    
    .student-result-item:hover {
        background: #f0f8ff;
    }
    
    .student-result-item.wins {
        border-left: 3px solid #28a745;
    }
    
    .student-result-item.losses {
        border-left: 3px solid #dc3545;
    }
    
    .student-result-item.total {
        border-left: 3px solid #007cba;
    }
    
    .student-result-item.win-rate {
        border-left: 3px solid #ffc107;
    }
    
    .student-result-item .result-number {
        display: block;
        font-size: 18px;
        font-weight: 700;
        color: #000;
        margin-bottom: 2px;
    }
    
    .student-result-item .result-label {
        display: block;
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    .no-students {
        padding: 20px;
        text-align: center;
        color: #666;
        font-style: italic;
    }
    
    .results-note {
        font-size: 11px;
        font-weight: 500;
        color: #666;
        background: rgba(255, 255, 255, 0.1);
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 10px;
    }
    
    .results-info {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 6px;
        padding: 15px;
        margin: 15px 20px 20px 20px;
        font-size: 14px;
        color: #1565c0;
        line-height: 1.5;
    }
    .attendee-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }
    .attendee-card:hover {
        background: #f0f0f0;
    }
    .attendee-avatar {
        font-size: 32px;
        color: #000000;
        background: #e5e5e5;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .attendee-info { display: flex; flex-direction: column; gap: 2px; }
    .attendee-label {
        font-size: 12px;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    .attendee-name {
        font-size: 14px;
        font-weight: 500;
        color: #000000;
    }
    .attendee-email {
        font-size: 13px;
        color: #666;
    }
    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        padding: 20px;
    }
    .result-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #e5e5e5;
        box-shadow: none;
    }
    .result-number {
        font-size: 32px;
        font-weight: bold;
        color: #000000;
        margin-bottom: 5px;
    }
    .result-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }
    .highlights-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #e5e5e5;
        color: #000000;
        line-height: 1.6;
    }
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        padding: 20px;
    }
    .meta-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }
    .meta-item:hover {
        background: #f0f0f0;
    }
    .meta-label {
        font-size: 12px;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    .meta-value {
        color: #000000;
        font-weight: 500;
        font-size: 14px;
    }
    /* Responsive Design */
    @media (max-width: 768px) {
        .wcb-single-competition {
            margin: 10px;
        }
        .competition-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
            padding: 20px;
        }
        .competition-title {
            font-size: 24px;
        }
        .competition-actions {
            justify-content: stretch;
        }
        .btn-dashboard-modern,
        .btn-edit-modern {
            flex: 1;
            justify-content: center;
        }
        .competition-content {
            padding: 16px;
        }
        .section-title {
            font-size: 12px !important;
            padding: 12px 16px;
        }
        .attendees-grid,
        .attendees-content,
        .students-grid {
            grid-template-columns: 1fr;
            padding: 16px;
        }
        
        .student-results {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .results-info {
            margin: 15px 16px 16px 16px;
            padding: 12px;
            font-size: 13px;
        }
        .results-grid {
            grid-template-columns: 1fr 1fr;
            padding: 16px;
        }
        .meta-grid {
            grid-template-columns: 1fr;
            padding: 16px;
        }
        .meta-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
    }
    @media (max-width: 480px) {
        .competition-title {
            font-size: 20px;
        }
        .section-title {
            font-size: 11px !important;
            padding: 10px 10px;
        }
        .attendees-grid,
        .attendees-content,
        .students-grid,
        .results-grid,
        .meta-grid {
            gap: 8px;
            padding: 8px;
        }
        
        .student-header {
            padding: 12px;
        }
        
        .student-result-item {
            padding: 8px 6px;
        }
        
        .student-result-item .result-number {
            font-size: 16px;
        }
        
        .student-result-item .result-label {
            font-size: 10px;
        }
        
        .results-info {
            margin: 10px 8px;
            padding: 10px;
            font-size: 12px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_single_competition', 'single_competition_shortcode');