<?php
// Dashboard Students Component - Enhanced Student Management Interface

/**
 * Main Students Dashboard Shortcode
 * Displays comprehensive student management interface
 */
function dashboard_students_shortcode() {
    $current_user = wp_get_current_user();
    
    // Check permissions - only coaches and admins can access
    if (!current_user_can('edit_posts') && !in_array('coach', $current_user->roles)) {
        return '<div class="access-denied">You do not have permission to access the student dashboard.</div>';
    }

    // Get current filter from URL (default to active members)
    $current_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'active';
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    
    ob_start();
    ?>
    <div id="wcb-students-dashboard" class="students-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h2>
                <span class="dashicons dashicons-groups"></span>
                Student Management Dashboard
            </h2>
            <p class="dashboard-subtitle">Manage students, view profiles, and track membership status</p>
        </div>

        <!-- Quick Stats Cards -->
        <div class="student-stats-grid">
            <?php echo wcb_get_student_stats_cards(); ?>
        </div>

        <!-- Search and Filter Controls -->
        <div class="dashboard-controls">
            <div class="search-section">
                <div class="search-container">
                    <input type="text" 
                           id="wcb-student-search" 
                           class="student-search-input" 
                           placeholder="Search students by name or email..." 
                           value="<?php echo esc_attr($search_term); ?>">
                    <button id="wcb-student-search-btn" class="search-btn">
                        <span class="dashicons dashicons-search"></span>
                        Search
                    </button>
                    <button id="wcb-clear-search" class="clear-btn">
                        <span class="dashicons dashicons-no"></span>
                        Clear
                    </button>
                </div>
                
                <!-- Live Search Results -->
                <div id="wcb-student-search-results" class="search-results-container" style="display: none;"></div>
            </div>

            <!-- Active Members Only -->
            <div class="active-members-header">
                <h3>
                    <span class="dashicons dashicons-groups"></span>
                    Active Members (Program Groups)
                    <span class="member-count"><?php echo $stats['active']; ?></span>
                </h3>
                <p class="subtitle">Showing members from the 7 defined program groups</p>
            </div>
        </div>

        <!-- Students Table Container -->
        <div class="students-table-container">
            <div class="table-header">
                <h3 id="table-title">
                    Active Members
                </h3>
                <div class="table-controls">
                    <label for="per-page-select">Show:</label>
                    <select id="per-page-select" class="per-page-select">
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                    <button id="export-students" class="export-btn">
                        <span class="dashicons dashicons-download"></span>
                        Export
                    </button>
                </div>
            </div>

            <!-- Dynamic Table Content -->
            <div id="wcb-students-table-content">
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading students...</p>
                </div>
            </div>

            <!-- Pagination Container -->
            <div id="wcb-students-pagination" class="pagination-container"></div>
        </div>

        <!-- Student Profile Overlay Container -->
        <div id="wcb-student-profile-container" class="profile-overlay-container" style="display: none;">
            <!-- Dynamic content loaded here -->
        </div>

        <!-- Bulk Actions Panel (Future Enhancement) -->
        <div id="bulk-actions-panel" class="bulk-actions-panel" style="display: none;">
            <div class="bulk-actions-content">
                <h4>Bulk Actions</h4>
                <select id="bulk-action-select">
                    <option value="">Select Action</option>
                    <option value="export">Export Selected</option>
                    <option value="move-to-waitlist">Move to Waitlist</option>
                    <option value="activate">Activate Memberships</option>
                </select>
                <button id="apply-bulk-action" class="apply-btn">Apply</button>
                <button id="cancel-bulk-action" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <?php echo wcb_students_dashboard_styles(); ?>
    <?php echo wcb_students_dashboard_scripts(); ?>
    <?php
    return ob_get_clean();
}

/**
 * Generate Quick Stats Cards for Student Dashboard
 */
