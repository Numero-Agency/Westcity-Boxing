<?php
// Dashboard Stats Component - Enhanced with Clickable Summary Boxes

function dashboard_stats_shortcode() {
    // Get date range from URL parameters or use defaults
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');

    // Use the proven logic from active-members-test.php for active members counting
    // Only count members from the 7 defined program groups
    $active_members_data = get_active_members_from_defined_groups($date_from, $date_to);
    $total_students = $active_members_data['total_count'];

    $total_sessions = wp_count_posts('session_log')->publish;

    // Get MemberPress groups breakdown using the proven logic
    $memberships_breakdown = get_active_groups_breakdown($date_from, $date_to);
    
    // Get non-renewed members within date range (only from defined groups)
    $non_renewed_members = get_non_renewed_members_from_defined_groups($date_from, $date_to);
    
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
    
    // Get waitlist members using same logic as student table
    $waitlist_members = get_waitlist_member_count_consistent();

    // Get ethnicity and age data using the EXACT SAME active members logic as the total count
    // This ensures all counts match perfectly
    $active_member_ids = get_active_member_ids_consistent_with_total($date_from, $date_to);
    
    // DEBUG: Log the active member IDs count to verify consistency
    wcb_debug_log("Dashboard Stats DEBUG: Total active member IDs count: " . count($active_member_ids));
    
    $ethnicity_data = get_member_ethnicity_breakdown($active_member_ids);
    $ethnicity_breakdown = $ethnicity_data['grouped'];
    $ethnicity_detailed = $ethnicity_data['detailed'];
    
    // DEBUG: Log ethnicity breakdown total
    wcb_debug_log("Dashboard Stats DEBUG: Ethnicity breakdown total: " . array_sum($ethnicity_breakdown));
    
    $age_breakdown = get_member_age_breakdown($active_member_ids);
    
    // DEBUG: Log age breakdown total  
    wcb_debug_log("Dashboard Stats DEBUG: Age breakdown total: " . array_sum($age_breakdown));
    
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
            <h3><?php echo array_sum($ethnicity_breakdown); ?></h3>
            <p><span class="dashicons dashicons-chart-pie"></span> Ethnicity Data</p>
            <small>Click to view breakdown</small>
        </div>
        <div class="stat-card age-ranges clickable-stat" data-popup="age-ranges">
            <h3><?php echo array_sum($age_breakdown); ?></h3>
            <p><span class="dashicons dashicons-chart-bar"></span> Age Data</p>
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
                <div class="non-renewed-cards-container">
                    <?php foreach ($non_renewed_members as $member): ?>
                    <div class="non-renewed-member-card">
                        <!-- Member Header -->
                        <div class="member-card-header">
                            <div class="member-info">
                                <h4 class="member-name"><?php echo esc_html($member['name']); ?></h4>
                                <div class="member-email"><?php echo esc_html($member['email']); ?></div>
                            </div>
                            <div class="status-badge <?php echo esc_attr($member['status_class']); ?>">
                                <?php echo esc_html($member['status_text']); ?>
                            </div>
                        </div>

                        <!-- Member Details -->
                        <div class="member-card-body">
                            <div class="member-details-grid">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <span class="dashicons dashicons-groups"></span>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Program</div>
                                        <div class="detail-value"><?php echo esc_html($member['program']); ?></div>
                                        <?php if (isset($member['membership_type']) && !empty($member['membership_type'])): ?>
                                        <div class="detail-sub"><?php echo esc_html($member['membership_type']); ?> Membership</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Expired</div>
                                        <div class="detail-value"><?php echo esc_html($member['expired_date']); ?></div>
                                        <div class="detail-sub"><?php echo esc_html($member['days_since_expiry']); ?> ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Member Actions -->
                        <div class="member-card-footer">
                            <div class="member-actions">
                                <?php if (isset($member['user_id'])): ?>
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>"
                                   class="action-btn view-profile" target="_blank" title="View Profile">
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <span class="btn-text">Profile</span>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($member['subscription_id'])): ?>
                                <a href="<?php echo admin_url('admin.php?page=memberpress-subscriptions&action=edit&id=' . $member['subscription_id']); ?>"
                                   class="action-btn view-subscription" target="_blank" title="View Subscription">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                    <span class="btn-text">Subscription</span>
                                </a>
                                <?php endif; ?>
                                <a href="mailto:<?php echo esc_attr($member['email']); ?>"
                                   class="action-btn send-email" title="Send Email">
                                    <span class="dashicons dashicons-email-alt"></span>
                                    <span class="btn-text">Email</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
    
    /* Card-based layout for non-renewed members */
    .non-renewed-cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .non-renewed-member-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .non-renewed-member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #FF6B6B;
    }

    .member-card-header {
        background: #f8f9fa;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e5e5;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
    }

    .member-card-body {
        padding: 20px;
    }

    .member-card-footer {
        background: #f8f9fa;
        padding: 16px 20px;
        border-top: 1px solid #e5e5e5;
    }

    /* Member info styling for cards */
    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #212529;
        font-size: 16px;
        margin: 0 0 4px 0;
        line-height: 1.3;
    }

    .member-email {
        font-size: 13px;
        color: #6c757d;
        margin: 0;
    }

    /* Member details grid */
    .member-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .detail-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .detail-icon {
        width: 32px;
        height: 32px;
        background: #f8f9fa;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .detail-icon .dashicons {
        font-size: 16px;
        color: #6c757d;
    }

    .detail-content {
        flex: 1;
    }

    .detail-label {
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .detail-value {
        font-weight: 600;
        color: #212529;
        font-size: 14px;
        line-height: 1.3;
        margin-bottom: 2px;
    }

    .detail-sub {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.3;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.expired {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-badge.overdue {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-badge.recent {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Action buttons for card layout */
    .member-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: flex-start;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #e5e5e5;
        font-size: 13px;
        font-weight: 500;
        flex: 1;
        justify-content: center;
        min-width: 0;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        text-decoration: none;
    }

    .action-btn.view-profile {
        background: #e3f2fd;
        color: #1976d2;
        border-color: #bbdefb;
    }

    .action-btn.view-profile:hover {
        background: #bbdefb;
        color: #1565c0;
        border-color: #90caf9;
    }

    .action-btn.view-subscription {
        background: #f3e5f5;
        color: #7b1fa2;
        border-color: #e1bee7;
    }

    .action-btn.view-subscription:hover {
        background: #e1bee7;
        color: #6a1b9a;
        border-color: #ce93d8;
    }

    .action-btn.send-email {
        background: #e8f5e8;
        color: #388e3c;
        border-color: #c8e6c9;
    }

    .action-btn.send-email:hover {
        background: #c8e6c9;
        color: #2e7d32;
        border-color: #a5d6a7;
    }

    .action-btn .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .btn-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        
        /* Responsive cards for tablet */
        .non-renewed-cards-container {
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 16px;
        }

        .member-details-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .member-actions {
            flex-direction: column;
            gap: 8px;
        }

        .action-btn {
            padding: 10px 16px;
            justify-content: flex-start;
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
        
        /* Mobile responsive cards */
        .non-renewed-cards-container {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .member-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .member-details-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .detail-item {
            gap: 8px;
        }

        .detail-icon {
            width: 28px;
            height: 28px;
        }

        .member-actions {
            flex-direction: column;
            gap: 8px;
        }

        .action-btn {
            padding: 12px 16px;
            font-size: 14px;
        }

        .btn-text {
            display: block;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

// NEW: Function to get active members from the 7 defined program groups using proven logic
function get_active_members_from_defined_groups($date_from = null, $date_to = null) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return ['total_count' => 0, 'group_breakdown' => []];
    }

    // Get all groups using the same query as active-members-test.php
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    // Define the 7 program groups (same as active-members-test.php)
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $total_active_members = [];
    $group_breakdown = [];

    foreach ($defined_groups as $group_name) {
        // Find the group - exact matching
        $group = null;
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            $group_breakdown[$group_name] = 0;
            continue;
        }

        // Use the EXACT same logic as active-members-test.php
        $group_memberships = wcb_get_group_memberships($group->ID);
        $group_member_count = 0;

        if (!empty($group_memberships)) {
            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
            $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

            // Get members who have active transactions for memberships in this group
            // Use EXACT same query as active-members-test.php (line 320-329)
            $group_members = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT u.ID
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                WHERE t.product_id IN ({$placeholders})
                AND t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                AND u.user_login != 'bwgdev'
                ORDER BY u.ID
            ", ...$membership_ids));

            $group_member_ids = array_column($group_members, 'ID');
            $group_member_count = count($group_member_ids);

            // Add to total (avoiding duplicates across groups)
            foreach ($group_member_ids as $member_id) {
                $total_active_members[$member_id] = true;
            }
        }

        $group_breakdown[$group_name] = $group_member_count;
    }

    // STEP 2: Also include Competitive Team members (ID: 1932) to match dashboard-students.php logic
    $competitive_team_id = 1932;
    $competitive_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
    ", $competitive_team_id));

    $competitive_count = count($competitive_members);
    
    // Add Competitive Team members to total count (avoiding duplicates)
    foreach ($competitive_members as $competitive_member) {
        $total_active_members[$competitive_member->ID] = true;
    }

    // Add Competitive Team to breakdown if there are members
    if ($competitive_count > 0) {
        $group_breakdown['Competitive Team'] = $competitive_count;
    }

    return [
        'total_count' => count($total_active_members),
        'group_breakdown' => $group_breakdown
    ];
}

// NEW: Function to get active groups breakdown using proven logic
function get_active_groups_breakdown($date_from = null, $date_to = null) {
    $active_members_data = get_active_members_from_defined_groups($date_from, $date_to);
    return $active_members_data['group_breakdown'];
}

// NEW: Helper function to get active member IDs that matches EXACTLY with the total count
function get_active_member_ids_consistent_with_total($date_from = null, $date_to = null) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    // Get all groups using the same query as active-members-test.php
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    // Define the 7 program groups (same as active-members-test.php)
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $total_active_members = [];

    // STEP 1: Get members from the 7 defined groups (EXACT same logic as get_active_members_from_defined_groups)
    foreach ($defined_groups as $group_name) {
        // Find the group - exact matching
        $group = null;
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            continue;
        }

        // Use the EXACT same logic as get_active_members_from_defined_groups
        $group_memberships = wcb_get_group_memberships($group->ID);

        if (!empty($group_memberships)) {
            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
            $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

            // Get members who have active transactions for memberships in this group
            // Use EXACT same query as get_active_members_from_defined_groups
            $group_members = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT u.ID
                FROM {$wpdb->users} u
                JOIN {$txn_table} t ON u.ID = t.user_id
                WHERE t.product_id IN ({$placeholders})
                AND t.status IN ('confirmed', 'complete')
                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                AND u.user_login != 'bwgdev'
                ORDER BY u.ID
            ", ...$membership_ids));

            $group_member_ids = array_column($group_members, 'ID');

            // Add to total (avoiding duplicates across groups)
            foreach ($group_member_ids as $member_id) {
                $total_active_members[$member_id] = true;
            }
        }
    }

    // STEP 2: Also include Competitive Team members (ID: 1932) to match dashboard-students.php logic
    $competitive_team_id = 1932;
    $competitive_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
    ", $competitive_team_id));

    foreach ($competitive_members as $competitive_member) {
        $total_active_members[$competitive_member->ID] = true;
    }

    return array_keys($total_active_members);
}

// DEPRECATED: Helper function to get just the member IDs from defined groups
function get_active_member_ids_from_defined_groups($date_from = null, $date_to = null) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    // Get all groups using the same query as active-members-test.php
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    // Define the 7 program groups (same as active-members-test.php)
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $total_active_members = [];

    foreach ($defined_groups as $group_name) {
        // Find the group - exact matching
        $group = null;
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            continue;
        }

        // Use the EXACT same logic as active-members-test.php
        $group_memberships = wcb_get_group_memberships($group->ID);

        if (!empty($group_memberships)) {
            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
            $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

            // Get members who have active transactions for memberships in this group
            if ($date_from && $date_to) {
                // Filter by date range if provided
                $group_members = $wpdb->get_results($wpdb->prepare("
                    SELECT DISTINCT u.ID
                    FROM {$wpdb->users} u
                    JOIN {$txn_table} t ON u.ID = t.user_id
                    WHERE t.product_id IN ({$placeholders})
                    AND t.status IN ('confirmed', 'complete')
                    AND DATE(t.created_at) <= %s
                    AND (
                        t.expires_at IS NULL
                        OR t.expires_at = '0000-00-00 00:00:00'
                        OR DATE(t.expires_at) >= %s
                    )
                    AND u.user_login != 'bwgdev'
                    ORDER BY u.ID
                ", array_merge($membership_ids, [$date_to, $date_from])));
            } else {
                // No date filter - get currently active members
                $group_members = $wpdb->get_results($wpdb->prepare("
                    SELECT DISTINCT u.ID
                    FROM {$wpdb->users} u
                    JOIN {$txn_table} t ON u.ID = t.user_id
                    WHERE t.product_id IN ({$placeholders})
                    AND t.status IN ('confirmed', 'complete')
                    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                    AND u.user_login != 'bwgdev'
                    ORDER BY u.ID
                ", ...$membership_ids));
            }

            $group_member_ids = array_column($group_members, 'ID');

            // Add to total (avoiding duplicates across groups)
            foreach ($group_member_ids as $member_id) {
                $total_active_members[$member_id] = true;
            }
        }
    }

    return array_keys($total_active_members);
}

// NEW: Function to get non-renewed members from defined groups only
function get_non_renewed_members_from_defined_groups($date_from, $date_to) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    // Get all groups and find the defined groups
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    // Define the 7 program groups
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    // Get all membership IDs from the defined groups
    $all_group_membership_ids = [];
    foreach ($defined_groups as $group_name) {
        // Find the group
        $group = null;
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }

        if ($group) {
            $group_memberships = wcb_get_group_memberships($group->ID);
            if (!empty($group_memberships)) {
                $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                $all_group_membership_ids = array_merge($all_group_membership_ids, $membership_ids);
            }
        }
    }
    
    // Also include Competitive Team (ID: 1932) in non-renewed checking
    $competitive_team_id = 1932;
    $all_group_membership_ids[] = $competitive_team_id;

    if (empty($all_group_membership_ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($all_group_membership_ids), '%d'));

    // Get all transactions that expired within the date range for defined group memberships only
    $expired_transactions = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, u.display_name, u.user_email, p.post_title as program_name,
               s.id as subscription_id, s.status as subscription_status
        FROM {$txn_table} t
        JOIN {$wpdb->users} u ON t.user_id = u.ID
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        LEFT JOIN {$wpdb->prefix}mepr_subscriptions s ON t.subscription_id = s.id
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND t.expires_at IS NOT NULL
        AND t.expires_at != '0000-00-00 00:00:00'
        AND DATE(t.expires_at) BETWEEN %s AND %s
        ORDER BY t.expires_at DESC
    ", array_merge($all_group_membership_ids, [$date_from, $date_to])));

    $non_renewed_members = [];

    foreach ($expired_transactions as $expired_txn) {
        $user_id = $expired_txn->user_id;

        // IMPROVED: Check if this user currently has ANY active membership
        // This handles weekly subscriptions and Stripe renewals properly
        $has_active_membership = wcb_user_has_active_membership($user_id);
        
        // ADDITIONAL: Check for renewal transactions after expiry (for edge cases)
        $renewed_membership = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$txn_table} t
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND t.created_at > %s
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ", $user_id, $expired_txn->expires_at));

        // DEBUG: Log the checks for troubleshooting
        wcb_debug_log("Non-Renewed Check - User ID: {$user_id}, Name: {$expired_txn->display_name}, Program: {$expired_txn->program_name}");
        wcb_debug_log("  - Has Active Membership: " . ($has_active_membership ? 'YES' : 'NO'));
        wcb_debug_log("  - Renewal Transactions After Expiry: {$renewed_membership}");
        wcb_debug_log("  - Expired Date: {$expired_txn->expires_at}");

        // Only consider non-renewed if BOTH conditions are true:
        // 1. No currently active membership AND 
        // 2. No renewal transactions after expiry
        if (!$has_active_membership && $renewed_membership == 0) {
            $expired_date = date('d/m/Y', strtotime($expired_txn->expires_at));
            $days_since_expiry = floor((time() - strtotime($expired_txn->expires_at)) / (60 * 60 * 24));

            // Determine status based on days since expiry
            $status_class = 'expired';
            $status_text = 'Expired';
            if ($days_since_expiry > 30) {
                $status_class = 'overdue';
                $status_text = 'Overdue';
            } elseif ($days_since_expiry <= 7) {
                $status_class = 'recent';
                $status_text = 'Recent';
            }

            // Get membership type from product title
            $membership_type = '';
            if (stripos($expired_txn->program_name, 'monthly') !== false) {
                $membership_type = 'Monthly';
            } elseif (stripos($expired_txn->program_name, 'weekly') !== false) {
                $membership_type = 'Weekly';
            } elseif (stripos($expired_txn->program_name, 'term') !== false) {
                $membership_type = 'Full Term';
            }

            // Avoid duplicates (same user with multiple expired memberships)
            $user_key = $user_id . '_' . $expired_txn->product_id;
            if (!isset($non_renewed_members[$user_key])) {
                $non_renewed_members[$user_key] = [
                    'user_id' => $user_id,
                    'name' => $expired_txn->display_name,
                    'email' => $expired_txn->user_email,
                    'program' => $expired_txn->program_name,
                    'membership_type' => $membership_type,
                    'expired_date' => $expired_date,
                    'days_since_expiry' => $days_since_expiry . ' days',
                    'status_class' => $status_class,
                    'status_text' => $status_text,
                    'subscription_id' => $expired_txn->subscription_id,
                    'subscription_status' => $expired_txn->subscription_status
                ];
            }
        }
    }

    return array_values($non_renewed_members);
}

// NEW: Function to check if a user has any active membership (handles weekly subscriptions & Stripe)
function wcb_user_has_active_membership($user_id) {
    global $wpdb;
    
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';
    
    // Method 1: Check for active transactions (immediate check)
    $active_transactions = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$txn_table} t
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    if ($active_transactions > 0) {
        wcb_debug_log("  - ACTIVE via Method 1: Active Transactions ({$active_transactions} found)");
        return true;
    }
    
    // Method 2: Check for active subscriptions (especially important for weekly/Stripe)
    $active_subscriptions = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$subscriptions_table} s
        WHERE s.user_id = %d
        AND s.status IN ('active', 'trialing')
        AND (s.expires_at IS NULL OR s.expires_at > NOW() OR s.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    if ($active_subscriptions > 0) {
        wcb_debug_log("  - ACTIVE via Method 2: Active Subscriptions ({$active_subscriptions} found)");
        return true;
    }
    
    // Method 3: Enhanced check for weekly subscriptions
    // Weekly subscriptions might have gaps between transactions or overlapping periods
    $weekly_check = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, p.post_title as product_name
        FROM {$txn_table} t
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND p.post_title LIKE '%weekly%'
        ORDER BY t.created_at DESC
        LIMIT 5
    ", $user_id));
    
    if (!empty($weekly_check)) {
        foreach ($weekly_check as $weekly_txn) {
            // For weekly subscriptions, check if:
            // 1. Transaction was created within last 14 days (covers 2 weeks)
            // 2. OR transaction expires in the future
            // 3. OR transaction was created recently but might be processing
            $created_days_ago = floor((time() - strtotime($weekly_txn->created_at)) / (60 * 60 * 24));
            $expires_in_future = ($weekly_txn->expires_at === null || 
                                 $weekly_txn->expires_at === '0000-00-00 00:00:00' || 
                                 strtotime($weekly_txn->expires_at) > time());
            
            if ($created_days_ago <= 14 || $expires_in_future) {
                wcb_debug_log("  - ACTIVE via Method 3: Weekly Subscription (created {$created_days_ago} days ago, expires in future: " . ($expires_in_future ? 'YES' : 'NO') . ")");
                return true;
            }
        }
    }
    
    // Method 4: Check for Stripe subscription transactions specifically
    // Stripe transactions might have different patterns
    $stripe_transactions = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$txn_table} t
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND t.gateway LIKE '%stripe%'
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    if ($stripe_transactions > 0) {
        wcb_debug_log("  - ACTIVE via Method 4: Stripe Transactions ({$stripe_transactions} found)");
        return true;
    }
    
    // Method 5: Check if user has MemberPress capabilities (using WP roles/capabilities)
    $user = get_user_by('ID', $user_id);
    if ($user && function_exists('mepr_user_has_access')) {
        // Get all membership products and check access
        $membership_products = get_posts([
            'post_type' => 'memberpressproduct',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);
        
        foreach ($membership_products as $product) {
            if (mepr_user_has_access($user_id, $product->ID)) {
                wcb_debug_log("  - ACTIVE via Method 5: MemberPress Access (Product ID: {$product->ID})");
                return true;
            }
        }
    }
    
    wcb_debug_log("  - NOT ACTIVE: No active membership found via any method");
    return false;
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

    // Get WCB Mentoring membership ID
    $wcb_mentoring_id = 1738;

    // Get all users with active MemberPress memberships excluding WCB Mentoring and admin users
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND t.product_id != %d
        AND u.user_login != 'bwgdev'
        ORDER BY u.ID
    ", $wcb_mentoring_id));

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

// Helper function to get waitlist member count (old method)
function get_waitlist_member_count() {
    return WCB_MemberPress_Helper::get_waitlist_count();
}

// Helper function to get waitlist member count using same logic as student table
function get_waitlist_member_count_consistent() {
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

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

    return (int) $waitlist_count;
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
    
    // Get ALL ethnicity data for active members (including empty/missing)
    $all_ethnicities = $wpdb->get_results("
        SELECT COALESCE(um.meta_value, '') as meta_value, u.ID as user_id
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_ethnicity'
        WHERE u.ID IN ($member_ids)
    ");
    
    // Define Polynesian ethnicity patterns
    $polynesian_patterns = [
        'samoan', 'samoa', 'tongan', 'tonga', 'fijian', 'fiji', 'cook island', 'cook islands',
        'tahitian', 'tahiti', 'hawaiian', 'hawaii', 'niuean', 'niue', 'tokelauan', 'tokelau',
        'tuvaluan', 'tuavaluan', 'tuvalu', 'kiribati', 'marshal', 'solomon', 'vanuatu', 'polynesian', 'pacific'
    ];
    
    $grouped_breakdown = [
        'Māori' => 0,
        'Pacific Island' => 0,
        'NZ European' => 0,
        'Asian' => 0,
        'Other' => 0,
        'Not Specified' => 0
    ];
    
    // Detailed breakdowns for clickable groups
    $detailed_breakdowns = [
        'Pacific Island' => [],
        'Asian' => [],
        'Other' => []
    ];
    
    foreach ($all_ethnicities as $ethnicity_data) {
        $ethnicity_value = trim(strtolower($ethnicity_data->meta_value));
        
        // Handle empty/missing ethnicity data
        if (empty($ethnicity_value) || $ethnicity_value == 'not specified') {
            $grouped_breakdown['Not Specified']++;
            continue;
        }
        
        // First check the full string for patterns before splitting
        $found_categories = [];
        
        // Check for Māori in full string first (highest priority)
        if (strpos($ethnicity_value, 'maori') !== false || strpos($ethnicity_value, 'māori') !== false) {
            $found_categories['Māori'] = true;
        }
        
        // Check for Polynesian patterns in full string (high priority)
        foreach ($polynesian_patterns as $pattern) {
            if (strpos($ethnicity_value, $pattern) !== false) {
                $found_categories['Pacific Island'] = true;
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
                
                // Check for Māori (highest priority)
                if (strpos($single_ethnicity, 'maori') !== false || strpos($single_ethnicity, 'māori') !== false) {
                    $found_categories['Māori'] = true;
                }
                // Check for Polynesian ethnicities (second highest priority)
                else {
                    $is_polynesian = false;
                    foreach ($polynesian_patterns as $pattern) {
                        if (strpos($single_ethnicity, $pattern) !== false) {
                            $found_categories['Pacific Island'] = true;
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
        
        // Priority assignment: Māori > Pacific Island > Asian > NZ European > Other
        // Pacific Island should have higher priority than Asian to ensure proper categorization
        $assigned_category = null;

        if (isset($found_categories['Māori'])) {
            $assigned_category = 'Māori';
        } elseif (isset($found_categories['Pacific Island'])) {
            $assigned_category = 'Pacific Island';
            // Store detailed breakdown for Pacific Island
            $detailed_breakdowns['Pacific Island'][$ethnicity_value] = isset($detailed_breakdowns['Pacific Island'][$ethnicity_value]) ? $detailed_breakdowns['Pacific Island'][$ethnicity_value] + 1 : 1;
        } elseif (isset($found_categories['Asian'])) {
            $assigned_category = 'Asian';
            // Store detailed breakdown for Asian
            $detailed_breakdowns['Asian'][$ethnicity_value] = isset($detailed_breakdowns['Asian'][$ethnicity_value]) ? $detailed_breakdowns['Asian'][$ethnicity_value] + 1 : 1;
        } elseif (isset($found_categories['New Zealand']) || isset($found_categories['European'])) {
            $assigned_category = 'NZ European';
        } else {
            // Only assign to Other if no other category was found
            $assigned_category = 'Other';
            // Store detailed breakdown for Other
            $detailed_breakdowns['Other'][$ethnicity_value] = isset($detailed_breakdowns['Other'][$ethnicity_value]) ? $detailed_breakdowns['Other'][$ethnicity_value] + 1 : 1;
        }
        
        // Assign to the determined category
        if ($assigned_category) {
            $grouped_breakdown[$assigned_category]++;
        }
    }
    
    // Remove empty categories (but keep "Not Specified" if it has members)
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
    
    // Get ALL age data for active members (including empty/missing)
    $age_data = $wpdb->get_results("
        SELECT COALESCE(um.meta_value, '') as meta_value, u.ID as user_id
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_age'
        WHERE u.ID IN ($member_ids)
        ORDER BY u.ID
    ");
    
    $age_groups = [
        '9-11' => 0,
        '12-14' => 0,
        '15-18' => 0,
        '18-24' => 0,
        '24+' => 0,
        'Not Specified' => 0
    ];
    
    $processed_users = [];
    
    foreach ($age_data as $data) {
        // Skip if we already processed this user (avoid double counting)
        if (in_array($data->user_id, $processed_users)) {
            continue;
        }
        
        $age = calculate_age_from_data($data->meta_value);
        
        // Process ALL members (including those without valid age data)
        $processed_users[] = $data->user_id;
        
        if ($age !== null) {
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
        } else {
            // No valid age data - count as Not Specified
            $age_groups['Not Specified']++;
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

// Helper function to get active groups breakdown (updated to use Groups instead of individual memberships)
function get_active_memberships_breakdown($date_from = null, $date_to = null) {
    global $wpdb;

    // Check if MemberPress transactions table exists
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    // Get all published Groups first, fallback to individual memberships if no groups exist
    $groups = get_posts([
        'post_type' => 'memberpressgroup',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);

    $groups_breakdown = [];

    if (!empty($groups)) {
        // Use Groups approach
        foreach ($groups as $group) {
            // Get all membership IDs in this group
            $membership_ids = $wpdb->get_col($wpdb->prepare("
                SELECT p.ID
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE pm.meta_key = '_mepr_group_id'
                AND pm.meta_value = %d
                AND p.post_type = 'memberpressproduct'
                AND p.post_status = 'publish'
            ", $group->ID));

            if (empty($membership_ids)) {
                continue;
            }

            // Count unique users with active transactions for any membership in this group
            $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

            if ($date_from && $date_to) {
                // Get members who were active during the date range for this group
                $query = "
                    SELECT COUNT(DISTINCT t.user_id)
                    FROM {$txn_table} t
                    WHERE t.product_id IN ({$placeholders})
                    AND t.status IN ('confirmed', 'complete')
                    AND DATE(t.created_at) <= %s
                    AND (
                        t.expires_at IS NULL
                        OR t.expires_at = '0000-00-00 00:00:00'
                        OR DATE(t.expires_at) >= %s
                    )
                ";
                $member_count = (int) $wpdb->get_var($wpdb->prepare($query, ...array_merge($membership_ids, [$date_to, $date_from])));
            } else {
                // Use current active logic
                $query = "
                    SELECT COUNT(DISTINCT t.user_id)
                    FROM {$txn_table} t
                    WHERE t.product_id IN ({$placeholders})
                    AND t.status IN ('confirmed', 'complete')
                    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                ";
                $member_count = (int) $wpdb->get_var($wpdb->prepare($query, ...$membership_ids));
            }

            // Only include groups with active members
            if ($member_count > 0) {
                $groups_breakdown[$group->post_title] = $member_count;
            }
        }

        return $groups_breakdown;
    } else {
        // Fallback: use individual memberships if no groups exist
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
