<?php
/**
 * Core plugin class
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main CT_Docs_Core class
 */
class CT_Docs_Core {

    /**
     * Single instance
     *
     * @var CT_Docs_Core
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return CT_Docs_Core
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once CT_DOCS_PATH . 'includes/class-ct-docs-cpt.php';
        require_once CT_DOCS_PATH . 'includes/class-ct-docs-taxonomy.php';
        require_once CT_DOCS_PATH . 'includes/class-ct-docs-cache.php';
        require_once CT_DOCS_PATH . 'includes/class-ct-docs-search.php';
        require_once CT_DOCS_PATH . 'includes/class-ct-docs-toc-generator.php';
        
        // Admin classes
        if ( is_admin() ) {
            require_once CT_DOCS_PATH . 'admin/class-ct-docs-admin.php';
        }
        
        // Elementor integration (only if Elementor is active)
        if ( did_action( 'elementor/loaded' ) ) {
            require_once CT_DOCS_PATH . 'elementor/class-ct-docs-elementor.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register CPT and Taxonomy
        add_action( 'init', array( 'CT_Docs_CPT', 'register' ) );
        add_action( 'init', array( 'CT_Docs_Taxonomy', 'register' ) );
        
        // Initialize components
        add_action( 'init', array( $this, 'init_components' ) );
        
        // Register shortcodes
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        
        // Enqueue frontend assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        
        // Template loading
        add_filter( 'template_include', array( $this, 'load_template' ) );
        
        // Add body classes
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
        
        // Calculate read time on post save
        add_action( 'save_post_docs', array( $this, 'calculate_read_time' ), 10, 2 );
        
        // Clear cache on post save
        add_action( 'save_post_docs', array( 'CT_Docs_Cache', 'flush_all' ) );
        
        // Elementor late init
        add_action( 'elementor/init', array( $this, 'init_elementor' ) );
    }

    /**
     * Initialize components
     */
    public function init_components() {
        // Initialize search
        CT_Docs_Search::init();
    }

    /**
     * Initialize Elementor integration
     */
    public function init_elementor() {
        if ( class_exists( 'CT_Docs_Elementor' ) ) {
            CT_Docs_Elementor::instance();
        }
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode( 'ct_docs_toc', array( $this, 'shortcode_toc' ) );
        add_shortcode( 'ct_docs_search', array( $this, 'shortcode_search' ) );
    }

