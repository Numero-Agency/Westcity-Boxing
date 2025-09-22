<?php
// Programs/Memberships Table Component
// Updated to use proven logic from active-members-test.php for accurate member counting
// Only shows the 7 defined program groups with consistent member counting logic

function programs_table_shortcode($atts) {
    $atts = shortcode_atts([
        'show_search' => 'true',
        'class' => 'wcb-programs-table',
        'show_inactive' => 'false'
    ], $atts);
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="wcb-programs-table">
        <div class="programs-table-header">
            <h3><span class="dashicons dashicons-awards"></span> Programs & Memberships</h3>
            <div class="table-controls">
                <?php if($atts['show_search'] === 'true'): ?>
                <div class="table-search">
                    <div class="search-input-wrapper">
                        <span class="dashicons dashicons-search search-icon"></span>
                        <input type="text" 
                               id="programs-search-input" 
                               placeholder="Search programs..." 
                               class="table-search-input">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="filter-controls">
                    <label>
                        <input type="checkbox" id="show-inactive-toggle" <?php checked($atts['show_inactive'], 'true'); ?>>
                        Show Inactive
                    </label>
                </div>
            </div>
        </div>
        
        <div class="programs-table-content">
            <div id="programs-loading" class="loading" style="display: none;">
                <p>Loading programs...</p>
            </div>
            
            <table class="programs-table" id="programs-table">
                <thead>
                    <tr>
                        <th><span class="dashicons dashicons-awards"></span> Program Name</th>
                        <th><span class="dashicons dashicons-groups"></span> Total Members</th>
                        <th><span class="dashicons dashicons-chart-bar"></span> Total Sessions</th>
                        <th><span class="dashicons dashicons-calendar-alt"></span> Recent Session</th>
                        <th><span class="dashicons dashicons-info"></span> Status</th>
                        <th><span class="dashicons dashicons-admin-tools"></span> Actions</th>
                    </tr>
                </thead>
                <tbody id="programs-table-body">
                    <!-- Content loaded via AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Program Details Overlay -->
        <div id="program-details-overlay" class="program-details-overlay" style="display: none;">
            <div class="program-overlay-content">
                <!-- Program details content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var searchTimeout;
        var showInactive = <?php echo $atts['show_inactive'] === 'true' ? 'true' : 'false'; ?>;
        
        // Initial load
        loadProgramsTable();
        
        // Search functionality
        $('#programs-search-input').on('input', function() {
            clearTimeout(searchTimeout);
            var searchTerm = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                loadProgramsTable();
            }, 300);
        });
        
        // Show inactive toggle
        $('#show-inactive-toggle').on('change', function() {
            showInactive = $(this).is(':checked');
            loadProgramsTable();
        });
        
        // View program details
        $(document).on('click', '.program-view-btn', function(e) {
            e.preventDefault();
            var programId = $(this).data('program-id');
            var programType = $(this).data('program-type') || 'group';
            var programName = $(this).closest('tr').find('.program-name').text();
            
            loadProgramDetails(programId, programName, programType);
        });
        
        // Back to programs list
        window.showProgramsTable = function() {
            $('#program-details-overlay').hide();
            $('.programs-table-content').show();
            $('.programs-table-header h3').html('<span class="dashicons dashicons-awards"></span> Programs & Memberships');
        };
        
        // Function to get sign-up URL for a program name
        function getSignupUrl(programName) {
            var baseUrl = '<?php echo home_url(); ?>';
            var signupPaths = {
                'Cadet Boys Group 1': '/plans/cadet-boys-group-1/',
                'Cadet Boys Group 2': '/plans/cadet-boys-group-2/',
                'Mini Cadet Boys (9-11 Years) Group 1': '/plans/mini-cadet-boys-9-11-years-group-1/',
                'Mini Cadets Girls Group 1': '/plans/mini-cadets-girls-group-1/',
                'Youth Boys Group 1': '/plans/youth-boys-group-1/',
                'Youth Boys Group 2': '/plans/youth-boys-group-2/',
                'Youth Girls Group 1': '/plans/youth-girls-group-1/'
            };
            
            if (signupPaths[programName]) {
                return baseUrl + signupPaths[programName];
            }
            return null;
        }
        
        function loadProgramsTable() {
            $('#programs-loading').show();
            $('#programs-table-body').html('');
            
            var searchTerm = $('#programs-search-input').val().trim();
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wcb_load_programs_table',
                    search: searchTerm,
                    show_inactive: showInactive,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    $('#programs-loading').hide();
                    
                    if (response.success) {
                        $('#programs-table-body').html(response.data.rows);
                        
                        // Add smooth animation to rows
                        $('#programs-table-body tr').hide().each(function(index) {
                            $(this).delay(index * 50).fadeIn(200);
                        });
                    } else {
                        $('#programs-table-body').html('<tr><td colspan="6" class="error">' + response.data + '</td></tr>');
                    }
                },
                error: function() {
                    $('#programs-loading').hide();
                    $('#programs-table-body').html('<tr><td colspan="6" class="error">Failed to load programs. Please try again.</td></tr>');
                }
            });
        }
        
        function loadProgramDetails(programId, programName, programType) {
            if (!programId) {
                return;
            }
            
            programType = programType || 'group';
            
            // Update header title with sign-up button if applicable
            var signupUrl = getSignupUrl(programName);
            var titleHtml = '<span class="dashicons dashicons-awards"></span> ' + programName;
            if (signupUrl) {
                titleHtml += '<a href="' + signupUrl + '" target="_blank" class="program-signup-btn-inline">' +
                           '<span class="dashicons dashicons-plus-alt"></span> Sign Up</a>';
            }
            $('.programs-table-header h3').html(titleHtml);
            
            // Hide table content
            $('.programs-table-content').hide();
            
            // Show overlay with loading state
            $('#program-details-overlay .program-overlay-content').html(
                '<div class="program-loading"><p>Loading program details...</p></div>'
            );
            $('#program-details-overlay').show();
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_program_details',
                    program_id: programId,
                    program_type: programType,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#program-details-overlay .program-overlay-content').html(response.data.html);
                        
                        // Debug output
                        if (response.data.debug) {
                            console.log('Program Details Debug:', response.data.debug);
                        }
                    } else {
                        $('#program-details-overlay .program-overlay-content').html(
                            '<div class="program-error">Error loading program details: ' + response.data + '</div>'
                        );
                    }
                },
                error: function() {
                    $('#program-details-overlay .program-overlay-content').html(
                        '<div class="program-error">Failed to load program details. Please try again.</div>'
                    );
                }
            });
        }
    });
    </script>
    
    <style>
    /* Override browser default table styling */
    .programs-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }

    .programs-table,
    .programs-table th,
    .programs-table td {
        border: none !important;
        border-bottom: 1px solid #e5e5e5 !important;
        box-shadow: none !important;
        background-clip: padding-box !important;
    }

    .programs-table th,
    .programs-table td {
        padding: 12px !important;
        vertical-align: middle !important;
        text-align: left !important;
        font-size: 14px !important;
        color: #333 !important;
    }

    .programs-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .programs-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    .programs-table tbody tr:nth-child(even) {
        background-color: #ffffff !important;
    }

    .programs-table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    /* Programs table specific styles */
    .wcb-programs-table {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: white;
        border: 1px solid #e5e5e5;
        overflow: hidden;
    }
    
    .programs-table-header {
        background: #000000;
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .programs-table-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    
    .programs-table-header h3 .dashicons {
        font-size: 20px;
        color: white;
    }
    
    .table-controls {
        display: flex;
        align-items: center;
        gap: 20px;
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
    
    .filter-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-controls label {
        color: white;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    
    .filter-controls input[type="checkbox"] {
        margin: 0;
    }
    
    .programs-table-content {
        background: white;
    }
    
    .programs-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        font-size: 14px;
    }
    
    .programs-table th {
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
    
    .programs-table th .dashicons {
        font-size: 16px;
        margin-right: 6px;
        vertical-align: middle;
        color: #666666;
    }
    
    .programs-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        color: #000000;
    }
    
    .programs-table td:last-child {
        text-align: center;
    }
    
    .programs-table tr:hover {
        background: #fafafa;
    }
    
    .program-name {
        font-weight: 600;
        color: #000000;
    }
    
    .member-count {
        font-weight: 600;
        color: #000000;
    }
    
    .session-count {
        font-weight: 600;
        color: #000000;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .status-badge.active {
        background: white;
        color: #000000;
        border-color: #000000;
    }
    
    .status-badge.inactive {
        background: #f8f9fa;
        color: #666666;
        border-color: #cccccc;
    }
    
    .program-view-btn {
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
    
    .program-view-btn:hover {
        background: #333333;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    #programs-loading {
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
    
    /* Program Details Overlay Styles */
    .program-details-overlay {
        background: white;
        min-height: 400px;
    }
    
    .program-overlay-content {
        padding: 0;
    }
    
    .program-loading {
        padding: 60px 24px;
        text-align: center;
        color: #666666;
        font-size: 16px;
    }
    
    .program-error {
        padding: 60px 24px;
        text-align: center;
        color: #000000;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        margin: 20px;
    }
    
    .program-details {
        background: white;
    }
    
    .program-details-header {
        background: #000000;
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .program-details-info {
        flex: 1;
        min-width: 300px;
    }
    
    .program-details-info h2 {
        margin: 0 0 12px 0;
        font-size: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .program-details-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
        opacity: 0.9;
    }
    
    .program-details-actions {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    
    .program-signup-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: #007cba;
        color: white;
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
    
    .program-signup-btn:hover {
        background: #005a87;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .program-signup-btn-inline {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: #007cba;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        margin-left: 12px;
        vertical-align: middle;
    }
    
    .program-signup-btn-inline:hover {
        background: #005a87;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .program-signup-btn-inline .dashicons {
        font-size: 14px;
    }
    
    .back-to-programs-btn {
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
    
    .back-to-programs-btn:hover {
        background: #f0f0f0;
        color: #000000;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .program-details-content {
        padding: 24px;
    }
    
    .program-details-section {
        margin-bottom: 32px;
    }
    
    .program-details-section:last-child {
        margin-bottom: 0;
    }
    
    .program-section-title {
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
    
    .program-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .program-stat-card {
        background: white;
        border: 1px solid #e5e5e5;
        padding: 20px;
        text-align: center;
        overflow: hidden;
    }
    
    .program-stat-card h4 {
        margin: 0 0 8px 0;
        font-size: 24px;
        font-weight: 600;
        color: #000000;
    }
    
    .program-stat-card p {
        margin: 0;
        color: #666666;
        font-weight: 500;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .members-table,
    .sessions-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        font-size: 14px;
    }
    
    .members-table th,
    .members-table td,
    .sessions-table th,
    .sessions-table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .members-table th,
    .sessions-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #000000;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .members-table tr:hover,
    .sessions-table tr:hover {
        background: #fafafa;
    }
    
    .no-members,
    .no-sessions {
        text-align: center;
        color: #666666;
        padding: 40px;
        font-style: italic;
        background: #f8f9fa;
    }
    
    .session-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        background: #000000;
        color: white;
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
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .session-view-btn .dashicons {
        font-size: 12px;
    }
    
    .sessions-note {
        margin-top: 12px;
        color: #666666;
        font-size: 13px;
        text-align: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    
    /* School-specific styling */
    .school-type-badge {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-left: 8px;
        display: inline-block;
        vertical-align: middle;
    }
    
    .school-info-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 6px;
        border-left: 4px solid #28a745;
    }
    
    .school-info-box p {
        margin: 0;
        font-size: 14px;
        color: #666666;
        line-height: 1.6;
    }
    
    .school-info-box strong {
        color: #000000;
        font-weight: 600;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .programs-table-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .table-controls {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }
        
        .table-search-input {
            width: 100% !important;
        }
        
        .programs-table {
            font-size: 13px;
        }
        
        .programs-table th,
        .programs-table td {
            padding: 12px 16px;
        }
        
        .program-details-header {
            flex-direction: column;
            gap: 16px;
        }
        
        .program-details-info {
            min-width: auto;
        }
        
        .program-details-info h2 {
            font-size: 20px;
        }
        
        .program-details-meta {
            gap: 12px;
            font-size: 13px;
        }
        
        .program-details-content {
            padding: 16px;
        }
        
        .program-stats-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
    
    @media (max-width: 600px) {
        .programs-table th:nth-child(4),
        .programs-table td:nth-child(4) {
            display: none;
        }
        
        .programs-table th,
        .programs-table td {
            padding: 10px 12px;
        }
        
        .table-search-input {
            width: 180px !important;
        }
        
        .programs-table th .dashicons {
            font-size: 14px;
            margin-right: 4px;
        }
        
        .program-details-meta {
            flex-direction: column;
            gap: 8px;
        }
        
        .members-table th:nth-child(3),
        .members-table td:nth-child(3),
        .sessions-table th:nth-child(4),
        .sessions-table td:nth-child(4) {
            display: none;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('programs_table', 'programs_table_shortcode');

// Function to update mentoring sessions to link to real WCB Mentoring membership
function wcb_link_mentoring_sessions_to_membership() {
    $wcb_mentoring_id = 1738; // Real WCB Mentoring membership ID
    
    // Get all mentoring sessions
    $mentoring_sessions = get_posts([
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'session_type',
                'field' => 'slug',
                'terms' => 'mentoring'
            ]
        ]
    ]);
    
    $updated_count = 0;
    foreach ($mentoring_sessions as $session) {
        $current_membership = get_field('selected_membership', $session->ID);
        
        // If not already linked to the correct membership, update it
        if ($current_membership != $wcb_mentoring_id) {
            update_field('selected_membership', $wcb_mentoring_id, $session->ID);
            $updated_count++;
        }
    }
    
    return $updated_count;
}

// Run the fix once on admin init for admin users
function wcb_run_mentoring_sessions_fix_once() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $fix_run = get_option('wcb_mentoring_sessions_fix_run', false);
    if (!$fix_run) {
        $updated_count = wcb_link_mentoring_sessions_to_membership();
        update_option('wcb_mentoring_sessions_fix_run', true);
        
        // Add an admin notice to show the result
        add_action('admin_notices', function() use ($updated_count) {
            if ($updated_count > 0) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>WCB Mentoring Fix:</strong> Successfully linked ' . $updated_count . ' mentoring sessions to the WCB Mentoring program.</p>';
                echo '</div>';
            }
        });
    }
}
add_action('admin_init', 'wcb_run_mentoring_sessions_fix_once');

// Helper function to get sign-up URL for a program
function wcb_get_program_signup_url($program_title) {
    // Map program names to their sign-up URL paths (relative)
    $signup_paths = [
        'Cadet Boys Group 1' => '/plans/cadet-boys-group-1/',
        'Cadet Boys Group 2' => '/plans/cadet-boys-group-2/',
        'Mini Cadet Boys (9-11 Years) Group 1' => '/plans/mini-cadet-boys-9-11-years-group-1/',
        'Mini Cadets Girls Group 1' => '/plans/mini-cadets-girls-group-1/',
        'Youth Boys Group 1' => '/plans/youth-boys-group-1/',
        'Youth Boys Group 2' => '/plans/youth-boys-group-2/',
        'Youth Girls Group 1' => '/plans/youth-girls-group-1/'
    ];
    
    // Check if we have a sign-up path for this program and build full URL
    if (isset($signup_paths[$program_title])) {
        return home_url($signup_paths[$program_title]);
    }
    
    return null;
}

// AJAX Handler for loading programs table
function wcb_ajax_load_programs_table() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $search = sanitize_text_field($_POST['search']);
    $show_inactive = $_POST['show_inactive'] === 'true';

    // Use the proven logic from active-members-test.php - only show the 7 defined groups plus additional memberships
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    // Additional individual memberships to include
    $additional_memberships = [
        1738 => 'WCB Mentoring',
        1932 => 'Competitive Team'
    ];

    // Get all groups using the same query as active-members-test.php
    global $wpdb;
    $all_groups = $wpdb->get_results("SELECT ID, post_title, 'group' as type FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    // Filter to only include the 7 defined groups
    $programs = [];
    foreach ($defined_groups as $group_name) {
        foreach ($all_groups as $group) {
            if (strcasecmp($group->post_title, $group_name) === 0) {
                // Apply search filter if provided
                if (empty($search) || stripos($group->post_title, $search) !== false) {
                    $programs[] = $group;
                }
                break;
            }
        }
    }

    // Add individual memberships
    foreach ($additional_memberships as $membership_id => $membership_name) {
        // Apply search filter if provided
        if (empty($search) || stripos($membership_name, $search) !== false) {
            $membership_obj = new stdClass();
            $membership_obj->ID = $membership_id;
            $membership_obj->post_title = $membership_name;
            $membership_obj->type = 'membership';
            $programs[] = $membership_obj;
        }
    }

    // Add Schools to the programs array
    // Try different possible post type names
    $possible_school_types = ['schools', 'school', 'wcb_schools', 'wcb_school'];
    $schools_found = false;
    
    foreach ($possible_school_types as $post_type_name) {
        $schools = get_posts([
            'post_type' => $post_type_name,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        if (!empty($schools)) {
            $schools_found = true;
            wcb_debug_log("Found " . count($schools) . " schools with post type: " . $post_type_name);
            
            foreach ($schools as $school) {
                // Apply search filter if provided
                if (empty($search) || stripos($school->post_title, $search) !== false) {
                    $school_obj = new stdClass();
                    $school_obj->ID = $school->ID;
                    $school_obj->post_title = $school->post_title;
                    $school_obj->type = 'school';
                    $programs[] = $school_obj;
                }
            }
            break; // Stop after finding schools
        }
    }
    
    if (!$schools_found) {
        wcb_debug_log("No schools found with any of these post types: " . implode(', ', $possible_school_types));
    }

    $using_groups = true;

    // Generate table rows
    $rows_html = '';
    if (!empty($programs)) {
        foreach ($programs as $program) {
            $program_id = $program->ID;
            $program_type = isset($program->type) ? $program->type : 'group';

            if ($program_type === 'group') {
                // Get member count for this group (across all its memberships)
                $member_count = wcb_get_group_member_count($program_id);

                // Get session count for this group
                $session_count = wcb_get_group_session_count($program_id);

                // Get most recent session for this group
                $recent_session = wcb_get_group_recent_session($program_id);
            } elseif ($program_type === 'school') {
                // School program
                $member_count = wcb_get_school_student_count($program_id);
                $session_count = wcb_get_school_session_count($program_id);
                $recent_session = wcb_get_school_recent_session($program_id);
            } else {
                // Individual membership/program
                $member_count = wcb_get_program_member_count($program_id);
                $session_count = wcb_get_program_session_count($program_id);
                $recent_session = wcb_get_program_recent_session($program_id);
            }

            // Determine if program is active (has recent activity)
            $is_active = ($member_count > 0 || $session_count > 0);

            // Skip inactive programs if not showing them
            if (!$is_active && !$show_inactive) {
                continue;
            }

            $status = $is_active ? 'Active' : 'Inactive';
            $status_class = $is_active ? 'active' : 'inactive';

            // Add type indicator for schools
            $program_name_display = esc_html($program->post_title);
            if ($program_type === 'school') {
                $program_name_display .= ' <span class="school-type-badge">School</span>';
            }

            $rows_html .= '<tr>';
            $rows_html .= '<td><div class="program-name">' . $program_name_display . '</div></td>';
            $rows_html .= '<td><span class="member-count">' . esc_html($member_count) . '</span></td>';
            $rows_html .= '<td><span class="session-count">' . esc_html($session_count) . '</span></td>';
            $rows_html .= '<td>' . ($recent_session ? esc_html(date('M j, Y', strtotime($recent_session))) : 'No sessions') . '</td>';
            $rows_html .= '<td><span class="status-badge ' . esc_attr($status_class) . '">' . esc_html($status) . '</span></td>';
            $rows_html .= '<td><button class="program-view-btn" data-program-id="' . esc_attr($program_id) . '" data-program-type="' . esc_attr($program_type) . '">View Details</button></td>';
            $rows_html .= '</tr>';
        }
    }

    if (empty($rows_html)) {
        $rows_html = '<tr><td colspan="6" class="no-results">No programs found' . (!empty($search) ? ' matching "' . esc_html($search) . '"' : '') . '</td></tr>';
    }

    wp_send_json_success([
        'rows' => $rows_html,
        'total_programs' => count($programs)
    ]);
}
add_action('wp_ajax_wcb_load_programs_table', 'wcb_ajax_load_programs_table');
add_action('wp_ajax_nopriv_wcb_load_programs_table', 'wcb_ajax_load_programs_table');

// AJAX Handler for loading program details
function wcb_ajax_load_program_details() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $program_id = intval($_POST['program_id']);
    $program_type = isset($_POST['program_type']) ? sanitize_text_field($_POST['program_type']) : 'group';
    
    // DEBUG: Log what we received
    wcb_debug_log("wcb_ajax_load_program_details: Program ID: {$program_id}, Type: {$program_type}");
    
    // Ensure program_type is properly set
    if (!in_array($program_type, ['group', 'membership', 'school'])) {
        $program_type = 'group'; // Default fallback
    }
    
    if (!$program_id) {
        wp_send_json_error('Invalid program ID');
        return;
    }
    
    // Handle individual memberships vs groups vs schools differently
    if ($program_type === 'membership') {
        // For individual memberships, we need to get the title from our mapping
        $additional_memberships = [
            1738 => 'WCB Mentoring',
            1932 => 'Competitive Team'
        ];
        
        if (!isset($additional_memberships[$program_id])) {
            wp_send_json_error('Membership not found');
            return;
        }
        
        $program_title = $additional_memberships[$program_id];
        $program_content = ''; // Individual memberships don't have content in this context
    } elseif ($program_type === 'school') {
        // Get the school post
        $school = get_post($program_id);
        if (!$school) {
            wp_send_json_error('School not found');
            return;
        }
        
        $program_title = $school->post_title;
        $program_content = $school->post_content;
    } else {
        // Get the program (group)
        $program = get_post($program_id);
        if (!$program) {
            wp_send_json_error('Program not found');
            return;
        }

        $program_title = $program->post_title;
        $program_content = $program->post_content;
    }

    if ($program_type === 'group') {
        // Get group statistics
        $member_count = wcb_get_group_member_count($program_id);
        $session_count = wcb_get_group_session_count($program_id);
        $recent_session = wcb_get_group_recent_session($program_id);

        // Get group members and sessions
        $members = wcb_get_group_members($program_id);
        $sessions = wcb_get_group_sessions($program_id, 10);
        $total_session_count = wcb_get_group_session_count($program_id);
        $using_groups = true;
    } elseif ($program_type === 'school') {
        // School statistics
        $member_count = wcb_get_school_student_count($program_id);
        $session_count = wcb_get_school_session_count($program_id);
        $recent_session = wcb_get_school_recent_session($program_id);
        
        // Get school students (not members)
        $members = wcb_get_school_students($program_id);
        // Get school sessions
        $sessions = wcb_get_school_sessions($program_id, 10);
        $total_session_count = wcb_get_school_session_count($program_id);
        $using_groups = false;
        
        // Debug log
        wcb_debug_log("School {$program_id}: Found " . count($members) . " students, {$session_count} sessions");
    } else {
        // Individual membership statistics
        $member_count = wcb_get_program_member_count($program_id);
        $session_count = wcb_get_program_session_count($program_id);
        $recent_session = wcb_get_program_recent_session($program_id);

        // Get individual membership members and sessions
        $members = wcb_get_program_members($program_id);
        $sessions = wcb_get_program_sessions($program_id, 10);
        $total_session_count = wcb_get_program_session_count($program_id);
        
        // DEBUG: Log member count for individual memberships
        wcb_debug_log("Individual membership {$program_id}: Found " . count($members) . " members, {$session_count} sessions");
        $using_groups = false;
    }
    
    ob_start();
    
    // Determine if this is a school program
    $is_school = ($program_type === 'school');
    
    // Debug output for schools
    if ($is_school) {
        wcb_debug_log("Rendering School Details: ID={$program_id}, Title={$program_title}, Students=" . count($members));
        if (empty($members)) {
            error_log("WARNING: No students found for school {$program_id}. ACF field might not be configured correctly.");
        }
    }
    ?>
    <div class="program-details">
        <div class="program-details-header">
            <div class="program-details-info">
                <div class="program-details-meta">
                    <span><span class="dashicons dashicons-groups"></span> <?php echo esc_html($member_count); ?> <?php echo $is_school ? 'Students' : 'Members'; ?></span>
                    <span><span class="dashicons dashicons-chart-bar"></span> <?php echo esc_html($session_count); ?> Total Sessions</span>
                    <?php if ($recent_session): ?>
                    <span><span class="dashicons dashicons-calendar-alt"></span> Last session: <?php echo date('M j, Y', strtotime($recent_session)); ?></span>
                    <?php endif; ?>
                    <?php if ($is_school): ?>
                    <span><span class="dashicons dashicons-admin-site"></span> School Program</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="program-details-actions">
                <button class="back-to-programs-btn" onclick="showProgramsTable()">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> All Programs
                </button>
            </div>
        </div>
        
        <div class="program-details-content">
            <!-- Program Statistics -->
            <div class="program-details-section">
                <h3 class="program-section-title">
                    <span class="dashicons dashicons-chart-pie"></span> Overview
                </h3>
                <div class="program-stats-grid">
                    <div class="program-stat-card">
                        <h4><?php echo esc_html($member_count); ?></h4>
                        <p><?php echo $is_school ? 'Total Students' : 'Total Members'; ?></p>
                    </div>
                    <div class="program-stat-card">
                        <h4><?php echo esc_html($session_count); ?></h4>
                        <p>Total Sessions</p>
                    </div>
                    <div class="program-stat-card">
                        <h4><?php echo $recent_session ? date('M j', strtotime($recent_session)) : 'N/A'; ?></h4>
                        <p>Last Session</p>
                    </div>
                </div>
            </div>
            
            <!-- Program Description -->
            <?php if (!empty($program_content)): ?>
            <div class="program-details-section">
                <h3 class="program-section-title">
                    <span class="dashicons dashicons-admin-page"></span> Description
                </h3>
                <div style="background: white; border: 1px solid #e5e5e5; padding: 20px; line-height: 1.6;">
                    <?php echo wp_kses_post($program_content); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Current Members/Students -->
            <div class="program-details-section">
                <h3 class="program-section-title">
                    <span class="dashicons dashicons-groups"></span> 
                    <?php echo $is_school ? 'Students' : 'Current Members'; ?> 
                    (<?php echo count($members); ?>)
                </h3>
                <?php if (!empty($members)): ?>
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <?php if ($is_school): ?>
                                <th>Date of Birth</th>
                                <th>Ethnicity</th>
                            <?php else: ?>
                                <th>Email</th>
                                <th>Join Date</th>
                                <th>Status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo esc_html($member['name']); ?></td>
                            <?php if ($is_school): ?>
                                <td><?php 
                                    if (!empty($member['date_of_birth'])) {
                                        $date_str = $member['date_of_birth'];
                                        
                                        // Parse the date - ACF returns Y-m-d format
                                        $timestamp = strtotime($date_str);
                                        if ($timestamp !== false && $timestamp > 0) {
                                            // Successfully parsed - format for display
                                            echo esc_html(date('M j, Y', $timestamp));
                                        } else {
                                            // Just show the date as is if valid format
                                            echo esc_html($date_str);
                                        }
                                    } else {
                                        echo '<em>Not provided</em>';
                                    }
                                ?></td>
                                <td><?php echo !empty($member['ethnicity']) ? esc_html($member['ethnicity']) : '<em>Not specified</em>'; ?></td>
                            <?php else: ?>
                                <td><?php echo esc_html($member['email']); ?></td>
                                <td><?php echo esc_html($member['join_date']); ?></td>
                                <td><?php echo esc_html($member['status']); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-members">
                    <?php echo $is_school 
                        ? 'No students currently enrolled in this school' 
                        : 'No members currently enrolled in this program'; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Sessions -->
            <div class="program-details-section">
                <h3 class="program-section-title">
                    <span class="dashicons dashicons-calendar-alt"></span> Recent Sessions (<?php echo count($sessions); ?>)
                </h3>
                <?php if (!empty($sessions)): ?>
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Attendance</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo esc_html($session['date']); ?></td>
                            <td><?php echo esc_html($session['type']); ?></td>
                            <td><?php echo esc_html($session['attendance']); ?></td>
                            <td><?php echo esc_html($session['notes']); ?></td>
                            <td>
                                <a href="<?php echo get_permalink($session['id']); ?>" 
                                   class="session-view-btn" 
                                   target="_blank"
                                   title="View Session Details">
                                    <span class="dashicons dashicons-visibility"></span> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($sessions) >= 10): ?>
                <p class="sessions-note">
                    <em>Showing 10 most recent sessions. Total sessions for <?php echo $is_school ? 'this school' : 'this program'; ?>: <?php echo esc_html($total_session_count); ?></em>
                </p>
                <?php endif; ?>
                <?php else: ?>
                <div class="no-sessions">No sessions recorded for <?php echo $is_school ? 'this school' : 'this program'; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'program_name' => $program_title,
        'debug' => [
            'program_type' => $program_type,
            'member_count' => $member_count,
            'members_array_count' => count($members)
        ]
    ]);
}
add_action('wp_ajax_wcb_load_program_details', 'wcb_ajax_load_program_details');
add_action('wp_ajax_nopriv_wcb_load_program_details', 'wcb_ajax_load_program_details');

// AJAX Handler for manually fixing mentoring sessions
function wcb_ajax_fix_mentoring_sessions() {
    // Verify nonce and permissions
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    $updated_count = wcb_link_mentoring_sessions_to_membership();
    
    wp_send_json_success([
        'message' => "Successfully linked {$updated_count} mentoring sessions to WCB Mentoring program.",
        'updated_count' => $updated_count
    ]);
}
add_action('wp_ajax_wcb_fix_mentoring_sessions', 'wcb_ajax_fix_mentoring_sessions');

// Add admin notice with manual fix button for testing
function wcb_add_mentoring_fix_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $screen = get_current_screen();
    // Only show on admin pages where it's relevant
    if (!$screen || !in_array($screen->id, ['dashboard', 'edit-session_log', 'session_log'])) {
        return;
    }
    
    ?>
    <div class="notice notice-info" id="wcb-mentoring-fix-notice">
        <p>
            <strong>WCB Mentoring Fix Available:</strong> 
            Click to ensure all mentoring sessions are properly linked to the WCB Mentoring program.
            <button type="button" class="button button-primary" id="wcb-fix-mentoring-btn" style="margin-left: 10px;">
                Fix Mentoring Sessions
            </button>
            <span id="wcb-fix-result" style="margin-left: 10px;"></span>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#wcb-fix-mentoring-btn').on('click', function() {
            var $btn = $(this);
            var $result = $('#wcb-fix-result');
            
            $btn.prop('disabled', true).text('Fixing...');
            $result.text('');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wcb_fix_mentoring_sessions',
                    nonce: '<?php echo wp_create_nonce('wcb_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                        setTimeout(function() {
                            $('#wcb-mentoring-fix-notice').fadeOut();
                        }, 3000);
                    } else {
                        $result.html('<span style="color: red;">✗ Error: ' + response.data + '</span>');
                    }
                },
                error: function() {
                    $result.html('<span style="color: red;">✗ AJAX Error occurred</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Fix Mentoring Sessions');
                }
            });
        });
    });
    </script>
    <?php
}
add_action('admin_notices', 'wcb_add_mentoring_fix_admin_notice');

