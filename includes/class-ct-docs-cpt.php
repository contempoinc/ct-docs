<?php
/**
 * Custom Post Type Registration
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_CPT class
 */
class CT_Docs_CPT {

    /**
     * Get the Docs page ID based on environment
     * Automatically detects localhost vs production
     *
     * @return int Page ID for the /docs/ Elementor page
     */
    public static function get_docs_page_id() {
        $host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
        
        // Check for localhost environments
        if ( strpos( $host, 'localhost' ) !== false || strpos( $host, '127.0.0.1' ) !== false ) {
            return 2929451; // Local development
        }
        
        // Production (contempothemes.com)
        return 7021;
    }

    /**
     * Register the docs post type
     */
    public static function register() {
        $labels = array(
            'name'                  => 'Docs',
            'singular_name'         => 'Doc',
            'menu_name'             => 'Docs',
            'name_admin_bar'        => 'Doc',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Doc',
            'new_item'              => 'New Doc',
            'edit_item'             => 'Edit Doc',
            'view_item'             => 'View Doc',
            'all_items'             => 'All Docs',
            'search_items'          => 'Search Docs',
            'parent_item_colon'     => 'Parent Docs:',
            'not_found'             => 'No docs found.',
            'not_found_in_trash'    => 'No docs found in Trash.',
            'featured_image'        => 'Featured Image',
            'set_featured_image'    => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image'    => 'Use as featured image',
            'archives'              => 'Doc Archives',
            'insert_into_item'      => 'Insert into doc',
            'uploaded_to_this_item' => 'Uploaded to this doc',
            'filter_items_list'     => 'Filter docs list',
            'items_list_navigation' => 'Docs list navigation',
            'items_list'            => 'Docs list',
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => 'docs',
            'rewrite'             => false, // We handle rewrites manually to avoid conflict with /docs/ page
            'capability_type'     => 'post',
            'has_archive'         => false, // No archive - /docs/ is an Elementor page with slug 'docs'
            'hierarchical'        => false,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-book-alt',
            'show_in_rest'        => true,
            'supports'            => array( 
                'title', 
                'editor', 
                'thumbnail', 
                'excerpt', 
                'revisions',
                'author',
            ),
        );

        register_post_type( 'docs', $args );
        
        // Add custom rewrite rules for single docs at /docs/post-name/
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 20 );
        
        // Filter permalink for docs to use /docs/post-name/
        add_filter( 'post_type_link', array( __CLASS__, 'fix_permalink' ), 10, 2 );
    }

    /**
     * Add rewrite rules for single docs at /docs/post-name/
     * The /docs/ page itself is handled by WordPress page with slug 'docs'
     */
    public static function add_rewrite_rules() {
        // Single doc: /docs/post-name/ -> loads the doc post
        // This rule must be more specific than the page rule
        add_rewrite_rule(
            '^docs/([^/]+)/?$',
            'index.php?docs=$matches[1]',
            'top'
        );
    }

    /**
     * Fix permalinks for docs to show /docs/post-name/
     */
    public static function fix_permalink( $permalink, $post ) {
        if ( $post->post_type !== 'docs' ) {
            return $permalink;
        }
        
        return home_url( '/docs/' . $post->post_name . '/' );
    }
}
