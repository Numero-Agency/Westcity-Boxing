<?php
/**
 * Manual Membership Assignment Tool
 * Bulk assign users to MemberPress memberships with manual transactions
 * 
 * Usage: Access via browser at /wp-content/themes/ChildHelloElementor/manual-membership-assignment.php
 * Requires: Administrator access
 */

// Load WordPress - multiple path attempts for compatibility
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
} elseif (file_exists('../../../../wp-load.php')) {
    require_once('../../../../wp-load.php');
} elseif (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
} else {
    die('Could not locate wp-load.php. Please check file paths.');
}

// Error reporting for development (remove for production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/**
 * Main Manual Membership Assignment Class
 */
class WCB_Manual_Membership_Assignment {
    
    private $results = [];
    private $errors = [];
    private $success_count = 0;
    private $error_count = 0;
    
    public function __construct() {
        // Security check first
        $this->check_permissions();
        
        // Handle form submission
        if ($_POST && isset($_POST['action']) && $_POST['action'] === 'assign_memberships') {
            $this->handle_form_submission();
        }
        
        // Render the interface
        $this->render_interface();
    }
    
    /**
     * Check if user has administrator permissions
     */
    private function check_permissions() {
        // Check if WordPress functions are available
        if (!function_exists('current_user_can')) {
            die('WordPress not properly loaded. Please check file paths.');
        }
        
        // Check if MemberPress is active
        if (!$this->is_memberpress_active()) {
            wp_die('MemberPress plugin is required for this tool to function.');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            $this->render_login_message();
            exit;
        }
        
        if (!current_user_can('administrator')) {
            wp_die('Access denied. Administrator privileges required.');
        }
    }
    
