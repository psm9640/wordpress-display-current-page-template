# Display Current Page Template Plugin

A simple WordPress plugin that displays the current page template in the admin panel and at the bottom-left of the front-end view for logged-in users with post edit capabilities. Useful for developers and administrators to quickly view which template is being used.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Filters and Actions](#filters-and-actions)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)

## Features

- Displays the current page template in the WordPress admin panel as a column.
- Shows the current page template in the front-end for logged-in users with post edit capabilities.
- Adds a template filter dropdown in the admin list view to filter posts/pages/products by template.
- Supports `page`, `post`, and `product` post types by default, but can be extended for other post types.

## Installation

### 1. Download and Install the Plugin
1. Clone or download this repository.
2. Upload the plugin folder to your WordPress `/wp-content/plugins/` directory.

### 2. Activate the Plugin
1. Go to your WordPress admin dashboard.
2. Navigate to **Plugins** → **Installed Plugins**.
3. Find "Display Current Page Template" and click **Activate**.

## Usage

Once activated:

1. **View in Admin Panel:**
   - A new column labeled **Post Template** will appear in the post list view for supported post types (`page`, `post`, and `product` by default). This shows the template file used for each post or page.
   
2. **View on the Front-End:**
   - When logged in as a user with post edit capabilities, the current template will be displayed at the bottom-left corner of the screen while viewing any page.

3. **Filter by Template in Admin:**
   - A new dropdown labeled **All Templates** appears in the admin panel for post types. You can filter posts/pages/products by the template they use.

## Filters and Actions

### Hooks:

- **Filters:**
  - `manage_edit-{$post_type}_columns`: Adds a new column to display the post template in the list view for supported post types.
  
- **Actions:**
  - `wp_enqueue_scripts`: Enqueues the CSS to display the template name on the front end.
  - `wp_footer`: Renders the template name in the front-end footer for logged-in users with post edit capabilities.
  - `manage_{$post_type}_posts_custom_column`: Displays the actual template name in the new **Post Template** column.
  - `restrict_manage_posts`: Adds a filter dropdown in the admin list view to filter posts by template.
  - `parse_query`: Modifies the query to filter posts/pages/products by the selected template.

### Customizable:

- The post types where the template column appears can be modified via the `vtw_post_types` function.
- You can extend the functionality by adding more post types or filters if needed.

## Changelog

### Version 1.1

- Added nonce protection for filtering by template.
- Optimized queries and global variable usage.
- Improved escaping of output for security (XSS prevention).
- Consistent use of translation text domain.
- Added versioning to enqueued CSS.

### Version 1.0

- Initial release.
- Display template in the front end for logged-in users.
- Add template column in the admin list view.

## Contributing

Contributions, issues, and feature requests are welcome! Feel free to submit a pull request or open an issue on GitHub.

### How to Contribute:
1. Fork the repository.
2. Create a new feature branch (`git checkout -b feature/new-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/new-feature`).
5. Create a new pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
