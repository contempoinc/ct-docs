<?php
/**
 * TOC Elementor Widget
 *
 * Supports two display styles:
 * - Classic List: Traditional book-style table of contents
 * - Modern: Stripe-inspired sectioned grid with excerpts
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_TOC_Widget class
 */
class CT_Docs_TOC_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'ct-docs-toc';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title() {
        return 'CT Docs TOC';
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-bullet-list';
    }

    /**
     * Get widget categories
     *
     * @return array Widget categories
     */
    public function get_categories() {
        return array( 'ct-docs' );
    }

    /**
     * Get widget keywords
     *
     * @return array Widget keywords
     */
    public function get_keywords() {
        return array( 'docs', 'documentation', 'toc', 'table of contents', 'list', 'grid' );
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => 'Content',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'display_style',
            array(
                'label'   => 'Display Style',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'classic',
                'options' => array(
                    'classic' => 'Classic List',
                    'modern'  => 'Modern (Stripe Style)',
                ),
                'description' => 'Classic shows a simple list, Modern shows a sectioned grid with excerpts',
            )
        );

        $this->add_control(
            'title',
            array(
                'label'       => 'Title',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Documentation',
                'placeholder' => 'Enter title',
            )
        );

        $this->add_control(
            'subtitle',
            array(
                'label'       => 'Subtitle',
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'default'     => '',
                'placeholder' => 'Enter subtitle',
                'rows'        => 2,
                'condition'   => array(
                    'display_style' => 'modern',
                ),
            )
        );

        $this->add_control(
            'category',
            array(
                'label'   => 'Category',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'all',
                'options' => $this->get_category_options(),
            )
        );

        $this->add_control(
            'columns',
            array(
                'label'   => 'Columns',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => array(
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                ),
                'condition' => array(
                    'display_style' => 'classic',
                ),
            )
        );

        $this->add_control(
            'grid_columns',
            array(
                'label'   => 'Grid Columns',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                ),
                'condition' => array(
                    'display_style' => 'modern',
                ),
            )
        );

        $this->add_control(
            'show_count',
            array(
                'label'        => 'Show Article Count',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Yes',
                'label_off'    => 'No',
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_excerpts',
            array(
                'label'        => 'Show Excerpts',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Yes',
                'label_off'    => 'No',
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array(
                    'display_style' => 'modern',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - Title
        $this->start_controls_section(
            'style_title_section',
            array(
                'label' => 'Title',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => 'Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#1a1a1a',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-toc-title, {{WRAPPER}} .ct-docs-index-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .ct-docs-toc-title, {{WRAPPER}} .ct-docs-index-title',
            )
        );

        $this->add_responsive_control(
            'title_margin',
            array(
                'label'      => 'Margin',
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-toc-title, {{WRAPPER}} .ct-docs-index-header' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - List Items (Classic style)
        $this->start_controls_section(
            'style_list_section',
            array(
                'label'     => 'List Items',
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'display_style' => 'classic',
                ),
            )
        );

        $this->add_control(
            'link_color',
            array(
                'label'     => 'Text Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#1a1a1a',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-toc-list a' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'link_hover_color',
            array(
                'label'     => 'Hover Text Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#03b5c3',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-toc-list a:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'hover_bg_color',
            array(
                'label'     => 'Hover Background',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => 'rgba(3, 181, 195, 0.08)',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-toc-list a:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'border_color',
            array(
                'label'     => 'Border Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#e7e7e7',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-toc-item' => 'border-bottom-color: {{VALUE}};',
                    '{{WRAPPER}} .ct-docs-toc-title' => 'border-bottom-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'link_typography',
                'selector' => '{{WRAPPER}} .ct-docs-toc-list a',
            )
        );

        $this->add_responsive_control(
            'item_padding',
            array(
                'label'      => 'Item Padding',
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em' ),
                'default'    => array(
                    'top'    => 16,
                    'right'  => 20,
                    'bottom' => 16,
                    'left'   => 20,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-toc-list a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'column_gap',
            array(
                'label'      => 'Column Gap',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 20,
                        'max' => 80,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 40,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-toc-list.ct-docs-columns-2, {{WRAPPER}} .ct-docs-toc-list.ct-docs-columns-3' => 'gap: 0 {{SIZE}}{{UNIT}};',
                ),
                'condition' => array(
                    'columns!' => '1',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - Modern Grid
        $this->start_controls_section(
            'style_modern_section',
            array(
                'label'     => 'Grid Items',
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'display_style' => 'modern',
                ),
            )
        );

        $this->add_control(
            'item_title_color',
            array(
                'label'     => 'Item Title Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#03b5c3',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-grid-item-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'item_title_hover_color',
            array(
                'label'     => 'Item Title Hover Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#028a94',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-grid-item:hover .ct-docs-grid-item-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'excerpt_color',
            array(
                'label'     => 'Excerpt Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#666666',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-grid-item-excerpt' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'section_header_color',
            array(
                'label'     => 'Section Header Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#666666',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-section-header' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'divider_color',
            array(
                'label'     => 'Divider Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#03b5c3',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-section-divider' => 'background: linear-gradient(to right, {{VALUE}}, transparent);',
                ),
            )
        );

        $this->add_responsive_control(
            'grid_gap',
            array(
                'label'      => 'Grid Gap',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 16,
                        'max' => 80,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 32,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-section-grid' => 'gap: {{SIZE}}{{UNIT}} 48px;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get category options for dropdown
     *
     * @return array Category options
     */
    private function get_category_options() {
        $options = array(
            'all' => 'All Categories',
        );

        $terms = get_terms( array(
            'taxonomy'   => 'doc_category',
            'hide_empty' => false,
        ) );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->slug ] = $term->name;
            }
        }

        return $options;
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $display_style = $settings['display_style'] ?? 'classic';

        if ( 'modern' === $display_style ) {
            $this->render_modern_style( $settings );
        } else {
            $this->render_classic_style( $settings );
        }
    }

    /**
     * Render classic list style (existing behavior)
     *
     * @param array $settings Widget settings
     */
    private function render_classic_style( $settings ) {
        // Build shortcode attributes
        $atts = array(
            'title'    => $settings['title'],
            'category' => $settings['category'],
            'columns'  => $settings['columns'],
            'count'    => $settings['show_count'] === 'yes' ? 'yes' : 'no',
            'style'    => 'classic',
        );

        // Output shortcode
        echo do_shortcode( sprintf(
            '[ct_docs_toc title="%s" category="%s" columns="%s" count="%s" style="%s"]',
            esc_attr( $atts['title'] ),
            esc_attr( $atts['category'] ),
            esc_attr( $atts['columns'] ),
            esc_attr( $atts['count'] ),
            esc_attr( $atts['style'] )
        ) );
    }

    /**
     * Render modern Stripe-style grid
     *
     * @param array $settings Widget settings
     */
    private function render_modern_style( $settings ) {
        $category_filter = $settings['category'] ?? 'all';
        $grid_columns = $settings['grid_columns'] ?? '3';
        $show_count = $settings['show_count'] === 'yes';
        $show_excerpts = $settings['show_excerpts'] === 'yes';
        
        // Get docs by category
        $docs_by_category = CT_Docs_Taxonomy::get_docs_by_category();
        
        if ( empty( $docs_by_category ) ) {
            echo '<p>No documentation found.</p>';
            return;
        }

        // Filter to specific category if not 'all'
        if ( 'all' !== $category_filter && isset( $docs_by_category[ $category_filter ] ) ) {
            $docs_by_category = array( $category_filter => $docs_by_category[ $category_filter ] );
        } elseif ( 'all' !== $category_filter ) {
            echo '<p>No documentation found in this category.</p>';
            return;
        }
        
        // Determine if showing single category (use title option) or all categories (use term names)
        $is_single_category = ( 'all' !== $category_filter );
        ?>
        
        <div class="ct-docs-modern-index">
            
            <?php if ( ! $is_single_category && ! empty( $settings['title'] ) ) : ?>
                <div class="ct-docs-index-header">
                    <h2 class="ct-docs-index-title"><?php echo esc_html( $settings['title'] ); ?></h2>
                    <?php if ( ! empty( $settings['subtitle'] ) ) : ?>
                        <p class="ct-docs-index-subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php foreach ( $docs_by_category as $slug => $data ) :
                $term = $data['term'];
                $docs = $data['docs'];
                $total_count = count( $docs );
                
                // Use title option for single category, otherwise use term name
                $section_title = $is_single_category && ! empty( $settings['title'] ) 
                    ? $settings['title'] 
                    : $term->name;
                ?>
                
                <section class="ct-docs-section">
                    <div class="ct-docs-section-header-wrapper">
                        <h3 class="ct-docs-section-header">
                            <?php echo esc_html( $section_title ); ?>
                            <?php if ( $show_count ) : ?>
                                <span class="ct-docs-section-count">(<?php echo esc_html( $total_count ); ?>)</span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="ct-docs-section-divider"></div>
                    
                    <div class="ct-docs-section-grid ct-docs-grid-cols-<?php echo esc_attr( $grid_columns ); ?>">
                        <?php foreach ( $docs as $doc ) : 
                            // Generate excerpt from content (first ~10 words)
                            $excerpt = '';
                            if ( $show_excerpts ) {
                                $excerpt = ! empty( $doc->post_excerpt ) 
                                    ? $doc->post_excerpt 
                                    : wp_trim_words( wp_strip_all_tags( $doc->post_content ), 10, '...' );
                            }
                            ?>
                            <a href="<?php echo esc_url( get_permalink( $doc->ID ) ); ?>" class="ct-docs-grid-item">
                                <span class="ct-docs-grid-item-title"><?php echo esc_html( get_the_title( $doc->ID ) ); ?></span>
                                <?php if ( $show_excerpts && ! empty( $excerpt ) ) : ?>
                                    <span class="ct-docs-grid-item-excerpt"><?php echo esc_html( $excerpt ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                
            <?php endforeach; ?>
            
        </div>
        
        <?php
    }

    /**
     * Render widget output in editor
     */
    protected function content_template() {
        ?>
        <#
        var title = settings.title || 'Documentation';
        var displayStyle = settings.display_style || 'classic';
        var columns = settings.columns || '1';
        var gridColumns = settings.grid_columns || '3';
        var showExcerpts = settings.show_excerpts === 'yes';
        #>
        
        <# if ( displayStyle === 'modern' ) { #>
            <div class="ct-docs-modern-index ct-docs-preview">
                <# if ( title ) { #>
                    <div class="ct-docs-index-header">
                        <h2 class="ct-docs-index-title">{{{ title }}}</h2>
                        <# if ( settings.subtitle ) { #>
                            <p class="ct-docs-index-subtitle">{{{ settings.subtitle }}}</p>
                        <# } #>
                    </div>
                <# } #>
                
                <!-- Documentation Section -->
                <section class="ct-docs-section">
                    <div class="ct-docs-section-header-wrapper">
                        <h3 class="ct-docs-section-header">
                            Documentation
                            <# if ( settings.show_count === 'yes' ) { #>
                                <span class="ct-docs-section-count">(45)</span>
                            <# } #>
                        </h3>
                    </div>
                    <div class="ct-docs-section-divider"></div>
                    
                    <div class="ct-docs-section-grid ct-docs-grid-cols-{{ gridColumns }}">
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Installation & Setup Wizard</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Get started with the quick setup wizard...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Homepage Setup</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Configure your homepage layout and content...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Listings Advanced Search</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Set up powerful search for your listings...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Adding & Managing Listings</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Create and manage property listings easily...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Using Mega Menus</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Build advanced navigation with mega menus...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Google Maps Integration</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Connect and configure Google Maps API...</span>
                            <# } #>
                        </a>
                    </div>
                </section>
                
                <!-- Knowledge Base Section -->
                <section class="ct-docs-section">
                    <div class="ct-docs-section-header-wrapper">
                        <h3 class="ct-docs-section-header">
                            Knowledge Base
                            <# if ( settings.show_count === 'yes' ) { #>
                                <span class="ct-docs-section-count">(45)</span>
                            <# } #>
                        </h3>
                    </div>
                    <div class="ct-docs-section-divider"></div>
                    
                    <div class="ct-docs-section-grid ct-docs-grid-cols-{{ gridColumns }}">
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">How Do I Get Support?</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Contact our support team for help...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Google Maps Aren't Working</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Troubleshoot common Google Maps issues...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">How Do I Update the Theme?</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Keep your theme up to date safely...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">I'm Getting a 404 Error</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Fix page not found errors quickly...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Contact Forms Not Working</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Resolve contact form submission issues...</span>
                            <# } #>
                        </a>
                        <a href="#" class="ct-docs-grid-item">
                            <span class="ct-docs-grid-item-title">Missing Emails From Site</span>
                            <# if ( showExcerpts ) { #>
                                <span class="ct-docs-grid-item-excerpt">Troubleshoot email delivery problems...</span>
                            <# } #>
                        </a>
                    </div>
                </section>
            </div>
        <# } else { #>
            <div class="ct-docs-toc-widget ct-docs-toc-preview">
                <# if ( title ) { #>
                    <h3 class="ct-docs-toc-title">{{{ title }}}</h3>
                <# } #>
                <ul class="ct-docs-toc-list ct-docs-columns-{{ columns }}">
                    <li class="ct-docs-toc-item"><a href="#">Installation & Setup Wizard</a></li>
                    <li class="ct-docs-toc-item"><a href="#">Instructional Videos</a></li>
                    <li class="ct-docs-toc-item"><a href="#">Homepage Setup</a></li>
                    <li class="ct-docs-toc-item"><a href="#">Listings Advanced Search</a></li>
                    <li class="ct-docs-toc-item"><a href="#">Adding & Managing Listings</a></li>
                </ul>
                <# if ( settings.show_count === 'yes' ) { #>
                    <p class="ct-docs-toc-count">5 articles</p>
                <# } #>
            </div>
        <# } #>
        <?php
    }
}
