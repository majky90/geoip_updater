# Changelog

## 1.0.2

- Updated module metadata to version `1.0.2`.
- Improved admin configuration UI:
  - full-width cron URL textarea for easier viewing of the entire URL
  - copy button moved below the URL field for better spacing and readability
  - cleaner layout and spacing between admin panel elements
- Added server timezone display for `Last update` values in the admin UI.
- Adjusted cron schedule guidance text to clarify that the cron should run after the GeoIP file is generated, not at the exact generation time.
- Added upgrade compatibility so the module can be updated while already installed.

## 1.0.1

- Initial implementation of the GeoLite2 database updater module.
- Automatic download and atomic update process for `GeoLite2-City.mmdb`.
- Manual update action in admin panel.
- Front controller with token-based cron URL and JSON response.
- Basic error logging via `PrestaShopLogger`.
