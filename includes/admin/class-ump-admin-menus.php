<?php
defined('ABSPATH') || exit;
if (class_exists('UMP_Admin_Menus', false)) {
    return new UMP_Admin_Menus();
}
/**
 * BMP_Admin_Menus Class.
 */
class UMP_Admin_Menus
{

    public function __construct()
    {
        // Add menus.
        add_action('admin_menu', array($this, 'admin_menu'), 9);
    }


    public function admin_menu()
    {
        global $menu;

        $icon_url = UMP()->plugin_url() . '/image/mlm_tree.png';
        add_menu_page(__('Unilevel MLM Plan', 'ump'), __('Unilevel MLM Plan', 'ump'), 'manage_options', 'ump-settings', array($this, 'settings_page_init'), $icon_url, 100);

        add_submenu_page('ump-settings', __('Settings', 'ump'), __('Settings', 'ump'), 'manage_ump', 'ump-settings', array($this, 'settings_page_init'));
        add_submenu_page('ump-settings', __('ePin Reports', 'ump'), __('ePin Reports', 'ump'), 'manage_ump', 'ump-epin-reports', array($this, 'ump_epin_reports'));
        add_submenu_page('ump-settings', __('Upgrade Plugin', 'ump'), __('Upgrade Plugin', 'ump'), 'manage_ump', 'ump-upgrade', array($this, 'ump_upgrade'));
    }
    public function ump_epin_reports()
    {
        global $wpdb;
        $UMP_Admin_ePin_Reports = new UMP_Admin_ePin_Reports;
        $UMP_Admin_ePin_Reports->get_epins_reports();
    }
    public function settings_page_init()
    {

        // echo '<div class="ump-section">';
        // echo '<div class="">' . isset($wp_session['ump_success_message']) . '</div>';
        UMP_Admin_Settings::get_settings_pages();
        // echo '</div>';
    }

    public function ump_upgrade()
    { ?>
        <div>
            <h2 class="mb-5">If you want to add more functionality like <b>(Users Reports, Commissions Distribution and User mails & sms, and Payout Report and Withdrwal Amount etc.)</b> in your plugin. so you can Upgrade/Purchase the plugin by link below.</h2>
            <a class="button btn button-primary m-auto d-block w-50 button-large mt-3" href="https://www.mlmtrees.com/product/unilevel-mlm-plan-wordpress/" target="_blank"><?php _e('Upgrade Plugin', 'ump'); ?></a>
        </div>
<?php

    }

    public function settings_page()
    {

        //UMP_Admin_Settings::output();
    }
}
return new UMP_Admin_Menus();
