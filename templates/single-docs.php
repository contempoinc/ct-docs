<?php
/**
 * Single Doc template
 *
 * Three-column layout:
 * - Left: Accordion navigation
 * - Center: Content with breadcrumbs
 * - Right: Sticky TOC
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Get current post
$current_post_id = get_the_ID();

// Generate TOC from post content (for sidebar)
$toc_html = CT_Docs_TOC_Generator::get_toc( $current_post_id );

// Get docs page URL
$docs_page_url = get_permalink( CT_Docs_CPT::get_docs_page_id() );
?>

<!-- Desktop Docs Header -->
<header class="ct-docs-page-header ct-docs-page-header--sticky ct-docs-desktop-header">
    <div class="ct-docs-page-header-inner">
        <a href="<?php echo esc_url( $docs_page_url ); ?>" class="ct-docs-page-header-title">
            <span>Documentation</span>
        </a>
        <div class="ct-docs-page-header-search">
            <?php echo do_shortcode( '[ct_docs_search placeholder="Search docs..."]' ); ?>
        </div>
    </div>
</header>

<!-- Mobile Header Bar -->
<header class="ct-docs-mobile-header">
    <button class="ct-docs-mobile-menu-btn" aria-label="Open navigation" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
        <span>Menu</span>
    </button>
    
    <a href="<?php echo esc_url( $docs_page_url ); ?>" class="ct-docs-mobile-header-title">Docs</a>
    
    <?php if ( ! empty( $toc_html ) ) : ?>
    <button class="ct-docs-mobile-toc-btn" aria-label="Table of contents" aria-expanded="false">
        <span>Contents</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
        </svg>
    </button>
    <?php endif; ?>
</header>

<div id="ct-docs-single" class="ct-docs-single-wrapper">
    
    <!-- Backdrop for mobile overlays -->
    <div class="ct-docs-backdrop"></div>

    <!-- Left Sidebar: Accordion Navigation -->
    <aside class="ct-docs-sidebar" role="navigation" aria-label="Documentation navigation">
        <?php include CT_DOCS_PATH . 'templates/parts/sidebar-accordion.php'; ?>
    </aside>

    <!-- Main Content Area -->
    <main class="ct-docs-content" role="main">
        <?php
        while ( have_posts() ) :
            the_post();
            
            // Breadcrumbs
            include CT_DOCS_PATH . 'templates/parts/breadcrumbs.php';
            ?>
            
            <article id="doc-<?php the_ID(); ?>" <?php post_class( 'ct-docs-article' ); ?>>
                <header class="ct-docs-header">
                    <h1 class="ct-docs-title"><?php the_title(); ?></h1>
                    
                    <div class="ct-docs-meta">
                        <span class="ct-docs-read-time">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <?php echo esc_html( ct_docs_get_read_time( $current_post_id ) ); ?> min read
                        </span>
                        
                        <?php
                        $terms = get_the_terms( $current_post_id, 'doc_category' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
                            ?>
                            <span class="ct-docs-category">
                                <?php echo esc_html( $terms[0]->name ); ?>
                            </span>
                            <?php
                        endif;
                        ?>
                    </div>
                </header>
                
                <div class="ct-docs-body">
                    <?php the_content(); ?>
                </div>
                
                <!-- Related Docs -->
                <?php include CT_DOCS_PATH . 'templates/parts/related-docs.php'; ?>
                
            </article>
            
        <?php endwhile; ?>
    </main>

    <!-- Right Sidebar: Sticky TOC -->
    <aside class="ct-docs-toc-sidebar" role="complementary" aria-label="Table of contents">
        <div class="ct-docs-toc-sticky">
            <?php echo $toc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </aside>

</div>

<!-- Schema Markup -->
<script type="application/ld+json">
<?php
$schema = array(
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id'   => get_permalink(),
    ),
    'headline'      => get_the_title(),
    'datePublished' => get_the_date( 'c' ),
    'dateModified'  => get_the_modified_date( 'c' ),
    'author'        => array(
        '@type' => 'Organization',
        'name'  => 'Contempo Themes',
        'url'   => 'https://contempothemes.com',
    ),
    'publisher'     => array(
        '@type' => 'Organization',
        'name'  => 'Contempo Themes',
        'logo'  => array(
            '@type' => 'ImageObject',
            'url'   => home_url( '/wp-content/themes/contempo-themes/screenshot.png' ),
        ),
    ),
    'description'   => get_the_excerpt(),
    'wordCount'     => str_word_count( wp_strip_all_tags( get_the_content() ) ),
);

echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
?>
</script>

<?php
get_footer();