function wcb_get_student_stats_cards() {
    global $wpdb;
    
    // Check if MemberPress tables exist
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return '<div class="stats-error">MemberPress data not available</div>';
    }

    // Get counts using the same logic as the filtering system
    $stats = wcb_get_membership_status_counts();
    
    ob_start();
    ?>
    <div class="stat-card active-card" data-status="active">
        <div class="stat-icon">
            <span class="dashicons dashicons-yes-alt"></span>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['active']); ?></h3>
            <p>Active Members</p>
            <span class="stat-description">Currently enrolled students</span>
        </div>
    </div>

    <div class="stat-card waitlist-card" data-status="waitlist">
        <div class="stat-icon">
            <span class="dashicons dashicons-clock"></span>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['waitlist']); ?></h3>
            <p>Waitlist Members</p>
            <span class="stat-description">Students in waitlist programs</span>
        </div>
    </div>

    <div class="stat-card inactive-card" data-status="inactive">
        <div class="stat-icon">
            <span class="dashicons dashicons-dismiss"></span>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['inactive']); ?></h3>
            <p>Inactive Members</p>
            <span class="stat-description">Expired or cancelled memberships</span>
        </div>
    </div>

    <div class="stat-card total-card" data-status="all">
        <div class="stat-icon">
            <span class="dashicons dashicons-admin-users"></span>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($stats['total']); ?></h3>
            <p>Total Students</p>
            <span class="stat-description">All registered students</span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Get membership status counts using EXACT same logic as active-members-test.php
 */
