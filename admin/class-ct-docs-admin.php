<?php
/**
 * Admin Functionality
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Admin class
 */
class CT_Docs_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize admin hooks
     */
    private function init_hooks() {
        // Admin notices
        add_action( 'admin_notices', array( $this, 'activation_notice' ) );
        add_action( 'admin_notices', array( $this, 'betterdocs_notice' ) );
        
        // Custom columns
        add_filter( 'manage_docs_posts_columns', array( $this, 'add_columns' ) );
        add_action( 'manage_docs_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
        add_filter( 'manage_edit-docs_sortable_columns', array( $this, 'sortable_columns' ) );
        
        // Admin styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        
        // Clear cache action
        add_action( 'admin_post_ct_docs_clear_cache', array( $this, 'clear_cache' ) );
    }

    /**
     * Display activation notice
     */
    public function activation_notice() {
        if ( ! get_transient( 'ct_docs_activation_notice' ) ) {
            return;
        }

        delete_transient( 'ct_docs_activation_notice' );
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Contempo Docs</strong> has been activated successfully!</p>
            <p>
                Your documentation is ready to use. 
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=docs' ) ); ?>">View your docs</a> or 
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=docs' ) ); ?>">create a new doc</a>.
            </p>
        </div>
        <?php
    }

    /**
     * Display BetterDocs detection notice
     */
    public function betterdocs_notice() {
        // Only show once per session
        if ( get_transient( 'ct_docs_betterdocs_notice_dismissed' ) ) {
            return;
        }

        // Check if BetterDocs is active
        if ( ! is_plugin_active( 'betterdocs/betterdocs.php' ) && ! is_plugin_active( 'betterdocs-pro/betterdocs-pro.php' ) ) {
            return;
        }
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>BetterDocs Detected!</strong></p>
            <p>
                You have BetterDocs installed alongside Contempo Docs. Since both plugins use the same 
                <code>docs</code> post type and <code>doc_category</code> taxonomy, your existing documentation 
                will work automatically with Contempo Docs.
            </p>
            <p>
                <strong>To complete the transition:</strong>
            </p>
            <ol>
                <li>Test your documentation with Contempo Docs active</li>
                <li>Once satisfied, deactivate BetterDocs</li>
                <li>Update your /docs/ page to use CT Docs widgets</li>
            </ol>
        </div>
        <?php
        // Set transient to not show again for 1 day
        set_transient( 'ct_docs_betterdocs_notice_dismissed', true, DAY_IN_SECONDS );
    }

    /**
     * Add custom columns to docs list
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            
            // Add category column after title
            if ( $key === 'title' ) {
                $new_columns['doc_category'] = 'Category';
                $new_columns['read_time'] = 'Read Time';
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column content
     *
     * @param string $column  Column name
     * @param int    $post_id Post ID
     */
    public function render_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'doc_category':
                $terms = get_the_terms( $post_id, 'doc_category' );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    $term_links = array();
                    foreach ( $terms as $term ) {
                        $url = admin_url( 'edit.php?post_type=docs&doc_category=' . $term->slug );
                        $term_links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
                    }
                    echo implode( ', ', $term_links );
                } else {
                    echo '<span class="na">—</span>';
                }
                break;
                
            case 'read_time':
                $read_time = ct_docs_get_read_time( $post_id );
                echo esc_html( $read_time ) . ' min';
                break;
        }
    }

    /**
     * Make columns sortable
     *
     * @param array $columns Sortable columns
     * @return array Modified columns
     */
    public function sortable_columns( $columns ) {
        $columns['read_time'] = 'read_time';
        return $columns;
    }

    /**
     * Enqueue admin styles
     *
     * @param string $hook Current admin page
     */
    public function enqueue_admin_styles( $hook ) {
        // Only load on docs pages
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'docs' ) {
            return;
        }

        wp_enqueue_style(
            'ct-docs-admin',
            CT_DOCS_URL . 'admin/css/ct-docs-admin.css',
            array(),
            CT_DOCS_VERSION
        );
    }

    /**
     * Clear cache action handler
     */
    public function clear_cache() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'ct_docs_clear_cache' ) ) {
            wp_die( 'Security check failed.' );
        }

        // Check capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to do this.' );
        }

        // Clear cache
        CT_Docs_Cache::flush_all();

        // Redirect back with success message
        wp_safe_redirect( add_query_arg( 'cache_cleared', '1', wp_get_referer() ) );
        exit;
    }
}

// Initialize admin
new CT_Docs_Admin();