// Helper function to get member count for a program
function wcb_get_program_member_count($program_id) {
    global $wpdb;

    // Use EXACT same query logic as active-members-test.php
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$wpdb->prefix}mepr_transactions t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
    ", $program_id));

    return count($results);
}

// Override: Helper function to get session count for a group using proven logic
function wcb_get_group_session_count($group_id) {
    global $wpdb;

    // Use the EXACT same logic as active-members-test.php to get group memberships
    $group_memberships = wcb_get_group_memberships($group_id);

    if (empty($group_memberships)) {
        return 0;
    }

    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);

    // Count sessions for any membership in this group
    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));
    $query = "
        SELECT COUNT(*)
        FROM {$wpdb->posts} s
        JOIN {$wpdb->postmeta} sm ON s.ID = sm.post_id
        WHERE s.post_type = 'session_log'
        AND s.post_status = 'publish'
        AND sm.meta_key = 'selected_membership'
        AND sm.meta_value IN ({$placeholders})
    ";

    return (int) $wpdb->get_var($wpdb->prepare($query, ...$membership_ids));
}

// Override: Helper function to get most recent session date for a group using proven logic
function wcb_get_group_recent_session($group_id) {
    global $wpdb;

    // Use the EXACT same logic as active-members-test.php to get group memberships
    $group_memberships = wcb_get_group_memberships($group_id);

    if (empty($group_memberships)) {
        return null;
    }

    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);

    // Find the most recent session for any membership in this group
    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

    // Check if any membership is WCB Mentoring (ID: 1738) for special handling
    $is_wcb_mentoring = in_array(1738, $membership_ids);

    if ($is_wcb_mentoring) {
        // For WCB Mentoring, order by intervention_date_
        $query = "
            SELECT sm2.meta_value as session_date
            FROM {$wpdb->posts} s
            JOIN {$wpdb->postmeta} sm ON s.ID = sm.post_id
            JOIN {$wpdb->postmeta} sm2 ON s.ID = sm2.post_id
            WHERE s.post_type = 'session_log'
            AND s.post_status = 'publish'
            AND sm.meta_key = 'selected_membership'
            AND sm.meta_value IN ({$placeholders})
            AND sm2.meta_key = 'intervention_date_'
            AND sm2.meta_value != ''
            ORDER BY sm2.meta_value DESC
            LIMIT 1
        ";
    } else {
        // For regular programs, order by session_date
        $query = "
            SELECT sm2.meta_value as session_date
            FROM {$wpdb->posts} s
            JOIN {$wpdb->postmeta} sm ON s.ID = sm.post_id
            JOIN {$wpdb->postmeta} sm2 ON s.ID = sm2.post_id
            WHERE s.post_type = 'session_log'
            AND s.post_status = 'publish'
            AND sm.meta_key = 'selected_membership'
            AND sm.meta_value IN ({$placeholders})
            AND sm2.meta_key = 'session_date'
            AND sm2.meta_value != ''
            ORDER BY sm2.meta_value DESC
            LIMIT 1
        ";
    }

    return $wpdb->get_var($wpdb->prepare($query, ...$membership_ids));
}

