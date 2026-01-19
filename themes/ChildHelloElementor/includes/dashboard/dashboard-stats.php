<?php
// Dashboard Stats Component - Enhanced with Clickable Summary Boxes

function dashboard_stats_shortcode() {
    // Get date range from URL parameters or use defaults
    // Use WordPress timezone for accurate local dates
    $timezone = wp_timezone();
    $now = new DateTime('now', $timezone);
    $thirty_days_ago = new DateTime('-30 days', $timezone);
    
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : $thirty_days_ago->format('Y-m-d');
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : $now->format('Y-m-d');

    // Use the proven logic from active-members-test.php for active members counting
    // Get total students breakdown FIRST (active + non-renewed) with payment method breakdown
    // This calculates corrected counts: Active = currently active only (excludes non-renewed)
    $total_students_breakdown = get_total_students_breakdown($date_from, $date_to);
    
    // Use the corrected active count (excludes non-renewed members)
    $total_students = $total_students_breakdown['active_count'];
    
    // Get non-renewed members within date range (only from defined groups)
    $non_renewed_members = get_non_renewed_members_from_defined_groups($date_from, $date_to);

    // Get sessions count filtered by date range
    $sessions_query = new WP_Query([
        'post_type' => 'session_log',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'date_query' => [
            [
                'after' => $date_from,
                'before' => $date_to,
                'inclusive' => true,
            ]
        ]
    ]);
    $total_sessions = $sessions_query->found_posts;
    wp_reset_postdata();

    // Get MemberPress groups breakdown using the proven logic
    $memberships_breakdown = get_active_groups_breakdown($date_from, $date_to);
    
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
    $waitlist_members_detailed = get_waitlist_members_detailed();

    // Get "Other Active Memberships" (Competitive Team & WCB Mentoring)
    $other_memberships = get_other_active_memberships($date_from, $date_to);

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

    // Get schools data
    $schools_data = get_schools_data();
    $total_schools = count($schools_data);

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
                    <small><strong>📊 Date Filter:</strong> Showing members who joined on/before <strong><?php echo date('M j, Y', strtotime($date_to)); ?></strong> AND whose subscription was active during this period (not expired before <strong><?php echo date('M j, Y', strtotime($date_from)); ?></strong>).<br>
                    <em>Note: Join dates use MemberPress Registration Date if available, otherwise the first transaction date. Expiry dates from transaction records determine when members stopped being active.</em></small>
                </div>
            </form>
        </div>
        
        <div class="dashboard-stats">
            <!-- Row 1: Core Stats -->
            <div class="stat-card total-students clickable-stat" data-popup="total-students">
                <h3><?php echo $total_students_breakdown['total']; ?></h3>
                <p><span class="dashicons dashicons-groups"></span> Total Students</p>
                <small>Click to view breakdown</small>
            </div>
            <div class="stat-card students clickable-stat" data-popup="active-members">
                <h3><?php echo $total_students_breakdown['active_count']; ?></h3>
                <p><span class="dashicons dashicons-admin-users"></span> Active Members</p>
                <small>Click to view breakdown</small>
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
            <div class="stat-card paused clickable-stat" data-popup="paused">
                <h3><?php echo $total_students_breakdown['paused_count']; ?></h3>
                <p><span class="dashicons dashicons-controls-pause"></span> Paused Members</p>
                <small>Click to view members</small>
            </div>
        
        <!-- Row 2: Demographics -->
        <div class="stat-card ethnicity clickable-stat" data-popup="ethnicity">
            <h3><?php echo count($ethnicity_breakdown); ?></h3>
            <p><span class="dashicons dashicons-chart-pie"></span> Ethnicity Groups</p>
            <small>Click to view breakdown</small>
        </div>
        <div class="stat-card age-ranges clickable-stat" data-popup="age-ranges">
            <h3><?php echo count($age_breakdown); ?></h3>
            <p><span class="dashicons dashicons-chart-bar"></span> Age Ranges</p>
            <small>Click to view breakdown</small>
        </div>
        
        <!-- Row 3: Community and Competition Stats -->
        <div class="stat-card waitlist clickable-stat" data-popup="waitlist">
            <h3><?php echo $waitlist_members; ?></h3>
            <p><span class="dashicons dashicons-clock"></span> Members on Waitlist</p>
            <small>Click to view members</small>
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
        <div class="stat-card schools clickable-stat" data-popup="schools">
            <h3><?php echo $total_schools; ?></h3>
            <p><span class="dashicons dashicons-building"></span> Total Schools</p>
            <small>Click to view breakdown</small>
        </div>
    </div>
    </div>
    
    <!-- DEBUG SECTION: Active Members & Expired Members Tables -->
    <?php 
    $debug_active_members = wcb_get_debug_active_members($date_from, $date_to);
    $debug_expired_members = wcb_get_debug_expired_members($date_from, $date_to);
    ?>
    <div class="dashboard-debug-section">
        <div class="debug-toggle-header" onclick="toggleDebugSection()">
            <h3>
                <span class="dashicons dashicons-admin-tools"></span> 
                Debug: Member Data Visibility
                <span class="debug-toggle-icon">+</span>
            </h3>
            <small>Click to expand/collapse - Shows detailed member data for verification</small>
        </div>
        
        <div class="debug-content" id="debug-content" style="display: none;">
            <!-- Active Members Table -->
            <?php 
            // Pre-calculate counts for filter buttons
            $active_count = count(array_filter($debug_active_members, function($m) { return $m['member_status'] === 'active'; }));
            $paused_count = count(array_filter($debug_active_members, function($m) { return $m['member_status'] === 'paused'; }));
            $cancelled_count = count(array_filter($debug_active_members, function($m) { return $m['member_status'] === 'cancelled'; }));
            $expired_count = count(array_filter($debug_active_members, function($m) { return $m['member_status'] === 'expired'; }));
            ?>
            <div class="debug-table-section">
                <h4>
                    <span class="dashicons dashicons-yes-alt"></span> 
                    Active Members During Period (<span id="active-members-visible-count"><?php echo count($debug_active_members); ?></span>)
                </h4>
                <p class="debug-description">
                    Members who joined on/before <strong><?php echo date('M j, Y', strtotime($date_to)); ?></strong> 
                    AND whose subscription is still active or expired on/after <strong><?php echo date('M j, Y', strtotime($date_from)); ?></strong>
                </p>
                
                <?php if (!empty($debug_active_members)): ?>
                <!-- Filter Buttons for Active Members -->
                <div class="active-filter-buttons">
                    <button type="button" class="filter-btn active" data-filter="all" onclick="filterActiveMembers('all')">
                        All <span class="filter-count">(<?php echo count($debug_active_members); ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-active-status" data-filter="active" onclick="filterActiveMembers('active')">
                        Active <span class="filter-count">(<?php echo $active_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-paused-status" data-filter="paused" onclick="filterActiveMembers('paused')">
                        Paused <span class="filter-count">(<?php echo $paused_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-cancelled-status" data-filter="cancelled" onclick="filterActiveMembers('cancelled')">
                        Cancelled <span class="filter-count">(<?php echo $cancelled_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-expired-status" data-filter="expired" onclick="filterActiveMembers('expired')">
                        Expired <span class="filter-count">(<?php echo $expired_count; ?>)</span>
                    </button>
                </div>
                
                <div class="debug-table-wrapper">
                    <table class="debug-table" id="active-members-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program/Group</th>
                                <th>Membership</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th>First Transaction</th>
                                <th>Expires</th>
                                <th>Gateway</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_active_members as $member): ?>
                            <?php 
                            // Determine row class based on member status
                            $row_class = '';
                            if ($member['member_status'] === 'paused') {
                                $row_class = 'member-paused-row';
                            } elseif ($member['member_status'] === 'cancelled') {
                                $row_class = 'member-cancelled-row';
                            } elseif ($member['member_status'] === 'expired' || $member['is_expired']) {
                                $row_class = 'member-expired-row';
                            }
                            ?>
                            <tr class="<?php echo $row_class; ?>" data-status="<?php echo esc_attr($member['member_status']); ?>">
                                <td>
                                    <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>" target="_blank">
                                        <?php echo esc_html($member['name']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($member['email']); ?></td>
                                <td><?php echo esc_html($member['group']); ?></td>
                                <td><?php echo esc_html($member['membership']); ?></td>
                                <td>
                                    <?php if ($member['member_status'] === 'paused'): ?>
                                        <span class="status-badge-small status-paused"><?php echo esc_html($member['status_label']); ?></span>
                                    <?php elseif ($member['member_status'] === 'cancelled'): ?>
                                        <span class="status-badge-small status-cancelled"><?php echo esc_html($member['status_label']); ?></span>
                                    <?php elseif ($member['member_status'] === 'expired'): ?>
                                        <span class="status-badge-small status-expired"><?php echo esc_html($member['status_label']); ?></span>
                                    <?php else: ?>
                                        <span class="status-badge-small status-active"><?php echo esc_html($member['status_label']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($member['registration_date']): ?>
                                        <?php echo esc_html($member['registration_date']); ?>
                                    <?php else: ?>
                                        <span class="no-data-badge">Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($member['first_txn_date'] ?: 'N/A'); ?></td>
                                <td>
                                    <?php if ($member['expires_at'] === 'Never'): ?>
                                        <span class="never-expires-badge">Never</span>
                                    <?php elseif ($member['is_expired']): ?>
                                        <span class="expired-date-badge"><?php echo esc_html($member['expires_at']); ?></span>
                                    <?php else: ?>
                                        <?php echo esc_html($member['expires_at']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="gateway-badge gateway-<?php echo strtolower($member['gateway']); ?>">
                                        <?php echo esc_html($member['gateway']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Stats for Active Members -->
                <div class="debug-summary">
                    <div class="summary-grid">
                        <div class="summary-item success">
                            <span class="summary-number"><?php echo $active_count; ?></span>
                            <span class="summary-label">Active</span>
                        </div>
                        <div class="summary-item paused">
                            <span class="summary-number"><?php echo $paused_count; ?></span>
                            <span class="summary-label">Paused</span>
                        </div>
                        <div class="summary-item cancelled">
                            <span class="summary-number"><?php echo $cancelled_count; ?></span>
                            <span class="summary-label">Cancelled</span>
                        </div>
                        <div class="summary-item warning">
                            <span class="summary-number"><?php echo $expired_count; ?></span>
                            <span class="summary-label">Expired</span>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="no-data">No active members found for this period.</div>
                <?php endif; ?>
            </div>
            
            <!-- Expired Members Table -->
            <?php 
            // Pre-calculate counts for the filter buttons
            $expired_renewed_count = count(array_filter($debug_expired_members, function($m) { return $m['has_renewed'] === 'Yes'; }));
            $expired_not_renewed_count = count(array_filter($debug_expired_members, function($m) { return $m['has_renewed'] === 'No' && !$m['has_other_active']; }));
            $expired_other_active_count = count(array_filter($debug_expired_members, function($m) { return $m['has_renewed'] === 'No' && $m['has_other_active']; }));
            $expired_paused_count = count(array_filter($debug_expired_members, function($m) { return isset($m['is_paused']) && $m['is_paused']; }));
            $expired_cancelled_count = count(array_filter($debug_expired_members, function($m) { return isset($m['is_cancelled']) && $m['is_cancelled']; }));
            ?>
            <div class="debug-table-section">
                <h4>
                    <span class="dashicons dashicons-warning"></span> 
                    Members Expired During Period (<span id="expired-visible-count"><?php echo count($debug_expired_members); ?></span>)
                </h4>
                <p class="debug-description">
                    Members whose subscription expired between <strong><?php echo date('M j, Y', strtotime($date_from)); ?></strong> 
                    and <strong><?php echo date('M j, Y', strtotime($date_to)); ?></strong>. 
                    <br><strong>Note:</strong> "Renewed?" only checks defined program groups. "Sub Status" shows if subscription is Paused/Cancelled.
                </p>
                
                <?php if (!empty($debug_expired_members)): ?>
                <!-- Filter Buttons -->
                <div class="expired-filter-buttons">
                    <button type="button" class="filter-btn active" data-filter="all" onclick="filterExpiredMembers('all')">
                        All <span class="filter-count">(<?php echo count($debug_expired_members); ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-not-renewed" data-filter="not-renewed" onclick="filterExpiredMembers('not-renewed')">
                        Not Renewed <span class="filter-count">(<?php echo count($debug_expired_members) - $expired_renewed_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-renewed" data-filter="renewed" onclick="filterExpiredMembers('renewed')">
                        Renewed <span class="filter-count">(<?php echo $expired_renewed_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-paused-status" data-filter="paused" onclick="filterExpiredMembers('paused')">
                        Paused <span class="filter-count">(<?php echo $expired_paused_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-cancelled-status" data-filter="cancelled" onclick="filterExpiredMembers('cancelled')">
                        Cancelled <span class="filter-count">(<?php echo $expired_cancelled_count; ?>)</span>
                    </button>
                    <button type="button" class="filter-btn filter-truly-churned" data-filter="truly-churned" onclick="filterExpiredMembers('truly-churned')">
                        Truly Churned <span class="filter-count">(<?php echo $expired_not_renewed_count; ?>)</span>
                    </button>
                </div>
                
                <div class="debug-table-wrapper">
                    <table class="debug-table" id="expired-members-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Program/Group</th>
                                <th>Membership</th>
                                <th>Sub Status</th>
                                <th>Txn Expired On</th>
                                <th>Current Expires</th>
                                <th>Gateway</th>
                                <th>Renewed?</th>
                                <th>Status Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_expired_members as $member): ?>
                            <?php 
                            // Determine row class based on subscription status first, then renewal status
                            $row_class = '';
                            if (isset($member['is_paused']) && $member['is_paused']) {
                                $row_class = 'member-paused-row';
                            } elseif (isset($member['is_cancelled']) && $member['is_cancelled']) {
                                $row_class = 'member-cancelled-row';
                            } elseif ($member['has_renewed'] === 'Yes') {
                                $row_class = 'renewed-row';
                            } elseif ($member['has_other_active']) {
                                $row_class = 'other-active-row';
                            } else {
                                $row_class = 'not-renewed-row truly-churned-row';
                            }
                            ?>
                            <tr class="<?php echo $row_class; ?>" 
                                data-renewed="<?php echo $member['has_renewed'] === 'Yes' ? 'yes' : 'no'; ?>"
                                data-other-active="<?php echo $member['has_other_active'] ? 'yes' : 'no'; ?>"
                                data-truly-churned="<?php echo ($member['has_renewed'] === 'No' && !$member['has_other_active']) ? 'yes' : 'no'; ?>"
                                data-paused="<?php echo (isset($member['is_paused']) && $member['is_paused']) ? 'yes' : 'no'; ?>"
                                data-cancelled="<?php echo (isset($member['is_cancelled']) && $member['is_cancelled']) ? 'yes' : 'no'; ?>">
                                <td>
                                    <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>" target="_blank">
                                        <?php echo esc_html($member['name']); ?>
                                    </a>
                                    <div class="member-email-small"><?php echo esc_html($member['email']); ?></div>
                                </td>
                                <td><?php echo esc_html($member['group']); ?></td>
                                <td class="membership-cell"><?php echo esc_html($member['membership']); ?></td>
                                <td>
                                    <?php if (isset($member['is_paused']) && $member['is_paused']): ?>
                                        <span class="status-badge-small status-paused"><?php echo esc_html($member['subscription_status_label']); ?></span>
                                    <?php elseif (isset($member['is_cancelled']) && $member['is_cancelled']): ?>
                                        <span class="status-badge-small status-cancelled"><?php echo esc_html($member['subscription_status_label']); ?></span>
                                    <?php elseif (isset($member['subscription_status']) && $member['subscription_status'] === 'active'): ?>
                                        <span class="status-badge-small status-active"><?php echo esc_html($member['subscription_status_label']); ?></span>
                                    <?php else: ?>
                                        <span class="status-badge-small status-none"><?php echo isset($member['subscription_status_label']) ? esc_html($member['subscription_status_label']) : 'N/A'; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="expired-date"><?php echo esc_html($member['expired_txn_date']); ?></td>
                                <td>
                                    <?php if ($member['has_renewed'] === 'Yes'): ?>
                                        <span class="current-expires-renewed"><?php echo esc_html($member['current_expires']); ?></span>
                                    <?php else: ?>
                                        <span class="current-expires-none">Expired</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="gateway-badge gateway-<?php echo strtolower($member['gateway']); ?>">
                                        <?php echo esc_html($member['gateway']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($member['has_renewed'] === 'Yes'): ?>
                                        <span class="renewed-badge">Yes</span>
                                    <?php else: ?>
                                        <span class="not-renewed-badge">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="status-detail-cell">
                                    <?php if ($member['overall_status'] === 'renewed_program'): ?>
                                        <span class="status-badge status-renewed">Renewed in Program</span>
                                    <?php elseif ($member['overall_status'] === 'active_other'): ?>
                                        <span class="status-badge status-other-active">Has Other Active</span>
                                        <div class="status-detail-text"><?php echo esc_html($member['status_detail']); ?></div>
                                    <?php else: ?>
                                        <span class="status-badge status-churned">Truly Churned</span>
                                        <div class="status-detail-text">No active membership found</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Stats -->
                <div class="debug-summary">
                    <?php 
                    $renewed_count = count(array_filter($debug_expired_members, function($m) { return $m['has_renewed'] === 'Yes'; }));
                    $other_active_count = count(array_filter($debug_expired_members, function($m) { return $m['has_renewed'] === 'No' && $m['has_other_active']; }));
                    $truly_churned_count = count(array_filter($debug_expired_members, function($m) { return $m['has_renewed'] === 'No' && !$m['has_other_active']; }));
                    $manual_count = count(array_filter($debug_expired_members, function($m) { return $m['gateway'] === 'Manual'; }));
                    $stripe_count = count(array_filter($debug_expired_members, function($m) { return $m['gateway'] === 'Stripe'; }));
                    ?>
                    <div class="summary-grid">
                        <div class="summary-item success">
                            <span class="summary-number"><?php echo $renewed_count; ?></span>
                            <span class="summary-label">Renewed (Program)</span>
                        </div>
                        <div class="summary-item info">
                            <span class="summary-number"><?php echo $other_active_count; ?></span>
                            <span class="summary-label">Other Active</span>
                        </div>
                        <div class="summary-item paused">
                            <span class="summary-number"><?php echo $expired_paused_count; ?></span>
                            <span class="summary-label">Paused</span>
                        </div>
                        <div class="summary-item cancelled">
                            <span class="summary-number"><?php echo $expired_cancelled_count; ?></span>
                            <span class="summary-label">Cancelled</span>
                        </div>
                        <div class="summary-item warning">
                            <span class="summary-number"><?php echo $truly_churned_count; ?></span>
                            <span class="summary-label">Truly Churned</span>
                        </div>
                    </div>
                    <div class="summary-explanation">
                        <p><strong>Legend:</strong></p>
                        <ul>
                            <li><strong>Renewed (Program):</strong> Member renewed within the same defined program groups</li>
                            <li><strong>Other Active:</strong> Member has other active memberships but NOT renewed in original program</li>
                            <li><strong>Paused:</strong> Member has a suspended/paused subscription (soft yellow highlight)</li>
                            <li><strong>Cancelled:</strong> Member has a cancelled subscription (gray highlight)</li>
                            <li><strong>Truly Churned:</strong> Member has NO active membership anywhere - completely left</li>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <div class="no-data">No expired members found during this period.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    function toggleDebugSection() {
        var content = document.getElementById('debug-content');
        var icon = document.querySelector('.debug-toggle-icon');
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '−';
        } else {
            content.style.display = 'none';
            icon.textContent = '+';
        }
    }
    
    function filterExpiredMembers(filter) {
        var table = document.getElementById('expired-members-table');
        var rows = table.querySelectorAll('tbody tr');
        var visibleCount = 0;
        
        // Update active button state
        var buttons = document.querySelectorAll('.expired-filter-buttons .filter-btn');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            }
        });
        
        // Filter rows
        rows.forEach(function(row) {
            var renewed = row.getAttribute('data-renewed');
            var otherActive = row.getAttribute('data-other-active');
            var trulyChurned = row.getAttribute('data-truly-churned');
            var paused = row.getAttribute('data-paused');
            var cancelled = row.getAttribute('data-cancelled');
            var show = false;
            
            if (filter === 'all') {
                show = true;
            } else if (filter === 'not-renewed' && renewed === 'no') {
                show = true;
            } else if (filter === 'renewed' && renewed === 'yes') {
                show = true;
            } else if (filter === 'other-active' && otherActive === 'yes') {
                show = true;
            } else if (filter === 'truly-churned' && trulyChurned === 'yes') {
                show = true;
            } else if (filter === 'paused' && paused === 'yes') {
                show = true;
            } else if (filter === 'cancelled' && cancelled === 'yes') {
                show = true;
            }
            
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        
        // Update visible count in header
        document.getElementById('expired-visible-count').textContent = visibleCount;
    }
    
    function filterActiveMembers(filter) {
        var table = document.getElementById('active-members-table');
        var rows = table.querySelectorAll('tbody tr');
        var visibleCount = 0;
        
        // Update active button state
        var buttons = document.querySelectorAll('.active-filter-buttons .filter-btn');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            }
        });
        
        // Filter rows
        rows.forEach(function(row) {
            var status = row.getAttribute('data-status');
            var show = false;
            
            if (filter === 'all') {
                show = true;
            } else if (filter === status) {
                show = true;
            }
            
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        
        // Update visible count in header
        document.getElementById('active-members-visible-count').textContent = visibleCount;
    }

    // Filter function for Active Members Popup table
    function filterActiveMembersPopup(filter) {
        var table = document.getElementById('active-members-popup-table');
        if (!table) return;

        var rows = table.querySelectorAll('tbody tr');
        var visibleCount = 0;

        // Update active button state
        var buttons = document.querySelectorAll('.active-members-filters .filter-btn');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            }
        });

        // Filter rows
        rows.forEach(function(row) {
            var source = row.getAttribute('data-source');
            var show = false;

            if (filter === 'all') {
                show = true;
            } else if (filter === source) {
                show = true;
            }

            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
    }

    // Add click handlers for Active Members Popup filter buttons
    document.addEventListener('DOMContentLoaded', function() {
        var filterBtns = document.querySelectorAll('.active-members-filters .filter-btn');
        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var filter = this.getAttribute('data-filter');
                filterActiveMembersPopup(filter);
            });
        });
    });
    </script>
    
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
                        $is_clickable = in_array($ethnicity, ['Pacific Island', 'Asian', 'Other', 'Not Specified']) && 
                                       (($ethnicity != 'Not Specified' && !empty($ethnicity_detailed[$ethnicity])) || 
                                        $ethnicity == 'Not Specified');
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
                        <?php 
                        $is_age_clickable = ($age_range == 'Not Specified');
                        $age_item_class = $is_age_clickable ? 'breakdown-item age-group-' . str_replace('-', '_', $age_range) . ' clickable-breakdown-item' : 'breakdown-item age-group-' . str_replace('-', '_', $age_range);
                        $age_data_attr = $is_age_clickable ? 'data-detail-popup="age-not-specified"' : '';
                        ?>
                        <div class="<?php echo $age_item_class; ?>" <?php echo $age_data_attr; ?>>
                            <div class="breakdown-number"><?php echo $count; ?></div>
                            <div class="breakdown-label">Ages <?php echo $age_range; ?></div>
                            <div class="breakdown-percentage">
                                <?php echo round(($count / array_sum($age_breakdown)) * 100, 1); ?>%
                            </div>
                            <?php if ($is_age_clickable): ?>
                            <div class="breakdown-click-hint">👆 Click for details</div>
                            <?php endif; ?>
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
    
    <!-- Not Specified Details Popup -->
    <div id="not-specified-details-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-admin-users"></span> Members Without Ethnicity Information</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php 
                $members_without_ethnicity = get_members_without_ethnicity_data($active_member_ids);
                if (!empty($members_without_ethnicity)): 
                ?>
                <div class="not-specified-header">
                    <p><strong>Members who have not provided ethnicity information:</strong></p>
                    <p><small>These members need to update their profile with ethnicity information.</small></p>
                </div>
                <div class="not-specified-members-container">
                    <?php foreach ($members_without_ethnicity as $member): ?>
                    <div class="not-specified-member-card">
                        <div class="member-card-header">
                            <div class="member-info">
                                <h4 class="member-name"><?php echo esc_html($member['name']); ?></h4>
                                <div class="member-email"><?php echo esc_html($member['email']); ?></div>
                            </div>
                            <div class="status-badge missing-info">
                                Missing Data
                            </div>
                        </div>
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
                                        <div class="detail-label">Member Since</div>
                                        <div class="detail-value"><?php echo esc_html($member['member_since']); ?></div>
                                        <div class="detail-sub"><?php echo esc_html($member['days_active']); ?> ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="member-card-footer">
                            <div class="member-actions">
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>"
                                   class="action-btn edit-profile" target="_blank" title="Edit Profile">
                                    <span class="dashicons dashicons-edit"></span>
                                    <span class="btn-text">Edit Profile</span>
                                </a>
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
                    <p><strong>Members Missing Ethnicity:</strong> <?php echo count($members_without_ethnicity); ?></p>
                    <p><strong>Completion Rate:</strong> <?php echo round(((count($active_member_ids) - count($members_without_ethnicity)) / count($active_member_ids)) * 100, 1); ?>%</p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>All members have provided ethnicity information.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Age Not Specified Details Popup -->
    <div id="age-not-specified-details-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-admin-users"></span> Members Without Age Information</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php 
                $members_without_age = get_members_without_age_data($active_member_ids);
                if (!empty($members_without_age)): 
                ?>
                <div class="not-specified-header">
                    <p><strong>Members who have not provided age information:</strong></p>
                    <p><small>These members need to update their profile with age information.</small></p>
                </div>
                <div class="age-not-specified-members-container">
                    <?php foreach ($members_without_age as $member): ?>
                    <div class="age-not-specified-member-card">
                         <div class="member-card-header">
                             <div class="member-info">
                                 <h4 class="member-name">
                                     <?php echo esc_html($member['name']); ?>
                                     <span class="age-info">(<?php echo esc_html($member['age_display']); ?>)</span>
                                 </h4>
                                 <div class="member-email"><?php echo esc_html($member['email']); ?></div>
                             </div>
                             <div class="status-badge missing-info">
                                 Missing Age
                             </div>
                         </div>
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
                                        <div class="detail-label">Member Since</div>
                                        <div class="detail-value"><?php echo esc_html($member['member_since']); ?></div>
                                        <div class="detail-sub"><?php echo esc_html($member['days_active']); ?> ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="member-card-footer">
                            <div class="member-actions">
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>"
                                   class="action-btn edit-profile" target="_blank" title="Edit Profile">
                                    <span class="dashicons dashicons-edit"></span>
                                    <span class="btn-text">Edit Profile</span>
                                </a>
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
                    <p><strong>Members Missing Age:</strong> <?php echo count($members_without_age); ?></p>
                    <p><strong>Completion Rate:</strong> <?php echo round(((count($active_member_ids) - count($members_without_age)) / count($active_member_ids)) * 100, 1); ?>%</p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>All members have provided age information.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Waitlist Members Popup -->
    <div id="waitlist-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-clock"></span> Members on Waitlist</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php if (!empty($waitlist_members_detailed)): ?>
                <div class="waitlist-header">
                    <p><strong>Current members awaiting full program enrollment:</strong></p>
                    <p><small>These members have active waitlist memberships and are pending placement in regular programs.</small></p>
                </div>
                <div class="waitlist-members-container">
                    <?php foreach ($waitlist_members_detailed as $member): ?>
                    <div class="waitlist-member-card">
                        <div class="member-card-header">
                             <div class="member-info">
                                 <h4 class="member-name">
                                     <?php echo esc_html($member['name']); ?>
                                     <span class="age-info-waitlist">(<?php echo esc_html($member['age_display']); ?>)</span>
                                 </h4>
                                 <div class="member-email"><?php echo esc_html($member['email']); ?></div>
                             </div>
                             <div class="status-badge waitlist-status">
                                 On Waitlist
                             </div>
                        </div>
                        <div class="member-card-body">
                            <div class="member-details-grid">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <span class="dashicons dashicons-groups"></span>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Waitlist Program</div>
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
                                        <div class="detail-label">Joined Waitlist</div>
                                        <div class="detail-value"><?php echo esc_html($member['joined_date']); ?></div>
                                        <div class="detail-sub"><?php echo esc_html($member['days_waiting']); ?> ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="member-card-footer">
                            <div class="member-actions">
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>"
                                   class="action-btn view-profile" target="_blank" title="View Profile">
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <span class="btn-text">View Profile</span>
                                </a>
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
                    <p><strong>Total on Waitlist:</strong> <?php echo count($waitlist_members_detailed); ?></p>
                    <p><strong>Average Wait Time:</strong> <?php echo calculate_average_wait_time($waitlist_members_detailed); ?> days</p>
                    <p><strong>Longest Wait:</strong> <?php echo get_longest_wait_time($waitlist_members_detailed); ?> days</p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No members currently on waitlist.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Schools Breakdown Popup -->
    <div id="schools-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-building"></span> Schools Breakdown</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php if (!empty($schools_data)): ?>
                <div class="schools-list">
                    <?php foreach ($schools_data as $school): ?>
                    <div class="school-item">
                        <div class="school-info">
                            <h4><?php echo esc_html($school['name']); ?></h4>
                            <div class="school-stats">
                                <span class="student-count"><?php echo $school['student_count']; ?> students</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="breakdown-summary">
                    <p><strong>Total Schools:</strong> <?php echo $total_schools; ?></p>
                    <p><strong>Total Students:</strong> <?php echo array_sum(array_column($schools_data, 'student_count')); ?></p>
                    <p><strong>Largest School:</strong> <?php
                        $largest_school = '';
                        $max_students = 0;
                        foreach ($schools_data as $school) {
                            if ($school['student_count'] > $max_students) {
                                $max_students = $school['student_count'];
                                $largest_school = $school['name'];
                            }
                        }
                        echo esc_html($largest_school) . ' (' . $max_students . ' students)';
                    ?></p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No schools found with enrolled students.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Total Students Breakdown Popup -->
    <div id="total-students-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-groups"></span> Total Students Breakdown (<?php echo date('M j', strtotime($date_from)); ?> - <?php echo date('M j, Y', strtotime($date_to)); ?>)</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div class="total-students-header">
                    <p><strong>Complete breakdown of all students during the selected period:</strong></p>
                </div>
                <div class="breakdown-grid total-students-grid">
                    <div class="breakdown-item total-item">
                        <div class="breakdown-number"><?php echo $total_students_breakdown['total']; ?></div>
                        <div class="breakdown-label">Total Students</div>
                        <div class="breakdown-percentage">100%</div>
                    </div>
                    <div class="breakdown-item active-item">
                        <div class="breakdown-number"><?php echo $total_students_breakdown['active_count']; ?></div>
                        <div class="breakdown-label">Active Members</div>
                        <div class="breakdown-percentage">
                            <?php echo $total_students_breakdown['total'] > 0 ? round(($total_students_breakdown['active_count'] / $total_students_breakdown['total']) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                    <div class="breakdown-item non-renewed-item">
                        <div class="breakdown-number"><?php echo $total_students_breakdown['non_renewed_count']; ?></div>
                        <div class="breakdown-label">Non-Renewed</div>
                        <div class="breakdown-percentage">
                            <?php echo $total_students_breakdown['total'] > 0 ? round(($total_students_breakdown['non_renewed_count'] / $total_students_breakdown['total']) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                    <div class="breakdown-item paused-item">
                        <div class="breakdown-number"><?php echo $total_students_breakdown['paused_count']; ?></div>
                        <div class="breakdown-label">Paused</div>
                        <div class="breakdown-percentage">
                            <?php echo $total_students_breakdown['total'] > 0 ? round(($total_students_breakdown['paused_count'] / $total_students_breakdown['total']) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                </div>
                
                <div class="payment-breakdown-section">
                    <h4><span class="dashicons dashicons-money-alt"></span> Currently Active Members by Payment Method</h4>
                    <p class="section-description">Members with active Stripe subscriptions or valid manual transactions</p>
                    <div class="breakdown-grid payment-grid">
                        <div class="breakdown-item stripe-item">
                            <div class="breakdown-number"><?php echo $total_students_breakdown['stripe_count']; ?></div>
                            <div class="breakdown-label">Active Stripe Subscription</div>
                            <div class="breakdown-percentage">
                                <?php echo $total_students_breakdown['active_count'] > 0 ? round(($total_students_breakdown['stripe_count'] / $total_students_breakdown['active_count']) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                        <div class="breakdown-item manual-item">
                            <div class="breakdown-number"><?php echo $total_students_breakdown['manual_count']; ?></div>
                            <div class="breakdown-label">Manual (No Expiration)</div>
                            <div class="breakdown-percentage">
                                <?php echo $total_students_breakdown['active_count'] > 0 ? round(($total_students_breakdown['manual_count'] / $total_students_breakdown['active_count']) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    </div>
                </div>

                <div class="breakdown-summary">
                    <p><strong>Currently Active:</strong> <?php echo $total_students_breakdown['active_count']; ?>
                        <span class="breakdown-detail">(Stripe: <?php echo $total_students_breakdown['stripe_count']; ?> | Manual: <?php echo $total_students_breakdown['manual_count']; ?>)</span>
                    </p>
                    <p><strong>Non-Renewed:</strong> <?php echo $total_students_breakdown['non_renewed_count']; ?>
                        <span class="breakdown-detail">(expired during period, not renewed)</span>
                    </p>
                    <p><strong>Paused:</strong> <?php echo $total_students_breakdown['paused_count']; ?>
                        <span class="breakdown-detail">(suspended subscriptions)</span>
                    </p>
                    <p class="total-line"><strong>Total Students in Period:</strong> <?php echo $total_students_breakdown['total']; ?></p>
                </div>

                <!-- Other Active Memberships Section -->
                <div class="other-memberships-section">
                    <h4><span class="dashicons dashicons-awards"></span> Other Active Memberships</h4>
                    <p class="section-description">Members with Competitive Team or WCB Mentoring memberships (separate from program groups)</p>

                    <div class="breakdown-grid other-memberships-grid">
                        <div class="breakdown-item competitive-item">
                            <div class="breakdown-number"><?php echo $other_memberships['competitive_count']; ?></div>
                            <div class="breakdown-label">Competitive Team</div>
                        </div>
                        <div class="breakdown-item mentoring-item">
                            <div class="breakdown-number"><?php echo $other_memberships['mentoring_count']; ?></div>
                            <div class="breakdown-label">WCB Mentoring</div>
                        </div>
                    </div>

                    <div class="other-memberships-summary">
                        <p><strong>Total Unique Members:</strong> <?php echo $other_memberships['total_unique']; ?></p>
                        <p class="status-active"><span class="dashicons dashicons-yes-alt"></span> Also Active in Program: <?php echo $other_memberships['overlap_with_active_programs']; ?></p>
                        <p class="status-expired"><span class="dashicons dashicons-warning"></span> Program Expired (still in Competitive/Mentoring): <?php echo $other_memberships['with_expired_programs']; ?></p>
                        <p class="status-none"><span class="dashicons dashicons-minus"></span> Only Competitive/Mentoring (no program): <?php echo $other_memberships['only_other_memberships']; ?></p>
                    </div>

                    <?php if ($other_memberships['competitive_count'] > 0): ?>
                    <div class="members-list-section">
                        <h5><span class="dashicons dashicons-superhero"></span> Competitive Team Members (<?php echo $other_memberships['competitive_count']; ?>)</h5>
                        <div class="members-table-wrapper">
                            <table class="other-members-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Payment</th>
                                        <th>Expires</th>
                                        <th>Program Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($other_memberships['competitive_team'] as $member): ?>
                                    <tr>
                                        <td><?php echo esc_html($member['display_name']); ?></td>
                                        <td><?php echo esc_html($member['user_email']); ?></td>
                                        <td><span class="gateway-badge"><?php echo esc_html($member['gateway_display']); ?></span></td>
                                        <td><?php echo esc_html($member['expires_display']); ?></td>
                                        <td><span class="program-status-badge status-<?php echo esc_attr($member['program_status_class']); ?>"><?php echo esc_html($member['program_status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($other_memberships['mentoring_count'] > 0): ?>
                    <div class="members-list-section">
                        <h5><span class="dashicons dashicons-heart"></span> WCB Mentoring Members (<?php echo $other_memberships['mentoring_count']; ?>)</h5>
                        <div class="members-table-wrapper">
                            <table class="other-members-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Payment</th>
                                        <th>Expires</th>
                                        <th>Program Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($other_memberships['wcb_mentoring'] as $member): ?>
                                    <tr>
                                        <td><?php echo esc_html($member['display_name']); ?></td>
                                        <td><?php echo esc_html($member['user_email']); ?></td>
                                        <td><span class="gateway-badge"><?php echo esc_html($member['gateway_display']); ?></span></td>
                                        <td><?php echo esc_html($member['expires_display']); ?></td>
                                        <td><span class="program-status-badge status-<?php echo esc_attr($member['program_status_class']); ?>"><?php echo esc_html($member['program_status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Paused Members Popup -->
    <div id="paused-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-controls-pause"></span> Paused Members</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php 
                $paused_members = $total_students_breakdown['paused_members'];
                if (!empty($paused_members)): 
                ?>
                <div class="paused-header">
                    <p><strong>Members with paused/suspended subscriptions:</strong></p>
                    <p><small>These members have temporarily paused their membership and are not currently attending.</small></p>
                </div>
                <div class="paused-members-container">
                    <?php foreach ($paused_members as $member): ?>
                    <div class="paused-member-card">
                        <div class="member-card-header">
                            <div class="member-info">
                                <h4 class="member-name"><?php echo esc_html($member['name']); ?></h4>
                                <div class="member-email"><?php echo esc_html($member['email']); ?></div>
                            </div>
                            <div class="status-badge paused-status">
                                Paused
                            </div>
                        </div>
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
                                        <div class="detail-label">Paused Since</div>
                                        <div class="detail-value"><?php echo esc_html($member['paused_date']); ?></div>
                                        <?php if (!empty($member['days_paused'])): ?>
                                        <div class="detail-sub"><?php echo esc_html($member['days_paused']); ?> ago</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    <p><strong>Total Paused:</strong> <?php echo count($paused_members); ?></p>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No paused members found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Active Members Breakdown Popup -->
    <div id="active-members-popup" class="stats-popup" style="display: none;">
        <div class="popup-overlay"></div>
        <div class="popup-content popup-content-large">
            <div class="popup-header">
                <h3><span class="dashicons dashicons-admin-users"></span> Active Members Breakdown</h3>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <?php
                $currently_active = $total_students_breakdown['currently_active_members'];
                $stripe_count = $total_students_breakdown['stripe_count'];
                $manual_count = $total_students_breakdown['manual_count'];
                $active_count = $total_students_breakdown['active_count'];
                ?>

                <div class="active-members-header">
                    <p><strong>Currently active members with valid paid program memberships:</strong></p>
                    <p><small>These members have an active Stripe subscription or valid manual transaction for a program group.</small></p>
                </div>

                <!-- Payment Method Breakdown -->
                <div class="payment-breakdown-section">
                    <h4><span class="dashicons dashicons-money-alt"></span> By Payment Method</h4>
                    <div class="breakdown-grid payment-grid">
                        <div class="breakdown-item stripe-item">
                            <div class="breakdown-number"><?php echo $stripe_count; ?></div>
                            <div class="breakdown-label">Stripe Subscriptions</div>
                            <div class="breakdown-percentage">
                                <?php echo $active_count > 0 ? round(($stripe_count / $active_count) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                        <div class="breakdown-item manual-item">
                            <div class="breakdown-number"><?php echo $manual_count; ?></div>
                            <div class="breakdown-label">Manual Transactions</div>
                            <div class="breakdown-percentage">
                                <?php echo $active_count > 0 ? round(($manual_count / $active_count) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Members List -->
                <?php if (!empty($currently_active)): ?>
                <div class="members-list-section">
                    <h4><span class="dashicons dashicons-list-view"></span> Active Members List (<?php echo count($currently_active); ?>)</h4>

                    <!-- Filter Buttons -->
                    <div class="filter-buttons active-members-filters">
                        <button class="filter-btn active" data-filter="all">
                            All <span class="filter-count">(<?php echo count($currently_active); ?>)</span>
                        </button>
                        <button class="filter-btn" data-filter="stripe">
                            Stripe <span class="filter-count">(<?php echo $stripe_count; ?>)</span>
                        </button>
                        <button class="filter-btn" data-filter="manual">
                            Manual <span class="filter-count">(<?php echo $manual_count; ?>)</span>
                        </button>
                    </div>

                    <div class="members-table-wrapper">
                        <table class="active-members-table" id="active-members-popup-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Membership</th>
                                    <th>Payment Type</th>
                                    <th>Expires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currently_active as $member):
                                    $expires_display = 'Never';
                                    if (!empty($member['expires_at']) && $member['expires_at'] !== '0000-00-00 00:00:00') {
                                        $expires_display = date('d M Y', strtotime($member['expires_at']));
                                    }
                                    $source_class = $member['source'] === 'stripe' ? 'stripe' : 'manual';
                                    $source_label = $member['source'] === 'stripe' ? 'Stripe' : 'Manual';
                                ?>
                                <tr data-source="<?php echo esc_attr($member['source']); ?>">
                                    <td class="member-name"><?php echo esc_html($member['display_name']); ?></td>
                                    <td class="member-email"><?php echo esc_html($member['user_email']); ?></td>
                                    <td class="member-membership"><?php echo esc_html($member['membership_name'] ?? 'N/A'); ?></td>
                                    <td class="member-source">
                                        <span class="source-badge <?php echo $source_class; ?>-badge"><?php echo $source_label; ?></span>
                                    </td>
                                    <td class="member-expires"><?php echo esc_html($expires_display); ?></td>
                                    <td class="member-actions">
                                        <a href="<?php echo admin_url('user-edit.php?user_id=' . $member['user_id']); ?>"
                                           class="action-btn view-profile" target="_blank" title="View Profile">
                                            <span class="dashicons dashicons-admin-users"></span>
                                        </a>
                                        <a href="mailto:<?php echo esc_attr($member['user_email']); ?>"
                                           class="action-btn send-email" title="Send Email">
                                            <span class="dashicons dashicons-email-alt"></span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <p>No active members found with paid program memberships.</p>
                    <p><small>Check the debug logs for more information about subscription status.</small></p>
                </div>
                <?php endif; ?>

                <div class="breakdown-summary">
                    <p><strong>Total Active Members:</strong> <?php echo $active_count; ?>
                        <span class="breakdown-detail">(Stripe: <?php echo $stripe_count; ?> | Manual: <?php echo $manual_count; ?>)</span>
                    </p>
                </div>
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
    .stat-card.total-students {
        border-top-color: #4A90D9;
    }
    
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

    .stat-card.schools {
        border-top-color: #32CD32;
    }
    
    .stat-card.paused {
        border-top-color: #FFA500;
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

    .popup-content-large {
        max-width: 900px;
    }

    /* Active Members Popup Styles */
    .active-members-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e5e5;
    }

    .active-members-header p {
        margin: 5px 0;
    }

    .active-members-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .active-members-filters .filter-btn {
        padding: 8px 16px;
        border: 1px solid #ddd;
        background: #f8f9fa;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
    }

    .active-members-filters .filter-btn:hover {
        background: #e9ecef;
        border-color: #ccc;
    }

    .active-members-filters .filter-btn.active {
        background: #000;
        color: #fff;
        border-color: #000;
    }

    .active-members-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .active-members-table th,
    .active-members-table td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #e5e5e5;
    }

    .active-members-table th {
        background: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        color: #666;
    }

    .active-members-table tbody tr:hover {
        background: #f8f9fa;
    }

    .active-members-table .member-name {
        font-weight: 500;
    }

    .active-members-table .member-email {
        color: #666;
        font-size: 12px;
    }

    .active-members-table .member-membership {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .source-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .source-badge.stripe-badge {
        background: #635bff;
        color: white;
    }

    .source-badge.manual-badge {
        background: #6c757d;
        color: white;
    }

    .active-members-table .member-actions {
        display: flex;
        gap: 8px;
    }

    .active-members-table .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 4px;
        background: #f0f0f0;
        color: #333;
        text-decoration: none;
        transition: all 0.2s;
    }

    .active-members-table .action-btn:hover {
        background: #000;
        color: #fff;
    }

    .active-members-table .action-btn .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
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
    
    /* Total Students Popup Styles */
    .total-students-header {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #4A90D9;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .total-students-header p {
        margin: 0;
        font-size: 14px;
        color: #000000;
    }
    
    .total-students-grid .breakdown-item.total-item {
        background: #e8f4fc;
        border-color: #4A90D9;
    }
    
    .total-students-grid .breakdown-item.active-item {
        background: #e8f8e8;
        border-color: #27ae60;
    }
    
    .total-students-grid .breakdown-item.non-renewed-item {
        background: #fff3e8;
        border-color: #FF6B6B;
    }
    
    .total-students-grid .breakdown-item.paused-item {
        background: #fff8e1;
        border-color: #FFA500;
    }
    
    .payment-breakdown-section {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e5e5e5;
    }
    
    .payment-breakdown-section h4 {
        margin: 0 0 16px 0;
        font-size: 14px;
        font-weight: 600;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .payment-breakdown-section h4 .dashicons {
        font-size: 16px;
        color: #666666;
    }
    
    .payment-grid .breakdown-item.manual-item {
        background: #fff8e1;
        border-color: #ffc107;
    }
    
    .payment-grid .breakdown-item.stripe-item {
        background: #e8f0fe;
        border-color: #635bff;
    }

    .payment-breakdown-section .section-description {
        margin: 0 0 16px 0;
        font-size: 12px;
        color: #666;
        font-style: italic;
    }

    .competitive-only-note {
        margin-top: 12px;
        padding: 10px 14px;
        background: #fff8e1;
        border: 1px solid #ffc107;
        border-radius: 6px;
        font-size: 13px;
        color: #856404;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .competitive-only-note .dashicons {
        font-size: 16px;
        color: #ffa000;
    }

    .breakdown-summary .breakdown-detail {
        font-weight: normal;
        color: #666;
        font-size: 12px;
        margin-left: 4px;
    }

    .breakdown-summary .total-line {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e0e0e0;
    }

    /* Other Active Memberships Section Styles */
    .other-memberships-section {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 2px solid #e5e5e5;
    }

    .other-memberships-section h4 {
        margin: 0 0 8px 0;
        font-size: 14px;
        font-weight: 600;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .other-memberships-section h4 .dashicons {
        font-size: 16px;
        color: #9c27b0;
    }

    .other-memberships-section .section-description {
        margin: 0 0 16px 0;
        font-size: 12px;
        color: #666;
        font-style: italic;
    }

    .other-memberships-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .other-memberships-grid .breakdown-item.competitive-item {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-color: #1976d2;
    }

    .other-memberships-grid .breakdown-item.mentoring-item {
        background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%);
        border-color: #c2185b;
    }

    .other-memberships-summary {
        background: #f5f5f5;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }

    .other-memberships-summary p {
        margin: 4px 0;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .other-memberships-summary p .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
    }

    .other-memberships-summary .status-active {
        color: #2e7d32;
    }

    .other-memberships-summary .status-expired {
        color: #f57c00;
    }

    .other-memberships-summary .status-none {
        color: #757575;
    }

    .members-list-section {
        margin-top: 16px;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }

    .members-list-section h5 {
        margin: 0;
        padding: 12px 16px;
        background: #f5f5f5;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #e5e5e5;
    }

    .members-list-section h5 .dashicons {
        font-size: 14px;
        color: #666;
    }

    .members-table-wrapper {
        max-height: 250px;
        overflow-y: auto;
    }

    .other-members-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .other-members-table th,
    .other-members-table td {
        padding: 8px 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .other-members-table th {
        background: #fafafa;
        font-weight: 600;
        color: #333;
        position: sticky;
        top: 0;
    }

    .other-members-table tr:hover {
        background: #f9f9f9;
    }

    .other-members-table .gateway-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        background: #e3f2fd;
        color: #1565c0;
    }

    .program-status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
    }

    .program-status-badge.status-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .program-status-badge.status-expired {
        background: #fff3e0;
        color: #e65100;
    }

    .program-status-badge.status-none {
        background: #f5f5f5;
        color: #757575;
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

     .age-info {
         font-weight: 400;
         color: #dc3545;
         font-size: 12px;
         margin-left: 8px;
         background: #f8d7da;
         padding: 2px 6px;
         border-radius: 4px;
         border: 1px solid #f5c6cb;
         white-space: nowrap;
     }

     .age-info-waitlist {
         font-weight: 400;
         color: #1976d2;
         font-size: 12px;
         margin-left: 8px;
         background: #e3f2fd;
         padding: 2px 6px;
         border-radius: 4px;
         border: 1px solid #bbdefb;
         white-space: nowrap;
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

    .status-badge.missing-info {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-badge.waitlist-status {
        background: #f0f8ff;
        color: #1976d2;
        border: 1px solid #bbdefb;
    }

    /* Not Specified Members Popup Styles */
    .not-specified-header {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #ffc107;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .not-specified-header p {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #000000;
    }
    
    .not-specified-header p:last-child {
        margin-bottom: 0;
    }
    
    .not-specified-members-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .not-specified-member-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .not-specified-member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #ffc107;
    }
    
    /* Age Not Specified Members Popup Styles */
    .age-not-specified-members-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .age-not-specified-member-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .age-not-specified-member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #17a2b8;
    }
    
    /* Waitlist Members Popup Styles */
    .waitlist-header {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #1976d2;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .waitlist-header p {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #000000;
    }
    
    .waitlist-header p:last-child {
        margin-bottom: 0;
    }
    
    .waitlist-members-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .waitlist-member-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
        .waitlist-member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #1976d2;
        }

    /* Paused Members Popup Styles */
    .paused-header {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #FFA500;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .paused-header p {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #000000;
    }
    
    .paused-header p:last-child {
        margin-bottom: 0;
    }
    
    .paused-members-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .paused-member-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .paused-member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #FFA500;
    }

    .status-badge.paused-status {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }

        /* Responsive schools list */
        .schools-list {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
        }

    /* Schools Popup Styles */
    .schools-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .school-item {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 16px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .school-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #32CD32;
    }

    .school-info h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #000000;
    }

    .school-stats {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .student-count {
        font-size: 14px;
        color: #666666;
        font-weight: 500;
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

    .action-btn.edit-profile {
        background: #fff3e0;
        color: #f57c00;
        border-color: #ffcc02;
    }

    .action-btn.edit-profile:hover {
        background: #ffcc02;
        color: #e65100;
        border-color: #ffb300;
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
        .non-renewed-cards-container,
        .not-specified-members-container,
        .age-not-specified-members-container,
        .waitlist-members-container {
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
        .non-renewed-cards-container,
        .not-specified-members-container,
        .age-not-specified-members-container,
        .waitlist-members-container {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .schools-list {
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
    
    /* Debug Section Styles */
    .dashboard-debug-section {
        margin-top: 30px;
        border: 2px dashed #ccc;
        background: #fafafa;
    }
    
    .debug-toggle-header {
        background: #2c3e50;
        color: white;
        padding: 16px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        transition: background 0.2s ease;
    }
    
    .debug-toggle-header:hover {
        background: #34495e;
    }
    
    .debug-toggle-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .debug-toggle-header h3 .dashicons {
        font-size: 20px;
    }
    
    .debug-toggle-header small {
        color: #bdc3c7;
        font-size: 12px;
    }
    
    .debug-toggle-icon {
        font-size: 24px;
        font-weight: bold;
        margin-left: 10px;
    }
    
    .debug-content {
        padding: 20px;
    }
    
    .debug-table-section {
        margin-bottom: 30px;
        background: white;
        border: 1px solid #e5e5e5;
        padding: 20px;
    }
    
    .debug-table-section h4 {
        margin: 0 0 10px 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .debug-table-section h4 .dashicons {
        font-size: 18px;
    }
    
    .debug-table-section h4 .dashicons-yes-alt {
        color: #27ae60;
    }
    
    .debug-table-section h4 .dashicons-warning {
        color: #e74c3c;
    }
    
    /* Expired Members Filter Buttons */
    .expired-filter-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .expired-filter-buttons .filter-btn {
        padding: 8px 16px;
        border: 2px solid #e5e5e5;
        background: white;
        color: #666;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .expired-filter-buttons .filter-btn:hover {
        border-color: #999;
        background: #f8f9fa;
    }
    
    .expired-filter-buttons .filter-btn.active {
        border-color: #2c3e50;
        background: #2c3e50;
        color: white;
    }
    
    .expired-filter-buttons .filter-btn.filter-not-renewed:hover,
    .expired-filter-buttons .filter-btn.filter-not-renewed.active {
        border-color: #e74c3c;
        background: #e74c3c;
        color: white;
    }
    
    .expired-filter-buttons .filter-btn.filter-renewed:hover,
    .expired-filter-buttons .filter-btn.filter-renewed.active {
        border-color: #27ae60;
        background: #27ae60;
        color: white;
    }
    
    .expired-filter-buttons .filter-btn.filter-other-active:hover,
    .expired-filter-buttons .filter-btn.filter-other-active.active {
        border-color: #f39c12;
        background: #f39c12;
        color: white;
    }
    
    .expired-filter-buttons .filter-btn.filter-truly-churned:hover,
    .expired-filter-buttons .filter-btn.filter-truly-churned.active {
        border-color: #c0392b;
        background: #c0392b;
        color: white;
    }
    
    .expired-filter-buttons .filter-btn.filter-paused-status:hover,
    .expired-filter-buttons .filter-btn.filter-paused-status.active {
        border-color: #f9a825;
        background: #f9a825;
        color: white;
    }
    
    .expired-filter-buttons .filter-btn.filter-cancelled-status:hover,
    .expired-filter-buttons .filter-btn.filter-cancelled-status.active {
        border-color: #757575;
        background: #757575;
        color: white;
    }
    
    .expired-filter-buttons .filter-count {
        opacity: 0.8;
        font-weight: 500;
    }
    
    /* Active Members Filter Buttons */
    .active-filter-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .active-filter-buttons .filter-btn {
        padding: 8px 16px;
        border: 2px solid #e5e5e5;
        background: white;
        color: #666;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .active-filter-buttons .filter-btn:hover {
        border-color: #999;
        background: #f8f9fa;
    }
    
    .active-filter-buttons .filter-btn.active {
        border-color: #2c3e50;
        background: #2c3e50;
        color: white;
    }
    
    .active-filter-buttons .filter-btn.filter-active-status:hover,
    .active-filter-buttons .filter-btn.filter-active-status.active {
        border-color: #27ae60;
        background: #27ae60;
        color: white;
    }
    
    .active-filter-buttons .filter-btn.filter-paused-status:hover,
    .active-filter-buttons .filter-btn.filter-paused-status.active {
        border-color: #f9a825;
        background: #f9a825;
        color: white;
    }
    
    .active-filter-buttons .filter-btn.filter-cancelled-status:hover,
    .active-filter-buttons .filter-btn.filter-cancelled-status.active {
        border-color: #757575;
        background: #757575;
        color: white;
    }
    
    .active-filter-buttons .filter-btn.filter-expired-status:hover,
    .active-filter-buttons .filter-btn.filter-expired-status.active {
        border-color: #e74c3c;
        background: #e74c3c;
        color: white;
    }
    
    .active-filter-buttons .filter-count {
        opacity: 0.8;
        font-weight: 500;
    }
    
    .debug-description {
        margin: 0 0 15px 0;
        padding: 10px 15px;
        background: #f8f9fa;
        border-left: 3px solid #3498db;
        font-size: 13px;
        color: #555;
    }
    
    .debug-table-wrapper {
        overflow-x: auto;
        margin-bottom: 15px;
    }
    
    .debug-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    
    .debug-table th {
        background: #2c3e50;
        color: white;
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    
    .debug-table td {
        padding: 10px;
        border-bottom: 1px solid #e5e5e5;
        vertical-align: middle;
    }
    
    .debug-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .debug-table a {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
    }
    
    .debug-table a:hover {
        text-decoration: underline;
    }
    
    .gateway-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .gateway-badge.gateway-manual {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    
    .gateway-badge.gateway-stripe {
        background: #e8f4fd;
        color: #635bff;
        border: 1px solid #635bff;
    }
    
    .gateway-badge.gateway-unknown {
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }
    
    .no-data-badge {
        display: inline-block;
        padding: 2px 6px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 4px;
        font-size: 11px;
    }
    
    .never-expires-badge {
        display: inline-block;
        padding: 2px 6px;
        background: #d4edda;
        color: #155724;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .expired-date-badge {
        display: inline-block;
        padding: 2px 6px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .member-expired-row {
        background: #fff5f5 !important;
    }
    
    .member-expired-row:hover {
        background: #ffe8e8 !important;
    }
    
    .member-paused-row {
        background: #fffde7 !important;
    }
    
    .member-paused-row:hover {
        background: #fff9c4 !important;
    }
    
    .member-cancelled-row {
        background: #f5f5f5 !important;
    }
    
    .member-cancelled-row:hover {
        background: #e0e0e0 !important;
    }
    
    .renewed-badge {
        display: inline-block;
        padding: 3px 8px;
        background: #d4edda;
        color: #155724;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .not-renewed-badge {
        display: inline-block;
        padding: 3px 8px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .renewed-row {
        background: #f8fff8;
    }
    
    .not-renewed-row {
        background: #fff8f8;
    }
    
    .other-active-row {
        background: #fff9e6;
    }
    
    .truly-churned-row {
        background: #ffe6e6;
    }
    
    .expired-date {
        color: #e74c3c;
        font-weight: 500;
    }
    
    .member-email-small {
        font-size: 11px;
        color: #6c757d;
        margin-top: 2px;
    }
    
    .membership-cell {
        max-width: 200px;
        font-size: 12px;
    }
    
    .status-detail-cell {
        max-width: 250px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    
    .status-badge.status-renewed {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-badge.status-other-active {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .status-badge.status-churned {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Status badges for subscription status column */
    .status-badge-small {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-badge-small.status-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-badge-small.status-paused {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    
    .status-badge-small.status-cancelled {
        background: #e0e0e0;
        color: #424242;
        border: 1px solid #bdbdbd;
    }
    
    .status-badge-small.status-expired {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .status-badge-small.status-none {
        background: #f5f5f5;
        color: #757575;
        border: 1px solid #e0e0e0;
    }
    
    .status-detail-text {
        font-size: 11px;
        color: #555;
        line-height: 1.3;
        word-break: break-word;
    }
    
    .current-expires-renewed {
        color: #27ae60;
        font-weight: 500;
    }
    
    .current-expires-none {
        color: #e74c3c;
        font-weight: 500;
        font-style: italic;
    }
    
    .debug-summary {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #2c3e50;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
    }
    
    .summary-item {
        text-align: center;
        padding: 10px;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
    }
    
    .summary-item.warning {
        background: #fff8f8;
        border-color: #f5c6cb;
    }
    
    .summary-item.success {
        background: #f8fff8;
        border-color: #c3e6cb;
    }
    
    .summary-item.info {
        background: #fff9e6;
        border-color: #ffeeba;
    }
    
    .summary-number {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .summary-item.warning .summary-number {
        color: #e74c3c;
    }
    
    .summary-item.success .summary-number {
        color: #27ae60;
    }
    
    .summary-item.info .summary-number {
        color: #856404;
    }
    
    .summary-item.paused {
        background: #fffde7;
        border-color: #ffc107;
    }
    
    .summary-item.paused .summary-number {
        color: #f9a825;
    }
    
    .summary-item.cancelled {
        background: #f5f5f5;
        border-color: #bdbdbd;
    }
    
    .summary-item.cancelled .summary-number {
        color: #616161;
    }
    
    .summary-explanation {
        margin-top: 15px;
        padding: 12px 15px;
        background: #e8f4fc;
        border: 1px solid #bee5eb;
        border-radius: 4px;
    }
    
    .summary-explanation p {
        margin: 0 0 8px 0;
        font-size: 13px;
        font-weight: 600;
        color: #0c5460;
    }
    
    .summary-explanation ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .summary-explanation li {
        font-size: 12px;
        color: #0c5460;
        margin-bottom: 4px;
    }
    
    .summary-explanation li:last-child {
        margin-bottom: 0;
    }
    
    .summary-label {
        display: block;
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }
    
    @media (max-width: 768px) {
        .debug-table {
            font-size: 12px;
        }
        
        .debug-table th,
        .debug-table td {
            padding: 8px 6px;
        }
        
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

// Helper function to get member join date from custom fields or transaction
function wcb_get_member_join_date($user_id, $product_id = null) {
    global $wpdb;
    
    // Priority 1: Check MemberPress custom registration date fields
    $possible_date_fields = [
        'mepr_registration_date',
        'mepr_date_registered'
    ];
    
    foreach ($possible_date_fields as $field_name) {
        $registration_date = get_user_meta($user_id, $field_name, true);
        
        // Skip if empty or invalid placeholder values
        if (empty($registration_date) || 
            $registration_date === '0000-00-00' || 
            $registration_date === '0000-00-00 00:00:00' || 
            $registration_date === '1970-01-01' ||
            $registration_date === '1970-01-01 00:00:00') {
            continue;
        }
        
        // Return valid date in YYYY-MM-DD format
        $timestamp = null;
        
        // Handle DD/MM/YYYY format
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $registration_date, $matches)) {
            $day = intval($matches[1]);
            $month = intval($matches[2]);
            $year = intval($matches[3]);
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
        }
        // Handle YYYY-MM-DD format
        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $registration_date)) {
            $timestamp = strtotime($registration_date);
        }
        
        if ($timestamp && $timestamp > 0) {
            return date('Y-m-d', $timestamp);
        }
    }
    
    // Priority 2: Fallback to transaction created_at date
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    if ($product_id) {
        // Get the earliest transaction for this specific product
        $created_at = $wpdb->get_var($wpdb->prepare("
            SELECT DATE(created_at)
            FROM {$txn_table}
            WHERE user_id = %d
            AND product_id = %d
            AND status IN ('confirmed', 'complete')
            ORDER BY created_at ASC
            LIMIT 1
        ", $user_id, $product_id));
    } else {
        // Get the earliest transaction for any product
        $created_at = $wpdb->get_var($wpdb->prepare("
            SELECT DATE(created_at)
            FROM {$txn_table}
            WHERE user_id = %d
            AND status IN ('confirmed', 'complete')
            ORDER BY created_at ASC
            LIMIT 1
        ", $user_id));
    }
    
    return $created_at ?: null;
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
            // Apply date filter if provided
            if ($date_from && $date_to) {
                // Filter by date range: members who joined on/before date_to AND (still active OR expired on/after date_from)
                // Use custom registration date fields if available, otherwise fall back to transaction created_at
                $group_members = $wpdb->get_results($wpdb->prepare("
                    SELECT DISTINCT u.ID
                    FROM {$wpdb->users} u
                    JOIN {$txn_table} t ON u.ID = t.user_id
                    LEFT JOIN {$wpdb->usermeta} um_reg ON u.ID = um_reg.user_id 
                        AND um_reg.meta_key IN ('mepr_registration_date', 'mepr_date_registered')
                    WHERE t.product_id IN ({$placeholders})
                    AND t.status IN ('confirmed', 'complete')
                    AND (
                        CASE 
                            WHEN um_reg.meta_value IS NOT NULL 
                                AND um_reg.meta_value != '0000-00-00' 
                                AND um_reg.meta_value != '0000-00-00 00:00:00'
                                AND um_reg.meta_value != '1970-01-01'
                                AND um_reg.meta_value != '1970-01-01 00:00:00'
                            THEN STR_TO_DATE(um_reg.meta_value, '%%d/%%m/%%Y')
                            ELSE DATE(t.created_at)
                        END
                    ) <= %s
                    AND (
                        t.expires_at IS NULL 
                        OR t.expires_at = '0000-00-00 00:00:00'
                        OR DATE(t.expires_at) >= %s
                    )
                    AND u.user_login != 'bwgdev'
                    ORDER BY u.ID
                ", array_merge($membership_ids, [$date_to, $date_from])));
            } else {
                // No date filter - use current active members logic
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
    if ($date_from && $date_to) {
        $competitive_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            LEFT JOIN {$wpdb->usermeta} um_reg ON u.ID = um_reg.user_id 
                AND um_reg.meta_key IN ('mepr_registration_date', 'mepr_date_registered')
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (
                CASE 
                    WHEN um_reg.meta_value IS NOT NULL 
                        AND um_reg.meta_value != '0000-00-00' 
                        AND um_reg.meta_value != '0000-00-00 00:00:00'
                        AND um_reg.meta_value != '1970-01-01'
                        AND um_reg.meta_value != '1970-01-01 00:00:00'
                    THEN STR_TO_DATE(um_reg.meta_value, '%%d/%%m/%%Y')
                    ELSE DATE(t.created_at)
                END
            ) <= %s
            AND (
                t.expires_at IS NULL 
                OR t.expires_at = '0000-00-00 00:00:00'
                OR DATE(t.expires_at) >= %s
            )
            AND u.user_login != 'bwgdev'
        ", $competitive_team_id, $date_to, $date_from));
    } else {
        $competitive_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            AND u.user_login != 'bwgdev'
        ", $competitive_team_id));
    }

    $competitive_count = count($competitive_members);
    
    // Add Competitive Team members to total count (avoiding duplicates)
    foreach ($competitive_members as $competitive_member) {
        $total_active_members[$competitive_member->ID] = true;
    }

    // Add Competitive Team to breakdown if there are members
    if ($competitive_count > 0) {
        $group_breakdown['Competitive Team'] = $competitive_count;
    }

    // STEP 3: Also include WCB Mentoring members (ID: 1738) for the programs breakdown
    $wcb_mentoring_id = 1738;
    if ($date_from && $date_to) {
        $mentoring_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            LEFT JOIN {$wpdb->usermeta} um_reg ON u.ID = um_reg.user_id 
                AND um_reg.meta_key IN ('mepr_registration_date', 'mepr_date_registered')
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (
                CASE 
                    WHEN um_reg.meta_value IS NOT NULL 
                        AND um_reg.meta_value != '0000-00-00' 
                        AND um_reg.meta_value != '0000-00-00 00:00:00'
                        AND um_reg.meta_value != '1970-01-01'
                        AND um_reg.meta_value != '1970-01-01 00:00:00'
                    THEN STR_TO_DATE(um_reg.meta_value, '%%d/%%m/%%Y')
                    ELSE DATE(t.created_at)
                END
            ) <= %s
            AND (
                t.expires_at IS NULL 
                OR t.expires_at = '0000-00-00 00:00:00'
                OR DATE(t.expires_at) >= %s
            )
            AND u.user_login != 'bwgdev'
        ", $wcb_mentoring_id, $date_to, $date_from));
    } else {
        $mentoring_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            AND u.user_login != 'bwgdev'
        ", $wcb_mentoring_id));
    }

    $mentoring_count = count($mentoring_members);
    
    // Add WCB Mentoring to breakdown if there are members
    if ($mentoring_count > 0) {
        $group_breakdown['WCB Mentoring'] = $mentoring_count;
    }

    // Note: We don't add mentoring members to the total_count because 
    // the main "Active Students" count excludes mentoring by design

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
            // Apply same date filter logic as get_active_members_from_defined_groups
            if ($date_from && $date_to) {
                $group_members = $wpdb->get_results($wpdb->prepare("
                    SELECT DISTINCT u.ID
                    FROM {$wpdb->users} u
                    JOIN {$txn_table} t ON u.ID = t.user_id
                    LEFT JOIN {$wpdb->usermeta} um_reg ON u.ID = um_reg.user_id 
                        AND um_reg.meta_key IN ('mepr_registration_date', 'mepr_date_registered')
                    WHERE t.product_id IN ({$placeholders})
                    AND t.status IN ('confirmed', 'complete')
                    AND (
                        CASE 
                            WHEN um_reg.meta_value IS NOT NULL 
                                AND um_reg.meta_value != '0000-00-00' 
                                AND um_reg.meta_value != '0000-00-00 00:00:00'
                                AND um_reg.meta_value != '1970-01-01'
                                AND um_reg.meta_value != '1970-01-01 00:00:00'
                            THEN STR_TO_DATE(um_reg.meta_value, '%%d/%%m/%%Y')
                            ELSE DATE(t.created_at)
                        END
                    ) <= %s
                    AND (
                        t.expires_at IS NULL 
                        OR t.expires_at = '0000-00-00 00:00:00'
                        OR DATE(t.expires_at) >= %s
                    )
                    AND u.user_login != 'bwgdev'
                    ORDER BY u.ID
                ", array_merge($membership_ids, [$date_to, $date_from])));
            } else {
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

    // STEP 2: Also include Competitive Team members (ID: 1932) to match dashboard-students.php logic
    $competitive_team_id = 1932;
    if ($date_from && $date_to) {
        $competitive_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            LEFT JOIN {$wpdb->usermeta} um_reg ON u.ID = um_reg.user_id 
                AND um_reg.meta_key IN ('mepr_registration_date', 'mepr_date_registered')
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (
                CASE 
                    WHEN um_reg.meta_value IS NOT NULL 
                        AND um_reg.meta_value != '0000-00-00' 
                        AND um_reg.meta_value != '0000-00-00 00:00:00'
                        AND um_reg.meta_value != '1970-01-01'
                        AND um_reg.meta_value != '1970-01-01 00:00:00'
                    THEN STR_TO_DATE(um_reg.meta_value, '%%d/%%m/%%Y')
                    ELSE DATE(t.created_at)
                END
            ) <= %s
            AND (
                t.expires_at IS NULL 
                OR t.expires_at = '0000-00-00 00:00:00'
                OR DATE(t.expires_at) >= %s
            )
            AND u.user_login != 'bwgdev'
        ", $competitive_team_id, $date_to, $date_from));
    } else {
        $competitive_members = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            WHERE t.product_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            AND u.user_login != 'bwgdev'
        ", $competitive_team_id));
    }

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

    // DEBUG: Log how many expired transactions were found
    wcb_debug_log("=== NON-RENEWED MEMBERS CHECK ===");
    wcb_debug_log("Date Range: {$date_from} to {$date_to}");
    wcb_debug_log("Found " . count($expired_transactions) . " expired transactions in defined groups");
    
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
        wcb_debug_log("  - Expired Date (UTC): {$expired_txn->expires_at}");

        // Check if user has a PAUSED subscription - if so, they're not "non-renewed", they're "paused"
        $has_paused_subscription = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}mepr_subscriptions
            WHERE user_id = %d
            AND status = 'suspended'
        ", $user_id));
        
        // Only consider non-renewed if ALL conditions are true:
        // 1. No currently active membership AND 
        // 2. No renewal transactions after expiry AND
        // 3. No paused/suspended subscription (those go in the Paused category)
        if (!$has_active_membership && $renewed_membership == 0 && $has_paused_subscription == 0) {
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

// NEW: Function to get paused members
// Paused members have a subscription with status='suspended' (MemberPress uses 'suspended' for paused)
// Filters by date range - shows members whose subscription was paused (last expires_at) within the date range
// NOTE: mepr_subscriptions table does NOT have expires_at column - get it from latest transaction instead
function get_paused_members_from_defined_groups($date_from, $date_to) {
    global $wpdb;

    $subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    // Get suspended subscriptions where the last transaction expired within the date range
    // This means the member paused their subscription during or before the selected period
    $query = $wpdb->prepare("
        SELECT s.user_id, s.id as subscription_id, s.product_id, s.status as subscription_status,
               s.created_at as subscription_created,
               u.display_name, u.user_email, p.post_title as program_name,
               (SELECT MAX(t.expires_at) FROM {$txn_table} t WHERE t.subscription_id = s.id) as last_expires_at
        FROM {$subscriptions_table} s
        JOIN {$wpdb->users} u ON s.user_id = u.ID
        JOIN {$wpdb->posts} p ON s.product_id = p.ID
        WHERE s.status = 'suspended'
        HAVING last_expires_at IS NULL 
           OR last_expires_at = '0000-00-00 00:00:00'
           OR DATE(last_expires_at) <= %s
        ORDER BY last_expires_at DESC
    ", $date_to);
    
    $all_paused_subscriptions = $wpdb->get_results($query);

    $paused_members = [];
    $processed_users = [];

    if (!empty($all_paused_subscriptions)) {
        foreach ($all_paused_subscriptions as $paused_sub) {
            $user_id = $paused_sub->user_id;
            
            // Avoid duplicates (same user with multiple paused subscriptions)
            if (isset($processed_users[$user_id])) {
                continue;
            }
            $processed_users[$user_id] = true;

            // Get membership type from product title
            $membership_type = '';
            if (stripos($paused_sub->program_name, 'monthly') !== false) {
                $membership_type = 'Monthly';
            } elseif (stripos($paused_sub->program_name, 'weekly') !== false) {
                $membership_type = 'Weekly';
            } elseif (stripos($paused_sub->program_name, 'term') !== false) {
                $membership_type = 'Full Term';
            }

            // Use the last transaction expires_at, or subscription created date as fallback
            $paused_date = $paused_sub->last_expires_at;
            if (empty($paused_date) || $paused_date === '0000-00-00 00:00:00') {
                $paused_date = $paused_sub->subscription_created;
            }
            
            $days_paused = '';
            if ($paused_date && $paused_date !== '0000-00-00 00:00:00') {
                $days_paused = floor((time() - strtotime($paused_date)) / (60 * 60 * 24)) . ' days';
            }

            $paused_members[] = [
                'user_id' => $user_id,
                'name' => $paused_sub->display_name,
                'email' => $paused_sub->user_email,
                'program' => $paused_sub->program_name,
                'group' => 'N/A',
                'membership_type' => $membership_type,
                'paused_date' => $paused_date ? date('d/m/Y', strtotime($paused_date)) : 'Unknown',
                'days_paused' => $days_paused,
                'subscription_id' => $paused_sub->subscription_id,
                'subscription_status' => $paused_sub->subscription_status
            ];
        }
    }

    return $paused_members;
}

// DEBUG: Helper function to identify WHY a user is marked as having active membership
function wcb_get_active_membership_reason($user_id) {
    global $wpdb;
    
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';
    
    // Use exact same queries as wcb_user_has_active_membership() but return details
    
    // Method 1: Check for active transactions (COUNT like the real function)
    $method1_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$txn_table} t
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    if ($method1_count > 0) {
        // Get the actual transactions for display
        $active_transactions = $wpdb->get_results($wpdb->prepare("
            SELECT t.*, p.post_title as product_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ", $user_id));
        
        $products = array_map(function($t) { 
            $exp = $t->expires_at ?: 'Never';
            return $t->product_name . " (exp: {$exp})"; 
        }, $active_transactions);
        return "M1({$method1_count}): " . implode(', ', $products);
    }
    
    // Method 2: Check for active subscriptions (COUNT like the real function)
    $method2_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$subscriptions_table} s
        WHERE s.user_id = %d
        AND s.status IN ('active', 'trialing')
        AND (s.expires_at IS NULL OR s.expires_at > NOW() OR s.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    if ($method2_count > 0) {
        $active_subscriptions = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, p.post_title as product_name
            FROM {$subscriptions_table} s
            JOIN {$wpdb->posts} p ON s.product_id = p.ID
            WHERE s.user_id = %d
            AND s.status IN ('active', 'trialing')
            AND (s.expires_at IS NULL OR s.expires_at > NOW() OR s.expires_at = '0000-00-00 00:00:00')
        ", $user_id));
        
        $subs = array_map(function($s) { return $s->product_name . " ({$s->status})"; }, $active_subscriptions);
        return "M2({$method2_count}): " . implode(', ', $subs);
    }
    
    // Method 3: Weekly subscriptions check
    $weekly_check = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, p.post_title as product_name
        FROM {$txn_table} t
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND p.post_title LIKE '%%weekly%%'
        ORDER BY t.created_at DESC
        LIMIT 5
    ", $user_id));
    
    if (!empty($weekly_check)) {
        foreach ($weekly_check as $weekly_txn) {
            $expires_in_future = ($weekly_txn->expires_at === null || 
                                 $weekly_txn->expires_at === '0000-00-00 00:00:00' || 
                                 strtotime($weekly_txn->expires_at) > time());
            
            if ($expires_in_future) {
                return "M3: Weekly - " . $weekly_txn->product_name . " (exp: " . ($weekly_txn->expires_at ?: 'Never') . ")";
            }
        }
    }
    
    // Method 4: Stripe transactions (COUNT like the real function)
    $method4_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$txn_table} t
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND t.gateway LIKE '%%stripe%%'
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    if ($method4_count > 0) {
        $stripe_transactions = $wpdb->get_results($wpdb->prepare("
            SELECT t.*, p.post_title as product_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND t.gateway LIKE '%%stripe%%'
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ", $user_id));
        
        $txns = array_map(function($t) { return $t->product_name; }, $stripe_transactions);
        return "M4({$method4_count}): " . implode(', ', $txns);
    }
    
    // Method 5: MemberPress access (skip for admins)
    $user = get_user_by('ID', $user_id);
    $is_admin = $user && in_array('administrator', (array) $user->roles);
    
    if ($is_admin) {
        // Check Method 2 without expires filter to see if there's an 'active' subscription
        $m2_no_expiry = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$subscriptions_table} s
            WHERE s.user_id = %d
            AND s.status IN ('active', 'trialing')
        ", $user_id));
        
        // Check all subscriptions for this user
        $all_subs = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, p.post_title as product_name
            FROM {$subscriptions_table} s
            LEFT JOIN {$wpdb->posts} p ON s.product_id = p.ID
            WHERE s.user_id = %d
        ", $user_id));
        
        $subs_info = '';
        if (!empty($all_subs)) {
            foreach ($all_subs as $sub) {
                $subs_info .= " [{$sub->product_name}: status={$sub->status}, exp={$sub->expires_at}]";
            }
        }
        
        return "ADMIN - M1={$method1_count}, M2={$method2_count}, M2_no_exp={$m2_no_expiry}, M4={$method4_count} | Subs:{$subs_info}";
    }
    
    if ($user && function_exists('mepr_user_has_access')) {
        $membership_products = get_posts([
            'post_type' => 'memberpressproduct',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);
        
        foreach ($membership_products as $product) {
            if (mepr_user_has_access($user_id, $product->ID)) {
                return "M5: MemberPress Access - " . $product->post_title;
            }
        }
    }
    
    return "Unknown - M1={$method1_count}, M2={$method2_count}, M4={$method4_count}, is_admin=" . ($is_admin ? 'YES' : 'NO');
}

// DEBUG: Helper function to show all expired transactions and why they were filtered
function get_non_renewed_debug_info($date_from, $date_to) {
    global $wpdb;

    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [];
    }

    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $all_group_membership_ids = [];
    foreach ($defined_groups as $group_name) {
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
    
    $competitive_team_id = 1932;
    $all_group_membership_ids[] = $competitive_team_id;

    if (empty($all_group_membership_ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($all_group_membership_ids), '%d'));

    $expired_transactions = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, u.display_name, u.user_email, p.post_title as program_name
        FROM {$txn_table} t
        JOIN {$wpdb->users} u ON t.user_id = u.ID
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND t.expires_at IS NOT NULL
        AND t.expires_at != '0000-00-00 00:00:00'
        AND DATE(t.expires_at) BETWEEN %s AND %s
        ORDER BY t.expires_at DESC
    ", array_merge($all_group_membership_ids, [$date_from, $date_to])));

    $debug_info = [];
    
    foreach ($expired_transactions as $expired_txn) {
        $user_id = $expired_txn->user_id;
        $has_active_membership = wcb_user_has_active_membership($user_id);
        
        $renewed_membership = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$txn_table} t
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND t.created_at > %s
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ", $user_id, $expired_txn->expires_at));

        $is_non_renewed = !$has_active_membership && $renewed_membership == 0;
        
        // Get the internal debug info from the function
        global $wcb_debug_active_method;
        $func_debug = isset($wcb_debug_active_method) ? json_encode($wcb_debug_active_method) : 'N/A';
        
        // Show the internal function debug
        $active_reason = "FUNC=" . ($has_active_membership ? 'TRUE' : 'FALSE') . " | INTERNAL: " . $func_debug;
        
        $debug_info[] = [
            'user_id' => $user_id,
            'name' => $expired_txn->display_name,
            'email' => $expired_txn->user_email,
            'program' => $expired_txn->program_name,
            'expires_at' => $expired_txn->expires_at,
            'has_active' => $has_active_membership,
            'active_reason' => $active_reason,
            'renewed_after' => $renewed_membership,
            'is_non_renewed' => $is_non_renewed
        ];
    }
    
    return $debug_info;
}

// NEW: Function to check if a user has any active membership (handles weekly subscriptions & Stripe)
function wcb_user_has_active_membership($user_id) {
    global $wpdb;
    
    // TEMP DEBUG: Store which method returns true
    global $wcb_debug_active_method;
    $wcb_debug_active_method = [];
    
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
    
    $wcb_debug_active_method['M1'] = $active_transactions;
    
    if ($active_transactions > 0) {
        $wcb_debug_active_method['returned_at'] = 'M1';
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
    
    $wcb_debug_active_method['M2'] = $active_subscriptions;
    
    if ($active_subscriptions > 0) {
        $wcb_debug_active_method['returned_at'] = 'M2';
        return true;
    }
    
    // Method 3: Enhanced check for weekly subscriptions
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
    
    $wcb_debug_active_method['M3_count'] = count($weekly_check);
    
    if (!empty($weekly_check)) {
        foreach ($weekly_check as $weekly_txn) {
            $expires_in_future = ($weekly_txn->expires_at === null || 
                                 $weekly_txn->expires_at === '0000-00-00 00:00:00' || 
                                 strtotime($weekly_txn->expires_at) > time());
            
            $wcb_debug_active_method['M3_check'] = "exp={$weekly_txn->expires_at}, future=" . ($expires_in_future ? 'YES' : 'NO');
            
            if ($expires_in_future) {
                $wcb_debug_active_method['returned_at'] = 'M3';
                return true;
            }
        }
    }
    
    // Method 4: Check for Stripe subscription transactions specifically
    // NOTE: Use %%stripe%% to escape % in wpdb->prepare()
    $stripe_transactions = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$txn_table} t
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND t.gateway LIKE '%%stripe%%'
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
    ", $user_id));
    
    $wcb_debug_active_method['M4'] = $stripe_transactions;
    
    if ($stripe_transactions > 0) {
        $wcb_debug_active_method['returned_at'] = 'M4';
        return true;
    }
    
    // Method 5: Check if user has MemberPress capabilities
    $user = get_user_by('ID', $user_id);
    $is_admin = $user && in_array('administrator', (array) $user->roles);
    
    $wcb_debug_active_method['is_admin'] = $is_admin ? 'YES' : 'NO';
    
    if ($user && !$is_admin && function_exists('mepr_user_has_access')) {
        $membership_products = get_posts([
            'post_type' => 'memberpressproduct',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);
        
        foreach ($membership_products as $product) {
            if (mepr_user_has_access($user_id, $product->ID)) {
                $wcb_debug_active_method['returned_at'] = 'M5:' . $product->post_title;
                return true;
            }
        }
    }
    
    $wcb_debug_active_method['returned_at'] = 'NONE-FALSE';
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
    
    // Get ALL date of birth data for active members (including empty/missing)
    $age_data = $wpdb->get_results("
        SELECT COALESCE(um.meta_value, '') as meta_value, u.ID as user_id
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_date_of_birth'
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
        
        $age = calculate_age_from_dob($data->meta_value);
        
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

// Helper function to calculate age from mepr_date_of_birth field
function calculate_age_from_dob($dob_value) {
    // Clean the input
    $dob_value = trim($dob_value);

    // Check if we have a valid date of birth
    if (empty($dob_value) || $dob_value === 'not specified' || $dob_value === 'Not specified') {
        return null;
    }

    // Try to parse the date of birth - handle multiple date formats
    $dob = null;

    // First try DD/MM/YYYY format (common MemberPress format)
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dob_value, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        $dob = date_create_from_format('d/m/Y', "$day/$month/$year");
    }

    // If that failed, try other common formats
    if (!$dob) {
        // Try standard date_create (handles YYYY-MM-DD, MM/DD/YYYY, etc.)
        $dob = date_create($dob_value);
    }

    // If still no valid date, return null
    if (!$dob) {
        return null;
    }

    // Calculate age
    $today = date_create('today');
    $age = date_diff($dob, $today)->y;

    // Validate the calculated age (reasonable range)
    if ($age >= 0 && $age < 120) {
        return $age;
    }

    // If age calculation seems invalid, return null
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
    
    // Method 1: Check for referrals stored as custom post type using referral_date field
    // This is the correct method for your referral form submissions
    $referrals_query = new WP_Query([
        'post_type' => 'referral',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'referral_date',
                'value' => [$date_from, $date_to],
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ]
        ],
        'fields' => 'ids' // Only get IDs for performance
    ]);
    
    $referrals_count = $referrals_query->found_posts;
    wp_reset_postdata();
    
    if ($referrals_count > 0) {
        return $referrals_count;
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

// NEW: Function to get members without ethnicity data
function get_members_without_ethnicity_data($active_member_ids) {
    global $wpdb;
    
    if (empty($active_member_ids)) {
        return [];
    }
    
    $member_ids = implode(',', $active_member_ids);
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    // Get members who have missing or empty ethnicity data
    $members_without_ethnicity = $wpdb->get_results("
        SELECT u.ID as user_id, u.display_name as name, u.user_email as email,
               COALESCE(um.meta_value, '') as ethnicity_value
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_ethnicity'
        WHERE u.ID IN ($member_ids)
        AND (um.meta_value IS NULL OR um.meta_value = '' OR um.meta_value = 'not specified' OR um.meta_value = 'Not specified')
        ORDER BY u.display_name ASC
    ");
    
    $members_data = [];
    
    foreach ($members_without_ethnicity as $member) {
        // Get member's program information
        $member_program_info = $wpdb->get_row($wpdb->prepare("
            SELECT p.post_title as program_name, t.created_at as member_since
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            ORDER BY t.created_at DESC
            LIMIT 1
        ", $member->user_id));
        
        if ($member_program_info) {
            $member_since = date('d/m/Y', strtotime($member_program_info->member_since));
            $days_active = floor((time() - strtotime($member_program_info->member_since)) / (60 * 60 * 24));
            
            // Get membership type from program name
            $membership_type = '';
            if (stripos($member_program_info->program_name, 'monthly') !== false) {
                $membership_type = 'Monthly';
            } elseif (stripos($member_program_info->program_name, 'weekly') !== false) {
                $membership_type = 'Weekly';
            } elseif (stripos($member_program_info->program_name, 'term') !== false) {
                $membership_type = 'Full Term';
            }
            
            $members_data[] = [
                'user_id' => $member->user_id,
                'name' => $member->name ?: 'No Name',
                'email' => $member->email,
                'program' => $member_program_info->program_name,
                'membership_type' => $membership_type,
                'member_since' => $member_since,
                'days_active' => $days_active . ' days'
            ];
        }
    }
    
    return $members_data;
}

// NEW: Function to get members without age data
function get_members_without_age_data($active_member_ids) {
    global $wpdb;

    if (empty($active_member_ids)) {
        return [];
    }

    $member_ids = implode(',', $active_member_ids);
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get ALL members first, then filter those with missing/invalid date of birth data
    $all_members = $wpdb->get_results("
        SELECT u.ID as user_id, u.display_name as name, u.user_email as email,
                COALESCE(um.meta_value, '') as dob_value
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_date_of_birth'
        WHERE u.ID IN ($member_ids)
        ORDER BY u.display_name ASC
    ");

    $members_data = [];

    foreach ($all_members as $member) {
        // Use the same logic as get_member_age_breakdown to determine if age data is missing/invalid
        $age = calculate_age_from_dob($member->dob_value);

        // Only include members who have missing or invalid age data
        if ($age === null) {
        // Get member's program information
        $member_program_info = $wpdb->get_row($wpdb->prepare("
            SELECT p.post_title as program_name, t.created_at as member_since
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            ORDER BY t.created_at DESC
            LIMIT 1
        ", $member->user_id));

        if ($member_program_info) {
            $member_since = date('d/m/Y', strtotime($member_program_info->member_since));
            $days_active = floor((time() - strtotime($member_program_info->member_since)) / (60 * 60 * 24));

            // Get membership type from program name
            $membership_type = '';
            if (stripos($member_program_info->program_name, 'monthly') !== false) {
                $membership_type = 'Monthly';
            } elseif (stripos($member_program_info->program_name, 'weekly') !== false) {
                $membership_type = 'Weekly';
            } elseif (stripos($member_program_info->program_name, 'term') !== false) {
                $membership_type = 'Full Term';
            }

            // Determine what age/DOB info to display
            $age_display = 'No Date of Birth';
            if (!empty($member->dob_value) && $member->dob_value !== 'not specified' && $member->dob_value !== 'Not specified') {
                $age_display = 'Invalid DOB: ' . $member->dob_value;
            }

            $members_data[] = [
                'user_id' => $member->user_id,
                'name' => $member->name ?: 'No Name',
                'email' => $member->email,
                'program' => $member_program_info->program_name,
                'membership_type' => $membership_type,
                'member_since' => $member_since,
                'days_active' => $days_active . ' days',
                'age_display' => $age_display
            ];
        }
        } // End if ($age === null)
    }

    return $members_data;
}

// NEW: Function to get detailed waitlist member data
function get_waitlist_members_detailed() {
    global $wpdb;
    
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    
    // Get detailed waitlist member information including date of birth
    $waitlist_members = $wpdb->get_results("
        SELECT DISTINCT u.ID as user_id, u.display_name as name, u.user_email as email,
               p.post_title as program_name, t.created_at as joined_date,
               t.id as transaction_id, s.id as subscription_id,
               COALESCE(um.meta_value, '') as dob_value
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        LEFT JOIN {$wpdb->prefix}mepr_subscriptions s ON t.subscription_id = s.id
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mepr_date_of_birth'
        WHERE t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND p.post_type = 'memberpressproduct'
        AND p.post_title LIKE '%waitlist%'
        AND u.user_login != 'bwgdev'
        ORDER BY t.created_at ASC
    ");
    
    $members_data = [];
    
    foreach ($waitlist_members as $member) {
        $joined_date = date('d/m/Y', strtotime($member->joined_date));
        $days_waiting = floor((time() - strtotime($member->joined_date)) / (60 * 60 * 24));
        
        // Get membership type from program name
        $membership_type = '';
        if (stripos($member->program_name, 'monthly') !== false) {
            $membership_type = 'Monthly';
        } elseif (stripos($member->program_name, 'weekly') !== false) {
            $membership_type = 'Weekly';
        } elseif (stripos($member->program_name, 'term') !== false) {
            $membership_type = 'Full Term';
        }

        // Calculate age from date of birth
        $age = calculate_age_from_dob($member->dob_value);
        $age_display = ($age !== null) ? $age . ' years old' : 'Age not specified';
        
        $members_data[] = [
            'user_id' => $member->user_id,
            'name' => $member->name ?: 'No Name',
            'email' => $member->email,
            'program' => $member->program_name,
            'membership_type' => $membership_type,
            'joined_date' => $joined_date,
            'days_waiting' => $days_waiting . ' days',
            'days_waiting_number' => $days_waiting, // For calculations
            'subscription_id' => $member->subscription_id,
            'transaction_id' => $member->transaction_id,
            'age_display' => $age_display
        ];
    }
    
    return $members_data;
}

// NEW: Helper function to calculate average wait time
function calculate_average_wait_time($waitlist_members) {
    if (empty($waitlist_members)) {
        return 0;
    }
    
    $total_days = 0;
    $count = 0;
    
    foreach ($waitlist_members as $member) {
        if (isset($member['days_waiting_number'])) {
            $total_days += $member['days_waiting_number'];
            $count++;
        }
    }
    
    return $count > 0 ? round($total_days / $count, 1) : 0;
}

// NEW: Helper function to get longest wait time
function get_longest_wait_time($waitlist_members) {
    if (empty($waitlist_members)) {
        return 0;
    }
    
    $longest_wait = 0;
    
    foreach ($waitlist_members as $member) {
        if (isset($member['days_waiting_number']) && $member['days_waiting_number'] > $longest_wait) {
            $longest_wait = $member['days_waiting_number'];
        }
    }
    
    return $longest_wait;
}

// NEW: Function to get schools data
function get_schools_data() {
    $possible_school_types = ['schools', 'school', 'wcb_schools', 'wcb_school'];
    $schools_data = [];

    foreach ($possible_school_types as $post_type_name) {
        $schools = get_posts([
            'post_type' => $post_type_name,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        if (!empty($schools)) {
            foreach ($schools as $school) {
                $student_count = wcb_get_school_student_count($school->ID);
                if ($student_count > 0) { // Only include schools with students
                    $schools_data[] = [
                        'id' => $school->ID,
                        'name' => $school->post_title,
                        'student_count' => $student_count
                    ];
                }
            }
            break; // Stop after finding schools
        }
    }

    return $schools_data;
}

// Helper function to convert UTC datetime to WordPress local timezone
function wcb_utc_to_local_date($utc_datetime) {
    if (empty($utc_datetime) || $utc_datetime === '0000-00-00 00:00:00') {
        return null;
    }
    
    try {
        // Create DateTime object in UTC
        $utc_tz = new DateTimeZone('UTC');
        $local_tz = wp_timezone();
        
        $date = new DateTime($utc_datetime, $utc_tz);
        $date->setTimezone($local_tz);
        
        return $date->format('Y-m-d');
    } catch (Exception $e) {
        // Fallback to simple date extraction if timezone conversion fails
        return date('Y-m-d', strtotime($utc_datetime));
    }
}

// DEBUG: Get detailed active members for debug table
// FIXED: Now gets the LATEST transaction with MAX expiry date for each user
// ENHANCED: Now includes subscription status (active/paused/cancelled)
function wcb_get_debug_active_members($date_from, $date_to) {
    global $wpdb;
    
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return [];
    }
    
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");
    
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];
    
    $all_membership_ids = [];
    $membership_to_group = [];
    
    foreach ($defined_groups as $group_name) {
        $group = null;
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }
        
        if (!$group) continue;
        
        $group_memberships = wcb_get_group_memberships($group->ID);
        if (!empty($group_memberships)) {
            foreach ($group_memberships as $m) {
                $all_membership_ids[] = $m->ID;
                $membership_to_group[$m->ID] = $group_name;
            }
        }
    }
    
    $competitive_team_id = 1932;
    $all_membership_ids[] = $competitive_team_id;
    $membership_to_group[$competitive_team_id] = 'Competitive Team';
    
    if (empty($all_membership_ids)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($all_membership_ids), '%d'));
    
    // STEP 1: Get all active user IDs first (same logic as main count)
    $active_users_query = $wpdb->prepare("
        SELECT DISTINCT u.ID as user_id
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        LEFT JOIN {$wpdb->usermeta} um_reg ON u.ID = um_reg.user_id 
            AND um_reg.meta_key IN ('mepr_registration_date', 'mepr_date_registered')
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND (
            CASE 
                WHEN um_reg.meta_value IS NOT NULL 
                    AND um_reg.meta_value != '0000-00-00' 
                    AND um_reg.meta_value != '0000-00-00 00:00:00'
                    AND um_reg.meta_value != '1970-01-01'
                    AND um_reg.meta_value != '1970-01-01 00:00:00'
                THEN STR_TO_DATE(um_reg.meta_value, '%%d/%%m/%%Y')
                ELSE DATE(t.created_at)
            END
        ) <= %s
        AND (
            t.expires_at IS NULL 
            OR t.expires_at = '0000-00-00 00:00:00'
            OR DATE(t.expires_at) >= %s
        )
        AND u.user_login != 'bwgdev'
    ", array_merge($all_membership_ids, [$date_to, $date_from]));
    
    $active_user_ids = $wpdb->get_col($active_users_query);
    
    if (empty($active_user_ids)) {
        return [];
    }
    
    $members = [];
    
    // STEP 2: For each active user, get their details with LATEST transaction info
    foreach ($active_user_ids as $user_id) {
        $user = get_userdata($user_id);
        if (!$user) continue;
        
        // Get registration date
        $registration_date = get_user_meta($user_id, 'mepr_registration_date', true);
        $reg_date = null;
        if (!empty($registration_date) && 
            $registration_date !== '0000-00-00' && 
            $registration_date !== '1970-01-01') {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $registration_date, $matches)) {
                $reg_date = $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            } else {
                $reg_date = $registration_date;
            }
        }
        
        // Get the FIRST transaction date (earliest)
        $first_txn = $wpdb->get_row($wpdb->prepare("
            SELECT created_at
            FROM {$txn_table}
            WHERE user_id = %d
            AND product_id IN ({$placeholders})
            AND status IN ('confirmed', 'complete')
            ORDER BY created_at ASC
            LIMIT 1
        ", array_merge([$user_id], $all_membership_ids)));
        
        // Get the LATEST transaction with MAX expiry date
        $latest_txn = $wpdb->get_row($wpdb->prepare("
            SELECT t.*, p.post_title as membership_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.product_id IN ({$placeholders})
            AND t.status IN ('confirmed', 'complete')
            ORDER BY 
                CASE WHEN t.expires_at IS NULL OR t.expires_at = '0000-00-00 00:00:00' THEN '9999-12-31' ELSE t.expires_at END DESC,
                t.created_at DESC
            LIMIT 1
        ", array_merge([$user_id], $all_membership_ids)));
        
        if (!$latest_txn) continue;
        
        // Get subscription status for this user (check for paused/cancelled)
        $subscription_status = $wpdb->get_row($wpdb->prepare("
            SELECT s.status, s.id as subscription_id
            FROM {$subscriptions_table} s
            WHERE s.user_id = %d
            AND s.product_id IN ({$placeholders})
            ORDER BY s.id DESC
            LIMIT 1
        ", array_merge([$user_id], $all_membership_ids)));
        
        // Determine member status
        $member_status = 'active';
        $status_label = 'Active';
        if ($subscription_status) {
            if ($subscription_status->status === 'suspended') {
                $member_status = 'paused';
                $status_label = 'Paused';
            } elseif ($subscription_status->status === 'cancelled') {
                $member_status = 'cancelled';
                $status_label = 'Cancelled';
            }
        }
        
        // Check if member's transaction is expired (overrides subscription status for display)
        $is_expired = false;
        if ($latest_txn->expires_at && $latest_txn->expires_at !== '0000-00-00 00:00:00') {
            $expires_timestamp = strtotime($latest_txn->expires_at);
            if ($expires_timestamp && $expires_timestamp < time()) {
                $is_expired = true;
                if ($member_status === 'active') {
                    $member_status = 'expired';
                    $status_label = 'Expired';
                }
            }
        }
        
        $gateway_label = 'Unknown';
        if ($latest_txn->gateway === 'manual') {
            $gateway_label = 'Manual';
        } elseif ($latest_txn->gateway === 'stripe' || strpos($latest_txn->gateway, 'stripe') !== false) {
            $gateway_label = 'Stripe';
        } elseif (!empty($latest_txn->gateway)) {
            $gateway_label = ucfirst($latest_txn->gateway);
        }
        
        $group_name = isset($membership_to_group[$latest_txn->product_id]) ? $membership_to_group[$latest_txn->product_id] : 'Unknown';
        
        // Convert expires_at from UTC to local timezone
        $expires_display = 'Never';
        if ($latest_txn->expires_at && $latest_txn->expires_at !== '0000-00-00 00:00:00') {
            $expires_display = wcb_utc_to_local_date($latest_txn->expires_at);
        }
        
        // Convert first_txn_date from UTC to local timezone
        $first_txn_local = $first_txn ? wcb_utc_to_local_date($first_txn->created_at) : null;
        
        $members[] = [
            'user_id' => $user_id,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'membership' => $latest_txn->membership_name,
            'group' => $group_name,
            'registration_date' => $reg_date,
            'first_txn_date' => $first_txn_local,
            'expires_at' => $expires_display,
            'gateway' => $gateway_label,
            'txn_status' => $latest_txn->status,
            'member_status' => $member_status,
            'status_label' => $status_label,
            'is_expired' => $is_expired
        ];
    }
    
    // Sort by name
    usort($members, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    return $members;
}

// DEBUG: Get members who expired during the selected period
// ENHANCED: Now shows detailed status including other active memberships, renewal reasons, and paused/cancelled status
function wcb_get_debug_expired_members($date_from, $date_to) {
    global $wpdb;
    
    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;
    
    if (!$table_exists) {
        return [];
    }
    
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");
    
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];
    
    $all_membership_ids = [];
    $membership_to_group = [];
    
    foreach ($defined_groups as $group_name) {
        $group = null;
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group = $g;
                break;
            }
        }
        
        if (!$group) continue;
        
        $group_memberships = wcb_get_group_memberships($group->ID);
        if (!empty($group_memberships)) {
            foreach ($group_memberships as $m) {
                $all_membership_ids[] = $m->ID;
                $membership_to_group[$m->ID] = $group_name;
            }
        }
    }
    
    $competitive_team_id = 1932;
    $all_membership_ids[] = $competitive_team_id;
    $membership_to_group[$competitive_team_id] = 'Competitive Team';
    
    if (empty($all_membership_ids)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($all_membership_ids), '%d'));
    
    // Get WordPress timezone offset for SQL conversion
    $wp_tz = wp_timezone();
    $now = new DateTime('now', $wp_tz);
    $tz_offset_seconds = $wp_tz->getOffset($now);
    $tz_offset_hours = $tz_offset_seconds / 3600;
    $tz_offset_string = sprintf('%+03d:00', $tz_offset_hours);
    
    // STEP 1: Get all users who have ANY transaction that expired during the period
    $users_with_expirations = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT user_id
        FROM {$txn_table}
        WHERE product_id IN ({$placeholders})
        AND status IN ('confirmed', 'complete')
        AND expires_at IS NOT NULL 
        AND expires_at != '0000-00-00 00:00:00'
        AND DATE(CONVERT_TZ(expires_at, '+00:00', %s)) >= %s
        AND DATE(CONVERT_TZ(expires_at, '+00:00', %s)) <= %s
    ", array_merge($all_membership_ids, [$tz_offset_string, $date_from, $tz_offset_string, $date_to])));
    
    if (empty($users_with_expirations)) {
        return [];
    }
    
    $members = [];
    
    // STEP 2: For each user, get their transaction details with ENHANCED status info
    foreach ($users_with_expirations as $user_id) {
        $user = get_userdata($user_id);
        if (!$user || $user->user_login === 'bwgdev') continue;
        
        // Get the transaction that expired during the period
        $expired_txn = $wpdb->get_row($wpdb->prepare("
            SELECT t.*, p.post_title as membership_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.product_id IN ({$placeholders})
            AND t.status IN ('confirmed', 'complete')
            AND t.expires_at IS NOT NULL 
            AND t.expires_at != '0000-00-00 00:00:00'
            AND DATE(CONVERT_TZ(t.expires_at, '+00:00', %s)) >= %s
            AND DATE(CONVERT_TZ(t.expires_at, '+00:00', %s)) <= %s
            ORDER BY t.expires_at DESC
            LIMIT 1
        ", array_merge([$user_id], $all_membership_ids, [$tz_offset_string, $date_from, $tz_offset_string, $date_to])));
        
        if (!$expired_txn) continue;
        
        // Get registration date
        $registration_date = get_user_meta($user_id, 'mepr_registration_date', true);
        $reg_date = null;
        if (!empty($registration_date) && 
            $registration_date !== '0000-00-00' && 
            $registration_date !== '1970-01-01') {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $registration_date, $matches)) {
                $reg_date = $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            } else {
                $reg_date = $registration_date;
            }
        }
        
        // ========================================
        // ENHANCED: Get subscription status (paused/cancelled)
        // ========================================
        $subscription_info = $wpdb->get_row($wpdb->prepare("
            SELECT s.status as sub_status, s.id as subscription_id
            FROM {$subscriptions_table} s
            WHERE s.user_id = %d
            AND s.product_id IN ({$placeholders})
            ORDER BY s.id DESC
            LIMIT 1
        ", array_merge([$user_id], $all_membership_ids)));
        
        $subscription_status = 'none';
        $subscription_status_label = 'No Subscription';
        if ($subscription_info) {
            $subscription_status = $subscription_info->sub_status;
            if ($subscription_status === 'suspended') {
                $subscription_status_label = 'Paused';
            } elseif ($subscription_status === 'cancelled') {
                $subscription_status_label = 'Cancelled';
            } elseif ($subscription_status === 'active') {
                $subscription_status_label = 'Active';
            } else {
                $subscription_status_label = ucfirst($subscription_status);
            }
        }
        
        // ========================================
        // ENHANCED: Detailed renewal/status checks
        // ========================================
        
        // Check 1: Renewed within SAME program group (defined groups only)
        $renewed_in_program = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$txn_table}
            WHERE user_id = %d
            AND product_id IN ({$placeholders})
            AND status IN ('confirmed', 'complete')
            AND (
                (expires_at IS NOT NULL AND expires_at != '0000-00-00 00:00:00' AND expires_at > %s)
                OR (expires_at IS NULL OR expires_at = '0000-00-00 00:00:00')
            )
        ", array_merge([$user_id], $all_membership_ids, [$expired_txn->expires_at])));
        
        // Check 2: Has ANY other active membership (outside defined groups)
        $other_active_memberships = $wpdb->get_results($wpdb->prepare("
            SELECT t.*, p.post_title as membership_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.product_id NOT IN ({$placeholders})
            AND t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        ", array_merge([$user_id], $all_membership_ids)));
        
        // Check 3: Has active subscription (Stripe recurring)
        $active_subscriptions = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, p.post_title as product_name
            FROM {$subscriptions_table} s
            JOIN {$wpdb->posts} p ON s.product_id = p.ID
            WHERE s.user_id = %d
            AND s.status IN ('active', 'trialing')
        ", $user_id));
        
        // Check 4: Recent weekly transactions (within 14 days)
        $recent_weekly = $wpdb->get_results($wpdb->prepare("
            SELECT t.*, p.post_title as product_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.status IN ('confirmed', 'complete')
            AND p.post_title LIKE '%%weekly%%'
            AND t.created_at > DATE_SUB(NOW(), INTERVAL 14 DAY)
        ", $user_id));
        
        // Build status reason
        $status_reasons = [];
        $has_renewed = false;
        
        if ($renewed_in_program > 0) {
            $has_renewed = true;
            $status_reasons[] = 'Renewed in program';
        }
        
        if (!empty($other_active_memberships)) {
            $other_names = array_map(function($m) { return $m->membership_name; }, $other_active_memberships);
            $status_reasons[] = 'Other active: ' . implode(', ', array_unique($other_names));
        }
        
        if (!empty($active_subscriptions)) {
            $sub_names = array_map(function($s) { return $s->product_name . ' (' . $s->status . ')'; }, $active_subscriptions);
            $status_reasons[] = 'Active subs: ' . implode(', ', array_unique($sub_names));
        }
        
        if (!empty($recent_weekly)) {
            $weekly_names = array_map(function($w) { return $w->product_name; }, $recent_weekly);
            $status_reasons[] = 'Recent weekly: ' . implode(', ', array_unique($weekly_names));
        }
        
        // Determine overall status
        $overall_status = 'not_renewed';
        $status_detail = 'No active membership found';
        
        if ($renewed_in_program > 0) {
            $overall_status = 'renewed_program';
            $status_detail = 'Renewed within program';
        } elseif (!empty($other_active_memberships) || !empty($active_subscriptions) || !empty($recent_weekly)) {
            $overall_status = 'active_other';
            $status_detail = implode(' | ', $status_reasons);
        }
        
        // Get the LATEST transaction in defined groups
        $latest_txn = $wpdb->get_row($wpdb->prepare("
            SELECT t.*, p.post_title as membership_name
            FROM {$txn_table} t
            JOIN {$wpdb->posts} p ON t.product_id = p.ID
            WHERE t.user_id = %d
            AND t.product_id IN ({$placeholders})
            AND t.status IN ('confirmed', 'complete')
            ORDER BY 
                CASE WHEN t.expires_at IS NULL OR t.expires_at = '0000-00-00 00:00:00' THEN '9999-12-31' ELSE t.expires_at END DESC,
                t.created_at DESC
            LIMIT 1
        ", array_merge([$user_id], $all_membership_ids)));
        
        $gateway_label = 'Unknown';
        if ($expired_txn->gateway === 'manual') {
            $gateway_label = 'Manual';
        } elseif ($expired_txn->gateway === 'stripe' || strpos($expired_txn->gateway, 'stripe') !== false) {
            $gateway_label = 'Stripe';
        } elseif (!empty($expired_txn->gateway)) {
            $gateway_label = ucfirst($expired_txn->gateway);
        }
        
        $group_name = isset($membership_to_group[$expired_txn->product_id]) ? $membership_to_group[$expired_txn->product_id] : 'Unknown';
        
        // Determine current expires display
        $current_expires = 'Expired';
        if ($latest_txn && $renewed_in_program > 0) {
            if (!$latest_txn->expires_at || $latest_txn->expires_at === '0000-00-00 00:00:00') {
                $current_expires = 'Never (renewed)';
            } else {
                $current_expires = wcb_utc_to_local_date($latest_txn->expires_at);
            }
        }
        
        $expired_txn_local = wcb_utc_to_local_date($expired_txn->expires_at);
        
        $members[] = [
            'user_id' => $user_id,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'membership' => $expired_txn->membership_name,
            'group' => $group_name,
            'registration_date' => $reg_date,
            'expired_txn_date' => $expired_txn_local,
            'current_expires' => $current_expires,
            'gateway' => $gateway_label,
            'txn_status' => $expired_txn->status,
            'has_renewed' => $renewed_in_program > 0 ? 'Yes' : 'No',
            'overall_status' => $overall_status,
            'status_detail' => $status_detail,
            'has_other_active' => !empty($other_active_memberships) || !empty($active_subscriptions) || !empty($recent_weekly),
            'subscription_status' => $subscription_status,
            'subscription_status_label' => $subscription_status_label,
            'is_paused' => $subscription_status === 'suspended',
            'is_cancelled' => $subscription_status === 'cancelled'
        ];
    }
    
    // Sort by expired date (most recent first), then by name
    usort($members, function($a, $b) {
        $date_cmp = strcmp($b['expired_txn_date'], $a['expired_txn_date']);
        if ($date_cmp !== 0) return $date_cmp;
        return strcasecmp($a['name'], $b['name']);
    });
    
    return $members;
}

// Helper function to get total students breakdown (active + non-renewed + paused with payment methods)
// IMPROVED: Now properly checks Stripe subscriptions to identify currently active members
function get_total_students_breakdown($date_from, $date_to) {
    global $wpdb;

    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';

    // Get all program group membership IDs
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private')");
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $program_membership_ids = [];
    foreach ($defined_groups as $group_name) {
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group_memberships = wcb_get_group_memberships($g->ID);
                if (!empty($group_memberships)) {
                    foreach ($group_memberships as $m) {
                        $program_membership_ids[] = $m->ID;
                    }
                }
                break;
            }
        }
    }

    // NOTE: Active members only counts PAID program groups
    // Competitive Team and WCB Mentoring are shown separately in "Other Active Memberships"

    if (empty($program_membership_ids)) {
        return [
            'total' => 0,
            'active_count' => 0,
            'non_renewed_count' => 0,
            'paused_count' => 0,
            'manual_count' => 0,
            'stripe_count' => 0,
            'stripe_active_count' => 0,
            'manual_active_count' => 0,
            'paused_members' => [],
            'currently_active_members' => []
        ];
    }

    $placeholders = implode(',', array_fill(0, count($program_membership_ids), '%d'));

    // ========================================
    // STEP 1: Get CURRENTLY ACTIVE members (PAID PROGRAM GROUPS ONLY)
    // A member is currently active if they have a valid transaction with:
    // - expires_at > NOW or NULL/0000-00-00 (no expiration)
    // Payment type is determined by the transaction's gateway field:
    // - Gateway contains 'stripe' = Stripe payment
    // - Gateway is 'manual' or empty = Manual payment
    // ========================================

    // Get all active members with their most recent valid transaction
    $all_active_members = $wpdb->get_results($wpdb->prepare("
        SELECT t.user_id, u.display_name, u.user_email, t.expires_at,
               p.post_title as membership_name, t.gateway,
               t.id as transaction_id
        FROM {$txn_table} t
        JOIN {$wpdb->users} u ON t.user_id = u.ID
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.product_id IN ({$placeholders})
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY t.user_id, t.id DESC
    ", $program_membership_ids), ARRAY_A);

    // DEBUG: Log query results
    wcb_debug_log("=== ACTIVE MEMBERS DEBUG ===");
    wcb_debug_log("Program Membership IDs: " . implode(', ', $program_membership_ids));
    wcb_debug_log("All active transactions found: " . count($all_active_members));

    // Group by user and determine payment type from gateway
    $currently_active_members = [];
    $seen_users = [];

    foreach ($all_active_members as $member) {
        $user_id = $member['user_id'];

        // Skip if we've already processed this user (we only want one entry per user)
        if (isset($seen_users[$user_id])) {
            continue;
        }
        $seen_users[$user_id] = true;

        // Determine payment source based on gateway field
        $gateway = strtolower($member['gateway'] ?? '');

        // If gateway is 'manual' or empty = Manual payment
        // Otherwise it's an online payment (Stripe) - gateway could be ID like 'sz7gj0-4lm' or 'MeproStripeGateway'
        if (empty($gateway) || $gateway === 'manual' || $gateway === 'free') {
            $member['source'] = 'manual';
        } else {
            // Any other gateway value is considered Stripe/online payment
            $member['source'] = 'stripe';
        }

        wcb_debug_log("  User: {$member['display_name']}, Gateway: '{$member['gateway']}', Source: {$member['source']}");

        $currently_active_members[] = $member;
    }

    // ========================================
    // STEP 2: Get PAUSED members (suspended subscriptions)
    // We need this first to exclude them from active counts
    // ========================================
    $paused_members = get_paused_members_from_defined_groups($date_from, $date_to);
    $paused_count = count($paused_members);
    $paused_user_ids = array_column($paused_members, 'user_id');

    // Remove paused members from active members list
    $currently_active_members = array_filter($currently_active_members, function($member) use ($paused_user_ids) {
        return !in_array($member['user_id'], $paused_user_ids);
    });
    $currently_active_members = array_values($currently_active_members); // Re-index array

    $currently_active_user_ids = array_column($currently_active_members, 'user_id');

    // Count by source (after excluding paused members)
    $stripe_active_count = 0;
    $manual_active_count = 0;
    foreach ($currently_active_members as $member) {
        if ($member['source'] === 'stripe') {
            $stripe_active_count++;
        } else {
            $manual_active_count++;
        }
    }
    $active_count = count($currently_active_members);

    wcb_debug_log("Active count: {$active_count}, Stripe: {$stripe_active_count}, Manual: {$manual_active_count}");

    // ========================================
    // STEP 4: Get NON-RENEWED members
    // These are members who were active during the period but are NOT currently active and NOT paused
    // ========================================
    $all_active_during_period = get_active_members_from_defined_groups($date_from, $date_to);
    $all_during_period_count = $all_active_during_period['total_count'];

    // Get all member IDs who were active during period
    $all_during_period_ids = get_active_member_ids_consistent_with_total($date_from, $date_to);

    // Non-renewed = was active during period BUT NOT currently active AND NOT paused
    $non_renewed_user_ids = array_diff($all_during_period_ids, $currently_active_user_ids, $paused_user_ids);
    $non_renewed_count = count($non_renewed_user_ids);

    // Get non-renewed member details for display
    $non_renewed_members = get_non_renewed_members_from_defined_groups($date_from, $date_to);

    // ========================================
    // STEP 5: Calculate totals
    // ========================================
    // Total = everyone who was active during period (historical view)
    $total_count = $all_during_period_count;

    // For payment breakdown, use the currently active members
    $manual_count = 0;
    $stripe_count = 0;

    foreach ($currently_active_members as $member) {
        if ($member['source'] === 'stripe') {
            $stripe_count++;
        } else {
            $manual_count++;
        }
    }

    return [
        'total' => $total_count,
        'active_count' => $active_count,
        'non_renewed_count' => $non_renewed_count,
        'paused_count' => $paused_count,
        'manual_count' => $manual_count,
        'stripe_count' => $stripe_count,
        'stripe_active_count' => $stripe_active_count,
        'manual_active_count' => $manual_active_count,
        'paused_members' => $paused_members,
        'currently_active_members' => $currently_active_members
    ];
}

/**
 * Get members with "Other Active Memberships" (Competitive Team & WCB Mentoring)
 * Shows detailed breakdown of members in these special programs
 * and whether they also have active/expired program group memberships
 */
function get_other_active_memberships($date_from, $date_to) {
    global $wpdb;

    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        return [
            'competitive_team' => [],
            'wcb_mentoring' => [],
            'competitive_count' => 0,
            'mentoring_count' => 0,
            'overlap_with_programs' => 0,
            'only_other_memberships' => 0
        ];
    }

    // Membership IDs
    $competitive_team_id = 1932;
    $wcb_mentoring_id = 1738;

    // Get all program group membership IDs (to check for overlap)
    $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private')");
    $defined_groups = [
        'Mini Cadet Boys (9-11 Years) Group 1',
        'Cadet Boys Group 1',
        'Cadet Boys Group 2',
        'Youth Boys Group 1',
        'Youth Boys Group 2',
        'Mini Cadets Girls Group 1',
        'Youth Girls Group 1'
    ];

    $program_membership_ids = [];
    foreach ($defined_groups as $group_name) {
        foreach ($groups as $g) {
            if (strcasecmp($g->post_title, $group_name) === 0) {
                $group_memberships = wcb_get_group_memberships($g->ID);
                if (!empty($group_memberships)) {
                    foreach ($group_memberships as $m) {
                        $program_membership_ids[] = $m->ID;
                    }
                }
                break;
            }
        }
    }

    // Get Competitive Team members with details
    $competitive_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID as user_id, u.display_name, u.user_email,
               t.expires_at, t.gateway, t.created_at
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name ASC
    ", $competitive_team_id), ARRAY_A);

    // Get WCB Mentoring members with details
    $mentoring_members = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID as user_id, u.display_name, u.user_email,
               t.expires_at, t.gateway, t.created_at
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name ASC
    ", $wcb_mentoring_id), ARRAY_A);

    // Check program membership status for each member
    $program_placeholders = !empty($program_membership_ids) ? implode(',', array_fill(0, count($program_membership_ids), '%d')) : '0';

    // Track unique users across both memberships
    $all_other_member_ids = [];
    $members_with_active_program = [];
    $members_with_expired_program = [];

    // Process Competitive Team members
    foreach ($competitive_members as &$member) {
        $user_id = $member['user_id'];
        $all_other_member_ids[$user_id] = true;

        // Format expiration
        if (empty($member['expires_at']) || $member['expires_at'] === '0000-00-00 00:00:00') {
            $member['expires_display'] = 'Never';
        } else {
            $member['expires_display'] = date('d M Y', strtotime($member['expires_at']));
        }

        // Format gateway
        $gateway = strtolower($member['gateway']);
        $member['gateway_display'] = ($gateway === 'manual' || empty($gateway)) ? 'Manual' : 'Stripe';

        // Check if they have active program membership
        if (!empty($program_membership_ids)) {
            $has_active_program = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$txn_table}
                 WHERE user_id = %d
                 AND product_id IN ({$program_placeholders})
                 AND status IN ('confirmed', 'complete')
                 AND (expires_at IS NULL OR expires_at > NOW() OR expires_at = '0000-00-00 00:00:00')",
                array_merge([$user_id], $program_membership_ids)
            ));

            $has_expired_program = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$txn_table}
                 WHERE user_id = %d
                 AND product_id IN ({$program_placeholders})
                 AND status IN ('confirmed', 'complete')
                 AND expires_at IS NOT NULL
                 AND expires_at != '0000-00-00 00:00:00'
                 AND expires_at <= NOW()",
                array_merge([$user_id], $program_membership_ids)
            ));

            $member['has_active_program'] = $has_active_program > 0;
            $member['has_expired_program'] = $has_expired_program > 0 && !$member['has_active_program'];

            if ($member['has_active_program']) {
                $members_with_active_program[$user_id] = true;
            } elseif ($member['has_expired_program']) {
                $members_with_expired_program[$user_id] = true;
            }

            // Determine program status label
            if ($member['has_active_program']) {
                $member['program_status'] = 'Active in Program';
                $member['program_status_class'] = 'active';
            } elseif ($member['has_expired_program']) {
                $member['program_status'] = 'Program Expired';
                $member['program_status_class'] = 'expired';
            } else {
                $member['program_status'] = 'No Program Membership';
                $member['program_status_class'] = 'none';
            }
        } else {
            $member['has_active_program'] = false;
            $member['has_expired_program'] = false;
            $member['program_status'] = 'No Program Membership';
            $member['program_status_class'] = 'none';
        }
    }

    // Process Mentoring members
    foreach ($mentoring_members as &$member) {
        $user_id = $member['user_id'];
        $all_other_member_ids[$user_id] = true;

        // Format expiration
        if (empty($member['expires_at']) || $member['expires_at'] === '0000-00-00 00:00:00') {
            $member['expires_display'] = 'Never';
        } else {
            $member['expires_display'] = date('d M Y', strtotime($member['expires_at']));
        }

        // Format gateway
        $gateway = strtolower($member['gateway']);
        $member['gateway_display'] = ($gateway === 'manual' || empty($gateway)) ? 'Manual' : 'Stripe';

        // Check if they have active program membership
        if (!empty($program_membership_ids)) {
            $has_active_program = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$txn_table}
                 WHERE user_id = %d
                 AND product_id IN ({$program_placeholders})
                 AND status IN ('confirmed', 'complete')
                 AND (expires_at IS NULL OR expires_at > NOW() OR expires_at = '0000-00-00 00:00:00')",
                array_merge([$user_id], $program_membership_ids)
            ));

            $has_expired_program = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$txn_table}
                 WHERE user_id = %d
                 AND product_id IN ({$program_placeholders})
                 AND status IN ('confirmed', 'complete')
                 AND expires_at IS NOT NULL
                 AND expires_at != '0000-00-00 00:00:00'
                 AND expires_at <= NOW()",
                array_merge([$user_id], $program_membership_ids)
            ));

            $member['has_active_program'] = $has_active_program > 0;
            $member['has_expired_program'] = $has_expired_program > 0 && !$member['has_active_program'];

            if ($member['has_active_program']) {
                $members_with_active_program[$user_id] = true;
            } elseif ($member['has_expired_program']) {
                $members_with_expired_program[$user_id] = true;
            }

            // Determine program status label
            if ($member['has_active_program']) {
                $member['program_status'] = 'Active in Program';
                $member['program_status_class'] = 'active';
            } elseif ($member['has_expired_program']) {
                $member['program_status'] = 'Program Expired';
                $member['program_status_class'] = 'expired';
            } else {
                $member['program_status'] = 'No Program Membership';
                $member['program_status_class'] = 'none';
            }
        } else {
            $member['has_active_program'] = false;
            $member['has_expired_program'] = false;
            $member['program_status'] = 'No Program Membership';
            $member['program_status_class'] = 'none';
        }
    }

    // Calculate totals
    $total_unique = count($all_other_member_ids);
    $overlap_count = count($members_with_active_program);
    $expired_program_count = count($members_with_expired_program);
    $only_other = $total_unique - $overlap_count - $expired_program_count;

    return [
        'competitive_team' => $competitive_members,
        'wcb_mentoring' => $mentoring_members,
        'competitive_count' => count($competitive_members),
        'mentoring_count' => count($mentoring_members),
        'total_unique' => $total_unique,
        'overlap_with_active_programs' => $overlap_count,
        'with_expired_programs' => $expired_program_count,
        'only_other_memberships' => $only_other
    ];
}

// Register the shortcode
add_shortcode('dashboard_stats', 'dashboard_stats_shortcode');

// Legacy support
add_shortcode('staff_dashboard_stats', 'dashboard_stats_shortcode');
