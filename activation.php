<?php
/*
/
Hello, I'm creating a plugin for wordpress, which is installed and active in my client wordpress instance: staging and production. If it is in production, it doesn't work. If active on staging, it allows the admin user to deactivate plugins installed in wordpress. To deactivate plugins, a list with checkboxes and plugin names are shown. When the admin presses Deactivate button, the list of deactivated plugins is saved in its own table 'disabled_plugins_GO', where each entry has the plugin name and the date of deactivation. In the plugin settings, the list of currently deactivated plugins comes from 'disabled_plugins_GO'. The plugin keeps plugins deactivated as long as they are selected by the checkbox in the config panel.
*/
// Create a Custom Database Table on Plugin Activation
function create_plugin_deactivator_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'disabled_plugins_GO';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
plugin_name text NOT NULL,
deactivated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
PRIMARY KEY (id)
) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_plugin_deactivator_table');