    /**
     * Handle form submission and process membership assignments
     */
    private function handle_form_submission() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['wcb_assignment_nonce'], 'wcb_manual_assignment')) {
            $this->errors[] = 'Security check failed. Please try again.';
            return;
        }
        
        $user_list = sanitize_textarea_field($_POST['user_list']);
        $membership_id = intval($_POST['membership_id']);
        
        // Validate inputs
        if (empty($user_list)) {
            $this->errors[] = 'Please provide a list of users.';
            return;
        }
        
        if (empty($membership_id)) {
            $this->errors[] = 'Please select a membership.';
            return;
        }
        
        // Verify membership exists
        $membership = get_post($membership_id);
        if (!$membership || $membership->post_type !== 'memberpressproduct') {
            $this->errors[] = 'Invalid membership selected.';
            return;
        }
        
        // Process user list
        $this->process_user_assignments($user_list, $membership_id, $membership->post_title);
    }
    
    /**
     * Process the user list and create membership assignments
     */
    private function process_user_assignments($user_list, $membership_id, $membership_name) {
        // Split user list into individual entries
        $user_entries = array_filter(array_map('trim', explode("\n", $user_list)));
        
        foreach ($user_entries as $user_entry) {
            $user = $this->resolve_user($user_entry);
            
            if (!$user) {
                $this->results[] = [
                    'status' => 'error',
                    'input' => $user_entry,
                    'message' => 'User not found'
                ];
                $this->error_count++;
                continue;
            }
            
            // Check if user already has this membership
            if ($this->user_has_membership($user->ID, $membership_id)) {
                $this->results[] = [
                    'status' => 'warning',
                    'input' => $user_entry,
                    'user' => $user,
                    'message' => 'User already has this membership'
                ];
                continue;
            }
            
            // Create manual transaction using native MemberPress methods if available
            $transaction_id = $this->create_memberpress_transaction_native($user->ID, $membership_id);
            
            if ($transaction_id) {
                $this->results[] = [
                    'status' => 'success',
                    'input' => $user_entry,
                    'user' => $user,
                    'transaction_id' => $transaction_id,
                    'message' => 'Successfully assigned to ' . $membership_name
                ];
                $this->success_count++;
            } else {
                $this->results[] = [
                    'status' => 'error',
                    'input' => $user_entry,
                    'user' => $user,
                    'message' => 'Failed to create transaction'
                ];
                $this->error_count++;
            }
        }
    }
    
    /**
     * Resolve user from various input formats (email, username, display name)
     */
    private function resolve_user($input) {
        $user = null;
        
        // Try email first (most reliable)
        if (is_email($input)) {
            $user = get_user_by('email', $input);
        }
        
        // Try username
        if (!$user) {
            $user = get_user_by('login', $input);
        }
        
        // Try user ID if numeric
        if (!$user && is_numeric($input)) {
            $user = get_user_by('id', intval($input));
        }
        
        // Try display name (less reliable, but useful)
        if (!$user) {
            global $wpdb;
            $user_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->users} WHERE display_name = %s LIMIT 1",
                $input
            ));
            if ($user_data) {
                $user = new WP_User($user_data);
            }
        }
        
        return $user;
    }
    
    /**
     * Check if user already has an active membership
     */
    private function user_has_membership($user_id, $membership_id) {
        // Try MemberPress native method first
        if (class_exists('MeprUser')) {
            $user = new MeprUser($user_id);
            if (method_exists($user, 'has_active_membership')) {
                return $user->has_active_membership($membership_id);
            }
        }
        
        // Fallback to direct database check
        global $wpdb;
        
        $txn_table = $wpdb->prefix . 'mepr_transactions';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$txn_table} 
             WHERE user_id = %d 
             AND product_id = %d 
             AND status IN ('complete', 'confirmed')
             AND (expires_at IS NULL OR expires_at > NOW() OR expires_at = '0000-00-00 00:00:00')",
            $user_id,
            $membership_id
        ));
        
        return $existing > 0;
    }
    
    /**
     * Create a manual transaction for the user using MemberPress standards
     */
    private function create_manual_transaction($user_id, $membership_id) {
        global $wpdb;
        
        $txn_table = $wpdb->prefix . 'mepr_transactions';
        
        // Generate unique transaction number  
        $trans_num = 'manual_' . time() . '_' . $user_id . '_' . $membership_id;
        
        // Get current timestamp
        $current_time = current_time('mysql', true);
        
        // Create transaction with essential MemberPress fields matching working transactions
        $result = $wpdb->insert(
            $txn_table,
            [
                'user_id' => $user_id,
                'product_id' => $membership_id,
                'amount' => '0.00',
                'total' => '0.00',
                'tax_amount' => '0.00',
                'tax_rate' => '0.0000', 
                'status' => 'complete',
                'gateway' => 'manual',
                'trans_num' => $trans_num,
                'created_at' => $current_time,
                'expires_at' => null,
                'subscription_id' => null, // NULL for non-recurring (not 0)
                'response' => null,
                'rebill' => 0,
                'subscription_payment_index' => null, // NULL for non-recurring
                'commissions' => null,
                'tax_desc' => '',
                'tax_class' => 'standard'
            ],
            [
                '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
            ]
        );
        
        if ($result) {
            $transaction_id = $wpdb->insert_id;
            
            // Try to use MemberPress hooks/functions if available
            if (function_exists('mepr_send_transaction_receipt_notices')) {
                // Don't send receipt for manual assignments
                // mepr_send_transaction_receipt_notices($transaction_id);
            }
            
            return $transaction_id;
        }
        
        return false;
    }
    
    /**
     * Get all available MemberPress products for dropdown
     */
    private function get_memberpress_products() {
        return get_posts([
            'post_type' => 'memberpressproduct',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
    }
    
    /**
     * Render the main interface
     */
    private function render_interface() {
        $products = $this->get_memberpress_products();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manual Membership Assignment - West City Boxing</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f1f1f1;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }
                .container {
                    max-width: 1000px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                .header {
                    background: #000;
                    color: white;
                    padding: 20px 30px;
                    border-bottom: 3px solid #007cba;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                .header p {
                    margin: 8px 0 0 0;
                    opacity: 0.9;
                    font-size: 14px;
                }
                .content {
                    padding: 30px;
                }
                .form-group {
                    margin-bottom: 25px;
                }
                .form-group label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 8px;
                    color: #333;
                }
                .form-group textarea {
                    width: 100%;
                    height: 200px;
                    padding: 12px;
                    border: 2px solid #e1e1e1;
                    border-radius: 6px;
                    font-family: monospace;
                    font-size: 14px;
                    resize: vertical;
                    box-sizing: border-box;
                }
                .form-group textarea:focus {
                    border-color: #007cba;
                    outline: none;
                }
                .form-group select {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e1e1e1;
                    border-radius: 6px;
                    font-size: 14px;
                    box-sizing: border-box;
                }
                .form-group select:focus {
                    border-color: #007cba;
                    outline: none;
                }
                .help-text {
                    font-size: 13px;
                    color: #666;
                    margin-top: 5px;
                    line-height: 1.4;
                }
                .button {
                    background: #007cba;
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    border-radius: 6px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.2s;
                }
                .button:hover {
                    background: #005a87;
                }
                .button:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                }
                .alerts {
                    margin-bottom: 25px;
                }
                .alert {
                    padding: 15px;
                    border-radius: 6px;
                    margin-bottom: 10px;
                }
                .alert-error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
                .alert-success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                .results {
                    margin-top: 30px;
                    border-top: 2px solid #e1e1e1;
                    padding-top: 30px;
                }
                .results h3 {
                    margin: 0 0 20px 0;
                    color: #333;
                }
                .result-item {
                    padding: 12px;
                    border-radius: 6px;
                    margin-bottom: 8px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .result-success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                }
                .result-error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                }
                .result-warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                }
                .result-info {
                    flex: 1;
                }
                .result-status {
                    font-weight: 600;
                    text-transform: uppercase;
                    font-size: 12px;
                    padding: 4px 8px;
                    border-radius: 4px;
                }
                .status-success {
                    background: #28a745;
                    color: white;
                }
                .status-error {
                    background: #dc3545;
                    color: white;
                }
                .status-warning {
                    background: #ffc107;
                    color: #212529;
                }
                .summary {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: space-around;
                    text-align: center;
                }
                .summary-item h4 {
                    margin: 0 0 5px 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                .summary-item p {
                    margin: 0;
                    color: #666;
                    font-size: 14px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .summary-success h4 { color: #28a745; }
                .summary-error h4 { color: #dc3545; }
                .summary-total h4 { color: #007cba; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Manual Membership Assignment Tool</h1>
                    <p>Bulk assign users to MemberPress memberships with manual transactions</p>
                </div>
                
                <div class="content">
                    <?php $this->render_alerts(); ?>
                    
                    <form method="POST" action="">
                        <?php wp_nonce_field('wcb_manual_assignment', 'wcb_assignment_nonce'); ?>
                        <input type="hidden" name="action" value="assign_memberships">
                        
                        <div class="form-group">
                            <label for="user_list">User List</label>
                            <textarea 
                                name="user_list" 
                                id="user_list" 
                                placeholder="Enter users one per line. You can use:
- Email addresses: dan.pinto@example.com
- Usernames: john_doe  
- Display names: Jane Smith
- User IDs: 123"
                                required><?php echo isset($_POST['user_list']) ? esc_textarea($_POST['user_list']) : ''; ?></textarea>
                            <div class="help-text">
                                Enter one user per line. Supports email addresses, usernames, display names, or user IDs.
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="membership_id">Membership</label>
                            <select name="membership_id" id="membership_id" required>
                                <option value="">Select a membership...</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo esc_attr($product->ID); ?>"
                                            <?php selected(isset($_POST['membership_id']) ? $_POST['membership_id'] : '', $product->ID); ?>>
                                        <?php echo esc_html($product->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="help-text">
                                Select the membership/product to assign to the users.
                            </div>
                        </div>
                        
                        <button type="submit" class="button">Assign Memberships</button>
                    </form>
                    
                    <?php $this->render_results(); ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render alert messages
     */
    private function render_alerts() {
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                echo '<div class="alert alert-error">' . esc_html($error) . '</div>';
            }
        }
        
        if (!empty($this->results)) {
            $total = count($this->results);
            echo '<div class="alert alert-success">';
            echo "Processing completed! {$this->success_count} successful, {$this->error_count} errors out of {$total} total users.";
            echo '</div>';
        }
    }
    
    /**
     * Render processing results
     */
    private function render_results() {
        if (empty($this->results)) {
            return;
        }
        
        echo '<div class="results">';
        echo '<h3>Assignment Results</h3>';
        
        // Summary
        $total = count($this->results);
        echo '<div class="summary">';
        echo '<div class="summary-item summary-total">';
        echo '<h4>' . $total . '</h4>';
        echo '<p>Total Processed</p>';
        echo '</div>';
        echo '<div class="summary-item summary-success">';
        echo '<h4>' . $this->success_count . '</h4>';
        echo '<p>Successful</p>';
        echo '</div>';
        echo '<div class="summary-item summary-error">';
        echo '<h4>' . $this->error_count . '</h4>';
        echo '<p>Errors</p>';
        echo '</div>';
        echo '</div>';
        
        // Detailed results
        foreach ($this->results as $result) {
            $class = 'result-' . $result['status'];
            echo '<div class="result-item ' . $class . '">';
            echo '<div class="result-info">';
            
            if (isset($result['user'])) {
                echo '<strong>' . esc_html($result['user']->display_name) . '</strong> (' . esc_html($result['user']->user_email) . ')';
            } else {
                echo '<strong>' . esc_html($result['input']) . '</strong>';
            }
            
            echo '<br><small>' . esc_html($result['message']);
            if (isset($result['transaction_id'])) {
                echo ' (Transaction ID: ' . esc_html($result['transaction_id']) . ')';
            }
            echo '</small>';
            echo '</div>';
            
            $status_class = 'status-' . $result['status'];
            echo '<div class="result-status ' . $status_class . '">' . esc_html($result['status']) . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Check if MemberPress is active
     */
    private function is_memberpress_active() {
        return class_exists('MeprUser') || function_exists('mepr_autoloader');
    }
    
    /**
     * Alternative transaction creation using MemberPress native methods
     */
    private function create_memberpress_transaction_native($user_id, $membership_id) {
        // If MemberPress classes are available, try to use them
        if (class_exists('MeprTransaction')) {
            $txn = new MeprTransaction();
            $txn->user_id = $user_id;
            $txn->product_id = $membership_id;
            $txn->amount = 0.00;
            $txn->total = 0.00;
            $txn->tax_amount = 0.00;
            $txn->tax_rate = 0.00;
            $txn->status = 'complete';
            $txn->gateway = 'manual';
            $txn->trans_num = 'manual_' . time() . '_' . $user_id . '_' . $membership_id;
            $txn->created_at = date('Y-m-d H:i:s');
            $txn->expires_at = null; // Lifetime
            
            $transaction_id = $txn->store();
            
            if ($transaction_id) {
                return $transaction_id;
            }
        }
        
        // Fallback to direct database insert
        return $this->create_manual_transaction($user_id, $membership_id);
    }
    
    /**
     * Render login required message
     */
    private function render_login_message() {
        $admin_url = admin_url();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Required - Manual Membership Assignment</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f1f1f1;
                    margin: 0;
                    padding: 20px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 500px;
                }
                .login-container h1 {
                    color: #333;
                    margin-bottom: 20px;
                }
                .login-container p {
                    color: #666;
                    line-height: 1.6;
                    margin-bottom: 30px;
                }
                .login-btn {
                    display: inline-block;
                    background: #007cba;
                    color: white;
                    padding: 15px 30px;
                    text-decoration: none;
                    border-radius: 6px;
                    font-weight: 600;
                    transition: background 0.2s;
                }
                .login-btn:hover {
                    background: #005a87;
                    color: white;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>🔒 Login Required</h1>
                <p>You need to be logged in as a WordPress administrator to access the Manual Membership Assignment tool.</p>
                <p>Please log in to the WordPress admin panel first, then return to this page.</p>
                <a href="<?php echo esc_url($admin_url); ?>" class="login-btn">Log in to WordPress Admin</a>
            </div>
        </body>
        </html>
        <?php
    }
}

// Initialize the tool
new WCB_Manual_Membership_Assignment();