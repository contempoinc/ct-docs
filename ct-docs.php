<?php
/**
 * Plugin Name: Contempo Docs
 * Plugin URI: https://contempothemes.com/
 * Description: A streamlined documentation plugin with live search, auto-generated TOC, and Elementor widgets.
 * Version: 1.1.2
 * Author: Contempo Themes
 * Author URI: https://contempothemes.com/
 * Text Domain: ct-docs
 *
 * @package CT_Docs
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Constants
 */
define( 'CT_DOCS_VERSION', '1.1.2' );
define( 'CT_DOCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CT_DOCS_URL', plugin_dir_url( __FILE__ ) );
define( 'CT_DOCS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for plugin classes
 */
spl_autoload_register( function( $class ) {
    // Only autoload CT_Docs classes
    if ( strpos( $class, 'CT_Docs_' ) !== 0 ) {
        return;
    }
    
    // Convert class name to file name
    $class_name = str_replace( 'CT_Docs_', '', $class );
    $class_name = strtolower( str_replace( '_', '-', $class_name ) );
    
    // Check includes directory
    $file = CT_DOCS_PATH . 'includes/class-ct-docs-' . $class_name . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
        return;
    }
    
    // Check elementor directory
    $file = CT_DOCS_PATH . 'elementor/class-ct-docs-' . $class_name . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
        return;
    }
    
    // Check admin directory
    $file = CT_DOCS_PATH . 'admin/class-ct-docs-' . $class_name . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
        return;
    }
});

/**
 * Plugin activation hook
 */
function ct_docs_activate() {
    // Load CPT and Taxonomy classes
    require_once CT_DOCS_PATH . 'includes/class-ct-docs-cpt.php';
    require_once CT_DOCS_PATH . 'includes/class-ct-docs-taxonomy.php';
    
    // Register CPT and Taxonomy
    CT_Docs_CPT::register();
    CT_Docs_Taxonomy::register();
    
    // Create default terms if they don't exist (preserves BetterDocs terms)
    CT_Docs_Taxonomy::create_default_terms();
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set activation flag for admin notice
    set_transient( 'ct_docs_activation_notice', true, 30 );
}
register_activation_hook( __FILE__, 'ct_docs_activate' );

/**
 * Plugin deactivation hook
 */
function ct_docs_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Clear all plugin transients
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ct_docs_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ct_docs_%'" );
}
register_deactivation_hook( __FILE__, 'ct_docs_deactivate' );

/**
 * Initialize the plugin
 */
function ct_docs_init() {
    // Load core class
    require_once CT_DOCS_PATH . 'includes/class-ct-docs-core.php';
    
    // Initialize plugin
    CT_Docs_Core::instance();
}
add_action( 'plugins_loaded', 'ct_docs_init' );

/**
 * Helper function to get read time for a doc
 *
 * @param int $post_id Post ID
 * @return int Read time in minutes
 */
function ct_docs_get_read_time( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    $read_time = get_post_meta( $post_id, '_ct_docs_read_time', true );
    
    if ( empty( $read_time ) ) {
        $post = get_post( $post_id );
        if ( $post ) {
            $word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
            $read_time = max( 1, ceil( $word_count / 200 ) );
            update_post_meta( $post_id, '_ct_docs_read_time', $read_time );
        }
    }
    
    return intval( $read_time );
}

/**
 * Helper function to get related docs
 *
 * @param int $post_id Post ID
 * @param int $limit Number of related docs to return
 * @return array Array of WP_Post objects
 */
function ct_docs_get_related( $post_id = null, $limit = 3 ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    // Get post terms
    $terms = get_the_terms( $post_id, 'doc_category' );
    
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return array();
    }
    
    $term_ids = wp_list_pluck( $terms, 'term_id' );
    
    // Query related docs from same category
    $args = array(
        'post_type'      => 'docs',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'post__not_in'   => array( $post_id ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'doc_category',
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ),
        ),
    );
    
    $related = get_posts( $args );
    
    // If not enough results, get from other categories
    if ( count( $related ) < $limit ) {
        $remaining = $limit - count( $related );
        $exclude_ids = array_merge( array( $post_id ), wp_list_pluck( $related, 'ID' ) );
        
        $args = array(
            'post_type'      => 'docs',
            'post_status'    => 'publish',
            'posts_per_page' => $remaining,
            'post__not_in'   => $exclude_ids,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        
        $more_docs = get_posts( $args );
        $related = array_merge( $related, $more_docs );
    }
    
    return $related;
}

