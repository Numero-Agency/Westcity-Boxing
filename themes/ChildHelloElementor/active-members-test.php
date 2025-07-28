<?php
/**
 * Active Members Test Page - Simplified
 */

// Load WordPress
require_once('../../../wp-load.php');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Active Members Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #212529;
            border-bottom: 3px solid #212529;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .stat-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #007bff;
            margin-bottom: 30px;
            text-align: center;
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #212529;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 16px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th {
            background: #212529;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .member-name {
            font-weight: 600;
            color: #212529;
        }
        .member-email {
            color: #6c757d;
            font-size: 13px;
        }
        .membership-info {
            font-size: 13px;
            color: #495057;
        }
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px;
        }
        .duplicate-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
            padding: 20px;
            border-radius: 6px;
            margin: 30px 0;
        }
        .duplicate-group {
            background: #fff;
            border: 1px solid #e9ecef;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .duplicate-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 10px;
        }
        .duplicate-member {
            padding: 8px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 3px;
            font-size: 14px;
        }
        .duplicate-reason {
            color: #6c757d;
            font-size: 12px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🥊 Active Members</h1>

        <?php
        // Get the active members data excluding WCB Mentoring and admin users
        global $wpdb;
        $txn_table = $wpdb->prefix . 'mepr_transactions';

        // Get WCB Mentoring membership ID (assuming it's ID 1738 based on previous code)
        $wcb_mentoring_id = 1738;

        // Get all active members excluding WCB Mentoring and admin users
        $active_members = $wpdb->get_results("
            SELECT DISTINCT u.ID
            FROM {$wpdb->users} u
            JOIN {$txn_table} t ON u.ID = t.user_id
            WHERE t.status IN ('confirmed', 'complete')
            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
            AND t.product_id != {$wcb_mentoring_id}
            AND u.user_login != 'bwgdev'
            ORDER BY u.ID
        ");

        $active_member_ids = array_column($active_members, 'ID');
        $total_active_members = count($active_member_ids);

        // Get groups for filter
        $groups = wcb_get_all_groups();
        $selected_group = isset($_GET['group']) ? intval($_GET['group']) : '';

        // Function to detect potential duplicates and users without email
        function detect_duplicates($member_ids, $wpdb, $txn_table, $wcb_mentoring_id) {
            $duplicates = [];
            $no_email_users = [];
            $members_data = [];

            // Confirmed different members (not duplicates)
            $confirmed_different = [
                [136, 409], // Holden O'Brien and Milo O'Brien
                [451, 1049], // Lucas Atui and Lucas Tauil
                [472, 1017], // Caris Gage (different people with same name)
            ];

            // Get all member data
            foreach ($member_ids as $user_id) {
                $user = get_user_by('ID', $user_id);
                if (!$user) continue;

                // Check for users without email
                if (empty(trim($user->user_email))) {
                    $no_email_users[] = [
                        'id' => $user_id,
                        'display_name' => $user->display_name,
                        'display_email' => 'No email'
                    ];
                    continue; // Skip from duplicate detection
                }

                $members_data[] = [
                    'id' => $user_id,
                    'name' => trim(strtolower($user->display_name)),
                    'email' => trim(strtolower($user->user_email)),
                    'display_name' => $user->display_name,
                    'display_email' => $user->user_email
                ];
            }

            // Check for duplicates
            for ($i = 0; $i < count($members_data); $i++) {
                for ($j = $i + 1; $j < count($members_data); $j++) {
                    $member1 = $members_data[$i];
                    $member2 = $members_data[$j];

                    // Skip confirmed different members
                    $is_confirmed_different = false;
                    foreach ($confirmed_different as $pair) {
                        if (($member1['id'] == $pair[0] && $member2['id'] == $pair[1]) ||
                            ($member1['id'] == $pair[1] && $member2['id'] == $pair[0])) {
                            $is_confirmed_different = true;
                            break;
                        }
                    }
                    if ($is_confirmed_different) {
                        continue;
                    }

                    $reasons = [];

                    // Same name, different email
                    if ($member1['name'] === $member2['name'] && $member1['email'] !== $member2['email']) {
                        $reasons[] = 'Same name, different email (name: "' . $member1['display_name'] . '")';
                    }

                    // Same email, different name
                    if ($member1['email'] === $member2['email'] && $member1['name'] !== $member2['name']) {
                        $reasons[] = 'Same email, different name (email: ' . $member1['display_email'] . ')';
                    }

                    // Similar names (allowing for typos/variations)
                    $similarity = similar_text($member1['name'], $member2['name'], $percent);
                    if ($percent > 85 && $member1['name'] !== $member2['name']) {
                        $reasons[] = 'Very similar names (' . round($percent) . '% match: "' . $member1['display_name'] . '" vs "' . $member2['display_name'] . '")';
                    }

                    // Check for name variations (first name + last name swapped, etc.)
                    $name1_parts = explode(' ', $member1['name']);
                    $name2_parts = explode(' ', $member2['name']);
                    if (count($name1_parts) >= 2 && count($name2_parts) >= 2) {
                        // Check if first and last names are swapped
                        if (in_array($name1_parts[0], $name2_parts) && in_array($name1_parts[count($name1_parts)-1], $name2_parts)) {
                            $reasons[] = 'Possible name order variation ("' . $member1['display_name'] . '" vs "' . $member2['display_name'] . '")';
                        }
                    }

                    // Check for email domain similarities (same person with different email providers)
                    $email1_parts = explode('@', $member1['email']);
                    $email2_parts = explode('@', $member2['email']);
                    if (count($email1_parts) === 2 && count($email2_parts) === 2) {
                        if ($email1_parts[0] === $email2_parts[0] && $email1_parts[1] !== $email2_parts[1]) {
                            $reasons[] = 'Same email username, different provider (username: ' . $email1_parts[0] . ')';
                        }
                    }

                    if (!empty($reasons)) {
                        $duplicate_key = min($member1['id'], $member2['id']) . '_' . max($member1['id'], $member2['id']);
                        if (!isset($duplicates[$duplicate_key])) {
                            $duplicates[$duplicate_key] = [
                                'members' => [$member1, $member2],
                                'reasons' => $reasons
                            ];
                        }
                    }
                }
            }

            return [
                'duplicates' => $duplicates,
                'no_email' => $no_email_users
            ];
        }

        // Detect duplicates and users without email
        $detection_results = detect_duplicates($active_member_ids, $wpdb, $txn_table, $wcb_mentoring_id);
        $duplicates = $detection_results['duplicates'];
        $no_email_users = $detection_results['no_email'];

        // Calculate the official active member count (same as MemberPress summary)
        // This will be calculated later, so we'll use a placeholder for now
        ?>

        <div class="stat-summary">
            <div class="stat-number" id="official-active-count">-</div>
            <div class="stat-label">Active Members</div>
        </div>

        <!-- Group Filter -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 30px;">
            <form method="GET" style="display: flex; align-items: center; gap: 15px;">
                <label for="group" style="font-weight: 600; color: #212529;">Filter by Group:</label>
                <select id="group" name="group" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; background: white;">
                    <option value="">All Groups</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group->ID; ?>" <?php selected($selected_group, $group->ID); ?>>
                            <?php echo esc_html($group->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" style="padding: 8px 16px; background: #212529; color: white; border: none; border-radius: 4px; cursor: pointer;">Filter</button>
                <?php if ($selected_group): ?>
                    <a href="?" style="padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php
        // Filter members by group if selected
        $filtered_members = $active_member_ids;
        $filter_info = '';

        if ($selected_group) {
            $selected_group_obj = get_post($selected_group);
            $filter_info = "Showing members from: " . esc_html($selected_group_obj->post_title);

            // Get memberships in this group
            $group_memberships = wcb_get_group_memberships($selected_group);
            if (!empty($group_memberships)) {
                $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

                // Get members who have active transactions for memberships in this group
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

                $filtered_members = array_column($group_members, 'ID');
            } else {
                $filtered_members = [];
            }
        }
        ?>

        <?php if ($filter_info): ?>
            <div style="background: #e7f3ff; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #007bff;">
                <strong><?php echo $filter_info; ?></strong> (<?php echo count($filtered_members); ?> members)
            </div>
        <?php endif; ?>

        <?php if (!empty($filtered_members)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email</th>
                        <th>User ID</th>
                        <th>Active Membership</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered_members as $user_id): ?>
                        <?php
                        $user = get_user_by('ID', $user_id);
                        if (!$user) continue;

                        // Get user's most recent active transaction (excluding WCB Mentoring)
                        $user_transaction = $wpdb->get_row($wpdb->prepare("
                            SELECT t.*, p.post_title as membership_name
                            FROM {$txn_table} t
                            JOIN {$wpdb->posts} p ON t.product_id = p.ID
                            WHERE t.user_id = %d
                            AND t.status IN ('confirmed', 'complete')
                            AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                            AND t.product_id != %d
                            ORDER BY t.created_at DESC
                            LIMIT 1
                        ", $user_id, $wcb_mentoring_id));

                        // Get user's group
                        $user_group = '';
                        if ($user_transaction) {
                            $membership_group_id = get_post_meta($user_transaction->product_id, '_mepr_group_id', true);
                            if ($membership_group_id) {
                                $group_post = get_post($membership_group_id);
                                if ($group_post) {
                                    $user_group = $group_post->post_title;
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="member-name"><?php echo esc_html($user->display_name); ?></div>
                            </td>
                            <td>
                                <div class="member-email"><?php echo esc_html($user->user_email); ?></div>
                            </td>
                            <td><?php echo $user_id; ?></td>
                            <td>
                                <?php if ($user_transaction): ?>
                                    <div class="membership-info">
                                        <strong><?php echo esc_html($user_transaction->membership_name); ?></strong><br>
                                        <small>
                                            Start: <?php echo date('M j, Y', strtotime($user_transaction->created_at)); ?><br>
                                            <?php if ($user_transaction->expires_at && $user_transaction->expires_at !== '0000-00-00 00:00:00'): ?>
                                                End: <?php echo date('M j, Y', strtotime($user_transaction->expires_at)); ?>
                                            <?php else: ?>
                                                End: Never
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <em>No active membership found</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="membership-info"><?php echo esc_html($user_group); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <?php if ($selected_group): ?>
                    No active members found in the selected group.
                <?php else: ?>
                    No active members found.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Users Without Email Section - Hidden for now -->
        <?php if (false && !empty($no_email_users) && !$selected_group): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-left: 4px solid #dc3545; padding: 20px; border-radius: 6px; margin: 30px 0;">
                <h3 style="margin-top: 0; color: #721c24;">📧 Users Without Email Address</h3>
                <p>The following members don't have email addresses. Please add email addresses for better communication:</p>

                <?php foreach ($no_email_users as $user): ?>
                    <div style="background: #fff; border: 1px solid #e9ecef; padding: 10px; margin: 5px 0; border-radius: 4px;">
                        <strong><?php echo esc_html($user['display_name']); ?></strong> - ID: <?php echo $user['id']; ?>
                        <span style="color: #dc3545; font-style: italic;"> (No email address)</span>
                    </div>
                <?php endforeach; ?>

                <p><small><strong>Action needed:</strong> Please update these user profiles with valid email addresses.</small></p>
            </div>
        <?php endif; ?>

        <!-- Duplicate Detection Section -->
        <?php if (!empty($duplicates) && !$selected_group): ?>
            <div class="duplicate-section">
                <h3 style="margin-top: 0; color: #856404;">⚠️ Potential Duplicate Members Found</h3>
                <p>The following members might be duplicates. Please review and merge if necessary:</p>

                <?php foreach ($duplicates as $duplicate): ?>
                    <div class="duplicate-group">
                        <div class="duplicate-title">Potential Match:</div>
                        <?php foreach ($duplicate['members'] as $member): ?>
                            <div class="duplicate-member">
                                <strong><?php echo esc_html($member['display_name']); ?></strong>
                                (<?php echo esc_html($member['display_email']); ?>)
                                - ID: <?php echo $member['id']; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="duplicate-reason">
                            Reason(s): <?php echo implode(', ', $duplicate['reasons']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <p><small><strong>Note:</strong> These are automated suggestions. Please manually verify before taking any action.</small></p>
            </div>
        <?php elseif (!$selected_group): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-left: 4px solid #28a745; padding: 20px; border-radius: 6px; margin: 30px 0;">
                <h3 style="margin-top: 0; color: #155724;">✅ No Duplicate Members Found</h3>
                <p>Great! No potential duplicate members were detected in your active member list.</p>
            </div>
        <?php endif; ?>

        <!-- Member List Comparison Section -->
        <?php if (!$selected_group): ?>
            <?php
            // Expected member list by group - using EXACT group titles from MemberPress
            $expected_members = [
                'Mini Cadet Boys (9-11 Years) Group 1' => [
                    'Astyn Finau', 'Caleb Gaseata', 'Cassius Nordermeer', 'Cruise Hita', 'Daz Adams',
                    'Dil Samraat Singh Sandhar', 'Eli Fong', 'Falcon Rissetto', 'Holden O\'Brien', 'JACOB GREATBATCH',
                    'Jalen Moore-Utai', 'James Kumar', 'Jordan Bentley', 'joseph asiata', 'Julius-Zion Seu',
                    'Levi Bentley', 'Louis Goodhue', 'Maison Segi', 'Malaki Peniamina', 'Mecaiah Fifita',
                    'Moses Fiaola', 'Samuel Pulu', 'Selafi Chu Ling', 'SJ Poi', 'troy hohua'
                ],
                'Cadet Boys Group 1' => [
                    'Orlando Tapuni', 'Kaedynn Tito', 'Maika Hart', 'Solomon Asekona', 'Nathaniel Otte',
                    'Joseph Phillips', 'Misilusi Lameko', 'Rehaan Akhonzada', 'Pereme Porter Te Anini', 'Ayden Warren',
                    'Hector Jones', 'Trevor Pulu', 'Jayden bezuidenhout', 'Brody Slater-Harris', 'Ayden Chan',
                    'Dylan Renner', 'Zentan Richardson', 'Lucas Jerry', 'Adam Misikea', 'Wirihana Taurere'
                ],
                'Cadet Boys Group 2' => [
                    'Blake Bryan', 'Daniel Segi Segi', 'Jayden Kumar', 'Kingston Katu', 'Hilly Bentley',
                    'Marley Brunt-Mahe', 'Matthias Brunt-Mahe', 'Ashton Hodgson', 'Nicolas Gomes Silva', 'Samuel cole',
                    'Joshua Tuisamoa', 'Daymin (MDCAT) PERENARA', 'Anton Falstie-Jensen', 'Karlisle Ochoa', 'Ryder Noble',
                    'Kaleb Cuff', 'Ivan Keestra May', 'Miguel Rivera'
                ],
                'Youth Boys Group 1' => [
                    'Cristiano Tavai', 'Hala Houma', 'Xarisma Paga', 'Demetrius Gagau', 'TJ (Taulaga Junior) Auimatagi',
                    'Livingstone Lesatele', 'Tavita Fesolai', 'Andre Bunton', 'Elijah Holt', 'Charles Bloomfield',
                    'Sebastian Grey', 'Matthew Arnold', 'Boston Dovey', 'Logan Blanch', 'Joel Mccabe',
                    'Angelo Faasavalu', 'Joel Bloomfield', 'Calan Boyd', 'Ace Jovanov', 'Matthew Victor Siua-King',
                    'Dion-Grace Palemene', 'Fiston Iradukunda', 'Michael Yan', 'Ethan Hunter', 'Matthew Taylor',
                    'Tamati Hart', 'Maika Hart', 'Brandon Condren', 'Isaiah Alofa', 'Sebastian Aualiitia',
                    'Luca Ogilvy', 'Milo O\'Brien', 'Sev Tolhurst', 'Isaac Carr', 'AmirAta Farzami',
                    'Ash Archibald', 'kai maxwell', 'Teina William Milton Edwards', 'Rahmat Ahmadi', 'Ryan Harding',
                    'sisi fononga', 'Mosese Houma', 'mathew lakalaka', 'Giovanni Vehikite', 'Casey Mitchell', 'westcityboxing'
                ],
                'Youth Boys Group 2' => [
                    'Arya Sukesh', 'Niue Raymond Tulafono', 'Hunter Saaga', 'Kalo Wood', 'Juneyt Wiki-Misipeka',
                    'Andrew Martin', 'Dayton Dawson', 'Andreas Dawson', 'Jared Lucas', 'Parth Tailor',
                    'Jay Howarth', 'Emerson Brooks', 'elijah - Kaitinang Redfern', 'Matthias Luani', 'Setty Salaivao',
                    'Lucas Atui', 'Husayn Nohotahi', 'Israel Hall', 'Eli Hall', 'Maya Davis',
                    'Tioni Rae', 'George Hales', 'Haini Sualauvi Mikaio', 'Loveti Toutai', 'Luke Iwikau-Poi',
                    'Aslam Harris', 'Elyh ASHBY', 'Tehetekia Whitney'
                ],
                'Mini Cadets Girls Group 1' => [
                    'Ariya Kumar', 'Zahlia Thomas', 'Naira Maiava', 'Ocean Afualo', 'Rainbow Smoothy',
                    'Capri Hunuki', 'Ella-Rose Green', 'Caris Gage', 'Princess Tuivanu', 'Paige Archibald',
                    'Jayda Thony', 'Caris Gage', 'Ocean Talaoloa', 'Jeanie Park', 'Amelia Harris', 'Zainab Faizy',
                    'Anika Craig', 'Astary Hetaraka', 'Ngaio Te Whiu'
                ],
                'Youth Girls Group 1' => [
                    'Dakota Hunuki', 'Supriya Mistry', 'Navarah Browne', 'Emma Lenehan', 'Angelee Faasavalu',
                    'Dayna Segi', 'Crystal Kainamu', 'Jasmine Hamilton-Momberg', 'Laveycia Tolepai', 'Jamie Cawdron',
                    'Giovanna Gomes Silva', 'Lana Menary', 'Sophie Salesa', 'Aaliyah Nelson', 'letoya fernandez',
                    'Huia Wanoa Urquhart', 'Kaylamesha Fahiua', 'Iliana Fotu', 'Tanika Wulf', 'skyla winstanley',
                    'Yana Toi', 'Eman Ata', 'Sam Hooper', 'Kapiri Winchester', 'Chardonnay Raukawa',
                    'Kaeis Aiono-Butler', 'Makarita Te Rore', 'Stevie-lynn Rapihana', 'Amanda Roy', 'Alvina Roy',
                    'Lucy Clark', 'Rahera Tatana', 'Paige Cassidy'
                ]
            ];



            // Function to normalize names for comparison
            function normalize_name($name) {
                // Remove punctuation and convert to lowercase
                $normalized = trim(strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
                // Replace multiple spaces with single space
                $normalized = preg_replace('/\s+/', ' ', $normalized);
                return trim($normalized);
            }

            // Get current members by group
            $comparison_results = [];
            foreach ($expected_members as $group_name => $expected_list) {
                // Initialize default result
                $comparison_results[$group_name] = [
                    'expected_count' => count($expected_list),
                    'current_count' => 0,
                    'found_count' => 0,
                    'missing' => $expected_list, // Default: all missing
                    'extra' => [],
                    'found' => []
                ];

                // Find the group - exact matching
                $group = null;
                foreach ($groups as $g) {
                    if (strcasecmp($g->post_title, $group_name) === 0) {
                        $group = $g;
                        break;
                    }
                }

                if (!$group) {
                    // Group not found - all members are missing
                    continue;
                }

                // Use the EXACT same logic as the working filter
                $group_memberships = wcb_get_group_memberships($group->ID);
                $current_members = [];

                if (!empty($group_memberships)) {
                    $membership_ids = array_map(function($m) { return $m->ID; }, $group_memberships);
                    $placeholders = implode(',', array_fill(0, count($membership_ids), '%d'));

                    // Get members who have active transactions for memberships in this group
                    // Use EXACT same query as the filter
                    $group_members = $wpdb->get_results($wpdb->prepare("
                        SELECT DISTINCT u.ID, u.display_name
                        FROM {$wpdb->users} u
                        JOIN {$txn_table} t ON u.ID = t.user_id
                        WHERE t.product_id IN ({$placeholders})
                        AND t.status IN ('confirmed', 'complete')
                        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                        AND u.user_login != 'bwgdev'
                        ORDER BY u.ID
                    ", ...$membership_ids));

                    $current_members = array_column($group_members, 'display_name');
                } else {
                    // No memberships in group - all members are missing
                    continue;
                }

                // Now do the comparison
                $missing_from_system = [];
                $extra_in_system = [];
                $found_in_system = [];

                // Find missing members - improved logic for duplicate names
                $current_members_copy = $current_members; // Make a copy to track used matches

                foreach ($expected_list as $expected_name) {
                    $found = false;
                    foreach ($current_members_copy as $index => $current_name) {
                        if (normalize_name($expected_name) === normalize_name($current_name)) {
                            $found_in_system[] = $expected_name;
                            $found = true;
                            // Remove this match so it can't be used again (handles duplicates)
                            unset($current_members_copy[$index]);
                            break;
                        }
                    }
                    if (!$found) {
                        $missing_from_system[] = $expected_name;
                    }
                }

                // Find extra members - use remaining unmatched members
                $extra_in_system = array_values($current_members_copy);

                // Update the results
                $comparison_results[$group_name] = [
                    'expected_count' => count($expected_list),
                    'current_count' => count($current_members),
                    'found_count' => count($found_in_system),
                    'missing' => $missing_from_system,
                    'extra' => $extra_in_system,
                    'found' => $found_in_system
                ];
            }
            ?>

            <div style="background: #e3f2fd; border: 1px solid #bbdefb; border-left: 4px solid #2196f3; padding: 20px; border-radius: 6px; margin: 30px 0;">
                <h3 style="margin-top: 0; color: #1565c0;">📊 Member List Comparison</h3>
                <p>Comparing expected member list with current system data:</p>

                <?php foreach ($comparison_results as $group_name => $result): ?>
                    <div style="background: #fff; border: 1px solid #e9ecef; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <h4 style="margin-top: 0; color: #212529;"><?php echo esc_html($group_name); ?></h4>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 15px 0;">
                            <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #6c757d;"><?php echo $result['expected_count']; ?></div>
                                <div style="font-size: 12px; color: #6c757d;">Expected</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #007bff;"><?php echo $result['current_count']; ?></div>
                                <div style="font-size: 12px; color: #6c757d;">In System</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: #d4edda; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;"><?php echo $result['found_count']; ?></div>
                                <div style="font-size: 12px; color: #155724;">Found</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: #f8d7da; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #dc3545;"><?php echo count($result['missing']); ?></div>
                                <div style="font-size: 12px; color: #721c24;">Missing</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: #fff3cd; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #856404;"><?php echo count($result['extra']); ?></div>
                                <div style="font-size: 12px; color: #856404;">Extra</div>
                            </div>
                        </div>

                        <?php if (!empty($result['missing'])): ?>
                            <div style="margin: 10px 0;">
                                <strong style="color: #dc3545;">Missing from System:</strong>
                                <div style="background: #f8d7da; padding: 10px; border-radius: 4px; margin: 5px 0;">
                                    <?php echo implode(', ', array_map('esc_html', $result['missing'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($result['extra'])): ?>
                            <div style="margin: 10px 0;">
                                <strong style="color: #856404;">Extra in System:</strong>
                                <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin: 5px 0;">
                                    <?php echo implode(', ', array_map('esc_html', $result['extra'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <p><small><strong>Note:</strong> This comparison uses fuzzy name matching to account for variations in spelling and formatting.</small></p>
            </div>

            <!-- Summary Comparison -->
            <?php
            // Calculate totals - ONLY from the 7 defined groups
            $total_airtable_members = array_sum(array_map('count', $expected_members));

            // Calculate MemberPress total ONLY from the 7 defined groups
            // Use found_count which properly handles duplicate names
            $total_memberpress_from_groups = 0;
            foreach ($comparison_results as $group_name => $result) {
                $total_memberpress_from_groups += $result['found_count'];
            }
            $total_memberpress_members = $total_memberpress_from_groups;

            // Debug: Check what's in comparison_results
            $debug_info = [];
            $total_found_members = 0;
            $total_missing_members = 0;
            $total_extra_members = 0;

            foreach ($comparison_results as $group_name => $result) {
                $found_count = isset($result['found_count']) ? $result['found_count'] : 0;
                $missing_count = isset($result['missing']) ? count($result['missing']) : 0;
                $extra_count = isset($result['extra']) ? count($result['extra']) : 0;

                $total_found_members += $found_count;
                $total_missing_members += $missing_count;
                $total_extra_members += $extra_count;

                $debug_info[] = "{$group_name}: Found={$found_count}, Missing={$missing_count}, Extra={$extra_count}";
            }

            // Add debug comment (will be visible in HTML source)
            echo "<!-- DEBUG: Groups processed: " . count($comparison_results) . " -->\n";
            echo "<!-- DEBUG: " . implode(' | ', $debug_info) . " -->\n";

            // Update the top stat with the official active member count
            echo "<script>document.getElementById('official-active-count').textContent = '{$total_memberpress_members}';</script>\n";

            // Get all missing and extra members with details
            $all_missing_members = [];
            $all_extra_members_detailed = [];
            $all_group_members = [];

            // First, collect all members found in groups
            foreach ($comparison_results as $group_name => $result) {
                foreach ($result['missing'] as $missing) {
                    $all_missing_members[] = $missing . ' (' . $group_name . ')';
                }
                foreach ($result['extra'] as $extra) {
                    // Get detailed info for extra members
                    $member_details = $wpdb->get_row($wpdb->prepare("
                        SELECT u.ID, u.user_email, u.user_registered, t.created_at, t.expires_at, p.post_title as membership_name
                        FROM {$wpdb->users} u
                        JOIN {$txn_table} t ON u.ID = t.user_id
                        JOIN {$wpdb->posts} p ON t.product_id = p.ID
                        WHERE u.display_name = %s
                        AND t.status IN ('confirmed', 'complete')
                        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
                        AND u.user_login != 'bwgdev'
                        ORDER BY t.created_at DESC
                        LIMIT 1
                    ", $extra));

                    $all_extra_members_detailed[] = [
                        'name' => $extra,
                        'group' => $group_name,
                        'details' => $member_details
                    ];

                    if ($member_details) {
                        $all_group_members[$member_details->ID] = true;
                    }
                }

                // Also track found members
                foreach ($result['found'] as $found) {
                    $found_member = $wpdb->get_row($wpdb->prepare("
                        SELECT u.ID
                        FROM {$wpdb->users} u
                        WHERE u.display_name = %s
                        AND u.user_login != 'bwgdev'
                        LIMIT 1
                    ", $found));
                    if ($found_member) {
                        $all_group_members[$found_member->ID] = true;
                    }
                }
            }

            // Don't include members not in defined groups for this comparison
            // We only want to compare the 7 specific groups, not all active members

            $all_extra_members = array_map(function($item) {
                return $item['name'] . ' (' . $item['group'] . ')';
            }, $all_extra_members_detailed);

            $difference = $total_memberpress_members - $total_airtable_members;
            ?>

            <div style="background: #f8f9fa; border: 2px solid #dee2e6; padding: 25px; border-radius: 8px; margin: 30px 0;">
                <h3 style="margin-top: 0; color: #212529; text-align: center;">📋 Airtable vs MemberPress Summary</h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                    <div style="text-align: center; padding: 20px; background: #e3f2fd; border-radius: 6px; border: 2px solid #2196f3;">
                        <div style="font-size: 36px; font-weight: bold; color: #1565c0;"><?php echo $total_airtable_members; ?></div>
                        <div style="font-size: 14px; color: #1565c0; font-weight: 600;">AIRTABLE MEMBERS</div>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">(Expected Target)</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #e8f5e8; border-radius: 6px; border: 2px solid #4caf50;">
                        <div style="font-size: 36px; font-weight: bold; color: #2e7d32;"><?php echo $total_memberpress_members; ?></div>
                        <div style="font-size: 14px; color: #2e7d32; font-weight: 600;">MEMBERPRESS MEMBERS</div>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">(Current Active)</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: <?php echo $difference > 0 ? '#fff3e0' : '#ffebee'; ?>; border-radius: 6px; border: 2px solid <?php echo $difference > 0 ? '#ff9800' : '#f44336'; ?>;">
                        <div style="font-size: 36px; font-weight: bold; color: <?php echo $difference > 0 ? '#f57c00' : '#c62828'; ?>;">
                            <?php echo $difference > 0 ? '+' : ''; ?><?php echo $difference; ?>
                        </div>
                        <div style="font-size: 14px; color: <?php echo $difference > 0 ? '#f57c00' : '#c62828'; ?>; font-weight: 600;">DIFFERENCE</div>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                            <?php if ($difference > 0): ?>
                                (MemberPress has MORE)
                            <?php elseif ($difference < 0): ?>
                                (MemberPress has LESS)
                            <?php else: ?>
                                (Perfect Match!)
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 6px; margin: 20px 0; border: 1px solid #dee2e6;">
                    <h4 style="margin-top: 0; color: #212529;">🔍 Analysis & Action Required:</h4>

                    <?php if ($difference > 0): ?>
                        <div style="background: #fff3e0; padding: 15px; border-radius: 4px; border-left: 4px solid #ff9800; margin: 10px 0;">
                            <strong style="color: #f57c00;">MemberPress has <?php echo $difference; ?> MORE members than Airtable</strong>
                            <p style="margin: 10px 0 0 0;">This means you have extra active members in MemberPress that aren't in your Airtable list.</p>
                        </div>
                    <?php elseif ($difference < 0): ?>
                        <div style="background: #ffebee; padding: 15px; border-radius: 4px; border-left: 4px solid #f44336; margin: 10px 0;">
                            <strong style="color: #c62828;">MemberPress has <?php echo abs($difference); ?> FEWER members than Airtable</strong>
                            <p style="margin: 10px 0 0 0;">This means some expected members from your Airtable list are missing from MemberPress.</p>
                        </div>
                    <?php else: ?>
                        <div style="background: #e8f5e8; padding: 15px; border-radius: 4px; border-left: 4px solid #4caf50; margin: 10px 0;">
                            <strong style="color: #2e7d32;">Perfect Match! Both systems have the same number of members.</strong>
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                        <div>
                            <h5 style="color: #dc3545; margin-bottom: 10px;">❌ Missing from MemberPress (<?php echo $total_missing_members; ?> members):</h5>
                            <?php if (!empty($all_missing_members)): ?>
                                <div style="background: #f8d7da; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto; font-size: 13px;">
                                    <?php foreach ($all_missing_members as $missing): ?>
                                        <div style="margin: 2px 0;">• <?php echo esc_html($missing); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <p style="font-size: 12px; color: #721c24; margin: 10px 0 0 0;">
                                    <strong>Action:</strong> These members need to be added to MemberPress or their memberships activated.
                                </p>
                            <?php else: ?>
                                <div style="color: #28a745; font-style: italic;">All Airtable members found in MemberPress! ✅</div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h5 style="color: #856404; margin-bottom: 10px;">➕ Extra in MemberPress (<?php echo $total_extra_members; ?> members):</h5>
                            <?php if (!empty($all_extra_members_detailed)): ?>
                                <div style="background: #fff3cd; padding: 15px; border-radius: 4px; max-height: 400px; overflow-y: auto;">
                                    <?php foreach ($all_extra_members_detailed as $extra_member): ?>
                                        <div style="background: #fff; border: 1px solid #e9ecef; padding: 12px; margin: 8px 0; border-radius: 4px; font-size: 13px;">
                                            <div style="font-weight: 600; color: #856404; margin-bottom: 5px;">
                                                <?php echo esc_html($extra_member['name']); ?>
                                            </div>
                                            <div style="color: #6c757d; font-size: 12px;">
                                                <strong>Group:</strong> <?php echo esc_html($extra_member['group']); ?><br>
                                                <?php if ($extra_member['details']): ?>
                                                    <strong>Email:</strong> <?php echo esc_html($extra_member['details']->user_email ?: 'No email'); ?><br>
                                                    <strong>User ID:</strong> <?php echo $extra_member['details']->ID; ?><br>
                                                    <strong>Membership:</strong> <?php echo esc_html($extra_member['details']->membership_name); ?><br>
                                                    <strong>Started:</strong> <?php echo date('M j, Y', strtotime($extra_member['details']->created_at)); ?><br>
                                                    <strong>Expires:</strong>
                                                    <?php if ($extra_member['details']->expires_at && $extra_member['details']->expires_at !== '0000-00-00 00:00:00'): ?>
                                                        <?php echo date('M j, Y', strtotime($extra_member['details']->expires_at)); ?>
                                                    <?php else: ?>
                                                        Never
                                                    <?php endif; ?><br>
                                                    <strong>Registered:</strong> <?php echo date('M j, Y', strtotime($extra_member['details']->user_registered)); ?>
                                                <?php else: ?>
                                                    <em>Member details not found</em>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p style="font-size: 12px; color: #856404; margin: 10px 0 0 0;">
                                    <strong>Action:</strong> Review these members - they might need to be moved to different groups, added to Airtable, or removed from MemberPress.
                                </p>
                            <?php else: ?>
                                <div style="color: #28a745; font-style: italic;">No extra members in MemberPress! ✅</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="background: #e7f3ff; padding: 15px; border-radius: 4px; border-left: 4px solid #2196f3; margin: 20px 0;">
                        <h5 style="margin-top: 0; color: #1565c0;">📝 To Match Airtable Exactly:</h5>
                        <ol style="margin: 10px 0; padding-left: 20px; color: #1565c0;">
                            <li><strong>Add Missing Members:</strong> Create MemberPress accounts for the <?php echo $total_missing_members; ?> missing members</li>
                            <li><strong>Review Extra Members:</strong> Decide what to do with the <?php echo $total_extra_members; ?> extra members</li>
                            <li><strong>Verify Groups:</strong> Ensure all members are in the correct groups</li>
                            <li><strong>Update Airtable:</strong> If extra members are valid, add them to your Airtable list</li>
                        </ol>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
