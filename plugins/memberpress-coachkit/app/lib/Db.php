<?php

namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

use memberpress\coachkit as base;

class Db {
  public $prefix, $groups, $milestones, $checkins, $enrollments, $student_progress, $habits, $rooms, $room_participants, $message_attachments, $messages, $notes;

  public function __construct() {
     global $wpdb;

    $this->prefix = $wpdb->prefix . base\SLUG_KEY . '_';

    // Tables
    $this->groups              = "{$this->prefix}groups";
    $this->milestones          = "{$this->prefix}milestones";
    $this->habits              = "{$this->prefix}habits";
    $this->checkins            = "{$this->prefix}check_ins";
    $this->enrollments         = "{$this->prefix}enrollments";
    $this->student_progress    = "{$this->prefix}student_progress";
    $this->notes               = "{$this->prefix}notes";
    $this->messages            = "{$this->prefix}messages";
    $this->message_attachments = "{$this->prefix}message_attachments";
    $this->room_participants   = "{$this->prefix}room_participants";
    $this->rooms               = "{$this->prefix}rooms";
  }

  public static function fetch( $force = false ) {
    static $db;

    if ( ! isset( $db ) || $force ) {
      $db = new Db();
    }

    return apply_filters( base\SLUG_KEY . '_fetch_db', $db );
  }

  public function upgrade() {
    global $wpdb;

    static $upgrade_already_running;

    if ( isset( $upgrade_already_running ) && true === $upgrade_already_running ) {
      return;
    } else {
      $upgrade_already_running = true;
    }

    $old_db_version = get_option( base\SLUG_KEY . '_db_version' );

    if ( base\DB_VERSION > $old_db_version ) {
      // Ensure our big queries can run in an upgrade
      $wpdb->query( 'SET SQL_BIG_SELECTS=1' ); // This may be getting set back to 0 when SET MAX_JOIN_SIZE is executed
      $wpdb->query( 'SET MAX_JOIN_SIZE=18446744073709551615' );

      $this->before_upgrade( $old_db_version );

      // This was introduced in WordPress 3.5
      // $char_col = $wpdb->get_charset_collate(); //This doesn't work for most non english setups
      $char_col  = '';
      $collation = $wpdb->get_row( "SHOW FULL COLUMNS FROM {$wpdb->posts} WHERE field = 'post_content'" );

      if ( isset( $collation->Collation ) ) {
        $charset = explode( '_', $collation->Collation );

        if ( is_array( $charset ) && count( $charset ) > 1 ) {
          $charset  = $charset[0]; // Get the charset from the collation
          $char_col = "DEFAULT CHARACTER SET {$charset} COLLATE {$collation->Collation}";
        }
      }

      // Fine we'll try it your way this time
      if ( empty( $char_col ) ) {
        $char_col = $wpdb->get_charset_collate();
      }

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';

      $groups =
        "CREATE TABLE {$this->groups} (
        `id` bigint(20) NOT NULL auto_increment,
        `title` text NOT NULL,
        `status` varchar(255) NOT NULL,
        `allow_enrollment_cap` tinyint(1) NOT NULL,
        `enrollment_cap` varchar(20) NOT NULL,
        `allow_appointments` tinyint(1) NOT NULL,
        `appointment_url` varchar(512) DEFAULT NULL,
        `program_id` bigint(20) NOT NULL,
        `type` varchar(255) NOT NULL,
        `start_date` datetime NOT NULL,
        `end_date` datetime NOT NULL,
        `coach_id` bigint(20) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY `coach_id` (`coach_id`),
        KEY `program_id` (`program_id`),
        KEY `status` (`status`)
      ) {$char_col};";
      dbDelta( $groups );

      $milestones =
        "CREATE TABLE {$this->milestones} (
        `id` bigint(20) NOT NULL auto_increment,
        `title` text NOT NULL,
        `timing` varchar(255) NOT NULL,
        `due_length` int(11) NOT NULL,
        `due_unit` varchar(20) NOT NULL,
        `program_id` bigint(20) NOT NULL,
        `downloads` text NOT NULL,
        `courses` text NOT NULL,
        `enable_checkin` tinyint(1) NOT NULL,
        `position` int(11) DEFAULT 0,
        `uuid` varchar(40) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY `program_id` (`program_id`),
        KEY `position` (`position`),
        KEY `uuid` (`uuid`)
      ) {$char_col};";
      dbDelta( $milestones );

