<?php
/**
 * Referrals Dashboard Shortcode
 * Displays a table of referrals with filtering and search
 */

function wcb_referrals_dashboard_shortcode($atts) {
    // Add error handling to prevent 500 errors
    try {
        $atts = shortcode_atts([
            'limit' => 20,
            'show_stats' => 'true',
            'class' => 'wcb-referrals-dashboard'
        ], $atts);
    
    // Get pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = intval($atts['limit']);
    $offset = ($page - 1) * $limit;
    
     // Build query args
     $query_args = [
         'post_type' => 'referral',
         'posts_per_page' => $limit,
         'offset' => $offset,
         'post_status' => 'publish',
         'orderby' => 'date',
         'order' => 'DESC',
         'suppress_filters' => false,
         'no_found_rows' => false
     ];
    
    // Get referrals
    $referrals_query = new WP_Query($query_args);
    $referrals = $referrals_query->posts;
    $total_count = $referrals_query->found_posts;
    $total_pages = ceil($total_count / $limit);
    
    // Get statistics
    $stats = wcb_get_referral_stats();
    
    ob_start();
    ?>
    <div class="all-referrals-container">
        
        <?php if ($atts['show_stats'] === 'true'): ?>
        <!-- Statistics Cards -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo esc_html($stats['total']); ?></div>
                    <div class="stat-label">Total Referrals</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-number"><?php echo esc_html($stats['pending']); ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
                <div class="stat-card processed">
                    <div class="stat-number"><?php echo esc_html($stats['processed']); ?></div>
                    <div class="stat-label">Processed</div>
                </div>
                <div class="stat-card completed">
                    <div class="stat-number"><?php echo esc_html($stats['completed']); ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Table Header -->
        <div class="referrals-header">
            <div class="referrals-title-section">
                <h3><span class="dashicons dashicons-admin-users"></span> Referrals</h3>
                <span class="referrals-count"><?php echo $total_count; ?> referrals total</span>
            </div>
            <div class="referrals-filter-actions">
                <a href="/referral-form" class="btn-log-simple btn-log-referral">
                    <span class="dashicons dashicons-plus"></span> New Referral
                </a>
            </div>
        </div>
        

        
        <?php if (!empty($referrals)): ?>
        
        <div class="referrals-table-container">
            <table class="referrals-table" id="referrals-table">
                <thead>
                    <tr>
                        <th><span class="dashicons dashicons-admin-users"></span> Young Person</th>
                        <th><span class="dashicons dashicons-calendar-alt"></span> Referral Date</th>
                        <th><span class="dashicons dashicons-groups"></span> Referrer/Agency</th>
                        <th class="sortable-header" data-sort="status">
                            <span class="sort-link">
                                <span class="dashicons dashicons-info"></span> Status
                                <span class="sort-indicator dashicons dashicons-sort"></span>
                            </span>
                        </th>
                        <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $referral): ?>
                    <?php
                    $referral_id = $referral->ID;
                    
                     // Use safe field retrieval with fallbacks
                     if (function_exists('get_field')) {
                         $first_name = get_field('first_name', $referral_id);
                         $last_name = get_field('last_name', $referral_id);
                         $referral_date = get_field('referral_date', $referral_id);
                         $referrer_name = get_field('referrer_name', $referral_id);
                         $agency = get_field('agency', $referral_id);
                         $status = get_field('referral_status', $referral_id) ?: 'pending';
                         $date_of_birth = get_field('date_of_birth', $referral_id);
                     } else {
                         // Fallback to post meta if ACF is not available
                         $first_name = get_post_meta($referral_id, 'first_name', true);
                         $last_name = get_post_meta($referral_id, 'last_name', true);
                         $referral_date = get_post_meta($referral_id, 'referral_date', true);
                         $referrer_name = get_post_meta($referral_id, 'referrer_name', true);
                         $agency = get_post_meta($referral_id, 'agency', true);
                         $status = get_post_meta($referral_id, 'referral_status', true) ?: 'pending';
                         $date_of_birth = get_post_meta($referral_id, 'date_of_birth', true);
                     }
                    

                    
                    // Calculate age if DOB is available - with robust error handling
                    $age = '';
                    if ($date_of_birth && !empty(trim($date_of_birth))) {
                        try {
                            // Validate date format first
                            $date_string = trim($date_of_birth);
                            if (strtotime($date_string) !== false) {
                                $dob = new DateTime($date_string);
                                $now = new DateTime();
                                $age_diff = $now->diff($dob);
                                $age = $age_diff->y;
                            }
                        } catch (Exception $e) {
                            // Log error for debugging but don't break the page
                            error_log('Invalid date format in referral ' . $referral_id . ': ' . $date_of_birth);
                            $age = '';
                        }
                    }
                    
                    // Format referral date safely using the helper function
                    $formatted_date = 'Unknown';
                    
                    // Try referral_date field first
                    if ($referral_date && !empty(trim($referral_date))) {
                        $formatted_date = function_exists('wcb_format_date_for_display') ? 
                            wcb_format_date_for_display($referral_date) : 
                            date('d/m/Y', strtotime($referral_date));
                    } else {
                        // If still unknown, try alternative field names
                        $alternative_fields = ['date', 'submission_date', 'created_date', 'referral_submission_date'];
                        foreach ($alternative_fields as $field_name) {
                            $alt_date = function_exists('get_field') ? get_field($field_name, $referral_id) : get_post_meta($referral_id, $field_name, true);
                            if ($alt_date && !empty(trim($alt_date))) {
                                $formatted_date = function_exists('wcb_format_date_for_display') ? 
                                    wcb_format_date_for_display($alt_date) : 
                                    date('d/m/Y', strtotime($alt_date));
                                break;
                            }
                        }
                    }
                    
                    // If still unknown, use the post creation date as final fallback
                    if ($formatted_date === 'Unknown') {
                        $post_date = $referral->post_date;
                        if ($post_date && $post_date !== '0000-00-00 00:00:00') {
                            $formatted_date = function_exists('wcb_format_date_for_display') ? 
                                wcb_format_date_for_display($post_date) : 
                                date('d/m/Y', strtotime($post_date));
                        }
                    }
                    
                    // If STILL unknown (should never happen), show a helpful message
                    if ($formatted_date === 'Unknown') {
                        $formatted_date = 'Date not set';
                    }
                    

                    $full_name = trim($first_name . ' ' . $last_name);
                    $referrer_info = trim($referrer_name . ($agency ? ' (' . $agency . ')' : ''));
                    ?>
                    <tr>
                        <td class="young-person">
                            <div class="person-info">
                                <strong><?php echo esc_html($full_name); ?></strong>
                                <?php if ($age): ?>
                                    <div class="person-age">Age: <?php echo esc_html($age); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="referral-date"><?php echo esc_html($formatted_date); ?></td>
                        <td class="referrer-info">
                            <?php if ($referrer_info): ?>
                                <?php echo esc_html($referrer_info); ?>
                            <?php else: ?>
                                <span class="no-info">Not specified</span>
                            <?php endif; ?>
                        </td>
                        <td class="status" data-sort-value="<?php echo esc_attr($status); ?>">
                            <span class="status-badge status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </td>
                        <td class="referral-actions">
                            <div class="action-buttons">
                                <a href="<?php echo get_permalink($referral_id); ?>" class="btn-view" title="View Details">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                                <?php if (current_user_can('edit_posts')): ?>
                                <a href="<?php echo admin_url('post.php?post=' . $referral_id . '&action=edit'); ?>" class="btn-edit" title="Edit Referral">
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
        <div class="no-referrals">
            <div class="no-referrals-icon"><span class="dashicons dashicons-admin-users"></span></div>
            <h3>No referrals found</h3>
            <p>There are no referrals matching your criteria.</p>
            <a href="/referral-form" class="btn-create-referral">Create First Referral</a>
        </div>
        <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
    /* Modern Minimalistic Black & White Referrals Table - Matching Dashboard Style */
    .all-referrals-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: white;
        border: 1px solid #e5e5e5;
        overflow: hidden;
        margin-bottom: 40px;
    }
    
    /* Force override browser table defaults */
    .all-referrals-container table {
        border: none !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }
    
    .all-referrals-container th,
    .all-referrals-container td {
        border: none !important;
        margin: 0 !important;
        padding: 16px 20px !important;
        border-bottom: 1px solid #f1f1f1 !important;
    }
    
    .all-referrals-container th {
        border-bottom: 1px solid #e5e5e5 !important;
    }
    
    /* Statistics Section */
    .stats-section {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e5e5;
        background: #fafafa;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        border-top: 4px solid #3498db;
    }
    
    .stat-card.pending {
        border-top-color: #f39c12;
    }
    
    .stat-card.processed {
        border-top-color: #27ae60;
    }
    
    .stat-card.completed {
        border-top-color: #721c24;
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
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .referrals-header {
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
    
    .referrals-title-section h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    
    .referrals-title-section h3 .dashicons {
        font-size: 20px;
        color: white;
    }
    
    .referrals-count {
        font-size: 14px;
        color: white;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .referrals-filter-actions {
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
    
    .btn-log-referral {
        background: #4caf50;
        color: white !important;
    }
    
    .btn-log-referral:hover {
        background: #45a049;
        color: white !important;
        text-decoration: none;
    }
    
    .referrals-table-container {
        overflow-x: auto;
        background: white;
        border: none;
        border-radius: 0;
    }
    
    .referrals-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        font-size: 14px;
        min-width: 900px;
        border: none;
        border-spacing: 0;
        table-layout: auto;
    }
    
    .referrals-table th {
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
    
    .referrals-table th .dashicons {
        font-size: 16px;
        margin-right: 6px;
        vertical-align: middle;
        color: #666666;
    }
    
    /* Sortable Header Styles */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }
    
    .sortable-header:hover {
        background-color: #f0f0f0;
    }
    
    .sort-link {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        color: #000000;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        width: 100%;
    }
    
    .sort-indicator {
        margin-left: 8px;
        font-size: 14px;
        color: #666666;
        transition: color 0.2s ease;
    }
    
    .sortable-header:hover .sort-indicator {
        color: #000000;
    }
    
    .sortable-header.sort-asc .sort-indicator:before {
        content: "\f142"; /* dashicons-arrow-up-alt2 */
    }
    
    .sortable-header.sort-desc .sort-indicator:before {
        content: "\f140"; /* dashicons-arrow-down-alt2 */
    }
    
    .referrals-table td {
        padding: 16px 20px;
        border: none;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        color: #000000;
        background: white;
        text-align: left;
    }
    
    .referrals-table tr:hover {
        background: #fafafa;
    }
    
    .referrals-table tr:hover td {
        background: #fafafa;
    }
    
    /* Person Info Styling */
    .person-info strong {
        color: #000000;
        font-size: 16px;
        font-weight: 600;
    }
    
    .person-age {
        font-size: 12px;
        color: #666666;
        margin-top: 2px;
        font-style: italic;
    }
    
    .no-info {
        color: #999999;
        font-style: italic;
        font-size: 13px;
    }
    
    /* Status Badge Styling */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 12px;
        white-space: nowrap;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
        border-color: #856404;
    }
    
    .status-reviewed {
        background: #d4edda;
        color: #155724;
        border-color: #155724;
    }
    
    .status-processed {
        background: #d1ecf1;
        color: #0c5460;
        border-color: #0c5460;
    }
    
    .status-contacted {
        background: #e2e3e5;
        color: #383d41;
        border-color: #383d41;
    }
    
    .status-completed {
        background: #f8d7da;
        color: #721c24;
        border-color: #721c24;
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
    
    /* No Referrals State */
    .no-referrals {
        text-align: center;
        padding: 60px 30px;
        color: #666666;
        background: white;
    }
    
    .no-referrals-icon {
        margin-bottom: 20px;
    }
    
    .no-referrals-icon .dashicons {
        font-size: 48px;
        color: #666666;
    }
    
    .no-referrals h3 {
        margin: 0 0 16px 0;
        font-size: 24px;
        color: #000000;
        font-weight: 600;
    }
    
    .no-referrals p {
        margin: 0 0 24px 0;
        color: #666666;
        font-size: 16px;
    }
    
    .btn-create-referral {
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
    
    .btn-create-referral:hover {
        background: #333333;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
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
        .referrals-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .referrals-title-section h3 {
            font-size: 16px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .referrals-table th,
        .referrals-table td {
            padding: 12px 16px;
            font-size: 12px;
        }
        
        .action-buttons {
            gap: 6px;
        }
        
        .btn-view,
        .btn-edit {
            width: 30px;
            height: 30px;
        }
        
        .no-referrals h3 {
            font-size: 20px;
        }
        
        .no-referrals p {
            font-size: 14px;
        }
    }
    
    @media (max-width: 600px) {
        .referrals-table th:nth-child(3),
        .referrals-table td:nth-child(3) {
            display: none;
        }
        
        .referrals-table th,
        .referrals-table td {
            padding: 10px 12px;
        }
        
        .referrals-table th .dashicons {
            font-size: 14px;
            margin-right: 4px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .btn-log-simple {
            padding: 6px 10px;
            font-size: 11px;
        }
        
        .sort-link {
            font-size: 12px;
        }
        
        .sort-indicator {
            font-size: 12px;
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('referrals-table');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const sortableHeaders = table.querySelectorAll('.sortable-header');
        
        let currentSort = null;
        let currentOrder = 'asc';
        
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sortType = this.dataset.sort;
                
                // Reset all headers
                sortableHeaders.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                    const indicator = h.querySelector('.sort-indicator');
                    indicator.className = 'sort-indicator dashicons dashicons-sort';
                });
                
                // Determine sort order
                if (currentSort === sortType) {
                    currentOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    currentOrder = 'asc';
                }
                
                currentSort = sortType;
                
                // Update header appearance
                this.classList.add('sort-' + currentOrder);
                const indicator = this.querySelector('.sort-indicator');
                indicator.className = 'sort-indicator dashicons dashicons-arrow-' + 
                    (currentOrder === 'asc' ? 'up' : 'down') + '-alt2';
                
                // Sort the table
                sortTable(sortType, currentOrder);
            });
        });
        
        function sortTable(sortType, order) {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                let aValue, bValue;
                
                if (sortType === 'status') {
                    aValue = a.querySelector('.status').dataset.sortValue || '';
                    bValue = b.querySelector('.status').dataset.sortValue || '';
                }
                
                // Handle sorting for different data types
                if (sortType === 'status') {
                    // Define status order for proper sorting
                    const statusOrder = {
                        'pending': 1,
                        'reviewed': 2, 
                        'processed': 3,
                        'contacted': 4,
                        'completed': 5
                    };
                    
                    aValue = statusOrder[aValue] || 999;
                    bValue = statusOrder[bValue] || 999;
                }
                
                if (order === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
            
            // Clear tbody and re-append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }
    });
    </script>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
        
    } catch (Exception $e) {
        // Return error message instead of crashing the page
        error_log('WCB Referrals Dashboard Error: ' . $e->getMessage());
        return '<div class="wcb-error-message">
            <p><strong>Error loading referrals dashboard:</strong> ' . esc_html($e->getMessage()) . '</p>
            <p>Please check the error logs or contact support if this persists.</p>
        </div>';
    }
}
add_shortcode('wcb_referrals_dashboard', 'wcb_referrals_dashboard_shortcode');

// Helper function to get referral statistics
function wcb_get_referral_stats() {
    try {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'processed' => 0,
            'completed' => 0
        ];
    
    // Get total referrals
    $total_query = new WP_Query([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $stats['total'] = $total_query->found_posts;
    
    // Get pending referrals
    $pending_query = new WP_Query([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'referral_status',
                'value' => 'pending',
                'compare' => '='
            ],
            [
                'key' => 'referral_status',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ]);
    $stats['pending'] = $pending_query->found_posts;
    
    // Get processed referrals
    $processed_query = new WP_Query([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'referral_status',
                'value' => ['processed', 'contacted', 'reviewed'],
                'compare' => 'IN'
            ]
        ]
    ]);
    $stats['processed'] = $processed_query->found_posts;
    
    // Get completed referrals
    $completed_query = new WP_Query([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'referral_status',
                'value' => 'completed',
                'compare' => '='
            ]
        ]
    ]);
    $stats['completed'] = $completed_query->found_posts;
    
        wp_reset_postdata();
        return $stats;
        
    } catch (Exception $e) {
        error_log('WCB Referral Stats Error: ' . $e->getMessage());
        return [
            'total' => 0,
            'pending' => 0,
            'processed' => 0,
            'completed' => 0
        ];
    }
} 