// Override: Helper function to get group members using proven logic from active-members-test.php
function wcb_get_group_members($group_id) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    // Use the EXACT same logic as active-members-test.php
    $group_memberships = wcb_get_group_memberships($group_id);

    if (empty($group_memberships)) {
        return [];
    }

    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

    // Get members who have active transactions for memberships in this group
    // Use EXACT same query as active-members-test.php
    $group_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email, t.created_at
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ", ...$membership_ids));

    $members = [];
    foreach ($group_members as $result) {
        $members[] = [
            'name' => $result->display_name,
            'email' => $result->user_email,
            'join_date' => date('M j, Y', strtotime($result->created_at)),
            'status' => 'Active' // All members returned by this query are active
        ];
    }

    return $members;
}

// Override: Helper function to get group sessions using proven logic
function wcb_get_group_sessions($group_id, $limit = 10) {
    global $wpdb;

    // Use the EXACT same logic as active-members-test.php to get group memberships
    $group_memberships = wcb_get_group_memberships($group_id);

    if (empty($group_memberships)) {
        return [];
    }

    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);

    // Check if any membership is WCB Mentoring (ID: 1738) for special handling
    $is_wcb_mentoring = in_array(1738, $membership_ids);

    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

    if ($is_wcb_mentoring) {
        // For WCB Mentoring, order by intervention_date_
        $query = "
            SELECT s.ID, s.post_title
            FROM {$wpdb->posts} s
            JOIN {$wpdb->postmeta} sm ON s.ID = sm.post_id
            LEFT JOIN {$wpdb->postmeta} sm2 ON s.ID = sm2.post_id AND sm2.meta_key = 'intervention_date_'
            WHERE s.post_type = 'session_log'
            AND s.post_status = 'publish'
            AND sm.meta_key = 'selected_membership'
            AND sm.meta_value IN ({$placeholders})
            ORDER BY COALESCE(sm2.meta_value, s.post_date) DESC
            LIMIT %d
        ";
        $session_posts = $wpdb->get_results($wpdb->prepare($query, ...array_merge($membership_ids, [$limit])));
    } else {
        // For regular programs, order by session_date
        $query = "
            SELECT s.ID, s.post_title
            FROM {$wpdb->posts} s
            JOIN {$wpdb->postmeta} sm ON s.ID = sm.post_id
            LEFT JOIN {$wpdb->postmeta} sm2 ON s.ID = sm2.post_id AND sm2.meta_key = 'session_date'
            WHERE s.post_type = 'session_log'
            AND s.post_status = 'publish'
            AND sm.meta_key = 'selected_membership'
            AND sm.meta_value IN ({$placeholders})
            ORDER BY COALESCE(sm2.meta_value, s.post_date) DESC
            LIMIT %d
        ";
        $session_posts = $wpdb->get_results($wpdb->prepare($query, ...array_merge($membership_ids, [$limit])));
    }

    $sessions = [];
    foreach ($session_posts as $session_post) {
        $session_id = $session_post->ID;

        // Get session type using helper function
        $session_type_data = wcb_get_session_type($session_id);
        $session_type = $session_type_data['name'];
        $session_type_slug = $session_type_data['slug'];

        // Check if this is a mentoring session and handle differently
        if ($session_type_slug === 'mentoring' || $is_wcb_mentoring) {
            // For mentoring sessions, use intervention date and student info
            $intervention_date = get_field('intervention_date_', $session_id);
            $student_involved = get_field('student_involved', $session_id);
            $debrief_notes = get_field('debrief_event', $session_id);

            if ($intervention_date) {
                $date_display = date('M j, Y', strtotime($intervention_date));
            } else {
                // Fallback to post date if no intervention date
                $date_display = date('M j, Y', strtotime($session_post->post_date));
            }

            // Get student name for mentoring session
            $student_name = 'Unknown Student';
            if ($student_involved) {
                $user = get_user_by('ID', $student_involved);
                if ($user) {
                    $student_name = $user->display_name;
                }
            }

            $attendance_display = '1 student (' . $student_name . ')';
            $notes_display = $debrief_notes ? wp_trim_words($debrief_notes, 10) : 'No notes';

        } else {
            // Handle regular class sessions
            $date = get_field('session_date', $session_id);
            $notes = get_field('session_notes', $session_id);

            $date_display = $date ? date('M j, Y', strtotime($date)) : 'Unknown';

            // Get attendance count using the proper helper function
            $attendance_data = wcb_get_session_attendance($session_id);
            $attended_count = count($attendance_data['attended']);
            $excused_count = count($attendance_data['excused']);
            $total_attendance = $attended_count + $excused_count;

            // Check if this is a 1-on-1 session (associated student)
            $associated_student = get_field('associated_student', $session_id);
            if ($associated_student) {
                $attendance_display = '1 student (1-on-1)';
            } else {
                $attendance_display = $total_attendance . ' students';
                if ($attended_count > 0 || $excused_count > 0) {
                    $attendance_display .= ' (' . $attended_count . ' present, ' . $excused_count . ' excused)';
                }
            }

            $notes_display = $notes ? wp_trim_words($notes, 10) : 'No notes';
        }

        $sessions[] = [
            'id' => $session_id,
            'date' => $date_display,
            'type' => $session_type,
            'attendance' => $attendance_display,
            'notes' => $notes_display
        ];
    }

    return $sessions;
}

