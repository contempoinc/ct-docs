<?php
/**
 * Taxonomy Registration
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Taxonomy class
 */
class CT_Docs_Taxonomy {

    /**
     * Register the doc_category taxonomy
     */
    public static function register() {
        $labels = array(
            'name'                       => 'Doc Categories',
            'singular_name'              => 'Doc Category',
            'search_items'               => 'Search Doc Categories',
            'popular_items'              => 'Popular Doc Categories',
            'all_items'                  => 'All Doc Categories',
            'parent_item'                => 'Parent Doc Category',
            'parent_item_colon'          => 'Parent Doc Category:',
            'edit_item'                  => 'Edit Doc Category',
            'view_item'                  => 'View Doc Category',
            'update_item'                => 'Update Doc Category',
            'add_new_item'               => 'Add New Doc Category',
            'new_item_name'              => 'New Doc Category Name',
            'separate_items_with_commas' => 'Separate categories with commas',
            'add_or_remove_items'        => 'Add or remove categories',
            'choose_from_most_used'      => 'Choose from the most used categories',
            'not_found'                  => 'No doc categories found.',
            'no_terms'                   => 'No doc categories',
            'menu_name'                  => 'Categories',
            'items_list_navigation'      => 'Doc Categories list navigation',
            'items_list'                 => 'Doc Categories list',
            'back_to_items'              => '&larr; Back to Doc Categories',
        );

        $args = array(
            'labels'            => $labels,
            'public'            => false, // No public archive pages
            'publicly_queryable'=> false, // No frontend queries
            'show_ui'           => true,  // Show in admin
            'show_in_menu'      => true,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'show_in_quick_edit'=> true,
            'show_admin_column' => true,
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'rewrite'           => false, // No rewrite rules - no archive URLs
        );

        register_taxonomy( 'doc_category', 'docs', $args );
    }

    /**
     * Create default terms if they don't exist
     * This preserves existing BetterDocs terms
     */
    public static function create_default_terms() {
        $default_terms = array(
            'documentation'  => 'Documentation',
            'knowledge-base' => 'Knowledge Base',
        );

        foreach ( $default_terms as $slug => $name ) {
            if ( ! term_exists( $slug, 'doc_category' ) ) {
                wp_insert_term( $name, 'doc_category', array(
                    'slug' => $slug,
                ) );
            }
        }
    }

    /**
     * Get all docs grouped by category
     *
     * @return array Docs grouped by category term
     */
    public static function get_docs_by_category() {
        $categories = get_terms( array(
            'taxonomy'   => 'doc_category',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( is_wp_error( $categories ) || empty( $categories ) ) {
            return array();
        }

        $docs_by_category = array();

        foreach ( $categories as $category ) {
            $docs = get_posts( array(
                'post_type'      => 'docs',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => array(
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'doc_category',
                        'field'    => 'term_id',
                        'terms'    => $category->term_id,
                    ),
                ),
            ) );

            if ( ! empty( $docs ) ) {
                $docs_by_category[ $category->slug ] = array(
                    'term'  => $category,
                    'docs'  => $docs,
                );
            }
        }

        return $docs_by_category;
    }
}