      $habits =
        "CREATE TABLE {$this->habits} (
        `id` bigint(20) NOT NULL auto_increment,
        `title` text NOT NULL,
        `timing` varchar(255) NOT NULL,
        `repeat_interval` varchar(80) NOT NULL,
        `repeat_length` int(11) NOT NULL,
        `repeat_days` varchar(255) NOT NULL,
        `program_id` bigint(20) NOT NULL,
        `downloads` text NOT NULL,
        `enable_checkin` tinyint(1) NOT NULL,
        `position` int(11) DEFAULT 0,
        `uuid` varchar(40) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY `program_id` (`program_id`),
        KEY `position` (`position`),
        KEY `uuid` (`uuid`)
      ) {$char_col};";
      dbDelta( $habits );

      $checkins =
        "CREATE TABLE {$this->checkins} (
        `id` bigint(20) NOT NULL auto_increment,
        `question` text NOT NULL,
        `channel` varchar(255) NOT NULL,
        `milestone_id` bigint(20),
        `habit_id` bigint(20),
        PRIMARY KEY  (id),
        KEY `milestone_id` (`milestone_id`),
        KEY `habit_id` (`habit_id`)
      ) {$char_col};";
      dbDelta( $checkins );

      $enrollments =
        "CREATE TABLE {$this->enrollments} (
        `id` bigint(20) NOT NULL auto_increment,
        `student_id` bigint(20) NOT NULL,
        `group_id` bigint(20) NOT NULL,
        `program_id` bigint(20) NOT NULL,
        `txn_id` bigint(20) DEFAULT NULL,
        `features` varchar(255) NOT NULL,
        `start_date` datetime NOT NULL,
        `end_date` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id)
      ) {$char_col};";
      dbDelta( $enrollments );

      $student_progress =
        "CREATE TABLE {$this->student_progress} (
        `id` bigint(20) NOT NULL auto_increment,
        `milestone_id` bigint(20) DEFAULT NULL,
        `habit_id` bigint(20) DEFAULT NULL,
        `habit_date` date NULL,
        `enrollment_id` bigint(20) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id)
      ) {$char_col};";
      dbDelta( $student_progress );

      $notes =
        "CREATE TABLE {$this->notes} (
        `id` bigint(20) NOT NULL auto_increment,
        `note` text NOT NULL,
        `coach_id` bigint(20) NOT NULL,
        `student_id` bigint(20) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id)
      ) {$char_col};";
      dbDelta( $notes );

      $room_participants = "CREATE TABLE {$this->room_participants} (
        `id` bigint(20) NOT NULL auto_increment,
        `room_id` bigint(20) NOT NULL,
        `user_id` bigint(20) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY `room_id` (`room_id`),
        KEY `user_id` (`user_id`)
      ) {$char_col};";
      dbDelta( $room_participants );

      $rooms = "CREATE TABLE {$this->rooms} (
        `id` bigint(20) NOT NULL auto_increment,
        `group_id` bigint(20) DEFAULT NULL,
        `uuid` varchar(40) NOT NULL,
        `type` varchar(20) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id)
      ) {$char_col};";
      dbDelta( $rooms );

      $messages = "CREATE TABLE {$this->messages} (
        `id` bigint(20) NOT NULL auto_increment,
        `room_id` bigint(20) NOT NULL,
        `sender_id` bigint(20) NOT NULL,
        `message` text NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id)
      ) {$char_col};";
      dbDelta( $messages );

      $message_attachments = "CREATE TABLE {$this->message_attachments} (
        `id` bigint(20) NOT NULL auto_increment,
        `attachment` text NOT NULL,
        `path` text NOT NULL,
        `url` text NOT NULL,
        `type` varchar(255) NOT NULL,
        `sender_id` bigint(20) NOT NULL,
        `message_id` bigint(20) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY  (id)
      ) {$char_col};";
      dbDelta( $message_attachments );

      $this->after_upgrade( $old_db_version );

      /***** SAVE DB VERSION */
      // Let's only run this query if we're actually updating
      update_option( base\SLUG_KEY . '_db_version', base\DB_VERSION );
    }
  }

  public function before_upgrade( $curr_db_version ) {
    // Nothing yet
  }

  public function after_upgrade( $curr_db_version ) {
    flush_rewrite_rules();
  }

  public function create_record( $table, $args, $record_created_at = true ) {
    global $wpdb;

    $cols   = array();
    $vars   = array();
    $values = array();

    $i = 0;
    foreach ( $args as $key => $value ) {
      if ( $key == 'created_at' && $record_created_at && empty( $value ) ) {
        continue;
      }

      $cols[ $i ] = $key;
      if ( is_numeric( $value ) and preg_match( '!\.!', $value ) ) {
        $vars[ $i ] = '%f';
      } elseif ( is_int( $value ) or is_numeric( $value ) or is_bool( $value ) ) {
        $vars[ $i ] = '%d';
      } else {
        $vars[ $i ] = '%s';
      }

      if ( is_bool( $value ) ) {
        $values[ $i ] = $value ? 1 : 0;
      } else {
        $values[ $i ] = $value;
      }

      $i++;
    }

    if ( $record_created_at && ( ! isset( $args['created_at'] ) || empty( $args['created_at'] ) ) ) {
      $cols[ $i ] = 'created_at';
      $vars[ $i ] = $wpdb->prepare( '%s', Utils::db_now() );
      $i++;
    }

    if ( empty( $cols ) ) {
      return false;
    }

    $cols_str = implode( ',', $cols );
    $vars_str = implode( ',', $vars );

    $query = "INSERT INTO {$table} ( {$cols_str} ) VALUES ( {$vars_str} )";
    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    $query_results = $wpdb->query( $query );

    if ( $query_results ) {
      return $wpdb->insert_id;
    } else {
      return false;
    }
  }

  public function update_record( $table, $id, $args ) {
    global $wpdb;

    if ( empty( $args ) or empty( $id ) ) {
      return false;
    }

    $set    = '';
    $values = array();
    foreach ( $args as $key => $value ) {
      if ( empty( $set ) ) {
        $set .= ' SET';
      } else {
        $set .= ',';
      }

      $set .= " {$key}=";

      if ( is_numeric( $value ) and preg_match( '!\.!', $value ) ) {
        $set .= '%f';
      } elseif ( is_int( $value ) or is_numeric( $value ) or is_bool( $value ) ) {
        $set .= '%d';
      } else {
        $set .= '%s';
      }

      if ( is_bool( $value ) ) {
        $values[] = $value ? 1 : 0;
      } else {
        $values[] = $value;
      }
    }

    $values[] = $id;
    $query    = "UPDATE {$table}{$set} WHERE id=%d";

    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    if ( $wpdb->query( $query ) ) {
      return $id;
    } else {
      return false;
    }
  }

  public function delete_records( $table, $args ) {
    global $wpdb;

    extract( self::get_where_clause_and_values( $args ) );

    $query = "DELETE FROM {$table}{$where}";

    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->query( $query );
  }

  public function get_count( $table, $args = array(), $joins = array() ) {
    global $wpdb;
    $join = '';

    if ( ! empty( $joins ) ) {
      foreach ( $joins as $join_clause ) {
        $join .= " {$join_clause}";
      }
    }

    extract( self::get_where_clause_and_values( $args ) );

    $query = "SELECT COUNT(*) FROM {$table}{$join}{$where}";

    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->get_var( $query );
  }

  public function get_where_clause_and_values( $args ) {
    $args = (array) $args;

    $where  = '';
    $values = array();
    foreach ( $args as $key => $value ) {
      if ( ! empty( $where ) ) {
        $where .= ' AND';
      } else {
        $where .= ' WHERE';
      }

      $where .= " {$key}=";

      if ( is_numeric( $value ) and preg_match( '!\.!', $value ) ) {
        $where .= '%f';
      } elseif ( is_int( $value ) or is_numeric( $value ) or is_bool( $value ) ) {
        $where .= '%d';
      } else {
        $where .= '%s';
      }

      if ( is_bool( $value ) ) {
        $values[] = $value ? 1 : 0;
      } else {
        $values[] = $value;
      }
    }

    return compact( 'where', 'values' );
  }

  public function get_one_model( $model, $args = array() ) {
    $table = $this->get_table_for_model( $model );

    $rec = $this->get_one_record( $table, $args );

    if ( ! empty( $rec ) ) {
      $obj = new $model();
      $obj->load_from_array( $rec );
      return $obj;
    }

    return $rec;
  }

  /**
   * Get one record
   *
   * @param string $table table name.
   * @param array  $args arguments.
   * @return object|null
   */
  public function get_one_record( $table, $args = array() ) {
    global $wpdb;

    extract( self::get_where_clause_and_values( $args ) );

    $query = "SELECT * FROM {$table}{$where} LIMIT 1";

    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->get_row( $query );
  }

  public function get_models( $model, $order_by = '', $limit = '', $args = array() ) {
    $table = $this->get_table_for_model( $model );
    $recs  = $this->get_records( $table, $args, $order_by, $limit );

    $models = array();
    foreach ( $recs as $rec ) {
      $obj = new $model();
      $obj->load_from_array( $rec );
      $models[] = $obj;
    }

    return $models;
  }

  public function get_records( $table, $args = array(), $order_by = '', $limit = '', $joins = array(), $return_type = OBJECT ) {
    global $wpdb;

    extract( self::get_where_clause_and_values( $args ) );
    $join = '';

    if ( ! empty( $order_by ) ) {
      $order_by = " ORDER BY {$order_by}";
    }

    if ( ! empty( $limit ) ) {
      $limit = " LIMIT {$limit}";
    }

    if ( ! empty( $joins ) ) {
      foreach ( $joins as $join_clause ) {
        $join .= " {$join_clause}";
      }
    }

    $query = "SELECT * FROM {$table}{$join}{$where}{$order_by}{$limit}";

    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->get_results( $query, $return_type );
  }


  public function get_col( $table, $col, $args = array(), $order_by = '', $limit = '' ) {
    global $wpdb;

    extract( self::get_where_clause_and_values( $args ) );

    if ( ! empty( $order_by ) ) {
      $order_by = " ORDER BY {$order_by}";
    }
    if ( ! empty( $limit ) ) {
      $limit = " LIMIT {$limit}";
    }

    $query = "SELECT {$table}.{$col} FROM {$table}{$where}{$order_by}{$limit}";

    if ( ! empty( $values ) ) {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->get_col( $query );
  }

  /**
   * Add a metadata field for a given meta table
   *
   * Mimics the behavior of 'add_{type}_meta'
   *
   * @param string $table      Tablename of the meta table.
   * @param string $object_col Column containing the foreign id of the associated object
   * @param int    $object_id  Foreign id of the associated object
   * @param string $meta_key   Meta key
   * @param string $meta_value Meta value. Will be serialized if an object or an array.
   * @param string $unique_key     Key should be unique for the meta_key/object_id
   * @param string $unique_value   Value should be unique for the meta_key/object_id
   * @return int|false The meta ID on success, false on failure.
   */
  public function add_metadata( $table, $object_col, $object_id, $meta_key, $meta_value, $unique_key = false, $unique_value = false ) {
    global $wpdb;

    if ( empty( $meta_key ) || empty( $object_id ) || ! is_numeric( $object_id ) ) {
      return false;
    }

    // expected_slashed ($meta_key)
    $meta_key   = wp_unslash( $meta_key );
    $meta_value = wp_unslash( $meta_value );
    $meta_value = maybe_serialize( $meta_value );

    $object_id = absint( $object_id );
    if ( $unique_key && $wpdb->get_var(
      $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE meta_key = %s AND $object_col = %d",
        $meta_key,
        $object_id
      )
    ) ) {
      return false;
    }

    // check unique value
    if ( $unique_value && $wpdb->get_var(
      $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE meta_key = %s AND meta_value = %s",
        $meta_key,
        $meta_value
      )
    ) ) {
      return false;
    }

    $result = $wpdb->insert(
      $table,
      array(
        $object_col  => $object_id,
        'meta_key'   => $meta_key,
        'meta_value' => $meta_value,
      )
    );

    if ( empty( $result ) ) {
      return false;
    }

    $mid = (int) $wpdb->insert_id;

    return $mid;
  }


  /**
   * Get a metadata field for a given meta table
   *
   * Mimics the behavior of 'get_{type}_meta'
   *
   * @param string $table      Tablename of the meta table.
   * @param string $object_col Column containing the foreign id of the associated object
   * @param int    $object_id  Foreign id of the associated object
   * @param string $meta_key   Meta key
   * @param bool   $single     Return a single value or not
   */
  public function get_metadata( $table, $object_col, $object_id, $meta_key, $single = false ) {
    if ( empty( $meta_key ) || empty( $object_id ) || ! is_numeric( $object_id ) ) {
      return false;
    }

    $meta_value = '';

    if ( $single ) {
      $row = $this->get_one_record(
        $table,
        array(
          $object_col => $object_id,
          'meta_key'  => $meta_key,
        )
      );

      if ( ! empty( $row ) ) {
        $meta_value = maybe_unserialize( $row->meta_value );
      }
    } else {
      $meta_value = $this->get_col(
        $table,
        'meta_value',
        array(
          $object_col => $object_id,
          'meta_key'  => $meta_key,
        )
      );
      $meta_value = array_map( 'maybe_unserialize', $meta_value );
    }

    return $meta_value;
  }

  public function prepare_array( $item_type, $values ) {
    return implode(
      ',',
      array_map(
        function( $value ) use ( $item_type ) {
          global $wpdb;
          return $wpdb->prepare( $item_type, $value );
        },
        $values
      )
    );
  }


  /**
   * Delete metadata for the specified object.
   *
   * @global wpdb $wpdb WordPress database abstraction object.
   *
   * @param string $table      Tablename of the metadata object we're deleting
   * @param string $object_col Column of the object foreign id
   * @param int    $object_id  ID of the object metadata is for
   * @param string $meta_key   Metadata key
   * @param mixed  $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
   *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
   *                           Pass `null, `false`, or an empty string to skip this check. (For backward compatibility,
   *                           it is not possible to pass an empty string to delete those entries with an empty string
   *                           for a value.)
   * @param bool   $delete_all Optional, default is false. If true, delete matching metadata entries for all objects,
   *                           ignoring the specified object_id. Otherwise, only delete matching metadata entries for
   *                           the specified object_id.
   * @return bool True on successful delete, false on failure.
   */
  public function delete_metadata( $table, $object_col, $object_id, $meta_key, $meta_value = '', $delete_all = false ) {
    global $wpdb;

    if ( empty( $meta_key ) || empty( $object_id ) || ! is_numeric( $object_id ) ) {
      return false;
    }

    $object_id = absint( $object_id );
    if ( ! $object_id && ! $delete_all ) {
      return false;
    }

    // expected_slashed ($meta_key)
    $meta_key   = wp_unslash( $meta_key );
    $meta_value = wp_unslash( $meta_value );

    $meta_value = maybe_serialize( $meta_value );

    $query = $wpdb->prepare( "SELECT id FROM {$table} WHERE meta_key = %s", $meta_key );

    if ( ! $delete_all ) {
      $query .= $wpdb->prepare( " AND {$object_col} = %d", $object_id );
    }

    if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value ) {
      $query .= $wpdb->prepare( ' AND meta_value = %s', $meta_value );
    }

    $meta_ids = $wpdb->get_col( $query );
    if ( ! count( $meta_ids ) ) {
      return false;
    }

    if ( $delete_all ) {
      if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value ) {
        $object_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $table WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value ) );
      } else {
        $object_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $table WHERE meta_key = %s", $meta_key ) );
      }
    }

    $query = "DELETE FROM $table WHERE id IN( " . implode( ',', $meta_ids ) . ' )';

    $count = $wpdb->query( $query );
    return ! empty( $count );
  }

  /* Built to work with WordPress' built in WP_List_Table class */
  public static function list_table(
    $cols,
    $from,
    $joins = array(),
    $args = array(),
    $order_by = '',
    $order = '',
    $paged = 0,
    $search = '',
    $perpage = 10,
    $distinct = false
  ) {
    global $wpdb;

    // Setup selects
    $col_str_array = array();
    foreach ( $cols as $col => $code ) {
      $col_str_array[] = "{$code} AS {$col}";
    }

    $col_str = implode( ', ', $col_str_array );

    // Setup Joins
    if ( ! empty( $joins ) ) {
      $join_str = ' ' . implode( ' ', $joins );
    } else {
      $join_str = '';
    }

    $args_str = implode( ' AND ', $args );

    // Ordering parameters
    // Parameters that are going to be used to order the result
    $order_by = ( ! empty( $order_by ) && ! empty( $order ) ) ? ( $order_by = ' ORDER BY ' . $order_by . ' ' . $order ) : '';

    // Page Number
    if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
      $paged = 1;
    }

    $limit = '';
    // adjust the query to take pagination into account
    if ( ! empty( $paged ) && ! empty( $perpage ) ) {
      $offset = ( $paged - 1 ) * $perpage;
      $limit  = ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
    }

    // Searching
    $search_str = '';
    $searches   = array();
    if ( ! empty( $search ) ) {
      foreach ( $cols as $col => $code ) {
        $searches[] = "{$code} LIKE '%{$search}%'";
      }

      if ( ! empty( $searches ) ) {
        $search_str = implode( ' OR ', $searches );
      }
    }

    $conditions = '';

    // Pull Searching into where
    if ( ! empty( $args ) ) {
      if ( ! empty( $searches ) ) {
        $conditions = " WHERE $args_str AND ({$search_str})";
      } else {
        $conditions = " WHERE $args_str";
      }
    } else {
      if ( ! empty( $searches ) ) {
        $conditions = " WHERE {$search_str}";
      }
    }

    if ( $distinct ) {
      $query = "SELECT DISTINCT {$col_str} FROM {$from}{$join_str}{$conditions}{$order_by}{$limit}";
    } else {
      $query = "SELECT {$col_str} FROM {$from}{$join_str}{$conditions}{$order_by}{$limit}";
    }

    $total_query = "SELECT COUNT(*) FROM {$from}{$join_str}{$conditions}";
    // Allows us to run the bazillion JOINS we use on the list tables
    $wpdb->query( 'SET SQL_BIG_SELECTS=1' );
    $results = $wpdb->get_results( $query );
    $count   = $wpdb->get_var( $total_query );

    return array(
      'results' => $results,
      'count'   => $count,
    );
  }

  public function get_table_for_model( $model ) {
    global $wpdb;
    $class_name = wp_unslash( preg_replace( '/^' . wp_slash( base\MODELS_NAMESPACE ) . '(.*)/', '$1', $model ) );
    $table      = Utils::snakecase( $class_name );

    // TODO: We need to get true inflections working here eventually ...
    // Only append an s if it doesn't end in s
    if ( ! preg_match( '/s$/', $table ) ) {
      $table .= 's';
    }

    return "{$this->prefix}{$table}";
  }

  /**
   * Light weight query to check if record exists
   *
   * @param string $table name of table
   * @param array  $args array of args
   * @return true|false
   */
  public function record_exists( $table, $args = array() ) {
    global $wpdb;

    extract( self::get_where_clause_and_values( $args ), EXTR_SKIP );

    $query = "SELECT 1 FROM {$table}{$where} LIMIT 1";

    if ( empty( $values ) ) {
      $query = esc_sql( $query );
    } else {
      $query = $wpdb->prepare( $query, $values );
    }

    $record_exists = $wpdb->get_var( $query );

    return $record_exists === '1' ? true : false;
  }

  public function table_exists( $table ) {
    global $wpdb;
    $q         = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table );
    $table_res = $wpdb->get_var( $q );
    return ( $table_res == $table );
  }

  public function table_empty( $table ) {
    return ( $this->get_count( $table ) <= 0 );
  }

  public function column_exists( $table, $column ) {
    global $wpdb;
    $q   = $wpdb->prepare( "SHOW COLUMNS FROM {$table} LIKE %s", $column );
    $res = $wpdb->get_col( $q );
    return ( count( $res ) > 0 );
  }
}