// Note: wcb_get_group_member_count is already defined in student-table.php with the proven logic

// Helper function to get session count for a program
function wcb_get_program_session_count($program_id) {
    $args = [
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'selected_membership',
                'value' => $program_id,
                'compare' => '='
            ]
        ]
    ];
    
    $sessions = get_posts($args);
    return count($sessions);
}

// Helper function to get most recent session date for a program
function wcb_get_program_recent_session($program_id) {
    // Check if this is the WCB Mentoring program (ID: 1738)
    $is_wcb_mentoring = ($program_id == 1738);
    
    if ($is_wcb_mentoring) {
        // For WCB Mentoring, order by intervention_date_
        $args = [
            'post_type' => 'session_log',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_key' => 'intervention_date_',
            'meta_query' => [
                [
                    'key' => 'selected_membership',
                    'value' => $program_id,
                    'compare' => '='
                ]
            ]
        ];
        
        $sessions = get_posts($args);
        if (!empty($sessions)) {
            $intervention_date = get_field('intervention_date_', $sessions[0]->ID);
            if ($intervention_date) {
                return $intervention_date;
            }
            // Fallback to post date if no intervention date
            return $sessions[0]->post_date;
        }
    } else {
        // For regular programs, order by session_date
        $args = [
            'post_type' => 'session_log',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_key' => 'session_date',
            'meta_query' => [
                [
                    'key' => 'selected_membership',
                    'value' => $program_id,
                    'compare' => '='
                ]
            ]
        ];
        
        $sessions = get_posts($args);
        if (!empty($sessions)) {
            return get_field('session_date', $sessions[0]->ID);
        }
    }
    
    return null;
}

