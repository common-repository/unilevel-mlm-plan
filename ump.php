<?php

/**
 * Plugin Name: Unilevel MLM Plan
 * Plugin URI: https://letscms.com/
 * Description: Best Unilevel MLM Plan with faviroute CMS wordpress. Good graphical representation.
 * Version: 1.1.0
 * Tested up to: 6.2
 * Author: LetsCMS
 * Author URI: https://letscms.com
 * Text Domain: ump
 * Domain Path: /i18n/languages/
 * Requires at least: 6.1
 * Requires PHP: 7.4
 *
 * @package UMP
 */
session_start();
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define UMP_PLUGIN_FILE.
if (!defined('UMP_PLUGIN_FILE')) {
    define('UMP_PLUGIN_FILE', __FILE__);
}
// Include the main WooCommerce class.
if (!class_exists('Ump')) {
    include_once dirname(__FILE__) . '/includes/class-ump.php';
}
function UMP()
{
    return Ump::instance();
}

// Global for backwards compatibility.
$GLOBALS['UMP'] = UMP();
