=== UC Nature Directory ===
Contributors: Lobsang Wangdu
Tags: directory, staff directory, custom post type, acf, blocks
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 0.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Block-friendly staff directory plugin for UC Nature sites. It provides a dedicated contact post type, taxonomy-based organization, public directory filtering, and single-contact templates.

== Description ==

UC Nature Directory adds a public-facing directory powered by a custom post type and block-friendly rendering.

Features include:

* Directory contact custom post type
* Campus, reserve, and general role taxonomies
* Public directory archive with search, campus filtering, and organization options
* Single contact templates for block themes and classic themes
* ACF-powered contact data entry fields registered in code
* Custom dynamic blocks for directory filters, results, and contact detail fields

This plugin is best suited for sites that already use Advanced Custom Fields and want a controlled, code-defined directory experience rather than a backend-configured field group.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it as a custom plugin in your deployment workflow.
2. Activate the plugin in WordPress.
3. Ensure Advanced Custom Fields is installed and active.
4. Add or edit `Directory Contact` entries in the WordPress admin.
5. Visit the `/directory/` archive, or place the provided directory blocks into a template.

== Frequently Asked Questions ==

= Does this plugin require ACF? =

Yes. The contact fields are registered with ACF local PHP field groups, so ACF must be active for the editor fields to appear.

= Can I edit the ACF field group from the WordPress backend? =

No. The field group is registered in code, so it is intentionally managed in the plugin files instead of the ACF field group UI.

= Does it work with block themes? =

Yes. The plugin includes block templates and custom dynamic blocks for archive and single contact displays.

== Changelog ==

= 0.1.2 =

* Hardened dynamic block rendering for empty values and pagination
* Improved front-end accessibility for async directory updates
* Improved editor UX and content-entry guidance
* Added public distribution metadata and plugin readme
