<?php
/**
 * Register Schools Custom Post Type
 */

function wcb_register_schools_post_type() {
    $labels = [
        'name'                  => 'Schools',
        'singular_name'         => 'School',
        'menu_name'             => 'Schools',
        'name_admin_bar'        => 'School',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New School',
        'new_item'              => 'New School',
        'edit_item'             => 'Edit School',
        'view_item'             => 'View School',
        'all_items'             => 'All Schools',
        'search_items'          => 'Search Schools',
        'parent_item_colon'     => 'Parent Schools:',
        'not_found'             => 'No schools found.',
        'not_found_in_trash'    => 'No schools found in Trash.',
        'featured_image'        => 'School Logo',
        'set_featured_image'    => 'Set school logo',
        'remove_featured_image' => 'Remove school logo',
        'use_featured_image'    => 'Use as school logo',
        'archives'              => 'School Archives',
        'insert_into_item'      => 'Insert into school',
        'uploaded_to_this_item' => 'Uploaded to this school',
        'filter_items_list'     => 'Filter schools list',
        'items_list_navigation' => 'Schools list navigation',
        'items_list'            => 'Schools list',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'schools'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-building',
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'       => true, // Enable Gutenberg editor
    ];

    register_post_type('schools', $args);
}
add_action('init', 'wcb_register_schools_post_type', 5); // Register earlier with priority 5

/**
 * Register ACF Field Group for Schools
 */
function wcb_register_schools_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_schools_students',
        'title' => 'School Students',
        'fields' => [
            [
                'key' => 'field_school_students',
                'label' => 'Students',
                'name' => 'student',
                'type' => 'repeater',
                'instructions' => 'Add students enrolled in this school',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'collapsed' => 'field_student_name',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => 'Add Student',
                'sub_fields' => [
                    [
                        'key' => 'field_student_name',
                        'label' => 'Student Name',
                        'name' => 'student_name',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '30',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => 'Enter student full name',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ],
                    [
                        'key' => 'field_student_dob',
                        'label' => 'Date of Birth',
                        'name' => 'date_of_birth',
                        'type' => 'date_picker',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '25',
                            'class' => '',
                            'id' => '',
                        ],
                        'display_format' => 'd/m/Y',
                        'return_format' => 'Y-m-d',
                        'first_day' => 1,
                    ],
                    [
                        'key' => 'field_student_ethnicity',
                        'label' => 'Student Ethnicity',
                        'name' => 'student_ethnicity',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '25',
                            'class' => '',
                            'id' => '',
                        ],
                        'choices' => [
                            'maori' => 'Māori',
                            'pacific' => 'Pacific Islander',
                            'asian' => 'Asian',
                            'european' => 'European',
                            'middle_eastern' => 'Middle Eastern',
                            'african' => 'African',
                            'latin_american' => 'Latin American',
                            'other' => 'Other',
                            'prefer_not_to_say' => 'Prefer not to say',
                        ],
                        'default_value' => '',
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'ajax' => 0,
                        'return_format' => 'label',
                        'placeholder' => 'Select ethnicity',
                    ],
                    [
                        'key' => 'field_student_notes',
                        'label' => 'Notes',
                        'name' => 'student_notes',
                        'type' => 'textarea',
                        'instructions' => 'Optional notes about the student',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '20',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => '',
                        'maxlength' => '',
                        'rows' => 2,
                        'new_lines' => '',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'schools',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'Student information for schools',
    ]);
}
add_action('acf/init', 'wcb_register_schools_acf_fields');

/**
 * Flush rewrite rules on activation
 */
function wcb_schools_flush_rewrite_rules() {
    wcb_register_schools_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wcb_schools_flush_rewrite_rules');

// Also flush on theme switch
add_action('after_switch_theme', 'wcb_schools_flush_rewrite_rules');

// Force flush rewrite rules if schools post type doesn't exist
add_action('init', function() {
    if (!post_type_exists('schools')) {
        error_log('Schools post type not found, attempting to register and flush');
        wcb_register_schools_post_type();
        flush_rewrite_rules();
    }
}, 20); // Run after the normal registration at priority 10