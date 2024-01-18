<?php
/*
Core plugins classes
*/

class Vxpt
{
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        $this->version = defined('VXPT_VERSION') ? VXPT_VERSION : '1.0.0';
        $this->plugin_name = 'vxpt';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/db_data.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-vxpt-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-vxpt-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-vxpt-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-vxpt-public.php';

        $this->loader = new Vxpt_Loader();
    }

    private function set_locale()
    {
        $plugin_i18n = new Vxpt_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new Vxpt_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'setupSettingsMenu', 10);
        $this->loader->add_action('admin_post_vxpt_admin_save', $plugin_admin, 'savePricingTableData');
    }

    private function define_public_hooks()
    {
        $plugin_public = new Vxpt_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function run()
    {
        $this->loader->run();
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_loader()
    {
        return $this->loader;
    }

    public function get_version()
    {
        return $this->version;
    }
}
