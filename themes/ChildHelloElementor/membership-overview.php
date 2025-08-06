<?php
/**
 * Comprehensive Membership Overview Dashboard
 * Professional summary of groups, members, and transactions
 */

// Load WordPress
require_once('../../../wp-load.php');

// Include required files
require_once('includes/shortcodes/student-table.php');
require_once('includes/dashboard/dashboard-stats.php');

// Temporarily allow access for testing (restore admin check in production)
// if (!current_user_can('administrator')) {
//     wp_die('Access denied. Admin only.');
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Overview - West City Boxing</title>
    
    <!-- Modern CSS Framework -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
            color: #212529;
        }
        
        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .header h1 {
            color: #212529;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header .subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .stats-column {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-column.payment-stats {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .stats-section h4 {
            color: #495057;
            font-size: 1.2rem;
            margin-bottom: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            text-align: center;
        }

        .payment-stats h4 {
            border-bottom-color: #6c757d;
        }

        .stats-grid {
            display: grid;
            gap: 15px;
        }

        .stats-grid.member-top {
            grid-template-columns: 1fr 1fr 1fr;
            margin-bottom: 20px;
        }

        .stats-grid.member-bottom {
            grid-template-columns: repeat(3, 1fr);
        }

        .stats-grid.payment-top {
            grid-template-columns: 1fr;
            margin-bottom: 20px;
        }

        .stats-grid.payment-bottom {
            grid-template-columns: 1fr 1fr 1fr;
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .stats-grid.member-top,
            .stats-grid.member-bottom {
                grid-template-columns: 1fr;
            }
        }
        
        .stat-item {
            background: #212529;
            color: white;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #343a40;
        }
        
        .stat-item .number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .stat-item .label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .section-header .title-group {
            display: flex;
            align-items: center;
        }

        .section-header i {
            font-size: 1.5rem;
            color: #495057;
            margin-right: 15px;
        }

        .section-header h2 {
            color: #212529;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .toggle-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .toggle-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #212529;
        }

        .toggle-btn .toggle-icon {
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .toggle-btn.collapsed .toggle-icon {
            transform: rotate(180deg);
        }

        .transaction-link {
            color: #495057;
            text-decoration: none;
            font-size: 0.8rem;
            padding: 2px 6px;
            border-radius: 3px;
            transition: all 0.2s ease;
            display: inline-block;
            margin-top: 3px;
        }

        .transaction-link:hover {
            background: #e9ecef;
            color: #212529;
            text-decoration: none;
        }

        .transaction-link i {
            margin-right: 4px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            background: #212529;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }

        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success { background: #212529; color: white; }
        .badge-warning { background: #6c757d; color: white; }
        .badge-danger { background: #495057; color: white; }
        .badge-info { background: #adb5bd; color: #212529; }
        .badge-primary { background: #343a40; color: white; }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #ecf0f1;
        }
        
        .card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .progress-bar {
            background: #ecf0f1;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            background: #212529;
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            background: #212529;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.2s ease;
            margin-top: 20px;
        }
        
        .back-link:hover {
            background: #495057;
            color: white;
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .date-filter {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .filter-row {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .filter-group input, .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
            background: white;
        }

        .filter-btn {
            background: #212529;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
            margin-top: 20px;
        }

        .filter-btn:hover {
            background: #495057;
        }

        .filter-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(248, 249, 250, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader-content {
            text-align: center;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .loader-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e9ecef;
            border-top: 4px solid #212529;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .highlight {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            font-weight: 600;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 600; }
        .text-sm { font-size: 0.85rem; }
        .text-xs { font-size: 0.75rem; }
        
        @media (max-width: 768px) {
            .dashboard { padding: 10px; }
            .header h1 { font-size: 2rem; }
            .section { padding: 20px; }
            .header .stats-summary { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Header with Summary Stats -->
        <div class="header">
            <h1><i class="fas fa-users"></i> Membership Overview</h1>
            <p class="subtitle">Comprehensive dashboard for West City Boxing membership data</p>
            
            <div class="stats-summary">
                <?php
                // Get date filter parameters
                $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
                $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
                $filter_type = 'active_during'; // Always use active during period logic

                // Get summary statistics using proven logic from active-members-test.php
                global $wpdb;
                $txn_table = $wpdb->prefix . 'mepr_transactions';
                $wcb_mentoring_id = 1738;

                // Get all groups using the same query as active-members-test.php
                $groups = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'memberpressgroup' AND post_status IN ('publish', 'private') ORDER BY post_title");

                // Get active members using the proven logic (only from 7 defined groups)
                $defined_groups = [
                    'Mini Cadet Boys (9-11 Years) Group 1',
                    'Cadet Boys Group 1',
                    'Cadet Boys Group 2',
                    'Youth Boys Group 1',
                    'Youth Boys Group 2',
                    'Mini Cadets Girls Group 1',
                    'Youth Girls Group 1'
                ];

                // Build date filter for member counting
                $member_date_filter = '';
                $date_params = [];
                if ($date_from && $date_to) {
                    if ($filter_type === 'membership_start') {
                        $member_date_filter = " AND DATE(t.created_at) BETWEEN %s AND %s";
                        $date_params = [$date_from, $date_to];
                    } elseif ($filter_type === 'expires_at') {
                        $member_date_filter = " AND DATE(t.expires_at) BETWEEN %s AND %s";
                        $date_params = [$date_from, $date_to];
                    } else { // active_during
                        $member_date_filter = " AND DATE(t.created_at) <= %s AND (t.expires_at IS NULL OR t.expires_at = '0000-00-00 00:00:00' OR DATE(t.expires_at) >= %s)";
                        $date_params = [$date_to, $date_from];
                    }
                }

                $total_active_members_from_groups = 0;
                $group_stats = [];

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
                        // Get memberships in this group using proven logic
                        $group_memberships = wcb_get_group_memberships($group->ID);

                        if (!empty($group_memberships)) {
                            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                            $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

                            // Build query with date filter
                            $query_params = array_merge($membership_ids, $date_params);

                            $group_member_count = $wpdb->get_var($wpdb->prepare("
                                SELECT COUNT(DISTINCT u.ID)
                                FROM {$wpdb->users} u
                                JOIN {$txn_table} t ON u.ID = t.user_id
                                WHERE t.product_id IN ({$placeholders})
                                AND t.status IN ('confirmed', 'complete')
                                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                                AND u.user_login != 'bwgdev'
                                {$member_date_filter}
                            ", ...$query_params));

                            $total_active_members_from_groups += $group_member_count;
                            $group_stats[$group_name] = $group_member_count;
                        } else {
                            $group_stats[$group_name] = 0;
                        }
                    } else {
                        $group_stats[$group_name] = 0;
                    }
                }

                $total_active_members = $total_active_members_from_groups;

                // Count active programs (only from the 7 defined groups)
                $total_programs = count($defined_groups);

                // Count transactions only from the 7 defined groups
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
                        $group_memberships = $wpdb->get_results($wpdb->prepare("
                            SELECT p.ID
                            FROM {$wpdb->posts} p
                            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                            WHERE pm.meta_key = '_mepr_group_id'
                            AND pm.meta_value = %d
                            AND p.post_type = 'memberpressproduct'
                            AND p.post_status IN ('publish', 'private')
                        ", $group->ID));

                        foreach ($group_memberships as $membership) {
                            $all_group_membership_ids[] = $membership->ID;
                        }
                    }
                }

                // Calculate total transactions - use the same data as the breakdown
                $total_transactions = 0;

                // Calculate additional quick statistics - reset counters
                $manual_transactions = 0;
                $stripe_transactions = 0;
                $community_class_transactions = 0;
                $weekly_members = 0;
                $monthly_members = 0;
                $full_term_members = 0;
                $community_class_members = 0;

                // Get Community Class membership ID
                $community_class_membership = $wpdb->get_row("
                    SELECT ID FROM {$wpdb->posts}
                    WHERE post_title = 'Community Class'
                    AND post_type = 'memberpressproduct'
                    AND post_status IN ('publish', 'private')
                    LIMIT 1
                ");

                // Get Community Class member count and debug missing transactions
                if ($community_class_membership) {
                    $query_params_cc = [$community_class_membership->ID];
                    if (!empty($date_params)) {
                        $query_params_cc = array_merge($query_params_cc, $date_params);
                    }

                    $community_class_members = $wpdb->get_var($wpdb->prepare("
                        SELECT COUNT(DISTINCT u.ID)
                        FROM {$wpdb->users} u
                        JOIN {$txn_table} t ON u.ID = t.user_id
                        WHERE t.product_id = %d
                        AND t.status IN ('confirmed', 'complete')
                        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                        AND u.user_login != 'bwgdev'
                        {$member_date_filter}
                    ", ...$query_params_cc));

                    // Debug: Get ALL Community Class sz7gj0-4lm transactions to understand the difference
                    $all_cc_stripe = $wpdb->get_results($wpdb->prepare("
                        SELECT t.trans_num, t.gateway, t.status, u.user_login, t.expires_at, t.created_at,
                               DATE(t.created_at) as created_date
                        FROM {$txn_table} t
                        JOIN {$wpdb->users} u ON u.ID = t.user_id
                        WHERE t.product_id = %d
                        AND t.status IN ('confirmed', 'complete')
                        AND t.gateway = 'sz7gj0-4lm'
                        AND u.user_login != 'bwgdev'
                        ORDER BY t.created_at DESC
                    ", $community_class_membership->ID));

                    // Separate the 10 MemberPress transactions from the others
                    $memberpress_txns = [
                        'ch_3RnrSWP0QmeaPCQr0cGYlba7', 'ch_3RnanpP0QmeaPCQr0p9mcZU4', 'ch_3RnRxjP0QmeaPCQr1C2Ly1Rb',
                        'mp-txn-6885f595e5533', 'ch_3RnDQ7P0QmeaPCQr0A0N2GRi', 'ch_3RnChQP0QmeaPCQr0SZLKwAv',
                        'ch_3RnC7cP0QmeaPCQr15hQq8p8', 'ch_3Rm7EgP0QmeaPCQr1LKcUxtF', 'ch_3Rl2VRP0QmeaPCQr1LYDICyw',
                        'mp-txn-6874d9c5ee8ac'
                    ];

                    $mp_transactions = [];
                    $other_transactions = [];
                    foreach ($all_cc_stripe as $cc_txn) {
                        if (in_array($cc_txn->trans_num, $memberpress_txns)) {
                            $mp_transactions[] = $cc_txn;
                        } else {
                            $other_transactions[] = $cc_txn;
                        }
                    }
                }

                if (!empty($all_group_membership_ids)) {
                    $placeholders = implode(',', array_fill(0, count($all_group_membership_ids), '%d'));

                    // Include Community Class in transaction query
                    $all_membership_ids = $all_group_membership_ids;
                    if ($community_class_membership) {
                        $all_membership_ids[] = $community_class_membership->ID;
                    }

                    $placeholders_all = implode(',', array_fill(0, count($all_membership_ids), '%d'));
                    $query_params = array_merge($all_membership_ids, $date_params);

                    $all_transactions = $wpdb->get_results($wpdb->prepare("
                        SELECT
                            t.gateway,
                            t.trans_num,
                            t.status,
                            p.post_title as membership_name
                        FROM {$txn_table} t
                        JOIN {$wpdb->posts} p ON t.product_id = p.ID
                        JOIN {$wpdb->users} u ON t.user_id = u.ID
                        WHERE t.product_id IN ({$placeholders_all})
                        AND t.status IN ('confirmed', 'complete')
                        AND (
                            p.post_title = 'Community Class' OR
                            (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                        )
                        AND u.user_login != 'bwgdev'
                        {$member_date_filter}
                        ORDER BY t.gateway, t.trans_num
                    ", ...$query_params));

                    // Debug: Let's see what gateways we have
                    $debug_gateways = [];
                    $gateway_counts = [];
                    $transaction_samples = [];
                    $community_class_debug = [];

                    // Debug array for sz7gj0-4lm transactions
                    $sz7_debug = [];

                    foreach ($all_transactions as $txn) {
                        // Count total transactions
                        $total_transactions++;

                        // Count each gateway type for debugging
                        if (!isset($gateway_counts[$txn->gateway])) {
                            $gateway_counts[$txn->gateway] = 0;
                        }
                        $gateway_counts[$txn->gateway]++;

                        // Debug ALL sz7gj0-4lm transactions
                        if ($txn->gateway === 'sz7gj0-4lm') {
                            $sz7_debug[] = [
                                'trans_num' => $txn->trans_num,
                                'membership' => $txn->membership_name,
                                'is_sub' => strpos($txn->trans_num, 'sub_') === 0,
                                'is_cs' => strpos($txn->trans_num, 'cs_live_') === 0,
                                'is_ch' => strpos($txn->trans_num, 'ch_') === 0,
                                'is_mp' => strpos($txn->trans_num, 'mp-txn-') === 0
                            ];
                        }

                        // Collect sample transaction numbers for debugging
                        if (!isset($transaction_samples[$txn->gateway])) {
                            $transaction_samples[$txn->gateway] = [];
                        }
                        if (count($transaction_samples[$txn->gateway]) < 3) {
                            $transaction_samples[$txn->gateway][] = $txn->trans_num;
                        }

                        // Simple logic: sz7gj0-4lm gateway = Stripe (excluding sub_ and cs_live_)
                        $is_stripe = ($txn->gateway === 'sz7gj0-4lm' &&
                                     strpos($txn->trans_num, 'sub_') !== 0 &&
                                     strpos($txn->trans_num, 'cs_live_') !== 0);

                        // Count by membership type and payment method
                        if ($txn->membership_name === 'Community Class' && $is_stripe) {
                            $community_class_transactions++;
                            $community_class_debug[] = $txn->trans_num . ' (' . $txn->gateway . ')';
                        } elseif ($txn->gateway === 'manual') {
                            $manual_transactions++;
                        } elseif ($is_stripe) {
                            $stripe_transactions++;
                            if (!isset($program_stripe_debug)) $program_stripe_debug = [];
                            $program_stripe_debug[] = $txn->trans_num . ' (' . $txn->membership_name . ')';
                        }

                        // Count by membership type (excluding Community Class)
                        if (strpos($txn->membership_name, 'Weekly') !== false) {
                            $weekly_members++;
                        } elseif (strpos($txn->membership_name, 'Monthly') !== false) {
                            $monthly_members++;
                        } elseif (strpos($txn->membership_name, 'Full Term') !== false) {
                            $full_term_members++;
                        }

                        // Collect unique gateways for debugging
                        if (!in_array($txn->gateway, $debug_gateways)) {
                            $debug_gateways[] = $txn->gateway;
                        }
                    }
                }
                ?>

                <!-- Two Column Stats Layout -->
                <div class="stats-container">
                    <!-- Left Column: Member Statistics -->
                    <div class="stats-column member-stats">
                        <div class="stats-section">
                            <h4><i class="fas fa-users" style="margin-right: 8px;"></i>Member Statistics</h4>

                            <!-- Top Row: Active Members & Programs & Community -->
                            <div class="stats-grid member-top">
                                <div class="stat-item">
                                    <span class="number"><?php echo $total_active_members; ?></span>
                                    <span class="label">Program Members</span>
                                </div>
                                <div class="stat-item">
                                    <span class="number"><?php echo $total_programs; ?></span>
                                    <span class="label">Programs Running</span>
                                </div>
                                <div class="stat-item">
                                    <span class="number"><?php echo $community_class_members; ?></span>
                                    <span class="label">Community Class</span>
                                </div>
                            </div>

                            <!-- Bottom Row: Membership Types -->
                            <div class="stats-grid member-bottom">
                                <div class="stat-item">
                                    <span class="number"><?php echo $weekly_members; ?></span>
                                    <span class="label">Weekly Members</span>
                                </div>
                                <div class="stat-item">
                                    <span class="number"><?php echo $monthly_members; ?></span>
                                    <span class="label">Monthly Members</span>
                                </div>
                                <div class="stat-item">
                                    <span class="number"><?php echo $full_term_members; ?></span>
                                    <span class="label">Full Term Members</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Payment Statistics -->
                    <div class="stats-column payment-stats">
                        <div class="stats-section">
                            <h4><i class="fas fa-credit-card" style="margin-right: 8px;"></i>Payment Statistics</h4>

                            <!-- Top Row: Total Transactions -->
                            <div class="stats-grid payment-top">
                                <div class="stat-item">
                                    <span class="number"><?php echo $total_transactions; ?></span>
                                    <span class="label">Total Transactions</span>
                                </div>
                            </div>

                            <!-- Bottom Row: Manual, Stripe & Community -->
                            <div class="stats-grid payment-bottom">
                                <div class="stat-item">
                                    <span class="number"><?php echo $manual_transactions; ?></span>
                                    <span class="label">Manual Payments</span>
                                </div>
                                <div class="stat-item">
                                    <span class="number"><?php echo $stripe_transactions; ?></span>
                                    <span class="label">Stripe Payments</span>
                                </div>
                                <div class="stat-item">
                                    <span class="number"><?php echo $community_class_transactions; ?></span>
                                    <span class="label">Community Class</span>
                                </div>
                            </div>

                            <?php if (!empty($debug_gateways)): ?>
                            <div style="margin-top: 15px;">
                                <div class="stat-item" style="background: #fff3cd; border: 1px solid #ffeaa7;">
                                    <span class="number" style="font-size: 0.8rem; color: #856404;">DEBUG</span>
                                    <span class="label" style="font-size: 0.7rem; color: #856404;">
                                        Gateway Breakdown:<br>
                                        <?php
                                        foreach ($gateway_counts as $gateway => $count) {
                                            echo '"' . $gateway . '": ' . $count;
                                            if (isset($transaction_samples[$gateway])) {
                                                echo ' (samples: ' . implode(', ', array_slice($transaction_samples[$gateway], 0, 2)) . ')';
                                            }
                                            echo '<br>';
                                        }
                                        ?>
                                        <br>Math: <?php echo $manual_transactions; ?> + <?php echo $stripe_transactions; ?> + <?php echo $community_class_transactions; ?> = <?php echo ($manual_transactions + $stripe_transactions + $community_class_transactions); ?> (Total: <?php echo $total_transactions; ?>)
                                        <br><br>Community Class: <?php echo $community_class_transactions; ?> transactions (Expected: 10)
                                        <br>CC Found: <?php echo count($community_class_debug); ?> transactions
                                        <br>Program Stripe Found: <?php echo isset($program_stripe_debug) ? count($program_stripe_debug) : 0; ?> transactions
                                        <br><strong>ALL 28 sz7gj0-4lm transactions breakdown:</strong>
                                        <?php
                                        $cc_count = 0; $stripe_count = 0; $sub_count = 0; $cs_count = 0;
                                        foreach ($sz7_debug as $sz7) {
                                            if ($sz7['is_sub']) $sub_count++;
                                            elseif ($sz7['is_cs']) $cs_count++;
                                            elseif ($sz7['membership'] === 'Community Class') $cc_count++;
                                            else $stripe_count++;
                                        }
                                        echo '<br>- Community Class: ' . $cc_count;
                                        echo '<br>- Program Stripe: ' . $stripe_count;
                                        echo '<br>- Subscriptions (sub_): ' . $sub_count;
                                        echo '<br>- Checkout Sessions (cs_): ' . $cs_count;
                                        echo '<br>- Total: ' . ($cc_count + $stripe_count + $sub_count + $cs_count);
                                        ?>
                                        <br><strong>ALL 19 Community Class Transactions:</strong>
                                        <?php
                                        // Get ALL Community Class sz7gj0-4lm transactions
                                        $all_cc_debug = $wpdb->get_results($wpdb->prepare("
                                            SELECT t.trans_num, t.gateway, t.status, u.user_login, t.expires_at,
                                                   DATE(t.created_at) as created_date, t.created_at
                                            FROM {$txn_table} t
                                            JOIN {$wpdb->users} u ON u.ID = t.user_id
                                            WHERE t.product_id = %d
                                            AND t.status IN ('confirmed', 'complete')
                                            AND t.gateway = 'sz7gj0-4lm'
                                            AND u.user_login != 'bwgdev'
                                            ORDER BY t.created_at DESC
                                        ", $community_class_membership->ID));

                                        $memberpress_list = [
                                            'ch_3RnrSWP0QmeaPCQr0cGYlba7', 'ch_3RnanpP0QmeaPCQr0p9mcZU4', 'ch_3RnRxjP0QmeaPCQr1C2Ly1Rb',
                                            'mp-txn-6885f595e5533', 'ch_3RnDQ7P0QmeaPCQr0A0N2GRi', 'ch_3RnChQP0QmeaPCQr0SZLKwAv',
                                            'ch_3RnC7cP0QmeaPCQr15hQq8p8', 'ch_3Rm7EgP0QmeaPCQr1LKcUxtF', 'ch_3Rl2VRP0QmeaPCQr1LYDICyw',
                                            'mp-txn-6874d9c5ee8ac'
                                        ];

                                        foreach ($all_cc_debug as $cc_txn) {
                                            $is_in_mp = in_array($cc_txn->trans_num, $memberpress_list);
                                            $label = $is_in_mp ? '✅ MP' : '❌ EXTRA';
                                            echo '<br>' . $label . ': ' . $cc_txn->trans_num . ' (' . $cc_txn->created_date . ')';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Loader -->
        <div id="page-loader" class="page-loader">
            <div class="loader-content">
                <div class="loader-spinner"></div>
                <h3>Loading Membership Data...</h3>
                <p>Please wait while we fetch the latest information</p>
            </div>
        </div>

        <!-- Date Filter Section -->
        <div class="date-filter">
            <h3 style="margin-bottom: 15px; color: #212529;">
                <i class="fas fa-calendar-alt" style="margin-right: 10px;"></i>
                Date Filter - Affects All Statistics & Data
            </h3>
            <p style="margin-bottom: 15px; color: #6c757d; font-size: 0.9rem;">
                Filter all statistics, member counts, and transaction data by date range. Shows members who were active during the selected period.
            </p>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="date-from">From Date:</label>
                    <input type="date" id="date-from" name="date-from" value="<?php echo esc_attr($date_from); ?>">
                </div>
                <div class="filter-group">
                    <label for="date-to">To Date:</label>
                    <input type="date" id="date-to" name="date-to" value="<?php echo esc_attr($date_to); ?>">
                </div>

                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button id="apply-filter" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                </div>
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button id="reset-filter" class="filter-btn" style="background: #6c757d;">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
            <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-radius: 4px; font-size: 0.9rem;">
                <strong>Quick Filters:</strong>
                <button class="filter-btn" style="margin: 5px; padding: 5px 10px; font-size: 0.8rem;" onclick="setQuickFilter('all')">All Time</button>
                <button class="filter-btn" style="margin: 5px; padding: 5px 10px; font-size: 0.8rem;" onclick="setQuickFilter('year')">This Year</button>
                <button class="filter-btn" style="margin: 5px; padding: 5px 10px; font-size: 0.8rem;" onclick="setQuickFilter('month')">This Month</button>
                <button class="filter-btn" style="margin: 5px; padding: 5px 10px; font-size: 0.8rem;" onclick="setQuickFilter('30days')">Last 30 Days</button>
                <button class="filter-btn" style="margin: 5px; padding: 5px 10px; font-size: 0.8rem;" onclick="setQuickFilter('7days')">Last 7 Days</button>
            </div>
        </div>

        <!-- Groups Overview Section -->
        <div class="section">
            <div class="section-header">
                <div class="title-group">
                    <i class="fas fa-layer-group"></i>
                    <h2>Groups Overview</h2>
                </div>
                <button class="toggle-btn" onclick="toggleSection('groups-overview-content')">
                    <span>Show</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </button>
            </div>

            <div id="groups-overview-content" style="display: none;">
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Total Members</th>
                            <th>Memberships Included</th>
                            <th>Recent Activity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Only show the 7 defined groups using proven logic
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
                                // Get memberships in this group using proven logic
                                $group_memberships = $wpdb->get_results($wpdb->prepare("
                                    SELECT p.ID, p.post_title
                                    FROM {$wpdb->posts} p
                                    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                                    WHERE pm.meta_key = '_mepr_group_id'
                                    AND pm.meta_value = %d
                                    AND p.post_type = 'memberpressproduct'
                                    AND p.post_status IN ('publish', 'private')
                                    ORDER BY p.post_title
                                ", $group->ID));

                                // Get member count using proven logic
                                $member_count = 0;
                                if (!empty($group_memberships)) {
                                    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                                    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

                                    $member_count = $wpdb->get_var($wpdb->prepare("
                                        SELECT COUNT(DISTINCT u.ID)
                                        FROM {$wpdb->users} u
                                        JOIN {$txn_table} t ON u.ID = t.user_id
                                        WHERE t.product_id IN ({$placeholders})
                                        AND t.status IN ('confirmed', 'complete')
                                        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                                        AND u.user_login != 'bwgdev'
                                    ", ...$membership_ids));
                                }

                                $status = $member_count > 0 ? 'Active' : 'Inactive';
                                $status_class = $member_count > 0 ? 'badge-success' : 'badge-warning';

                                echo '<tr>';
                                echo '<td class="font-bold">' . esc_html($group->post_title) . '</td>';
                                echo '<td class="text-center"><span class="badge badge-primary">' . $member_count . '</span></td>';
                                echo '<td class="text-sm">';
                                if (!empty($group_memberships)) {
                                    // Filter out monthly memberships for display only
                                    $display_memberships = array_filter($group_memberships, function($m) {
                                        return stripos($m->post_title, 'monthly') === false;
                                    });

                                    if (!empty($display_memberships)) {
                                        $membership_names = array_map(function($m) {
                                            return esc_html($m->post_title);
                                        }, $display_memberships);
                                        echo implode('<br>', $membership_names);
                                    } else {
                                        echo '<em>No non-monthly memberships</em>';
                                    }
                                } else {
                                    echo '<em>No memberships assigned</em>';
                                }
                                echo '</td>';
                                echo '<td class="text-sm">';
                                echo '<em>Session tracking not implemented</em>';
                                echo '</td>';
                                echo '<td><span class="badge ' . $status_class . '">' . $status . '</span></td>';
                                echo '</tr>';
                            } else {
                                // Group not found
                                echo '<tr>';
                                echo '<td class="font-bold" style="color: #dc3545;">' . esc_html($group_name) . '</td>';
                                echo '<td class="text-center"><span class="badge badge-danger">0</span></td>';
                                echo '<td class="text-sm"><em>Group not found in system</em></td>';
                                echo '<td class="text-sm"><em>N/A</em></td>';
                                echo '<td><span class="badge badge-danger">Missing</span></td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>



        <!-- Active Members by Groups Section -->
        <div class="section">
            <div class="section-header">
                <div class="title-group">
                    <i class="fas fa-users"></i>
                    <h2>Active Members by Groups</h2>
                </div>
                <button class="toggle-btn" onclick="toggleSection('active-members-content')">
                    <span>Hide</span>
                    <i class="fas fa-chevron-up toggle-icon"></i>
                </button>
            </div>

            <div id="active-members-content">
            <?php
            // Only show the 7 defined groups using proven logic
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
                    // Get member count using proven logic
                    $group_memberships = $wpdb->get_results($wpdb->prepare("
                        SELECT p.ID, p.post_title
                        FROM {$wpdb->posts} p
                        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                        WHERE pm.meta_key = '_mepr_group_id'
                        AND pm.meta_value = %d
                        AND p.post_type = 'memberpressproduct'
                        AND p.post_status IN ('publish', 'private')
                        ORDER BY p.post_title
                    ", $group->ID));

                    $member_count = 0;
                    if (!empty($group_memberships)) {
                        $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                        $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

                        $member_count = $wpdb->get_var($wpdb->prepare("
                            SELECT COUNT(DISTINCT u.ID)
                            FROM {$wpdb->users} u
                            JOIN {$txn_table} t ON u.ID = t.user_id
                            WHERE t.product_id IN ({$placeholders})
                            AND t.status IN ('confirmed', 'complete')
                            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                            AND u.user_login != 'bwgdev'
                        ", ...$membership_ids));
                    }

                    if ($member_count > 0) {
                        echo '<div style="margin-bottom: 40px;">';
                        echo '<h3 style="color: #212529; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #212529; display: flex; justify-content: space-between; align-items: center;">';
                        echo '<span>';
                        echo '<i class="fas fa-users" style="margin-right: 10px;"></i>';
                        echo esc_html($group->post_title) . ' (' . $member_count . ' members)';
                        echo '</span>';
                        echo '<button class="toggle-btn" onclick="toggleSection(\'group-' . $group->ID . '-content\')" style="margin: 0;">';
                        echo '<span>Hide</span>';
                        echo '<i class="fas fa-chevron-up toggle-icon"></i>';
                        echo '</button>';
                        echo '</h3>';

                        // Get members for this group using proven logic
                        echo '<div id="group-' . $group->ID . '-content">';
                        if (!empty($group_memberships)) {
                            $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                            $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

                            // Build date filter for group members
                            $group_date_filter = '';
                            $query_params = $membership_ids;

                            if ($date_from && $date_to) {
                                if ($filter_type === 'membership_start' || $filter_type === 'created_at') {
                                    $group_date_filter = " AND DATE(t.created_at) BETWEEN %s AND %s";
                                    $query_params[] = $date_from;
                                    $query_params[] = $date_to;
                                } elseif ($filter_type === 'expires_at') {
                                    $group_date_filter = " AND DATE(t.expires_at) BETWEEN %s AND %s";
                                    $query_params[] = $date_from;
                                    $query_params[] = $date_to;
                                }
                            }

                            // Build base date filter for member queries
                            // Logic: Show members whose membership period overlaps with the selected date range
                            $base_date_filter = '';
                            $date_params = [];
                            if ($date_from && $date_to) {
                                if ($filter_type === 'membership_start') {
                                    // Show memberships that started during the selected period
                                    $base_date_filter = " AND DATE(t.created_at) BETWEEN %s AND %s";
                                    $date_params = [$date_from, $date_to];
                                } elseif ($filter_type === 'expires_at') {
                                    // Show memberships that expire during the selected period
                                    $base_date_filter = " AND DATE(t.expires_at) BETWEEN %s AND %s";
                                    $date_params = [$date_from, $date_to];
                                } else {
                                    // Default: "active_during" - show memberships that were active at any point during the period
                                    // Logic: membership started before/during period end AND (never expires OR expires after/during period start)
                                    $base_date_filter = " AND DATE(t.created_at) <= %s AND (t.expires_at IS NULL OR t.expires_at = '0000-00-00 00:00:00' OR DATE(t.expires_at) >= %s)";
                                    $date_params = [$date_to, $date_from];
                                }
                            }

                            // Get unique members with their transactions
                            $members_query_sql = "
                                SELECT DISTINCT u.ID, u.display_name, u.user_email
                                FROM {$wpdb->users} u
                                JOIN {$txn_table} t ON u.ID = t.user_id
                                WHERE t.product_id IN ({$placeholders})
                                AND t.status IN ('confirmed', 'complete')
                                AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                                {$base_date_filter}
                                ORDER BY u.display_name
                            ";

                            $members_query_params = array_merge($membership_ids, $date_params);
                            $unique_members = $wpdb->get_results($wpdb->prepare($members_query_sql, ...$members_query_params));
                            $group_members = [];
                            $processed_users = []; // Track processed users to avoid duplicates

                            foreach ($unique_members as $user) {
                                // Skip if we've already processed this user
                                if (in_array($user->ID, $processed_users)) {
                                    continue;
                                }
                                $processed_users[] = $user->ID;

                                // Get ALL transactions for this user in this group (not just latest)
                                // Then we'll filter them by date criteria and pick the best match

                                $all_user_transactions = $wpdb->get_results($wpdb->prepare("
                                    SELECT
                                        t.id as transaction_id,
                                        t.created_at as membership_start,
                                        t.expires_at,
                                        t.gateway,
                                        t.trans_num,
                                        t.subscription_id,
                                        t.amount,
                                        t.status,
                                        p.post_title as membership_name
                                    FROM {$txn_table} t
                                    JOIN {$wpdb->posts} p ON t.product_id = p.ID
                                    WHERE t.user_id = %d
                                    AND t.product_id IN ({$placeholders})
                                    AND t.status IN ('confirmed', 'complete')
                                    ORDER BY t.created_at DESC
                                ", $user->ID, ...$membership_ids));

                                $manual_txn = null;
                                $stripe_txn = null;

                                // Process each transaction and find the best matches
                                foreach ($all_user_transactions as $txn) {
                                    // Check if transaction is currently active
                                    $is_active = ($txn->expires_at === null ||
                                                 $txn->expires_at === '0000-00-00 00:00:00' ||
                                                 strtotime($txn->expires_at) > time());

                                    if (!$is_active) continue; // Skip expired transactions

                                    // Determine if it's manual or Stripe
                                    $is_stripe = (
                                        $txn->gateway === 'stripe' ||
                                        $txn->gateway === 'MeprStripeGateway' ||
                                        strpos($txn->gateway, 'stripe') !== false ||
                                        strpos($txn->trans_num, 'pi_') === 0 ||
                                        strpos($txn->trans_num, 'ch_') === 0 ||
                                        strpos($txn->trans_num, 'sub_') === 0
                                    );

                                    // Check if transaction matches date criteria
                                    $matches_date = true;
                                    if ($date_from && $date_to) {
                                        if ($filter_type === 'membership_start') {
                                            $matches_date = (date('Y-m-d', strtotime($txn->membership_start)) >= $date_from &&
                                                           date('Y-m-d', strtotime($txn->membership_start)) <= $date_to);
                                        } elseif ($filter_type === 'expires_at') {
                                            $matches_date = ($txn->expires_at && $txn->expires_at !== '0000-00-00 00:00:00' &&
                                                           date('Y-m-d', strtotime($txn->expires_at)) >= $date_from &&
                                                           date('Y-m-d', strtotime($txn->expires_at)) <= $date_to);
                                        } else { // active_during (default)
                                            $start_date = date('Y-m-d', strtotime($txn->membership_start));
                                            $end_date = ($txn->expires_at && $txn->expires_at !== '0000-00-00 00:00:00') ?
                                                       date('Y-m-d', strtotime($txn->expires_at)) : '9999-12-31';
                                            $matches_date = ($start_date <= $date_to && $end_date >= $date_from);
                                        }
                                    }

                                    if ($matches_date) {
                                        if ($is_stripe && !$stripe_txn) {
                                            $stripe_txn = $txn;
                                        } elseif (!$is_stripe && !$manual_txn) {
                                            $manual_txn = $txn;
                                        }

                                        // If we have both types, we can break
                                        if ($manual_txn && $stripe_txn) break;
                                    }
                                }

                                // Create member object if we found any matching transactions
                                if ($manual_txn || $stripe_txn) {
                                    $member = new stdClass();
                                    $member->ID = $user->ID;
                                    $member->display_name = $user->display_name;
                                    $member->user_email = $user->user_email;
                                    $member->manual_transaction = $manual_txn;
                                    $member->stripe_transaction = $stripe_txn;

                                    $group_members[] = $member;
                                }
                            }



                            if (!empty($group_members)) {
                                echo '<div class="table-container">';
                                echo '<table>';
                                echo '<thead>';
                                echo '<tr>';
                                echo '<th>Member</th>';
                                echo '<th>Manual Transaction</th>';
                                echo '<th>Stripe Transaction</th>';
                                echo '<th>Stripe Cycle Details</th>';
                                echo '<th>Status</th>';
                                echo '</tr>';
                                echo '</thead>';
                                echo '<tbody>';

                                foreach ($group_members as $member) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo '<div class="font-bold">' . esc_html($member->display_name) . '</div>';
                                    echo '<div class="text-xs" style="color: #6c757d;">' . esc_html($member->user_email) . '</div>';
                                    echo '</td>';

                                    // Manual Transaction Column
                                    echo '<td>';
                                    if ($member->manual_transaction) {
                                        $manual_txn = $member->manual_transaction;
                                        $start_date = date('M j, Y', strtotime($manual_txn->membership_start));
                                        $end_date = 'Never';
                                        if ($manual_txn->expires_at && $manual_txn->expires_at !== '0000-00-00 00:00:00') {
                                            $end_date = date('M j, Y', strtotime($manual_txn->expires_at));
                                        }

                                        echo '<span class="badge badge-success">Manual Active</span><br>';
                                        echo '<span class="text-sm">Start: ' . $start_date . '</span><br>';
                                        echo '<span class="text-sm">End: ' . $end_date . '</span>';
                                        // Add transaction link
                                        $transaction_url = admin_url('admin.php?page=memberpress-trans&action=edit&id=' . $manual_txn->transaction_id);
                                        echo '<br><a href="' . esc_url($transaction_url) . '" class="transaction-link" target="_blank">';
                                        echo '<i class="fas fa-external-link-alt"></i>View';
                                        echo '</a>';
                                    } else {
                                        echo '<span class="text-sm" style="color: #6c757d;">No manual transaction</span>';
                                    }
                                    echo '</td>';

                                    // Stripe Transaction Column
                                    echo '<td>';
                                    if ($member->stripe_transaction) {
                                        $stripe_txn = $member->stripe_transaction;
                                        $start_date = date('M j, Y', strtotime($stripe_txn->membership_start));
                                        $end_date = 'Never';
                                        if ($stripe_txn->expires_at && $stripe_txn->expires_at !== '0000-00-00 00:00:00') {
                                            $end_date = date('M j, Y', strtotime($stripe_txn->expires_at));
                                        }

                                        echo '<span class="badge badge-primary">Stripe Active</span><br>';
                                        echo '<span class="text-sm">Start: ' . $start_date . '</span><br>';
                                        echo '<span class="text-sm">End: ' . $end_date . '</span>';
                                        if ($stripe_txn->subscription_id) {
                                            echo '<br><span class="text-xs">ID: ' . esc_html($stripe_txn->subscription_id) . '</span>';
                                        }
                                        // Add transaction link
                                        $transaction_url = admin_url('admin.php?page=memberpress-trans&action=edit&id=' . $stripe_txn->transaction_id);
                                        echo '<br><a href="' . esc_url($transaction_url) . '" class="transaction-link" target="_blank">';
                                        echo '<i class="fas fa-external-link-alt"></i>View';
                                        echo '</a>';
                                    } else {
                                        echo '<span class="text-sm" style="color: #6c757d;">No Stripe transaction</span>';
                                    }
                                    echo '</td>';

                                    // Stripe Cycle Details Column
                                    echo '<td>';
                                    if ($member->stripe_transaction) {
                                        $stripe_txn = $member->stripe_transaction;
                                        // Extract cycle information from membership name
                                        $cycle_info = 'Unknown';
                                        $renewal_info = '';

                                        if (strpos($stripe_txn->membership_name, 'Weekly') !== false) {
                                            $cycle_info = 'Weekly';
                                            if ($stripe_txn->expires_at && $stripe_txn->expires_at !== '0000-00-00 00:00:00') {
                                                $next_renewal = date('M j, Y', strtotime($stripe_txn->expires_at . ' +1 week'));
                                                $renewal_info = '<br><span class="text-xs">Next: ' . $next_renewal . '</span>';
                                            } else {
                                                $renewal_info = '<br><span class="text-xs">Auto-renewing</span>';
                                            }
                                        } elseif (strpos($stripe_txn->membership_name, 'Fortnightly') !== false) {
                                            $cycle_info = 'Fortnightly';
                                            if ($stripe_txn->expires_at && $stripe_txn->expires_at !== '0000-00-00 00:00:00') {
                                                $next_renewal = date('M j, Y', strtotime($stripe_txn->expires_at . ' +2 weeks'));
                                                $renewal_info = '<br><span class="text-xs">Next: ' . $next_renewal . '</span>';
                                            } else {
                                                $renewal_info = '<br><span class="text-xs">Auto-renewing</span>';
                                            }
                                        } elseif (strpos($stripe_txn->membership_name, 'Monthly') !== false) {
                                            $cycle_info = 'Monthly';
                                            if ($stripe_txn->expires_at && $stripe_txn->expires_at !== '0000-00-00 00:00:00') {
                                                $next_renewal = date('M j, Y', strtotime($stripe_txn->expires_at . ' +1 month'));
                                                $renewal_info = '<br><span class="text-xs">Next: ' . $next_renewal . '</span>';
                                            } else {
                                                $renewal_info = '<br><span class="text-xs">Auto-renewing</span>';
                                            }
                                        } elseif (strpos($stripe_txn->membership_name, 'Full Term') !== false) {
                                            $cycle_info = 'Full Term';
                                            if ($stripe_txn->expires_at && $stripe_txn->expires_at !== '0000-00-00 00:00:00') {
                                                $renewal_info = '<br><span class="text-xs">Ends: ' . date('M j, Y', strtotime($stripe_txn->expires_at)) . '</span>';
                                            } else {
                                                $renewal_info = '<br><span class="text-xs">Lifetime</span>';
                                            }
                                        }

                                        echo '<span class="badge badge-info">' . $cycle_info . '</span>';
                                        echo $renewal_info;
                                        echo '<br><span class="text-xs">$' . number_format($stripe_txn->amount, 2) . '</span>';
                                    } else {
                                        echo '<span class="text-sm" style="color: #6c757d;">N/A</span>';
                                    }
                                    echo '</td>';

                                    // Status Column
                                    echo '<td>';
                                    $is_active = !$member->expires_at || $member->expires_at === '0000-00-00 00:00:00' || strtotime($member->expires_at) > time();
                                    $status = $is_active ? 'Active' : 'Expired';
                                    $status_class = $is_active ? 'badge-success' : 'badge-danger';
                                    echo '<span class="badge ' . $status_class . '">' . $status . '</span>';
                                    echo '</td>';

                                    echo '</tr>';
                                }

                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>';
                            } else {
                                echo '<p class="no-data">No active members found for this group.</p>';
                            }
                        }
                        echo '</div>'; // Close group content div
                        echo '</div>'; // Close group container div
                    }
                } else {
                    // Group not found in system
                    echo '<div style="margin-bottom: 40px;">';
                    echo '<h3 style="color: #dc3545; margin-bottom: 20px; padding: 15px; background: #f8d7da; border-radius: 6px; border-left: 4px solid #dc3545;">';
                    echo '<i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i>';
                    echo esc_html($group_name) . ' (Group not found in system)';
                    echo '</h3>';
                    echo '</div>';
                }
            }
            ?>
            </div>
        </div>

        <!-- Transactions Overview Section (Toggleable) -->
        <div class="section">
            <div class="section-header">
                <div class="title-group">
                    <i class="fas fa-credit-card"></i>
                    <h2>Active Transactions & Subscriptions</h2>
                </div>
                <button class="toggle-btn" onclick="toggleSection('transactions-content')">
                    <span>Show</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </button>
            </div>

            <div id="transactions-content" style="display: none;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Group/Membership</th>
                                <th>Payment Method</th>
                                <th>Start Date</th>
                                <th>Expires</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get active transactions only from the 7 defined groups
                            $active_transactions = [];
                            if (!empty($all_group_membership_ids)) {
                                $placeholders = implode(',', array_fill(0, count($all_group_membership_ids), '%d'));

                                $active_transactions = $wpdb->get_results($wpdb->prepare("
                                    SELECT
                                        t.id as transaction_id,
                                        t.user_id,
                                        t.product_id,
                                        t.amount,
                                        t.created_at,
                                        t.expires_at,
                                        t.gateway,
                                        t.trans_num,
                                        t.status,
                                        t.subscription_id,
                                        u.display_name,
                                        u.user_email,
                                        p.post_title as membership_name
                                    FROM {$txn_table} t
                                    JOIN {$wpdb->users} u ON t.user_id = u.ID
                                    JOIN {$wpdb->posts} p ON t.product_id = p.ID
                                    WHERE t.product_id IN ({$placeholders})
                                    AND t.status IN ('confirmed', 'complete')
                                    AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                                    AND u.user_login != 'bwgdev'
                                    ORDER BY t.created_at DESC
                                    LIMIT 100
                                ", ...$all_group_membership_ids));
                            }

                            if (!empty($active_transactions)) {
                                foreach ($active_transactions as $transaction) {
                                    // Get group name if membership belongs to a group
                                    $group_id = get_post_meta($transaction->product_id, '_mepr_group_id', true);
                                    $display_name = $transaction->membership_name;

                                    if ($group_id) {
                                        $group = get_post($group_id);
                                        if ($group && $group->post_type === 'memberpressgroup') {
                                            $display_name = $group->post_title;
                                        }
                                    }

                                    // Determine payment method with improved Stripe detection
                                    $payment_method = 'Manual';
                                    $is_stripe_txn = (
                                        $transaction->gateway === 'stripe' ||
                                        $transaction->gateway === 'MeprStripeGateway' ||
                                        strpos($transaction->gateway, 'stripe') !== false ||
                                        strpos($transaction->trans_num, 'pi_') === 0 ||
                                        strpos($transaction->trans_num, 'ch_') === 0 ||
                                        strpos($transaction->trans_num, 'sub_') === 0
                                    );

                                    if ($is_stripe_txn) {
                                        $payment_method = $transaction->subscription_id ? 'Stripe Subscription' : 'Stripe Payment';
                                    } elseif ($transaction->gateway) {
                                        $payment_method = ucfirst($transaction->gateway);
                                    }

                                    // Format dates
                                    $start_date = date('M j, Y', strtotime($transaction->created_at));
                                    $expires_date = 'Never';
                                    if ($transaction->expires_at && $transaction->expires_at !== '0000-00-00 00:00:00') {
                                        $expires_date = date('M j, Y', strtotime($transaction->expires_at));
                                        $days_until_expiry = ceil((strtotime($transaction->expires_at) - time()) / (60 * 60 * 24));
                                        if ($days_until_expiry <= 30) {
                                            $expires_date .= ' <span class="badge badge-warning">(' . $days_until_expiry . ' days)</span>';
                                        }
                                    }

                                    // Status badge
                                    $status_class = $transaction->status === 'confirmed' ? 'badge-success' : 'badge-info';

                                    echo '<tr>';
                                    echo '<td>';
                                    echo '<div class="font-bold">' . esc_html($transaction->display_name) . '</div>';
                                    echo '<div class="text-xs" style="color: #6c757d;">' . esc_html($transaction->user_email) . '</div>';
                                    echo '</td>';
                                    echo '<td class="font-bold">' . esc_html($display_name) . '</td>';
                                    echo '<td>';
                                    echo '<span class="badge badge-info">' . esc_html($payment_method) . '</span>';
                                    if ($transaction->subscription_id) {
                                        echo '<br><span class="text-xs">ID: ' . esc_html($transaction->subscription_id) . '</span>';
                                    }
                                    // Add link to MemberPress transaction admin page
                                    $transaction_url = admin_url('admin.php?page=memberpress-trans&action=edit&id=' . $transaction->transaction_id);
                                    echo '<br><a href="' . esc_url($transaction_url) . '" class="transaction-link" target="_blank">';
                                    echo '<i class="fas fa-external-link-alt"></i>View Transaction';
                                    echo '</a>';
                                    echo '</td>';
                                    echo '<td>' . $start_date . '</td>';
                                    echo '<td>' . $expires_date . '</td>';
                                    echo '<td class="text-right font-bold">$' . number_format($transaction->amount, 2) . '</td>';
                                    echo '<td><span class="badge ' . $status_class . '">' . ucfirst($transaction->status) . '</span></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="7" class="no-data">No active transactions found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Member Details Section (Toggleable) -->
        <div class="section">
            <div class="section-header">
                <div class="title-group">
                    <i class="fas fa-users-cog"></i>
                    <h2>Recent Member Activity</h2>
                </div>
                <button class="toggle-btn" onclick="toggleSection('member-activity-content')">
                    <span>Show</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </button>
            </div>

            <div id="member-activity-content" style="display: none;">
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Group</th>
                            <th>Join Date</th>
                            <th>Age</th>
                            <th>Ethnicity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get recent members with their details
                        $recent_members = $wpdb->get_results("
                            SELECT DISTINCT
                                u.ID,
                                u.display_name,
                                u.user_email,
                                u.user_registered,
                                t.created_at as membership_start,
                                t.expires_at,
                                t.product_id
                            FROM {$wpdb->users} u
                            JOIN {$txn_table} t ON u.ID = t.user_id
                            WHERE t.status IN ('confirmed', 'complete')
                            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                            ORDER BY t.created_at DESC
                            LIMIT 30
                        ");

                        if (!empty($recent_members)) {
                            foreach ($recent_members as $member) {
                                // Get group name
                                $group_id = get_post_meta($member->product_id, '_mepr_group_id', true);
                                $group_name = 'Individual Membership';
                                if ($group_id) {
                                    $group = get_post($group_id);
                                    if ($group && $group->post_type === 'memberpressgroup') {
                                        $group_name = $group->post_title;
                                    }
                                }

                                // Get member details
                                $age = get_user_meta($member->ID, 'mepr_age', true);
                                $ethnicity = get_user_meta($member->ID, 'mepr_ethnicity', true);

                                // Calculate age if birth date is available
                                if (empty($age)) {
                                    $birth_date = get_user_meta($member->ID, 'mepr_date_of_birth', true);
                                    if ($birth_date) {
                                        $age = floor((time() - strtotime($birth_date)) / 31556926); // seconds in a year
                                    }
                                }

                                // Format join date
                                $join_date = date('M j, Y', strtotime($member->membership_start));

                                // Status
                                $is_active = !$member->expires_at || $member->expires_at === '0000-00-00 00:00:00' || strtotime($member->expires_at) > time();
                                $status = $is_active ? 'Active' : 'Expired';
                                $status_class = $is_active ? 'badge-success' : 'badge-danger';

                                echo '<tr>';
                                echo '<td>';
                                echo '<div class="font-bold">' . esc_html($member->display_name) . '</div>';
                                echo '<div class="text-xs">' . esc_html($member->user_email) . '</div>';
                                echo '</td>';
                                echo '<td class="font-bold">' . esc_html($group_name) . '</td>';
                                echo '<td>' . $join_date . '</td>';
                                echo '<td class="text-center">' . ($age ? $age . ' years' : 'N/A') . '</td>';
                                echo '<td class="text-sm">' . ($ethnicity ? esc_html($ethnicity) : 'Not specified') . '</td>';
                                echo '<td><span class="badge ' . $status_class . '">' . $status . '</span></td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="no-data">No member data found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="section">
            <div class="section-header">
                <div class="title-group">
                    <i class="fas fa-chart-bar"></i>
                    <h2>Quick Statistics</h2>
                </div>
                <button class="toggle-btn" onclick="toggleSection('statistics-content')">
                    <span>Hide</span>
                    <i class="fas fa-chevron-up toggle-icon"></i>
                </button>
            </div>

            <div id="statistics-content">
                <div class="grid">
                    <div class="card">
                        <h3><i class="fas fa-money-bill-wave"></i> Revenue Summary</h3>

                        <!-- Revenue Toggle Options -->
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                                <button class="filter-btn" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleRevenue('all')">All Transactions</button>
                                <button class="filter-btn" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleRevenue('stripe')">Stripe/Online</button>
                                <button class="filter-btn" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleRevenue('manual')">Manual</button>
                            </div>
                        </div>

                        <div id="revenue-all">
                            <?php
                            $revenue_stats = $wpdb->get_row("
                                SELECT
                                    COUNT(*) as total_transactions,
                                    SUM(amount) as total_revenue,
                                    AVG(amount) as avg_transaction
                                FROM {$txn_table}
                                WHERE status IN ('confirmed', 'complete')
                                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                            ");

                            if ($revenue_stats) {
                                echo '<div class="text-center">';
                                echo '<div style="font-size: 2rem; font-weight: bold; color: #212529;">$' . number_format($revenue_stats->total_revenue, 2) . '</div>';
                                echo '<div class="text-sm">Total Revenue (12 months)</div>';
                                echo '<div style="margin-top: 15px;">';
                                echo '<div>Transactions: <strong>' . $revenue_stats->total_transactions . '</strong></div>';
                                echo '<div>Average: <strong>$' . number_format($revenue_stats->avg_transaction, 2) . '</strong></div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <div id="revenue-stripe" style="display: none;">
                            <?php
                            $stripe_stats = $wpdb->get_row("
                                SELECT
                                    COUNT(*) as total_transactions,
                                    SUM(amount) as total_revenue,
                                    AVG(amount) as avg_transaction
                                FROM {$txn_table}
                                WHERE status IN ('confirmed', 'complete')
                                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                                AND (
                                    gateway = 'stripe' OR
                                    gateway = 'MeprStripeGateway' OR
                                    gateway LIKE '%stripe%' OR
                                    trans_num LIKE 'pi_%' OR
                                    trans_num LIKE 'ch_%' OR
                                    trans_num LIKE 'sub_%'
                                )
                            ");

                            if ($stripe_stats && $stripe_stats->total_transactions > 0) {
                                echo '<div class="text-center">';
                                echo '<div style="font-size: 2rem; font-weight: bold; color: #212529;">$' . number_format($stripe_stats->total_revenue, 2) . '</div>';
                                echo '<div class="text-sm">Stripe/Online Revenue (12 months)</div>';
                                echo '<div style="margin-top: 15px;">';
                                echo '<div>Transactions: <strong>' . $stripe_stats->total_transactions . '</strong></div>';
                                echo '<div>Average: <strong>$' . number_format($stripe_stats->avg_transaction, 2) . '</strong></div>';
                                echo '</div>';
                                echo '</div>';
                            } else {
                                echo '<div class="text-center">';
                                echo '<div style="font-size: 2rem; font-weight: bold; color: #6c757d;">$0.00</div>';
                                echo '<div class="text-sm">No Stripe/Online Revenue (12 months)</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <div id="revenue-manual" style="display: none;">
                            <?php
                            $manual_stats = $wpdb->get_row("
                                SELECT
                                    COUNT(*) as total_transactions,
                                    SUM(amount) as total_revenue,
                                    AVG(amount) as avg_transaction
                                FROM {$txn_table}
                                WHERE status IN ('confirmed', 'complete')
                                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                                AND (
                                    gateway IS NULL OR
                                    gateway = '' OR
                                    gateway = 'manual' OR
                                    (gateway NOT LIKE '%stripe%' AND
                                     gateway != 'MeprStripeGateway' AND
                                     trans_num NOT LIKE 'pi_%' AND
                                     trans_num NOT LIKE 'ch_%' AND
                                     trans_num NOT LIKE 'sub_%')
                                )
                            ");

                            if ($manual_stats && $manual_stats->total_transactions > 0) {
                                echo '<div class="text-center">';
                                echo '<div style="font-size: 2rem; font-weight: bold; color: #212529;">$' . number_format($manual_stats->total_revenue, 2) . '</div>';
                                echo '<div class="text-sm">Manual Revenue (12 months)</div>';
                                echo '<div style="margin-top: 15px;">';
                                echo '<div>Transactions: <strong>' . $manual_stats->total_transactions . '</strong></div>';
                                echo '<div>Average: <strong>$' . number_format($manual_stats->avg_transaction, 2) . '</strong></div>';
                                echo '</div>';
                                echo '</div>';
                            } else {
                                echo '<div class="text-center">';
                                echo '<div style="font-size: 2rem; font-weight: bold; color: #6c757d;">$0.00</div>';
                                echo '<div class="text-sm">No Manual Revenue (12 months)</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-calendar-check"></i> Membership Growth</h3>
                    <?php
                    $growth_stats = $wpdb->get_results("
                        SELECT
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            COUNT(*) as new_members
                        FROM {$txn_table}
                        WHERE status IN ('confirmed', 'complete')
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month DESC
                        LIMIT 6
                    ");

                    if (!empty($growth_stats)) {
                        foreach ($growth_stats as $stat) {
                            $month_name = date('M Y', strtotime($stat->month . '-01'));
                            echo '<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">';
                            echo '<span>' . $month_name . '</span>';
                            echo '<span class="font-bold">' . $stat->new_members . ' new</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo admin_url(); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Admin Dashboard
            </a>
        </div>
    </div>

    <script>
        // Page loader functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loader after page loads
            setTimeout(function() {
                const loader = document.getElementById('page-loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }, 1000);

            // Set default date values - use a wider range to ensure we see data
            const today = new Date();
            const oneYearAgo = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());

            // Only set defaults if no URL parameters exist
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.get('date_from') && !urlParams.get('date_to')) {
                document.getElementById('date-from').value = oneYearAgo.toISOString().split('T')[0];
                document.getElementById('date-to').value = today.toISOString().split('T')[0];
            }
        });

        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const button = event.target.closest('button');
            const span = button.querySelector('span');
            const icon = button.querySelector('.toggle-icon');

            if (section.style.display === 'none' || section.style.display === '') {
                // Show section
                section.style.display = 'block';
                span.textContent = 'Hide';
                icon.className = 'fas fa-chevron-up toggle-icon';
                button.classList.remove('collapsed');
            } else {
                // Hide section
                section.style.display = 'none';
                span.textContent = 'Show';
                icon.className = 'fas fa-chevron-down toggle-icon';
                button.classList.add('collapsed');
            }
        }

        function setQuickFilter(period) {
            const today = new Date();
            const dateFrom = document.getElementById('date-from');
            const dateTo = document.getElementById('date-to');

            dateTo.value = today.toISOString().split('T')[0];

            switch(period) {
                case 'all':
                    // Set a very wide range to capture all data
                    const fiveYearsAgo = new Date(today.getFullYear() - 5, 0, 1);
                    dateFrom.value = fiveYearsAgo.toISOString().split('T')[0];
                    break;
                case '7days':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 7);
                    dateFrom.value = weekAgo.toISOString().split('T')[0];
                    break;
                case '30days':
                    const thirtyDaysAgo = new Date(today);
                    thirtyDaysAgo.setDate(today.getDate() - 30);
                    dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
                    break;
                case 'month':
                    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                    dateFrom.value = monthStart.toISOString().split('T')[0];
                    break;
                case 'year':
                    const yearStart = new Date(today.getFullYear(), 0, 1);
                    dateFrom.value = yearStart.toISOString().split('T')[0];
                    break;
            }
        }

        function showLoader() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.display = 'flex';
            }
        }

        // Apply filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('apply-filter');
            const resetBtn = document.getElementById('reset-filter');

            applyBtn.addEventListener('click', function() {
                const dateFrom = document.getElementById('date-from').value;
                const dateTo = document.getElementById('date-to').value;

                if (dateFrom && dateTo) {
                    showLoader();
                    const url = new URL(window.location);
                    url.searchParams.set('date_from', dateFrom);
                    url.searchParams.set('date_to', dateTo);
                    window.location.href = url.toString();
                } else {
                    alert('Please select both from and to dates.');
                }
            });

            resetBtn.addEventListener('click', function() {
                showLoader();
                const url = new URL(window.location);
                url.searchParams.delete('date_from');
                url.searchParams.delete('date_to');
                window.location.href = url.toString();
            });

            // Set current filter values if they exist
            const urlParams = new URLSearchParams(window.location.search);
            const dateFrom = urlParams.get('date_from');
            const dateTo = urlParams.get('date_to');

            if (dateFrom) document.getElementById('date-from').value = dateFrom;
            if (dateTo) document.getElementById('date-to').value = dateTo;
        });

        function toggleRevenue(type) {
            // Hide all revenue sections
            document.getElementById('revenue-all').style.display = 'none';
            document.getElementById('revenue-stripe').style.display = 'none';
            document.getElementById('revenue-manual').style.display = 'none';

            // Show selected section
            document.getElementById('revenue-' + type).style.display = 'block';

            // Update button styles
            const buttons = document.querySelectorAll('[onclick^="toggleRevenue"]');
            buttons.forEach(btn => {
                btn.style.background = '#6c757d';
            });

            // Highlight active button
            event.target.style.background = '#212529';
        }
    </script>
</body>
</html>
