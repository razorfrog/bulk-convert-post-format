# Bulk Convert Post Format

Bulk convert posts in a category to a selected post format. Select from a dropdown of categories and a dropdown of defined post formats.

- **WordPress.org:** https://wordpress.org/plugins/bulk-convert-post-format/
- **Plugin page:** https://razorfrog.com/bulk-edit-wordpress-post-format/
- **Current release:** 1.1.5 · requires WordPress 5.2 and PHP 7.2 · tested up to WordPress 6.9
- **License:** GPLv2 or later

`readme.txt` is the WordPress.org plugin page (description, FAQ, changelog). For help, use the plugin's support forum on WordPress.org.

## Checks

Every push runs `.github/workflows/check.yml`:

- `php -l` on every PHP file under PHP 7.2 and 8.4
- PHP_CodeSniffer with `phpcs.xml.dist`: PHP 7.2+ compatibility (PHPCompatibilityWP) plus WordPress security sniffs (output escaping, nonces, input sanitizing, prepared SQL, deprecated functions)

Run the same locally with `phpcs` from the repo root once WPCS and PHPCompatibilityWP are installed.
