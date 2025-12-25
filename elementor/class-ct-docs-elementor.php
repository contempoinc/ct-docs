<?php
/**
 * Elementor Integration
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Elementor class
 */
class CT_Docs_Elementor {

    /**
     * Single instance
     *
     * @var CT_Docs_Elementor
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return CT_Docs_Elementor
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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register widget category
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_widget_category' ) );
        
        // Register widgets
        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
        
        // Enqueue editor styles
        add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
        
        // Enqueue frontend styles for widgets
        add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_widget_styles' ) );
    }

    /**
     * Register widget category
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager
     */
    public function register_widget_category( $elements_manager ) {
        $elements_manager->add_category(
            'ct-docs',
            array(
                'title' => 'Contempo Docs',
                'icon'  => 'eicon-folder',
            )
        );
    }

    /**
     * Register widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager
     */
    public function register_widgets( $widgets_manager ) {
        // Include widget files
        require_once CT_DOCS_PATH . 'elementor/widgets/class-toc-widget.php';
        require_once CT_DOCS_PATH . 'elementor/widgets/class-search-widget.php';
        
        // Register widgets
        $widgets_manager->register( new CT_Docs_TOC_Widget() );
        $widgets_manager->register( new CT_Docs_Search_Widget() );
    }

    /**
     * Enqueue editor styles
     */
    public function enqueue_editor_styles() {
        wp_enqueue_style(
            'ct-docs-elementor-editor',
            CT_DOCS_URL . 'assets/css/ct-docs-elementor.css',
            array(),
            CT_DOCS_VERSION
        );
    }

    /**
     * Enqueue widget styles on frontend
     */
    public function enqueue_widget_styles() {
        wp_enqueue_style(
            'ct-docs-elementor',
            CT_DOCS_URL . 'assets/css/ct-docs-elementor.css',
            array(),
            CT_DOCS_VERSION
        );
    }
}

