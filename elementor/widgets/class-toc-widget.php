<?php
/**
 * TOC Elementor Widget
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
        return array( 'docs', 'documentation', 'toc', 'table of contents', 'list' );
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
            'title',
            array(
                'label'       => 'Title',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Documentation',
                'placeholder' => 'Enter title',
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
                'default'      => 'no',
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
                    '{{WRAPPER}} .ct-docs-toc-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .ct-docs-toc-title',
            )
        );

        $this->add_responsive_control(
            'title_margin',
            array(
                'label'      => 'Margin',
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-toc-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - List Items
        $this->start_controls_section(
            'style_list_section',
            array(
                'label' => 'List Items',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
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

        // Build shortcode attributes
        $atts = array(
            'title'    => $settings['title'],
            'category' => $settings['category'],
            'columns'  => $settings['columns'],
            'count'    => $settings['show_count'] === 'yes' ? 'yes' : 'no',
        );

        // Output shortcode
        echo do_shortcode( sprintf(
            '[ct_docs_toc title="%s" category="%s" columns="%s" count="%s"]',
            esc_attr( $atts['title'] ),
            esc_attr( $atts['category'] ),
            esc_attr( $atts['columns'] ),
            esc_attr( $atts['count'] )
        ) );
    }

    /**
     * Render widget output in editor
     */
    protected function content_template() {
        ?>
        <#
        var title = settings.title || 'Documentation';
        var columns = settings.columns || '1';
        #>
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
        <?php
    }
}

