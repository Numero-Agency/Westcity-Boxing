<?php
namespace memberpress\coachkit\lib;

use memberpress\coachkit\lib\View;
use memberpress\coachkit\models\Coach;
use memberpress\coachkit\helpers\AppHelper;
use memberpress\coachkit\helpers\CoachHelper;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CoachesTable extends \WP_List_Table {
  public $_screen;
  public $_columns;
  public $_sortable;

  public $_searchable;
  public $db_search_cols;
  public $totalitems;

  public function __construct( $screen, $columns = array() ) {
    if ( is_string( $screen ) ) {
      $screen = convert_to_screen( $screen );
    }

    $this->_screen = $screen;

    if ( ! empty( $columns ) ) {
      $this->_columns = $columns;
    }

    $this->_searchable = array(
      'username'   => __( 'Username', 'memberpress-coachkit' ),
      'email'      => __( 'Email', 'memberpress-coachkit' ),
      'first_name' => __( 'First Name', 'memberpress-coachkit' ),
      'last_name'  => __( 'Last Name', 'memberpress-coachkit' ),
      'id'         => __( 'Id', 'memberpress-coachkit' ),
    );

    $this->db_search_cols = array(
      'username'   => 'u.user_login',
      'email'      => 'u.user_email',
      'first_name' => 'pm_first_name.meta_value',
      'last_name'  => 'pm_last_name.meta_value',
      'id'         => 'u.ID',
    );

    parent::__construct(
      array(
        'singular' => 'wp_list_mpch_coaches', // Singular label
        'plural'   => 'wp_list_mpch_coaches', // plural label, also this will be one of the table css class
        'ajax'     => true, // false //We won't support Ajax for this table
      )
    );
  }

  /**
   * Gets a list of all, hidden, and sortable columns, with filter applied.
   *
   * @return array
   */
  public function get_column_info() {
    $columns = get_column_headers( $this->_screen );
    $hidden  = get_hidden_columns( $this->_screen );

    // Bypass MeprHooks to call built-in filter
    $sortable = apply_filters( "manage_{$this->_screen->id}_sortable_columns", $this->get_sortable_columns() );

    $primary = 'col_id';
    return array( $columns, $hidden, $sortable, $primary );
  }

  /**
   * Extra controls to be displayed between bulk actions and pagination.
   *
   * @param string $which
   */
  public function extra_tablenav( $which ) {
    if ( $which == 'top' ) {
      $search_cols = $this->_searchable;
      View::render( '/admin/common/table_controls', compact( 'search_cols' ) );
    }

    if ( $which == 'bottom' ) {
      $action     = 'mpch-coaches';
      $totalitems = $this->totalitems;
      $itemcount  = count( $this->items );
      View::render( '/admin/table_footer', compact( 'action', 'totalitems', 'itemcount' ) );
    }
  }

  /**
   * Gets a list of columns.
   *
   * @return array
   */
  public function get_columns() {
    return $this->_columns;
  }

  /**
   * Gets a list of sortable columns.
   *
   * @return array
   */
  public function get_sortable_columns() {
    return $sortable = array(
      'title'       => array( 'name', true ),
      'programs'    => array( 'programs', true ),
      'last_active' => array( 'last_login_date', true ),
    );
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

    $orderby      = ! empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'registered';
    $order        = ! empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'DESC';
    $paged        = ! empty( $_GET['paged'] ) ? esc_sql( $_GET['paged'] ) : 1;
    $search       = ! empty( $_GET['search'] ) ? esc_sql( $_GET['search'] ) : '';
    $search_field = ! empty( $_GET['search-field'] ) ? esc_sql( $_GET['search-field'] ) : 'any';
    $search_field = isset( $this->db_search_cols[ $search_field ] ) ? $this->db_search_cols[ $search_field ] : 'any';

    $list_table = Coach::list_table( $orderby, $order, $paged, $search, $search_field, $perpage );
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

    /* -- Fetch the items -- */
    $this->items = $list_table['results'];
  }

  /**
   * Generates the table rows.
   */
  public function display_rows() {
    // Get the records registered in the prepare_items method
    $records = Utils::collect( $this->items );
    $records = $records->map(function ( $rec ) {
      $coach = new Coach( $rec->ID );
      return (object) [
        'id'              => $rec->ID,
        'title'           => empty( trim( $rec->name ) ) ? $rec->display_name : $rec->name,
        'email'           => $rec->email,
        'programs'        => count( Coach::get_programs( $rec->ID ) ),
        'last_active'     => AppHelper::format_date( $rec->last_login_date, __( 'Never', 'memberpress-coachkit' ) ),
        'active_students' => count( $coach->get_active_students() ),
        'edit_link'       => CoachHelper::get_profile_url( $rec->ID ),
      ];
    })->all();

    // Get the columns registered in the get_columns and get_sortable_columns methods
    list( $columns, $hidden ) = $this->get_column_info();

    View::render( '/admin/coaches/row', get_defined_vars() );
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
