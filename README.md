# Outstand Icons

> A toolkit for working with icons in the block editor.

A growing set of icon enhancements for the block editor, served through the existing Gutenberg icon picker.

## Features

### Inline icons

Insert icons mid-text through a new **Inline icon** button in the rich-text toolbar (paragraph, heading, list, button, etc.). The picker lists every icon registered with the core `core/icon` block. See [docs/icons.md](docs/icons.md) for the full guide.

> Looking to register your own custom icons (or remove the ones that ship with WordPress)? See my [Icons Extended](https://github.com/s3rgiosan/icons-extended) plugin.

## Installation

### Manual Installation

1. Download the latest release ZIP from the [Releases page](https://github.com/pixelalbatross/outstand-icons/releases/latest).
2. Go to Plugins > Add New > Upload Plugin in your WordPress admin area.
3. Upload the ZIP file and click Install Now.
4. Activate the plugin.

### Install with Composer

To include this plugin as a dependency in your Composer-managed WordPress project:

1. Add the plugin to your project using the following command:

```bash
composer require pixelalbatross/outstand-icons
```

1. Run `composer install`.
2. Activate the plugin from your WordPress admin area or using WP-CLI.

## Quick start

1. In a post or page, select text in any paragraph/heading and click the **Inline icon** button in the rich-text toolbar.
2. Pick an icon from the popover.
3. Save — the frontend renders `<span class="os-icons-inline"><svg>…</svg></span>` inheriting `currentColor`.

## Requirements

- WordPress 7.0 or higher
- PHP 8.2 or higher

### Tests

PHP tests run inside a `wp-env` container:

```bash
npm run test:setup   # first time only — starts Docker WP + test DB
npm run test:unit
```

## Changelog

All notable changes to this project are documented in [CHANGELOG.md](https://github.com/pixelalbatross/outstand-icons/blob/main/CHANGELOG.md).

## License

This project is licensed under the [GPL-3.0-or-later](https://spdx.org/licenses/GPL-3.0-or-later.html).
