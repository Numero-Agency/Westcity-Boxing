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
    $student_involved = get_field('student_involved', $competition_id);
    $who_else_attended = get_field('who_else_attended', $competition_id);
    $results_wins = get_field('results_wins', $competition_id) ?: 0;
    $results_lost = get_field('results_lost', $competition_id) ?: 0;
    $highlights = get_field('highlights', $competition_id);
    $creator = get_user_by('ID', $competition_post->post_author);
    $creator_name = $creator ? $creator->display_name : 'Unknown';
    $created_date = $competition_post->post_date;
    $modified_date = $competition_post->post_modified;
    $formatted_date = $event_date ? date('l, F j, Y', strtotime($event_date)) : 'Unknown Date';
    $total_matches = $results_wins + $results_lost;
    $win_percentage = $total_matches > 0 ? round(($results_wins / $total_matches) * 100, 1) : 0;
    $student_user = $student_involved ? get_user_by('ID', $student_involved) : null;
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
        <!-- Student & Attendees Section -->
        <div class="competition-section attendees-section">
            <h3 class="section-title"><span class="dashicons dashicons-admin-users"></span> Student & Attendees</h3>
            <div class="attendees-grid">
                <div class="attendee-card student-card">
                    <div class="attendee-avatar">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="attendee-info">
                        <div class="attendee-label">Student Involved</div>
                        <div class="attendee-name"><?php echo $student_user ? esc_html($student_user->display_name) : 'Not specified'; ?></div>
                        <?php if ($student_user): ?>
                        <div class="attendee-email"><?php echo esc_html($student_user->user_email); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="attendee-card others-card">
                    <div class="attendee-avatar">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="attendee-info">
                        <div class="attendee-label">Who Else Attended</div>
                        <div class="attendee-name"><?php echo $who_else_attended ? esc_html($who_else_attended) : 'Not specified'; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Results Section -->
        <div class="competition-section results-section">
            <h3 class="section-title"><span class="dashicons dashicons-chart-bar"></span> Results</h3>
            <div class="results-grid">
                <div class="result-card win-card">
                    <div class="result-number"><?php echo esc_html($results_wins); ?></div>
                    <div class="result-label">Wins</div>
                </div>
                <div class="result-card loss-card">
                    <div class="result-number"><?php echo esc_html($results_lost); ?></div>
                    <div class="result-label">Losses</div>
                </div>
                <div class="result-card total-card">
                    <div class="result-number"><?php echo esc_html($total_matches); ?></div>
                    <div class="result-label">Total Matches</div>
                </div>
                <div class="result-card percentage-card">
                    <div class="result-number"><?php echo esc_html($win_percentage); ?>%</div>
                    <div class="result-label">Win Rate</div>
                </div>
            </div>
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
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background-color 0.3s ease;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: white;
        color: #000000;
        border: 1px solid #e5e5e5;
    }
    .btn-dashboard-modern:hover,
    .btn-edit-modern:hover {
        background: #f8f9fa;
        color: #000000;
        text-decoration: none;
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
    .attendees-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        padding: 20px;
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
        .attendees-grid {
            grid-template-columns: 1fr;
            padding: 16px;
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
        .results-grid,
        .meta-grid {
            gap: 8px;
            padding: 8px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_single_competition', 'single_competition_shortcode');