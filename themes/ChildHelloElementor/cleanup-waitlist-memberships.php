<?php
/**
 * Waitlist Membership Cleanup Tool
 *
 * Usage: Access via browser at
 * /wp-content/themes/ChildHelloElementor/cleanup-waitlist-memberships.php
 *
 * Rules:
 * - Remove active waitlist memberships created on or before the cutoff date
 * - Keep protected members from the cutoff cleanup
 * - Still remove any active waitlist membership when the member already has another active non-waitlist membership
 */

if (file_exists('../../../wp-load.php')) {
    require_once '../../../wp-load.php';
} elseif (file_exists('../../../../wp-load.php')) {
    require_once '../../../../wp-load.php';
} elseif (file_exists('../../../../../wp-load.php')) {
    require_once '../../../../../wp-load.php';
} else {
    die('Could not locate wp-load.php.');
}

class WCB_Waitlist_Membership_Cleanup_Tool {

    private $default_cutoff_date = '2025-03-16';
    private $default_protected_members = "Navala Leupolu";
    private $cutoff_date = '';
    private $protected_members_text = '';
    private $analysis = null;
    private $apply_results = null;
    private $errors = [];

    public function __construct() {
        $this->check_permissions();

        $this->cutoff_date = $this->default_cutoff_date;
        $this->protected_members_text = $this->default_protected_members;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_request();
        }

