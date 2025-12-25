<?php
/**
 * Related Docs Component
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_post_id = get_the_ID();
$related_docs = ct_docs_get_related( $current_post_id, 3 );

if ( empty( $related_docs ) ) {
    return;
}
?>

<section class="ct-docs-related" aria-labelledby="ct-docs-related-title">
    <h2 id="ct-docs-related-title" class="ct-docs-related-heading">Related Documentation</h2>
    
    <div class="ct-docs-related-grid">
        <?php foreach ( $related_docs as $doc ) : 
            $terms = get_the_terms( $doc->ID, 'doc_category' );
            $category_name = ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0]->name : '';
            $excerpt = ! empty( $doc->post_excerpt ) 
                ? $doc->post_excerpt 
                : wp_trim_words( wp_strip_all_tags( $doc->post_content ), 15, '...' );
            ?>
            
            <article class="ct-docs-related-card">
                <a href="<?php echo esc_url( get_permalink( $doc->ID ) ); ?>" class="ct-docs-related-link">
                    <?php if ( $category_name ) : ?>
                        <span class="ct-docs-related-category"><?php echo esc_html( $category_name ); ?></span>
                    <?php endif; ?>
                    
                    <h3 class="ct-docs-related-title"><?php echo esc_html( $doc->post_title ); ?></h3>
                    
                    <p class="ct-docs-related-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                    
                    <span class="ct-docs-related-read-more">
                        Read article
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </span>
                </a>
            </article>
            
        <?php endforeach; ?>
    </div>
</section>

