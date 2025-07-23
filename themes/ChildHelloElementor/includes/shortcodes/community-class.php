<?php
// Community Class Component with Member List and Session Logs

function community_class_shortcode($atts) {
    $atts = shortcode_atts([
        'per_page' => '10',
        'show_search' => 'true',
        'show_pagination' => 'true',
        'class' => 'wcb-community-class',
        'active_tab' => 'members'
    ], $atts);
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="wcb-community-class">
        <div class="community-class-header">
            <h3><span class="dashicons dashicons-groups"></span> Community Class</h3>
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="members">
                    <span class="dashicons dashicons-admin-users"></span> Members
                </button>
                <button class="tab-btn" data-tab="sessions">
                    <span class="dashicons dashicons-calendar-alt"></span> Session Logs
                </button>
            </div>
        </div>
        
        <!-- Members Tab -->
        <div class="tab-content active" id="members-tab">
            <div class="table-controls">
                <?php if($atts['show_search'] === 'true'): ?>
                <div class="table-search">
                    <div class="search-input-wrapper">
                        <span class="dashicons dashicons-search search-icon"></span>
                        <input type="text" 
                               id="members-search-input" 
                               placeholder="Search members..." 
                               class="table-search-input">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="entries-per-page">
                    <label for="members-entries-select">Show:</label>
                    <select id="members-entries-select">
                        <option value="10" <?php selected($atts['per_page'], '10'); ?>>10</option>
                        <option value="25" <?php selected($atts['per_page'], '25'); ?>>25</option>
                        <option value="50" <?php selected($atts['per_page'], '50'); ?>>50</option>
                        <option value="100" <?php selected($atts['per_page'], '100'); ?>>100</option>
                    </select>
                </div>
            </div>
            
            <div class="members-table-content">
                <div id="members-loading" class="loading" style="display: none;">
                    <p>Loading members...</p>
                </div>
                
                <table class="members-table" id="members-table">
                    <thead>
                        <tr>
                            <th><span class="dashicons dashicons-admin-users"></span> Name</th>
                            <th><span class="dashicons dashicons-email"></span> Email</th>
                            <th><span class="dashicons dashicons-awards"></span> Membership</th>
                            <th><span class="dashicons dashicons-chart-bar"></span> Sessions</th>
                            <th><span class="dashicons dashicons-calendar-alt"></span> Joined</th>
                            <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="members-table-body">
                        <!-- Content loaded via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <?php if($atts['show_pagination'] === 'true'): ?>
            <div class="pagination" id="members-pagination">
                <div class="pagination-info" id="members-pagination-info">
                    Showing 0 - 0 of 0 members
                </div>
                <div class="pagination-controls" id="members-pagination-controls">
                    <!-- Pagination buttons loaded via AJAX -->
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Session Logs Tab -->
        <div class="tab-content" id="sessions-tab">
            <div class="sessions-header">
                <div class="sessions-controls">
                    <button class="btn-add-session" id="add-session-btn">
                        <span class="dashicons dashicons-plus"></span> Add Session
                    </button>
                    <div class="table-search">
                        <div class="search-input-wrapper">
                            <span class="dashicons dashicons-search search-icon"></span>
                            <input type="text" 
                                   id="sessions-search-input" 
                                   placeholder="Search sessions..." 
                                   class="table-search-input">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sessions-table-content">
                <div id="sessions-loading" class="loading" style="display: none;">
                    <p>Loading sessions...</p>
                </div>
                
                <table class="sessions-table" id="sessions-table">
                    <thead>
                        <tr>
                            <th><span class="dashicons dashicons-calendar-alt"></span> Date</th>
                            <th><span class="dashicons dashicons-clock"></span> Time</th>
                            <th><span class="dashicons dashicons-groups"></span> Attendees</th>
                            <th><span class="dashicons dashicons-admin-users"></span> Instructor</th>
                            <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sessions-table-body">
                        <!-- Content loaded via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <div class="pagination" id="sessions-pagination">
                <div class="pagination-info" id="sessions-pagination-info">
                    Showing 0 - 0 of 0 sessions
                </div>
                <div class="pagination-controls" id="sessions-pagination-controls">
                    <!-- Pagination buttons loaded via AJAX -->
                </div>
            </div>
        </div>
        
        <!-- Add Session Modal -->
        <div id="add-session-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><span class="dashicons dashicons-plus"></span> Add Community Class Session</h3>
                    <button class="close-modal" id="close-session-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="add-session-form">
                        <?php wp_nonce_field('add_community_session', 'community_session_nonce'); ?>
                        
                        <div class="form-row">
                            <label for="session_date">Date & Time *</label>
                            <input type="datetime-local" id="session_date" name="session_date" required>
                        </div>
                        
                        <div class="form-row">
                            <label for="instructor">Instructor</label>
                            <select id="instructor" name="instructor">
                                <option value="">Select Instructor</option>
                                <?php
                                $instructors = get_users(['role__in' => ['administrator', 'editor', 'instructor']]);
                                foreach ($instructors as $instructor): ?>
                                    <option value="<?php echo $instructor->ID; ?>">
                                        <?php echo esc_html($instructor->display_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <label>Attendance</label>
                            <div class="attendance-section">
                                <p class="field-description">Select members who attended this session:</p>
                                <div class="checkbox-grid" id="attendance-list">
                                    <!-- Members loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <label for="session_notes">Notes</label>
                            <textarea id="session_notes" name="session_notes" rows="4" 
                                placeholder="Any notes about this session..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">Add Session</button>
                            <button type="button" class="btn-cancel" id="cancel-session">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentPage = 1;
        var perPage = <?php echo intval($atts['per_page']); ?>;
        var searchTerm = '';
        var searchTimeout;
        var activeTab = 'members';
        
        // Tab Navigation
        $('.tab-btn').on('click', function() {
            var tabId = $(this).data('tab');
            switchTab(tabId);
        });
        
        function switchTab(tabId) {
            // Update active tab
            $('.tab-btn').removeClass('active');
            $('.tab-btn[data-tab="' + tabId + '"]').addClass('active');
            
            // Show/hide tab content
            $('.tab-content').removeClass('active');
            $('#' + tabId + '-tab').addClass('active');
            
            activeTab = tabId;
            
            // Load content based on tab
            if (tabId === 'members') {
                loadMembersTable();
            } else if (tabId === 'sessions') {
                loadSessionsTable();
            }
        }
        
        // Initialize - load members table
        loadMembersTable();
        
        // Members Search
        $('#members-search-input').on('input', function() {
            clearTimeout(searchTimeout);
            searchTerm = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                currentPage = 1;
                loadMembersTable();
            }, 300);
        });
        
        // Members per page change
        $('#members-entries-select').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadMembersTable();
        });
        
        // Members pagination
        $(document).on('click', '.pagination-btn', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled') || $(this).hasClass('current')) {
                return;
            }
            
            var page = $(this).data('page');
            if (page) {
                currentPage = page;
                if (activeTab === 'members') {
                    loadMembersTable();
                } else if (activeTab === 'sessions') {
                    loadSessionsTable();
                }
            }
        });
        
        // Add Session Modal
        $('#add-session-btn').on('click', function() {
            loadMembersForAttendance();
            $('#add-session-modal').show();
        });
        
        $('#close-session-modal, #cancel-session').on('click', function() {
            $('#add-session-modal').hide();
        });
        
        // Add Session Form
        $('#add-session-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: formData + '&action=wcb_add_community_session&nonce=' + wcb_ajax.nonce,
                success: function(response) {
                    if (response.success) {
                        $('#add-session-modal').hide();
                        $('#add-session-form')[0].reset();
                        loadSessionsTable();
                        alert('Session added successfully!');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to add session. Please try again.');
                }
            });
        });
        
        // Sessions Search
        $('#sessions-search-input').on('input', function() {
            clearTimeout(searchTimeout);
            searchTerm = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                currentPage = 1;
                loadSessionsTable();
            }, 300);
        });
        
        function loadMembersTable() {
            $('#members-loading').show();
            $('#members-table-body').html('');
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wcb_load_community_members',
                    page: currentPage,
                    per_page: perPage,
                    search: searchTerm,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    $('#members-loading').hide();
                    
                    if (response.success) {
                        $('#members-table-body').html(response.data.rows);
                        $('#members-pagination-info').html(response.data.pagination_info);
                        $('#members-pagination-controls').html(response.data.pagination_controls);
                        
                        // Add smooth animation
                        $('#members-table-body tr').hide().each(function(index) {
                            $(this).delay(index * 50).fadeIn(200);
                        });
                    } else {
                        $('#members-table-body').html('<tr><td colspan="6" class="error">' + response.data + '</td></tr>');
                    }
                },
                error: function() {
                    $('#members-loading').hide();
                    $('#members-table-body').html('<tr><td colspan="6" class="error">Failed to load members.</td></tr>');
                }
            });
        }
        
        function loadSessionsTable() {
            $('#sessions-loading').show();
            $('#sessions-table-body').html('');
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wcb_load_community_sessions',
                    page: currentPage,
                    per_page: perPage,
                    search: searchTerm,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    $('#sessions-loading').hide();
                    
                    if (response.success) {
                        $('#sessions-table-body').html(response.data.rows);
                        $('#sessions-pagination-info').html(response.data.pagination_info);
                        $('#sessions-pagination-controls').html(response.data.pagination_controls);
                        
                        // Add smooth animation
                        $('#sessions-table-body tr').hide().each(function(index) {
                            $(this).delay(index * 50).fadeIn(200);
                        });
                    } else {
                        $('#sessions-table-body').html('<tr><td colspan="5" class="error">' + response.data + '</td></tr>');
                    }
                },
                error: function() {
                    $('#sessions-loading').hide();
                    $('#sessions-table-body').html('<tr><td colspan="5" class="error">Failed to load sessions.</td></tr>');
                }
            });
        }
        
        function loadMembersForAttendance() {
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_members_for_attendance',
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#attendance-list').html(response.data);
                    } else {
                        $('#attendance-list').html('<p class="error">Failed to load members</p>');
                    }
                },
                error: function() {
                    $('#attendance-list').html('<p class="error">Failed to load members</p>');
                }
            });
        }
    });
    </script>
    
    <style>
    /* Override browser default table styling */
    .members-table,
    .sessions-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }

    .members-table,
    .members-table th,
    .members-table td,
    .sessions-table,
    .sessions-table th,
    .sessions-table td {
        border: none !important;
        border-bottom: 1px solid #e5e5e5 !important;
        box-shadow: none !important;
        background-clip: padding-box !important;
    }

    .members-table th,
    .members-table td,
    .sessions-table th,
    .sessions-table td {
        padding: 12px !important;
        vertical-align: middle !important;
        text-align: left !important;
        font-size: 14px !important;
        color: #333 !important;
    }

    .members-table th,
    .sessions-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .members-table tbody tr:hover,
    .sessions-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    .members-table tbody tr:nth-child(even),
    .sessions-table tbody tr:nth-child(even) {
        background-color: #ffffff !important;
    }

    .members-table tbody tr:nth-child(odd),
    .sessions-table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    /* Community class specific styles */
    .wcb-community-class {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        margin: 20px 0;
    }
    
    .community-class-header {
        background: #000000;
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .community-class-header h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: white;
    }
    
    .tab-navigation {
        display: flex;
        gap: 10px;
    }
    
    .tab-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .tab-btn:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .tab-btn.active {
        background: white;
        color: #000000;
    }
    
    .tab-content {
        display: none;
        padding: 20px;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .table-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 20px;
    }
    
    .sessions-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .btn-add-session {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s ease;
    }
    
    .btn-add-session:hover {
        background: #218838;
    }
    
    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .search-icon {
        position: absolute;
        left: 10px;
        color: #999;
    }
    
    .table-search-input {
        padding: 10px 15px 10px 35px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        width: 250px;
    }
    
    .entries-per-page {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .entries-per-page select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
    }
    
    .pagination-info {
        color: #666;
        font-size: 14px;
    }
    
    .pagination-controls {
        display: flex;
        gap: 5px;
    }
    
    .pagination-btn {
        padding: 8px 12px;
        border: 1px solid #ddd;
        background: white;
        color: #333;
        text-decoration: none;
        border-radius: 3px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .pagination-btn:hover {
        background: #f8f9fa;
    }
    
    .pagination-btn.current {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .form-row {
        margin-bottom: 20px;
    }
    
    .form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }
    
    .form-row input, .form-row select, .form-row textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }
    
    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
    }
    
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    
    .checkbox-item input[type="checkbox"] {
        width: auto;
        margin: 0;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .btn-submit {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .error {
        color: #dc3545;
        text-align: center;
        padding: 20px;
    }
    
    .loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }
    
    .field-description {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_community_class', 'community_class_shortcode');

// AJAX Handlers

// Load Community Members
function wcb_ajax_load_community_members() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }
    
    $page = intval($_POST['page']);
    $per_page = intval($_POST['per_page']);
    $search = sanitize_text_field($_POST['search']);
    
    $offset = ($page - 1) * $per_page;
    
    // Get Community Class membership ID
    $community_class_membership = get_posts([
        'post_type' => 'memberpressproduct',
        'title' => 'Community Class',
        'post_status' => 'publish',
        'numberposts' => 1
    ]);
    
    if (empty($community_class_membership)) {
        wp_send_json_error('Community Class membership not found');
        return;
    }
    
    $community_class_id = $community_class_membership[0]->ID;
    
    // Get users with active Community Class membership
    $community_members = wcb_get_community_class_members($community_class_id, $search);
    
    // Apply pagination
    $total_users = count($community_members);
    $users = array_slice($community_members, $offset, $per_page);
    
    $rows = '';
    foreach ($users as $user) {
        $membership = wcb_get_user_membership($user->ID);
        $session_count = wcb_get_user_session_count($user->ID);
        $join_date = date('M j, Y', strtotime($user->user_registered));
        
        $rows .= '<tr>';
        $rows .= '<td><strong>' . esc_html($user->display_name) . '</strong></td>';
        $rows .= '<td>' . esc_html($user->user_email) . '</td>';
        $rows .= '<td>' . esc_html($membership) . '</td>';
        $rows .= '<td>' . intval($session_count) . '</td>';
        $rows .= '<td>' . esc_html($join_date) . '</td>';
        $rows .= '<td><button class="btn-view-member" data-user-id="' . $user->ID . '">View</button></td>';
        $rows .= '</tr>';
    }
    
    // Generate pagination
    $total_pages = ceil($total_users / $per_page);
    $start = $offset + 1;
    $end = min($offset + $per_page, $total_users);
    
    $pagination_info = "Showing {$start} - {$end} of {$total_users} members";
    $pagination_controls = wcb_generate_pagination_controls($page, $total_pages);
    
    wp_send_json_success([
        'rows' => $rows,
        'pagination_info' => $pagination_info,
        'pagination_controls' => $pagination_controls
    ]);
}
add_action('wp_ajax_wcb_load_community_members', 'wcb_ajax_load_community_members');
add_action('wp_ajax_nopriv_wcb_load_community_members', 'wcb_ajax_load_community_members');

