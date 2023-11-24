<?php
/*
Plugin Name: Plugin Deactivator
Description: Checks the environment and manages plugins accordingly.
Version: 1.0
Author: Diego González Cruz - https://go-agency.com
*/

function my_plugin_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_deactivator';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plugin_name varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'my_plugin_activate');



function my_plugin_deactivate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_deactivator'; // Same table name as you used in the activation hook

    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_deactivation_hook(__FILE__, 'my_plugin_deactivate');


function my_plugin_save_data($plugins) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_deactivator';

    // Check if the input is a single string, and if so, convert it to an array
    if (is_string($plugins)) {
        $plugins = array($plugins);
    }

    // Iterate over the array and insert each plugin name into the database
    foreach ($plugins as $plugin_name) {
        $wpdb->insert(
            $table_name,
            array('plugin_name' => $plugin_name),
            array('%s') // Format specifier - '%s' for string
        );
    }
}

function my_plugin_get_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_deactivator';

    // Perform the query to get all rows from the table
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    // Check if results are empty and return an appropriate value
    if (empty($results)) {
        // Return null or an empty array if no data is found
        return null; // or return array();
    }

    return $results;
}


function my_plugin_deactivate_plugins($plugins_to_deactivate) {
    if (!current_user_can('activate_plugins')) {
        error_log('User does not have permission to deactivate plugins.');
        return; // Exit if current user is not an admin
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    foreach ($plugins_to_deactivate as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
            // Optionally, you can handle errors or log the deactivation
        }
    }
}


function handle_plugin_deactivation_and_save() {
    if (isset($_POST['plugins_to_deactivate']) && current_user_can('activate_plugins')) {
        check_admin_referer('deactivate-plugins-action');

        $plugins_to_deactivate = $_POST['plugins_to_deactivate'];
        my_plugin_deactivate_plugins($plugins_to_deactivate); // Deactivate the plugins
        my_plugin_save_data($plugins_to_deactivate); // Save the plugin names to the table

        // Optionally, you can add an admin notice for successful operation
    }
}


function keep_plugins_deactivated() {
    // Get the list of plugins that should remain deactivated
    $plugins_to_keep_deactivated = my_plugin_get_data();

    if (is_array($plugins_to_keep_deactivated) && !empty($plugins_to_keep_deactivated)) {
        // Extract plugin names from the results
        $plugins_to_deactivate = array_map(function ($item) {
            return $item['plugin_name'];
        }, $plugins_to_keep_deactivated);

        // Check which of these plugins are currently active and deactivate them
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        foreach ($plugins_to_deactivate as $plugin) {
            if (is_plugin_active($plugin)) {
                deactivate_plugins($plugin);
            }
        }
    }
}

add_action('admin_init', 'keep_plugins_deactivated');


function is_staging_or_local_environment() {
    $url = $_SERVER['HTTP_HOST'];
    return (strpos($url, 'staging') !== false || strpos($url, 'stage') !== false || strpos($url, 'local') !== false);
}



function handle_plugin_reactivation() {
    if (isset($_POST['action']) && $_POST['action'] == 'reactivate_plugin' && current_user_can('activate_plugins')) {
        check_admin_referer('reactivate-plugin-action'); // Check the correct nonce


        $plugin_to_reactivate = $_POST['plugin_to_reactivate'];

        // Delete the plugin entry from the table
        global $wpdb;
        $table_name = $wpdb->prefix . 'plugin_deactivator';
        $wpdb->delete($table_name, array('plugin_name' => $plugin_to_reactivate));

        // Reactivate the plugin
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!is_plugin_active($plugin_to_reactivate)) {
            activate_plugin($plugin_to_reactivate);
        }

        // Optionally, add an admin notice for successful operation
    }
}




// Add admin menu
function my_plugin_menu() {
    add_menu_page('Plugin Deactivator', 'Plugin Deactivator', 'manage_options', 'plugin-deactivator', 'plugin_deactivator_settings_page');
}
add_action('admin_menu', 'my_plugin_menu');

// Settings page with checkboxes for active plugins
function plugin_deactivator_settings_page() {
    // Handle the form submission for deactivating and saving plugins
    handle_plugin_deactivation_and_save();
    handle_plugin_reactivation();


    $active_plugins = get_option('active_plugins');
    $deactivated_plugins = my_plugin_get_data(); ?>

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
                echo esc_html($plugin['plugin_name']); // Access the 'plugin_name' field

        ?>
                <form method="post" action="">
                    <input type="hidden" name="plugin_to_reactivate" value="<?php echo esc_attr($plugin['plugin_name']); ?>">
                    <input type="hidden" name="action" value="reactivate_plugin">
                    <?php wp_nonce_field('reactivate-plugin-action'); ?> <!-- Unique nonce for reactivation -->
                    <?php submit_button('Reactivate', 'secondary', 'submit_reactivate_plugin', false); ?>
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
