<?php
/**
 * Breadcrumbs Component
 *
 * Format: Home / Docs / Post Title
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Docs page ID from CPT class (auto-detects environment)
$docs_page_id = CT_Docs_CPT::get_docs_page_id();
$docs_page_url = get_permalink( $docs_page_id );
?>

<nav class="ct-docs-breadcrumbs" aria-label="Breadcrumb">
    <ol class="ct-docs-breadcrumbs-list" itemscope itemtype="https://schema.org/BreadcrumbList">
        
        <!-- Home -->
        <li class="ct-docs-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="item">
                <span itemprop="name">Home</span>
            </a>
            <meta itemprop="position" content="1" />
        </li>
        
        <li class="ct-docs-breadcrumb-separator" aria-hidden="true">/</li>
        
        <!-- Docs -->
        <li class="ct-docs-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a href="<?php echo esc_url( $docs_page_url ); ?>" itemprop="item">
                <span itemprop="name">Docs</span>
            </a>
            <meta itemprop="position" content="2" />
        </li>
        
        <li class="ct-docs-breadcrumb-separator" aria-hidden="true">/</li>
        
        <!-- Current Page (Post Title) -->
        <li class="ct-docs-breadcrumb-item ct-docs-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <span itemprop="name" aria-current="page"><?php the_title(); ?></span>
            <meta itemprop="position" content="3" />
        </li>
        
    </ol>
</nav>

