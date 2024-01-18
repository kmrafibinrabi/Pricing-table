<?php
/**delete when uninstall plugin */
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "vxpt_features`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "vxpt_columns`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "vxpt_pricing_tables`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "vxpt_templates`");
$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "vxpt_currency`");