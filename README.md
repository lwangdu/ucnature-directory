# UC Nature Directory

Block-friendly staff directory plugin for UC Nature sites.

This plugin provides a custom post type for directory contacts, taxonomy-based organization, public directory filtering, and block-aware single/archive templates.

## Overview

UC Nature Directory is designed for teams that want a maintainable, code-driven directory instead of a large amount of backend field configuration.

It gives you:

- A dedicated `Directory Contact` post type
- Structured contact fields powered by ACF
- Campus, reserve, and role taxonomy organization
- A public `/directory/` archive with search and filters
- Single-contact templates for both block themes and classic themes
- Dynamic directory blocks for archives and contact detail output

## Features

- Custom post type for directory contacts
- Campus, reserve, and general role taxonomies
- Public directory archive with search and filtering
- Single-contact display templates
- ACF-powered contact fields registered in code
- Custom dynamic blocks for directory filters, results, and contact details

## Requirements

- WordPress 6.4+
- PHP 7.4+
- Advanced Custom Fields

## Installation

1. Copy this plugin into `wp-content/plugins/ucnature-directory`
2. Activate the plugin in WordPress
3. Make sure Advanced Custom Fields is installed and active
4. Add directory contacts in the WordPress admin
5. Visit `/directory/` or place the provided blocks into a template

## How It Works

### Content Entry

Contacts are stored as a custom post type named `ucn_contact`.

Editors add contact information through ACF-powered fields such as:

- Name parts
- Preferred display name
- Email addresses
- Phone numbers
- Job title
- Mailing address
- Public visibility toggle

### Public Output

The plugin supports:

- A public directory archive at `/directory/`
- Search and campus filtering
- Grouped display by last name, campus, or reserve
- Single contact pages
- Block-template rendering for supported themes

### Editor Blocks

The plugin includes custom dynamic blocks for:

- Full directory archive
- Directory filters
- Directory results
- Taxonomy detail output
- Meta field detail output

## Main Data Fields

The plugin stores contact information in post meta, including:

- `first_name`
- `middle_name`
- `last_name`
- `suffix`
- `preferred_display_name`
- `primary_email`
- `secondary_email`
- `phone`
- `cell_phone`
- `job_title`
- `street_1`
- `street_2`
- `city`
- `state`
- `postal_code`
- `country`
- `directory_visibility`

## Taxonomies

- `ucn_campus`
- `ucn_reserve`
- `ucn_general_role`

## Importing Data

You can import content into this plugin with a CSV importer, WP All Import, a REST workflow, or a custom script.

Common import mapping:

- Post type: `ucn_contact`
- Public visibility: `directory_visibility` set to `1`
- Taxonomies:
  - `ucn_campus`
  - `ucn_reserve`
  - `ucn_general_role`

If you use CSV import tools, map your columns directly to the meta keys listed above.

## Recommended Release Checklist

Before using this on a production or public site:

1. Test activation on a clean WordPress install
2. Confirm ACF is active and the contact fields appear correctly
3. Check the `/directory/` archive and single contact pages
4. Test a block theme and a classic theme if you support both
5. Create contacts with partial data and confirm empty fields display cleanly
6. Turn on `WP_DEBUG` and check for warnings or notices
7. Confirm directory search, filtering, and pagination still work as expected

## Notes

- The ACF field group is registered in code, not through the ACF admin UI.
- If ACF is missing, the plugin now shows an admin notice.
- The directory supports both block-theme and classic-theme rendering paths.
- This repository includes both `readme.txt` for WordPress/plugin packaging and `README.md` for GitHub visitors.

## Development

Typical update workflow:

```bash
git add .
git commit -m "Describe your change"
git push
```

## License

GPL-2.0-or-later
