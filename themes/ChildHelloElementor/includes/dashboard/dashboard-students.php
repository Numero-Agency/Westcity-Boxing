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

    // Get current filter from URL
    $current_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
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

            <!-- Status Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn <?php echo $current_filter === 'all' ? 'active' : ''; ?>" 
                        data-status="all">
                    <span class="dashicons dashicons-admin-users"></span>
                    All Students
                </button>
                <button class="filter-btn <?php echo $current_filter === 'active' ? 'active' : ''; ?>" 
                        data-status="active">
                    <span class="dashicons dashicons-yes-alt"></span>
                    Active Members
                </button>
                <button class="filter-btn <?php echo $current_filter === 'waitlist' ? 'active' : ''; ?>" 
                        data-status="waitlist">
                    <span class="dashicons dashicons-clock"></span>
                    Waitlist
                </button>
                <button class="filter-btn <?php echo $current_filter === 'inactive' ? 'active' : ''; ?>" 
                        data-status="inactive">
                    <span class="dashicons dashicons-dismiss"></span>
                    Inactive
                </button>
            </div>
        </div>

        <!-- Students Table Container -->
        <div class="students-table-container">
            <div class="table-header">
                <h3 id="table-title">
                    <?php echo wcb_get_table_title($current_filter); ?>
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
 * Get membership status counts for stats cards
 */
function wcb_get_membership_status_counts() {
    global $wpdb;
    
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    // Active members
    $active_count = $wpdb->get_var("
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ");

    // Waitlist members - all students in memberships with "Waitlist" in title (active or inactive)
    $waitlist_count = $wpdb->get_var("
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        JOIN {$wpdb->prefix}mepr_members m ON u.ID = m.user_id
        JOIN {$wpdb->posts} p ON (
            m.memberships LIKE CONCAT('%', p.ID, '%') 
            OR m.inactive_memberships LIKE CONCAT('%', p.ID, '%')
        )
        WHERE p.post_type = 'memberpressproduct'
        AND p.post_title LIKE '%Waitlist%'
    ");

    // Inactive members
    $inactive_count = $wpdb->get_var("
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        WHERE u.ID NOT IN (
            SELECT DISTINCT t.user_id 
            FROM {$txn_table} t 
            WHERE t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        )
        AND u.ID IN (
            SELECT DISTINCT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = '{$wpdb->prefix}capabilities' 
            AND (meta_value LIKE '%subscriber%' OR meta_value LIKE '%member%' OR meta_value LIKE '%customer%')
        )
    ");

    // Total students (all with member-related roles)
    $total_count = $wpdb->get_var("
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        WHERE u.ID IN (
            SELECT DISTINCT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = '{$wpdb->prefix}capabilities' 
            AND (meta_value LIKE '%subscriber%' OR meta_value LIKE '%member%' OR meta_value LIKE '%customer%')
        )
    ");

    return [
        'active' => (int) $active_count,
        'waitlist' => (int) $waitlist_count,
        'inactive' => (int) $inactive_count,
        'total' => (int) $total_count
    ];
}

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
    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 12px 20px;
        border: 2px solid #ddd;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-btn:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .filter-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
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
        let currentFilter = 'all';
        let currentSearch = '';
        let itemsPerPage = 20;

        // Initialize dashboard
        initStudentsDashboard();

        function initStudentsDashboard() {
            loadStudentsTable();
            bindEvents();
            
            // Load initial state from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status')) {
                currentFilter = urlParams.get('status');
                $('.filter-btn[data-status="' + currentFilter + '"]').addClass('active').siblings().removeClass('active');
            }
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

            // Filter buttons
            $('.filter-btn').on('click', function() {
                currentFilter = $(this).data('status');
                currentPage = 1;
                $(this).addClass('active').siblings().removeClass('active');
                $('#table-title').text(getTableTitle(currentFilter));
                loadStudentsTable();
                updateURL();
            });

            // Stat cards click
            $('.stat-card').on('click', function() {
                const status = $(this).data('status');
                if (status) {
                    currentFilter = status;
                    currentPage = 1;
                    $('.filter-btn[data-status="' + status + '"]').addClass('active').siblings().removeClass('active');
                    $('#table-title').text(getTableTitle(status));
                    loadStudentsTable();
                    updateURL();
                }
            });

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
                    action: 'wcb_load_students_table',
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
                    action: 'wcb_search_students',
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
            if (currentFilter !== 'all') params.set('status', currentFilter);
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
