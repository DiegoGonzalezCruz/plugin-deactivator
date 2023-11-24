<?php

// Function to check if the environment is staging or local
function is_staging_or_local_environment() {
    $url = $_SERVER['HTTP_HOST'];
    return preg_match('/(staging|stage|local)/i', $url);
}

// Function to deactivate selected plugins
function deactivate_selected_plugins($plugins_to_deactivate) {
    if (!current_user_can('activate_plugins')) {
        error_log('User does not have permission to deactivate plugins.');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'deactivated_plugins';

    foreach ($plugins_to_deactivate as $plugin) {
        if (is_plugin_active($plugin)) {
            $result = deactivate_plugins($plugin);
            if (is_wp_error($result)) {
                error_log('Failed to deactivate plugin: ' . $plugin);
            } else {
                error_log('Plugin deactivated successfully: ' . $plugin);
                $wpdb->insert(
                    $table_name,
                    array('plugin_name' => $plugin, 'deactivated_at' => current_time('mysql')),
                    array('%s', '%s')
                );
            }
        } else {
            error_log('Plugin already inactive: ' . $plugin);
        }
    }
}

// Function to display a list of plugins with checkboxes
function list_of_plugins($active_plugins) {
    $html = '<h2>This is the list of active plugins:</h2>';
    $html .= '<form method="post" action="">';
    $html .= wp_nonce_field('deactivate-plugins-action', null, true, false);

    foreach ($active_plugins as $plugin) {
        $html .= '<input type="checkbox" name="plugins_to_deactivate[]" value="' . esc_attr($plugin) . '">';
        $html .= esc_html($plugin) . '<br>';
    }

    $html .= get_submit_button('Deactivate Selected Plugins');
    $html .= '</form>';

    return $html;
}