        $this->render_interface();
    }

    private function check_permissions() {
        if (!function_exists('current_user_can')) {
            wp_die('WordPress did not load correctly.');
        }

        if (!is_user_logged_in()) {
            $this->render_login_message();
            exit;
        }

        if (!current_user_can('manage_options')) {
            wp_die('Access denied. Administrator privileges required.');
        }
    }

    private function handle_request() {
        $action = isset($_POST['action']) ? sanitize_text_field(wp_unslash($_POST['action'])) : '';

        if (!in_array($action, ['preview_cleanup', 'apply_cleanup'], true)) {
            return;
        }

        if (!isset($_POST['wcb_waitlist_cleanup_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wcb_waitlist_cleanup_nonce'])), 'wcb_waitlist_cleanup')) {
            $this->errors[] = 'Security check failed. Please reload and try again.';
            return;
        }

        $this->cutoff_date = $this->sanitize_cutoff_date(isset($_POST['cutoff_date']) ? wp_unslash($_POST['cutoff_date']) : $this->default_cutoff_date);
        $this->protected_members_text = isset($_POST['protected_members'])
            ? sanitize_textarea_field(wp_unslash($_POST['protected_members']))
            : $this->default_protected_members;

        $this->analysis = $this->analyze_waitlist_members($this->cutoff_date, $this->protected_members_text);

        if ($action === 'apply_cleanup' && empty($this->errors)) {
            $this->apply_results = $this->apply_cleanup($this->analysis['targets']);
            $this->analysis = $this->analyze_waitlist_members($this->cutoff_date, $this->protected_members_text);
        }
    }

    private function sanitize_cutoff_date($date_string) {
        $date_string = sanitize_text_field($date_string);
        $date = DateTime::createFromFormat('Y-m-d', $date_string);

        if (!$date || $date->format('Y-m-d') !== $date_string) {
            return $this->default_cutoff_date;
        }

        return $date_string;
    }

    private function analyze_waitlist_members($cutoff_date, $protected_members_text) {
        global $wpdb;

        $txn_table = $wpdb->prefix . 'mepr_transactions';
        $subs_table = $wpdb->prefix . 'mepr_subscriptions';
        $timezone = wp_timezone();
        $cutoff_utc = $this->get_cutoff_utc_end($cutoff_date, $timezone);
        $protected_identifiers = $this->parse_protected_members($protected_members_text);

        $rows = $wpdb->get_results(
            "SELECT t.id AS transaction_id,
                    t.user_id,
                    t.product_id,
                    t.subscription_id,
                    t.status AS transaction_status,
                    t.gateway,
                    t.created_at,
                    t.expires_at,
                    u.display_name,
                    u.user_email,
                    u.user_login,
                    p.post_title AS waitlist_membership,
                    s.status AS subscription_status,
                    s.product_id AS subscription_product_id,
                    EXISTS(
                        SELECT 1
                        FROM {$txn_table} t2
                        JOIN {$wpdb->posts} p2 ON p2.ID = t2.product_id
                        WHERE t2.user_id = t.user_id
                        AND t2.id != t.id
                        AND t2.status IN ('confirmed', 'complete')
                        AND (t2.expires_at IS NULL OR t2.expires_at > NOW() OR t2.expires_at = '0000-00-00 00:00:00')
                        AND p2.post_type = 'memberpressproduct'
                        AND p2.post_title NOT LIKE '%waitlist%'
                    ) AS has_other_active_membership,
                    (
                        SELECT GROUP_CONCAT(DISTINCT p2.post_title ORDER BY p2.post_title SEPARATOR ' | ')
                        FROM {$txn_table} t2
                        JOIN {$wpdb->posts} p2 ON p2.ID = t2.product_id
                        WHERE t2.user_id = t.user_id
                        AND t2.id != t.id
                        AND t2.status IN ('confirmed', 'complete')
                        AND (t2.expires_at IS NULL OR t2.expires_at > NOW() OR t2.expires_at = '0000-00-00 00:00:00')
                        AND p2.post_type = 'memberpressproduct'
                        AND p2.post_title NOT LIKE '%waitlist%'
                    ) AS other_active_memberships
             FROM {$txn_table} t
             JOIN {$wpdb->users} u ON u.ID = t.user_id
             JOIN {$wpdb->posts} p ON p.ID = t.product_id
             LEFT JOIN {$subs_table} s ON s.id = t.subscription_id
             WHERE t.status IN ('confirmed', 'complete')
             AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
             AND p.post_type = 'memberpressproduct'
             AND p.post_title LIKE '%waitlist%'
             AND u.user_login != 'bwgdev'
             ORDER BY t.created_at ASC, u.display_name ASC"
        );

        $targets = [];
        $kept = [];
        $stats = [
            'total_active_waitlist' => 0,
            'targeted_count' => 0,
            'kept_count' => 0,
            'cutoff_rule_count' => 0,
            'other_active_rule_count' => 0,
            'protected_kept_count' => 0,
            'newer_kept_count' => 0,
        ];

        foreach ($rows as $row) {
            $stats['total_active_waitlist']++;

            $created_at_utc = $this->parse_utc_datetime($row->created_at);
            $joined_before_cutoff = $created_at_utc ? ($created_at_utc <= $cutoff_utc) : false;
            $has_other_active = !empty($row->has_other_active_membership);
            $is_protected = $this->matches_protected_member($row, $protected_identifiers);

            $record = [
                'transaction_id' => (int) $row->transaction_id,
                'user_id' => (int) $row->user_id,
                'product_id' => (int) $row->product_id,
                'subscription_id' => $row->subscription_id ? (int) $row->subscription_id : 0,
                'display_name' => $row->display_name ?: 'No Name',
                'user_email' => $row->user_email,
                'user_login' => $row->user_login,
                'waitlist_membership' => $row->waitlist_membership,
                'transaction_status' => $row->transaction_status,
                'gateway' => $row->gateway,
                'joined_waitlist' => $this->format_local_datetime($row->created_at, $timezone),
                'joined_waitlist_sort' => $row->created_at,
                'joined_before_cutoff' => $joined_before_cutoff,
                'has_other_active_membership' => $has_other_active,
                'other_active_memberships' => $row->other_active_memberships ?: '',
                'subscription_status' => $row->subscription_status ?: '',
                'subscription_matches_product' => !empty($row->subscription_id) && (int) $row->subscription_product_id === (int) $row->product_id,
                'is_protected' => $is_protected,
                'should_cancel_subscription' => !empty($row->subscription_id)
                    && in_array($row->subscription_status, ['active', 'suspended'], true)
                    && (int) $row->subscription_product_id === (int) $row->product_id,
            ];

            if ($has_other_active) {
                $record['cleanup_reason'] = 'Has another active non-waitlist membership';
                $record['cleanup_rule'] = 'other_active_membership';
                $targets[] = $record;
                $stats['targeted_count']++;
                $stats['other_active_rule_count']++;
                continue;
            }

            if ($joined_before_cutoff && !$is_protected) {
                $record['cleanup_reason'] = 'Joined waitlist on or before cutoff date';
                $record['cleanup_rule'] = 'cutoff_date';
                $targets[] = $record;
                $stats['targeted_count']++;
                $stats['cutoff_rule_count']++;
                continue;
            }

            if ($is_protected) {
                $record['keep_reason'] = 'Protected member exception';
                $stats['protected_kept_count']++;
            } else {
                $record['keep_reason'] = 'Joined after cutoff date and has no other active membership';
                $stats['newer_kept_count']++;
            }

            $kept[] = $record;
            $stats['kept_count']++;
        }

        return [
            'cutoff_date' => $cutoff_date,
            'cutoff_utc' => $cutoff_utc->format('Y-m-d H:i:s'),
            'protected_members' => $protected_identifiers,
            'targets' => $targets,
            'kept' => $kept,
            'stats' => $stats,
        ];
    }

    private function apply_cleanup($targets) {
        global $wpdb;

        if (empty($targets)) {
            return [
                'cleanup_utc' => '',
                'cleanup_local' => '',
                'transactions_updated' => 0,
                'subscriptions_cancelled' => 0,
                'rows' => [],
                'errors' => [],
            ];
        }

        $txn_table = $wpdb->prefix . 'mepr_transactions';
        $subs_table = $wpdb->prefix . 'mepr_subscriptions';
        $timezone = wp_timezone();
        $cleanup_utc = gmdate('Y-m-d H:i:s', time() - 60);
        $cleanup_local = $this->format_local_datetime($cleanup_utc, $timezone);
        $transactions_updated = 0;
        $subscriptions_cancelled = 0;
        $rows = [];
        $errors = [];
        $handled_subscription_ids = [];

        foreach ($targets as $target) {
            $row_result = [
                'transaction_id' => $target['transaction_id'],
                'user_id' => $target['user_id'],
                'display_name' => $target['display_name'],
                'user_email' => $target['user_email'],
                'waitlist_membership' => $target['waitlist_membership'],
                'cleanup_reason' => $target['cleanup_reason'],
                'transaction_updated' => false,
                'subscription_cancelled' => false,
                'subscription_status' => $target['subscription_status'],
                'error' => '',
            ];

            $updated = $wpdb->update(
                $txn_table,
                ['expires_at' => $cleanup_utc],
                ['id' => $target['transaction_id']],
                ['%s'],
                ['%d']
            );

            if ($updated === false) {
                $row_result['error'] = 'Failed to expire transaction: ' . $wpdb->last_error;
                $errors[] = 'Txn ' . $target['transaction_id'] . ': ' . $wpdb->last_error;
                $rows[] = $row_result;
                continue;
            }

            $row_result['transaction_updated'] = true;
            $transactions_updated++;

            if ($target['should_cancel_subscription'] && !isset($handled_subscription_ids[$target['subscription_id']])) {
                $subscription_update = $wpdb->update(
                    $subs_table,
                    ['status' => 'cancelled'],
                    [
                        'id' => $target['subscription_id'],
                        'product_id' => $target['product_id'],
                    ],
                    ['%s'],
                    ['%d', '%d']
                );

                if ($subscription_update === false) {
                    $row_result['error'] = trim($row_result['error'] . ' Failed to cancel subscription: ' . $wpdb->last_error);
                    $errors[] = 'Subscription ' . $target['subscription_id'] . ': ' . $wpdb->last_error;
                } else {
                    $row_result['subscription_cancelled'] = true;
                    $subscriptions_cancelled++;
                    $handled_subscription_ids[$target['subscription_id']] = true;
                }
            }

            $rows[] = $row_result;
        }

        return [
            'cleanup_utc' => $cleanup_utc,
            'cleanup_local' => $cleanup_local,
            'transactions_updated' => $transactions_updated,
            'subscriptions_cancelled' => $subscriptions_cancelled,
            'rows' => $rows,
            'errors' => $errors,
        ];
    }

    private function parse_protected_members($protected_members_text) {
        $lines = preg_split('/\r\n|\r|\n/', (string) $protected_members_text);
        $identifiers = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $identifiers[] = $line;
            }
        }

        return $identifiers;
    }

    private function matches_protected_member($row, $protected_identifiers) {
        if (empty($protected_identifiers)) {
            return false;
        }

        $display_name = $this->normalize_match_string($row->display_name);
        $email = $this->normalize_match_string($row->user_email);
        $login = $this->normalize_match_string($row->user_login);
        $user_id = (int) $row->user_id;

        foreach ($protected_identifiers as $identifier) {
            $identifier = trim($identifier);
            if ($identifier === '') {
                continue;
            }

            if (ctype_digit($identifier) && (int) $identifier === $user_id) {
                return true;
            }

            $normalized_identifier = $this->normalize_match_string($identifier);

            if ($normalized_identifier === $display_name || $normalized_identifier === $email || $normalized_identifier === $login) {
                return true;
            }

            $tokens = array_filter(explode(' ', $normalized_identifier));

            if (!empty($tokens)) {
                $display_match = true;
                $email_match = true;
                $login_match = true;

                foreach ($tokens as $token) {
                    if (strpos($display_name, $token) === false) {
                        $display_match = false;
                    }
                    if (strpos($email, $token) === false) {
                        $email_match = false;
                    }
                    if (strpos($login, $token) === false) {
                        $login_match = false;
                    }
                }

                if ($display_match || $email_match || $login_match) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalize_match_string($value) {
        $value = strtolower((string) $value);
        $value = preg_replace('/[^a-z0-9@.\s]/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }

    private function get_cutoff_utc_end($cutoff_date, DateTimeZone $timezone) {
        $cutoff = new DateTime($cutoff_date . ' 23:59:59', $timezone);
        $cutoff->setTimezone(new DateTimeZone('UTC'));
        return $cutoff;
    }

    private function parse_utc_datetime($datetime_string) {
        if (empty($datetime_string) || $datetime_string === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return new DateTime($datetime_string, new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return null;
        }
    }

    private function format_local_datetime($utc_datetime, DateTimeZone $timezone) {
        if (empty($utc_datetime) || $utc_datetime === '0000-00-00 00:00:00') {
            return 'Not set';
        }

        try {
            $date = new DateTime($utc_datetime, new DateTimeZone('UTC'));
            $date->setTimezone($timezone);
            return $date->format('d M Y g:i a');
        } catch (Exception $e) {
            return $utc_datetime;
        }
    }

    private function render_interface() {
        $stats = $this->analysis ? $this->analysis['stats'] : null;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Waitlist Membership Cleanup</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #f1f1f1;
                    color: #1d2327;
                    margin: 0;
                    padding: 24px;
                }
                .wrap {
                    max-width: 1400px;
                    margin: 0 auto;
                }
                .card {
                    background: #fff;
                    border: 1px solid #dcdcde;
                    border-radius: 8px;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                    padding: 24px;
                    margin-bottom: 20px;
                }
                h1, h2, h3 {
                    margin-top: 0;
                }
                .description {
                    color: #50575e;
                    margin-bottom: 0;
                }
                .info-box,
                .error-box,
                .success-box,
                .warning-box {
                    border-radius: 6px;
                    padding: 14px 16px;
                    margin-bottom: 16px;
                }
                .info-box {
                    background: #eef6ff;
                    border: 1px solid #b6d4fe;
                }
                .error-box {
                    background: #fcf0f1;
                    border: 1px solid #f1aeb5;
                }
                .success-box {
                    background: #edf7ed;
                    border: 1px solid #a3cfbb;
                }
                .warning-box {
                    background: #fff8e5;
                    border: 1px solid #ffe08a;
                }
                .grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                    gap: 12px;
                    margin-top: 16px;
                }
                .stat {
                    background: #f6f7f7;
                    border-radius: 6px;
                    padding: 14px;
                }
                .stat strong {
                    display: block;
                    font-size: 24px;
                    margin-bottom: 4px;
                }
                label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 6px;
                }
                input[type="date"],
                textarea {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid #8c8f94;
                    border-radius: 4px;
                    box-sizing: border-box;
                    font: inherit;
                }
                textarea {
                    min-height: 80px;
                    resize: vertical;
                }
                .field {
                    margin-bottom: 16px;
                }
                .button-row {
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                    margin-top: 16px;
                }
                .button {
                    display: inline-block;
                    border: 1px solid #2271b1;
                    border-radius: 4px;
                    background: #2271b1;
                    color: #fff;
                    padding: 10px 16px;
                    cursor: pointer;
                    text-decoration: none;
                    font: inherit;
                }
                .button.secondary {
                    background: #fff;
                    color: #2271b1;
                }
                .button.danger {
                    background: #b32d2e;
                    border-color: #b32d2e;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 14px;
                    font-size: 13px;
                }
                th,
                td {
                    border-bottom: 1px solid #dcdcde;
                    padding: 10px 12px;
                    text-align: left;
                    vertical-align: top;
                }
                th {
                    background: #f6f7f7;
                    position: sticky;
                    top: 0;
                    z-index: 1;
                }
                .table-wrap {
                    overflow: auto;
                    max-height: 520px;
                    border: 1px solid #dcdcde;
                    border-radius: 6px;
                }
                .tag {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 999px;
                    font-size: 12px;
                    line-height: 1.4;
                    background: #e7f1ff;
                    color: #0a4b78;
                }
                .tag.warn {
                    background: #fff3cd;
                    color: #664d03;
                }
                .tag.ok {
                    background: #d1e7dd;
                    color: #0f5132;
                }
                .tag.muted {
                    background: #e2e3e5;
                    color: #41464b;
                }
                .muted {
                    color: #646970;
                }
                .small {
                    font-size: 12px;
                }
                ul.rule-list {
                    margin: 10px 0 0 18px;
                }
            </style>
        </head>
        <body>
            <div class="wrap">
                <div class="card">
                    <h1>Waitlist Membership Cleanup</h1>
                    <p class="description">Preview and remove stale MemberPress waitlist memberships directly on live. This only targets waitlist memberships and leaves any other active memberships in place.</p>
                    <ul class="rule-list">
                        <li>Remove active waitlist memberships created on or before the cutoff date.</li>
                        <li>Keep protected members out of that cutoff cleanup.</li>
                        <li>Still remove waitlist memberships for anyone who already has another active non-waitlist membership.</li>
                    </ul>
                </div>

                <?php foreach ($this->errors as $error) : ?>
                    <div class="error-box"><?php echo esc_html($error); ?></div>
                <?php endforeach; ?>

                <?php if ($this->apply_results) : ?>
                    <div class="card">
                        <h2>Apply Results</h2>
                        <div class="success-box">
                            <strong>Cleanup applied.</strong><br>
                            Transactions expired: <?php echo esc_html($this->apply_results['transactions_updated']); ?><br>
                            Waitlist subscriptions cancelled: <?php echo esc_html($this->apply_results['subscriptions_cancelled']); ?><br>
                            Cleanup time: <?php echo esc_html($this->apply_results['cleanup_local']); ?> NZ time
                            <?php if (!empty($this->apply_results['cleanup_utc'])) : ?>
                                <br><span class="small muted">UTC: <?php echo esc_html($this->apply_results['cleanup_utc']); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($this->apply_results['errors'])) : ?>
                            <div class="warning-box">
                                <strong>Some rows had issues:</strong>
                                <ul>
                                    <?php foreach ($this->apply_results['errors'] as $apply_error) : ?>
                                        <li><?php echo esc_html($apply_error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($this->apply_results['rows'])) : ?>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Txn ID</th>
                                            <th>Member</th>
                                            <th>Email</th>
                                            <th>Waitlist Membership</th>
                                            <th>Reason</th>
                                            <th>Transaction</th>
                                            <th>Subscription</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($this->apply_results['rows'] as $row) : ?>
                                            <tr>
                                                <td><?php echo esc_html($row['transaction_id']); ?></td>
                                                <td><?php echo esc_html($row['display_name']); ?></td>
                                                <td><?php echo esc_html($row['user_email']); ?></td>
                                                <td><?php echo esc_html($row['waitlist_membership']); ?></td>
                                                <td><?php echo esc_html($row['cleanup_reason']); ?></td>
                                                <td>
                                                    <?php if ($row['transaction_updated']) : ?>
                                                        <span class="tag ok">Expired</span>
                                                    <?php else : ?>
                                                        <span class="tag muted">Skipped</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['subscription_cancelled']) : ?>
                                                        <span class="tag ok">Cancelled</span>
                                                    <?php elseif (!empty($row['subscription_status'])) : ?>
                                                        <span class="tag muted"><?php echo esc_html($row['subscription_status']); ?></span>
                                                    <?php else : ?>
                                                        <span class="tag muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo esc_html($row['error'] ?: ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <h2>Preview Settings</h2>
                    <form method="post">
                        <?php wp_nonce_field('wcb_waitlist_cleanup', 'wcb_waitlist_cleanup_nonce'); ?>

                        <div class="field">
                            <label for="cutoff_date">Cutoff Date</label>
                            <input type="date" id="cutoff_date" name="cutoff_date" value="<?php echo esc_attr($this->cutoff_date); ?>">
                            <p class="description small">Waitlist memberships created on or before this NZ date will be targeted, except protected members.</p>
                        </div>

                        <div class="field">
                            <label for="protected_members">Protected Members</label>
                            <textarea id="protected_members" name="protected_members"><?php echo esc_textarea($this->protected_members_text); ?></textarea>
                            <p class="description small">One per line. You can use display name, email, username, or user ID. Protected members are still removed if they already have another active non-waitlist membership.</p>
                        </div>

                        <div class="button-row">
                            <button type="submit" class="button secondary" name="action" value="preview_cleanup">Preview Cleanup</button>
                        </div>
                    </form>
                </div>

                <?php if ($this->analysis && $stats) : ?>
                    <div class="card">
                        <h2>Preview Summary</h2>
                        <div class="info-box">
                            Cutoff date: <strong><?php echo esc_html($this->analysis['cutoff_date']); ?></strong>
                            <br>
                            <span class="small muted">Cutoff end in UTC: <?php echo esc_html($this->analysis['cutoff_utc']); ?></span>
                            <?php if (!empty($this->analysis['protected_members'])) : ?>
                                <br>
                                <span class="small muted">Protected: <?php echo esc_html(implode(', ', $this->analysis['protected_members'])); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="grid">
                            <div class="stat"><strong><?php echo esc_html($stats['total_active_waitlist']); ?></strong>Active waitlist records</div>
                            <div class="stat"><strong><?php echo esc_html($stats['targeted_count']); ?></strong>Will be removed</div>
                            <div class="stat"><strong><?php echo esc_html($stats['kept_count']); ?></strong>Will stay on waitlist</div>
                            <div class="stat"><strong><?php echo esc_html($stats['cutoff_rule_count']); ?></strong>Matched cutoff rule</div>
                            <div class="stat"><strong><?php echo esc_html($stats['other_active_rule_count']); ?></strong>Has other active membership</div>
                            <div class="stat"><strong><?php echo esc_html($stats['protected_kept_count']); ?></strong>Protected keeps</div>
                            <div class="stat"><strong><?php echo esc_html($stats['newer_kept_count']); ?></strong>Newer waitlist keeps</div>
                        </div>
                    </div>

                    <div class="card">
                        <h2>Rows To Remove</h2>
                        <?php if (!empty($this->analysis['targets'])) : ?>
                            <div class="warning-box">
                                These waitlist memberships will be removed. Any other active membership on the same member will stay untouched.
                            </div>

                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Txn ID</th>
                                            <th>Member</th>
                                            <th>Login</th>
                                            <th>Waitlist Membership</th>
                                            <th>Joined Waitlist</th>
                                            <th>Reason</th>
                                            <th>Other Active Memberships</th>
                                            <th>Waitlist Subscription</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($this->analysis['targets'] as $target) : ?>
                                            <tr>
                                                <td><?php echo esc_html($target['transaction_id']); ?></td>
                                                <td>
                                                    <?php echo esc_html($target['display_name']); ?><br>
                                                    <span class="small muted"><?php echo esc_html($target['user_email']); ?></span>
                                                </td>
                                                <td><?php echo esc_html($target['user_login']); ?></td>
                                                <td><?php echo esc_html($target['waitlist_membership']); ?></td>
                                                <td>
                                                    <?php echo esc_html($target['joined_waitlist']); ?>
                                                    <?php if ($target['joined_before_cutoff']) : ?>
                                                        <br><span class="tag warn">On/before cutoff</span>
                                                    <?php else : ?>
                                                        <br><span class="tag">After cutoff</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo esc_html($target['cleanup_reason']); ?></td>
                                                <td><?php echo esc_html($target['other_active_memberships'] ?: 'None'); ?></td>
                                                <td>
                                                    <?php if ($target['subscription_id']) : ?>
                                                        <span class="tag muted">#<?php echo esc_html($target['subscription_id']); ?></span>
                                                        <?php if (!empty($target['subscription_status'])) : ?>
                                                            <span class="tag muted"><?php echo esc_html($target['subscription_status']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($target['should_cancel_subscription']) : ?>
                                                            <br><span class="tag ok">Will cancel</span>
                                                        <?php endif; ?>
                                                    <?php else : ?>
                                                        <span class="tag muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <form method="post" onsubmit="return confirm('Apply waitlist cleanup to <?php echo esc_js($stats['targeted_count']); ?> records? This will expire those waitlist transactions and cancel matching waitlist subscriptions.');">
                                <?php wp_nonce_field('wcb_waitlist_cleanup', 'wcb_waitlist_cleanup_nonce'); ?>
                                <input type="hidden" name="cutoff_date" value="<?php echo esc_attr($this->cutoff_date); ?>">
                                <textarea name="protected_members" style="display:none;"><?php echo esc_textarea($this->protected_members_text); ?></textarea>
                                <div class="button-row">
                                    <button type="submit" class="button danger" name="action" value="apply_cleanup">Apply Cleanup</button>
                                </div>
                            </form>
                        <?php else : ?>
                            <div class="success-box">No active waitlist memberships match the cleanup rules.</div>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <h2>Rows Kept On Waitlist</h2>
                        <?php if (!empty($this->analysis['kept'])) : ?>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Txn ID</th>
                                            <th>Member</th>
                                            <th>Waitlist Membership</th>
                                            <th>Joined Waitlist</th>
                                            <th>Keep Reason</th>
                                            <th>Protected</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($this->analysis['kept'] as $keep) : ?>
                                            <tr>
                                                <td><?php echo esc_html($keep['transaction_id']); ?></td>
                                                <td>
                                                    <?php echo esc_html($keep['display_name']); ?><br>
                                                    <span class="small muted"><?php echo esc_html($keep['user_email']); ?></span>
                                                </td>
                                                <td><?php echo esc_html($keep['waitlist_membership']); ?></td>
                                                <td><?php echo esc_html($keep['joined_waitlist']); ?></td>
                                                <td><?php echo esc_html($keep['keep_reason']); ?></td>
                                                <td>
                                                    <?php if ($keep['is_protected']) : ?>
                                                        <span class="tag ok">Yes</span>
                                                    <?php else : ?>
                                                        <span class="tag muted">No</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="info-box">No active waitlist memberships remain after the current rules.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }

    private function render_login_message() {
        $current_url = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $login_url = wp_login_url($current_url);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Required</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #f1f1f1;
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .card {
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    padding: 32px;
                    max-width: 480px;
                    text-align: center;
                }
                .button {
                    display: inline-block;
                    margin-top: 16px;
                    background: #2271b1;
                    color: #fff;
                    text-decoration: none;
                    padding: 10px 16px;
                    border-radius: 4px;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Login Required</h1>
                <p>You must be logged in as an administrator to use this cleanup tool.</p>
                <a class="button" href="<?php echo esc_url($login_url); ?>">Log In</a>
            </div>
        </body>
        </html>
        <?php
    }
}

new WCB_Waitlist_Membership_Cleanup_Tool();
