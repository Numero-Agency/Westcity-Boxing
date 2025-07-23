<?php
// Dashboard Styles and Scripts Integration

function wcb_dashboard_styles_and_scripts() {
    ?>
    <style>
        /* Enhanced Dashboard Integration Styles */
        
        /* Make sure our dashboard components integrate well */
        .wcb-dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Ensure proper spacing between components */
        .wcb-dashboard-section {
            margin-bottom: 40px;
        }
        
        .wcb-dashboard-section:last-child {
            margin-bottom: 0;
        }
        
        /* Section headers */
        .wcb-section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .wcb-section-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
            font-weight: 700;
        }
        
        .wcb-section-header p {
            margin: 8px 0 0 0;
            color: #6c757d;
            font-size: 16px;
        }
        
        /* Enhanced search results popup behavior */
        .search-results {
            position: relative;
            z-index: 1000;
        }
        
        /* Smooth animations */
        .wcb-student-search,
        .wcb-student-profile-container,
        .dashboard-stats .stat-card {
            animation: slideInUp 0.3s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Loading states */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e9ecef;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Enhanced dashboard functionality
        
        // Auto-focus search input
        $('.student-search-input').focus();
        
        // Add loading states to buttons
        function addLoadingState(button, originalText) {
            button.prop('disabled', true);
            button.data('original-text', originalText);
            button.html('<span class="loading-spinner" style="width: 16px; height: 16px; border-width: 2px; margin-right: 8px; display: inline-block; vertical-align: middle;"></span> Loading...');
        }
        
        function removeLoadingState(button) {
            button.prop('disabled', false);
            button.html(button.data('original-text'));
        }
        
        // Enhanced search functionality with better UX
        $(document).on('click', '.view-profile-btn', function(e) {
            e.preventDefault();
            var button = $(this);
            var originalText = button.text();
            
            addLoadingState(button, originalText);
            
            // Trigger the profile loading
            var studentId = button.data('student-id');
            $(document).trigger('wcb:show-student-profile', [studentId]);
            
            // Scroll to profile container smoothly
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $('#wcb-student-profile-container').offset().top - 20
                }, 500);
                removeLoadingState(button);
            }, 500);
        });
      
        
        // Enhanced profile loading with better error handling
        $(document).on('wcb:show-student-profile', function(e, studentId) {
            if (!studentId) {
                showNotification('Invalid student ID', 'error');
                return;
            }
            
            // Show loading state in profile container
            $('#wcb-student-profile-container').html(
                '<div class="loading-profile">' +
                '<div class="loading-spinner" style="margin: 0 auto 15px;"></div>' +
                '<p>Loading student profile...</p>' +
                '</div>'
            );
            
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_student_profile',
                    student_id: studentId,
                    show_sessions: 'true',
                    show_memberships: 'true',
                    sessions_limit: '10',
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#wcb-student-profile-container').html(response.data.html);
                        showNotification('Profile loaded for ' + response.data.student_name);
                    } else {
                        $('#wcb-student-profile-container').html(
                            '<div class="error">Error loading profile: ' + response.data + '</div>'
                        );
                        showNotification('Error loading profile: ' + response.data, 'error');
                    }
                },
                error: function() {
                    $('#wcb-student-profile-container').html(
                        '<div class="error">Failed to load student profile. Please try again.</div>'
                    );
                    showNotification('Failed to load student profile', 'error');
                }
            });
        });
        
        // Add keyboard shortcuts
        $(document).on('keydown', function(e) {
            // ESC to clear search and profile
            if (e.key === 'Escape') {
                $('.student-search-input').val('');
                $('#wcb-student-search-results').hide();
                $('#wcb-student-profile-container').html(
                    '<div class="profile-placeholder">' +
                    '<div class="placeholder-content">' +
                    '<h3>👤 Student Profile</h3>' +
                    '<p>Use the student search above to select a student and view their profile here.</p>' +
                    '</div>' +
                    '</div>'
                );
            }
            
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('.student-search-input').focus().select();
            }
        });
        
        // Filter functionality for sessions table
        $(document).on('change', '#session-type-filter', function() {
            var filterValue = $(this).val();
            var rows = $('.sessions-table tbody tr');
            
            if (filterValue === '') {
                rows.show();
            } else {
                rows.hide();
                rows.filter('[data-session-type="' + filterValue + '"]').show();
            }
            
            // Update results count
            var visibleRows = rows.filter(':visible').length;
            $('.sessions-header h3').html('📋 Filtered Sessions (' + visibleRows + ' shown)');
        });
        
        // Add tooltips to buttons
        $('[title]').tooltip();
        
        console.log('West City Boxing Dashboard initialized successfully!');
    });
    </script>
    <?php
}

// Hook the styles and scripts
add_action('wp_head', 'wcb_dashboard_styles_and_scripts');
add_action('admin_head', 'wcb_dashboard_styles_and_scripts');
