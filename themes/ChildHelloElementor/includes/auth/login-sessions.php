<?php
// Login Sessions & User Tracking

class WCB_Login_Sessions {
    
    public function __init() {
        add_action('wp_login', [$this, 'log_user_login'], 10, 2);
        add_action('wp_logout', [$this, 'log_user_logout']);
        add_action('init', [$this, 'track_user_activity']);
        add_shortcode('user_login_history', [$this, 'user_login_history_shortcode']);
        add_shortcode('active_users_list', [$this, 'active_users_shortcode']);
    }
    
    public function log_user_login($user_login, $user) {
        $session_data = [
            'user_id' => $user->ID,
            'login_time' => current_time('mysql'),
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'login_method' => 'standard' // can be 'standard', 'social', etc.
        ];
        
        // Store in custom table or meta
        update_user_meta($user->ID, 'last_login', current_time('mysql'));
        $this->store_login_session($session_data);
    }
    
    public function log_user_logout() {
        $user_id = get_current_user_id();
        if ($user_id) {
            update_user_meta($user_id, 'last_logout', current_time('mysql'));
            $this->end_login_session($user_id);
        }
    }
    
    public function track_user_activity() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'last_activity', current_time('mysql'));
        }
    }
    
    private function get_user_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    private function store_login_session($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcb_login_sessions';
        
        // Create table if it doesn't exist
        $this->create_login_sessions_table();
        
        $wpdb->insert($table_name, $data);
    }
    
    private function create_login_sessions_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcb_login_sessions';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            login_time datetime NOT NULL,
            logout_time datetime NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            login_method varchar(50) DEFAULT 'standard',
            session_duration int(11) NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY login_time (login_time)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function user_login_history_shortcode($atts) {
        $atts = shortcode_atts(['user_id' => get_current_user_id(), 'limit' => 10], $atts);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_login_sessions';
        
        $sessions = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE user_id = %d 
            ORDER BY login_time DESC 
            LIMIT %d
        ", $atts['user_id'], $atts['limit']));
        
        ob_start();
        ?>
        <div class="login-history">
            <h3>Login History</h3>
            <?php if($sessions): ?>
            <table class="login-sessions-table">
                <tr>
                    <th>Login Time</th>
                    <th>IP Address</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
                <?php foreach($sessions as $session): ?>
                <tr>
                    <td><?php echo date('M j, Y g:i A', strtotime($session->login_time)); ?></td>
                    <td><?php echo esc_html($session->ip_address); ?></td>
                    <td>
                        <?php 
                        if($session->logout_time) {
                            $duration = strtotime($session->logout_time) - strtotime($session->login_time);
                            echo gmdate('H:i:s', $duration);
                        } else {
                            echo 'Active';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $session->logout_time ? 'completed' : 'active'; ?>">
                            <?php echo $session->logout_time ? 'Completed' : 'Active'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>No login history found.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function active_users_shortcode() {
        // Get users active in last 30 minutes
        $cutoff_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        
        $active_users = get_users([
            'meta_query' => [
                [
                    'key' => 'last_activity',
                    'value' => $cutoff_time,
                    'compare' => '>='
                ]
            ]
        ]);
        
        ob_start();
        ?>
        <div class="active-users">
            <h3>Currently Active Users (<?php echo count($active_users); ?>)</h3>
            <?php if($active_users): ?>
            <div class="active-users-list">
                <?php foreach($active_users as $user): ?>
                    <?php 
                    $last_activity = get_user_meta($user->ID, 'last_activity', true);
                    $time_ago = human_time_diff(strtotime($last_activity)) . ' ago';
                    ?>
                    <div class="active-user-item">
                        <div class="user-info">
                            <?php echo get_avatar($user->ID, 32); ?>
                            <div>
                                <strong><?php echo $user->display_name; ?></strong>
                                <small>Last seen: <?php echo $time_ago; ?></small>
                            </div>
                        </div>
                        <span class="status-indicator active"></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>No users currently active.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new WCB_Login_Sessions();