// Load Community Sessions
function wcb_ajax_load_community_sessions() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }
    
    $page = intval($_POST['page']);
    $per_page = intval($_POST['per_page']);
    $search = sanitize_text_field($_POST['search']);
    
    $offset = ($page - 1) * $per_page;
    
    // Get community sessions
    $args = [
        'post_type' => 'community_session',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'orderby' => 'meta_value',
        'meta_key' => 'session_date',
        'order' => 'DESC',
        'post_status' => 'publish'
    ];
    
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    $sessions = get_posts($args);
    
    // Get total count
    $total_args = $args;
    $total_args['posts_per_page'] = -1;
    unset($total_args['offset']);
    $total_sessions = count(get_posts($total_args));
    
    $rows = '';
    foreach ($sessions as $session) {
        $session_date = get_field('session_date', $session->ID);
        $instructor_id = get_field('instructor', $session->ID);
        $attendance = get_field('attendance', $session->ID);
        
        $formatted_date = $session_date ? date('M j, Y', strtotime($session_date)) : 'N/A';
        $formatted_time = $session_date ? date('g:i A', strtotime($session_date)) : 'N/A';
        
        $instructor = $instructor_id ? get_user_by('ID', $instructor_id) : null;
        $instructor_name = $instructor ? $instructor->display_name : 'N/A';
        
        $attendee_count = is_array($attendance) ? count($attendance) : 0;
        
        $rows .= '<tr>';
        $rows .= '<td>' . esc_html($formatted_date) . '</td>';
        $rows .= '<td>' . esc_html($formatted_time) . '</td>';
        $rows .= '<td><span class="attendee-count">' . $attendee_count . '</span> attendees</td>';
        $rows .= '<td>' . esc_html($instructor_name) . '</td>';
        $rows .= '<td>';
        $rows .= '<button class="btn-view-session" data-session-id="' . $session->ID . '">View</button> ';
        $rows .= '<button class="btn-edit-session" data-session-id="' . $session->ID . '">Edit</button>';
        $rows .= '</td>';
        $rows .= '</tr>';
    }
    
    // Generate pagination
    $total_pages = ceil($total_sessions / $per_page);
    $start = $offset + 1;
    $end = min($offset + $per_page, $total_sessions);
    
    $pagination_info = "Showing {$start} - {$end} of {$total_sessions} sessions";
    $pagination_controls = wcb_generate_pagination_controls($page, $total_pages);
    
    wp_send_json_success([
        'rows' => $rows,
        'pagination_info' => $pagination_info,
        'pagination_controls' => $pagination_controls
    ]);
}
add_action('wp_ajax_wcb_load_community_sessions', 'wcb_ajax_load_community_sessions');
add_action('wp_ajax_nopriv_wcb_load_community_sessions', 'wcb_ajax_load_community_sessions');

