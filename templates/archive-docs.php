<?php
/**
 * Archive template for Docs
 *
 * This is a minimal template that allows Elementor to take over.
 * The actual layout should be built with Elementor using CT Docs widgets.
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div id="ct-docs-archive" class="ct-docs-archive-wrapper">
    <?php
    /**
     * If using Elementor, this template serves as a fallback.
     * The primary archive page should be built with Elementor.
     * 
     * However, if no custom Elementor template exists, we provide
     * a basic fallback layout.
     */
    
    // Check if Elementor has a custom archive template
    if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'archive' ) ) {
        // Elementor handles the content
    } else {
        // Fallback template
        ?>
        <div class="ct-docs-archive-fallback">
            <header class="ct-docs-archive-header">
                <h1 class="ct-docs-archive-title">Documentation</h1>
                <p class="ct-docs-archive-description">Browse our documentation and knowledge base articles.</p>
            </header>

            <div class="ct-docs-archive-search">
                <?php echo do_shortcode( '[ct_docs_search placeholder="Search documentation..."]' ); ?>
            </div>

            <div class="ct-docs-archive-content">
                <?php
                // Display docs by category
                $categories = get_terms( array(
                    'taxonomy'   => 'doc_category',
                    'hide_empty' => true,
                ) );

                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
                    foreach ( $categories as $category ) :
                        ?>
                        <section class="ct-docs-category-section">
                            <h2 class="ct-docs-category-title"><?php echo esc_html( $category->name ); ?></h2>
                            
                            <?php
                            $docs = get_posts( array(
                                'post_type'      => 'docs',
                                'post_status'    => 'publish',
                                'posts_per_page' => -1,
                                'orderby'        => 'title',
                                'order'          => 'ASC',
                                'tax_query'      => array(
                                    array(
                                        'taxonomy' => 'doc_category',
                                        'field'    => 'term_id',
                                        'terms'    => $category->term_id,
                                    ),
                                ),
                            ) );

                            if ( ! empty( $docs ) ) :
                                ?>
                                <ul class="ct-docs-list">
                                    <?php foreach ( $docs as $doc ) : ?>
                                        <li class="ct-docs-list-item">
                                            <a href="<?php echo esc_url( get_permalink( $doc->ID ) ); ?>">
                                                <?php echo esc_html( $doc->post_title ); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php
                            endif;
                            ?>
                        </section>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php
get_footer();

