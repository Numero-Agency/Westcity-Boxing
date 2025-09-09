<?php
/**
 * Competitions Table Shortcode (ACF Post Type Version)
 * Displays a table of competitions with filtering and search, using the 'competition' post type and ACF fields
 */

function wcb_competitions_table_shortcode($atts) {
    $atts = shortcode_atts([
        'limit' => 20,
        'show_search' => 'true',
        'show_filters' => 'true',
        'show_stats' => 'true',
        'class' => 'wcb-competitions-table'
    ], $atts);
    
    // Get filter parameters
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = intval($atts['limit']);
    $offset = ($page - 1) * $limit;

    // Build WP_Query args
    $meta_query = [];
    if ($date_from) {
        $meta_query[] = [
            'key' => 'event_date',
            'value' => $date_from,
            'compare' => '>=',
            'type' => 'DATE',
        ];
    }
    if ($date_to) {
        $meta_query[] = [
            'key' => 'event_date',
            'value' => $date_to,
            'compare' => '<=',
            'type' => 'DATE',
        ];
    }
    if ($search) {
        $s = $search;
    } else {
        $s = '';
    }

    $query_args = [
        'post_type' => 'competition',
        'posts_per_page' => $limit,
        'offset' => $offset,
        'post_status' => 'publish',
        'orderby' => 'meta_value',
        'meta_key' => 'event_date',
        'order' => 'DESC',
        's' => $s,
    ];
    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }

    $competitions_query = new WP_Query($query_args);
    $competitions = $competitions_query->posts;
    $total_count = $competitions_query->found_posts;
    $total_pages = ceil($total_count / $limit);

    ob_start();
    ?>
    <div class="all-competitions-container">
        <!-- Table Header -->
        <div class="competitions-header">
            <div class="competitions-title-section">
                <h3><span class="dashicons dashicons-awards"></span> Competitions</h3>
                <span class="competitions-count"><?php echo count($competitions); ?> competitions total</span>
            </div>
            <div class="competitions-filter-actions">
                <a href="/log-competition" class="btn-log-simple btn-log-competition">
                    <span class="dashicons dashicons-plus"></span> Log Competition
                </a>
            </div>
        </div>
        <?php if ($atts['show_search'] === 'true' || $atts['show_filters'] === 'true'): ?>
        <!-- Search and Filters -->
        <div class="competitions-filters">
            <form method="get" class="competitions-filters-form">
                <?php if ($atts['show_search'] === 'true'): ?>
                <div class="filter-search">
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" 
                           placeholder="Search competitions..." class="filter-input">
                    <button type="submit" class="filter-submit">
                        <span class="dashicons dashicons-search"></span>
                    </button>
                </div>
                <?php endif; ?>
                <?php if ($atts['show_filters'] === 'true'): ?>
                <div class="filter-dates">
                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" 
                           class="filter-input">
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" 
                           class="filter-input">
                    <button type="submit" class="filter-submit">Filter</button>
                    <?php if ($search || $date_from || $date_to): ?>
                    <a href="?" class="filter-clear">Clear</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($competitions)): ?>
        
        <div class="competitions-table-container">
            <table class="competitions-table" id="competitions-table">
                <thead>
                    <tr>
                        <th><span class="dashicons dashicons-awards"></span> Event Name</th>
                        <th><span class="dashicons dashicons-calendar-alt"></span> Date</th>
                        <th><span class="dashicons dashicons-location"></span> Location</th>
                        <th><span class="dashicons dashicons-admin-users"></span> Student</th>
                        <th><span class="dashicons dashicons-chart-bar"></span> Results</th>
                        <th><span class="dashicons dashicons-star-filled"></span> Highlights</th>
                        <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($competitions as $competition): ?>
                    <?php
                    $event_name = get_field('event_name', $competition->ID);
                    $event_date = get_field('event_date', $competition->ID);
                    $where_was_it_hosted = get_field('where_was_it_hosted', $competition->ID);
                    
                    // Get both new and legacy student fields
                    $students_involved = get_field('students_involved', $competition->ID) ?: []; // New multi-student format
                    $student_detailed_results = get_field('student_detailed_results', $competition->ID) ?: [];
                    $student_involved = get_field('student_involved', $competition->ID); // Legacy single student format
                    
                    $who_else_attended = get_field('who_else_attended', $competition->ID);
                    $results_wins = get_field('results_wins', $competition->ID);
                    $results_lost = get_field('results_lost', $competition->ID);
                    $highlights = get_field('highlights', $competition->ID);
                    // Use consistent date formatting helper (same as dashboard)
                    if (function_exists('wcb_format_date_for_display')) {
                        $formatted_date = wcb_format_date_for_display($event_date);
                    } else {
                        $formatted_date = $event_date ? date('d/m/Y', strtotime($event_date)) : 'Unknown';
                    }
                    
                    // Debug student field data (only when debug parameter is set)
                    if (current_user_can('administrator') && isset($_GET['debug'])) {
                        error_log('=== COMPETITION DEBUG ID: ' . $competition->ID . ' ===');
                        error_log('Student Involved Raw: ' . print_r($student_involved, true));
                        error_log('Student Involved Type: ' . gettype($student_involved));
                    }
                    
                    // Process student data using same logic as single competition page
                    $processed_students = [];
                    $student_display_names = [];
                    
                    // Handle new multi-student format first
                    if (!empty($students_involved) && !empty($student_detailed_results)) {
                        foreach ($student_detailed_results as $result) {
                            if (isset($result['student_id'])) {
                                $student_user = get_userdata($result['student_id']);
                                if ($student_user) {
                                    $processed_students[] = $student_user;
                                    $student_display_names[] = $student_user->display_name;
                                }
                            }
                        }
                    } 
                    // Handle legacy single-student format
                    elseif (!empty($student_involved)) {
                        $student_user = null;
                        if (is_array($student_involved) && isset($student_involved['display_name'])) {
                            $student_user = (object) $student_involved;
                        } elseif (is_object($student_involved) && isset($student_involved->display_name)) {
                            $student_user = $student_involved;
                        } elseif (is_numeric($student_involved) && $student_involved > 0) {
                            $student_user = get_userdata($student_involved);
                        }
                        
                        if ($student_user) {
                            $processed_students[] = $student_user;
                            $student_display_names[] = $student_user->display_name;
                        }
                    }
                    
                    // Create display string for students
                    if (!empty($student_display_names)) {
                        if (count($student_display_names) === 1) {
                            $student_display = $student_display_names[0];
                        } else {
                            $student_display = implode(', ', array_slice($student_display_names, 0, 2));
                            if (count($student_display_names) > 2) {
                                $student_display .= ' +' . (count($student_display_names) - 2) . ' more';
                            }
                        }
                    } else {
                        $student_display = '';
                    }
                    
                    // Debug info
                    if (current_user_can('administrator') && isset($_GET['debug'])) {
                        error_log('=== COMPETITION DEBUG ID: ' . $competition->ID . ' ===');
                        error_log('Students Involved (new): ' . print_r($students_involved, true));
                        error_log('Student Detailed Results: ' . print_r($student_detailed_results, true));
                        error_log('Student Involved (legacy): ' . print_r($student_involved, true));
                        error_log('Final Student Display: ' . $student_display);
                        error_log('Processed Students Count: ' . count($processed_students));
                    }
                    ?>
                    <tr>
                        <td class="event-name">
                            <a href="<?php echo get_permalink($competition->ID); ?>" class="competition-link">
                                <?php echo esc_html($event_name ?: get_the_title($competition->ID)); ?>
                            </a>
                        </td>
                        <td class="event-date"><?php echo esc_html($formatted_date); ?></td>
                        <td class="event-location"><?php echo esc_html($where_was_it_hosted); ?></td>
                        <td class="student-involved">
                            <?php if (!empty($student_display)): ?>
                                <div class="student-info">
                                    <span class="student-name"><?php echo esc_html($student_display); ?></span>
                                    <?php if (count($processed_students) === 1 && isset($processed_students[0]->user_email)): ?>
                                        <div class="student-email"><?php echo esc_html($processed_students[0]->user_email); ?></div>
                                    <?php elseif (count($processed_students) > 1): ?>
                                        <div class="student-count"><?php echo count($processed_students); ?> students</div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="no-student">No students specified</span>
                            <?php endif; ?>
                            <?php if (current_user_can('administrator') && isset($_GET['debug'])): ?>
                                <br><small style="color: #999; font-size: 10px;">
                                    [ID: <?php echo $competition->ID; ?>] 
                                    [New: <?php echo !empty($students_involved) ? count($students_involved) : '0'; ?>] 
                                    [Legacy: <?php echo !empty($student_involved) ? 'Yes' : 'No'; ?>]
                                    [Display: "<?php echo $student_display; ?>"]
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="results">
                            <span class="wins"><?php echo esc_html($results_wins); ?>W</span>
                            <span class="losses"><?php echo esc_html($results_lost); ?>L</span>
                        </td>
                        <td class="highlights"><?php echo esc_html($highlights ?: 'No highlights recorded'); ?></td>
                        <td class="competition-actions">
                            <div class="action-buttons">
                                <a href="<?php echo get_permalink($competition->ID); ?>" class="btn-view" title="View Details">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                                <?php if (current_user_can('edit_posts') || get_current_user_id() == $competition->post_author): ?>
                                <a href="<?php echo admin_url('post.php?post=' . $competition->ID . '&action=edit'); ?>" class="btn-edit" title="Edit Competition">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
        <div class="no-competitions">
            <div class="no-competitions-icon"><span class="dashicons dashicons-awards"></span></div>
            <h3>No competitions found</h3>
            <p>There are no competitions matching your criteria.</p>
            <a href="/log-competition" class="btn-create-competition">Log First Competition</a>
        </div>
        <?php endif; ?>
        </div>
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    <style>
    /* Modern Minimalistic Black & White Competitions Table - Matching Dashboard Style */
    .all-competitions-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: white;
        border: 1px solid #e5e5e5;
        overflow: hidden;
        margin-bottom: 40px;
    }
    
    /* Force override browser table defaults */
    .all-competitions-container table {
        border: none !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }
    
    .all-competitions-container th,
    .all-competitions-container td {
        border: none !important;
        margin: 0 !important;
        padding: 16px 20px !important;
        border-bottom: 1px solid #f1f1f1 !important;
    }
    
    .all-competitions-container th {
        border-bottom: 1px solid #e5e5e5 !important;
    }
    
    .competitions-header {
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
    
    .competitions-title-section h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    
    .competitions-title-section h3 .dashicons {
        font-size: 20px;
        color: white;
    }
    
    .competitions-count {
        font-size: 14px;
        color: white;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .competitions-filter-actions {
        display: flex;
        gap: 12px;
        align-items: center;
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
    
    .btn-log-competition {
        background: #4caf50;
        color: white !important;
    }
    
    .btn-log-competition:hover {
        background: #45a049;
        color: white !important;
        text-decoration: none;
    }
    
    .competitions-table-container {
        overflow-x: auto;
        background: white;
        border: none;
        border-radius: 0;
    }
    
    .competitions-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        font-size: 14px;
        min-width: 900px;
        border: none;
        border-spacing: 0;
        table-layout: auto;
    }
    
    .competitions-table th {
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
    
    .competitions-table th .dashicons {
        font-size: 16px;
        margin-right: 6px;
        vertical-align: middle;
        color: #666666;
    }
    
    .competitions-table td {
        padding: 16px 20px;
        border: none;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        color: #000000;
        background: white;
        text-align: left;
    }
    
    .competitions-table tr:hover {
        background: #fafafa;
    }
    
    .competitions-table tr:hover td {
        background: #fafafa;
    }
    
    /* Highlights column styling */
    .competitions-table td.highlights {
        max-width: 220px;
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.4;
    }
    
    /* Competition specific styling */
    .competition-link {
        font-weight: 600;
        color: #000000 !important;
        text-decoration: none;
    }
    
    .competition-link:hover {
        color: #666666 !important;
        text-decoration: none;
    }
    
    /* Student column styling */
    .student-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .student-name {
        font-weight: 600;
        color: #000000;
        font-size: 14px;
    }
    
    .student-email {
        font-size: 12px;
        color: #666666;
        font-style: italic;
    }
    
    .student-count {
        font-size: 12px;
        color: #007cba;
        font-weight: 500;
        font-style: italic;
    }
    
    .no-student {
        color: #999999;
        font-style: italic;
        font-size: 13px;
    }
    
    .wins {
        color: #27ae60;
        font-weight: 600;
        margin-right: 8px;
    }
    
    .losses {
        color: #e74c3c;
        font-weight: 600;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        align-items: center;
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
    
    /* No Competitions State */
    .no-competitions {
        text-align: center;
        padding: 60px 30px;
        color: #666666;
        background: white;
    }
    
    .no-competitions-icon {
        margin-bottom: 20px;
    }
    
    .no-competitions-icon .dashicons {
        font-size: 48px;
        color: #666666;
    }
    
    .no-competitions h3 {
        margin: 0 0 16px 0;
        font-size: 24px;
        color: #000000;
        font-weight: 600;
    }
    
    .no-competitions p {
        margin: 0 0 24px 0;
        color: #666666;
        font-size: 16px;
    }
    
    .btn-create-competition {
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
    
    .btn-create-competition:hover {
        background: #333333;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    /* Filters */
    .competitions-filters {
        background: #f8f9fa;
        padding: 16px 24px;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .competitions-filters-form {
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-search,
    .filter-dates {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .filter-input {
        padding: 8px 12px;
        border: 1px solid #e5e5e5;
        background: white;
        color: #000000;
        font-size: 14px;
        outline: none;
        border-radius: 4px;
    }
    
    .filter-submit {
        padding: 8px 16px;
        background: #000000;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .filter-submit:hover {
        background: #333333;
    }
    
    .filter-clear {
        color: #666666;
        text-decoration: none;
        padding: 8px 12px;
        font-size: 14px;
    }
    
    .filter-clear:hover {
        color: #000000;
        text-decoration: none;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
        padding: 20px;
    }
    
    .page-link {
        padding: 10px 15px;
        background: white;
        color: #000000;
        text-decoration: none;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .page-link:hover,
    .page-link.active {
        background: #000000;
        color: white;
        border-color: #000000;
        text-decoration: none;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .competitions-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .competitions-title-section h3 {
            font-size: 16px;
        }
        
        .competitions-filters-form {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }
        
        .filter-search,
        .filter-dates {
            width: 100%;
            justify-content: stretch;
        }
        
        .competitions-table th,
        .competitions-table td {
            padding: 12px 16px;
            font-size: 12px;
        }
        
        .competitions-table td.highlights {
            max-width: 180px;
            font-size: 11px;
        }
        
        .action-buttons {
            gap: 6px;
        }
        
        .btn-view,
        .btn-edit {
            width: 30px;
            height: 30px;
        }
        
        .no-competitions h3 {
            font-size: 20px;
        }
        
        .no-competitions p {
            font-size: 14px;
        }
    }
    
    @media (max-width: 600px) {
        .competitions-table th:nth-child(3),
        .competitions-table td:nth-child(3),
        .competitions-table th:nth-child(6),
        .competitions-table td:nth-child(6) {
            display: none;
        }
        
        .competitions-table th,
        .competitions-table td {
            padding: 10px 12px;
        }
        
        .competitions-table th .dashicons {
            font-size: 14px;
            margin-right: 4px;
        }
        
        .competitions-filters-form {
            gap: 8px;
        }
        
        .btn-log-simple {
            padding: 6px 10px;
            font-size: 11px;
        }
        
        .action-buttons {
            gap: 4px;
        }
        
        .btn-view,
        .btn-edit {
            width: 28px;
            height: 28px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_competitions_table', 'wcb_competitions_table_shortcode');