<?php
/**
 * Docs Header Elementor Widget
 *
 * @package CT_Docs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CT_Docs_Header_Widget class
 */
class CT_Docs_Header_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'ct-docs-header';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title() {
        return 'CT Docs Header';
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-header';
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
        return array( 'docs', 'documentation', 'header', 'search', 'navigation' );
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
                'placeholder' => 'Documentation',
            )
        );

        $this->add_control(
            'show_search',
            array(
                'label'        => 'Show Search',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Yes',
                'label_off'    => 'No',
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'search_placeholder',
            array(
                'label'       => 'Search Placeholder',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Search docs...',
                'placeholder' => 'Search docs...',
                'condition'   => array(
                    'show_search' => 'yes',
                ),
            )
        );

        $this->add_control(
            'sticky',
            array(
                'label'        => 'Sticky Header',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Yes',
                'label_off'    => 'No',
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->end_controls_section();

        // Style Section - Header
        $this->start_controls_section(
            'style_header_section',
            array(
                'label' => 'Header',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'header_background',
            array(
                'label'     => 'Background Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-page-header' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'header_border_color',
            array(
                'label'     => 'Border Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#e7e7e7',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-page-header' => 'border-bottom-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'header_padding',
            array(
                'label'      => 'Padding',
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'default'    => array(
                    'top'    => 16,
                    'right'  => 20,
                    'bottom' => 16,
                    'left'   => 20,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-page-header-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'default'   => '#000000',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-page-header-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'title_hover_color',
            array(
                'label'     => 'Hover Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#03b5c3',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-page-header-title:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .ct-docs-page-header-title',
            )
        );

        $this->end_controls_section();

        // Style Section - Search
        $this->start_controls_section(
            'style_search_section',
            array(
                'label'     => 'Search',
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_search' => 'yes',
                ),
            )
        );

        $this->add_control(
            'search_max_width',
            array(
                'label'      => 'Max Width',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%' ),
                'range'      => array(
                    'px' => array(
                        'min' => 200,
                        'max' => 800,
                    ),
                    '%'  => array(
                        'min' => 20,
                        'max' => 100,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 480,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ct-docs-page-header-search' => 'max-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'search_background',
            array(
                'label'     => 'Background Color',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#f8f9fd',
                'selectors' => array(
                    '{{WRAPPER}} .ct-docs-search-input' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'search_border_radius',
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

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $docs_page_url = get_permalink( CT_Docs_CPT::get_docs_page_id() );
        $sticky_class = $settings['sticky'] === 'yes' ? ' ct-docs-page-header--sticky' : '';
        ?>
        <header class="ct-docs-page-header<?php echo esc_attr( $sticky_class ); ?>">
            <div class="ct-docs-page-header-inner">
                <a href="<?php echo esc_url( $docs_page_url ); ?>" class="ct-docs-page-header-title">
                    <span><?php echo esc_html( $settings['title'] ); ?></span>
                </a>
                <?php if ( $settings['show_search'] === 'yes' ) : ?>
                <div class="ct-docs-page-header-search">
                    <?php echo do_shortcode( sprintf( '[ct_docs_search placeholder="%s"]', esc_attr( $settings['search_placeholder'] ) ) ); ?>
                </div>
                <?php endif; ?>
            </div>
        </header>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <#
        var stickyClass = settings.sticky === 'yes' ? ' ct-docs-page-header--sticky' : '';
        #>
        <header class="ct-docs-page-header{{ stickyClass }}">
            <div class="ct-docs-page-header-inner">
                <a href="#" class="ct-docs-page-header-title">
                    <span>{{{ settings.title }}}</span>
                </a>
                <# if ( settings.show_search === 'yes' ) { #>
                <div class="ct-docs-page-header-search">
                    <div class="ct-docs-search-widget">
                        <div class="ct-docs-search-input-wrapper">
                            <input type="text" class="ct-docs-search-input" placeholder="{{ settings.search_placeholder }}" />
                            <span class="ct-docs-search-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="M21 21l-4.35-4.35"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <# } #>
            </div>
        </header>
        <?php
    }
}