// Helper function to get program members
function wcb_get_program_members($program_id) {
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        wcb_debug_log("wcb_get_program_members: MemberPress transactions table not found");
        return [];
    }
    
    // DEBUG: Log what we're looking for
    wcb_debug_log("wcb_get_program_members: Looking for members of program ID: {$program_id}");
    
    // Use EXACT same query logic as active-members-test.php
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email, t.created_at,
               CASE
                   WHEN t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00'
                   THEN 'Active'
                   ELSE 'Expired'
               END as status
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ", $program_id));
    
    // DEBUG: Log results
    wcb_debug_log("wcb_get_program_members: Found " . count($results) . " members for program ID: {$program_id}");
    
    $members = [];
    foreach ($results as $result) {
        $members[] = [
            'name' => $result->display_name,
            'email' => $result->user_email,
            'join_date' => date('M j, Y', strtotime($result->created_at)),
            'status' => $result->status
        ];
    }
    
    return $members;
}

// Helper function to get program sessions
function wcb_get_program_sessions($program_id, $limit = 10) {
    // Check if this is the WCB Mentoring program (ID: 1738)
    $is_wcb_mentoring = ($program_id == 1738);
    
    if ($is_wcb_mentoring) {
        // For WCB Mentoring, get mentoring sessions and order by intervention_date_
        $args = [
            'post_type' => 'session_log',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_key' => 'intervention_date_',
            'meta_query' => [
                [
                    'key' => 'selected_membership',
                    'value' => $program_id,
                    'compare' => '='
                ]
            ]
        ];
    } else {
        // For regular programs, order by session_date
        $args = [
            'post_type' => 'session_log',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_key' => 'session_date',
            'meta_query' => [
                [
                    'key' => 'selected_membership',
                    'value' => $program_id,
                    'compare' => '='
                ]
            ]
        ];
    }
    
    $session_posts = get_posts($args);
    $sessions = [];
    
    foreach ($session_posts as $session_post) {
        $session_id = $session_post->ID;
        
        // Get session type using helper function
        $session_type_data = wcb_get_session_type($session_id);
        $session_type = $session_type_data['name'];
        $session_type_slug = $session_type_data['slug'];
        
        // Check if this is a mentoring session and handle differently
        if ($session_type_slug === 'mentoring' || $is_wcb_mentoring) {
            // For mentoring sessions, use intervention date and student info
            $intervention_date = get_field('intervention_date_', $session_id);
            $student_involved = get_field('student_involved', $session_id);
            $debrief_notes = get_field('debrief_event', $session_id);
            
            if ($intervention_date) {
                $date_display = date('M j, Y', strtotime($intervention_date));
            } else {
                // Fallback to post date if no intervention date
                $date_display = date('M j, Y', strtotime($session_post->post_date));
            }
            
            // Get student name for mentoring session
            $student_name = 'Unknown Student';
            if ($student_involved) {
                $user = get_user_by('ID', $student_involved);
                if ($user) {
                    $student_name = $user->display_name;
                }
            }
            
            $attendance_display = '1 student (' . $student_name . ')';
            $notes_display = $debrief_notes ? wp_trim_words($debrief_notes, 10) : 'No notes';
            
        } else {
            // Handle regular class sessions
            $date = get_field('session_date', $session_id);
            $notes = get_field('session_notes', $session_id);
            
            $date_display = $date ? date('M j, Y', strtotime($date)) : 'Unknown';
            
            // Get attendance count using the proper helper function
            $attendance_data = wcb_get_session_attendance($session_id);
            $attended_count = count($attendance_data['attended']);
            $excused_count = count($attendance_data['excused']);
            $total_attendance = $attended_count + $excused_count;
            
            // Check if this is a 1-on-1 session (associated student)
            $associated_student = get_field('associated_student', $session_id);
            if ($associated_student) {
                $attendance_display = '1 student (1-on-1)';
            } else {
                $attendance_display = $total_attendance . ' students';
                if ($attended_count > 0 || $excused_count > 0) {
                    $attendance_display .= ' (' . $attended_count . ' present, ' . $excused_count . ' excused)';
                }
            }
            
            $notes_display = $notes ? wp_trim_words($notes, 10) : 'No notes';
        }
        
        $sessions[] = [
            'id' => $session_id,
            'date' => $date_display,
            'type' => $session_type,
            'attendance' => $attendance_display,
            'notes' => $notes_display
        ];
    }
    
    return $sessions;
}

