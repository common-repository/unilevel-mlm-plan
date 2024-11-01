<?php

/**
 * WooCommerce Admin Settings Class
 *
 * @package  WooCommerce/Admin
 * @version  3.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('UMP_Admin_Settings', false)) :


    class UMP_Admin_Settings
    {

        private static $errors = array();

        private static $messages = array();

        public static function get_settings_pages()
        {

            include_once dirname(__FILE__) . '/settings/class-ump-settings-page.php';
        }
    }

endif;
