<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * BMP_Admin class.
 */
class UMP_Admin
{

    public function __construct()
    {
        add_action('init', array($this, 'includes'));
    }

    public function includes()
    {
        include_once dirname(__FILE__) . '/class-ump-admin-menus.php';
        include_once dirname(__FILE__) . '/class-ump-admin-settings.php';
        include_once dirname(__FILE__) . '/settings/view/epin-report.php';
    }
}
return new UMP_Admin();
