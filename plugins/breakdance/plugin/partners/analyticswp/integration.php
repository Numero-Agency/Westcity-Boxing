<?php

// @psalm-ignore-file

namespace Breakdance\Partners\AnalyticsWP;

add_action('plugins_loaded', '\Breakdance\Partners\AnalyticsWP\init_integration');

function init_integration()
{

    if (is_analyticswp_installed()) {
        add_action('wp_insert_post', '\Breakdance\Partners\AnalyticsWP\breakdance_form_submission_handler', 10, 3);
        add_filter(
            'manage_breakdance_form_res_posts_columns',
            '\Breakdance\Partners\AnalyticsWP\breakdance_form_analyticswp_custom_column'
        );
        add_action('manage_breakdance_form_res_posts_custom_column', '\Breakdance\Partners\AnalyticsWP\breakdance_form_analyticswp_custom_column_content', 10, 2);
    } else {
        // add installation notices / wizard
    }
}


function breakdance_form_submission_handler($post_id, $post, $update)
{
    if ($post->post_type == 'breakdance_form_res') {
        \AnalyticsWP\Lib\Event::track_server_event(
            'breakdance_form_submission',
            [
                'unique_event_identifier' => get_unique_event_identifier_for_form_submission($post_id),
            ]
        );
    }
}

function breakdance_form_analyticswp_custom_column($columns)
{
    $columns['analyticswp_journey'] = 'AnalyticsWP Journey';
    return $columns;
}

function breakdance_form_analyticswp_custom_column_content($column, $post_id)
{

    if ($column !== 'analyticswp_journey') return;

    $journey_path = \AnalyticsWP\Lib\URLs::admin_journey_path_for_event_where_condition(
        ['unique_event_identifier' => get_unique_event_identifier_for_form_submission($post_id)]
    );

    if ($journey_path) {
        echo "<a href='{$journey_path}'>View Journey</a>";
    } else {
        echo "No Journey";
    }
}

function get_unique_event_identifier_for_form_submission($post_id)
{
    return 'breakdance_form_submission' . '_' . $post_id;
}

function is_analyticswp_installed()
{
    return class_exists('\AnalyticsWP\Lib\Core');
}
