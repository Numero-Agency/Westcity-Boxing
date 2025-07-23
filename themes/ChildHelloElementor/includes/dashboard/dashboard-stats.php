<?php
// Dashboard Stats Component - Enhanced with Clickable Summary Boxes

function dashboard_stats_shortcode() {
    // Get date range from URL parameters or use defaults
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
    
    // Basic stats - count members active during the selected date range
    $active_members = get_active_members_in_date_range($date_from, $date_to);
    $total_students = count($active_members);
    
    $total_sessions = wp_count_posts('session_log')->publish;
    
    // Get MemberPress memberships count - active during the date range
    $memberships_breakdown = get_active_memberships_breakdown($date_from, $date_to);
    
    // Get non-renewed members within date range
    $non_renewed_members = get_non_renewed_members($date_from, $date_to);
    
    // Get session types count using correct helper function and slugs
    $session_taxonomy = wcb_get_session_type_taxonomy();
    
    $class_sessions = get_posts([
        'post_type' => 'session_log',
        'numberposts' => -1,
        'tax_query' => [
            [
                'taxonomy' => $session_taxonomy,
                'field' => 'slug',
                'terms' => 'class'  // Fixed: use 'class' instead of 'class-session'
            ]
        ]
    ]);
    
    $interventions = get_posts([
        'post_type' => 'session_log',
        'numberposts' => -1,
        'tax_query' => [
            [
                'taxonomy' => $session_taxonomy,
                'field' => 'slug',
                'terms' => 'mentoring'  // Fixed: use 'mentoring' instead of 'mentoring-intervention'
            ]
        ]
    ]);
    
    // Get MemberPress users and their data for the selected date range
    $waitlist_members = get_waitlist_member_count();
    $ethnicity_data = get_member_ethnicity_breakdown($active_members);
    $ethnicity_breakdown = $ethnicity_data['grouped'];
    $ethnicity_detailed = $ethnicity_data['detailed'];
    $age_breakdown = get_member_age_breakdown($active_members);
    
    // Get community class and competition data
    $community_class_members = get_community_class_member_count();
    $total_competitions = get_total_competitions_count();
    
    // Get referrals data for the selected date range
    $total_referrals = get_referrals_count_in_date_range($date_from, $date_to);
    
    ob_start();
    ?>
    <div class="dashboard-stats-container">
        <!-- Date Filter Controls -->
        <div class="dashboard-date-filter">
            <form method="GET" id="dashboard-date-filter-form">
                <div class="date-filter-controls">
                    <div class="date-filter-group">
                        <label for="date_from">From:</label>
                        <input type="date" 
                               id="date_from" 
                               name="date_from" 
                               value="<?php echo esc_attr($date_from); ?>" 
                               class="date-filter-input">
                    </div>
                    <div class="date-filter-group">
                        <label for="date_to">To:</label>
                        <input type="date" 
                               id="date_to" 
                               name="date_to" 
                               value="<?php echo esc_attr($date_to); ?>" 
                               class="date-filter-input">
                    </div>
                    <div class="date-filter-group">
                        <button type="submit" class="date-filter-btn">
                            <span class="dashicons dashicons-search"></span> Filter
                        </button>
                        <button type="button" class="date-filter-reset-btn" onclick="resetDateFilter()">
                            <span class="dashicons dashicons-image-rotate"></span> Reset
                        </button>
                    </div>
                </div>
                <div class="date-filter-info">
                    <small><strong>📊 All dashboard stats below are filtered by this date range:</strong><br>
                    <?php echo date('M j, Y', strtotime($date_from)); ?> to <?php echo date('M j, Y', strtotime($date_to)); ?></small>
                </div>
            </form>
        </div>
        
        <div class="dashboard-stats">
            <!-- Row 1: Core Stats -->
                    <div class="stat-card students">
            <h3><?php echo $total_students; ?></h3>
            <p><span class="dashicons dashicons-admin-users"></span> Active Students</p>
            <small>During selected period</small>
        </div>
            <div class="stat-card sessions clickable-stat" data-popup="sessions">
                <h3><?php echo $total_sessions; ?></h3>
                <p><span class="dashicons dashicons-clipboard"></span> Total Sessions</p>
                <small>Click to view breakdown</small>
            </div>
            <div class="stat-card memberships clickable-stat" data-popup="memberships">
                <h3><?php echo count($memberships_breakdown); ?></h3>
                <p><span class="dashicons dashicons-awards"></span> Active Programs</p>
                <small>Click to view breakdown</small>
            </div>
            <div class="stat-card non-renewed clickable-stat" data-popup="non-renewed">
                <h3><?php echo count($non_renewed_members); ?></h3>
                <p><span class="dashicons dashicons-dismiss"></span> Non-Renewed Members</p>
                <small>Expired in date range</small>
            </div>
        
        <!-- Row 2: Demographics -->
        <div class="stat-card ethnicity clickable-stat" data-popup="ethnicity">
            <h3><?php echo count($ethnicity_breakdown); ?></h3>
            <p><span class="dashicons dashicons-chart-pie"></span> Ethnicity</p>
            <small>Click to view breakdown</small>
        </div>
        <div class="stat-card age-ranges clickable-stat" data-popup="age-ranges">
            <h3><?php echo count(array_filter($age_breakdown, function($count) { return $count > 0; })); ?></h3>
            <p><span class="dashicons dashicons-chart-bar"></span> Active Age Groups</p>
            <small>Click to view breakdown</small>
        </div>
        
        <!-- Row 3: Community and Competition Stats -->
        <div class="stat-card waitlist">
            <h3><?php echo $waitlist_members; ?></h3>
            <p><span class="dashicons dashicons-clock"></span> Members on Waitlist</p>
            <small>Pending membership</small>
        </div>
        <div class="stat-card community-class">
            <h3><?php echo $community_class_members; ?></h3>
            <p><span class="dashicons dashicons-groups"></span> Community Class</p>
            <small>Total members</small>
        </div>
        <div class="stat-card competitions">
            <h3><?php echo $total_competitions; ?></h3>
            <p><span class="dashicons dashicons-awards"></span> Total Competitions</p>
            <small>All competitions</small>
        </div>
        <div class="stat-card referrals">
            <h3><?php echo $total_referrals; ?></h3>
            <p><span class="dashicons dashicons-share"></span> Total Referrals</p>
            <small>During selected period</small>
        </div>
    </div>
    </div>
    
    <!-- Ethnicity Breakdown Popup -->
    <div id="ethnicity-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-chart-pie"></span> Ethnicity Breakdown (<?php echo date('M j', strtotime($date_from)); ?> - <?php echo date('M j, Y', strtotime($date_to)); ?>)</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div class="breakdown-grid">
                    <?php foreach ($ethnicity_breakdown as $ethnicity => $count): ?>
                        <?php 
                        $is_clickable = in_array($ethnicity, ['Pacific Island', 'Asian', 'Other']) && !empty($ethnicity_detailed[$ethnicity]);
                        $item_class = $is_clickable ? 'breakdown-item clickable-breakdown-item' : 'breakdown-item';
                        $data_attr = $is_clickable ? 'data-detail-popup="' . strtolower(str_replace(' ', '-', $ethnicity)) . '"' : '';
                        ?>
                        <div class="<?php echo $item_class; ?>" <?php echo $data_attr; ?>>
                            <div class="breakdown-number"><?php echo $count; ?></div>
                            <div class="breakdown-label"><?php echo ucfirst($ethnicity); ?></div>
                            <div class="breakdown-percentage">
                                <?php echo round(($count / array_sum($ethnicity_breakdown)) * 100, 1); ?>%
                            </div>
                            <?php if ($is_clickable): ?>
                            <div class="breakdown-click-hint">👆 Click for details</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Active Members:</strong> <?php echo array_sum($ethnicity_breakdown); ?></p>
                    <p><strong>Diversity Index:</strong> <?php echo count($ethnicity_breakdown); ?> different ethnic groups represented</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Age Ranges Breakdown Popup -->
    <div id="age-ranges-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-chart-bar"></span> Age Groups (<?php echo date('M j', strtotime($date_from)); ?> - <?php echo date('M j, Y', strtotime($date_to)); ?>)</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div class="breakdown-grid">
                    <?php foreach ($age_breakdown as $age_range => $count): ?>
                        <div class="breakdown-item age-group-<?php echo str_replace('-', '_', $age_range); ?>">
                            <div class="breakdown-number"><?php echo $count; ?></div>
                            <div class="breakdown-label">Ages <?php echo $age_range; ?></div>
                            <div class="breakdown-percentage">
                                <?php echo round(($count / array_sum($age_breakdown)) * 100, 1); ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Active Members:</strong> <?php echo array_sum($age_breakdown); ?></p>
                    <p><strong>Average Age:</strong> <?php echo calculate_average_age($age_breakdown); ?> years</p>
                    <p><strong>Most Common Group:</strong> Ages <?php echo get_largest_age_group($age_breakdown); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sessions Breakdown Popup -->
    <div id="sessions-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-clipboard"></span> Sessions Breakdown</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div class="breakdown-grid">
                    <div class="breakdown-item sessions-class">
                        <div class="breakdown-number"><?php echo count($class_sessions); ?></div>
                        <div class="breakdown-label">Class Sessions</div>
                        <div class="breakdown-percentage">
                            <?php echo $total_sessions > 0 ? round((count($class_sessions) / $total_sessions) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                    <div class="breakdown-item sessions-mentoring">
                        <div class="breakdown-number"><?php echo count($interventions); ?></div>
                        <div class="breakdown-label">Mentoring Class</div>
                        <div class="breakdown-percentage">
                            <?php echo $total_sessions > 0 ? round((count($interventions) / $total_sessions) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Class Sessions:</strong> <?php echo count($class_sessions); ?></p>
                    <p><strong>Mentoring Classes:</strong> <?php echo count($interventions); ?></p>
                    <p><strong>Total Sessions:</strong> <?php echo $total_sessions; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Memberships Breakdown Popup -->
    <div id="memberships-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-awards"></span> Programs (<?php echo date('M j', strtotime($date_from)); ?> - <?php echo date('M j, Y', strtotime($date_to)); ?>)</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div class="breakdown-grid">
                    <?php foreach ($memberships_breakdown as $membership_name => $member_count): ?>
                        <div class="breakdown-item membership-<?php echo sanitize_html_class($membership_name); ?>">
                            <div class="breakdown-number"><?php echo $member_count; ?></div>
                            <div class="breakdown-label"><?php echo esc_html($membership_name); ?></div>
                            <div class="breakdown-percentage">
                                <?php echo array_sum($memberships_breakdown) > 0 ? round(($member_count / array_sum($memberships_breakdown)) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Active Programs:</strong> <?php echo count($memberships_breakdown); ?></p>
                    <p><strong>Active Members:</strong> <?php echo array_sum($memberships_breakdown); ?></p>
                    <p><strong>Most Popular Program:</strong> <?php echo get_most_popular_membership($memberships_breakdown); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Non-Renewed Members Popup -->
    <div id="non-renewed-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-dismiss"></span> Non-Renewed Members</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div class="non-renewed-header">
                    <p><strong>Members whose memberships expired between <?php echo date('M j, Y', strtotime($date_from)); ?> and <?php echo date('M j, Y', strtotime($date_to)); ?> and did not renew</strong></p>
                    <p><small>Debug info: Found <?php echo count($non_renewed_members); ?> non-renewed members. Check your error log for detailed debugging info.</small></p>
                </div>
                <?php if (!empty($non_renewed_members)): ?>
                <div class="non-renewed-list">
                    <table class="non-renewed-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Expired Date</th>
                                <th>Days Since Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($non_renewed_members as $member): ?>
                            <tr>
                                <td><?php echo esc_html($member['name']); ?></td>
                                <td><?php echo esc_html($member['email']); ?></td>
                                <td><?php echo esc_html($member['program']); ?></td>
                                <td><?php echo esc_html($member['expired_date']); ?></td>
                                <td><?php echo esc_html($member['days_since_expiry']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Total Non-Renewed:</strong> <?php echo count($non_renewed_members); ?></p>
                    <p><strong>Date Range:</strong> <?php echo date('M j, Y', strtotime($date_from)); ?> to <?php echo date('M j, Y', strtotime($date_to)); ?></p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No non-renewed members found in the selected date range.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Pacific Island Details Popup -->
    <div id="pacific-island-details-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-admin-site-alt3"></span> Pacific Island Breakdown</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php if (!empty($ethnicity_detailed['Pacific Island'])): ?>
                <div class="breakdown-grid">
                    <?php foreach ($ethnicity_detailed['Pacific Island'] as $ethnicity => $count): ?>
                        <div class="breakdown-item">
                            <div class="breakdown-number"><?php echo $count; ?></div>
                            <div class="breakdown-label"><?php echo esc_html($ethnicity); ?></div>
                            <div class="breakdown-percentage">
                                <?php echo round(($count / array_sum($ethnicity_detailed['Pacific Island'])) * 100, 1); ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Total Pacific Island Members:</strong> <?php echo array_sum($ethnicity_detailed['Pacific Island']); ?></p>
                    <p><strong>Different Pacific Island Groups:</strong> <?php echo count($ethnicity_detailed['Pacific Island']); ?></p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No Pacific Island ethnicity data available.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Asian Details Popup -->
    <div id="asian-details-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-admin-site-alt3"></span> Asian Breakdown</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php if (!empty($ethnicity_detailed['Asian'])): ?>
                <div class="breakdown-grid">
                    <?php foreach ($ethnicity_detailed['Asian'] as $ethnicity => $count): ?>
                        <div class="breakdown-item">
                            <div class="breakdown-number"><?php echo $count; ?></div>
                            <div class="breakdown-label"><?php echo esc_html($ethnicity); ?></div>
                            <div class="breakdown-percentage">
                                <?php echo round(($count / array_sum($ethnicity_detailed['Asian'])) * 100, 1); ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Total Asian Members:</strong> <?php echo array_sum($ethnicity_detailed['Asian']); ?></p>
                    <p><strong>Different Asian Groups:</strong> <?php echo count($ethnicity_detailed['Asian']); ?></p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No Asian ethnicity data available.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Other Details Popup -->
    <div id="other-details-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-admin-site-alt3"></span> Other Ethnicity Breakdown</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php if (!empty($ethnicity_detailed['Other'])): ?>
                <div class="breakdown-grid">
                    <?php foreach ($ethnicity_detailed['Other'] as $ethnicity => $count): ?>
                        <div class="breakdown-item">
                            <div class="breakdown-number"><?php echo $count; ?></div>
                            <div class="breakdown-label"><?php echo esc_html($ethnicity); ?></div>
                            <div class="breakdown-percentage">
                                <?php echo round(($count / array_sum($ethnicity_detailed['Other'])) * 100, 1); ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Total Other Ethnicity Members:</strong> <?php echo array_sum($ethnicity_detailed['Other']); ?></p>
                    <p><strong>Different Other Groups:</strong> <?php echo count($ethnicity_detailed['Other']); ?></p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No other ethnicity data available.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle clickable stat cards
        $('.clickable-stat').on('click', function() {
            const popupId = $(this).data('popup') + '-popup';
            $('#' + popupId).fadeIn(300);
            $('body').addClass('popup-open');
        });
        
        // Handle clickable breakdown items (ethnicity details)
        $('.clickable-breakdown-item').on('click', function() {
            const detailPopup = $(this).data('detail-popup');
            if (detailPopup) {
                const popupId = detailPopup + '-details-popup';
                $('#' + popupId).fadeIn(300);
                $('body').addClass('popup-open');
            }
        });
        
        // Handle popup close
        $('.popup-close, .popup-overlay').on('click', function(e) {
            e.stopPropagation();
            // Check if this is a detail popup (Pacific Island, Asian, Other)
            const popupId = $(this).closest('.stats-popup').attr('id');
            if (popupId && (popupId.includes('-details-popup'))) {
                // Close only the detail popup, keep main popup open
                $(this).closest('.stats-popup').fadeOut(300);
            } else {
                // Close all popups for main popup close
                $('.stats-popup').fadeOut(300);
                $('body').removeClass('popup-open');
            }
        });
        
        // ESC key to close popup
        $(document).on('keyup', function(e) {
            if (e.keyCode === 27) {
                $('.stats-popup').fadeOut(300);
                $('body').removeClass('popup-open');
            }
        });
    });
    
    // Reset date filter to default (last 30 days)
    function resetDateFilter() {
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        document.getElementById('date_from').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('date_to').value = today.toISOString().split('T')[0];
        
        // Submit the form
        document.getElementById('dashboard-date-filter-form').submit();
    }
    </script>
    
    <style>
    /* Modern Minimalistic Black & White Dashboard Stats */
    .dashboard-stats-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        margin-bottom: 40px;
    }
    
    /* Date Filter Styles */
    .dashboard-date-filter {
        background: white;
        border: 1px solid #e5e5e5;
        border-bottom: 2px solid #000000;
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    
    .date-filter-controls {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .date-filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .date-filter-group label {
        font-size: 14px;
        font-weight: 600;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .date-filter-input {
        padding: 8px 12px !important;
        border: 1px solid #e5e5e5 !important;
        background: white;
        color: #000000;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        transition: border-color 0.2s ease;
    }
    
    .date-filter-input:focus {
        border-color: #000000 !important;
    }
    
    .date-filter-btn,
    .date-filter-reset-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: #000000;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .date-filter-reset-btn {
        background: #666666;
    }
    
    .date-filter-btn:hover {
        background: #333333;
        transform: translateY(-1px);
    }
    
    .date-filter-reset-btn:hover {
        background: #888888;
        transform: translateY(-1px);
    }
    
    .date-filter-btn .dashicons,
    .date-filter-reset-btn .dashicons {
        font-size: 16px;
    }
    
    .date-filter-info {
        margin-top: 12px;
        padding: 12px;
        background: #e8f4f8;
        border: 1px solid #bee5eb;
        border-radius: 6px;
    }
    
    .date-filter-info small {
        color: #2c3e50;
        font-size: 13px;
        font-weight: 500;
    }
    
    .date-filter-info strong {
        color: #1a5490;
    }
    
    .dashboard-stats {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-top: 4px solid #e5e5e5;
        padding: 24px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
    }
    
    /* Colorful top borders for different stat cards */
    .stat-card.students {
        border-top-color: #A0C6FF;
    }
    
    .stat-card.sessions {
        border-top-color: #CFF5D1;
    }
    
    .stat-card.memberships {
        border-top-color: #FFE0CC;
    }
    
    .stat-card.waitlist {
        border-top-color: #999999;
    }
    
    .stat-card.classes {
        border-top-color: #E0DAFD;
    }
    
    .stat-card.interventions {
        border-top-color: #FFB68E;
    }
    
    .stat-card.ethnicity {
        border-top-color: #C1F5F0;
    }
    
    .stat-card.age-ranges {
        border-top-color: #9AE095;
    }
    
    .stat-card.community-class {
        border-top-color: #D1E2FF;
    }
    
    .stat-card.competitions {
        border-top-color: #FFD700;
    }
    
    .stat-card.non-renewed {
        border-top-color: #FF6B6B;
    }
    
    .stat-card.referrals {
        border-top-color: #87CEEB;
    }
    
    .stat-card:hover {
        background: #fafafa;
        transform: translateY(-1px);
    }
    
    .stat-card.clickable-stat {
        cursor: pointer;
    }
    
    .stat-card.clickable-stat:hover {
        border-color: #000000;
    }
    
    .stat-card h3 {
        font-size: 32px;
        font-weight: 700;
        color: #000000;
        margin: 0 0 8px 0;
        line-height: 1;
    }
    
    .stat-card p {
        font-size: 14px;
        font-weight: 600;
        color: #000000;
        margin: 0 0 4px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .stat-card p .dashicons {
        font-size: 16px;
        color: #666666;
    }
    
    .stat-card small {
        font-size: 12px;
        color: #666666;
        font-weight: 400;
    }
    
    /* Popup Styles */
    .stats-popup {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }
    
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
    }
    
    .popup-content {
        background: white;
        border: 1px solid #e5e5e5;
        max-width: 600px;
        width: 100%;
        max-height: 80vh;
        overflow-y: auto;
        z-index: 10001;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    
    .popup-header {
        background: #000000;
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .popup-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    
    .popup-header h3 .dashicons {
        font-size: 20px;
        color: white;
    }
    
    .popup-close {
        background: none;
        border: none;
        font-size: 24px;
        color: white;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .popup-close:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .popup-body {
        padding: 24px;
    }
    
    .breakdown-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .breakdown-item {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        padding: 16px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .breakdown-item:hover {
        background: #ffffff;
        border-color: #000000;
    }
    
    .clickable-breakdown-item {
        cursor: pointer;
    }
    
    .clickable-breakdown-item:hover {
        background: #ffffff;
        border-color: #000000;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .breakdown-click-hint {
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        color: #666666;
        background: rgba(255, 255, 255, 0.95);
        padding: 2px 6px;
        border-radius: 3px;
        border: 1px solid #e5e5e5;
        opacity: 0;
        transition: opacity 0.2s ease;
        white-space: nowrap;
    }
    
    .clickable-breakdown-item:hover .breakdown-click-hint {
        opacity: 1;
    }
    
    .breakdown-number {
        font-size: 24px;
        font-weight: 700;
        color: #000000;
        margin-bottom: 8px;
        line-height: 1;
    }
    
    .breakdown-label {
        font-size: 12px;
        font-weight: 600;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .breakdown-percentage {
        font-size: 11px;
        color: #666666;
        font-weight: 500;
    }
    
    .breakdown-summary {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        padding: 16px;
        border-left: 4px solid #000000;
    }
    
    .breakdown-summary p {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #000000;
    }
    
    .breakdown-summary p:last-child {
        margin-bottom: 0;
    }
    
    .breakdown-summary strong {
        font-weight: 600;
    }
    
    /* Prevent body scroll when popup is open */
    body.popup-open {
        overflow: hidden;
    }
    
    /* Non-Renewed Members Popup Styles */
    .non-renewed-header {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #FF6B6B;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .non-renewed-header p {
        margin: 0;
        font-size: 14px;
        color: #000000;
    }
    
    .non-renewed-list {
        margin-bottom: 20px;
    }
    
    .non-renewed-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: white;
    }
    
    .non-renewed-table th {
        background: #f8f9fa;
        color: #000000;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid #e5e5e5;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .non-renewed-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        color: #000000;
    }
    
    .non-renewed-table tr:hover {
        background: #fafafa;
    }
    
    .no-data {
        text-align: center;
        color: #666666;
        padding: 40px;
        font-style: italic;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-date-filter {
            padding: 16px 20px;
        }
        
        .date-filter-controls {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }
        
        .date-filter-group {
            flex-direction: column;
            gap: 4px;
            align-items: stretch;
        }
        
        .date-filter-input {
            width: 100% !important;
        }
        
        .dashboard-stats {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .stat-card {
            padding: 20px;
        }
        
        .stat-card h3 {
            font-size: 28px;
        }
        
        .stat-card p {
            font-size: 13px;
        }
        
        .popup-content {
            margin: 10px auto;
            max-height: 90vh;
            width: calc(100% - 20px);
        }
        
        .popup-header {
            padding: 16px 20px;
        }
        
        .popup-header h3 {
            font-size: 16px;
        }
        
        .popup-body {
            padding: 20px;
        }
        
        .breakdown-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .breakdown-item {
            padding: 12px;
        }
        
        .breakdown-number {
            font-size: 20px;
        }
        
        .breakdown-summary {
            padding: 12px;
        }
        
        .breakdown-summary p {
            font-size: 13px;
        }
        
        .non-renewed-table {
            font-size: 13px;
        }
        
        .non-renewed-table th,
        .non-renewed-table td {
            padding: 10px 12px;
        }
        
        .non-renewed-table th:nth-child(5),
        .non-renewed-table td:nth-child(5) {
            display: none;
        }
    }
    
    @media (max-width: 480px) {
        .dashboard-date-filter {
            padding: 12px 16px;
        }
        
        .date-filter-group {
            gap: 2px;
        }
        
        .date-filter-btn,
        .date-filter-reset-btn {
            padding: 8px 12px;
            font-size: 13px;
        }
        
        .dashboard-stats {
            grid-template-columns: 1fr;
        }
        
        .breakdown-grid {
            grid-template-columns: 1fr;
        }
        
        .non-renewed-table th:nth-child(4),
        .non-renewed-table td:nth-child(4) {
            display: none;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

// Helper function to get all active members across all programs (current)
function get_all_active_members() {
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return [];
    }
    
    // Get all users with active MemberPress memberships across all programs
    $results = $wpdb->get_results("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ORDER BY u.ID
    ");
    
    return array_column($results, 'ID');
}

// Helper function to get members who were active during a specific date range
function get_active_members_in_date_range($date_from, $date_to) {
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return [];
    }
    
    // DEBUG: Log the date range query
    error_log("DEBUG: Getting active members from $date_from to $date_to");
    
    // Get users who had active memberships during the specified date range
    // A membership is "active during range" if:
    // 1. It was created before or during the range AND
    // 2. It expires after the start of the range (or never expires)
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND DATE(t.created_at) <= %s
        AND (
            t.expires_at IS NULL 
            OR t.expires_at = '0000-00-00 00:00:00' 
            OR DATE(t.expires_at) >= %s
        )
        ORDER BY u.ID
    ", $date_to, $date_from));
    
    $member_ids = array_column($results, 'ID');
    
    // DEBUG: Log the count
    error_log("DEBUG: Found " . count($member_ids) . " active members in date range");
    
    return $member_ids;
}

// Helper function to get non-renewed members within a date range
function get_non_renewed_members($date_from, $date_to) {
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return [];
    }
    
    // DEBUG: Let's see what we're actually querying
    error_log("DEBUG: Querying non-renewed members from $date_from to $date_to");
    
    // Get all transactions that expired within the date range
    $expired_transactions = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, u.display_name, u.user_email, p.post_title as program_name
        FROM {$txn_table} t
        JOIN {$wpdb->users} u ON t.user_id = u.ID
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.status IN ('confirmed', 'complete')
        AND t.expires_at IS NOT NULL 
        AND t.expires_at != '0000-00-00 00:00:00'
        AND DATE(t.expires_at) BETWEEN %s AND %s
        ORDER BY t.expires_at DESC
    ", $date_from, $date_to));
    
    // DEBUG: Log how many expired transactions we found
    error_log("DEBUG: Found " . count($expired_transactions) . " expired transactions in date range");
    
    $non_renewed_members = [];
    
    foreach ($expired_transactions as $expired_txn) {
        $user_id = $expired_txn->user_id;
        
        // Check if this user renewed THIS SPECIFIC membership or got a new one after expiry
        $renewed_membership = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$txn_table} t
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND t.created_at > %s
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ", $user_id, $expired_txn->expires_at));
        
        // If no new membership after expiry, this user didn't renew
        if ($renewed_membership == 0) {
            $expired_date = date('M j, Y', strtotime($expired_txn->expires_at));
            $days_since_expiry = floor((time() - strtotime($expired_txn->expires_at)) / (60 * 60 * 24));
            
            // Avoid duplicates (same user with multiple expired memberships)
            $user_key = $user_id . '_' . $expired_txn->product_id;
            if (!isset($non_renewed_members[$user_key])) {
                $non_renewed_members[$user_key] = [
                    'name' => $expired_txn->display_name,
                    'email' => $expired_txn->user_email,
                    'program' => $expired_txn->program_name,
                    'expired_date' => $expired_date,
                    'days_since_expiry' => $days_since_expiry . ' days'
                ];
                
                // DEBUG: Log each non-renewed member
                error_log("DEBUG: Non-renewed member: {$expired_txn->display_name} - {$expired_txn->program_name} expired on {$expired_date}");
            }
        }
    }
    
    // DEBUG: Log final count
    error_log("DEBUG: Final non-renewed count: " . count($non_renewed_members));
    
    return array_values($non_renewed_members);
}

// Helper function to get waitlist member count
function get_waitlist_member_count() {
    return WCB_MemberPress_Helper::get_waitlist_count();
}

// Helper function to get member ethnicity breakdown
function get_member_ethnicity_breakdown($active_members = null) {
    global $wpdb;
    
    // If no specific member list provided, get all current active members
    if ($active_members === null) {
        $active_members = get_all_active_members();
    }
    
    if (empty($active_members)) {
        return [];
    }
    
    $member_ids = implode(',', $active_members);
    
    // Get ethnicity data for active members only
    $all_ethnicities = $wpdb->get_results("
        SELECT meta_value 
        FROM {$wpdb->usermeta} 
        WHERE user_id IN ($member_ids)
        AND meta_key = 'mepr_ethnicity' 
        AND meta_value != '' 
        AND meta_value != 'Not specified'
        AND meta_value IS NOT NULL
    ");
    
    // Define Polynesian ethnicity patterns
    $polynesian_patterns = [
        'samoan', 'samoa', 'tongan', 'tonga', 'fijian', 'fiji', 'cook island', 'cook islands',
        'tahitian', 'tahiti', 'hawaiian', 'hawaii', 'niuean', 'niue', 'tokelauan', 'tokelau',
        'tuvaluan', 'tuvalu', 'kiribati', 'marshal', 'solomon', 'vanuatu', 'polynesian', 'pacific'
    ];
    
    $grouped_breakdown = [
        'Māori' => 0,
        'Pacific Island' => 0,
        'NZ European' => 0,
        'Asian' => 0,
        'Other' => 0
    ];
    
    // Detailed breakdowns for clickable groups
    $detailed_breakdowns = [
        'Pacific Island' => [],
        'Asian' => [],
        'Other' => []
    ];
    
    foreach ($all_ethnicities as $ethnicity_data) {
        $ethnicity_value = trim(strtolower($ethnicity_data->meta_value));
        
        if (empty($ethnicity_value)) {
            continue;
        }
        
        // First check the full string for patterns before splitting
        $found_categories = [];
        
        // Check for Māori in full string first (highest priority)
        if (strpos($ethnicity_value, 'maori') !== false || strpos($ethnicity_value, 'māori') !== false) {
            $found_categories['Māori'] = true;
        }
        
        // Check for Polynesian patterns in full string
        foreach ($polynesian_patterns as $pattern) {
            if (strpos($ethnicity_value, $pattern) !== false) {
                $found_categories['Polynesia'] = true;
                break;
            }
        }
        
        // Check for Asian patterns in full string
        $asian_patterns = ['chinese', 'indian', 'japanese', 'korean', 'filipino', 'thai', 'vietnamese', 'asian'];
        foreach ($asian_patterns as $pattern) {
            if (strpos($ethnicity_value, $pattern) !== false) {
                $found_categories['Asian'] = true;
                break;
            }
        }
        
        // Check for NZ/European patterns in full string
        if (strpos($ethnicity_value, 'new zealand') !== false || 
            strpos($ethnicity_value, 'nz') !== false || 
            strpos($ethnicity_value, 'kiwi') !== false) {
            $found_categories['New Zealand'] = true;
        }
        
        $european_patterns = ['european', 'british', 'english', 'irish', 'scottish', 'welsh', 'german', 'dutch', 'french', 'italian', 'spanish', 'pakeha'];
        foreach ($european_patterns as $pattern) {
            if (strpos($ethnicity_value, $pattern) !== false) {
                $found_categories['European'] = true;
                break;
            }
        }
        
        // If no specific category found, split and check individual parts
        if (empty($found_categories)) {
            // Split by common delimiters to handle mixed ethnicities
            $ethnicities = preg_split('/[,;&\/\-\s]+/', $ethnicity_value);
            $ethnicities = array_filter(array_map('trim', $ethnicities));
            
            foreach ($ethnicities as $single_ethnicity) {
                $single_ethnicity = trim(strtolower($single_ethnicity));
                
                // Check for Māori (highest priority after specific countries)
                if (strpos($single_ethnicity, 'maori') !== false || strpos($single_ethnicity, 'māori') !== false) {
                    $found_categories['Māori'] = true;
                }
                // Check for Polynesian ethnicities
                else {
                    $is_polynesian = false;
                    foreach ($polynesian_patterns as $pattern) {
                        if (strpos($single_ethnicity, $pattern) !== false) {
                            $found_categories['Polynesia'] = true;
                            $is_polynesian = true;
                            break;
                        }
                    }
                    
                        // If not Polynesian, categorize into other groups
                        if (!$is_polynesian) {
                            // Check for New Zealand (separate from European)
                            if (strpos($single_ethnicity, 'new zealand') !== false || 
                                strpos($single_ethnicity, 'nz') !== false || 
                                strpos($single_ethnicity, 'kiwi') !== false) {
                                $found_categories['New Zealand'] = true;
                            }
                            // Check for European (excluding NZ)
                            elseif (strpos($single_ethnicity, 'european') !== false || 
                                    strpos($single_ethnicity, 'british') !== false || 
                                    strpos($single_ethnicity, 'english') !== false || 
                                    strpos($single_ethnicity, 'irish') !== false || 
                                    strpos($single_ethnicity, 'scottish') !== false || 
                                    strpos($single_ethnicity, 'welsh') !== false || 
                                    strpos($single_ethnicity, 'german') !== false || 
                                    strpos($single_ethnicity, 'dutch') !== false || 
                                    strpos($single_ethnicity, 'french') !== false || 
                                    strpos($single_ethnicity, 'italian') !== false || 
                                    strpos($single_ethnicity, 'spanish') !== false || 
                                    strpos($single_ethnicity, 'pakeha') !== false) {
                                $found_categories['European'] = true;
                            }
                            // Check for Asian (highest priority for mixed)
                            elseif (strpos($single_ethnicity, 'chinese') !== false || 
                                    strpos($single_ethnicity, 'indian') !== false || 
                                    strpos($single_ethnicity, 'japanese') !== false || 
                                    strpos($single_ethnicity, 'korean') !== false || 
                                    strpos($single_ethnicity, 'filipino') !== false || 
                                    strpos($single_ethnicity, 'thai') !== false || 
                                    strpos($single_ethnicity, 'vietnamese') !== false || 
                                    strpos($single_ethnicity, 'asian') !== false) {
                                $found_categories['Asian'] = true;
                            } else {
                                // Only assign to Other if it doesn't match any specific category
                                $found_categories['Other'] = true;
                            }
                        }
                    }
                }
            }
        
        // If still no categories found, assign to Other
        if (empty($found_categories)) {
            $found_categories['Other'] = true;
        }
        
        // Priority assignment: Māori > Asian > Pacific Island > NZ European > Other
        if (isset($found_categories['Māori'])) {
            $assigned_category = 'Māori';
        } elseif (isset($found_categories['Asian'])) {
            $assigned_category = 'Asian';
            // Store detailed breakdown for Asian
            $detailed_breakdowns['Asian'][$ethnicity_value] = isset($detailed_breakdowns['Asian'][$ethnicity_value]) ? $detailed_breakdowns['Asian'][$ethnicity_value] + 1 : 1;
        } elseif (isset($found_categories['Polynesia'])) {
            $assigned_category = 'Pacific Island';
            // Store detailed breakdown for Pacific Island
            $detailed_breakdowns['Pacific Island'][$ethnicity_value] = isset($detailed_breakdowns['Pacific Island'][$ethnicity_value]) ? $detailed_breakdowns['Pacific Island'][$ethnicity_value] + 1 : 1;
        } elseif (isset($found_categories['New Zealand']) || isset($found_categories['European'])) {
            $assigned_category = 'NZ European';
        } elseif (isset($found_categories['Other'])) {
            $assigned_category = 'Other';
            // Store detailed breakdown for Other
            $detailed_breakdowns['Other'][$ethnicity_value] = isset($detailed_breakdowns['Other'][$ethnicity_value]) ? $detailed_breakdowns['Other'][$ethnicity_value] + 1 : 1;
        }
        
        // Assign to the determined category
        if ($assigned_category) {
            $grouped_breakdown[$assigned_category]++;
        }
    }
    
    // Remove empty categories
    $grouped_breakdown = array_filter($grouped_breakdown, function($count) {
        return $count > 0;
    });
    
    // If no real data exists, get count of active members with missing ethnicity data
    if (empty($grouped_breakdown)) {
        $no_data_count = $wpdb->get_var("
            SELECT COUNT(DISTINCT u.ID) 
            FROM {$wpdb->users} u 
            LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_ethnicity'
            WHERE u.ID IN ($member_ids)
            AND (um.meta_value IS NULL OR um.meta_value = '' OR um.meta_value = 'Not specified')
        ");
        
        if ($no_data_count > 0) {
            $grouped_breakdown['Not Specified'] = intval($no_data_count);
        }
    }
    
    return [
        'grouped' => $grouped_breakdown,
        'detailed' => $detailed_breakdowns
    ];
}

// Helper function to get member age breakdown
function get_member_age_breakdown($active_members = null) {
    global $wpdb;
    
    // If no specific member list provided, get all current active members
    if ($active_members === null) {
        $active_members = get_all_active_members();
    }
    
    if (empty($active_members)) {
        return [
            '9-11' => 0,
            '12-14' => 0,
            '15-18' => 0,
            '18-24' => 0,
            '24+' => 0
        ];
    }
    
    $member_ids = implode(',', $active_members);
    
    // Get age data for active members only, prioritizing mepr_age field
    $age_data = $wpdb->get_results("
        SELECT DISTINCT um.user_id, um.meta_value, um.meta_key
        FROM {$wpdb->usermeta} um
        WHERE um.user_id IN ($member_ids)
        AND um.meta_key = 'mepr_age'
        AND um.meta_value != ''
        AND um.meta_value IS NOT NULL
        ORDER BY um.user_id
    ");
    
    $age_groups = [
        '9-11' => 0,
        '12-14' => 0,
        '15-18' => 0,
        '18-24' => 0,
        '24+' => 0
    ];
    
    $processed_users = [];
    
    foreach ($age_data as $data) {
        // Skip if we already processed this user (avoid double counting)
        if (in_array($data->user_id, $processed_users)) {
            continue;
        }
        
        $age = calculate_age_from_data($data->meta_value);
        
        // Only process if we got a valid age
        if ($age !== null) {
            $processed_users[] = $data->user_id;
            
            if ($age >= 9 && $age <= 11) {
                $age_groups['9-11']++;
            } elseif ($age >= 12 && $age <= 14) {
                $age_groups['12-14']++;
            } elseif ($age >= 15 && $age <= 17) {
                $age_groups['15-18']++;
            } elseif ($age >= 18 && $age <= 24) {
                $age_groups['18-24']++;
            } elseif ($age >= 25) {
                $age_groups['24+']++;
            }
        }
    }
    
    return $age_groups;
}

// Helper function to calculate age from mepr_age field
function calculate_age_from_data($age_value) {
    // Clean the input
    $age_value = trim($age_value);
    
    // The mepr_age field should contain a numeric age value
    if (is_numeric($age_value) && $age_value > 0 && $age_value < 120) {
        return intval($age_value);
    }
    
    // If we can't determine age, return null instead of a default
    // This way we don't count invalid/missing data
    return null;
}

// Helper function to calculate average age
function calculate_average_age($age_breakdown) {
    $total_members = array_sum($age_breakdown);
    if ($total_members == 0) return 0;
    
    $weighted_sum = 0;
    foreach ($age_breakdown as $range => $count) {
        if ($range === '24+') {
            // For 24+ group, use 30 as the average (reasonable assumption)
            $average_for_range = 30;
        } else {
            // For ranges like '9-11', '12-14', etc.
            $parts = explode('-', $range);
            if (count($parts) == 2) {
                $min = intval($parts[0]);
                $max = intval($parts[1]);
                $average_for_range = ($min + $max) / 2;
            } else {
                // Fallback if format is unexpected
                $average_for_range = 15;
            }
        }
        $weighted_sum += $average_for_range * $count;
    }
    
    return round($weighted_sum / $total_members, 1);
}

// Helper function to get largest age group
function get_largest_age_group($age_breakdown) {
    if (empty($age_breakdown)) {
        return 'No data available';
    }
    
    $max_count = max($age_breakdown);
    if ($max_count == 0) {
        return 'No members with age data';
    }
    
    foreach ($age_breakdown as $range => $count) {
        if ($count == $max_count) {
            return $range;
        }
    }
    return 'Unknown'; // fallback
}

// Helper function to get active memberships breakdown
function get_active_memberships_breakdown($date_from = null, $date_to = null) {
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return [];
    }
    
    // Get all published MemberPress products
    $memberships = get_posts([
        'post_type' => 'memberpressproduct',
        'numberposts' => -1,
        'post_status' => 'publish'
    ]);
    
    $memberships_breakdown = [];
    
    foreach ($memberships as $membership) {
        if ($date_from && $date_to) {
            // Get members who were active during the date range for this specific membership
            $results = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT u.ID
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                WHERE t.product_id = %d 
                AND t.status IN ('confirmed', 'complete')
                AND DATE(t.created_at) <= %s
                AND (
                    t.expires_at IS NULL 
                    OR t.expires_at = '0000-00-00 00:00:00' 
                    OR DATE(t.expires_at) >= %s
                )
            ", $membership->ID, $date_to, $date_from));
        } else {
            // Use current active logic
            $results = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT u.ID
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                WHERE t.product_id = %d 
                AND t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            ", $membership->ID));
        }
        
        $member_count = count($results);
        
        // Only include memberships with active members
        if ($member_count > 0) {
            $memberships_breakdown[$membership->post_title] = $member_count;
        }
    }
    
    return $memberships_breakdown;
}

// Helper function to get most popular membership
function get_most_popular_membership($memberships_breakdown) {
    if (empty($memberships_breakdown)) {
        return 'No active programs';
    }
    
    $max_count = max($memberships_breakdown);
    if ($max_count == 0) {
        return 'No active members';
    }
    
    foreach ($memberships_breakdown as $membership_name => $member_count) {
        if ($member_count == $max_count) {
            return $membership_name;
        }
    }
    
    return 'Unknown'; // fallback
}

// Helper function to get community class member count
function get_community_class_member_count() {
    global $wpdb;
    
    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return 0;
    }
    
    // Get Community Class membership ID
    $community_class_membership = get_posts([
        'post_type' => 'memberpressproduct',
        'title' => 'Community Class',
        'post_status' => 'publish',
        'numberposts' => 1
    ]);
    
    if (empty($community_class_membership)) {
        return 0;
    }
    
    $community_class_id = $community_class_membership[0]->ID;
    
    // Use the same logic as other membership counts
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d 
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ", $community_class_id));
    
    return count($results);
}

// Helper function to get total competitions count
function get_total_competitions_count() {
    // Check if there's a competition post type
    $competitions = get_posts([
        'post_type' => 'competition',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);
    
    if (!empty($competitions)) {
        return count($competitions);
    }
    
    // Alternative: Check for sessions marked as competitions
    $competition_sessions = get_posts([
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => [
            [
                'key' => 'session_type',
                'value' => 'competition',
                'compare' => 'LIKE'
            ]
        ]
    ]);
    
    if (!empty($competition_sessions)) {
        return count($competition_sessions);
    }
    
    // Final fallback: Check if there's a taxonomy for competitions
    $session_taxonomy = wcb_get_session_type_taxonomy();
    if ($session_taxonomy) {
        $competition_sessions = get_posts([
            'post_type' => 'session_log',
            'post_status' => 'publish',
            'numberposts' => -1,
            'tax_query' => [
                [
                    'taxonomy' => $session_taxonomy,
                    'field' => 'slug',
                    'terms' => 'competition'
                ]
            ]
        ]);
        
        return count($competition_sessions);
    }
    
    return 0; // fallback
}

// Helper function to get referrals count in date range
function get_referrals_count_in_date_range($date_from, $date_to) {
    global $wpdb;
    
    // Method 1: Check for referrals stored as custom post type
    $referrals_posts = get_posts([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'numberposts' => -1,
        'date_query' => [
            [
                'after' => $date_from,
                'before' => $date_to,
                'inclusive' => true,
            ]
        ]
    ]);
    
    if (!empty($referrals_posts)) {
        return count($referrals_posts);
    }
    
    // Method 2: Check for referrals stored in user meta during date range
    // This looks for users who were referred during the date range
    $referrals_meta = $wpdb->get_results($wpdb->prepare("
        SELECT COUNT(DISTINCT u.ID) as referral_count
        FROM {$wpdb->users} u
        JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
        WHERE um.meta_key = 'referral_date'
        AND DATE(um.meta_value) BETWEEN %s AND %s
        AND um.meta_value IS NOT NULL
        AND um.meta_value != ''
    ", $date_from, $date_to));
    
    if (!empty($referrals_meta) && $referrals_meta[0]->referral_count > 0) {
        return intval($referrals_meta[0]->referral_count);
    }
    
    // Method 3: Check for referrals stored as user registrations with referral info
    // This looks for users who registered during the date range and have referral data
    $referrals_users = $wpdb->get_results($wpdb->prepare("
        SELECT COUNT(DISTINCT u.ID) as referral_count
        FROM {$wpdb->users} u
        JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
        WHERE um.meta_key IN ('referred_by', 'referral_source', 'mepr_referral_code')
        AND DATE(u.user_registered) BETWEEN %s AND %s
        AND um.meta_value IS NOT NULL
        AND um.meta_value != ''
    ", $date_from, $date_to));
    
    if (!empty($referrals_users) && $referrals_users[0]->referral_count > 0) {
        return intval($referrals_users[0]->referral_count);
    }
    
    // Method 4: Check if MemberPress has referral data
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if ($table_exists) {
        // Check for referral data in MemberPress transactions
        $mp_referrals = $wpdb->get_results($wpdb->prepare("
            SELECT COUNT(DISTINCT t.user_id) as referral_count
            FROM {$txn_table} t
            JOIN {$wpdb->usermeta} um ON t.user_id = um.user_id
            WHERE um.meta_key IN ('mepr_referral_code', 'mepr_referred_by')
            AND DATE(t.created_at) BETWEEN %s AND %s
            AND t.status IN ('confirmed', 'complete')
            AND um.meta_value IS NOT NULL
            AND um.meta_value != ''
        ", $date_from, $date_to));
        
        if (!empty($mp_referrals) && $mp_referrals[0]->referral_count > 0) {
            return intval($mp_referrals[0]->referral_count);
        }
    }
    
    return 0; // fallback if no referrals found
}

// Temporary debug function - remove after testing
function debug_memberpress_data() {
    if (!current_user_can('manage_options')) {
        return "Access denied";
    }
    
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    // Get sample recent transactions
    $recent_transactions = $wpdb->get_results("
        SELECT t.*, u.display_name, u.user_email, p.post_title as program_name
        FROM {$txn_table} t
        JOIN {$wpdb->users} u ON t.user_id = u.ID
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.status IN ('confirmed', 'complete')
        ORDER BY t.created_at DESC
        LIMIT 10
    ");
    
    ob_start();
    echo "<h3>Recent MemberPress Transactions (Last 10)</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User</th><th>Program</th><th>Status</th><th>Created</th><th>Expires</th><th>Days Since Created</th></tr>";
    
    foreach ($recent_transactions as $txn) {
        $created_date = date('M j, Y', strtotime($txn->created_at));
        $expires_date = $txn->expires_at ? date('M j, Y', strtotime($txn->expires_at)) : 'Never';
        $days_since_created = floor((time() - strtotime($txn->created_at)) / (60 * 60 * 24));
        
        echo "<tr>";
        echo "<td>{$txn->display_name}</td>";
        echo "<td>{$txn->program_name}</td>";
        echo "<td>{$txn->status}</td>";
        echo "<td>{$created_date}</td>";
        echo "<td>{$expires_date}</td>";
        echo "<td>{$days_since_created} days</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    return ob_get_clean();
}
add_shortcode('debug_memberpress', 'debug_memberpress_data');

// Register the shortcode
add_shortcode('dashboard_stats', 'dashboard_stats_shortcode');

// Legacy support
add_shortcode('staff_dashboard_stats', 'dashboard_stats_shortcode');
