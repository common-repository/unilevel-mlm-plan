<?php

/**
 * Ump setup
 *
 * @package Ump
 * @since   1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Main Ump Class.
 *
 * @class Ump
 */
final class Ump
{

    public $version = '1.0.0';

    protected static $_instance = null;

    public $session = null;


    public $query = null;

    public $product_factory = null;

    public $countries = null;


    public $integrations = null;


    public $cart = null;


    public $customer = null;

    public $structured_data = null;

    public $deprecated_hook_handlers = array();

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }



    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }



    private function init_hooks()
    {

        register_activation_hook(UMP_PLUGIN_FILE, array('UMP_Install', 'install'));
        add_action('init', array($this, 'init'), 0);
        add_action('init', array($this, 'ump_session_register'));

        register_deactivation_hook(UMP_PLUGIN_FILE, array('UMP_Install', 'deactivate'));

        register_uninstall_hook(UMP_PLUGIN_FILE, 'uninstall');
    }


    public function init()
    {
        $this->load_plugin_textdomain();

        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'custom_ump_admin_style'));
        }
    }
    public function ump_session_register()
    {
        $status = session_status();
        if (PHP_SESSION_DISABLED === $status) {
            return;
        }

        if (PHP_SESSION_NONE === $status) {
            return session_start();
        }
    }



    public function custom_ump_admin_style()
    {

        wp_enqueue_style('ump_admin_admin', UMP()->plugin_url() . '/assets/css/admin.css');
        wp_enqueue_style('ump_admin_bootstrap', UMP()->plugin_url() . '/assets/css/bootstrap.css');
        wp_enqueue_script('ump_admin_admin', UMP()->plugin_url() . '/assets/js/admin.js');
    }

    private function define_constants()
    {
        $upload_dir = wp_upload_dir(null, false);

        $this->define('UMP_ABSPATH', dirname(UMP_PLUGIN_FILE) . '/');
        $this->define('UMP_PLUGIN_BASENAME', plugin_basename(UMP_PLUGIN_FILE));
        $this->define('UMP_VERSION', $this->version);
    }


    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }


    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON') && !defined('REST_REQUEST');
        }
    }


    public function includes()
    {
        include_once UMP_ABSPATH . 'includes/ump-hooks.php';
        include_once UMP_ABSPATH . 'includes/common-functions.php';
        include_once UMP_ABSPATH . 'includes/class-ump-install.php';
        include_once UMP_ABSPATH . 'includes/catalog/class-ump-downlines.php';
        include_once UMP_ABSPATH . 'includes/catalog/class-ump-templates.php';
        if ($this->is_request('admin')) {
            include_once UMP_ABSPATH . 'includes/admin/class-ump-admin.php';
        }

        //UMP_Install::create_pages();
    }

    public function load_plugin_textdomain()
    {
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();

        $locale = apply_filters('plugin_locale', $locale, 'ump');

        unload_textdomain('ump');
        load_textdomain('ump', WP_LANG_DIR . '/ump/' . $locale . '.mo');
        load_plugin_textdomain('ump', false, plugin_basename(dirname(UMP_PLUGIN_FILE)) . '/i18n/languages');
    }


    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', UMP_PLUGIN_FILE));
    }

    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(UMP_PLUGIN_FILE));
    }
}
