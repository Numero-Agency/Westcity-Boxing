<?php
/**
 * Competition Database Table Creation
 * Creates the wp_wcb_competitions table for storing competition data
 */

// Function to create competitions table
function wcb_create_competitions_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'wcb_competitions';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        event_name varchar(255) NOT NULL,
        event_date date NOT NULL,
        event_location varchar(255) DEFAULT '',
        supporters text DEFAULT '',
        results_wins int(11) DEFAULT 0,
        results_losses int(11) DEFAULT 0,
        highlights text DEFAULT '',
        conversations text DEFAULT '',
        created_by bigint(20) UNSIGNED DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY event_date (event_date),
        KEY created_by (created_by)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add foreign key constraint for created_by
    $wpdb->query("ALTER TABLE $table_name ADD CONSTRAINT fk_competition_creator FOREIGN KEY (created_by) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL;");
}

// Function to check if competitions table exists
function wcb_competitions_table_exists() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcb_competitions';
    return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
}

// Function to drop competitions table (for development/cleanup)
function wcb_drop_competitions_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcb_competitions';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// Hook to create table on theme activation
add_action('after_switch_theme', 'wcb_create_competitions_table');

// Admin function to manually create table
function wcb_admin_create_competitions_table() {
    if (current_user_can('manage_options')) {
        wcb_create_competitions_table();
        wp_redirect(admin_url('admin.php?page=wcb-competitions&created=1'));
        exit;
    }
}

// AJAX handler for creating table
add_action('wp_ajax_wcb_create_competitions_table', 'wcb_admin_create_competitions_table');

// Database helper functions for competitions
class WCB_Competition_DB {
    
    /**
     * Insert a new competition record
     */
    public static function insert_competition($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        $defaults = [
            'event_name' => '',
            'event_date' => '',
            'event_location' => '',
            'supporters' => '',
            'results_wins' => 0,
            'results_losses' => 0,
            'highlights' => '',
            'conversations' => '',
            'created_by' => get_current_user_id()
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $table_name,
            $data,
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d']
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Update a competition record
     */
    public static function update_competition($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        $result = $wpdb->update(
            $table_name,
            $data,
            ['id' => $id],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Get a single competition by ID
     */
    public static function get_competition($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
        );
    }
    
    /**
     * Get all competitions with optional filters
     */
    public static function get_competitions($args = []) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        $defaults = [
            'limit' => 50,
            'offset' => 0,
            'order_by' => 'event_date',
            'order' => 'DESC',
            'search' => '',
            'date_from' => '',
            'date_to' => ''
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['1=1'];
        $prepare_args = [];
        
        // Search filter
        if (!empty($args['search'])) {
            $where[] = "(event_name LIKE %s OR event_location LIKE %s OR highlights LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $prepare_args[] = $search_term;
            $prepare_args[] = $search_term;
            $prepare_args[] = $search_term;
        }
        
        // Date range filter
        if (!empty($args['date_from'])) {
            $where[] = "event_date >= %s";
            $prepare_args[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "event_date <= %s";
            $prepare_args[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT c.*, u.display_name as creator_name 
                FROM $table_name c 
                LEFT JOIN {$wpdb->users} u ON c.created_by = u.ID 
                WHERE $where_clause 
                ORDER BY {$args['order_by']} {$args['order']} 
                LIMIT {$args['limit']} OFFSET {$args['offset']}";
        
        if (!empty($prepare_args)) {
            return $wpdb->get_results($wpdb->prepare($sql, $prepare_args));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    /**
     * Get competitions count
     */
    public static function get_competitions_count($args = []) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        $where = ['1=1'];
        $prepare_args = [];
        
        // Search filter
        if (!empty($args['search'])) {
            $where[] = "(event_name LIKE %s OR event_location LIKE %s OR highlights LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $prepare_args[] = $search_term;
            $prepare_args[] = $search_term;
            $prepare_args[] = $search_term;
        }
        
        // Date range filter
        if (!empty($args['date_from'])) {
            $where[] = "event_date >= %s";
            $prepare_args[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "event_date <= %s";
            $prepare_args[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
        
        if (!empty($prepare_args)) {
            return $wpdb->get_var($wpdb->prepare($sql, $prepare_args));
        } else {
            return $wpdb->get_var($sql);
        }
    }
    
    /**
     * Delete a competition
     */
    public static function delete_competition($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        return $wpdb->delete($table_name, ['id' => $id], ['%d']);
    }
    
    /**
     * Get competition statistics
     */
    public static function get_competition_stats($user_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcb_competitions';
        
        $where = '1=1';
        $prepare_args = [];
        
        if ($user_id) {
            $where .= ' AND created_by = %d';
            $prepare_args[] = $user_id;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_competitions,
                    SUM(results_wins) as total_wins,
                    SUM(results_losses) as total_losses,
                    MIN(event_date) as first_competition,
                    MAX(event_date) as last_competition
                FROM $table_name 
                WHERE $where";
        
        if (!empty($prepare_args)) {
            return $wpdb->get_row($wpdb->prepare($sql, $prepare_args));
        } else {
            return $wpdb->get_row($sql);
        }
    }
}

// Create table on plugin/theme activation
if (!wcb_competitions_table_exists()) {
    wcb_create_competitions_table();
}