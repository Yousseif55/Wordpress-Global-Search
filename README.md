# Global Search Plugin

## Description

**Global Search** is a powerful search enhancement plugin for WordPress. It extends the default WordPress dashboard search functionality, providing a comprehensive global search utility for both WordPress content and WooCommerce products. This plugin allows users to perform advanced searches across various types of content from the WordPress Dashboard.

## Features

- **Advanced Search**: Enhances the default WordPress search to include global content and WooCommerce products.
- **Toolbar Search**: Adds a search bar to the WordPress admin toolbar for quick and easy searches.
- **Search Settings**: Configure search preferences from the plugin settings page.
- **Admin Notices**: Provides notices to inform users about the search functionalities and updates.
- **Quick Search Toggle**: Option to enable or disable quick search directly from the toolbar.

## Installation

1. **Upload** the `global-search` plugin folder to your WordPress plugins directory (`/wp-content/plugins/`).
2. **Activate** the plugin through the 'Plugins' menu in WordPress.

## Usage

### Accessing the Search Feature

- **Toolbar Search**:
  - After activation, you will find a search bar in the WordPress admin toolbar.
  - Use this search bar to perform global searches across your WordPress site and WooCommerce products.

### Managing Settings

1. Navigate to **Global Search** in the WordPress admin menu.
2. Adjust settings such as quick search preferences and other configuration options.

### Quick Search Toggle

- **Enable/Disable Quick Search**:
  - Use the checkbox in the toolbar search bar to toggle quick search functionality on or off.
  - The status is saved and applied across your WordPress admin area.

## Functions

### `codecruze_search_menu()`

Adds a menu item to the WordPress admin menu for accessing the plugin's settings page.

### `codecruze_search_menu_page()`

Displays the plugin settings page and handles saving settings.

### `codecruze_add_toolbar_items($admin_bar)`

Adds the search bar to the WordPress admin toolbar.

### `codecruze_enqueue($hook)`

Enqueues necessary JavaScript and CSS files for the plugin.

### `admin_notices()`

Displays admin notices related to the plugin’s status and updates.

### `send_source_to_admin()`

Injects JavaScript with search content data into the admin footer.

### `setting_page_redirect_on_activation()`

Redirects to the plugin settings page upon activation.

### `save_quick_search_status()`

Handles AJAX requests to save the quick search toggle status.

## Changelog

### Version 1.1.1

- Added toolbar search functionality for quick access.
- Improved search settings and admin notices.
- Enhanced quick search toggle feature.

## Notes

- This code is a **sample** from a larger project and is **not complete**. It is provided for demonstration purposes and may require further development to fully integrate with your WordPress site.
- Ensure that you review and test the plugin thoroughly in your environment.

## Author

**Yousseif Ahmed**

## License

This plugin is licensed under the [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html) license.

---

For support, issues, or feature requests, please contact the author or contribute to the project repository.