// ===========================
// School Helper Functions
// ===========================

// Get student count for a school
function wcb_get_school_student_count($school_id) {
    // Try to get the field data
    $students = get_field('student', $school_id);
    
    // If that doesn't work, try with the field key
    if (empty($students)) {
        $students = get_field('field_school_students', $school_id);
    }
    
    // If still empty, try getting raw post meta
    if (empty($students)) {
        $students = get_post_meta($school_id, 'student', true);
    }
    
    $count = is_array($students) ? count($students) : 0;
    
    // Debug log
    wcb_debug_log("wcb_get_school_student_count for school {$school_id}: {$count} students found");
    
    return $count;
}

// Get school students list with detailed information
function wcb_get_school_students($school_id) {
    // Try to get the field data
    $students_data = get_field('student', $school_id);
    
    // If that doesn't work, try with the field key
    if (empty($students_data)) {
        $students_data = get_field('field_school_students', $school_id);
    }
    
    // If still empty, try getting raw post meta
    if (empty($students_data)) {
        $students_data = get_post_meta($school_id, 'student', true);
    }
    
    $students = [];
    
    // Debug log
    wcb_debug_log("wcb_get_school_students for school {$school_id}: Raw data type = " . gettype($students_data));
    
    if (is_array($students_data) && !empty($students_data)) {
        foreach ($students_data as $student) {
            $age = '';
            $dob = '';
            
            // Debug all student fields
            wcb_debug_log("Student fields available: " . implode(', ', array_keys($student)));
            
            // Check if date_of_birth exists and is not empty
            if (!empty($student['date_of_birth'])) {
                $dob = $student['date_of_birth'];
                
                try {
                    // ACF date picker can return dates in different formats
                    // Try to parse the date
                    $birth_date = new DateTime($dob);
                    $today = new DateTime();
                    $age_diff = $today->diff($birth_date);
                    $age = $age_diff->y . ' years';
                    
                    // If age is negative or over 100, something's wrong
                    if ($age_diff->y < 0 || $age_diff->y > 100) {
                        $age = 'Invalid age';
                    }
                } catch (Exception $e) {
                    wcb_debug_log("Error parsing date for student {$student['student_name']}: " . $e->getMessage());
                    $age = 'Unknown';
                }
            } else {
                $age = 'Not provided';
            }
            
            // Debug the date field
            wcb_debug_log("Student: {$student['student_name']}, DOB raw: '{$dob}', Age calculated: {$age}");
            
            $students[] = [
                'name' => isset($student['student_name']) ? $student['student_name'] : 'Unknown',
                'date_of_birth' => $dob,
                'age' => $age,
                'ethnicity' => isset($student['student_ethnicity']) ? $student['student_ethnicity'] : 'Not specified',
                'join_date' => 'N/A', // Schools don't have join dates like memberships
                'status' => 'Enrolled',
                'email' => '' // Schools students don't have emails in this context
            ];
        }
    } elseif (empty($students_data)) {
        // If no students found, return empty array with a note
        wcb_debug_log("wcb_get_school_students: No students found for school {$school_id}. Field might not be set up.");
    }
    
    wcb_debug_log("wcb_get_school_students: Returning " . count($students) . " students");
    
    return $students;
}

