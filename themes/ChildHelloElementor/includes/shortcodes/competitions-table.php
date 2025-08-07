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
    <div class="<?php echo esc_attr($atts['class']); ?>" id="competitions-table">
        <!-- Table Header -->
        <div class="table-header">
            <h2><span class="dashicons dashicons-awards"></span> Competitions</h2>
            <div class="header-actions">
                <a href="/log-competition" class="btn-primary">
                    <span class="dashicons dashicons-plus"></span> Log Competition
                </a>
            </div>
        </div>
        <?php if ($atts['show_search'] === 'true' || $atts['show_filters'] === 'true'): ?>
        <!-- Search and Filters -->
        <div class="table-filters">
            <form method="get" class="filters-form">
                <?php if ($atts['show_search'] === 'true'): ?>
                <div class="search-field">
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" 
                           placeholder="Search competitions..." class="search-input">
                    <button type="submit" class="search-btn">
                        <span class="dashicons dashicons-search"></span>
                    </button>
                </div>
                <?php endif; ?>
                <?php if ($atts['show_filters'] === 'true'): ?>
                <div class="date-filters">
                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" 
                           placeholder="From date" class="date-input">
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" 
                           placeholder="To date" class="date-input">
                    <button type="submit" class="filter-btn">Filter</button>
                    <?php if ($search || $date_from || $date_to): ?>
                    <a href="?" class="clear-filters">Clear</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>
        <!-- Results Info -->
        <div class="results-info">
            <span class="results-count">
                Showing <?php echo esc_html($offset + 1); ?>-<?php echo esc_html(min($offset + $limit, $total_count)); ?> 
                of <?php echo esc_html($total_count); ?> competitions
            </span>
        </div>
        <!-- Competitions Table -->
        <div class="table-container">
            <?php if (!empty($competitions)): ?>
            <table class="competitions-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Student</th>
                        <th>Results</th>
                        <th>Highlights</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($competitions as $competition): ?>
                    <?php
                    $event_name = get_field('event_name', $competition->ID);
                    $event_date = get_field('event_date', $competition->ID);
                    $where_was_it_hosted = get_field('where_was_it_hosted', $competition->ID);
                    $student_involved = get_field('student_involved', $competition->ID);
                    $who_else_attended = get_field('who_else_attended', $competition->ID);
                    $results_wins = get_field('results_wins', $competition->ID);
                    $results_lost = get_field('results_lost', $competition->ID);
                    $highlights = get_field('highlights', $competition->ID);
                    $formatted_date = $event_date ? date('M j, Y', strtotime($event_date)) : 'Unknown';
                    
                    // Debug student field data (only when debug parameter is set)
                    if (current_user_can('administrator') && isset($_GET['debug'])) {
                        error_log('=== COMPETITION DEBUG ID: ' . $competition->ID . ' ===');
                        error_log('Student Involved Raw: ' . print_r($student_involved, true));
                        error_log('Student Involved Type: ' . gettype($student_involved));
                    }
                    
                    // Handle ACF User field format
                    $student_user_data = null;
                    $student_name = 'Not specified';
                    
                    if (!empty($student_involved)) {
                        // Handle array format (ACF User field returns associative array)
                        if (is_array($student_involved) && isset($student_involved['display_name'])) {
                            $student_name = $student_involved['display_name'];
                            $student_user_data = (object) $student_involved;
                        }
                        // Handle object format
                        elseif (is_object($student_involved) && isset($student_involved->display_name)) {
                            $student_user_data = $student_involved;
                            $student_name = $student_involved->display_name;
                        }
                        // Fallback: if it's just an ID (legacy or manual entry)
                        elseif (is_numeric($student_involved) && $student_involved > 0) {
                            $student_user_data = get_userdata($student_involved);
                            if ($student_user_data) {
                                $student_name = $student_user_data->display_name;
                            }
                        }
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
                        <td class="student-involved" style="color: #333 !important;">
                            <?php echo esc_html($student_name); ?>
                            <?php if (current_user_can('administrator') && isset($_GET['debug'])): ?>
                                <br><small style="color: #999; font-size: 10px;">
                                    [ID: <?php echo $competition->ID; ?>] [Type: <?php echo gettype($student_involved); ?>]
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="results">
                            <span class="wins"><?php echo esc_html($results_wins); ?>W</span>
                            <span class="losses"><?php echo esc_html($results_lost); ?>L</span>
                        </td>
                        <td class="highlights"><?php echo esc_html($highlights); ?></td>
                        <td class="actions">
                            <a href="<?php echo get_permalink($competition->ID); ?>" class="btn-view" title="View Details">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <?php if (current_user_can('edit_posts') || get_current_user_id() == $competition->post_author): ?>
                            <a href="<?php echo admin_url('post.php?post=' . $competition->ID . '&action=edit'); ?>" class="btn-edit" title="Edit Competition">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">
                    <span class="dashicons dashicons-awards"></span>
                </div>
                <h3>No competitions found</h3>
                <p>There are no competitions matching your criteria.</p>
                <a href="/log-competition" class="btn-primary">Log Your First Competition</a>
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
    /* Override browser default table styling */
    .competitions-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }

    .competitions-table,
    .competitions-table th,
    .competitions-table td {
        border: none !important;
        border-bottom: 1px solid #e5e5e5 !important;
        box-shadow: none !important;
        background-clip: padding-box !important;
    }

    .competitions-table th,
    .competitions-table td {
        padding: 12px !important;
        vertical-align: middle !important;
        text-align: left !important;
        font-size: 14px !important;
        color: #333 !important;
    }

    .competitions-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .competitions-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    .competitions-table tbody tr:nth-child(even) {
        background-color: #ffffff !important;
    }

    .competitions-table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    /* Competitions table specific styles */
    .wcb-competitions-table {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .stats-section {
        margin-bottom: 30px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        border-top: 4px solid #e74c3c;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e1e1e1;
    }
    
    .table-header h2 {
        margin: 0;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-primary {
        background: #e74c3c;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: #c0392b;
    }
    
    .table-filters {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .filters-form {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .search-field {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .search-input {
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 6px;
        width: 250px;
    }
    
    .search-btn {
        background: #3498db;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .date-filters {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .date-input {
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 6px;
    }
    
    .filter-btn {
        background: #27ae60;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .clear-filters {
        color: #e74c3c;
        text-decoration: none;
        padding: 10px 15px;
    }
    
    .results-info {
        margin-bottom: 15px;
        color: #666;
        font-size: 14px;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .event-name {
        position: relative;
    }
    
    .competition-link {
        font-weight: bold;
        color: #2c3e50 !important;
        text-decoration: none;
    }
    
    .competition-link:hover {
        color: #e74c3c !important;
        text-decoration: none;
    }
    
    .has-highlights {
        position: relative;
        display: inline-block;
        margin-left: 5px;
        color: #f39c12;
    }
    
    .has-highlights .tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #2c3e50;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    
    .has-highlights:hover .tooltip {
        opacity: 1;
    }
    
    .results-summary {
        display: flex;
        gap: 10px;
    }
    
    .wins {
        color: #27ae60;
        font-weight: bold;
    }
    
    .losses {
        color: #e74c3c;
        font-weight: bold;
    }
    
    .win-rate-display {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .win-rate-bar {
        width: 50px;
        height: 8px;
        background: #e1e1e1;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .win-rate-fill {
        height: 100%;
        background: #27ae60;
        transition: width 0.3s ease;
    }
    
    .actions {
        display: flex;
        gap: 5px;
    }
    
    .btn-view,
    .btn-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn-view {
        background: #3498db;
        color: white;
    }
    
    .btn-view:hover {
        background: #2980b9;
        color: white;
        text-decoration: none;
    }
    
    .btn-edit {
        background: #f39c12;
        color: white;
    }
    
    .btn-edit:hover {
        background: #e67e22;
        color: white;
        text-decoration: none;
    }
    
    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .no-results-icon {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .no-results h3 {
        margin: 0 0 10px 0;
        color: #2c3e50;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }
    
    .page-link {
        padding: 10px 15px;
        background: white;
        color: #2c3e50;
        text-decoration: none;
        border: 2px solid #ddd;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .page-link:hover,
    .page-link.active {
        background: #e74c3c;
        color: white;
        border-color: #e74c3c;
    }
    
    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 20px;
        border-radius: 6px;
        border: 1px solid #f5c6cb;
        text-align: center;
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .table-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .filters-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-field,
        .date-filters {
            width: 100%;
            justify-content: stretch;
        }
        
        .search-input {
            width: 100%;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .competitions-table {
            min-width: 600px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .competitions-table th,
        .competitions-table td {
            padding: 10px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_competitions_table', 'wcb_competitions_table_shortcode');