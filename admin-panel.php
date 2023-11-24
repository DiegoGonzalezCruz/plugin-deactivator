<?php



// Handle form submission for plugin deactivation
function handle_plugin_deactivation() {
    if (isset($_POST['plugin_to_reactivate']) && current_user_can('activate_plugins')) {
        check_admin_referer('deactivate-plugins-action');
        $plugin_to_reactivate = $_POST['plugin_to_reactivate'];
        activate_plugin($plugin_to_reactivate);

        global $wpdb;
        $table_name = $wpdb->prefix . 'deactivated_plugins';
        $wpdb->delete($table_name, array('plugin_name' => $plugin_to_reactivate), array('%s'));
    }
}

// Settings page function
function plugin_deactivator_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'deactivated_plugins';
    $deactivated_plugins = $wpdb->get_results("SELECT plugin_name FROM $table_name", ARRAY_A);

    $active_plugins = get_option('active_plugins');
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
        <h2>Your environment is <?php echo (!is_staging_or_local_environment()) ? 'prod' : 'stage'; ?></h2>

        <h2>This plugin is currently deactivating the following plugin/s:</h2>
        <?php if (!empty($deactivated_plugins)) : ?>
            <ul>
                <?php foreach ($deactivated_plugins as $plugin) : ?>
                    <li class="unactive_plugin">
                        <?php echo esc_html($plugin['plugin_name']); ?>
                        <form method="post" action="">
                            <input type="hidden" name="plugin_to_reactivate" value="<?php echo esc_attr($plugin['plugin_name']); ?>">
                            <?php submit_button('Reactivate', 'secondary', 'reactivate_plugin', false); ?>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php echo (!is_staging_or_local_environment()) ? "Can't edit plugins list" : list_of_plugins($active_plugins); ?>
    </div>
<?php
}