// Get session count for a school
function wcb_get_school_session_count($school_id) {
    $args = [
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'selected_school',
                'value' => $school_id,
                'compare' => '='
            ]
        ]
    ];
    
    $sessions = get_posts($args);
    return count($sessions);
}

// Get most recent session date for a school
function wcb_get_school_recent_session($school_id) {
    global $wpdb;
    
    // Query to get the most recent session date for this school
    $query = $wpdb->prepare("
        SELECT pm2.meta_value as session_date
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'session_date'
        WHERE p.post_type = 'session_log'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'selected_school'
        AND pm.meta_value = %d
        AND pm2.meta_value IS NOT NULL
        AND pm2.meta_value != ''
        ORDER BY STR_TO_DATE(pm2.meta_value, '%%Y-%%m-%%dT%%H:%%i:%%s') DESC
        LIMIT 1
    ", $school_id);
    
    $result = $wpdb->get_var($query);
    
    if ($result) {
        return $result;
    }
    
    // Fallback: get the most recent session by post date
    $fallback_query = $wpdb->prepare("
        SELECT p.post_date
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'session_log'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'selected_school'
        AND pm.meta_value = %d
        ORDER BY p.post_date DESC
        LIMIT 1
    ", $school_id);
    
    return $wpdb->get_var($fallback_query);
}

// Get school sessions
function wcb_get_school_sessions($school_id, $limit = 10) {
    global $wpdb;
    
    // Get sessions with proper date ordering
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_date, pm2.meta_value as session_date
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'session_date'
        WHERE p.post_type = 'session_log'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'selected_school'
        AND pm.meta_value = %d
        ORDER BY 
            CASE 
                WHEN pm2.meta_value IS NOT NULL AND pm2.meta_value != '' 
                THEN STR_TO_DATE(pm2.meta_value, '%%Y-%%m-%%dT%%H:%%i:%%s')
                ELSE p.post_date 
            END DESC
        LIMIT %d
    ", $school_id, $limit);
    
    $session_posts = $wpdb->get_results($query);
    $sessions = [];
    
    foreach ($session_posts as $session_post) {
        $session_id = $session_post->ID;
        
        // Use the session_date from query or fall back to getting it directly
        $date = $session_post->session_date;
        if (empty($date)) {
            $date = get_field('session_date', $session_id);
        }
        if (empty($date)) {
            $date = $session_post->post_date;
        }
        
        $instructor = get_field('instructor', $session_id);
        $attendance_names = get_field('school_attendance_names', $session_id);
        $attendance_count = is_array($attendance_names) ? count($attendance_names) : 0;
        
        // Format date properly
        $formatted_date = 'Unknown';
        if ($date) {
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                $formatted_date = date('M j, Y', $timestamp);
            }
        }
        
        $sessions[] = [
            'id' => $session_id,
            'date' => $formatted_date,
            'type' => 'School Session',
            'attendance' => $attendance_count . ' students',
            'notes' => $instructor ? 'Instructor: ' . $instructor : 'No instructor recorded'
        ];
    }
    
    return $sessions;
}