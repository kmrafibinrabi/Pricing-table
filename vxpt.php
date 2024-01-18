<?php

/* @wordpress-plugin
 * Plugin Name:       VISER X Pricing Table
 * Plugin URI:        https://github.com/visex
 * Description:       Easily create and publish beautiful pricing tables with no coding skill.
 * Version:           1.0.0
 * Author:            Viserx
 * Author URI:        https://github.com/visex
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses
 * Text Domain:       viserx-pricing-table
 * Domain Path:       /languages
 */



 // If this file is called directly, terminate.
 if (!defined('WPINC')){
    die;
 }



 /**
 * Currently plugin version.
 * Rename this for your plugin and update it as you release new versions.
 */
define ('VXPT_VERSION', '1.0.0');

$url = plugin_dir_url(__FILE__);
define('VXPT_PLUGIN_URL', $url);


$dir_path = plugin_dir_path(__FILE__);
define('VXPT_PLUGIN_DIR_PATH', $dir_path);



/**
 * plugin activation.
 * documented in includes/class-vxpt-activator.php
 */

function activate_vxpt()
{
  require_once plugin_dir_path (__FILE__) . 'includes/class-vxpt-activator.php';
  Vxpt_Activator::activate();
}


function deactivate_vxpt()
{

   require_once plugin_dir_path(__FILE__) . 'includes/class-vxpt-deactivator.php';
   Vxpt_Deactivator::deactivate();
}

/**
 * activation and deactivation hook 
 */

register_activation_hook(__FILE__, 'activate_vxpt');
register_deactivation_hook(__FILE__, 'deactivate_vxpt');

require plugin_dir_path(__FILE__). 'includes/class-vxpt.php';

/**
 * execution of plugin
 * Since everything within the plugin is registered via hooks.
 * The file does not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_vxpt()
{
   $plugin = new Vxpt();
   $plugin->run();
}
run_vxpt();
