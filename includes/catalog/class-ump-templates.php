<?php
if (!defined('ABSPATH')) {
    exit;
}

add_filter('page_template', 'ump_page_template', 10, 2);
function ump_page_template($page_template, $temp)
{
    global $post;
    if (is_page('register') && get_option('ump_register_page_id', true) == $post->ID && get_post_meta($post->ID, 'is_ump_page', true) == 1) {
        add_action('wp_enqueue_scripts', 'custom_ump_style');
        $page_template = UMP_ABSPATH . '/templates/ump-register.php';
    }
    if (is_page('downlines') && get_option('ump_downlines_page_id', true) == $post->ID && get_post_meta($post->ID, 'is_ump_page', true) == 1) {
        add_action('wp_enqueue_scripts', 'custom_ump_style');
        $page_template = UMP_ABSPATH . '/templates/ump-downlines.php';
    }

    return $page_template;
}


function custom_ump_style()
{

    if (is_page('register') || is_page('downlines')) {
        wp_enqueue_script('jquery');
        wp_enqueue_style('ump_bootstrap', UMP()->plugin_url() . '/assets/css/bootstrap.css');
        wp_enqueue_style('ump_main', UMP()->plugin_url() . '/assets/css/ump.css');
        wp_enqueue_script('ump_canvas', UMP()->plugin_url() . '/assets/js/main.js', array(), '', true);
        wp_enqueue_script('ump_bootstrap', UMP()->plugin_url() . '/assets/js/bootstrap.min.js', array(), '', true);
        wp_enqueue_script('ump_bootstrap_bundle', UMP()->plugin_url() . '/assets/js/bootstrap.bundle.min.js', array(), '', true);
    }
}
