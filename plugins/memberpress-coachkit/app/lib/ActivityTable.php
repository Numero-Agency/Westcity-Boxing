<?php

namespace memberpress\coachkit\lib;

use memberpress\coachkit\lib\View;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\models\Enrollment;
use memberpress\coachkit\helpers\StudentHelper;
use memberpress\coachkit\models\StudentProgress;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( 'WP_List_Table' ) ) {
  include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ActivityTable extends \WP_List_Table {

  public $screen;
  public $columns;
  public $sortable;

  public $searchable;
  public $db_search_cols;
  public $totalitems;

  public function __construct( $screen, $columns = array() ) {
    if ( is_string( $screen ) ) {
      $screen = convert_to_screen( $screen );
    }

    $this->screen = $screen;

    if ( ! empty( $columns ) ) {
      $this->columns = $columns;
    }

    $this->searchable = array(
      'username'   => __( 'Username', 'memberpress-coachkit' ),
      'email'      => __( 'Email', 'memberpress-coachkit' ),
      'first_name' => __( 'First Name', 'memberpress-coachkit' ),
      'last_name'  => __( 'Last Name', 'memberpress-coachkit' ),
      'id'         => __( 'Id', 'memberpress-coachkit' ),
    );

    $this->db_search_cols = array(
      // 'username'   => 'u.user_login',
      // 'email'      => 'u.user_email',
      // 'first_name' => 'pm_first_name.meta_value',
      // 'last_name'  => 'pm_last_name.meta_value',
      // 'id' => 'e.ID',
    );

    parent::__construct(
      array(
        'singular' => 'wp_list_mpch_enrollments', // Singular label
        'plural'   => 'wp_list_mpch_enrollments', // plural label, also this will be one of the table css class
        'ajax'     => true, // false //We won't support Ajax for this table
      )
    );
  }
  // protected function display_tablenav( $which ) { }

  /**
   * Gets a list of all, hidden, and sortable columns, with filter applied.
   *
   * @return array
   */
  public function get_column_info() {
    $columns = get_column_headers( $this->screen );
    $hidden  = get_hidden_columns( $this->screen );

    // Bypass MeprHooks to call built-in filter
    $sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $this->get_sortable_columns() );
    $primary  = 'student';
    return array( $columns, $hidden, $sortable, $primary );
  }

  /**
   * Gets a list of columns.
   *
   * @return array
   */
  public function get_columns() {
     return $this->columns;
  }

  /**
   * Gets a list of sortable columns.
   *
   * @return array
   */
  public function get_sortable_columns() {
    $sortable = array(
      'column-title'       => array( 'name', true ),
      'column-programs'    => array( 'programs', true ),
      'column-last_active' => array( 'last_login_date', true ),
    );
    return $sortable;
  }

  /**
   * Prepares the list of items for displaying.
   *
   * @uses WP_List_Table::set_pagination_args()
   *
   * @abstract
   */
  public function prepare_items() {
    $user_id = get_current_user_id();
    $screen  = get_current_screen();

    if ( isset( $screen ) && is_object( $screen ) ) {
      $option = $screen->get_option( 'per_page', 'option' );

      $perpage = ! empty( $option ) ? get_user_meta( $user_id, $option, true ) : 10;
      $perpage = ! empty( $perpage ) && ! is_array( $perpage ) ? $perpage : 10;

      // Specifically for the CSV export to work properly
      $_SERVER['QUERY_STRING'] = ( empty( $_SERVER['QUERY_STRING'] ) ? '?' : "{$_SERVER['QUERY_STRING']}&" ) . "perpage={$perpage}";
    } else {
      $perpage = ! empty( $_GET['perpage'] ) ? esc_sql( $_GET['perpage'] ) : 10;
    }

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $orderby      = ! empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'created_at';
    $order        = ! empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'DESC';
    $paged        = ! empty( $_GET['paged'] ) ? esc_sql( $_GET['paged'] ) : 1;
    $search       = ! empty( $_GET['search'] ) ? esc_sql( $_GET['search'] ) : '';
    $search_field = ! empty( $_GET['search-field'] ) ? esc_sql( $_GET['search-field'] ) : 'any';
    $search_field = isset( $this->db_search_cols[ $search_field ] ) ? $this->db_search_cols[ $search_field ] : 'any';
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    $list_table = Activity::list_table( $orderby, $order, $paged, $search, $search_field, $perpage );
    $totalitems = $list_table['count'];
    // How many pages do we have in total?
    $totalpages = ceil( $totalitems / $perpage );

    /* -- Register the pagination -- */
    $this->set_pagination_args(
      array(
        'total_items' => $totalitems,
        'total_pages' => $totalpages,
        'per_page'    => $perpage,
      )
    );

    /* -- Register the Columns -- */
    if ( isset( $screen ) && is_object( $screen ) ) {
      $this->_column_headers = $this->get_column_info();
    } else { // For CSV to work properly
      $this->_column_headers = array(
        $this->get_columns(),
        array(),
        $this->get_sortable_columns(),
      );
    }
    $this->totalitems = $totalitems;

    $results = new Collection( $list_table['results'] );

    $items = $results->map(
      function ( $e ) {
        $coach           = new Coach( $e->coach_id );
        $e->student_url  = StudentHelper::get_profile_url( $e->student_id );
        $e->started      = Utils::format_date_readable( $e->started );
        $e->progress     = StudentProgress::progress_percentage( $e->program_id, $e->id );
        $e->off_track    = ! StudentProgress::is_student_on_track_for_program( $e->student_id, $e->program_id );
        $e->coach        = $coach->fullname;
        $e->student_name = empty( trim( $e->student_name ) ) ? $e->student_display_name : $e->student_name;
        return $e;
      }
    )->all();

    $this->items = $items;
  }

  /**
   * Generates the table rows.
   */
  public function display_rows() {
    // Get the records registered in the prepare_items method
    $rows = $this->items;

    // Get the columns registered in the get_columns and get_sortable_columns methods
    list($columns, $hidden) = $this->get_column_info();

    View::render( '/admin/activity/row', compact( 'rows' ) );
  }

  /**
   * Gets the number of items to display.
   *
   * @return int
   */
  public function get_items() {
    return $this->items;
  }
}
