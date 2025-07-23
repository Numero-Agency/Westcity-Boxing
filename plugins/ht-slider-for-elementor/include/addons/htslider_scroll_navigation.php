<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HTSlider_Elementor_Widget_Scroll_Navigation extends Widget_Base {

    public function get_name() {
        return 'htslider-scrollnavigation-addons';
    }
    
    public function get_title() {
        return esc_html__( 'HT Scroll Navigation Slider', 'ht-slider' );
    }

    public function get_icon() {
        return 'eicon-slider-full-screen';
    }

    public function get_categories() {
        return [ 'ht-slider' ];
    }

    public function get_keywords() {
        return ['scroll slider','fullscreen slider','horizontal slider','vertical slider','ht-slider','one page navigation', 'scroll navigation'];
    }

    public function get_help_url() {
        return 'https://hasthemes.com/plugins/ht-slider-pro-for-elementor/';
    }

    public function get_style_depends(){
        return [
            'swiper','htslider-widgets'
        ];
    }

    public function get_script_depends() {
        return [
            'swiper','htslider-widget-active'
        ];
    }
    protected function register_controls() {
        $this->start_controls_section(
            'htlider_content',
            [
                'label' => esc_html__( 'Slides Source', 'ht-slider' ),
            ]
        );

        $this->add_control(
            'slides_source',
            [
                'label'   => esc_html__( 'Source Type', 'ht-slider' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'custom'   => esc_html__( 'Custom Content', 'ht-slider' ),
                    'ht_slider_slides'   => esc_html__( 'HT Slider', 'ht-slider' ),
                ],
            ]
        );
        $this->add_control(
            'ht_slider_notice',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw'  => sprintf(
                    esc_html__( 'If you choose HT Slider, make sure you have created slides using the HT Slider post type. You can create slides by clicking %shere%s.', 'ht-slider' ),
                    '<a href="' . esc_url(admin_url('edit.php?post_type=htslider_slider')) . '" target="_blank">',
                    '</a>'
                ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                'condition' => [
                    'slides_source' => 'ht_slider_slides',
                ],
            ]
        );
        

        $this->end_controls_section();
        //custom post type
        $this->start_controls_section(
            'ht_slider_slides_content',
            [
                'label'     => esc_html__( 'Slides Query', 'ht-slider' ),
                'condition' => [
                    'slides_source' => 'ht_slider_slides',
                ]
            ]
        );

        $this->add_control(
                'slider_show_by',
                [
                    'label'     => esc_html__( 'Slides Show By', 'ht-slider' ),
                    'type'      => Controls_Manager::SELECT,
                    'default'   => 'show_bycat',
                    'options'   => [
                        'show_byid'   => esc_html__( 'Show By ID', 'ht-slider' ),
                        'show_bycat'  => esc_html__( 'Show By Category', 'ht-slider' ),
                    ],
                ]
            );

            $this->add_control(
                'slider_id',
                [
                    'label'         => esc_html__( 'Select Slides', 'ht-slider' ),
                    'type'          => Controls_Manager::SELECT2,
                    'label_block'   => true,
                    'multiple'      => true,
                    'options'       => htslider_post_name( 'htslider_slider' ),
                    'condition'     => [
                        'slider_show_by' => 'show_byid',
                    ]
                ]
            );

            $this->add_control(
                'slider_cat',
                [
                    'label'         => esc_html__( 'Select Category', 'ht-slider' ),
                    'type'          => Controls_Manager::SELECT2,
                    'label_block'   => true,
                    'multiple'      => true,
                    'options'       => htslider_get_taxonomies( 'htslider_category' ),
                    'condition'     => [
                        'slider_show_by' => 'show_bycat',
                    ]
                ]
            );
            
            $this->add_control(
                'slider_limit',
                [
                    'label'     => esc_html__( 'Slides Limit', 'ht-slider' ),
                    'type'      => Controls_Manager::NUMBER,
                    'step'      => 1,
                    'default'   => 3,
                ]
            );

        $this->end_controls_section();
        $this->start_controls_section(
            'slider_items_section',
            [
                'label' => esc_html__( 'Slider Items', 'ht-slider' ),
                'condition' => [
                    'slides_source' => 'custom'
                ],
            ]
        );
            
            $repeater = new Repeater();

            $repeater->add_control(
                'content_source',
                [
                    'label'   => esc_html__( 'Content Source', 'ht-slider' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'custom',
                    'options' => [
                        'custom'    => esc_html__( 'Custom', 'ht-slider' ),
                        "elementor" => esc_html__( 'Elementor Template (Pro)', 'ht-slider' ),
                    ],
                ]
            );
            htslider_pro_notice( $repeater,'content_source', 'elementor', Controls_Manager::RAW_HTML );
            $repeater->add_control(
                'slider_raw_content',
                [
                    'label'      => esc_html__( 'Content', 'ht-slider' ),
                    'type'       => Controls_Manager::WYSIWYG,
                    'default'    => '',
                    'condition' => [
                        'content_source' =>'custom',
                    ],
                ]
            );
            $repeater->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name'      => 'slide_item_background',
                    'label'     => esc_html__( 'Background', 'ht-slider' ),
                    'types'     => [ 'classic','gradient' ],
                    'selector'  => '{{WRAPPER}} {{CURRENT_ITEM}} .htslider-scroll-navigation-inner',
                    'separator' => 'before',
                    'default' => [
                        'color' => '#000',
                    ],
                    'condition' => [
                        'content_source' =>'custom',
                    ],
                ]
            ); 
            $this->add_control(
                'navigator_content_list',
                [
                    'type'    => Controls_Manager::REPEATER,
                    'fields'  => $repeater->get_controls() ,
                    'prevent_empty'=>false,
                    'max_items' => 4, 
                    'default' => [

                        [
                            'slider_raw_content'    => '<h4 style="text-align: center;">Welcome to HT Slider</h4><h2 style="text-align: center;">We\'re excited to have you here!</h2><p style="text-align: center;">Welcome to HasThemes, your go-to destination for innovative solutions. Whether you are here to explore new products, get inspired, or learn about our services, we are thrilled to be part of your journey.</p><p style="text-align: center;"><a href="#">Explore Now</a></p>',
                            'content_source' => 'custom'
                        ],
                        [
                            'slider_raw_content'    => '<h4 style="text-align: center;"><span style="color: #ccffff;">What We Offer</span></h4><h2 style="text-align: center;"><span style="color: #ccffff;">Tailored services just for you</span></h2><p style="text-align: center;"><span style="color: #ccffff;">At HasThemes, we offer a range of customized services designed to meet your needs. From business solutions to top-notch customer support,we are dedicated to delivering the best experience possible.</span></p><p style="text-align: center;"><span style="color: #ccffff;"><a style="color: #ccffff;" href="#">Learn More</a></span></p>',
                            'content_source' => 'custom'
                        ],
                        [
                            'slider_raw_content'    => '<h4 style="text-align: center;">Become Part of Our Community</h4><h2 style="text-align: center;">The best is yet to come</h2><p style="text-align: center;">Ready to take the next step? Join our community today and enjoy exclusive benefits, stay updated on new products, and get access to special offers and discounts. We can not wait to have you with us!</p><p style="text-align: center;"><a href="#">Sign Up Now</a></p>',
                            'content_source' => 'custom',
                        ],

                    ],
                ]
            );
            $this->add_control(
                'pro_repeater_notice',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => sprintf(
                        __('Free version is limited to 4 slides. Get unlimited slides in the Pro version. %1$s', 'ht-slider'),
                        '<a href="https://hasthemes.com/plugins/ht-slider-pro-for-elementor/" target="_blank">Upgrade to Pro</a>'
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                ]
            );
            
        $this->end_controls_section(); // Content Section End

        // Slider Options Section Start
        $this->start_controls_section(
            'scroll_navigation_slider_options',
            [
                'label' => esc_html__( 'Slider Options', 'ht-slider' ),
            ]
        );
            $this->add_control(
                'slider_direction',
                [
                    'label' => esc_html__( 'Slider Direction', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'vertical',
                    'options' => [
                        'vertical'  => esc_html__( 'Vertical', 'ht-slider' ),
                        'horizontal' => esc_html__( 'Horizontal (Pro)', 'ht-slider' ),
                    ],
                ]
            );
            htslider_pro_notice( $this,'slider_direction', 'horizontal', Controls_Manager::RAW_HTML );
            $this->add_control(
                'slider_height',
                [
                    'label' => esc_html__( 'Height', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'full_screen',
                    'options' => [
                        'full_screen'    => esc_html__( 'Full Screen', 'ht-slider' ),
                        'custom_height'  => esc_html__( 'Custom (Pro)', 'ht-slider' ),
                    ],
                ]
            );
            htslider_pro_notice( $this,'slider_height', 'custom_height', Controls_Manager::RAW_HTML );

            $this->add_control(
                'slider_speed',
                [
                    'label' => esc_html__( 'Speed', 'ht-slider' ),
                    'type' => Controls_Manager::NUMBER,
                    'default' => 300,
                ]
            );

            $this->add_control(
                'slider_item',
                [
                    'label' => esc_html__( 'Slider Visible Item', 'ht-slider' ),
                    'type' => Controls_Manager::NUMBER,
                    'default' => 1,
                ]
            );

            $this->add_control(
                'initial_slider',
                [
                    'label' => esc_html__( 'Active Slide', 'ht-slider' ) . ' <i class="eicon-pro-icon"></i>',
                    'type' => Controls_Manager::NUMBER,
                    'default' => 1,
                    'classes' => 'htslider-disable-control',
                ]
            );

            $this->add_control(
                'slider_mousewheel',
                [
                    'label' => esc_html__( 'Mouse Wheel', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );
            $this->add_control(
                'slider_keyboard_scroll',
                [
                    'label' => esc_html__( 'Keyboard Scroll', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'no',
                ]
            );
            $this->add_control(
                'slider_simulate_touch',
                [
                    'label' => esc_html__( 'Simulate Touch', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );
            $this->add_control(
                'slider_arrow',
                [
                    'label' => esc_html__( 'Slider Navigation', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'no',
                ]
            );

            $this->add_control(
                'slider_dots',
                [
                    'label' => esc_html__( 'Slider Pagination', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );
            $this->add_control(
                'slide_custom_menu',
                [
                    'label' => esc_html__( 'External Menu for Navigation', 'ht-slider' ) . ' <i class="eicon-pro-icon"></i>',
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'no',
                    'description' => esc_html( 'Enable this feature to allow the slider to scroll when clicking on the header menu.', 'ht-slider' ),
                    'classes' => 'htslider-disable-control',
                ]
            );
            $this->add_control(
				'slide_info_notice',
				[
					'raw'             => __( 'To integrate the external menu with the navigation slider, utilize the link structure <b>#htslider-scroll-slide-1</b> for navigating to a specific slide. ', 'ht-slider' ),
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					'condition'   => [
						'slide_custom_menu' => 'yes'
					],
				]
			);
        $this->end_controls_section(); // Slider Options Section End

        // Style tab section
        $this->start_controls_section(
            'scroll_navigation_style_section',
            [
                'label' => esc_html__( 'Content Style', 'ht-slider' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'slides_source' => 'custom'
                ]
            ]
        );
            $this->add_control(
                'scroll_navigation_content_color',
                [
                    'label' => esc_html__( 'Color', 'ht-slider' ),
                    'type' => Controls_Manager::COLOR,
                    'default' => '#000000',
                    'selectors' => [
                        '{{WRAPPER}} .htslider-scroll-navigation-content' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'scroll_navigation_content_typography',
                    'selector' => '{{WRAPPER}} .htslider-scroll-navigation-content',
                ]
            );

            $this->add_responsive_control(
                'scroll_navigation_content_padding',
                [
                    'label' => esc_html__( 'Padding', 'ht-slider' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .htslider-scroll-navigation-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );
            $this->add_control(
                'content_alignment_x',
                [
                    'label' => esc_html__( 'Content Box Horizontal Position', 'ht-slider' ),
                    'type'  => Controls_Manager::CHOOSE,
                    'options'   => [
                        'left'  => [
                            'title' => esc_html__( 'Left', 'ht-slider' ),
                            'icon'  => 'eicon-h-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__( 'Center', 'ht-slider' ),
                            'icon'  => 'eicon-h-align-center',
                        ],
                        'right' => [
                            'title' => esc_html__( 'Right', 'ht-slider' ),
                            'icon'  => 'eicon-h-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .htslider-scroll-navigation-content' => 'justify-content: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'content_alignment_y',
                [
                    'label' => esc_html__( 'Content Box Vertical Position', 'ht-slider' ), 
                    'type'  => Controls_Manager::CHOOSE,
                    'options'   => [
                        'start'  => [
                            'title' => esc_html__( 'Top', 'ht-slider' ),
                            'icon'  => 'eicon-v-align-top',
                        ],
                        'center' => [
                            'title' => esc_html__( 'Center', 'ht-slider' ),
                            'icon'  => 'eicon-v-align-middle',
                        ],
                        'end' => [
                            'title' => esc_html__( 'Bottom', 'ht-slider' ),
                            'icon'  => 'eicon-v-align-bottom',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .htslider-scroll-navigation-content' => 'align-items: {{VALUE}};',
                    ],
                ]
            );
            $this->add_control(
                'content_box-max_width',
                [
                    'label' => esc_html__( 'Content Box Max Width', 'ht-slider' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px','%' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 1200,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 300,
                            'step' => 1,
                        ]
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 600,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .htslider-scroll-navigation-content>div' => 'max-width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'slider_contnent_alignment',
                [
                    'label' => __( 'Alignment', 'ht-slider' ),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => __( 'Left', 'ht-slider' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'ht-slider' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'right' => [
                            'title' => __( 'Right', 'ht-slider' ),
                            'icon' => 'eicon-text-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .htslider-scroll-navigation-content>div' => 'text-align: {{VALUE}};',
                    ],
                ]
            );
        $this->end_controls_section();


        // Style Testimonial Dots style start
        $this->start_controls_section(
            'scroll_navigation_pagination_style',
            [
                'label'     => esc_html__( 'Pagination', 'ht-slider' ),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );
            
            $this->start_controls_tabs( 'scroll_navigation_pagination_style_tabs' );

                // Normal tab Start
                $this->start_controls_tab(
                    'scroll_navigation_pagination_style_normal_tab',
                    [
                        'label' => esc_html__( 'Normal', 'ht-slider' ),
                    ]
                );

                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'scroll_navigation_pagination_background',
                            'label' => esc_html__( 'Background', 'ht-slider' ),
                            'types' => [ 'classic', 'gradient' ],
                            'selector' => '{{WRAPPER}} .swiper-pagination-bullet',
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'scroll_navigation_pagination_border',
                            'label' => esc_html__( 'Border', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .swiper-pagination-bullet',
                        ]
                    );

                    $this->add_responsive_control(
                        'scroll_navigation_pagination_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .swiper-pagination-bullet' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );

                    $this->add_control(
                        'scroll_navigation_pagination_height',
                        [
                            'label' => esc_html__( 'Height', 'ht-slider' ),
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px' ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 300,
                                    'step' => 1,
                                ]
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => 20,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'scroll_navigation_pagination_width',
                        [
                            'label' => esc_html__( 'Width', 'ht-slider' ),
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px' ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 300,
                                    'step' => 1,
                                ]
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => 20,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
                            ],
                        ]
                    );
                    $this->add_control(
                        'scroll_navigation_pagination_opacity',
                        [
                            'label' => esc_html__( 'Opacity', 'ht-slider' ),
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px' ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 1,
                                    'step' => 0.1,
                                ],
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => 0.2,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .swiper-pagination-bullet' => 'opacity: {{SIZE}};',
                            ],
                            'separator' => 'before',
                        ]
                    );
                $this->end_controls_tab(); // Normal tab end

                // Hover tab Start
                $this->start_controls_tab(
                    'scroll_navigation_pagination_style_hover_tab',
                    [
                        'label' => esc_html__( 'Active', 'ht-slider' ),
                    ]
                );

                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'scroll_navigation_pagination_hover_background',
                            'label' => esc_html__( 'Background', 'ht-slider' ),
                            'types' => [ 'classic', 'gradient' ],
                            'selector' => '{{WRAPPER}} .swiper-pagination-bullet-active',
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'scroll_navigation_pagination_hover_border',
                            'label' => esc_html__( 'Border', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .swiper-pagination-bullet-active',
                        ]
                    );

                    $this->add_responsive_control(
                        'scroll_navigation_pagination_hover_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .swiper-pagination-bullet-active' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );
                    $this->add_control(
                        'scroll_navigation_pagination_opacity_active',
                        [
                            'label' => esc_html__( 'Opacity', 'ht-slider' ),
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px' ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 1,
                                    'step' => 0.1,
                                ],
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => 1,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .swiper-pagination-bullet-active' => 'opacity: {{SIZE}};',
                            ],
                            'separator' => 'before',
                        ]
                    );
                $this->end_controls_tab(); // Hover tab end

            $this->end_controls_tabs();

        $this->end_controls_section(); // Style slider dots style end

        $this->start_controls_section(  // Slider arrow style 
            'htslider_arrow_style',
            [
                'label'     => esc_html__( 'Navigation', 'ht-slider' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' =>[
                    'slider_arrow'  => 'yes',
                ],
            ]
        );
        $this->add_control(
            'arrow_position',
            [
                'label' => __('Alignment', 'ht-slider'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'ht-slider'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'ht-slider'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'ht-slider'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => false,
            ]
        );
        $this->add_responsive_control(
            'htslider_arrow_height',
            [
                'label' => esc_html__( 'Height', 'ht-slider' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'htslider_arrow_width',
            [
                'label' => esc_html__( 'Width', 'ht-slider' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'htslider_arrow_fontsize',
            [
                'label' => esc_html__( 'Icon Size', 'ht-slider' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next,{{WRAPPER}} .swiper-button-next:after,{{WRAPPER}} .swiper-button-prev:after' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .swiper-button-prev svg, {{WRAPPER}} .swiper-button-next svg' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'htslider_arrow_position',
            [
                'label' => esc_html__( 'Vertical Position', 'ht-slider' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 110,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => '50',
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'separator' =>'before',
            ]
        );

        $this->add_responsive_control(
            'htslider_arrow_position_x',
            [
                'label' => esc_html__( 'Horizontal Position', 'ht-slider' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => -1000,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => '0',
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'arrow_nav_hide_on',
            [
                'label' => esc_html__( 'Hide Arrow', 'ht-slider' ),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'none',
                'description' => esc_html__('Toggle this switch to hide the arrow button on responsive devices.','ht-slider'),
                'separator' => 'before',
                'default' => 'flex',
                'selectors' => [
                    '{{WRAPPER}} .htemga-pro-slider-nav .slick-arrow, {{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'display: {{VALUE}}!important',
                ],
                'tablet_default' => 'flex',
                'mobile_default' => 'none',
            ]
        );
        $this->add_control(
            'arrow_border_color_heading',
            [
                'label' => __( 'Colors and Border', 'ht-slider' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
            $this->start_controls_tabs( 'htslider_arrow_style_tabs' );

                // Normal tab Start
                $this->start_controls_tab(
                    'htslider_arrow_style_normal_tab',
                    [
                        'label' => esc_html__( 'Normal', 'ht-slider' ),
                    ]
                );
                    $this->add_control(
                        'htslider_arrow_color',
                        [
                            'label' => esc_html__( 'Color', 'ht-slider' ),
                            'type' => Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next,
                                {{WRAPPER}} .htemga-pro-slider-nav .slick-arrow' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .swiper-button-prev svg path,
                                {{WRAPPER}} .swiper-button-next svg path' => 'fill: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'htslider_arrow_background',
                            'label' => esc_html__( 'Background', 'ht-slider' ),
                            'types' => [ 'classic', 'gradient' ],
                            'selector' => '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next',
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'htslider_arrow_border',
                            'label' => esc_html__( 'Border', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next',
                        ]
                    );

                    $this->add_responsive_control(
                        'htslider_arrow_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );

                $this->end_controls_tab(); // Normal tab end

                // Hover tab Start
                $this->start_controls_tab(
                    'htslider_arrow_style_hover_tab',
                    [
                        'label' => esc_html__( 'Hover', 'ht-slider' ),
                    ]
                );

                    $this->add_control(
                        'htslider_arrow_hover_color',
                        [
                            'label' => esc_html__( 'Color', 'ht-slider' ),
                            'type' => Controls_Manager::COLOR,
                            'default' => '#ffffff',
                            'selectors' => [
                                '{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover,
                                {{WRAPPER}} .htemga-pro-slider-nav .slick-arrow:hover' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .swiper-button-prev:hover svg path,
                                {{WRAPPER}} .swiper-button-next:hover svg path' => 'fill: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'htslider_arrow_hover_background',
                            'label' => esc_html__( 'Background', 'ht-slider' ),
                            'types' => [ 'classic', 'gradient' ],
                            'selector' => '{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover',
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'htslider_arrow_hover_border',
                            'label' => esc_html__( 'Border', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover',
                        ]
                    );

                    $this->add_responsive_control(
                        'htslider_arrow_hover_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );

                $this->end_controls_tab(); // Hover tab end

            $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render( $instance = [] ) {

        $settings   = $this->get_settings_for_display();

        // check content source is ht_slider_slides
        if ( $settings['slides_source'] == 'ht_slider_slides' ) {
            $args = array(
                'post_type'             => 'htslider_slider',
                'posts_per_page'        => $settings['slider_limit'],
                'post_status'           => 'publish',
                'order'                 => 'ASC',
            );
    
            // Fetch By id
            if( $settings['slider_show_by'] == 'show_byid' ){
                $args['post__in'] = $settings['slider_id'];
            }
    
            // Fetch by category
            if( $settings['slider_show_by'] == 'show_bycat' ){
                // By Category
                $get_slider_categories = $settings['slider_cat'];
                $slider_cats = str_replace(' ', '', $get_slider_categories);
                if ( "0" != $get_slider_categories) {
                    if( is_array( $slider_cats ) && count( $slider_cats ) > 0 ){
                        $field_name = is_numeric( $slider_cats[0] )?'term_id':'slug';
                        $args['tax_query'] = array(
                            array(
                                'taxonomy' => 'htslider_category',
                                'terms' => $slider_cats,
                                'field' => $field_name,
                                'include_children' => false
                            )
                        );
                    }
                }
            }
            $sliders = new \WP_Query( $args );
    
            $sliderpost_ids = array();
            while( $sliders->have_posts() ):$sliders->the_post();
                $sliderpost_ids[] = get_the_ID();
            endwhile;
            wp_reset_postdata(); wp_reset_query();
        } else {
           
        }

        $this->add_render_attribute( 'swiperslider_area_attr', 'class', 'swiper-container' );

        $slider_settings = [
            'slideitem'         => absint( $settings['slider_item'] ),
            'direction'         => 'vertical',
            'mousewheel'        => ('yes' === $settings['slider_mousewheel']),
            'keyboardscroll'        => ('yes' === $settings['slider_keyboard_scroll']),
            'simulateTouch'     => ('yes' === $settings['slider_simulate_touch']) ? true:false,
            'arrow'             => ('yes' === $settings['slider_arrow']),
            'pagination'        => ('yes' === $settings['slider_dots']),
            'speed'             => absint( $settings['slider_speed'] ),
            'initialslide'      => absint( $settings['initial_slider'] ) - 1,
            'slide_custom_menu'        => false,
        ];
        $this->add_render_attribute( 'swiperslider_area_attr', 'data-settings', wp_json_encode( $slider_settings ) );
        if ( $settings['slides_source'] == 'ht_slider_slides' && empty( $sliderpost_ids ) ) {
           echo "<div class='htslider-error-notice'>" . esc_html__('There are no slides in this query.', 'ht-slider') . "</div>";
           return;
        }
        if ( $settings['slides_source'] == 'custom' && empty( $settings['navigator_content_list'] ) ) {
            echo "<div class='htslider-error-notice'>" . esc_html__('There are no slides in this query.', 'ht-slider') . "</div>";
            return;
         }
        ?>
            <!-- Swiper -->
            <div <?php echo $this->get_render_attribute_string( 'swiperslider_area_attr' ); ?>>
                <?php if( $settings['slider_arrow'] == 'yes' ){ echo '<div class="swiper-button-next"></div>'; } ?>
                <div class="swiper-wrapper">
                    <?php 
                     if ( $settings['slides_source'] == 'ht_slider_slides' ) {
                        if ( !empty( $sliderpost_ids ) ) {
                            foreach ( $sliderpost_ids as $sliderpost_id ) {
                                echo '<div class="swiper-slide">'.htslider_render_build_content( $sliderpost_id ).'</div>';
                            }
                        } else {
                            echo "<div class='htslider-error-notice'>" . esc_html__( 'There are no slides in this query','ht-slider' ) . "</div>";
                        }
                     } else {
                        
                        // Check if the 'navigator_content_list' has content
                        if ( !empty( $settings['navigator_content_list'] ) ) {                        
                            // Loop through each item in 'navigator_content_list'
                            foreach ( $settings['navigator_content_list'] as $navigatorcontent ) :
            
                                ?>
                                <div class="swiper-slide elementor-repeater-item-<?php echo esc_attr( $navigatorcontent['_id'] ); ?>">
                                    
                                    <?php 
                                    if ( !empty( $navigatorcontent['slider_raw_content'] ) ) {
                                        echo '<div class="htslider-scroll-navigation-inner">
                                                <div class="htslider-scroll-navigation-content">
                                                    <div>' . wp_kses_post( $navigatorcontent['slider_raw_content'] ) . '</div>
                                                </div>
                                              </div>';
                                    }
                                    ?>
                                    
                                </div>
                                <?php 
                            endforeach;
                        
                        } else {
                            // Display error message if the content list is empty
                            echo "<div class='htslider-error-notice'>" . esc_html__('There is no slide added','ht-slider') . "</div>";
                        }
                        
                    }
                ?>
                </div>
                <?php
                    if( $settings['slider_arrow'] == 'yes' ){
                        echo '<div class="swiper-button-prev"></div>';
                    }
                    // Pagination
                    if( $settings['slider_dots'] == 'yes' ){
                        echo '<div class="htslider-swiper-pagination swiper-pagination"></div>';
                    }
                ?>
                
            </div>

        <?php

    }

}