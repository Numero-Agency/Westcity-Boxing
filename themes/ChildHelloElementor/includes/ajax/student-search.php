<?php
// Student Search Component

function student_search_shortcode($atts) {
    $atts = shortcode_atts([
        'class' => 'wcb-student-search',
        'placeholder' => 'Search students by name or email...',
        'show_results' => 'true'
    ], $atts);
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>">
        <div class="search-container">
            <input type="text" 
                  id="wcb-student-search-input" 
                  placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                  class="student-search-input">
          <button type="button" id="wcb-student-search-btn" class="search-btn">
                🔍 Search
            </button>
        </div>
        
        <?php if($atts['show_results'] === 'true'): ?>
        <div id="wcb-student-search-results" class="search-results" style="display: none;">
            <div class="results-header">
                <h4>Search Results</h4>
                <span class="results-count"></span>
            </div>
            <div class="results-list"></div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var searchTimeout;
        
        // Search on input change (with debounce)
        $('#wcb-student-search-input').on('input', function() {
            clearTimeout(searchTimeout);
            var searchTerm = $(this).val().trim();
            
            if(searchTerm.length >= 2) {
                searchTimeout = setTimeout(function() {
                    performStudentSearch(searchTerm);
                }, 300);
            } else {
                $('#wcb-student-search-results').hide();
            }
        });
        
        // Search on button click
        $('#wcb-student-search-btn').on('click', function() {
            var searchTerm = $('#wcb-student-search-input').val().trim();
            if(searchTerm.length >= 1) {
                performStudentSearch(searchTerm);
            }
        });
        
        // Search on Enter key
        $('#wcb-student-search-input').on('keypress', function(e) {
            if(e.which == 13) {
                var searchTerm = $(this).val().trim();
                if(searchTerm.length >= 1) {
                    performStudentSearch(searchTerm);
                }
            }
        });
        
        function performStudentSearch(searchTerm) {
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_search_students',
                    search_term: searchTerm,
                    nonce: wcb_ajax.nonce
                },
                beforeSend: function() {
                    $('#wcb-student-search-btn').text('Searching...');
                    $('.results-list').html('<div class="loading">Searching students...</div>');
                    $('#wcb-student-search-results').show();
                },
                success: function(response) {
                    if(response.success) {
                        var results = response.data.students;
                        var html = '';
                        
                        if(results.length > 0) {
                            results.forEach(function(student) {
                                html += '<div class="student-result-item" data-student-id="' + student.ID + '">';
                                html += '<div class="student-info">';
                                html += '<strong>' + student.display_name + '</strong>';
                                html += '<span class="student-email">' + student.user_email + '</span>';
                                if(student.membership_info) {
                                    html += '<span class="student-membership">' + student.membership_info + '</span>';
                                }
                                html += '</div>';
                                html += '<div class="student-actions">';
                                html += '<a href="#" class="view-profile-btn" data-student-id="' + student.ID + '">View Profile</a>';
                                html += '</div>';
                                html += '</div>';
                            });
                            $('.results-count').text(results.length + ' students found');
                        } else {
                            html = '<div class="no-results">No students found matching "' + searchTerm + '"</div>';
                            $('.results-count').text('0 students found');
                        }
                        
                        $('.results-list').html(html);
                    } else {
                        $('.results-list').html('<div class="error">Error: ' + response.data + '</div>');
                    }
                },
                complete: function() {
                    $('#wcb-student-search-btn').text('🔍 Search');
                },
                error: function() {
                    $('.results-list').html('<div class="error">Search failed. Please try again.</div>');
                    $('#wcb-student-search-btn').text('🔍 Search');
                }
            });
        }
        
        // Handle profile view clicks
        $(document).on('click', '.view-profile-btn', function(e) {
            e.preventDefault();
            var studentId = $(this).data('student-id');
            // Trigger custom event for profile viewing
            $(document).trigger('wcb:show-student-profile', [studentId]);
        });
    });
    </script>
    
    <style>
    .wcb-student-search {
        margin: 20px 0;
    }
    .search-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    .student-search-input {
        flex: 1;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
    }
    .student-search-input:focus {
        outline: none;
        border-color: #007cba;
    }
    .search-btn {
        padding: 12px 20px;
        background: #007cba;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
    }
    .search-btn:hover {
        background: #005a87;
    }
    .search-results {
        border: 1px solid #ddd;
        border-radius: 6px;
        background: white;
        max-height: 400px;
        overflow-y: auto;
    }
    .results-header {
        padding: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .results-header h4 {
        margin: 0;
        font-size: 16px;
    }
    .results-count {
        color: #666;
        font-size: 14px;
    }
    .student-result-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .student-result-item:last-child {
        border-bottom: none;
    }
    .student-result-item:hover {
        background: #f9f9f9;
    }
    .student-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .student-email {
        color: #666;
        font-size: 14px;
    }
    .student-membership {
        color: #007cba;
        font-size: 12px;
        font-weight: bold;
    }
    .view-profile-btn {
        padding: 8px 16px;
        background: #007cba;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
    }
    .view-profile-btn:hover {
        background: #005a87;
        color: white;
    }
    .loading, .no-results, .error {
        padding: 20px;
        text-align: center;
        color: #666;
    }
    .error {
        color: #d63638;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('student_search', 'student_search_shortcode');

// AJAX Handler for student search
function wcb_ajax_search_students() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $search_term = sanitize_text_field($_POST['search_term']);
    
    if (empty($search_term)) {
        wp_send_json_error('Search term is required');
        return;
    }
    
    // Search users by display name and email
    $users = get_users([
        'search' => '*' . $search_term . '*',
        'search_columns' => ['display_name', 'user_email', 'user_login'],
        'role__in' => ['subscriber', 'member'], // Adjust roles as needed
        'number' => 20 // Limit results
    ]);
    
    $students = [];
    $html = '';
    
    if (!empty($users)) {
        foreach ($users as $user) {
            // Get membership information using safe helper
            $membership_info = WCB_MemberPress_Helper::get_membership_display($user->ID);
            
            $students[] = [
                'ID' => $user->ID,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
                'membership_info' => $membership_info
            ];
        }
        
        // Generate HTML for results
        $html .= '<div class="results-header">';
        $html .= '<h4>Search Results</h4>';
        $html .= '<span class="results-count">' . count($students) . ' students found</span>';
        $html .= '</div>';
        $html .= '<div class="results-list">';
        
        foreach ($students as $student) {
            $html .= '<div class="student-result-item" data-student-id="' . $student['ID'] . '">';
            $html .= '<div class="student-info">';
            $html .= '<strong>' . esc_html($student['display_name']) . '</strong>';
            $html .= '<span class="student-email">' . esc_html($student['user_email']) . '</span>';
            if ($student['membership_info']) {
                $html .= '<span class="student-membership">' . esc_html($student['membership_info']) . '</span>';
            }
            $html .= '</div>';
            $html .= '<div class="student-actions">';
            $html .= '<a href="#" class="view-profile-btn" data-student-id="' . $student['ID'] . '">View Profile</a>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
    } else {
        $html = '<div class="no-results">No students found matching "' . esc_html($search_term) . '"</div>';
    }
    
    wp_send_json_success([
        'students' => $students,
        'count' => count($students),
        'html' => $html
    ]);
}
add_action('wp_ajax_wcb_search_students', 'wcb_ajax_search_students');
add_action('wp_ajax_nopriv_wcb_search_students', 'wcb_ajax_search_students');
