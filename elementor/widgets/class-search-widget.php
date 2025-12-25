<?php
/**
 * Search Elementor Widget
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Search_Widget class
 */
class CT_Docs_Search_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'ct-docs-search';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title() {
        return 'CT Docs Live Search';
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-search';
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
        return array( 'docs', 'search', 'live search', 'typeahead', 'autocomplete' );
    }

    /**
     * Get script depends
     *
     * @return array Script dependencies
     */
    public function get_script_depends() {
        return array( 'ct-docs-search' );
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
            'placeholder',
            array(
                'label'       => 'Placeholder Text',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Search documentation...',
                'placeholder' => 'Enter placeholder text',
            )
        );

        $this->add_control(
            'limit',
            array(
                'label'   => 'Results Limit',
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min'     => 3,
                'max'     => 20,
            )
        );

        $this->add_control(
            'show_category',
            array(
                'label'        => 'Show Category Badge',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Yes',
                'label_off'    => 'No',
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // Style Section - Input
        $this->start_controls_section(
            'style_input_section',
            array(
                'label' => 'Search Input',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'input_bg_color',
            array(
                'label'     => 'Background Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-input' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_text_color',
            array(
                'label'     => 'Text Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#1a1a1a',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-input' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_placeholder_color',
            array(
                'label'     => 'Placeholder Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#999999',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-input::placeholder' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_border_color',
            array(
                'label'     => 'Border Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#e7e7e7',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-input' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_focus_border_color',
            array(
                'label'     => 'Focus Border Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#03b5c3',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-input:focus' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'input_padding',
            array(
                'label'      => 'Padding',
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em' ),
                'default'    => array(
                    'top'    => 12,
                    'right'  => 48,
                    'bottom' => 12,
                    'left'   => 16,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'input_border_radius',
            array(
                'label'      => 'Border Radius',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 8,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-search-input' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'input_typography',
                'selector' => '{{WRAPPER}} .ct-docs-search-input',
            )
        );

        $this->end_controls_section();

        // Style Section - Results Dropdown
        $this->start_controls_section(
            'style_results_section',
            array(
                'label' => 'Results Dropdown',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'results_bg_color',
            array(
                'label'     => 'Background Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-results' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'result_title_color',
            array(
                'label'     => 'Title Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#1a1a1a',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-result-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'result_excerpt_color',
            array(
                'label'     => 'Excerpt Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#666666',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-result-excerpt' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'result_hover_bg',
            array(
                'label'     => 'Hover Background',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#f7f7f7',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-result-item:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .ct-docs-search-result-item.is-selected' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'results_box_shadow',
                'selector' => '{{WRAPPER}} .ct-docs-search-results',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Output shortcode
        echo do_shortcode( sprintf(
            '[ct_docs_search placeholder="%s" limit="%s"]',
            esc_attr( $settings['placeholder'] ),
            esc_attr( $settings['limit'] )
        ) );
        
        // Add data attribute for category display
        if ( $settings['show_category'] !== 'yes' ) {
            echo '<style>.ct-docs-search-result-category { display: none; }</style>';
        }
    }

    /**
     * Render widget output in editor
     */
    protected function content_template() {
        ?>
        <#
        var placeholder = settings.placeholder || 'Search documentation...';
        #>
        <div class="ct-docs-search-widget ct-docs-search-preview">
            <div class="ct-docs-search-input-wrapper">
                <input 
                    type="text" 
                    class="ct-docs-search-input" 
                    placeholder="{{ placeholder }}"
                    readonly
                />
                <span class="ct-docs-search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                </span>
            </div>
            <div class="ct-docs-search-results ct-docs-search-preview-results" style="display: block; position: relative;">
                <div class="ct-docs-search-result-item">
                    <span class="ct-docs-search-result-title">Sample Search Result</span>
                    <span class="ct-docs-search-result-excerpt">This is a preview of how search results will appear...</span>
                    <# if ( settings.show_category === 'yes' ) { #>
                        <span class="ct-docs-search-result-category">Documentation</span>
                    <# } #>
                </div>
                <div class="ct-docs-search-result-item">
                    <span class="ct-docs-search-result-title">Another Result Title</span>
                    <span class="ct-docs-search-result-excerpt">Another preview result with some sample text...</span>
                    <# if ( settings.show_category === 'yes' ) { #>
                        <span class="ct-docs-search-result-category">Knowledge Base</span>
                    <# } #>
                </div>
            </div>
        </div>
        <?php
    }
}