    /**
     * TOC Shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function shortcode_toc( $atts ) {
        $atts = shortcode_atts( array(
            'title'    => 'Documentation',
            'category' => 'all',
            'columns'  => 1,
            'count'    => 'no',
        ), $atts, 'ct_docs_toc' );
        
        ob_start();
        
        // Query docs
        $args = array(
            'post_type'      => 'docs',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );
        
        if ( $atts['category'] !== 'all' ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'doc_category',
                    'field'    => 'slug',
                    'terms'    => sanitize_key( $atts['category'] ),
                ),
            );
        }
        
        $docs = get_posts( $args );
        
        if ( ! empty( $docs ) ) :
            $columns = intval( $atts['columns'] );
            $columns = max( 1, min( 3, $columns ) );
        ?>
        <div class="ct-docs-toc-widget">
            <?php if ( ! empty( $atts['title'] ) ) : ?>
                <h3 class="ct-docs-toc-title"><?php echo esc_html( $atts['title'] ); ?></h3>
            <?php endif; ?>
            
            <ul class="ct-docs-toc-list ct-docs-columns-<?php echo esc_attr( $columns ); ?>">
                <?php foreach ( $docs as $doc ) : ?>
                    <li class="ct-docs-toc-item">
                        <a href="<?php echo esc_url( get_permalink( $doc->ID ) ); ?>">
                            <?php echo esc_html( $doc->post_title ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if ( $atts['count'] === 'yes' ) : ?>
                <p class="ct-docs-toc-count"><?php echo esc_html( count( $docs ) ); ?> articles</p>
            <?php endif; ?>
        </div>
        <?php
        endif;
        
        return ob_get_clean();
    }

    /**
     * Search Shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function shortcode_search( $atts ) {
        $atts = shortcode_atts( array(
            'placeholder' => 'Search documentation...',
            'limit'       => 8,
        ), $atts, 'ct_docs_search' );
        
        $unique_id = 'ct-docs-search-' . wp_rand( 1000, 9999 );
        
        ob_start();
        ?>
        <div class="ct-docs-search-widget" id="<?php echo esc_attr( $unique_id ); ?>">
            <div class="ct-docs-search-input-wrapper">
                <input 
                    type="text" 
                    class="ct-docs-search-input" 
                    placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
                    autocomplete="off"
                    aria-label="<?php echo esc_attr( $atts['placeholder'] ); ?>"
                    aria-controls="<?php echo esc_attr( $unique_id ); ?>-results"
                    data-limit="<?php echo esc_attr( intval( $atts['limit'] ) ); ?>"
                />
                <span class="ct-docs-search-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                </span>
                <span class="ct-docs-search-loader" aria-hidden="true"></span>
            </div>
            <div 
                class="ct-docs-search-results" 
                id="<?php echo esc_attr( $unique_id ); ?>-results"
                role="listbox"
                aria-live="polite"
            ></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on docs pages
        if ( ! $this->is_docs_page() ) {
            return;
        }
        
        // Main stylesheet
        wp_enqueue_style(
            'ct-docs-frontend',
            CT_DOCS_URL . 'assets/css/ct-docs-frontend.css',
            array(),
            CT_DOCS_VERSION
        );
        
        // Search JS
        wp_enqueue_script(
            'ct-docs-search',
            CT_DOCS_URL . 'assets/js/ct-docs-search.js',
            array(),
            CT_DOCS_VERSION,
            true
        );
        
        wp_localize_script( 'ct-docs-search', 'ctDocsSearch', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ct_docs_search_nonce' ),
            'noResults' => 'No documentation found. Try different keywords.',
            'error'   => 'Something went wrong. Please try again.',
        ) );
        
        // TOC JS (only on single docs)
        if ( is_singular( 'docs' ) ) {
            wp_enqueue_script(
                'ct-docs-toc',
                CT_DOCS_URL . 'assets/js/ct-docs-toc.js',
                array(),
                CT_DOCS_VERSION,
                true
            );
            
            wp_enqueue_script(
                'ct-docs-accordion',
                CT_DOCS_URL . 'assets/js/ct-docs-accordion.js',
                array(),
                CT_DOCS_VERSION,
                true
            );
        }
    }

    /**
     * Check if current page is a docs page
     *
     * @return bool
     */
    public function is_docs_page() {
        // Single doc pages
        if ( is_singular( 'docs' ) ) {
            return true;
        }
        
        // Docs main page
        if ( is_page( CT_Docs_CPT::get_docs_page_id() ) ) {
            return true;
        }
        
        // Check if page contains our shortcodes or widgets
        global $post;
        if ( $post && ( has_shortcode( $post->post_content, 'ct_docs_toc' ) || has_shortcode( $post->post_content, 'ct_docs_search' ) ) ) {
            return true;
        }
        
        return false;
    }

    /**
     * Load custom templates
     *
     * @param string $template Template path
     * @return string Modified template path
     */
    public function load_template( $template ) {
        // Single doc template only
        // Archive is handled by the Elementor page (ID 7021)
        if ( is_singular( 'docs' ) ) {
            $custom_template = CT_DOCS_PATH . 'templates/single-docs.php';
            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
        }
        
        return $template;
    }

    /**
     * Add body classes
     *
     * @param array $classes Body classes
     * @return array Modified classes
     */
    public function add_body_classes( $classes ) {
        if ( is_singular( 'docs' ) ) {
            $classes[] = 'ct-docs-single';
        }
        
        if ( is_post_type_archive( 'docs' ) ) {
            $classes[] = 'ct-docs-archive';
        }
        
        return $classes;
    }

    /**
     * Calculate and save read time on post save
     *
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     */
    public function calculate_read_time( $post_id, $post ) {
        // Skip autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Skip revisions
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        // Calculate read time
        $word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
        $read_time = max( 1, ceil( $word_count / 200 ) );
        
        update_post_meta( $post_id, '_ct_docs_read_time', $read_time );
    }
}

