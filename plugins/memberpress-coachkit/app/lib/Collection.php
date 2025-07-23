<?php

namespace memberpress\coachkit\lib;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
}

class Collection {


  protected $items = [];

  /**
   * Create a new collection.
   *
   * @param  array|null $items
   * @return void
   */
  public function __construct( $items = [] ) {
    $this->items = $this->get_array_items( $items );
  }

  /**
   * Add an item to the collection.
   *
   * @param  mixed $item
   * @return $this
   */
  public function add( $item ) {
    $this->items[] = $item;
    return $this;
  }

  /**
   * Get an item from the collection by key.
   *
   * @param  mixed $key
   * @param  mixed $default
   * @return mixed
   */
  public function get( $key, $default = null ) {
    return array_key_exists( $key, $this->items ) ? $this->items[ $key ] : $this->value( $default );
  }

  /**
   * Get all of the items in the collection.
   *
   * @return array
   */
  public function all() {
    return $this->items;
  }

  /**
   * Get all of the items in the collection. Same as all, just different name
   *
   * @return array
   */
  public function get_items() {
    return $this->items;
  }

  /**
   * Map over each of the items.
   *
   * @param  callable $callback
   * @return static
   */
  public function map( callable $callback ) {
    $keys  = array_keys( $this->items );
    $items = array_map( $callback, $this->items, $keys );
    return new static( array_combine( $keys, $items ) );
  }

  /**
   * Execute a callback over each item.
   *
   * @param  callable $callback
   * @return $this
   */
  public function each( callable $callback ) {
    foreach ( $this->items as $key => $item ) {
      if ( $callback( $item, $key ) === false ) {
        break;
      }
    }
    return $this;
  }

  /**
   * Get the values of a given key.
   *
   * @param  string|int  $value
   * @param  string|null $key
   * @return static
   */
  public function pluck( $value, $key = null ) {
    $results       = [];
    [$value, $key] = $this->explode_pluck_parameters( $value, $key );

    foreach ( $this->items as $item ) {
      $item_value = $this->get_nested_value( $item, $value );

      if ( is_null( $key ) ) {
        $results[] = $item_value;
      } else {
        $item_key = $this->get_nested_value( $item, $key );
        if ( is_object( $item_key ) && method_exists( $item_key, '__toString' ) ) {
          $item_key = (string) $item_key;
        }
        $results[ $item_key ] = $item_value;
      }
    }
    return new static( $results );
  }

  /**
   * Reduce the collection to a single value.
   *
   * @param  callable $callback
   * @param  mixed    $initial
   * @return mixed
   */
  public function reduce( callable $callback, $initial = null ) {
    $result = $initial;
    foreach ( $this->items as $key => $value ) {
      $result = $callback( $result, $value, $key );
    }
    return $result;
  }

  /**
   * Run a filter over each of the items.
   *
   * @param bool|null|callable $callback
   * @return static
   */
  public function filter( callable $callback = null ) {
    if ( $callback ) {
      return new static( array_filter( $this->items, $callback, ARRAY_FILTER_USE_BOTH ) );
    }
    return new static( array_filter( $this->items ) );
  }

  /**
   * Sort the collection using the given callback.
   *
   * @param  mixed $callback
   * @param  int   $options
   * @param  bool  $descending
   * @return static
   */
  public function sort_by($callback, $options = SORT_REGULAR, $descending = false) {
    $results = [];

    $callback = function ($item) use ($callback) {
      return $this->get_nested_value($item, $callback);
    };

    foreach ($this->items as $key => $value) {
      $results[$key] = $callback($value, $key);
    }

    $descending ? arsort($results, $options) : asort($results, $options);

    foreach (array_keys($results) as $key) {
      $results[$key] = $this->items[$key];
    }

    return new static($results);
  }
  /**
   * Sort the collection using the given callback while preserving original order for equal values.
   *
   * @param  callable $callback
   * @param  int      $options
   * @param  bool     $descending
   * @return static
   */
  public function sort_preserve_order( $callback, $options = SORT_REGULAR, $descending = false ) {
    $comparator = function ( $a, $b ) use ( $callback ) {
      return $callback( $a ) <=> $callback( $b );
    };

    // Add an index to each item to preserve the original order
    $indexed_array = array_map(function( $item, $index ) {
      return [
        'index' => $index,
        'item'  => $item,
      ];
    }, $this->items, array_keys( $this->items ));

    // Sort the array based on the callback value
    uasort($indexed_array, function( $a, $b ) use ( $comparator ) {
      $comparison = $comparator( $a['item'], $b['item'] );

      if ( $comparison === 0 ) {
        // If values are equal, preserve the original order
        return $a['index'] - $b['index'];
      }

      return $comparison;
    });

    // Extract the original items
    $sorted_items = array_map(function( $item ) {
      return $item['item'];
    }, $indexed_array);

    if ( $descending ) {
      $sorted_items = array_reverse( $sorted_items );
    }

    // Update the collection with the sorted items
    $this->items = $sorted_items;

    return $this;  // Return the modified collection
  }