function wcb_get_membership_status_counts() {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [
            'active' => 0,
            'waitlist' => 0,
            'inactive' => 0,
            'total' => 0
        ];
    }

    // Use the EXACT same group names as active-members-test.php
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $all_groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    $total_active_count = 0;

    // Step 1: Get ALL active members first (same as active-members-test.php)
    $wcb_mentoring_id = 1738;
    $all_active_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id != %d
        AND u.user_login != 'bwgdev'
        ORDER BY u.ID
    ", $wcb_mentoring_id));

    // Step 2: Filter by groups (same as active-members-test.php)
    $program_group_members = [];

    foreach ($defined_groups as $group_name) {
        // Find the group - exact matching (same as active-members-test.php)
        $group = null;
        foreach ($all_groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            continue; // Group not found
        }

        // Use the EXACT same logic as active-members-test.php
        $group_memberships = wcb_get_group_memberships($group->ID);

        if (!empty($group_memberships)) {
            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);

            // Check each active member to see if they belong to this group (same as active-members-test.php)
            foreach ($all_active_members as $active_member) {
                // Get their current transaction
                $user_transaction = $wpdb->get_row($wpdb->prepare("
                    SELECT t.*, p.post_title as membership_name
                    FROM {$txn_table} t
                    LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
                    WHERE t.user_id = %d
                    AND t.status IN ('confirmed', 'complete')
                    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                    AND t.product_id != %d
                    ORDER BY t.created_at DESC
                    LIMIT 1
                ", $active_member->ID, $wcb_mentoring_id));

                if ($user_transaction && in_array($user_transaction->product_id, $membership_ids)) {
                    $program_group_members[$active_member->ID] = $active_member;
                }
            }
        }
    }

    $total_active_count = count($program_group_members);

    // Get waitlist members count (separate from program groups)
    $waitlist_count = $wpdb->get_var("
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND p.post_type = 'memberpressproduct'
        AND p.post_title LIKE '%waitlist%'
        AND u.user_login != 'bwgdev'
    ");

    return [
        'active' => (int) $total_active_count,
        'waitlist' => (int) $waitlist_count,
        'inactive' => 0, // Can be implemented later if needed
        'total' => (int) ($total_active_count + $waitlist_count)
    ];
}

// Note: wcb_get_group_member_count() and wcb_get_group_members() functions
// are defined in student-table.php to avoid conflicts

/**
 * Get table title based on current filter
 */
function wcb_get_table_title($filter) {
    switch ($filter) {
        case 'active':
            return 'Active Members';
        case 'waitlist':
            return 'Waitlist Members';
        case 'inactive':
            return 'Inactive Members';
        default:
            return 'All Students';
    }
}

/**
 * Dashboard Styles
 */
function wcb_students_dashboard_styles() {
    ob_start();
    ?>
    <style>
    .students-dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Dashboard Header */
    .dashboard-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .dashboard-header h2 {
        margin: 0 0 8px 0;
        font-size: 28px;
        font-weight: 600;
    }

    .dashboard-header .dashicons {
        font-size: 32px;
        margin-right: 10px;
        vertical-align: middle;
    }

    .dashboard-subtitle {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
    }

    /* Quick Stats Grid */
    .student-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: 2px solid transparent;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .stat-card.active-card { border-color: #4CAF50; }
    .stat-card.waitlist-card { border-color: #FF9800; }
    .stat-card.inactive-card { border-color: #f44336; }
    .stat-card.total-card { border-color: #2196F3; }

    .stat-card .stat-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        opacity: 0.2;
    }

    .stat-card .stat-icon .dashicons {
        font-size: 40px;
    }

    .stat-card h3 {
        font-size: 32px;
        margin: 0 0 5px 0;
        font-weight: 700;
        color: #333;
    }

    .stat-card p {
        font-size: 16px;
        margin: 0 0 5px 0;
        font-weight: 600;
        color: #555;
    }

    .stat-description {
        font-size: 13px;
        color: #777;
    }

    /* Dashboard Controls */
    .dashboard-controls {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .search-section {
        margin-bottom: 20px;
    }

    .search-container {
        display: flex;
        gap: 10px;
        max-width: 600px;
    }

    .student-search-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .student-search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-btn, .clear-btn {
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .search-btn {
        background: #667eea;
        color: white;
    }

    .search-btn:hover {
        background: #5a6fd8;
    }

    .clear-btn {
        background: #f5f5f5;
        color: #666;
    }

    .clear-btn:hover {
        background: #e0e0e0;
    }

    /* Filter Buttons */
    .active-members-header {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
        margin-bottom: 20px;
    }

    .active-members-header h3 {
        margin: 0 0 8px 0;
        color: #212529;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
    }

    .active-members-header .member-count {
        background: #667eea;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
    }

    .active-members-header .subtitle {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
    }

    /* Students Table */
    .students-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }

    .table-header h3 {
        margin: 0;
        color: #333;
        font-size: 20px;
    }

    .table-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .per-page-select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    .export-btn {
        padding: 8px 16px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: background 0.3s ease;
    }

    .export-btn:hover {
        background: #218838;
    }

    /* Loading State */
    .loading-state {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Search Results */
    .search-results-container {
        margin-top: 10px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 1000;
        position: relative;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .students-dashboard {
            padding: 15px;
        }

        .student-stats-grid {
            grid-template-columns: 1fr;
        }

        .search-container {
            flex-direction: column;
        }

        .filter-buttons {
            justify-content: center;
        }

        .table-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .table-controls {
            width: 100%;
            justify-content: space-between;
        }
    }

    /* Access Denied */
    .access-denied {
        text-align: center;
        padding: 40px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 8px;
        border: 1px solid #f5c6cb;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Dashboard JavaScript
 */
function wcb_students_dashboard_scripts() {
    ob_start();
    ?>
    <script>
    jQuery(document).ready(function($) {
        'use strict';
        
        let currentPage = 1;
        let currentFilter = 'active'; // Always active members only
        let currentSearch = '';
        let itemsPerPage = 20;

        // Initialize dashboard
        initStudentsDashboard();

        function initStudentsDashboard() {
            loadStudentsTable();
            bindEvents();
            
            // Load initial search from URL if provided
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('search')) {
                currentSearch = urlParams.get('search');
                $('#wcb-student-search').val(currentSearch);
            }
        }

        function bindEvents() {
            // Search functionality
            $('#wcb-student-search').on('input', debounce(function() {
                const searchTerm = $(this).val().trim();
                if (searchTerm.length >= 2) {
                    performLiveSearch(searchTerm);
                } else {
                    $('#wcb-student-search-results').hide();
                }
            }, 300));

            // Search button
            $('#wcb-student-search-btn').on('click', function(e) {
                e.preventDefault();
                currentSearch = $('#wcb-student-search').val().trim();
                currentPage = 1;
                loadStudentsTable();
                updateURL();
            });

            // Clear search
            $('#wcb-clear-search').on('click', function() {
                $('#wcb-student-search').val('');
                $('#wcb-student-search-results').hide();
                currentSearch = '';
                currentPage = 1;
                loadStudentsTable();
                updateURL();
            });

            // No filter buttons - always show active members

            // No stat card filtering - always show active members

            // Per page select
            $('#per-page-select').on('change', function() {
                itemsPerPage = parseInt($(this).val());
                currentPage = 1;
                loadStudentsTable();
            });

            // Export button
            $('#export-students').on('click', function() {
                exportStudents();
            });
        }

        function loadStudentsTable() {
            $('#wcb-students-table-content').html(`
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading students...</p>
                </div>
            `);

            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_dashboard_students_table',
                    page: currentPage,
                    per_page: itemsPerPage,
                    search: currentSearch,
                    membership_status: currentFilter,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#wcb-students-table-content').html(response.data.rows);
                        $('#wcb-students-pagination').html(response.data.pagination_controls);
                        updateStatsCards();
                    } else {
                        $('#wcb-students-table-content').html(`
                            <div class="error-state">
                                <p>Error loading students: ${response.data}</p>
                                <button onclick="location.reload()">Retry</button>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#wcb-students-table-content').html(`
                        <div class="error-state">
                            <p>Failed to load students. Please try again.</p>
                            <button onclick="location.reload()">Retry</button>
                        </div>
                    `);
                }
            });
        }

        function performLiveSearch(searchTerm) {
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_dashboard_search_students',
                    search: searchTerm,
                    limit: 10,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let resultsHtml = '';
                        response.data.forEach(function(student) {
                            resultsHtml += `
                                <div class="search-result-item" data-student-id="${student.id}">
                                    <strong>${student.name}</strong>
                                    <span class="student-email">${student.email}</span>
                                    <span class="student-status">${student.status}</span>
                                </div>
                            `;
                        });
                        $('#wcb-student-search-results').html(resultsHtml).show();
                        
                        // Bind click events to search results
                        $('.search-result-item').on('click', function() {
                            const studentId = $(this).data('student-id');
                            showStudentProfile(studentId);
                            $('#wcb-student-search-results').hide();
                        });
                    } else {
                        $('#wcb-student-search-results').html('<div class="no-results">No students found</div>').show();
                    }
                }
            });
        }

        function showStudentProfile(studentId) {
            // Trigger the existing student profile functionality
            $(document).trigger('wcb:show-student-profile', [studentId]);
        }

        function updateStatsCards() {
            // Refresh stats cards with latest data
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_get_student_stats',
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.stat-card').each(function() {
                            const status = $(this).data('status');
                            if (response.data[status] !== undefined) {
                                $(this).find('h3').text(response.data[status].toLocaleString());
                            }
                        });
                    }
                }
            });
        }

        function updateURL() {
            const params = new URLSearchParams();
            if (currentSearch) params.set('search', currentSearch);

            const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({}, '', newURL);
        }

        function getTableTitle(filter) {
            switch (filter) {
                case 'active': return 'Active Members';
                case 'waitlist': return 'Waitlist Members';
                case 'inactive': return 'Inactive Members';
                default: return 'All Students';
            }
        }

        function exportStudents() {
            const params = new URLSearchParams({
                action: 'wcb_export_students',
                status: currentFilter,
                search: currentSearch,
                nonce: wcb_ajax.nonce
            });
            
            window.open(wcb_ajax.ajax_url + '?' + params.toString(), '_blank');
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Pagination handler
        $(document).on('click', '.pagination-btn', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                loadStudentsTable();
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('#wcb-student-search').focus().select();
            }
            
            // Escape to clear search results
            if (e.key === 'Escape') {
                $('#wcb-student-search-results').hide();
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('dashboard_students', 'dashboard_students_shortcode');

/**
 * AJAX handler for loading dashboard students table using proven logic
 */
function wcb_ajax_load_dashboard_students_table() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }

    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Check if MemberPress transactions table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    if (!$table_exists) {
        wp_send_json_error('MemberPress data not available');
        return;
    }

    $page = max(1, intval($_POST['page']));
    $per_page = max(1, min(100, intval($_POST['per_page'])));
    $search = sanitize_text_field($_POST['search']);
    $membership_status = sanitize_text_field($_POST['membership_status']);

    $offset = ($page - 1) * $per_page;

    // Use the EXACT same group names as active-members-test.php
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $all_groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    // Filter to only include the 7 defined groups
    $programs = [];
    foreach ($all_groups as $group) {
        foreach ($defined_groups as $defined_group) {
            if (strcasecmp($group->post_title, $defined_group) === 0) {
                $programs[] = $group;
                break;
            }
        }
    }

    // Use EXACT same two-step approach as active-members-test.php
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $wcb_mentoring_id = 1738;
    $competitive_team_id = 1931; // Competitive Team membership ID

    // Step 1: Get ALL active members first (same as active-members-test.php)
    $all_active_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id != %d
        AND u.user_login != 'bwgdev'
        ORDER BY u.ID
    ", $wcb_mentoring_id));

    // Step 2: Filter by groups (same as active-members-test.php)
    $all_program_members = [];

    foreach ($defined_groups as $group_name) {
        // Find the group - exact matching (same as active-members-test.php)
        $group = null;
        foreach ($all_groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            continue; // Group not found
        }

        // Use the EXACT same logic as active-members-test.php
        $group_memberships = wcb_get_group_memberships($group->ID);

        if (!empty($group_memberships)) {
            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);

            // Check each active member to see if they belong to this group (same as active-members-test.php)
            foreach ($all_active_members as $active_member) {
                // Get their current transaction
                $user_transaction = $wpdb->get_row($wpdb->prepare("
                    SELECT t.*, p.post_title as membership_name
                    FROM {$txn_table} t
                    LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
                    WHERE t.user_id = %d
                    AND t.status IN ('confirmed', 'complete')
                    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                    AND t.product_id != %d
                    ORDER BY t.created_at DESC
                    LIMIT 1
                ", $active_member->ID, $wcb_mentoring_id));

                if ($user_transaction && in_array($user_transaction->product_id, $membership_ids)) {
                    $all_program_members[$active_member->ID] = $active_member;
                }
            }
        }
    }

    // Step 3: Also include Competitive Team members (they should remain active)
    foreach ($all_active_members as $active_member) {
        // Check if they have Competitive Team membership
        $competitive_transaction = $wpdb->get_row($wpdb->prepare("
            SELECT t.*
            FROM {$txn_table} t
            WHERE t.user_id = %d
            AND t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            LIMIT 1
        ", $active_member->ID, $competitive_team_id));

        if ($competitive_transaction) {
            $all_program_members[$active_member->ID] = $active_member;
        }
    }

    // Convert to array and apply search filter
    $filtered_members = array_values($all_program_members);

    if (!empty($search)) {
        $filtered_members = array_filter($filtered_members, function($user) use ($search) {
            return (stripos($user->display_name, $search) !== false ||
                    stripos($user->user_email, $search) !== false);
        });
    }

    // Only handle active members (from program groups)
    $target_members = $filtered_members;

    if (empty($target_members)) {
        wp_send_json_success([
            'rows' => '<div class="no-students-found"><p>No students found matching your criteria.</p></div>',
            'pagination_controls' => '<div class="pagination-info">Showing 0 students</div>',
            'total_items' => 0,
            'current_page' => 1,
            'total_pages' => 1
        ]);
        return;
    }

    // Apply pagination
    $total_items = count($target_members);
    $total_pages = ceil($total_items / $per_page);

    $paginated_members = array_slice($target_members, $offset, $per_page);

    // Convert to the format expected by the table generator
    $users = [];
    foreach ($paginated_members as $member) {
        if (is_object($member) && isset($member->ID)) {
            $users[] = (object) [
                'ID' => $member->ID,
                'display_name' => $member->display_name ?? 'Unknown',
                'user_email' => $member->user_email ?? '',
                'user_registered' => $member->user_registered ?? date('Y-m-d H:i:s')
            ];
        }
    }

    // Generate table HTML
    $rows_html = wcb_generate_students_table_html($users, $membership_status);

    // Generate pagination
    $pagination_html = wcb_generate_pagination_html($page, $total_pages, $total_items);

    wp_send_json_success([
        'rows' => $rows_html,
        'pagination_controls' => $pagination_html,
        'total_items' => $total_items,
        'current_page' => $page,
        'total_pages' => $total_pages
    ]);
}
add_action('wp_ajax_wcb_load_dashboard_students_table', 'wcb_ajax_load_dashboard_students_table');
add_action('wp_ajax_nopriv_wcb_load_dashboard_students_table', 'wcb_ajax_load_dashboard_students_table');

/**
 * Generate students table HTML
 */
function wcb_generate_students_table_html($users, $membership_status) {
    if (empty($users)) {
        return '<div class="no-students-found">
            <p>No ' . $membership_status . ' students found.</p>
        </div>';
    }

    ob_start();
    ?>
    <table class="wcb-students-table">
        <thead>
            <tr>
                <th><span class="dashicons dashicons-admin-users"></span> Name</th>
                <th><span class="dashicons dashicons-email"></span> Email</th>
                <th><span class="dashicons dashicons-groups"></span> Membership</th>
                <th><span class="dashicons dashicons-yes-alt"></span> Status</th>
                <th><span class="dashicons dashicons-calendar-alt"></span> Joined</th>
                <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <?php
                $membership_info = wcb_get_user_membership_info($user->ID);
                $status_info = wcb_get_user_status_info($user->ID, $membership_status);
                $join_date = date('M j, Y', strtotime($user->user_registered));
                ?>
                <tr data-user-id="<?php echo $user->ID; ?>">
                    <td>
                        <div class="student-name">
                            <strong><?php echo esc_html($user->display_name); ?></strong>
                        </div>
                    </td>
                    <td>
                        <div class="student-email">
                            <?php echo esc_html($user->user_email); ?>
                        </div>
                    </td>
                    <td>
                        <div class="membership-info">
                            <?php echo esc_html($membership_info); ?>
                        </div>
                    </td>
                    <td>
                        <div class="status-info">
                            <?php echo $status_info; ?>
                        </div>
                    </td>
                    <td>
                        <div class="join-date">
                            <?php echo esc_html($join_date); ?>
                        </div>
                    </td>
                    <td>
                        <div class="student-actions">
                            <button class="btn-view-student" data-user-id="<?php echo $user->ID; ?>">
                                <span class="dashicons dashicons-visibility"></span>
                                View
                            </button>
                            <button class="btn-edit-student" data-user-id="<?php echo $user->ID; ?>">
                                <span class="dashicons dashicons-edit"></span>
                                Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <style>
    .wcb-students-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .wcb-students-table th {
        background: #f8f9fa;
        color: #333;
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 2px solid #dee2e6;
    }

    .wcb-students-table th .dashicons {
        margin-right: 5px;
        color: #667eea;
    }

    .wcb-students-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }

    .wcb-students-table tr:hover {
        background: #f8f9fa;
    }

    .student-name strong {
        color: #333;
        font-size: 14px;
    }

    .student-email {
        color: #6c757d;
        font-size: 13px;
    }

    .membership-info {
        color: #495057;
        font-size: 13px;
    }

    .join-date {
        color: #6c757d;
        font-size: 13px;
    }

    .student-actions {
        display: flex;
        gap: 5px;
    }

    .btn-view-student,
    .btn-edit-student {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .btn-view-student {
        background: #667eea;
        color: white;
    }

    .btn-view-student:hover {
        background: #5a6fd8;
    }

    .btn-edit-student {
        background: #28a745;
        color: white;
    }

    .btn-edit-student:hover {
        background: #218838;
    }

    .no-students-found {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 20px;
    }

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-waitlist {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Get user membership information using proven logic
 */
function wcb_get_user_membership_info($user_id) {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get user's most recent active transaction
    $transaction = $wpdb->get_row($wpdb->prepare("
        SELECT t.*, p.post_title as membership_name
        FROM {$txn_table} t
        LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        ORDER BY t.created_at DESC
        LIMIT 1
    ", $user_id));

    if ($transaction && $transaction->membership_name) {
        return $transaction->membership_name;
    }

    return 'No Membership';
}

/**
 * Get user status information with badge
 */
function wcb_get_user_status_info($user_id, $current_filter) {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $wcb_mentoring_id = 1738;

    // Check if user has active transactions
    $active_transaction = $wpdb->get_row($wpdb->prepare("
        SELECT t.*, p.post_title as membership_name
        FROM {$txn_table} t
        LEFT JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id != %d
        ORDER BY t.created_at DESC
        LIMIT 1
    ", $user_id, $wcb_mentoring_id));

    if ($active_transaction) {
        // Check if it's a waitlist membership
        if (strpos($active_transaction->membership_name, 'Waitlist') !== false) {
            return '<span class="status-badge status-waitlist">Waitlist</span>';
        } else {
            return '<span class="status-badge status-active">Active</span>';
        }
    } else {
        return '<span class="status-badge status-inactive">Inactive</span>';
    }
}

/**
 * Generate pagination HTML
 */
function wcb_generate_pagination_html($current_page, $total_pages, $total_items) {
    if ($total_pages <= 1) {
        return '<div class="pagination-info">Showing all ' . $total_items . ' students</div>';
    }

    ob_start();
    ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Showing page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
            (<?php echo number_format($total_items); ?> total students)
        </div>

        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <button class="pagination-btn" data-page="1">
                    <span class="dashicons dashicons-controls-skipback"></span>
                    First
                </button>
                <button class="pagination-btn" data-page="<?php echo $current_page - 1; ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                    Previous
                </button>
            <?php endif; ?>

            <?php
            // Show page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);

            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <button class="pagination-btn <?php echo $i === $current_page ? 'active' : ''; ?>"
                        data-page="<?php echo $i; ?>">
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <button class="pagination-btn" data-page="<?php echo $current_page + 1; ?>">
                    Next
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
                <button class="pagination-btn" data-page="<?php echo $total_pages; ?>">
                    Last
                    <span class="dashicons dashicons-controls-skipforward"></span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }

    .pagination-info {
        color: #6c757d;
        font-size: 14px;
    }

    .pagination-controls {
        display: flex;
        gap: 5px;
    }

    .pagination-btn {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        background: white;
        color: #495057;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .pagination-btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .pagination-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 15px;
        }

        .pagination-controls {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * AJAX handler for dashboard student search
 */
function wcb_ajax_dashboard_search_students() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }

    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    $search = sanitize_text_field($_POST['search']);
    $limit = max(1, min(20, intval($_POST['limit'])));

    if (empty($search) || strlen($search) < 2) {
        wp_send_json_success([]);
        return;
    }

    // Search students using proven logic
    $students = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE u.user_login != 'bwgdev'
        AND (u.display_name LIKE %s OR u.user_email LIKE %s)
        ORDER BY u.display_name
        LIMIT %d
    ", '%' . $search . '%', '%' . $search . '%', $limit));

    $results = [];
    foreach ($students as $student) {
        $status_info = wcb_get_user_status_info($student->ID, 'all');
        $results[] = [
            'id' => $student->ID,
            'name' => $student->display_name,
            'email' => $student->user_email,
            'status' => strip_tags($status_info)
        ];
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_wcb_dashboard_search_students', 'wcb_ajax_dashboard_search_students');
add_action('wp_ajax_nopriv_wcb_dashboard_search_students', 'wcb_ajax_dashboard_search_students');

/**
 * AJAX handler for getting updated student stats
 */
function wcb_ajax_get_student_stats() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }

    $stats = wcb_get_membership_status_counts();
    wp_send_json_success($stats);
}
add_action('wp_ajax_wcb_get_student_stats', 'wcb_ajax_get_student_stats');
add_action('wp_ajax_nopriv_wcb_get_student_stats', 'wcb_ajax_get_student_stats');

/**
 * AJAX handler for exporting students
 */
function wcb_ajax_export_students() {
    if (!wp_verify_nonce($_GET['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }

    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    $status = sanitize_text_field($_GET['status']);
    $search = sanitize_text_field($_GET['search']);
    $wcb_mentoring_id = 1738;

    // Build query based on status (same logic as table loading)
    $base_where = "WHERE u.user_login != 'bwgdev'";

    $search_where = '';
    if (!empty($search)) {
        $search_where = $wpdb->prepare("
            AND (u.display_name LIKE %s OR u.user_email LIKE %s)
        ", '%' . $search . '%', '%' . $search . '%');
    }

    switch ($status) {
        case 'active':
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                {$base_where}
                AND t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                AND t.product_id != {$wcb_mentoring_id}
                {$search_where}
                ORDER BY u.display_name
            ";
            break;

        case 'waitlist':
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                JOIN {$wpdb->posts} p ON t.product_id = p.ID
                {$base_where}
                AND t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                AND p.post_type = 'memberpressproduct'
                AND p.post_title LIKE '%Waitlist%'
                {$search_where}
                ORDER BY u.display_name
            ";
            break;

        case 'inactive':
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                {$base_where}
                AND u.ID NOT IN (
                    SELECT DISTINCT t2.user_id
                    FROM {$txn_table} t2
                    WHERE t2.status IN ('confirmed', 'complete')
                    AND (t2.expires_at IS NULL OR t2.expires_at > NOW() OR t2.expires_at = '0000-00-00 00:00:00')
                    AND t2.product_id != {$wcb_mentoring_id}
                )
                {$search_where}
                ORDER BY u.display_name
            ";
            break;

        default: // 'all'
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                {$base_where}
                {$search_where}
                ORDER BY u.display_name
            ";
            break;
    }

    $users = $wpdb->get_results($query);

    // Generate CSV
    $filename = 'students_export_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, ['Name', 'Email', 'Membership', 'Status', 'Joined Date']);

    // CSV data
    foreach ($users as $user) {
        $membership_info = wcb_get_user_membership_info($user->ID);
        $status_info = strip_tags(wcb_get_user_status_info($user->ID, $status));
        $join_date = date('M j, Y', strtotime($user->user_registered));

        fputcsv($output, [
            $user->display_name,
            $user->user_email,
            $membership_info,
            $status_info,
            $join_date
        ]);
    }

    fclose($output);
    exit;
}
add_action('wp_ajax_wcb_export_students', 'wcb_ajax_export_students');
add_action('wp_ajax_nopriv_wcb_export_students', 'wcb_ajax_export_students');
