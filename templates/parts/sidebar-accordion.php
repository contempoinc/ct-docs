<?php
/**
 * Sidebar Accordion Navigation
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_post_id = get_the_ID();
$current_terms = get_the_terms( $current_post_id, 'doc_category' );
$current_category_slug = ! empty( $current_terms ) && ! is_wp_error( $current_terms ) 
    ? $current_terms[0]->slug 
    : '';

// Get docs organized by category
$docs_by_category = CT_Docs_Taxonomy::get_docs_by_category();

// Define display order (Documentation first, then Knowledge Base)
$category_order = array( 'documentation', 'knowledge-base' );
?>

<div class="ct-docs-accordion">
    
    <!-- Search (mobile only) -->
    <div class="ct-docs-sidebar-search">
        <?php echo do_shortcode( '[ct_docs_search placeholder="Search..."]' ); ?>
    </div>
    
    <?php
    foreach ( $category_order as $category_slug ) :
        if ( ! isset( $docs_by_category[ $category_slug ] ) ) {
            continue;
        }
        
        $category_data = $docs_by_category[ $category_slug ];
        $term = $category_data['term'];
        $docs = $category_data['docs'];
        
        // Determine if this accordion should be expanded
        $is_current_category = ( $category_slug === $current_category_slug );
        $is_expanded = $is_current_category || $category_slug === 'documentation';
        ?>
        
        <div class="ct-docs-accordion-item <?php echo $is_expanded ? 'is-open' : ''; ?>" data-category="<?php echo esc_attr( $category_slug ); ?>">
            
            <button 
                class="ct-docs-accordion-header" 
                aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>"
                aria-controls="ct-docs-accordion-<?php echo esc_attr( $category_slug ); ?>"
                id="ct-docs-accordion-btn-<?php echo esc_attr( $category_slug ); ?>"
            >
                <span class="ct-docs-accordion-title"><?php echo esc_html( $term->name ); ?></span>
                <span class="ct-docs-accordion-count"><?php echo esc_html( count( $docs ) ); ?></span>
                <span class="ct-docs-accordion-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </button>
            
            <div 
                class="ct-docs-accordion-content" 
                id="ct-docs-accordion-<?php echo esc_attr( $category_slug ); ?>"
                role="region"
                aria-labelledby="ct-docs-accordion-btn-<?php echo esc_attr( $category_slug ); ?>"
                <?php echo $is_expanded ? '' : 'hidden'; ?>
            >
                <ul class="ct-docs-nav-list">
                    <?php foreach ( $docs as $doc ) : 
                        $is_current = ( $doc->ID === $current_post_id );
                        ?>
                        <li class="ct-docs-nav-item <?php echo $is_current ? 'is-current' : ''; ?>">
                            <a 
                                href="<?php echo esc_url( get_permalink( $doc->ID ) ); ?>"
                                <?php echo $is_current ? 'aria-current="page"' : ''; ?>
                            >
                                <?php echo esc_html( $doc->post_title ); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
        </div>
        
    <?php endforeach; ?>
    
</div>

