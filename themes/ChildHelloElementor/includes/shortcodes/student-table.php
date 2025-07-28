<?php
// Student Table Component with Pagination

function student_table_shortcode($atts) {
    $atts = shortcode_atts([
        'per_page' => '10',
        'show_search' => 'true',
        'show_pagination' => 'true',
        'class' => 'wcb-student-table'
    ], $atts);
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="wcb-student-table">
        <div class="student-table-header">
            <h3><span class="dashicons dashicons-groups"></span> All Students</h3>
            <div class="table-controls">
                <div class="membership-filter">
                    <select id="membership-status-filter">
                        <option value="all">All Members</option>
                        <option value="active" selected>Active Members</option>
                        <option value="inactive">Inactive Members</option>
                        <option value="waitlist">Waitlist Members</option>
                    </select>
                </div>
                <?php if($atts['show_search'] === 'true'): ?>
                <div class="table-search">
                    <div class="search-input-wrapper">
                        <span class="dashicons dashicons-search search-icon"></span>
                        <input type="text" 
                               id="table-search-input" 
                               placeholder="Search students..." 
                               class="table-search-input">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="entries-per-page">
                    <label for="entries-select">Show:</label>
                    <select id="entries-select">
                        <option value="10" <?php selected($atts['per_page'], '10'); ?>>10</option>
                        <option value="25" <?php selected($atts['per_page'], '25'); ?>>25</option>
                        <option value="50" <?php selected($atts['per_page'], '50'); ?>>50</option>
                        <option value="100" <?php selected($atts['per_page'], '100'); ?>>100</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="student-table-content">
            <div id="table-loading" class="loading" style="display: none;">
                <p>Loading students...</p>
            </div>
            
            <table class="students-table" id="students-table">
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
                <tbody id="students-table-body">
                    <!-- Content loaded via AJAX -->
                </tbody>
            </table>
        </div>
        
        <?php if($atts['show_pagination'] === 'true'): ?>
        <div class="pagination" id="table-pagination">
            <div class="pagination-info" id="pagination-info">
                Showing 0 - 0 of 0 students
            </div>
            <div class="pagination-controls" id="pagination-controls">
                <!-- Pagination buttons loaded via AJAX -->
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Student Profile Overlay -->
        <div id="table-profile-overlay" class="table-profile-overlay" style="display: none;">
            <div class="profile-overlay-content">
                <!-- Profile content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentPage = 1;
        var perPage = <?php echo intval($atts['per_page']); ?>;
        var searchTerm = '';
        var membershipStatus = 'active'; // Default to active members
        var searchTimeout;
        
        // Initial load
        loadStudentsTable();
        
        // Membership status filter change
        $('#membership-status-filter').on('change', function() {
            membershipStatus = $(this).val();
            currentPage = 1;
            loadStudentsTable();
        });
        
        // Search functionality
        $('#table-search-input').on('input', function() {
            clearTimeout(searchTimeout);
            searchTerm = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                currentPage = 1;
                loadStudentsTable();
            }, 300);
        });
        
        // Entries per page change
        $('#entries-select').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadStudentsTable();
        });
        
        // Pagination click handler
        $(document).on('click', '.pagination-btn', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled') || $(this).hasClass('current')) {
                return;
            }
            
            var page = $(this).data('page');
            if (page) {
                currentPage = page;
                loadStudentsTable();
            }
        });
        
        // Load student profile when clicking on student
        $(document).on('click', '.student-table-view-btn', function(e) {
            e.preventDefault();
            var studentId = $(this).data('student-id');
            var studentName = $(this).closest('tr').find('.student-name').text();
            
            // Load profile in the same container
            loadStudentProfileOverlay(studentId, studentName);
        });
        
        // Back to students list function
        window.showStudentsTable = function() {
            $('#table-profile-overlay').hide();
            $('.student-table-content, .pagination').show();
            $('.student-table-header h3').html('<span class="dashicons dashicons-groups"></span> All Students');
        };
        
        function loadStudentsTable() {
            $('#table-loading').show();
            $('#students-table-body').html('');
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wcb_load_students_table',
                    page: currentPage,
                    per_page: perPage,
                    search: searchTerm,
                    membership_status: membershipStatus,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    $('#table-loading').hide();
                    
                    if (response.success) {
                        $('#students-table-body').html(response.data.rows);
                        $('#pagination-info').html(response.data.pagination_info);
                        $('#pagination-controls').html(response.data.pagination_controls);
                        
                        // Add smooth animation to rows
                        $('#students-table-body tr').hide().each(function(index) {
                            $(this).delay(index * 50).fadeIn(200);
                        });
                    } else {
                        $('#students-table-body').html('<tr><td colspan="6" class="error">' + response.data + '</td></tr>');
                        $('#pagination-info').html('Error loading students');
                        $('#pagination-controls').html('');
                    }
                },
                error: function() {
                    $('#table-loading').hide();
                    $('#students-table-body').html('<tr><td colspan="6" class="error">Failed to load students. Please try again.</td></tr>');
                    $('#pagination-info').html('Error loading students');
                    $('#pagination-controls').html('');
                }
            });
        }
        
        function loadStudentProfileOverlay(studentId, studentName) {
            if (!studentId) {
                return;
            }
            
            // Update header title
            $('.student-table-header h3').html('<span class="dashicons dashicons-admin-users"></span> ' + studentName);
            
            // Hide table content and pagination
            $('.student-table-content, .pagination').hide();
            
            // Show overlay with loading state
            $('#table-profile-overlay .profile-overlay-content').html(
                '<div class="profile-loading"><p>Loading student profile...</p></div>'
            );
            $('#table-profile-overlay').show();
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_student_profile_overlay',
                    student_id: studentId,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#table-profile-overlay .profile-overlay-content').html(response.data.html);
                    } else {
                        $('#table-profile-overlay .profile-overlay-content').html(
                            '<div class="profile-error">Error loading profile: ' + response.data + '</div>'
                        );
                    }
                },
                error: function() {
                    $('#table-profile-overlay .profile-overlay-content').html(
                        '<div class="profile-error">Failed to load student profile. Please try again.</div>'
                    );
                }
            });
        }
    });
    </script>
    
    <style>
    /* Override browser default table styling */
    .students-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }

    .students-table,
    .students-table th,
    .students-table td {
        border: none !important;
        border-bottom: 1px solid #e5e5e5 !important;
        box-shadow: none !important;
        background-clip: padding-box !important;
    }

    .students-table th,
    .students-table td {
        padding: 12px !important;
        vertical-align: middle !important;
        text-align: left !important;
        font-size: 14px !important;
        color: #333 !important;
    }

    .students-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .students-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    .students-table tbody tr:nth-child(even) {
        background-color: #ffffff !important;
    }

    .students-table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    /* Modern Minimalistic Black & White Styles */
    .wcb-student-table {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: white;
        border: 1px solid #e5e5e5;
        overflow: hidden;
    }
    
    .student-table-header {
        background: #000000;
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .student-table-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    
    .student-table-header h3 .dashicons {
        font-size: 20px;
        color: white;
    }
    
    .table-controls {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .membership-filter {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .membership-filter select {
        padding: 10px 12px;
        border: 1px solid #e5e5e5;
        background: white;
        color: #000000;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        min-width: 140px;
        border-radius: 4px;
        cursor: pointer;
        transition: border-color 0.2s ease;
    }
    
    .membership-filter select:focus {
        border-color: #000000;
    }
    
    .membership-filter select:hover {
        border-color: #666666;
    }
    
    .search-input-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #666666;
        font-size: 16px;
        pointer-events: none;
    }
    
    .table-search-input {
        padding: 10px 16px 10px 40px !important;
        border: 1px solid #e5e5e5 !important;
        background: white;
        color: #000000;
        font-size: 14px;
        width: 240px !important;
        outline: none;
        transition: border-color 0.2s ease;
    }
    
    .table-search-input:focus {
        border-color: #000000;
    }
    
    .table-search-input::placeholder {
        color: #666666;
    }
    
    .entries-per-page {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .entries-per-page label {
        color: white;
        font-size: 14px;
        font-weight: 500;
    }
    
    .entries-per-page select {
        padding: 8px 12px;
        border: 1px solid #e5e5e5;
        background: white;
        color: #000000;
        font-size: 14px;
        outline: none;
        min-width: 60px;
    }
    
    .student-table-content {
        background: white;
    }
    
    .students-table .sessions-count {
      color: black !important;
    }
    .students-table th {
        background: #f8f9fa;
        color: #000000;
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid #e5e5e5;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .students-table th .dashicons {
        font-size: 16px;
        margin-right: 6px;
        vertical-align: middle;
        color: #666666;
    }
    
    .students-table td {
        padding: 8px 16px;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        color: #000000;
    }
    .students-table td:nth-child(6) {
      text-align: center;
    }

    .students-table tr:hover {
        background: #fafafa;
    }
    
    .student-name {
        font-weight: 600;
        color: #000000;
    }
    
    .membership-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.1);
        margin-right: 4px;
        margin-bottom: 2px;
        white-space: nowrap;
    }
    
    .students-table td:nth-child(3) {
        max-width: 200px;
        line-height: 1.4;
    }
    
    .students-table td:nth-child(3) .membership-badge:last-child {
        margin-right: 0;
    }
    
    .sessions-count {
        font-weight: 600;
        color: #000000;
    }
    
    .student-table-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #000000;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .student-table-view-btn:hover {
        background: #333333;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    #table-loading {
        padding: 40px;
        text-align: center;
        color: #000000;
        font-size: 14px;
        font-weight: 500;
    }
    
    .no-results {
        text-align: center;
        color: #666666;
        font-style: italic;
        padding: 40px !important;
    }
    
    .error {
        text-align: center;
        color: #000000;
        background: #f8f9fa;
        padding: 20px !important;
    }
    
    /* Pagination Styles */
    .pagination {
        background: white;
        padding: 20px 24px;
        border-top: 1px solid #e5e5e5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .pagination-info {
        color: #666666;
        font-size: 14px;
        font-weight: 500;
    }
    
    .pagination-controls {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    
    .pagination-btn {
        padding: 8px 12px;
        background: white;
        color: #000000;
        border: 1px solid #e5e5e5;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    
    .pagination-btn .dashicons {
        font-size: 14px;
        line-height: 1;
    }
    
    .pagination-btn:hover:not(.disabled):not(.current) {
        background: #000000;
        color: white;
        border-color: #000000;
    }
    
    .pagination-btn.current {
        background: #000000;
        color: white;
        border-color: #000000;
    }
    
    .pagination-btn.disabled {
        background: #f8f9fa;
        color: #cccccc;
        cursor: not-allowed;
        border-color: #e5e5e5;
    }
    
    .pagination-ellipsis {
        padding: 8px 4px;
        color: #666666;
        font-weight: 500;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .student-table-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .table-controls {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }
        
        .membership-filter {
            order: 1;
        }
        
        .membership-filter select {
            width: 100%;
        }
        
        .table-search {
            order: 2;
        }
        
        .table-search-input {
            width: 100%;
        }
        
        .entries-per-page {
            order: 3;
        }
        
        .students-table {
            font-size: 13px;
        }
        
        .students-table th,
        .students-table td {
            padding: 12px 16px;
        }
        
        .pagination {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .pagination-controls {
            justify-content: center;
        }
    }
    
    @media (max-width: 600px) {
        .table-controls {
            gap: 8px;
        }
        
        .membership-filter select {
            font-size: 13px;
            padding: 8px 10px;
        }
        
        .students-table td:nth-child(3) {
            max-width: 150px;
        }
        
        .membership-badge {
            font-size: 9px;
            padding: 1px 6px;
            margin-right: 2px;
            margin-bottom: 1px;
        }
        
        .students-table th:nth-child(4),
        .students-table td:nth-child(4),
        .students-table th:nth-child(5),
        .students-table td:nth-child(5) {
            display: none;
        }
        
        .students-table th,
        .students-table td {
            padding: 10px 12px;
        }
        
        .table-search-input {
            width: 180px;
        }
        
        .pagination-prev .dashicons,
        .pagination-next .dashicons {
            display: none;
        }
        
        .students-table th .dashicons {
            font-size: 14px;
            margin-right: 4px;
        }
    }
    
    /* Profile Overlay Styles */
    .table-profile-overlay {
        background: white;
        min-height: 400px;
    }
    
    .profile-overlay-content {
        padding: 0;
    }
    
    .profile-loading {
        padding: 60px 24px;
        text-align: center;
        color: #666666;
        font-size: 16px;
    }
    
    .profile-error {
        padding: 60px 24px;
        text-align: center;
        color: #000000;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        margin: 20px;
    }
    
    .overlay-student-profile {
        background: white;
    }
    
    .overlay-profile-header {
        background: #000000;
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .overlay-profile-info {
        flex: 1;
        min-width: 300px;
    }
    
    .overlay-profile-info h2 {
        margin: 0 0 12px 0;
        font-size: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .overlay-profile-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
        opacity: 0.9;
    }
    
    .overlay-profile-actions {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    
    .back-to-students-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: white;
        color: #000000;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .back-to-students-btn:hover {
        background: #f0f0f0;
        color: #000000;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .overlay-profile-content {
        padding: 20px;
    }
    
    .overlay-profile-section {
        margin-bottom: 24px;
    }
    
    .overlay-profile-section:last-child {
        margin-bottom: 0;
    }
    
    .overlay-section-title {
        font-size: 16px !important;
        font-weight: 600;
        margin-bottom: 16px;
        color: #000000;
        border-bottom: 2px solid #000000;
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .overlay-three-column-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .info-column {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        overflow: hidden;
    }
    
    .column-header {
        background: #f8f9fa;
        padding: 12px 16px;
        margin: 0;
        font-size: 12px !important;
        font-weight: 600;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e5e5e5;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .column-header .dashicons {
        font-size: 14px;
        color: #666666;
    }
    
    .column-content {
        padding: 0;
    }
    
    .info-row {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f1f1;
        transition: background-color 0.2s ease;
    }
    
    .info-row:hover {
        background-color: #fafafa;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #000000;
        font-size: 10px;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        color: #666666;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.3;
        word-break: break-word;
    }
    
    .medical-note {
        min-height: 80px;
    }
    
    .medical-text {
        max-height: 120px;
        overflow-y: auto;
        white-space: pre-wrap;
    }
    
    .info-value.not-provided {
        color: #999999;
        font-style: italic;
    }
    
    /* Legacy styles for backward compatibility */
    .overlay-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .overlay-info-item {
        background: #f8f9fa;
        padding: 16px;
        border-left: 4px solid #000000;
    }
    
    .overlay-info-label {
        font-weight: 600;
        color: #000000;
        font-size: 12px;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .overlay-info-value {
        color: #666666;
        font-size: 16px;
        font-weight: 500;
    }
    
    .overlay-membership-item {
        padding: 16px;
        margin-bottom: 12px;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .overlay-membership-name {
        font-weight: 600;
        margin-bottom: 4px;
        font-size: 16px;
    }
    
    .overlay-membership-status {
        font-size: 14px;
    }
    
    .overlay-sessions-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        font-size: 14px;
    }
    
    .overlay-sessions-table th,
    .overlay-sessions-table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .overlay-sessions-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #000000;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .overlay-sessions-table tr:hover {
        background: #fafafa;
    }
    
    .overlay-sessions-table th:nth-child(5),
    .overlay-sessions-table td:nth-child(5) {
        text-align: center;
        min-width: 100px;
    }
    
    .overlay-sessions-table th:nth-child(6),
    .overlay-sessions-table td:nth-child(6) {
        text-align: center;
        min-width: 80px;
    }
    
    .overlay-sessions-table td:nth-child(5) {
        font-weight: 500;
        color: #000000;
    }
    
    /* Intervention styling */
    .overlay-sessions-table td.intervention-yes,
    .overlay-sessions-table td.intervention-full {
        color: #856404;
        font-weight: 600;
    }
    
    .overlay-sessions-table td.intervention-no {
        color: #666666;
        font-weight: 400;
    }
    
    /* Intervention link styling */
    .intervention-link {
        color: #856404 !important;
        text-decoration: none;
        font-weight: 600;
        border-bottom: 1px dotted #856404;
        transition: all 0.2s ease;
    }
    
    .intervention-link:hover {
        color: #5a4104 !important;
        text-decoration: none;
        border-bottom: 1px solid #5a4104;
    }
    
    /* Session view button styling */
    .session-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        background: #000000;
        color: white !important;
        text-decoration: none;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
    }
    
    .session-view-btn:hover {
        background: #333333;
        color: white !important;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .session-view-btn .dashicons {
        font-size: 12px;
        line-height: 1;
        height: auto;
    }
    
    .overlay-no-sessions {
        text-align: center;
        color: #666666;
        padding: 40px;
        font-style: italic;
        background: #f8f9fa;
    }
    
         /* Overlay Responsive Styles */
     @media (max-width: 1200px) {
         .overlay-three-column-grid {
             grid-template-columns: 1fr 1fr;
             gap: 14px;
         }
         
         .info-column:last-child {
             grid-column: 1 / -1;
         }
     }
     
     @media (max-width: 768px) {
        .overlay-profile-header {
            flex-direction: column;
            gap: 16px;
        }
        
        .overlay-profile-info {
            min-width: auto;
        }
        
        .overlay-profile-info h2 {
            font-size: 20px;
        }
        
        .overlay-profile-meta {
            gap: 12px;
            font-size: 13px;
        }
        
                 .overlay-profile-content {
             padding: 16px;
         }
         
         .overlay-three-column-grid {
             grid-template-columns: 1fr;
             gap: 12px;
         }
         
         .info-row {
             padding: 10px 14px;
         }
         
         .column-header {
             padding: 10px 14px;
         }
        
        .overlay-info-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .overlay-sessions-table {
            font-size: 13px;
        }
        
        .overlay-sessions-table th,
        .overlay-sessions-table td {
            padding: 8px 12px;
        }
    }
    
    @media (max-width: 768px) {
        .overlay-sessions-table {
            font-size: 13px;
        }
        
        .overlay-sessions-table th,
        .overlay-sessions-table td {
            padding: 8px 10px;
        }
        
        .session-view-btn {
            padding: 4px 6px;
            font-size: 10px;
        }
        
        .session-view-btn .dashicons {
            font-size: 11px;
        }
    }
    
    @media (max-width: 600px) {
        .overlay-sessions-table th:nth-child(3),
        .overlay-sessions-table td:nth-child(3) {
            display: none;
        }
        
        .overlay-sessions-table th:nth-child(5),
        .overlay-sessions-table td:nth-child(5) {
            min-width: 80px;
            font-size: 12px;
        }
        
        .overlay-sessions-table th:nth-child(6),
        .overlay-sessions-table td:nth-child(6) {
            min-width: 60px;
        }
        
        .session-view-btn {
            padding: 3px 6px;
            font-size: 10px;
        }
        
        .session-view-btn .dashicons {
            font-size: 11px;
        }
        
        .overlay-profile-meta {
            flex-direction: column;
            gap: 8px;
        }
    }
    
    @media (max-width: 480px) {
        .overlay-sessions-table th:nth-child(4),
        .overlay-sessions-table td:nth-child(4) {
            display: none;
        }
        
        .session-view-btn {
            padding: 2px 4px;
            font-size: 9px;
            gap: 2px;
        }
        
        .session-view-btn .dashicons {
            font-size: 10px;
        }
        
        .intervention-link {
            font-size: 11px;
        }
    }
     
     /* Membership Color Utility Classes - for reuse across dashboard */
     .membership-color-mini-cadet-boys { background-color: #CFF5D1 !important; color: #000000 !important; }
     .membership-color-cadet-boys-group1 { background-color: #FFE0CC !important; color: #000000 !important; }
     .membership-color-cadet-boys-group2 { background-color: #E0DAFD !important; color: #000000 !important; }
     .membership-color-youth-boys-group1 { background-color: #A0C6FF !important; color: #000000 !important; }
     .membership-color-youth-boys-group2 { background-color: #FFB68E !important; color: #000000 !important; }
     .membership-color-mini-cadet-girls { background-color: #D1E2FF !important; color: #000000 !important; }
     .membership-color-youth-girls { background-color: #C1F5F0 !important; color: #000000 !important; }
     .membership-color-elite { background-color: #FFB68E !important; color: #000000 !important; }
     .membership-color-sparring { background-color: #FFB68E !important; color: #000000 !important; }
     .membership-color-mentoring { background-color: #E0DAFD !important; color: #000000 !important; }
     .membership-color-comp-team { background-color: #9AE095 !important; color: #000000 !important; }
     .membership-color-waitlist { background-color: #999999 !important; color: #ffffff !important; }
     </style>
    <?php
    return ob_get_clean();
}
add_shortcode('student_table', 'student_table_shortcode');

// Helper function to get membership colors
function wcb_get_membership_color($membership_name) {
    $membership_name = strtolower(trim($membership_name));
    
    // Define membership color mapping
    $color_map = [
        // Mini Cadet Boys
        'mini cadet boys (9-11 years) group 1' => '#CFF5D1',
        'mini cadet boys (9-11 years) group 2' => '#CFF5D1',
        'mini cadet boys (9-11 years) waitlist' => '#999999',
        
        // Cadet Boys
        'cadet boys (12-14 years) group 1' => '#FFE0CC',
        'cadet boys (12-14 years) group 2' => '#E0DAFD',
        'cadet boys (12-14 years) waitlist' => '#999999',
        
        // Youth Boys
        'youth boys (15-18 years) group 1' => '#A0C6FF',
        'youth boys (15-18 years) group 2' => '#FFB68E',
        'youth boys (15-18 years) waitlist' => '#999999',
        
        // Mini Cadet Girls
        'mini cadet girls (9-12 years) group 1' => '#D1E2FF',
        'mini cadet girls (9-12 years) group 2' => '#D1E2FF',
        'mini cadet girls (9-12 years) waitlist' => '#999999',
        
        // Youth Girls
        'youth girls (13-18 years) group 1' => '#C1F5F0',
        'youth girls (13-18 years) group 2' => '#C1F5F0',
        'youth girls (13-18 years) waitlist' => '#999999',
        
        // Elite
        'elite 1 (18+ years) group 1' => '#FFB68E',
        'elite (18+ years) waitlist' => '#999999',
        
        // Special Programs
        'sparring' => '#FFB68E',
        'wcb mentoring' => '#E0DAFD',
        'comp team' => '#9AE095',
    ];
    
    // First try exact match
    if (isset($color_map[$membership_name])) {
        return $color_map[$membership_name];
    }
    
    // Try partial matches for flexibility
    foreach ($color_map as $key => $color) {
        if (strpos($membership_name, $key) !== false || strpos($key, $membership_name) !== false) {
            return $color;
        }
    }
    
    // Try keyword matching for common patterns
    if (strpos($membership_name, 'waitlist') !== false) {
        return '#999999';
    }
    
    if (strpos($membership_name, 'mini cadet boys') !== false) {
        return '#CFF5D1';
    }
    
    if (strpos($membership_name, 'cadet boys') !== false && strpos($membership_name, 'group 1') !== false) {
        return '#FFE0CC';
    }
    
    if (strpos($membership_name, 'cadet boys') !== false && strpos($membership_name, 'group 2') !== false) {
        return '#E0DAFD';
    }
    
    if (strpos($membership_name, 'youth boys') !== false && strpos($membership_name, 'group 1') !== false) {
        return '#A0C6FF';
    }
    
    if (strpos($membership_name, 'youth boys') !== false && strpos($membership_name, 'group 2') !== false) {
        return '#FFB68E';
    }
    
    if (strpos($membership_name, 'mini cadet girls') !== false) {
        return '#D1E2FF';
    }
    
    if (strpos($membership_name, 'youth girls') !== false) {
        return '#C1F5F0';
    }
    
    if (strpos($membership_name, 'elite') !== false) {
        return '#FFB68E';
    }
    
    if (strpos($membership_name, 'sparring') !== false) {
        return '#FFB68E';
    }
    
    if (strpos($membership_name, 'mentoring') !== false) {
        return '#E0DAFD';
    }
    
    if (strpos($membership_name, 'comp team') !== false) {
        return '#9AE095';
    }
    
    // Default color for unknown memberships
    return '#f8f9fa';
}

// Helper function to determine if text should be dark or light based on background color
function wcb_get_text_color_for_background($hex_color) {
    // Remove # if present
    $hex_color = ltrim($hex_color, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex_color, 0, 2));
    $g = hexdec(substr($hex_color, 2, 2));
    $b = hexdec(substr($hex_color, 4, 2));
    
    // Calculate brightness
    $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    
    // Return dark text for light backgrounds, light text for dark backgrounds
    return $brightness > 128 ? '#000000' : '#ffffff';
}

// Helper function to get CSS class name for membership (for reuse across dashboard)
function wcb_get_membership_css_class($membership_name) {
    $membership_name = strtolower(trim($membership_name));
    
    if (strpos($membership_name, 'waitlist') !== false) {
        return 'membership-color-waitlist';
    }
    
    if (strpos($membership_name, 'mini cadet boys') !== false) {
        return 'membership-color-mini-cadet-boys';
    }
    
    if (strpos($membership_name, 'cadet boys') !== false && strpos($membership_name, 'group 1') !== false) {
        return 'membership-color-cadet-boys-group1';
    }
    
    if (strpos($membership_name, 'cadet boys') !== false && strpos($membership_name, 'group 2') !== false) {
        return 'membership-color-cadet-boys-group2';
    }
    
    if (strpos($membership_name, 'youth boys') !== false && strpos($membership_name, 'group 1') !== false) {
        return 'membership-color-youth-boys-group1';
    }
    
    if (strpos($membership_name, 'youth boys') !== false && strpos($membership_name, 'group 2') !== false) {
        return 'membership-color-youth-boys-group2';
    }
    
    if (strpos($membership_name, 'mini cadet girls') !== false) {
        return 'membership-color-mini-cadet-girls';
    }
    
    if (strpos($membership_name, 'youth girls') !== false) {
        return 'membership-color-youth-girls';
    }
    
    if (strpos($membership_name, 'elite') !== false) {
        return 'membership-color-elite';
    }
    
    if (strpos($membership_name, 'sparring') !== false) {
        return 'membership-color-sparring';
    }
    
    if (strpos($membership_name, 'mentoring') !== false) {
        return 'membership-color-mentoring';
    }
    
    if (strpos($membership_name, 'comp team') !== false) {
        return 'membership-color-comp-team';
    }
    
    return '';
}

// Helper function to get session count for a user
function wcb_get_user_session_count($user_id) {
    // Get all sessions
    $all_sessions = get_posts([
        'post_type' => 'session_log',
        'numberposts' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'associated_student',
                'value' => $user_id,
                'compare' => 'LIKE'
            ],
            [
                'key' => 'student_involved',
                'value' => $user_id,
                'compare' => 'LIKE'
            ]
        ]
    ]);
    
    $session_count = 0;
    
    foreach ($all_sessions as $session) {
        $session_id = $session->ID;
        
        // Get session type to determine if it's mentoring/intervention
        $session_type_data = wcb_get_session_type($session_id);
        $session_type_slug = $session_type_data['slug'];
        
        if ($session_type_slug === 'mentoring') {
            // For mentoring sessions, check if this user is the student involved
            $student_involved = get_field('student_involved', $session_id);
            if ($student_involved == $user_id) {
                $session_count++;
            }
        } else {
            // For regular sessions, check attendance or association
            $attendance_data = wcb_get_session_attendance($session_id);
            $attended_students = $attendance_data['attended'];
            $excused_students = $attendance_data['excused'];
            
            // Check associated student
            $associated_student_raw = get_field('associated_student', $session_id);
            $associated_student = null;
            if (is_object($associated_student_raw) && isset($associated_student_raw->ID)) {
                $associated_student = $associated_student_raw->ID;
            } elseif (is_array($associated_student_raw) && isset($associated_student_raw['ID'])) {
                $associated_student = $associated_student_raw['ID'];
            } elseif (is_numeric($associated_student_raw)) {
                $associated_student = intval($associated_student_raw);
            }
            
            // Count if student attended, was excused, or was associated
            if (in_array($user_id, $attended_students) || 
                in_array($user_id, $excused_students) || 
                ($associated_student && $associated_student == $user_id)) {
                $session_count++;
            }
        }
    }
    
    return $session_count;
}

// AJAX Handler for loading students table
function wcb_ajax_load_students_table() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $page = max(1, intval($_POST['page']));
    $per_page = max(1, min(100, intval($_POST['per_page'])));
    $search = sanitize_text_field($_POST['search']);
    $membership_status = sanitize_text_field($_POST['membership_status']);
    
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    $users = [];
    $total_users = 0;
    
    if ($table_exists) {
        // Use EXACT same logic as active-members-test.php
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

        if ($membership_status === 'active') {
            // Use EXACT same two-step approach as active-members-test.php
            $wcb_mentoring_id = 1738;

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

            // Convert to array
            $filtered_members = array_values($all_program_members);

            if (empty($filtered_members)) {
                $query = "SELECT * FROM {$wpdb->users} WHERE 1=0"; // Return empty result
            } else {
                $member_ids = array_map(function($user) { return $user->ID; }, $filtered_members);
                $id_placeholders = implode(',', array_map('intval', $member_ids));
                $query = "
                    SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                    FROM {$wpdb->users} u
                    WHERE u.ID IN ({$id_placeholders})
                ";
            }
            
        } elseif ($membership_status === 'waitlist') {
            // Simple waitlist logic - completely separate from program groups
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                JOIN {$wpdb->posts} p ON t.product_id = p.ID
                WHERE t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                AND p.post_type = 'memberpressproduct'
                AND p.post_title LIKE '%waitlist%'
                AND u.user_login != 'bwgdev'
            ";

        } elseif ($membership_status === 'inactive') {
            // Inactive members: users with expired or cancelled transactions
            $wcb_mentoring_id = 1738;
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                WHERE (
                    t.status IN ('cancelled', 'failed', 'refunded')
                    OR (t.expires_at IS NOT NULL AND t.expires_at != '0000-00-00 00:00:00' AND t.expires_at <= NOW())
                )
                AND t.product_id != {$wcb_mentoring_id}
                AND u.user_login != 'bwgdev'
                AND u.ID NOT IN (
                    SELECT DISTINCT u2.ID
                    FROM {$wpdb->users} u2
                    JOIN {$txn_table} t2 ON u2.ID = t2.user_id
                    WHERE t2.status IN ('confirmed', 'complete')
                    AND (t2.expires_at IS NULL OR t2.expires_at > NOW() OR t2.expires_at = '0000-00-00 00:00:00')
                    AND t2.product_id != {$wcb_mentoring_id}
                )
            ";

        } else {
            // All members: get ALL active members (not just program groups)
            $wcb_mentoring_id = 1738;
            $query = "
                SELECT DISTINCT u.ID, u.display_name, u.user_email, u.user_registered
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                WHERE t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                AND t.product_id != {$wcb_mentoring_id}
                AND u.user_login != 'bwgdev'
            ";
        }
        
        // Add search filter
        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $query .= $wpdb->prepare(" AND (u.display_name LIKE %s OR u.user_email LIKE %s OR u.user_login LIKE %s)", $search_term, $search_term, $search_term);
        }
        
        // Get total count
        $count_query = preg_replace('/SELECT DISTINCT u\.ID, u\.display_name, u\.user_email, u\.user_registered/', 'SELECT COUNT(DISTINCT u.ID)', $query);
        $total_users = $wpdb->get_var($count_query);
        
        // Get users for current page
        $offset = ($page - 1) * $per_page;
        $query .= " ORDER BY u.display_name ASC LIMIT {$per_page} OFFSET {$offset}";
        
        $user_results = $wpdb->get_results($query);
        
        // Use the data we already have from the SQL query
        foreach ($user_results as $user_result) {
            // Create a user-like object with the data we need
            $user_obj = new stdClass();
            $user_obj->ID = $user_result->ID;
            $user_obj->display_name = $user_result->display_name;
            $user_obj->user_email = $user_result->user_email;
            $user_obj->user_registered = $user_result->user_registered;
            $users[] = $user_obj;
        }
        
    } else {
        // Fallback: use WordPress user query if MemberPress tables don't exist
        $args = [
            'role__in' => ['subscriber', 'member', 'customer'],
            'number' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'orderby' => 'display_name',
            'order' => 'ASC'
        ];
        
        // Add search if provided
        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['display_name', 'user_email', 'user_login'];
        }
        
        $users = get_users($args);
        
        // Get total count for pagination
        $count_args = $args;
        unset($count_args['number'], $count_args['offset']);
        $total_users = count(get_users($count_args));
    }
    
    // Generate table rows
    $rows_html = '';
    if (!empty($users)) {
        foreach ($users as $user) {
            // Skip if user object is invalid
            if (!$user || !is_object($user)) {
                continue;
            }
            
            // Get all active memberships for this user
            $membership_info = wcb_get_user_all_memberships($user->ID);
            
            // Get session count
            $session_count = wcb_get_user_session_count($user->ID);
            
            $join_date = date('M j, Y', strtotime($user->user_registered));
            
            $rows_html .= '<tr>';
            $rows_html .= '<td><div class="student-name">' . esc_html($user->display_name) . '</div></td>';
            $rows_html .= '<td>' . esc_html($user->user_email) . '</td>';
            $rows_html .= '<td>' . $membership_info . '</td>';
            $rows_html .= '<td><span class="sessions-count">' . esc_html($session_count) . '</span></td>';
            $rows_html .= '<td>' . esc_html($join_date) . '</td>';
            $rows_html .= '<td><button class="student-table-view-btn" data-student-id="' . $user->ID . '">View</button></td>';
            $rows_html .= '</tr>';
        }
    } else {
        $status_text = '';
        switch ($membership_status) {
            case 'active':
                $status_text = 'active ';
                break;
            case 'inactive':
                $status_text = 'inactive ';
                break;
            case 'waitlist':
                $status_text = 'waitlist ';
                break;
        }
        $rows_html = '<tr><td colspan="6" class="no-results">No ' . $status_text . 'students found' . (!empty($search) ? ' matching "' . esc_html($search) . '"' : '') . '</td></tr>';
    }
    
    // Generate pagination info
    $start = ($page - 1) * $per_page + 1;
    $end = min($page * $per_page, $total_users);
    $pagination_info = "Showing {$start} - {$end} of {$total_users} students";
    
    // Generate pagination controls
    $total_pages = ceil($total_users / $per_page);
    $pagination_controls = '';
    
    if ($total_pages > 1) {
        // Previous button
        $prev_disabled = ($page <= 1) ? 'disabled' : '';
        $pagination_controls .= '<button class="pagination-btn pagination-prev ' . $prev_disabled . '" data-page="' . ($page - 1) . '" ' . ($prev_disabled ? 'disabled' : '') . '><span class="dashicons dashicons-arrow-left-alt2"></span> Previous</button>';
        
        // Page numbers
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
            $pagination_controls .= '<button class="pagination-btn" data-page="1">1</button>';
            if ($start_page > 2) {
                $pagination_controls .= '<span class="pagination-ellipsis">...</span>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $current_class = ($i == $page) ? 'current' : '';
            $pagination_controls .= '<button class="pagination-btn ' . $current_class . '" data-page="' . $i . '">' . $i . '</button>';
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $pagination_controls .= '<span class="pagination-ellipsis">...</span>';
            }
            $pagination_controls .= '<button class="pagination-btn" data-page="' . $total_pages . '">' . $total_pages . '</button>';
        }
        
        // Next button
        $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
        $pagination_controls .= '<button class="pagination-btn pagination-next ' . $next_disabled . '" data-page="' . ($page + 1) . '" ' . ($next_disabled ? 'disabled' : '') . '>Next <span class="dashicons dashicons-arrow-right-alt2"></span></button>';
    }
    
    wp_send_json_success([
        'rows' => $rows_html,
        'pagination_info' => $pagination_info,
        'pagination_controls' => $pagination_controls,
        'total_users' => $total_users,
        'current_page' => $page,
        'total_pages' => $total_pages
    ]);
}
add_action('wp_ajax_wcb_load_students_table', 'wcb_ajax_load_students_table');
add_action('wp_ajax_nopriv_wcb_load_students_table', 'wcb_ajax_load_students_table');

// Helper function to get group for a membership ID
function wcb_get_membership_group($membership_id) {
    $group_id = get_post_meta($membership_id, '_mepr_group_id', true);
    if ($group_id) {
        $group = get_post($group_id);
        if ($group && $group->post_type === 'memberpressgroup') {
            return $group;
        }
    }
    return false;
}

// Helper function to get all groups
function wcb_get_all_groups() {
    return get_posts([
        'post_type' => 'memberpressgroup',
        'post_status' => ['publish', 'private'], // Include both publish and private groups
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
}

// Helper function to get all memberships within a group
function wcb_get_group_memberships($group_id) {
    global $wpdb;

    $memberships = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_mepr_group_id'
        AND pm.meta_value = %d
        AND p.post_type = 'memberpressproduct'
        AND p.post_status IN ('publish', 'private')
        ORDER BY p.post_title
    ", $group_id));

    return $memberships;
}

// Helper function to get total member count for a group (across all its memberships)
function wcb_get_group_member_count($group_id) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return 0;
    }

    // Get all membership IDs in this group
    $membership_ids = $wpdb->get_col($wpdb->prepare("
        SELECT p.ID
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_mepr_group_id'
        AND pm.meta_value = %d
        AND p.post_type = 'memberpressproduct'
        AND p.post_status = 'publish'
    ", $group_id));

    if (empty($membership_ids)) {
        return 0;
    }

    // Count unique users with active transactions for any membership in this group
    // Use EXACT same logic as active-members-test.php
    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));
    $query = "
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
    ";

    return (int) $wpdb->get_var($wpdb->prepare($query, ...$membership_ids));
}

// Helper function to get all active groups for a user (based on their memberships)
function wcb_get_user_groups($user_id) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    // Get all active memberships for this user and their associated groups
    $groups = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT g.ID, g.post_title as group_name
        FROM {$wpdb->posts} g
        JOIN {$wpdb->postmeta} pm ON g.ID = pm.meta_value
        JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        JOIN {$txn_table} t ON p.ID = t.product_id
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND p.post_type = 'memberpressproduct'
        AND g.post_type = 'memberpressgroup'
        AND pm.meta_key = '_mepr_group_id'
        ORDER BY g.post_title
    ", $user_id));

    return $groups;
}

// Helper function to infer group name from membership name
function wcb_infer_group_from_membership($membership_name) {
    // Pattern matching to extract group information from membership names
    $patterns = [
        // Pattern: "Cadet Boys (12-14 Years) Group 1 Monthly" -> "Cadet Boys Group 1"
        '/^(Cadet Boys) \([^)]+\) (Group \d+) .*$/' => '$1 $2',

        // Pattern: "Youth Boys (15-18 Years) Group 2 Monthly" -> "Youth Boys Group 2"
        '/^(Youth Boys) \([^)]+\) (Group \d+) .*$/' => '$1 $2',

        // Pattern: "Youth Girls (13-18 Years) Group 1 Monthly" -> "Youth Girls Group 1"
        '/^(Youth Girls) \([^)]+\) (Group \d+) .*$/' => '$1 $2',

        // Pattern: "Mini Cadet Boys (9-11 Years) Group 1 Monthly" -> "Mini Cadet Boys (9-11 Years) Group 1"
        '/^(Mini Cadet Boys \([^)]+\)) (Group \d+) .*$/' => '$1 $2',

        // Pattern: "Mini Cadet Girls (9-12 Years) Group 1 Monthly" -> "Mini Cadets Girls Group 1"
        '/^(Mini Cadet Girls) \([^)]+\) (Group \d+) .*$/' => 'Mini Cadets Girls $2',
    ];

    foreach ($patterns as $pattern => $replacement) {
        if (preg_match($pattern, $membership_name)) {
            return preg_replace($pattern, $replacement, $membership_name);
        }
    }

    // If no pattern matches, return the original name
    return $membership_name;
}

// Helper function to get all active memberships for a user as group badges
function wcb_get_user_all_memberships($user_id) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return '<span style="color: #666666;">No active membership</span>';
    }

    // Get all active memberships for this user with their group associations
    $memberships = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT p.ID, p.post_title
        FROM {$wpdb->posts} p
        JOIN {$txn_table} t ON p.ID = t.product_id
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND p.post_type = 'memberpressproduct'
        ORDER BY p.post_title
    ", $user_id));

    if (empty($memberships)) {
        return '<span style="color: #666666;">No active membership</span>';
    }

    $display_items = [];
    $processed_groups = [];

    foreach ($memberships as $membership) {
        // Check if this membership belongs to a group
        $group_id = get_post_meta($membership->ID, '_mepr_group_id', true);

        if ($group_id && !in_array($group_id, $processed_groups)) {
            // This membership belongs to a group, show the group name
            $group = get_post($group_id);
            if ($group && $group->post_type === 'memberpressgroup') {
                $display_name = $group->post_title;
                $processed_groups[] = $group_id; // Avoid showing the same group multiple times
            } else {
                // Group not found, fall back to inferred group name
                $display_name = wcb_infer_group_from_membership($membership->post_title);
            }
        } else if (!$group_id) {
            // This membership is not part of any group, try to infer the group name
            $inferred_group = wcb_infer_group_from_membership($membership->post_title);

            // Check if we've already processed this inferred group
            if (!in_array($inferred_group, $processed_groups)) {
                $display_name = $inferred_group;
                $processed_groups[] = $inferred_group;
            } else {
                // This inferred group was already processed, skip
                continue;
            }
        } else {
            // This group was already processed, skip
            continue;
        }

        $bg_color = wcb_get_membership_color($display_name);
        $text_color = wcb_get_text_color_for_background($bg_color);

        $display_items[] = '<span class="membership-badge" style="background-color: ' . esc_attr($bg_color) . '; color: ' . esc_attr($text_color) . ';">' . esc_html($display_name) . '</span>';
    }

    if (empty($display_items)) {
        return '<span style="color: #666666;">No active membership</span>';
    }

    return implode(' ', $display_items);
}

// Function to associate Monthly memberships with their corresponding groups
function wcb_associate_monthly_memberships_with_groups() {
    global $wpdb;

    // Define the mapping of monthly membership patterns to group IDs
    $monthly_to_group_mapping = [
        // Cadet Boys Group 1 (ID: 1786)
        'Cadet Boys (12-14 Years) Group 1 Monthly' => 1786,

        // Cadet Boys Group 2 (ID: 1790)
        'Cadet Boys (12-14 Years) Group 2 Monthly' => 1790,

        // Youth Boys Group 1 (ID: 1803)
        'Youth Boys (15-18 Years) Group 1 Monthly' => 1803,

        // Youth Boys Group 2 (ID: 1809)
        'Youth Boys (15-18 Years) Group 2 Monthly' => 1809,

        // Youth Girls Group 1 (ID: 1815)
        'Youth Girls (13-18 Years) Group 1 Monthly' => 1815,

        // Mini Cadet Boys Group 1 (ID: 1767)
        'Mini Cadet Boys (9-11 Years) Group 1 Monthly' => 1767,

        // Mini Cadets Girls Group 1 (ID: 1812)
        'Mini Cadet Girls (9-12 Years) Group 1 Monthly' => 1812,
    ];

    $updated_count = 0;
    $results = [];

    foreach ($monthly_to_group_mapping as $membership_name => $group_id) {
        // Find the membership by name
        $membership = get_posts([
            'post_type' => 'memberpressproduct',
            'post_status' => 'publish',
            'title' => $membership_name,
            'posts_per_page' => 1
        ]);

        if (!empty($membership)) {
            $membership_id = $membership[0]->ID;

            // Check if it already has a group association
            $existing_group = get_post_meta($membership_id, '_mepr_group_id', true);

            if (!$existing_group) {
                // Associate with the group
                $success = update_post_meta($membership_id, '_mepr_group_id', $group_id);

                if ($success) {
                    $updated_count++;
                    $results[] = "✅ Associated '{$membership_name}' (ID: {$membership_id}) with Group {$group_id}";
                } else {
                    $results[] = "❌ Failed to associate '{$membership_name}' (ID: {$membership_id}) with Group {$group_id}";
                }
            } else {
                $results[] = "ℹ️ '{$membership_name}' (ID: {$membership_id}) already associated with Group {$existing_group}";
            }
        } else {
            $results[] = "⚠️ Membership '{$membership_name}' not found";
        }
    }

    return [
        'updated_count' => $updated_count,
        'results' => $results
    ];
}

// AJAX handler to run the monthly membership association
function wcb_ajax_associate_monthly_memberships() {
    // Verify nonce and permissions
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Security check failed or insufficient permissions');
        return;
    }

    $result = wcb_associate_monthly_memberships_with_groups();

    wp_send_json_success([
        'message' => "Updated {$result['updated_count']} monthly memberships",
        'details' => $result['results']
    ]);
}
add_action('wp_ajax_wcb_associate_monthly_memberships', 'wcb_ajax_associate_monthly_memberships');

// AJAX Handler for loading student profile overlay
function wcb_ajax_load_student_profile_overlay() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $student_id = intval($_POST['student_id']);
    
    if (!$student_id) {
        wp_send_json_error('Invalid student ID');
        return;
    }
    
    $user = get_user_by('ID', $student_id);
    if (!$user) {
        wp_send_json_error('Student not found');
        return;
    }
    
    ob_start();
    ?>
    <div class="overlay-student-profile">
        <div class="overlay-profile-header">
            <div class="overlay-profile-info">
                <div class="overlay-profile-meta">
                    <span><span class="dashicons dashicons-email"></span> <?php echo esc_html($user->user_email); ?></span>
                    <span><span class="dashicons dashicons-calendar-alt"></span> Member since <?php echo date('M j, Y', strtotime($user->user_registered)); ?></span>
                    <span><span class="dashicons dashicons-id"></span> ID: <?php echo $student_id; ?></span>
                </div>
            </div>
            <div class="overlay-profile-actions">
                <button class="back-to-students-btn" onclick="showStudentsTable()">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> All Students
                </button>
            </div>
        </div>
        
        <div class="overlay-profile-content">
            <!-- Student Information -->
            <div class="overlay-profile-section">
                <h3 class="overlay-section-title">
                    <span class="dashicons dashicons-admin-generic"></span> Student Information
                </h3>
                <div class="overlay-three-column-grid">
                    <!-- Student Info Column -->
                    <div class="info-column">
                        <h4 class="column-header">
                            <span class="dashicons dashicons-admin-users"></span> Student Details
                        </h4>
                        <div class="column-content">
                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo esc_html($user->user_email); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_phone_number', true) ? ' not-provided' : ''; ?>"><?php 
                                    $phone = get_user_meta($student_id, 'mepr_phone_number', true);
                                    echo $phone ? esc_html($phone) : 'Not provided';
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Age</div>
                                <div class="info-value<?php 
                                    $age = get_user_meta($student_id, 'mepr_age', true);
                                    $dob = get_user_meta($student_id, 'mepr_date_of_birth', true);
                                    echo (!$age && !$dob) ? ' not-provided' : ''; 
                                ?>"><?php 
                                    $age = get_user_meta($student_id, 'mepr_age', true);
                                    $dob = get_user_meta($student_id, 'mepr_date_of_birth', true);
                                    
                                    if ($age) {
                                        echo esc_html($age . ' years old');
                                    } elseif ($dob) {
                                        $calculated_age = date_diff(date_create($dob), date_create('today'))->y;
                                        echo esc_html($calculated_age . ' years old');
                                    } else {
                                        echo 'Not provided';
                                    }
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Gender</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_gender', true) ? ' not-provided' : ''; ?>"><?php 
                                    $gender = get_user_meta($student_id, 'mepr_gender', true);
                                    echo $gender ? esc_html($gender) : 'Not specified';
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Ethnicity</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_ethnicity', true) ? ' not-provided' : ''; ?>"><?php 
                                    $ethnicity = get_user_meta($student_id, 'mepr_ethnicity', true);
                                    echo $ethnicity ? esc_html($ethnicity) : 'Not specified';
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_date_of_birth', true) ? ' not-provided' : ''; ?>"><?php 
                                    $dob = get_user_meta($student_id, 'mepr_date_of_birth', true);
                                    echo $dob ? date('M j, Y', strtotime($dob)) : 'Not provided';
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Address</div>
                                <div class="info-value<?php 
                                    $address_line1 = get_user_meta($student_id, 'mepr-address-one', true);
                                    $address_line2 = get_user_meta($student_id, 'mepr-address-two', true);
                                    $city = get_user_meta($student_id, 'mepr-address-city', true);
                                    $state = get_user_meta($student_id, 'mepr-address-state', true);
                                    $zip = get_user_meta($student_id, 'mepr-address-zip', true);
                                    $country = get_user_meta($student_id, 'mepr-address-country', true);
                                    
                                    $address_parts = array_filter([$address_line1, $address_line2, $city, $state, $zip, $country]);
                                    echo !$address_parts ? ' not-provided' : ''; 
                                ?>"><?php 
                                    $address_line1 = get_user_meta($student_id, 'mepr-address-one', true);
                                    $address_line2 = get_user_meta($student_id, 'mepr-address-two', true);
                                    $city = get_user_meta($student_id, 'mepr-address-city', true);
                                    $state = get_user_meta($student_id, 'mepr-address-state', true);
                                    $zip = get_user_meta($student_id, 'mepr-address-zip', true);
                                    $country = get_user_meta($student_id, 'mepr-address-country', true);
                                    
                                    $address_parts = array_filter([$address_line1, $address_line2, $city, $state, $zip, $country]);
                                    echo $address_parts ? esc_html(implode(', ', $address_parts)) : 'Not provided';
                                ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Parent/Guardian Info Column -->
                    <div class="info-column">
                        <h4 class="column-header">
                            <span class="dashicons dashicons-groups"></span> Parent/Guardian
                        </h4>
                        <div class="column-content">
                            <div class="info-row">
                                <div class="info-label">Parent/Guardian Name</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_parent_guardian_name', true) ? ' not-provided' : ''; ?>"><?php 
                                    $parent_name = get_user_meta($student_id, 'mepr_parent_guardian_name', true);
                                    echo $parent_name ? esc_html($parent_name) : 'Not provided';
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Parent/Guardian Phone</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_parent_guardian_phone_number', true) ? ' not-provided' : ''; ?>"><?php 
                                    $parent_phone = get_user_meta($student_id, 'mepr_parent_guardian_phone_number', true);
                                    echo $parent_phone ? esc_html($parent_phone) : 'Not provided';
                                ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Parent/Guardian Email</div>
                                <div class="info-value<?php echo !get_user_meta($student_id, 'mepr_parent_guardians_email_address', true) ? ' not-provided' : ''; ?>"><?php 
                                    $parent_email = get_user_meta($student_id, 'mepr_parent_guardians_email_address', true);
                                    echo $parent_email ? esc_html($parent_email) : 'Not provided';
                                ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medical Info Column -->
                    <div class="info-column">
                        <h4 class="column-header">
                            <span class="dashicons dashicons-heart"></span> Medical Information
                        </h4>
                        <div class="column-content">
                            <div class="info-row medical-note">
                                <div class="info-label">Medical Information</div>
                                <div class="info-value medical-text<?php echo !get_user_meta($student_id, 'mepr_medical_information', true) ? ' not-provided' : ''; ?>"><?php 
                                    $medical_info = get_user_meta($student_id, 'mepr_medical_information', true);
                                    echo $medical_info ? esc_html($medical_info) : 'No medical information provided';
                                ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Memberships -->
            <div class="overlay-profile-section">
                <h3 class="overlay-section-title">
                    <span class="dashicons dashicons-awards"></span> Memberships
                </h3>
                <?php
                // Get subscription display using safe helper
                $membership_display = WCB_MemberPress_Helper::get_membership_display($student_id);
                
                                 if ($membership_display !== 'No active membership'): 
                     $bg_color = wcb_get_membership_color($membership_display);
                     $text_color = wcb_get_text_color_for_background($bg_color);
                 ?>
                     <div class="overlay-membership-item" style="background-color: <?php echo esc_attr($bg_color); ?>;">
                         <div class="overlay-membership-name" style="color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($membership_display); ?></div>
                         <div class="overlay-membership-status" style="color: <?php echo esc_attr($text_color); ?>; opacity: 0.8;">Active subscription</div>
                     </div>
                 <?php else: ?>
                     <div class="overlay-info-item">
                         <div class="overlay-info-value">No active memberships found</div>
                     </div>
                 <?php endif; ?>
            </div>
            
            <!-- Session History -->
            <div class="overlay-profile-section">
                <h3 class="overlay-section-title">
                    <span class="dashicons dashicons-chart-bar"></span> Recent Sessions
                </h3>
                <?php
                // Get session count
                $session_count = wcb_get_user_session_count($student_id);
                
                // Get recent sessions for this student
                $all_sessions = get_posts([
                    'post_type' => 'session_log',
                    'numberposts' => 50, // Get more to filter through
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                
                $sessions = [];
                foreach ($all_sessions as $session) {
                    $session_id = $session->ID;
                    
                    // Get session type to determine if it's mentoring/intervention
                    $session_type_data = wcb_get_session_type($session_id);
                    $session_type_slug = $session_type_data['slug'];
                    
                    $is_student_involved = false;
                    
                    if ($session_type_slug === 'mentoring') {
                        // For mentoring sessions, check if this user is the student involved
                        $student_involved = get_field('student_involved', $session_id);
                        if ($student_involved == $student_id) {
                            $is_student_involved = true;
                        }
                    } else {
                        // For regular sessions, check attendance or association
                        $attendance_data = wcb_get_session_attendance($session_id);
                        $attended_students = $attendance_data['attended'];
                        $excused_students = $attendance_data['excused'];
                        
                        // Handle associated student
                        $associated_student_raw = get_field('associated_student', $session_id);
                        $associated_student = null;
                        if (is_object($associated_student_raw) && isset($associated_student_raw->ID)) {
                            $associated_student = $associated_student_raw->ID;
                        } elseif (is_array($associated_student_raw) && isset($associated_student_raw['ID'])) {
                            $associated_student = $associated_student_raw['ID'];
                        } elseif (is_numeric($associated_student_raw)) {
                            $associated_student = intval($associated_student_raw);
                        }
                        
                        // Check if this student is associated with the session
                        $is_associated = ($associated_student && $associated_student == $student_id);
                        $is_attended = in_array($student_id, $attended_students);
                        $is_excused = in_array($student_id, $excused_students);
                        
                        if ($is_associated || $is_attended || $is_excused) {
                            $is_student_involved = true;
                        }
                    }
                    
                    if ($is_student_involved) {
                        $sessions[] = $session;
                        if (count($sessions) >= 10) break; // Limit for overlay
                    }
                }
                
                if (!empty($sessions)): ?>
                <div style="margin-bottom: 16px;">
                    <strong>Total Sessions: <?php echo esc_html($session_count); ?></strong>
                </div>
                <table class="overlay-sessions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Class/Program</th>
                            <th>Attendance</th>
                            <th>Interventions</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <?php 
                        $session_id = $session->ID;
                        
                        // Get session type using helper function
                        $session_type_data = wcb_get_session_type($session_id);
                        $session_type = $session_type_data['name'];
                        $session_type_slug = $session_type_data['slug'];
                        
                        // Determine if this is a mentoring/intervention session
                        $is_mentoring = ($session_type_slug === 'mentoring');
                        
                        if ($is_mentoring) {
                            // For mentoring sessions
                            $date = get_field('intervention_date_', $session_id);
                            $class_name = 'WCB Mentoring';
                            $attendance_status = 'Present (Mentoring)';
                            $session_type_display = 'Mentoring';
                        } else {
                            // For regular sessions
                            $date = get_field('session_date', $session_id);
                            $membership_id = get_field('selected_membership', $session_id);
                            
                            // Get class name
                            $class_name = 'Unknown Class';
                            if ($membership_id) {
                                $membership_post = get_post($membership_id);
                                if ($membership_post) {
                                    $class_name = $membership_post->post_title;
                                }
                            }
                            
                            // Get attendance data for this session
                            $attendance_data = wcb_get_session_attendance($session_id);
                            $attended_students = $attendance_data['attended'];
                            $excused_students = $attendance_data['excused'];
                            
                            // Check associated student
                            $associated_student_raw = get_field('associated_student', $session_id);
                            $associated_student = null;
                            if (is_object($associated_student_raw) && isset($associated_student_raw->ID)) {
                                $associated_student = $associated_student_raw->ID;
                            } elseif (is_array($associated_student_raw) && isset($associated_student_raw['ID'])) {
                                $associated_student = $associated_student_raw['ID'];
                            } elseif (is_numeric($associated_student_raw)) {
                                $associated_student = intval($associated_student_raw);
                            }
                            
                            // Determine attendance status
                            $attendance_status = 'Unknown';
                            if ($associated_student && $associated_student == $student_id) {
                                $attendance_status = 'Present (1-on-1)';
                            } elseif (in_array($student_id, $attended_students)) {
                                $attendance_status = 'Present';
                            } elseif (in_array($student_id, $excused_students)) {
                                $attendance_status = 'Excused';
                            }
                            
                            $session_type_display = $session_type;
                        }
                        
                        // Check for interventions/concerns for this student in this session
                        $intervention_note = '';
                        $intervention_class = '';
                        if (!$is_mentoring) {
                            $intervention_student = get_field('which_student_did_you_have_an_intervention_or_concern_for', $session_id);
                            
                            // Handle different possible data formats for intervention student
                            $intervention_student_id = null;
                            if (is_array($intervention_student)) {
                                if (isset($intervention_student[0])) {
                                    $intervention_student_id = is_object($intervention_student[0]) && isset($intervention_student[0]->ID) ? $intervention_student[0]->ID : $intervention_student[0];
                                } elseif (isset($intervention_student['ID'])) {
                                    $intervention_student_id = $intervention_student['ID'];
                                }
                            } elseif (is_object($intervention_student) && isset($intervention_student->ID)) {
                                $intervention_student_id = $intervention_student->ID;
                            } elseif (is_numeric($intervention_student)) {
                                $intervention_student_id = intval($intervention_student);
                            }
                            
                            if ($intervention_student_id && $intervention_student_id == $student_id) {
                                $intervention_note = 'Yes';
                                $intervention_class = 'intervention-yes';
                                
                                // Get the intervention/concern text
                                $intervention_concern = get_field('interventionsconcerns_you_have_for_a_young_person', $session_id);
                                if ($intervention_concern) {
                                    $intervention_note = 'Yes - ' . wp_trim_words($intervention_concern, 8, '...');
                                }
                            } else {
                                $intervention_note = 'No';
                                $intervention_class = 'intervention-no';
                            }
                        } else {
                            // For intervention sessions, it's always an intervention
                            $intervention_note = 'Full Mentoring';
                            $intervention_class = 'intervention-full';
                        }
                        
                        $formatted_date = $date ? date('M j, Y', strtotime($date)) : 'Unknown Date';
                        ?>
                        <tr>
                            <td><?php echo esc_html($formatted_date); ?></td>
                            <td><?php echo esc_html($session_type_display); ?></td>
                            <td><?php echo esc_html($class_name); ?></td>
                            <td><?php echo esc_html($attendance_status); ?></td>
                            <td class="<?php echo esc_attr($intervention_class); ?>">
                                <?php if ($intervention_class === 'intervention-yes' || $intervention_class === 'intervention-full'): ?>
                                    <a href="<?php echo get_permalink($session_id); ?>" class="intervention-link" target="_blank">
                                        <?php echo esc_html($intervention_note); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($intervention_note); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo get_permalink($session_id); ?>" class="session-view-btn" target="_blank">
                                    <span class="dashicons dashicons-visibility"></span> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="overlay-no-sessions">No sessions found for this student</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'student_name' => $user->display_name
    ]);
}
add_action('wp_ajax_wcb_load_student_profile_overlay', 'wcb_ajax_load_student_profile_overlay');
add_action('wp_ajax_nopriv_wcb_load_student_profile_overlay', 'wcb_ajax_load_student_profile_overlay');