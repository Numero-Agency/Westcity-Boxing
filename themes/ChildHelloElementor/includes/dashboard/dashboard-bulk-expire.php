<?php
/**
 * Bulk Expire Manual Members Tool
 *
 * This tool allows admins to set expiration dates on manual MemberPress transactions
 * to transition members from active to inactive status.
 *
 * @package WCB_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode to display the bulk expire tool
 * Usage: [wcb_bulk_expire_tool]
 */
function wcb_bulk_expire_tool_shortcode() {
    // Security check
    if (!current_user_can('manage_options')) {
        return '<p>You do not have permission to access this tool.</p>';
    }

    ob_start();
    wcb_render_bulk_expire_tool();
    return ob_get_clean();
}
add_shortcode('wcb_bulk_expire_tool', 'wcb_bulk_expire_tool_shortcode');

/**
 * Render the bulk expire tool interface
 */
function wcb_render_bulk_expire_tool() {
    global $wpdb;

    $txn_table = $wpdb->prefix . 'mepr_transactions';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$txn_table'") == $txn_table;

    if (!$table_exists) {
        echo '<div class="wcb-error">MemberPress transactions table not found.</div>';
        return;
    }

    // Default expiration date: December 19, 2025 23:59:59 NZ time
    // NZ is UTC+13 in December (NZDT), so we need to convert to UTC
    // Dec 19, 2025 23:59:59 NZDT = Dec 19, 2025 10:59:59 UTC
    $default_expire_date = '2025-12-19';
    $default_expire_time = '23:59:59';

    ?>
    <div class="wcb-bulk-expire-tool">
        <h2>Bulk Expire Manual Members</h2>
        <p class="description">
            This tool sets an expiration date on manual MemberPress transactions.
            Members with manual transactions that have no expiration date (or '0000-00-00') will be updated.
        </p>
        <p class="description" style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa;">
            <strong>Note:</strong> The following memberships are <strong>excluded</strong> from this tool:
            <br>• WCB Mentoring (ID: 1738)
            <br>• Competitive Team (ID: 1932)
            <br>• All Waitlist memberships (e.g., "Mini Cadet Boys Waitlist")
        </p>

        <div class="wcb-tool-section">
            <h3>Settings</h3>
            <form id="bulk-expire-form">
                <?php wp_nonce_field('wcb_bulk_expire_nonce', 'wcb_bulk_expire_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="expire_date">Expiration Date (NZ Time)</label>
                        </th>
                        <td>
                            <input type="date" id="expire_date" name="expire_date"
                                   value="<?php echo esc_attr($default_expire_date); ?>" required>
                            <input type="time" id="expire_time" name="expire_time"
                                   value="<?php echo esc_attr($default_expire_time); ?>" required>
                            <p class="description">
                                Date will be converted to UTC for storage. Default: Dec 19, 2025 23:59:59 NZ time.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="transaction_status">Transaction Status to Update</label>
                        </th>
                        <td>
                            <select id="transaction_status" name="transaction_status">
                                <option value="confirmed,complete">Confirmed & Complete (Active)</option>
                                <option value="confirmed">Confirmed only</option>
                                <option value="complete">Complete only</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="wcb-button-group">
                    <button type="button" id="preview-btn" class="button button-secondary">
                        Preview Changes
                    </button>
                    <button type="button" id="apply-btn" class="button button-primary" disabled>
                        Apply Changes
                    </button>
                </div>
            </form>
        </div>

        <div id="preview-results" class="wcb-tool-section" style="display: none;">
            <h3>Preview Results</h3>
            <div id="preview-summary"></div>
            <div id="preview-table-container"></div>
        </div>

        <div id="apply-results" class="wcb-tool-section" style="display: none;">
            <h3>Update Results</h3>
            <div id="apply-summary"></div>
            <div id="apply-log-container"></div>
        </div>
    </div>

    <style>
        .wcb-bulk-expire-tool {
            max-width: 1200px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .wcb-bulk-expire-tool h2 {
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #0073aa;
        }
        .wcb-tool-section {
            margin-top: 25px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .wcb-tool-section h3 {
            margin-top: 0;
            color: #23282d;
        }
        .wcb-button-group {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .wcb-button-group button {
            margin-right: 10px;
        }
        .wcb-error {
            padding: 15px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: 5px;
        }
        .wcb-success {
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 5px;
        }
        .wcb-warning {
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            border-radius: 5px;
        }
        .wcb-info {
            padding: 15px;
            background: #cce5ff;
            border: 1px solid #b8daff;
            color: #004085;
            border-radius: 5px;
        }
        #preview-table-container {
            margin-top: 15px;
            max-height: 500px;
            overflow-y: auto;
        }
        #preview-table-container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        #preview-table-container th,
        #preview-table-container td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        #preview-table-container th {
            background: #f1f1f1;
            position: sticky;
            top: 0;
        }
        #preview-table-container tr:hover {
            background: #f5f5f5;
        }
        .gateway-manual {
            background: #fff3cd;
            color: #856404;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .spinner-active {
            display: inline-block;
            margin-left: 10px;
        }
        /* Update log styles */
        .update-log-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .update-log-table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .update-log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .update-log-table th,
        .update-log-table td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .update-log-table th {
            background: #f1f1f1;
            position: sticky;
            top: 0;
            font-weight: 600;
        }
        .update-log-table tr:hover {
            background: #f9f9f9;
        }
        .old-value {
            color: #999;
            text-decoration: line-through;
        }
        .new-value {
            color: #155724;
            font-weight: bold;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var previewData = null;

        // Preview button click
        $('#preview-btn').on('click', function() {
            var $btn = $(this);
            var $form = $('#bulk-expire-form');

            $btn.prop('disabled', true).text('Loading...');
            $form.addClass('loading');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'wcb_bulk_expire_preview',
                    nonce: $('#wcb_bulk_expire_nonce').val(),
                    expire_date: $('#expire_date').val(),
                    expire_time: $('#expire_time').val(),
                    transaction_status: $('#transaction_status').val()
                },
                success: function(response) {
                    if (response.success) {
                        previewData = response.data;
                        renderPreview(response.data);
                        $('#apply-btn').prop('disabled', response.data.count === 0);
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('AJAX error occurred');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Preview Changes');
                    $form.removeClass('loading');
                }
            });
        });

        // Apply button click
        $('#apply-btn').on('click', function() {
            if (!previewData || previewData.count === 0) {
                alert('No transactions to update. Run preview first.');
                return;
            }

            if (!confirm('Are you sure you want to update ' + previewData.count + ' transactions?\n\nThis will set their expiration date to: ' + previewData.expire_date_display + '\n\nThis action cannot be undone.')) {
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('Applying...');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'wcb_bulk_expire_apply',
                    nonce: $('#wcb_bulk_expire_nonce').val(),
                    expire_date: $('#expire_date').val(),
                    expire_time: $('#expire_time').val(),
                    transaction_status: $('#transaction_status').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#apply-results').show();

                        var timestamp = new Date().toLocaleString('en-NZ', {
                            dateStyle: 'full',
                            timeStyle: 'long'
                        });

                        $('#apply-summary').html(
                            '<div class="wcb-success">' +
                            '<strong>Success!</strong><br>' +
                            'Updated ' + response.data.updated_count + ' transactions.<br>' +
                            'New expiration date: ' + response.data.expire_date_display + '<br>' +
                            '<small>UTC: ' + response.data.expire_date_utc + '</small><br>' +
                            '<small>Applied at: ' + timestamp + '</small>' +
                            '</div>'
                        );

                        // Render the log of updated transactions
                        if (response.data.updated_transactions && response.data.updated_transactions.length > 0) {
                            renderUpdateLog(response.data);
                        }

                        // Disable apply button after success
                        $btn.prop('disabled', true).text('Changes Applied');
                        // Clear preview
                        previewData = null;
                    } else {
                        $('#apply-results').show();
                        $('#apply-summary').html('<div class="wcb-error">Error: ' + response.data + '</div>');
                        $btn.prop('disabled', false).text('Apply Changes');
                    }
                },
                error: function() {
                    alert('AJAX error occurred');
                    $btn.prop('disabled', false).text('Apply Changes');
                },
                complete: function() {
                    // Button state is handled in success/error callbacks
                }
            });
        });

        function renderPreview(data) {
            $('#preview-results').show();

            var summary = '';
            if (data.count === 0) {
                summary = '<div class="wcb-info">No manual transactions found that need updating.</div>';
            } else {
                summary = '<div class="wcb-warning">' +
                    '<strong>Found ' + data.count + ' transactions to update</strong><br>' +
                    'These transactions will have their expiration date set to:<br>' +
                    '<strong>' + data.expire_date_display + ' (NZ Time)</strong><br>' +
                    '<small>UTC: ' + data.expire_date_utc + '</small>' +
                    '</div>';
            }
            $('#preview-summary').html(summary);

            if (data.transactions && data.transactions.length > 0) {
                var table = '<table>' +
                    '<thead><tr>' +
                    '<th>ID</th>' +
                    '<th>User</th>' +
                    '<th>Email</th>' +
                    '<th>Membership</th>' +
                    '<th>Gateway</th>' +
                    '<th>Status</th>' +
                    '<th>Current Expires</th>' +
                    '<th>New Expires</th>' +
                    '</tr></thead><tbody>';

                data.transactions.forEach(function(txn) {
                    table += '<tr>' +
                        '<td>' + txn.id + '</td>' +
                        '<td>' + txn.display_name + '</td>' +
                        '<td>' + txn.user_email + '</td>' +
                        '<td>' + txn.membership_name + '</td>' +
                        '<td><span class="gateway-manual">' + txn.gateway + '</span></td>' +
                        '<td>' + txn.status + '</td>' +
                        '<td>' + (txn.current_expires || 'Never') + '</td>' +
                        '<td><strong>' + data.expire_date_display + '</strong></td>' +
                        '</tr>';
                });

                table += '</tbody></table>';
                $('#preview-table-container').html(table);
            } else {
                $('#preview-table-container').html('');
            }
        }

        function renderUpdateLog(data) {
            var html = '<div class="update-log-section">' +
                '<h4 style="margin-top: 20px; margin-bottom: 10px;">Updated Transactions Log</h4>' +
                '<p><small>The following ' + data.updated_transactions.length + ' transactions were updated:</small></p>' +
                '<div class="update-log-table-wrapper">' +
                '<table class="update-log-table">' +
                '<thead><tr>' +
                '<th>Txn ID</th>' +
                '<th>User</th>' +
                '<th>Email</th>' +
                '<th>Membership</th>' +
                '<th>Previous Expires</th>' +
                '<th>New Expires</th>' +
                '</tr></thead><tbody>';

            data.updated_transactions.forEach(function(txn) {
                html += '<tr>' +
                    '<td>' + txn.id + '</td>' +
                    '<td>' + txn.display_name + '</td>' +
                    '<td>' + txn.user_email + '</td>' +
                    '<td>' + txn.membership_name + '</td>' +
                    '<td><span class="old-value">' + (txn.previous_expires || 'Never') + '</span></td>' +
                    '<td><span class="new-value">' + data.expire_date_display + '</span></td>' +
                    '</tr>';
            });

            html += '</tbody></table></div></div>';

            $('#apply-log-container').html(html);
        }
    });
    </script>
    <?php
}

