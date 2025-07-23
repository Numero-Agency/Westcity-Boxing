<?php
namespace Elementor;
use Elementor\Icons_Manager;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Htslider_Elementor_Widget_Sliders extends Widget_Base {

    public function get_name() {
        return 'htslider-slider-addons';
    }
    
    public function get_title() {
        return __( 'HT: Slider', 'ht-slider' );
    }

    public function get_icon() {
        return 'eicon-slideshow';
    }

    public function get_categories() {
        return [ 'ht-slider' ];
    }

    public function get_style_depends() {
        return [ 'slick' ];
    }

    public function get_script_depends() {
        return [
            'htslider-active',
            'slick',
        ];
    }
    public function get_keywords() {
        return [ 'post slider', 'slider','custom post slider','carousel','post','ht-slider','htslider','content slider' ];
    }
    
    public function get_help_url() {
		return 'https://hasthemes.com/plugins/ht-slider-pro-for-elementor/';
	}
    protected function register_controls() {

        $this->start_controls_section(
            'ht-slider-slider-conent',
            [
                'label' => __( 'Slider', 'ht-slider' ),
            ]
        );
        $this->add_control(
            'avvanced_feature_slider_notice',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => sprintf(
                    /*
                    * translators: %1$s: HT Advanced Slider
                    * translators: %2$s: HT Posts Slider
                    */
                    __('Explore the  %1$s and %2$s widgets to showcase content from various posts', 'ht-slider'),
                    '<strong>  HT Advanced Slider</strong>',
                    '<strong> HT Posts Slider</strong>'),
                'content_classes' => 'htslider-addons-notice',
            ]
        );
            $this->add_control(
                'slider_show_by',
                [
                    'label' => esc_html__( 'Slider Show By', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'show_bycat',
                    'options' => [
                        'show_byid'   => __( 'Show By ID', 'ht-slider' ),
                        'show_bycat'  => __( 'Show By Category', 'ht-slider' ),
                    ],
                ]
            );

            $this->add_control(
                'slider_id',
                [
                    'label' => __( 'Select Slides', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT2,
                    'label_block' => true,
                    'multiple' => true,
                    'options' => htslider_post_name( 'htslider_slider' ),
                    'condition' => [
                        'slider_show_by' => 'show_byid',
                    ]
                ]
            );

            $this->add_control(
                'slider_cat',
                [
                    'label' => __( 'Select Category', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT2,
                    'label_block' => true,
                    'multiple' => true,
                    'options' => htslider_get_taxonomies( 'htslider_category' ),
                    'condition' => [
                        'slider_show_by' => 'show_bycat',
                    ]
                ]
            );
            
            $this->add_control(
                'slider_limit',
                [
                    'label' => __( 'Slider Limit', 'ht-slider' ),
                    'type' => Controls_Manager::NUMBER,
                    'step' => 1,
                    'default' => 2,
                ]
            );
            $this->add_control(
                'postorder',
                [
                    'label' => esc_html__( 'Order', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'ASC',
                    'options' => [
                        'DESC'  => esc_html__('Descending','ht-slider'),
                        'ASC'   => esc_html__('Ascending','ht-slider'),
                    ]
                ]
            );

        $this->end_controls_section();

        // Slider setting
        $this->start_controls_section(
            'ht-slider-slider',
            [
                'label' => esc_html__( 'Slider Option', 'ht-slider' ),
            ]
        );

            $this->add_control(
                'slprevicon',
                [
                    'label'         => esc_html__( 'Previous icon', 'ht-slider' ),
                    'type'          => Controls_Manager::ICONS,
                    'default'       => [
                        'value'     => 'fas fa-angle-left',
                        'library'   => 'fa-solid',
                    ],
                ]
            );

            $this->add_control(
                'slnexticon',
                [
                    'label'         => esc_html__( 'Next icon', 'ht-slider' ),
                    'type'          => Controls_Manager::ICONS,
                    'default'       => [
                        'value'     => 'fas fa-angle-right',
                        'library'   => 'fa-solid',
                    ]
                ]
            );

            $this->add_control(
                'slitems',
                [
                    'label' => esc_html__( 'Slider Items', 'ht-slider' ),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                    'default' => 1
                ]
            );
            $this->add_responsive_control(
                'column_gap',
                [
                    'label' => esc_html__( 'Column Gap', 'ht-slider' ),
                    'type' => Controls_Manager::SLIDER,
                    'description' => esc_html__( 'Add Column gap Ex. 15px', 'ht-slider' ),
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 1000,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .htslider-carousel-activation' => 'margin: 0 -{{SIZE}}px',
                        '{{WRAPPER}} .htslider-carousel-activation .slick-track' => 'margin: 0',
                        '{{WRAPPER}} .htslider-carousel-activation .slick-track .slick-slide' => 'padding-left:{{SIZE}}px;padding-right: {{SIZE}}px',
                    ],
                ]
            );
            $this->add_control(
                'slarrows',
                [
                    'label' => esc_html__( 'Slider Arrow', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'sldots',
                [
                    'label' => esc_html__( 'Slider dots', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'no'
                ]
            );

            $this->add_control(
                'slpause_on_hover',
                [
                    'type' => Controls_Manager::SWITCHER,
                    'label_off' => __('No', 'ht-slider'),
                    'label_on' => __('Yes', 'ht-slider'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                    'label' => __('Pause on Hover?', 'ht-slider'),
                ]
            );

            $this->add_control(
                'slpause_on_dragging',
                [
                    'type' => Controls_Manager::SWITCHER,
                    'label_off' => __('No', 'ht-slider'),
                    'label_on' => __('Yes', 'ht-slider'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                    'label' => __('Enable mouse dragging', 'ht-slider'),
                ]
            );

            $this->add_control(
                'slautolay',
                [
                    'label' => esc_html__( 'Slider auto play', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'separator' => 'before',
                    'default' => 'no'
                ]
            );

            $this->add_control(
                'slautoplay_speed',
                [
                    'label' => __('Autoplay speed', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'default' => 3000,
                    'condition' => [
                        'slautolay' => 'yes',
                    ]
                ]
            );


            $this->add_control(
                'slanimation_speed',
                [
                    'label' => __('Autoplay animation speed', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'default' => 300,
                    'condition' => [
                        'slautolay' => 'yes',
                    ]
                ]
            );

            $this->add_control(
                'slscroll_columns',
                [
                    'label' => __('Slider item to scroll', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                    'default' => 1,
                ]
            );

            $this->add_control(
                'heading_tablet',
                [
                    'label' => __( 'Tablet', 'ht-slider' ),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'after',
                ]
            );

            $this->add_control(
                'sltablet_display_columns',
                [
                    'label' => __('Slider Items', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 8,
                    'step' => 1,
                    'default' => 1,
                ]
            );

            $this->add_control(
                'sltablet_scroll_columns',
                [
                    'label' => __('Slider item to scroll', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 8,
                    'step' => 1,
                    'default' => 1,
                ]
            );

            $this->add_control(
                'sltablet_width',
                [
                    'label' => __('Tablet Resolution', 'ht-slider'),
                    'description' => __('The resolution to tablet.', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'default' => 1024,
                ]
            );

            $this->add_control(
                'heading_mobile',
                [
                    'label' => __( 'Mobile Phone', 'ht-slider' ),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'after',
                ]
            );

            $this->add_control(
                'slmobile_display_columns',
                [
                    'label' => __('Slider Items', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 4,
                    'step' => 1,
                    'default' => 1,
                ]
            );

            $this->add_control(
                'slmobile_scroll_columns',
                [
                    'label' => __('Slider item to scroll', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 4,
                    'step' => 1,
                    'default' => 1,
                ]
            );

            $this->add_control(
                'slmobile_width',
                [
                    'label' => __('Mobile Resolution', 'ht-slider'),
                    'description' => __('The resolution to mobile.', 'ht-slider'),
                    'type' => Controls_Manager::NUMBER,
                    'default' => 480,
                ]
            );
            $this->add_control(
                'hide_preloader',
                [
                    'label' => esc_html__( 'Hide Preloader', 'ht-slider' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'separator' => 'before',
                    'default' => 'no'
                ]
            );
        $this->end_controls_section(); // Slider Option end

        // Slider Button stle
        $this->start_controls_section(
            'ht-slider-slider-controller-style',
            [
                'label' => esc_html__( 'Slider Controller Style', 'ht-slider' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'slider_navigation_style',
                [
                    'label' => esc_html__( 'Slider Navigation Style', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => '1',
                    'options'   => [
                        '1'     => esc_html__( 'Default', 'ht-slider' ),
                        '2'     => esc_html__( 'Right Center', 'ht-slider' ),
                        '3'     => esc_html__( 'Bottom Left', 'ht-slider' ),
                        '4'     => esc_html__( 'Custom Position (Pro)', 'ht-slider' ),
                    ],
                ]
            );
            htslider_pro_notice( $this,'slider_navigation_style', '4', Controls_Manager::RAW_HTML );
            $this->add_responsive_control(
                'post_slider_arrow_inner_space',
                [
                    'label' => __( 'Inner Gap', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px','%'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 500,
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
                    'condition'     =>[
                        'slider_navigation_style' => ['2','3'],
                    ],
                    'classes' => 'htslider-disable-control',
                ]
            );
            $this->add_responsive_control(
                'slider_arrow_position_X',
                [
                    'label' => __( 'Offset X', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
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
                        'size' => '',
                    ],
                    'condition'     =>[
                        'slider_navigation_style!' => '4',
                    ],
                    'classes' => 'htslider-disable-control',
                ]
            );
            $this->start_controls_tabs('ht-slider_sliderbtn_style_tabs');

                // Slider Button style Normal
                $this->start_controls_tab(
                    'ht-slider_sliderbtn_style_normal_tab',
                    [
                        'label' => __( 'Normal', 'ht-slider' ),
                    ]
                );
                    $this->add_control(
                        'nav_size',
                        [
                            'label' => __( 'Arrow Size', 'ht-slider' ),
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px' ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 100,
                                    'step' => 1,
                                ]
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => 22,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider button i,
                                {{WRAPPER}} .slick-arrow i' => 'font-size: {{SIZE}}{{UNIT}};',
                                '{{WRAPPER}} .slick-arrow svg' => 'width: {{SIZE}}{{UNIT}};',
                            ],
                        ]
                    );
                    $this->add_responsive_control(
                        'slider_arrow_height',
                        [
                            'label' => esc_html__( 'Height', 'ht-slider' ) . ' <i class="eicon-pro-icon"></i>',
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
                                'size' => '',
                            ],
                            'classes' => 'htslider-disable-control'
                        ]
                    );
        
                    $this->add_responsive_control(
                        'slider_arrow_width',
                        [
                            'label' => esc_html__( 'Width', 'ht-slider' ) . ' <i class="eicon-pro-icon"></i>',
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
                                'size' => '',
                            ],
                            'classes' => 'htslider-disable-control'
                        ]
                    );
                    $this->add_control(
                        'button_color',
                        [
                            'label' => __( 'Color', 'ht-slider' ),
                            'type' => Controls_Manager::COLOR,
                            'default' =>'#1f2226',
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .htslider-slider .slick-arrow i' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .hero-slider-controls .slick-arrow i' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .htslider-slider .slick-arrow svg path' => 'fill: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_bg_color',
                        [
                            'label' => __( 'Background Color', 'ht-slider' ),
                            'type' => Controls_Manager::COLOR,
                            'default' =>'#ffffff',
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow' => 'background-color: {{VALUE}} !important;',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow' => 'background-color: {{VALUE}} !important;',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'button_border',
                            'label' => __( 'Border', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .htslider-slider .slick-arrow,{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow',
                        ]
                    );

                    $this->add_responsive_control(
                        'button_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'slider_arrow_boxshadow',
                            'label' => __( 'Box Shadow', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} button.slick-arrow',
                        ]
                    );
                    $this->add_responsive_control(
                        'button_padding',
                        [
                            'label' => __( 'Padding', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px', '%', 'em' ],
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                            ],
                        ]
                    );

                $this->end_controls_tab();// Normal button style end

                // Button style Hover
                $this->start_controls_tab(
                    'ht-slider_sliderbtn_style_hover_tab',
                    [
                        'label' => __( 'Hover', 'ht-slider' ),
                    ]
                );

                    $this->add_control(
                        'button_hover_color',
                        [
                            'label' => __( 'Color', 'ht-slider' ),
                            'type' => Controls_Manager::COLOR,
                            'default' =>'#23252a',
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow:hover' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .htslider-slider .slick-arrow:hover i' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .hero-slider-controls .slick-arrow:hover i' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow:hover' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .htslider-slider .slick-arrow:hover svg path' => 'fill: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'button_hover_bg_color',
                        [
                            'label' => __( 'Background', 'ht-slider' ),
                            'type' => Controls_Manager::COLOR,
                            'default' =>'#ffffff',
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow:hover' => 'background-color: {{VALUE}} !important;',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow:hover' => 'background-color: {{VALUE}} !important;',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'button_hover_border',
                            'label' => __( 'Border', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .htslider-slider .slick-arrow:hover,{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow:hover',
                        ]
                    );

                    $this->add_responsive_control(
                        'button_hover_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .htslider-slider .slick-arrow:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                                '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-arrow:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'slider_arrow_hover_boxshadow',
                            'label' => __( 'Box Shadow', 'ht-slider' ),
                            'selector' => '{{WRAPPER}} .slick-arrow:hover',
                        ]
                    );
                $this->end_controls_tab();// Hover button style end

            $this->end_controls_tabs();

        $this->end_controls_section(); // Tab option end
        $this->start_controls_section(
            'post_slider_pagination_style_section',
            [
                'label'         => esc_html__( 'Pagination', 'ht-slider' ),
                'tab'           => Controls_Manager::TAB_STYLE,
                'condition'     =>[
                    'sldots'    =>'yes',
                ]
            ]
        );
            
            //pagination postition
            $this->add_control(
                'pagination_position',
                [
                    'label' => esc_html__( 'Pagination Position', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                    'type' => Controls_Manager::POPOVER_TOGGLE,
                ]
            );
            $this->start_popover();
            $this->add_control(
                'dot_show_position',
                [
                    'label' => esc_html__( 'Dots Show In', 'ht-slider' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'dot_bottom_center',
                    'options' => [
                        'dot_bottom_center' => esc_html__( 'Bottom Center (Pro)', 'ht-slider' ),
                        'dot_bottom_left' => esc_html__( 'Bottom Left (Pro)', 'ht-slider' ),
                        'dot_bottom_right' => esc_html__( 'Bottom Right (Pro)', 'ht-slider' ),
                        'dot_right_center' => esc_html__( 'Right Center (Pro)', 'ht-slider' ),
                        'dot_left_center' => esc_html__( 'Left Center (Pro)', 'ht-slider' ),
                        'dot_custom' => esc_html__( 'Custom Position (Pro)', 'ht-slider' ),
                    ],                
                ]
            );

            $this->add_responsive_control(
                    'pagination_x_position',
                    [
                        'label' => esc_html__( 'Horizontal Position', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                        'type'  => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'default' => [
                            'size' => 50,
                            'unit' => '%',
                        ],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 100,
                            ],
                            '%' => [
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],

                        'condition'     =>[
                            'dot_show_position' => 'dot_custom',
                        ],
                        'classes' => 'htslider-disable-control',
                    ]
                );

                $this->add_responsive_control(
                    'pagination_y_position',
                    [
                        'label' => esc_html__( 'Vertical Position', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                        'type'  => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'default' => [
                            'size' => 92,
                            'unit' => '%',
                        ],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 100,
                            ],
                            '%' => [
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],
                        'condition'     =>[
                            'dot_show_position' => 'dot_custom',
                        ],
                        'classes' => 'htslider-disable-control',
                    ]
                );
                $this->add_responsive_control(
                    'carousel_dots_offset',
                    [
                        'label' => __( 'Offset X', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
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
                            'size' => '',
                        ],
                        'condition'     =>[
                            'dot_show_position!' => ['dot_custom','dot_bottom_center'],
                        ],
                        'classes' => 'htslider-disable-control',
                    ]
                );
                $this->add_responsive_control(
                    'carousel_dots_offset_y',
                    [
                        'label' => __( 'Offset Y', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
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
                            'size' => '',
                        ],
                        'condition'     =>[
                            'dot_show_position' => ['dot_bottom_left','dot_bottom_right','dot_bottom_center'],
                        ],
                        'classes' => 'htslider-disable-control',
                    ]
                );
                $this->add_responsive_control(
                    'carousel_dots_pagination_inner_space',
                    [
                        'label' => __( 'Inner Gap', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                        'type' => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 200,
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
                        'classes' => 'htslider-disable-control',
                    ]
                );
            $this->end_popover();

            $this->add_responsive_control(
                'slider_pagination_padding',
                [
                    'label'      => esc_html__( 'Padding', 'ht-slider' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [
                        '{{WRAPPER}} .htslider-postslider-area ul.slick-dots li button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'pagination_margin',
                [
                    'label'         => esc_html__( 'Margin', 'ht-slider' ),
                    'type'          => Controls_Manager::DIMENSIONS,
                    'size_units'    => [ 'px', '%', 'em' ],
                    'selectors'     => [
                        '{{WRAPPER}} .htslider-postslider-area .slick-dots li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' => 'after',
                ]
            );
            $this->start_controls_tabs('pagination_style_tabs',[
                'separator' => 'before',
            ]);

                $this->start_controls_tab(
                    'pagination_style_normal_tab',
                    [
                        'label' => esc_html__( 'Normal', 'ht-slider' ),
                    ]
                );
                
                $this->add_control(
                    'dots_bg_color',
                    [
                        'label' => __( 'Background Color', 'ht-slider' ),
                        'type' => Controls_Manager::COLOR,
                        'default' =>'#ffffff',
                        'selectors' => [
                            '{{WRAPPER}} .htslider-slider .slick-dots li button' => 'background-color: {{VALUE}} !important;',
                            '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li button' => 'background-color: {{VALUE}} !important;',
                        ],
                    ]
                );

                $this->add_group_control(
                    Group_Control_Border::get_type(),
                    [
                        'name' => 'dots_border',
                        'label' => __( 'Border', 'ht-slider' ),
                        'selector' => '{{WRAPPER}} .htslider-slider .slick-dots li button,{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li button',
                    ]
                );

                $this->add_responsive_control(
                    'dots_border_radius',
                    [
                        'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                        'type' => Controls_Manager::DIMENSIONS,
                        'selectors' => [
                            '{{WRAPPER}} .htslider-slider .slick-dots li button' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li button' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                        ],
                    ]
                );
                    $this->add_responsive_control(
                        'htslider_carousel_dots_height',
                        [
                            'label' => __( 'Height', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                            'type' => Controls_Manager::SLIDER,
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 200,
                                    'step' => 1,
                                ],
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => '',
                            ],
                            'classes' => 'htslider-disable-control',
                        ]
                    );
        
                    $this->add_responsive_control(
                        'htslider_carousel_dots_width',
                        [
                            'label' => __( 'Width', 'ht-slider' )  . ' <i class="eicon-pro-icon"></i>',
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px'],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 200,
                                    'step' => 1,
                                ],
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => '',
                            ],
                            'classes' => 'htslider-disable-control',
                        ]
                    );
                $this->end_controls_tab(); // Normal Tab end

                $this->start_controls_tab(
                    'pagination_style_active_tab',
                    [
                        'label' => esc_html__( 'Active', 'ht-slider' ),
                    ]
                );
                $this->add_control(
                    'dots_hover_bg_color',
                    [
                        'label' => __( 'Background Color', 'ht-slider' ),
                        'type' => Controls_Manager::COLOR,
                        'default' =>'#282828',
                        'selectors' => [
                            '{{WRAPPER}} .htslider-slider .slick-dots li button:hover' => 'background-color: {{VALUE}} !important;',
                            '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li button:hover' => 'background-color: {{VALUE}} !important;',
                            '{{WRAPPER}} .htslider-slider .slick-dots li.slick-active button' => 'background-color: {{VALUE}} !important;',
                            '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li.slick-active button' => 'background-color: {{VALUE}} !important;',
                            
                        ],
                    ]
                );

                $this->add_group_control(
                    Group_Control_Border::get_type(),
                    [
                        'name' => 'dots_border_hover',
                        'label' => __( 'Border', 'ht-slider' ),
                        'selector' => '{{WRAPPER}} .htslider-slider .slick-dots li button:hover,{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li button:hover,{{WRAPPER}} .htslider-slider .slick-dots li.slick-active button,{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li.slick-active button',
                    ]
                );

                $this->add_responsive_control(
                    'dots_border_radius_hover',
                    [
                        'label' => esc_html__( 'Border Radius', 'ht-slider' ),
                        'type' => Controls_Manager::DIMENSIONS,
                        'selectors' => [
                            '{{WRAPPER}} .htslider-slider .slick-dots li button:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            '{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li button:hover,{{WRAPPER}} .htslider-slider .slick-dots li.slick-active button,{{WRAPPER}} .htslider-slider-area .hero-slider-controls .slick-dots li.slick-active button' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                        ],
                    ]
                );

                    $this->add_responsive_control(
                        'htslider_carousel_dots_height_active',
                        [
                            'label' => __( 'Height', 'ht-slider' ) . ' <i class="eicon-pro-icon"></i>',
                            'type' => Controls_Manager::SLIDER,
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 200,
                                    'step' => 1,
                                ],
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => '',
                            ],
                            'classes' => 'htslider-disable-control',
                        ]
                    );
        
                    $this->add_responsive_control(
                        'htslider_carousel_dots_width_active',
                        [
                            'label' => __( 'Width', 'ht-slider' ) . ' <i class="eicon-pro-icon"></i>',
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => [ 'px'],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 200,
                                    'step' => 1,
                                ],
                            ],
                            'default' => [
                                'unit' => 'px',
                                'size' => '',
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .htslider-carousel-activation .slick-dots li.slick-active button' => 'width: {{SIZE}}px !important;',
                            ],
                            'classes' => 'htslider-disable-control',
                        ]
                    );
                $this->end_controls_tab(); // Hover Tab end

            $this->end_controls_tabs();

        $this->end_controls_section();

    }

    protected function render( $instance = [] ) {

        $settings   = $this->get_settings_for_display();
        $postorder  = $settings['postorder'];
        $id = $this->get_id();
        $args = array(
            'post_type'             => 'htslider_slider',
            'posts_per_page'        => $settings['slider_limit'],
            'post_status'           => 'publish',
            'order'                 => $postorder,
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

        // Slider Options
        $slider_settings = [
            'arrows' => ('yes' === $settings['slarrows']),
            'dots' => ('yes' === $settings['sldots']),
            'autoplay' => ('yes' === $settings['slautolay']),
            'autoplay_speed' => absint($settings['slautoplay_speed']),
            'animation_speed' => absint($settings['slanimation_speed']),
            'pause_on_hover' => ('yes' === $settings['slpause_on_hover']),
            'pause_on_dragging' => ('yes' === $settings['slpause_on_dragging']),
        ];

        $slider_responsive_settings = [
            'product_items' => $settings['slitems'],
            'scroll_columns' => $settings['slscroll_columns'],
            'tablet_width' => $settings['sltablet_width'],
            'tablet_display_columns' => $settings['sltablet_display_columns'],
            'tablet_scroll_columns' => $settings['sltablet_scroll_columns'],
            'mobile_width' => $settings['slmobile_width'],
            'mobile_display_columns' => $settings['slmobile_display_columns'],
            'mobile_scroll_columns' => $settings['slmobile_scroll_columns'],
        ];
        $slider_settings = array_merge( $slider_settings, $slider_responsive_settings );

        $sliderpost_ids = array();
        while( $sliders->have_posts() ):$sliders->the_post();
            $sliderpost_ids[] = get_the_ID();
        endwhile;
        wp_reset_postdata(); wp_reset_query();

        // Slider Area attribute
        $this->add_render_attribute( 'slider_area_attr', 'class', 'htslider-slider-area' );
        $this->add_render_attribute( 'slider_area_attr', 'class', 'navigation-style-'.$settings['slider_navigation_style'] );
        if ( 'yes' !== $settings['hide_preloader'] && !\Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            $this->add_render_attribute( 'slider_area_attr', 'class', 'loading' );
        }

        // Slider attribute
        $this->add_render_attribute( 'slider_attr', 'class', 'slider-area htslider-slider' );
        $this->add_render_attribute( 'slider_attr', 'data-settings', wp_json_encode( $slider_settings ) );
        
        // Append Navigation HTML
        $slider_append = array();
        if( $settings['slider_navigation_style'] != 1 ){
            $slider_append = [
                'appendArrows' =>'.htslider-controls-area-'.$id,
                'appendDots' =>'.htslider-controls-area-'.$id,
            ];
            $this->add_render_attribute( 'slider_attr', 'data-slick', wp_json_encode( $slider_append ) );
        }

        ?>
            <div <?php echo $this->get_render_attribute_string( 'slider_area_attr' ); ?> >
                <div <?php echo $this->get_render_attribute_string( 'slider_attr' ); ?> >
                    <?php foreach( $sliderpost_ids as $slider_item ): ?>
                        <div class="slingle-slider">
                            <?php
                           echo htslider_render_build_content($slider_item);             
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if( $settings['slider_navigation_style'] != 1 ){ echo '<div class="hero-slider-controls htslider-controls-area-'.esc_attr($id).'"></div>'; } ?>
                <?php if ( ! empty( $settings['slprevicon']['value'] ) ) : ?>
                    <button type="button" class="slick-prev" style="display:none"><?php Icons_Manager::render_icon( $settings['slprevicon'], ['aria-hidden' => 'true'] ); ?></button>
                <?php endif; ?>

                <?php if ( ! empty( $settings['slnexticon']['value'] ) ) : ?>
                    <button type="button" class="slick-next" style="display:none"><?php Icons_Manager::render_icon( $settings['slnexticon'], ['aria-hidden' => 'true'] ); ?></button>
                <?php endif; ?>
            </div>



        <?php
    }

}