  /**
   * Return the first element that passes a given truth test.
   *
   * @param callable|null $callback
   * @param mixed         $default
   * @return mixed
   */
  public function first( callable $callback = null, $default = null ) {
    if ( is_null( $callback ) ) {
      foreach ( $this->items as $item ) {
        return $item;
      }
    }

    foreach ( $this->items as $key => $value ) {
      if ( $callback( $value, $key ) ) {
        return $value;
      }
    }

    return $default instanceof \Closure ? $default() : $default;
  }

  /**
   * Return the last element in an array passing a given truth test.
   *
   * @param  callable|null $callback
   * @param  mixed         $default
   * @return mixed
   */
  public function last( callable $callback = null, $default = null ) {
    if ( is_null( $callback ) ) {
      return empty( $this->items ) ? $this->value( $default ) : end( $this->items );
    }
    return $this->first( array_reverse( $this->items, true ), $callback, $default );
  }

  /**
   * Get the number of items in the collection.
   *
   * @return int
   */
  public function count() {
    return count( $this->items );
  }

  /**
   * Determine if the collection is empty.
   *
   * @return bool
   */
  public function empty() {
    return empty( $this->items );
  }

  /**
   * Determine if an item exists in the collection.
   *
   * @param  mixed $key
   * @param  mixed $value
   * @return bool
   */
  public function contains( $key, $value ) {
    foreach ( $this->items as $item ) {
      if ( $this->get_nested_value( $item, $key ) === $value ) {
        return true;
      }
    }
    return false;
  }

  /**
   * Determine if an item does not exist in the collection.
   *
   * @param  mixed $key
   * @param  mixed $value
   * @return bool
   */
  public function does_not_contain($key, $value){
    foreach ($this->items as $item) {
      if ($this->get_nested_value($item, $key) === $value) {
        return false; // If any item has the specified key-value pair, return false.
      }
    }
    return true; // If none of the items have the specified key-value pair, return true.
  }

  /**
   * Determine if at least one element in the collection passes a given truth test.
   *
   * @param  callable|null $callback
   * @return bool
   */
  public function some( callable $callback = null ) {
    if ( is_null( $callback ) ) {
      return count( $this->items ) > 0;
    }

    foreach ( $this->items as $key => $value ) {
      if ( $callback( $value, $key ) ) {
        return true;
      }
    }

    return false;
  }

  /**
   * Determine if all elements in the collection pass a given truth test.
   *
   * @param  callable $callback
   * @return bool
   */
  public function every( callable $callback ) {
    foreach ( $this->items as $key => $value ) {
      if ( ! $callback( $value, $key ) ) {
        return false;
      }
    }
    return true;
  }


  /**
   * Check if any item in the collection meets the given condition.
   *
   * @param  callable $callback
   * @return bool
   */
  public function any( callable $callback ) {
    foreach ( $this->items as $key => $value ) {
      if ( $callback( $value, $key ) ) {
          return true;
      }
    }
    return false;
  }

  /**
   * Check if none of the items in the collection meet the given condition.
   *
   * @param  callable $callback
   * @return bool
   */
  public function none( callable $callback ) {
    foreach ( $this->items as $key => $value ) {
      if ( $callback( $value, $key ) ) {
          return false; // Found an item that meets the condition, so return false.
      }
    }
    return true; // No item met the condition, so return true.
  }

  /**
   * Get a random item from the collection.
   *
   * @return mixed|null Random item from the collection, or null if the collection is empty.
   */
  public function random() {
    if ( empty( $this->items ) ) {
      return null;
    }

    $random_index = array_rand( $this->items );
    return $this->items[ $random_index ];
  }

  /**
   * Explode the value and key arguments passed to "pluck".
   *
   * @param  mixed       $value
   * @param  string|null $key
   * @return array
   */
  private static function explode_pluck_parameters( $value, $key ) {
    $value = is_string( $value ) ? explode( '.', $value ) : $value;
    $key   = is_null( $key ) || is_array( $key ) ? $key : explode( '.', $key );
    return [ $value, $key ];
  }

  /**
   * Get the array of items.
   *
   * @param  mixed $items
   * @return array
   */
  private function get_array_items( $items ) {
    if ( is_array( $items ) ) {
      return $items;
    } elseif ( $items instanceof self ) {
      return $items->all();
    }
    return (array) $items;
  }

  /**
   * Get a nested value from an array or object.
   *
   * @param  mixed $target
   * @param  mixed $key
   * @param  mixed $default
   * @return mixed
   */
  private function get_nested_value( $target, $key, $default = null ) {
    if ( is_null( $key ) ) {
      return $target;
    }

    $key = is_array( $key ) ? $key : explode( '.', $key );

    foreach ( $key as $i => $segment ) {
      unset( $key[ $i ] );
      if ( is_null( $segment ) ) {
        return $target;
      }
      if ( is_array( $target ) && array_key_exists( $segment, $target ) ) {
        $target = $target[ $segment ];
      } elseif ( is_object( $target ) && isset( $target->{$segment} ) ) {
        $target = $target->{$segment};
      } else {
        return $default;
      }
    }
    return $target;
  }

  /**
   * Call the given Closure with the given value.
   *
   * @param  mixed ...$args
   * @return mixed
   */
  private function value( $value, ...$args ) {
    return $value instanceof \Closure ? $value( ...$args ) : $value;
  }
}
