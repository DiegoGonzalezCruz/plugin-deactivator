<?php
/*
Plugin Name: My Environment Checker
Description: Checks the environment and manages plugins accordingly.
Version: 1.0
Author: Diego González Cruz - https://go-agency.com
*/

function is_staging_or_local_environment()
{
    $url = $_SERVER['HTTP_HOST'];
    return (strpos($url, 'staging') !== false || strpos($url, 'stage') !== false || strpos($url, 'local') !== false);
}

function deactivate_selected_plugins($plugins_to_deactivate)
{
    if (!current_user_can('activate_plugins')) {
        error_log('User does not have permission to deactivate plugins.');
        return; // Exit if current user is not an admin
    }

    if (is_array($plugins_to_deactivate)) {
        foreach ($plugins_to_deactivate as $plugin) {
            if (is_plugin_active($plugin)) { // Check if the plugin is active before attempting to deactivate
                $result = deactivate_plugins($plugin);
                if (is_wp_error($result)) {
                    // Handle error, log to debug.log
                    error_log('Failed to deactivate plugin: ' . $plugin);
                } else {
                    error_log('Plugin deactivated successfully: ' . $plugin);
                }
            } else {
                error_log('Plugin already inactive: ' . $plugin);
            }
        }
    }
    update_option('my_plugin_deactivated_plugins', $plugins_to_deactivate);
}

// Handle form submission for plugin deactivation
function handle_plugin_deactivation()
{
    // Handle reactivation of a plugin
    if (isset($_POST['plugin_to_reactivate']) && current_user_can('activate_plugins')) {
        check_admin_referer('deactivate-plugins-action');
        $plugin_to_reactivate = $_POST['plugin_to_reactivate'];
        activate_plugin($plugin_to_reactivate);

        // Remove from the list of deactivated plugins
        $deactivated_plugins = get_option('my_plugin_deactivated_plugins', array());
        if (($key = array_search($plugin_to_reactivate, $deactivated_plugins)) !== false) {
            unset($deactivated_plugins[$key]);
        }
        update_option('my_plugin_deactivated_plugins', $deactivated_plugins);
    }
}

add_action('admin_init', 'handle_plugin_deactivation');

// Add admin menu
function my_plugin_menu()
{
    add_menu_page('Plugin Deactivator', 'Plugin Deactivator', 'manage_options', 'plugin-deactivator', 'plugin_deactivator_settings_page');
}
add_action('admin_menu', 'my_plugin_menu');

// Settings page with checkboxes for active plugins
function plugin_deactivator_settings_page()
{
    $active_plugins = get_option('active_plugins');
    $deactivated_plugins = get_option('my_plugin_deactivated_plugins', array());

?>
    <style>
        .unactive_plugin {

            display: flex;
            gap: 10px;
            align-items: center;
        }
    </style>
    <div class="wrap">
        <h1>Plugin Deactivator</h1>

        <?php
        // Display the list of deactivated plugins
        echo '<h2>This plugin is currently deactivating the following plugin/s:</h2>';
        if (!empty($deactivated_plugins)) {
            echo '<ul>';
            foreach ($deactivated_plugins as $plugin) {
                echo '<li class="unactive_plugin">';
                echo esc_html($plugin);
        ?>
                <form method="post" action="">
                    <input type="hidden" name="plugin_to_reactivate" value="<?php echo esc_attr($plugin); ?>">
                    <?php submit_button('Reactivate', 'secondary', 'reactivate_plugin', false); ?>
                </form>
        <?php
                echo '</li>';
            }
            echo '</ul>';
        }


        ?>
        <h2>This is the list of active plugins:</h2>

        <form method="post" action="">
            <?php wp_nonce_field('deactivate-plugins-action'); ?>
            <?php foreach ($active_plugins as $plugin) : ?>
                <input type="checkbox" name="plugins_to_deactivate[]" value="<?php echo esc_attr($plugin); ?>">
                <?php echo esc_html($plugin); ?><br>
            <?php endforeach; ?>
            <?php submit_button('Deactivate Selected Plugins'); ?>
        </form>
    </div>
<?php
}