// Load Members for Attendance
function wcb_ajax_load_members_for_attendance() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }
    
    // Get Community Class membership ID
    $community_class_membership = get_posts([
        'post_type' => 'memberpressproduct',
        'title' => 'Community Class',
        'post_status' => 'publish',
        'numberposts' => 1
    ]);
    
    if (empty($community_class_membership)) {
        wp_send_json_error('Community Class membership not found');
        return;
    }
    
    $community_class_id = $community_class_membership[0]->ID;
    
    // Get Community Class members
    $users = wcb_get_community_class_members($community_class_id);
    
    $html = '';
    foreach ($users as $user) {
        $html .= '<label class="checkbox-item">';
        $html .= '<input type="checkbox" name="attendance[]" value="' . $user->ID . '">';
        $html .= esc_html($user->display_name);
        $html .= '</label>';
    }
    
    wp_send_json_success($html);
}
add_action('wp_ajax_wcb_load_members_for_attendance', 'wcb_ajax_load_members_for_attendance');
add_action('wp_ajax_nopriv_wcb_load_members_for_attendance', 'wcb_ajax_load_members_for_attendance');

// Add Community Session
function wcb_ajax_add_community_session() {
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_die('Security check failed');
    }
    
    $session_date = sanitize_text_field($_POST['session_date']);
    $instructor = intval($_POST['instructor']);
    $attendance = isset($_POST['attendance']) ? array_map('intval', $_POST['attendance']) : [];
    $notes = sanitize_textarea_field($_POST['session_notes']);
    
    // Create post
    $post_id = wp_insert_post([
        'post_type' => 'community_session',
        'post_title' => 'Community Session - ' . date('M j, Y', strtotime($session_date)),
        'post_status' => 'publish',
        'post_author' => get_current_user_id()
    ]);
    
    if ($post_id) {
        // Save meta fields
        update_field('session_date', $session_date, $post_id);
        update_field('instructor', $instructor, $post_id);
        update_field('attendance', $attendance, $post_id);
        update_field('session_notes', $notes, $post_id);
        
        wp_send_json_success('Session added successfully');
    } else {
        wp_send_json_error('Failed to create session');
    }
}
add_action('wp_ajax_wcb_add_community_session', 'wcb_ajax_add_community_session');
add_action('wp_ajax_nopriv_wcb_add_community_session', 'wcb_ajax_add_community_session');

