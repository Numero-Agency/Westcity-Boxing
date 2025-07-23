<?php
/**
 * Referrals Dashboard Shortcode
 * Displays a table of referrals with filtering and search
 */

function wcb_referrals_dashboard_shortcode($atts) {
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
        'order' => 'DESC'
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
    <div class="<?php echo esc_attr($atts['class']); ?>" id="referrals-dashboard">
        
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
                <div class="stat-card this-month">
                    <div class="stat-number"><?php echo esc_html($stats['this_month']); ?></div>
                    <div class="stat-label">This Month</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Table Header -->
        <div class="table-header">
            <h2><span class="dashicons dashicons-admin-users"></span> Referrals</h2>
            <div class="header-actions">
                <a href="/referral-form" class="btn-primary">
                    <span class="dashicons dashicons-plus"></span> New Referral
                </a>
            </div>
        </div>
        

        
        <!-- Results Info -->
        <div class="results-info">
            <span class="results-count">
                Showing <?php echo esc_html($offset + 1); ?>-<?php echo esc_html(min($offset + $limit, $total_count)); ?> 
                of <?php echo esc_html($total_count); ?> referrals
            </span>
        </div>
        
        <!-- Referrals Table -->
        <div class="table-container">
            <?php if (!empty($referrals)): ?>
            <table class="referrals-table">
                <thead>
                    <tr>
                        <th>Young Person</th>
                        <th>Referral Date</th>
                        <th>Referrer/Agency</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $referral): ?>
                    <?php
                    $referral_id = $referral->ID;
                    $first_name = get_field('first_name', $referral_id);
                    $last_name = get_field('last_name', $referral_id);
                    $referral_date = get_field('referral_date', $referral_id);
                    $referrer_name = get_field('referrer_name', $referral_id);
                    $agency = get_field('agency', $referral_id);
                    $contact_phone = get_field('contact_phone', $referral_id);
                    $contact_email = get_field('contact_email', $referral_id);
                    $status = get_field('referral_status', $referral_id) ?: 'pending';
                    $date_of_birth = get_field('date_of_birth', $referral_id);
                    
                    // Calculate age if DOB is available
                    $age = '';
                    if ($date_of_birth) {
                        $dob = new DateTime($date_of_birth);
                        $now = new DateTime();
                        $age = $now->diff($dob)->y;
                    }
                    
                    $formatted_date = $referral_date ? date('M j, Y', strtotime($referral_date)) : 'Unknown';
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
                        <td class="status">
                            <span class="status-badge status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="<?php echo get_permalink($referral_id); ?>" class="btn-view" title="View Details">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <?php if (current_user_can('edit_posts')): ?>
                            <a href="<?php echo admin_url('post.php?post=' . $referral_id . '&action=edit'); ?>" class="btn-edit" title="Edit Referral">
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
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <h3>No referrals found</h3>
                <p>There are no referrals matching your criteria.</p>
                <a href="/referral-form" class="btn-primary">Create Your First Referral</a>
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
    .wcb-referrals-dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    /* Force override browser table defaults */
    .wcb-referrals-dashboard table {
        border: none !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }
    
    .wcb-referrals-dashboard th,
    .wcb-referrals-dashboard td {
        border: none !important;
        margin: 0 !important;
        padding: 15px !important;
        border-bottom: 1px solid #e1e1e1 !important;
    }
    
    .wcb-referrals-dashboard th {
        border-bottom: 1px solid #e1e1e1 !important;
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
        border-top: 4px solid #3498db;
    }
    
    .stat-card.pending {
        border-top-color: #f39c12;
    }
    
    .stat-card.processed {
        border-top-color: #27ae60;
    }
    
    .stat-card.this-month {
        border-top-color: #9b59b6;
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
        font-size: 24px;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-primary {
        background: #3498db;
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
        background: #2980b9;
        text-decoration: none;
        color: white;
    }
    

    
    .results-info {
        margin-bottom: 15px;
        color: #666;
        font-size: 14px;
        font-weight: 500;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        border: none;
    }
    
    .table-container table {
        border: none !important;
        border-collapse: collapse !important;
    }
    
    .referrals-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        border-spacing: 0;
        table-layout: auto;
        background: white;
    }
    
    .referrals-table th,
    .referrals-table td {
        padding: 15px;
        text-align: left;
        border: none;
        border-bottom: 1px solid #e1e1e1;
        vertical-align: middle;
    }
    
    .referrals-table th {
        background: #f8f9fa;
        font-weight: bold;
        color: #2c3e50;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: top;
    }
    
    .referrals-table td {
        background: white;
        color: #000000;
    }
    
    .referrals-table tr:hover {
        background: #f8f9fa;
    }
    
    .referrals-table tr:hover td {
        background: #f8f9fa;
    }
    
    .person-info strong {
        color: #2c3e50;
        font-size: 16px;
    }
    
    .person-age {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }
    
    .no-info {
        color: #999;
        font-style: italic;
        font-size: 14px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .status-reviewed {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-processed {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .status-contacted {
        background: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    .actions {
        display: flex;
        gap: 10px;
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
        padding: 60px 30px;
        color: #666;
    }
    
    .no-results-icon {
        margin-bottom: 20px;
    }
    
    .no-results-icon .dashicons {
        font-size: 48px;
        color: #ccc;
    }
    
    .no-results h3 {
        margin: 0 0 16px 0;
        font-size: 24px;
        color: #2c3e50;
    }
    
    .no-results p {
        margin: 0 0 24px 0;
        color: #666;
        font-size: 16px;
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
        border: 1px solid #ddd;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .page-link:hover {
        background: #f8f9fa;
    }
    
    .page-link.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .wcb-referrals-dashboard {
            padding: 10px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .table-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .referrals-table {
            min-width: 800px;
        }
        
        .referrals-table th,
        .referrals-table td {
            padding: 10px 8px;
            font-size: 14px;
        }
    }
    </style>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wcb_referrals_dashboard', 'wcb_referrals_dashboard_shortcode');

// Helper function to get referral statistics
function wcb_get_referral_stats() {
    $stats = [
        'total' => 0,
        'pending' => 0,
        'processed' => 0,
        'this_month' => 0
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
    
    // Get this month's referrals
    $this_month_query = new WP_Query([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'date_query' => [
            [
                'after' => date('Y-m-01'),
                'before' => date('Y-m-t'),
                'inclusive' => true
            ]
        ]
    ]);
    $stats['this_month'] = $this_month_query->found_posts;
    
    wp_reset_postdata();
    return $stats;
} 