<?php
/**
 * Search Functionality
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Search class
 */
class CT_Docs_Search {

    /**
     * Initialize search hooks
     */
    public static function init() {
        add_action( 'wp_ajax_ct_docs_search', array( __CLASS__, 'ajax_search' ) );
        add_action( 'wp_ajax_nopriv_ct_docs_search', array( __CLASS__, 'ajax_search' ) );
    }

    /**
     * AJAX search handler
     */
    public static function ajax_search() {
        // Verify nonce
        if ( ! check_ajax_referer( 'ct_docs_search_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        }

        // Get and sanitize search term
        $search_term = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 8;

        if ( strlen( $search_term ) < 2 ) {
            wp_send_json_success( array( 'results' => array() ) );
        }

        // Check cache first
        $cache_key = 'search_' . md5( $search_term . '_' . $limit );
        $cached_results = CT_Docs_Cache::get( $cache_key );

        if ( false !== $cached_results ) {
            wp_send_json_success( array( 'results' => $cached_results ) );
        }

        // Perform search
        $results = self::search_docs( $search_term, $limit );

        // Cache results for 30 minutes
        CT_Docs_Cache::set( $cache_key, $results );

        wp_send_json_success( array( 'results' => $results ) );
    }

    /**
     * Search docs
     *
     * @param string $search_term Search term
     * @param int    $limit       Max results
     * @return array Search results
     */
    public static function search_docs( $search_term, $limit = 8 ) {
        global $wpdb;

        // Search in title and content
        $like_term = '%' . $wpdb->esc_like( $search_term ) . '%';

        $query = $wpdb->prepare(
            "SELECT ID, post_title, post_content, post_excerpt
             FROM {$wpdb->posts}
             WHERE post_type = 'docs'
             AND post_status = 'publish'
             AND (post_title LIKE %s OR post_content LIKE %s)
             ORDER BY 
                CASE WHEN post_title LIKE %s THEN 0 ELSE 1 END,
                post_title ASC
             LIMIT %d",
            $like_term,
            $like_term,
            $like_term,
            $limit
        );

        $posts = $wpdb->get_results( $query );

        $results = array();

        foreach ( $posts as $post ) {
            // Get category
            $terms = get_the_terms( $post->ID, 'doc_category' );
            $category = '';
            $category_slug = '';
            
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $category = $terms[0]->name;
                $category_slug = $terms[0]->slug;
            }

            // Generate excerpt
            $excerpt = ! empty( $post->post_excerpt ) 
                ? $post->post_excerpt 
                : wp_trim_words( wp_strip_all_tags( $post->post_content ), 20, '...' );

            // Highlight search term in excerpt
            $excerpt = self::highlight_term( $excerpt, $search_term );

            $results[] = array(
                'id'            => $post->ID,
                'title'         => $post->post_title,
                'url'           => get_permalink( $post->ID ),
                'excerpt'       => $excerpt,
                'category'      => $category,
                'category_slug' => $category_slug,
            );
        }

        return $results;
    }

    /**
     * Highlight search term in text
     *
     * @param string $text        Text to highlight
     * @param string $search_term Search term
     * @return string Text with highlighted term
     */
    private static function highlight_term( $text, $search_term ) {
        $pattern = '/(' . preg_quote( $search_term, '/' ) . ')/i';
        return preg_replace( $pattern, '<mark>$1</mark>', $text );
    }
}