// Helper Functions

function wcb_get_community_class_members($community_class_id, $search = '') {
    global $wpdb;
    
    // Get Community Class members using MemberPress database
    $members = [];
    
    // Try MemberPress database query first
    $subs_table = $wpdb->prefix . 'mepr_subscriptions';
    $users_table = $wpdb->users;
    
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$subs_table'") == $subs_table;
    
    if ($table_exists) {
        $query = "
            SELECT DISTINCT u.* 
            FROM $users_table u
            JOIN $subs_table s ON u.ID = s.user_id
            WHERE s.product_id = %d 
            AND s.status = 'active'
        ";
        
        $query_args = [$community_class_id];
        
        if (!empty($search)) {
            $query .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $query_args[] = $search_term;
            $query_args[] = $search_term;
        }
        
        $query .= " ORDER BY u.display_name ASC";
        
        $results = $wpdb->get_results($wpdb->prepare($query, $query_args));
        
        if ($results) {
            foreach ($results as $result) {
                $members[] = get_user_by('ID', $result->ID);
            }
        }
    }
    
    // Fallback: If MemberPress tables don't exist or query fails, get all users with member roles
    if (empty($members)) {
        $args = [
            'role__in' => ['subscriber', 'member', 'customer'],
            'orderby' => 'display_name',
            'order' => 'ASC'
        ];
        
        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['display_name', 'user_email'];
        }
        
        $all_users = get_users($args);
        
        // Filter by checking if they have Community Class membership
        foreach ($all_users as $user) {
            $user_membership = wcb_get_user_membership($user->ID);
            if (strpos(strtolower($user_membership), 'community class') !== false) {
                $members[] = $user;
            }
        }
    }
    
    return $members;
}

function wcb_get_user_membership($user_id) {
    // Check if MemberPress is active
    if (class_exists('MeprUser')) {
        $mepr_user = new MeprUser($user_id);
        $active_memberships = $mepr_user->active_product_subscriptions();
        
        if (!empty($active_memberships)) {
            $membership = get_post($active_memberships[0]);
            return $membership ? $membership->post_title : 'Member';
        }
    }
    
    return 'Community Member';
}

function wcb_generate_pagination_controls($current_page, $total_pages) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $controls = '';
    
    // Previous button
    if ($current_page > 1) {
        $controls .= '<a href="#" class="pagination-btn" data-page="' . ($current_page - 1) . '">&laquo; Previous</a>';
    } else {
        $controls .= '<span class="pagination-btn disabled">&laquo; Previous</span>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $controls .= '<span class="pagination-btn current">' . $i . '</span>';
        } else {
            $controls .= '<a href="#" class="pagination-btn" data-page="' . $i . '">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $controls .= '<a href="#" class="pagination-btn" data-page="' . ($current_page + 1) . '">Next &raquo;</a>';
    } else {
        $controls .= '<span class="pagination-btn disabled">Next &raquo;</span>';
    }
    
    return $controls;
}