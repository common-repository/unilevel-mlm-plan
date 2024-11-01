<?php

if (!defined('ABSPATH')) {
    exit;
}
// register hook
add_action('wp_ajax_ump_user_register', 'ump_front_register_function');
add_action('wp_ajax_nopriv_ump_user_register', 'ump_front_register_function');

add_action('wp_ajax_ump_username_exist', 'ump_username_exist_function');
add_action('wp_ajax_nopriv_ump_username_exist', 'ump_username_exist_function');

add_action('wp_ajax_ump_email_exist', 'ump_email_exist_function');
add_action('wp_ajax_nopriv_ump_email_exist', 'ump_email_exist_function');

add_action('wp_ajax_ump_epin_exist', 'ump_epin_exist_function');
add_action('wp_ajax_nopriv_ump_epin_exist', 'ump_epin_exist_function');

add_action('wp_ajax_ump_password_validation', 'ump_password_validation_function');
add_action('wp_ajax_nopriv_ump_password_validation', 'ump_password_validation_function');

add_action('wp_ajax_ump_sponsor_exist', 'ump_sponsor_exist_function');
add_action('wp_ajax_nopriv_ump_sponsor_exist', 'ump_sponsor_exist_function');
// register hook
add_action('ump_user_check_validate', 'ump_user_check_validate_function');

add_action('wp_ajax_update_payment_status_ump', 'update_payment_status_ump_function');
add_action('wp_ajax_nopriv_update_payment_status_ump', 'update_payment_status_ump_function');

add_filter('manage_users_columns', 'ump_add_custom_column_users');
add_action('manage_users_custom_column',  'ump_add_custom_column_users_value', 10, 3);
add_action('manage_users_columns', 'ump_remove_custom_column_users');

add_action('wp_head', 'ump_base_name_information');