/**
 * AJAX handler for preview
 */
function wcb_bulk_expire_preview_ajax() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    if (!wp_verify_nonce($_POST['nonce'], 'wcb_bulk_expire_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $expire_date = sanitize_text_field($_POST['expire_date']);
    $expire_time = sanitize_text_field($_POST['expire_time']);
    $transaction_status = sanitize_text_field($_POST['transaction_status']);

    if (empty($expire_date) || empty($expire_time)) {
        wp_send_json_error('Date and time are required');
    }

    // Convert NZ time to UTC
    $nz_datetime = $expire_date . ' ' . $expire_time;
    $nz_tz = new DateTimeZone('Pacific/Auckland');
    $utc_tz = new DateTimeZone('UTC');

    try {
        $dt = new DateTime($nz_datetime, $nz_tz);
        $dt->setTimezone($utc_tz);
        $expire_utc = $dt->format('Y-m-d H:i:s');

        // Also format for display
        $dt_display = new DateTime($nz_datetime, $nz_tz);
        $expire_display = $dt_display->format('d M Y H:i:s') . ' (NZ Time)';
    } catch (Exception $e) {
        wp_send_json_error('Invalid date format: ' . $e->getMessage());
    }

    // Get manual transactions without expiration
    $transactions = wcb_get_manual_transactions_for_expiry($transaction_status);

    wp_send_json_success([
        'count' => count($transactions),
        'transactions' => $transactions,
        'expire_date_utc' => $expire_utc,
        'expire_date_display' => $expire_display
    ]);
}
add_action('wp_ajax_wcb_bulk_expire_preview', 'wcb_bulk_expire_preview_ajax');

/**
 * AJAX handler for applying changes
 */
function wcb_bulk_expire_apply_ajax() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    if (!wp_verify_nonce($_POST['nonce'], 'wcb_bulk_expire_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $expire_date = sanitize_text_field($_POST['expire_date']);
    $expire_time = sanitize_text_field($_POST['expire_time']);
    $transaction_status = sanitize_text_field($_POST['transaction_status']);

    if (empty($expire_date) || empty($expire_time)) {
        wp_send_json_error('Date and time are required');
    }

    // Convert NZ time to UTC
    $nz_datetime = $expire_date . ' ' . $expire_time;
    $nz_tz = new DateTimeZone('Pacific/Auckland');
    $utc_tz = new DateTimeZone('UTC');

    try {
        $dt = new DateTime($nz_datetime, $nz_tz);
        $dt->setTimezone($utc_tz);
        $expire_utc = $dt->format('Y-m-d H:i:s');

        // Also format for display
        $dt_display = new DateTime($nz_datetime, $nz_tz);
        $expire_display = $dt_display->format('d M Y H:i:s') . ' (NZ Time)';
    } catch (Exception $e) {
        wp_send_json_error('Invalid date format: ' . $e->getMessage());
    }

    // Get list of transactions BEFORE updating (for the log)
    $transactions_to_update = wcb_get_manual_transactions_for_expiry($transaction_status);

    // Log each transaction that will be updated
    wcb_debug_log("Bulk Expire Tool: About to update " . count($transactions_to_update) . " transactions");
    foreach ($transactions_to_update as $txn) {
        wcb_debug_log("Bulk Expire Tool: Will update Txn ID {$txn['id']} - User: {$txn['display_name']} ({$txn['user_email']}) - Membership: {$txn['membership_name']}");
    }

    // Update the transactions
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Parse status filter
    $statuses = array_map('trim', explode(',', $transaction_status));
    $status_placeholders = implode(',', array_fill(0, count($statuses), '%s'));

    // Excluded membership IDs (WCB Mentoring: 1738, Competitive Team: 1932)
    $excluded_ids = [1738, 1932];
    $excluded_placeholders = implode(',', array_fill(0, count($excluded_ids), '%d'));

    // Build the update query with JOIN to exclude waitlist memberships by name
    $query = $wpdb->prepare(
        "UPDATE {$txn_table} t
         JOIN {$wpdb->posts} p ON t.product_id = p.ID
         SET t.expires_at = %s
         WHERE t.gateway = 'manual'
         AND t.status IN ({$status_placeholders})
         AND t.product_id NOT IN ({$excluded_placeholders})
         AND p.post_title NOT LIKE '%%waitlist%%'
         AND (t.expires_at IS NULL OR t.expires_at = '0000-00-00 00:00:00')",
        array_merge([$expire_utc], $statuses, $excluded_ids)
    );

    // Log the action before executing
    wcb_debug_log("Bulk Expire Tool: Running query: " . $query);
    wcb_debug_log("Bulk Expire Tool: Setting expiration to UTC: " . $expire_utc);

    $updated = $wpdb->query($query);

    if ($updated === false) {
        wcb_debug_log("Bulk Expire Tool: Query failed - " . $wpdb->last_error);
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    }

    wcb_debug_log("Bulk Expire Tool: Updated {$updated} transactions");

    // Format transactions for the log display
    $formatted_transactions = array_map(function($txn) {
        return [
            'id' => $txn['id'],
            'display_name' => $txn['display_name'],
            'user_email' => $txn['user_email'],
            'membership_name' => $txn['membership_name'],
            'previous_expires' => $txn['current_expires']
        ];
    }, $transactions_to_update);

    wp_send_json_success([
        'updated_count' => $updated,
        'expire_date_utc' => $expire_utc,
        'expire_date_display' => $expire_display,
        'updated_transactions' => $formatted_transactions
    ]);
}
add_action('wp_ajax_wcb_bulk_expire_apply', 'wcb_bulk_expire_apply_ajax');

/**
 * Membership IDs to exclude from bulk expire
 * - WCB Mentoring: 1738
 * - Competitive Team: 1932
 */
define('WCB_BULK_EXPIRE_EXCLUDED_MEMBERSHIPS', [1738, 1932]);

/**
 * Get manual transactions that don't have an expiration date
 * Excludes: WCB Mentoring, Competitive Team, and any Waitlist memberships
 */
function wcb_get_manual_transactions_for_expiry($transaction_status = 'confirmed,complete') {
    global $wpdb;

    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Parse status filter
    $statuses = array_map('trim', explode(',', $transaction_status));
    $status_placeholders = implode(',', array_fill(0, count($statuses), '%s'));

    // Excluded membership IDs (WCB Mentoring: 1738, Competitive Team: 1932)
    $excluded_ids = WCB_BULK_EXPIRE_EXCLUDED_MEMBERSHIPS;
    $excluded_placeholders = implode(',', array_fill(0, count($excluded_ids), '%d'));

    $query = $wpdb->prepare(
        "SELECT t.id, t.user_id, t.product_id, t.gateway, t.status, t.expires_at, t.created_at,
                u.display_name, u.user_email,
                p.post_title as membership_name
         FROM {$txn_table} t
         JOIN {$wpdb->users} u ON t.user_id = u.ID
         JOIN {$wpdb->posts} p ON t.product_id = p.ID
         WHERE t.gateway = 'manual'
         AND t.status IN ({$status_placeholders})
         AND t.product_id NOT IN ({$excluded_placeholders})
         AND p.post_title NOT LIKE '%%waitlist%%'
         AND (t.expires_at IS NULL OR t.expires_at = '0000-00-00 00:00:00')
         ORDER BY u.display_name ASC",
        array_merge($statuses, $excluded_ids)
    );

    $results = $wpdb->get_results($query, ARRAY_A);

    // Format the expires_at for display
    foreach ($results as &$row) {
        if (empty($row['expires_at']) || $row['expires_at'] === '0000-00-00 00:00:00') {
            $row['current_expires'] = 'Never (no date set)';
        } else {
            $row['current_expires'] = $row['expires_at'];
        }
    }

    return $results;
}
