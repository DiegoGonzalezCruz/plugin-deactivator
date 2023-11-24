# Plugin Deactivator

Plugin Deactivator is a WordPress plugin designed to manage plugin deactivation based on the environment. It is particularly useful for staging or local development environments, where certain plugins need to be kept inactive.

## Features

- Automatically deactivates a set of specified plugins.
- Allows manual deactivation and reactivation of plugins through the WordPress admin panel.
- Keeps track of deactivated plugins in a custom database table.

## Installation

1. **Upload Plugin**: Upload the `plugin-deactivator` folder to the `/wp-content/plugins/` directory.
2. **Activate Plugin**: Activate the plugin through the 'Plugins' menu in WordPress.
3. **Configure**: Visit the 'Plugin Deactivator' page under the admin menu to manage plugin deactivation.

## Usage

### Deactivating Plugins

1. Go to the 'Plugin Deactivator' page in the WordPress admin area.
2. Select the plugins you wish to deactivate.
3. Click the 'Deactivate Selected Plugins' button.

### Reactivating Plugins

1. On the 'Plugin Deactivator' page, you'll see a list of currently deactivated plugins.
2. Click the 'Reactivate' button next to the plugin you wish to reactivate.

### Automatic Deactivation

The plugin will automatically deactivate any plugins that are listed in the plugin's custom table upon each admin page load. This ensures that certain plugins remain inactive in specific environments.

## Frequently Asked Questions

**Q: How does Plugin Deactivator determine which environment it is in?**

A: Plugin Deactivator checks the URL to determine if it contains 'staging', 'stage', or 'local' to identify the environment.

**Q: What happens to the custom table when the plugin is deactivated?**

A: The custom table created by the plugin is removed from the database upon plugin deactivation.

## Changelog

- **1.0**
  - Initial release.

## Support

For support, please visit [https://go-agency.com](https://go-agency.com).

## License

This plugin is released under the GPL license.
