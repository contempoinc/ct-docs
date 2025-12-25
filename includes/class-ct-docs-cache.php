<?php
/**
 * Cache Manager
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Cache class
 */
class CT_Docs_Cache {

    /**
     * Cache prefix
     */
    const PREFIX = 'ct_docs_';

    /**
     * Default cache duration (30 minutes)
     */
    const DEFAULT_DURATION = 1800;

    /**
     * Get cached value
     *
     * @param string $key Cache key
     * @return mixed|false Cached value or false if not found
     */
    public static function get( $key ) {
        return get_transient( self::PREFIX . $key );
    }

    /**
     * Set cache value
     *
     * @param string $key      Cache key
     * @param mixed  $value    Value to cache
     * @param int    $duration Cache duration in seconds
     * @return bool True if set successfully
     */
    public static function set( $key, $value, $duration = null ) {
        if ( is_null( $duration ) ) {
            $duration = self::DEFAULT_DURATION;
        }
        return set_transient( self::PREFIX . $key, $value, $duration );
    }

    /**
     * Delete cached value
     *
     * @param string $key Cache key
     * @return bool True if deleted successfully
     */
    public static function delete( $key ) {
        return delete_transient( self::PREFIX . $key );
    }

    /**
     * Flush all plugin transients
     */
    public static function flush_all() {
        global $wpdb;
        
        $wpdb->query( 
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_' . self::PREFIX . '%',
                '_transient_timeout_' . self::PREFIX . '%'
            )
        );
    }

    /**
     * Get or set cache with callback
     *
     * @param string   $key      Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int      $duration Cache duration in seconds
     * @return mixed Cached or generated value
     */
    public static function remember( $key, $callback, $duration = null ) {
        $value = self::get( $key );
        
        if ( false === $value ) {
            $value = call_user_func( $callback );
            self::set( $key, $value, $duration );
        }
        
        return $value;
    }
}

