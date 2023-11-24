<?php
// Uninstall Hook for Cleaning Up the Custom Table
function plugin_deactivator_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'disabled_plugins_GO';

    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}

register_uninstall_hook(__FILE__, 'plugin_deactivator_uninstall');
