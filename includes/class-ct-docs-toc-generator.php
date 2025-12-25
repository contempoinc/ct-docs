<?php
/**
 * Table of Contents Generator
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_TOC_Generator class
 */
class CT_Docs_TOC_Generator {

    /**
     * Generate TOC from content
     *
     * @param string $content  Post content
     * @param int    $post_id  Post ID for caching
     * @return array Array with 'toc' HTML and 'content' with IDs added to headings
     */
    public static function generate( $content, $post_id = null ) {
        // Try to get from cache
        if ( $post_id ) {
            $cache_key = 'toc_' . $post_id;
            $cached = CT_Docs_Cache::get( $cache_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        // Find all headings (H2-H5)
        $pattern = '/<h([2-5])[^>]*>(.*?)<\/h[2-5]>/i';
        preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );

        if ( empty( $matches ) ) {
            return array(
                'toc'     => '',
                'content' => $content,
            );
        }

        $toc_items = array();
        $modified_content = $content;
        $used_ids = array();

        foreach ( $matches as $match ) {
            $level = intval( $match[1] );
            $text = wp_strip_all_tags( $match[2] );
            $id = self::generate_id( $text, $used_ids );
            $used_ids[] = $id;

            // Add item to TOC
            $toc_items[] = array(
                'level' => $level,
                'text'  => $text,
                'id'    => $id,
            );

            // Replace heading with ID-enabled version
            $new_heading = sprintf(
                '<h%d id="%s">%s</h%d>',
                $level,
                esc_attr( $id ),
                $match[2],
                $level
            );
            $modified_content = str_replace( $match[0], $new_heading, $modified_content );
        }

        // Generate TOC HTML
        $toc_html = self::render_toc( $toc_items );

        $result = array(
            'toc'     => $toc_html,
            'content' => $modified_content,
            'items'   => $toc_items,
        );

        // Cache the result
        if ( $post_id ) {
            CT_Docs_Cache::set( $cache_key, $result );
        }

        return $result;
    }

    /**
     * Generate unique ID from heading text
     *
     * @param string $text     Heading text
     * @param array  $used_ids Already used IDs
     * @return string Unique ID
     */
    private static function generate_id( $text, $used_ids ) {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $id = sanitize_title( $text );
        
        // Ensure uniqueness
        $base_id = $id;
        $counter = 1;
        
        while ( in_array( $id, $used_ids, true ) ) {
            $id = $base_id . '-' . $counter;
            $counter++;
        }

        return $id;
    }

    /**
     * Render TOC HTML from items
     *
     * @param array $items TOC items
     * @return string TOC HTML
     */
    private static function render_toc( $items ) {
        if ( empty( $items ) ) {
            return '';
        }

        $html = '<nav class="ct-docs-toc" aria-label="Table of Contents">';
        $html .= '<div class="ct-docs-toc-header">';
        $html .= '<span class="ct-docs-toc-title">On this page</span>';
        $html .= '</div>';
        $html .= '<ul class="ct-docs-toc-list">';

        $current_level = 2; // Start at H2

        foreach ( $items as $item ) {
            $level = $item['level'];
            
            // Handle nesting
            while ( $level > $current_level ) {
                $html .= '<ul class="ct-docs-toc-sublist">';
                $current_level++;
            }
            
            while ( $level < $current_level ) {
                $html .= '</ul></li>';
                $current_level--;
            }

            $html .= sprintf(
                '<li class="ct-docs-toc-item ct-docs-toc-level-%d"><a href="#%s">%s</a>',
                $level,
                esc_attr( $item['id'] ),
                esc_html( $item['text'] )
            );
            
            // Don't close li yet if next item might be nested
        }

        // Close any remaining open lists
        while ( $current_level >= 2 ) {
            $html .= '</li></ul>';
            $current_level--;
        }

        $html .= '</nav>';

        return $html;
    }

    /**
     * Filter content to add IDs to headings
     * Does NOT use cache - processes content directly to preserve wpautop formatting
     *
     * @param string $content Post content (already processed by wpautop)
     * @return string Modified content with heading IDs
     */
    public static function filter_content( $content ) {
        if ( ! is_singular( 'docs' ) ) {
            return $content;
        }

        // Find all headings (H2-H5) and add IDs
        $pattern = '/<h([2-5])([^>]*)>(.*?)<\/h[2-5]>/i';
        $used_ids = array();
        
        $content = preg_replace_callback( $pattern, function( $match ) use ( &$used_ids ) {
            $level = $match[1];
            $attrs = $match[2];
            $text = $match[3];
            
            // Skip if already has an ID
            if ( preg_match( '/\bid\s*=/', $attrs ) ) {
                return $match[0];
            }
            
            // Generate ID from heading text
            $id = sanitize_title( wp_strip_all_tags( $text ) );
            $base_id = $id;
            $counter = 1;
            while ( in_array( $id, $used_ids, true ) ) {
                $id = $base_id . '-' . $counter;
                $counter++;
            }
            $used_ids[] = $id;
            
            return sprintf( '<h%s id="%s"%s>%s</h%s>', $level, esc_attr( $id ), $attrs, $text, $level );
        }, $content );

        return $content;
    }

    /**
     * Get just the TOC HTML for current post
     *
     * @param int $post_id Post ID
     * @return string TOC HTML
     */
    public static function get_toc( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return '';
        }

        $result = self::generate( $post->post_content, $post_id );
        return $result['toc'];
    }
}

