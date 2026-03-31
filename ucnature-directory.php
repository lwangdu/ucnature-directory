<?php
/**
 * Plugin Name: UC Nature Directory
 * Plugin URI: https://ucnature.org/
 * Description: Staff directory plugin using a custom post type, ACF, and block-theme friendly rendering.
 * Version: 0.1.2
 * Author: Lobsang Wangdu
 * Author URI: https://ucnature.org/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: ucnature-directory
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'UCNATURE_DIRECTORY_FILE' ) ) {
	define( 'UCNATURE_DIRECTORY_FILE', __FILE__ );
}

if ( ! defined( 'UCNATURE_DIRECTORY_DIR' ) ) {
	define( 'UCNATURE_DIRECTORY_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'UCNATURE_DIRECTORY_URL' ) ) {
	define( 'UCNATURE_DIRECTORY_URL', plugin_dir_url( __FILE__ ) );
}

require_once UCNATURE_DIRECTORY_DIR . 'includes/class-acf.php';
require_once UCNATURE_DIRECTORY_DIR . 'includes/class-query.php';
require_once UCNATURE_DIRECTORY_DIR . 'includes/class-content.php';
require_once UCNATURE_DIRECTORY_DIR . 'includes/class-blocks.php';

if ( ! class_exists( 'UCNature_Directory_Plugin' ) ) {

	final class UCNature_Directory_Plugin {

		const VERSION = '0.1.2';
		const CPT = 'ucn_contact';
		const TAX_CAMPUS = 'ucn_campus';
		const TAX_RESERVE = 'ucn_reserve';
		const TAX_ROLE = 'ucn_general_role';
		const META_FIRST_NAME = 'first_name';
		const META_MIDDLE_NAME = 'middle_name';
		const META_LAST_NAME = 'last_name';
		const META_SUFFIX = 'suffix';
		const META_PREFERRED_DISPLAY_NAME = 'preferred_display_name';
		const META_PRIMARY_EMAIL = 'primary_email';
		const META_SECONDARY_EMAIL = 'secondary_email';
		const META_PHONE = 'phone';
		const META_CELL_PHONE = 'cell_phone';
		const META_JOB_TITLE = 'job_title';
		const META_STREET_1 = 'street_1';
		const META_STREET_2 = 'street_2';
		const META_CITY = 'city';
		const META_STATE = 'state';
		const META_POSTAL_CODE = 'postal_code';
		const META_COUNTRY = 'country';
		const META_SORT_NAME = 'sort_name';
		const META_DIRECTORY_VISIBILITY = 'directory_visibility';
		const CACHE_VERSION_OPTION = 'ucnature_directory_cache_version';
		const DB_INDEX_OPTION = 'ucnature_directory_db_indexes_version';

		private static $instance = null;

		private $acf;
		private $blocks;
		private $content;
		private $query;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			$this->acf     = new UCNature_Directory_ACF( $this );
			$this->query   = new UCNature_Directory_Query( $this );
			$this->content = new UCNature_Directory_Content( $this );
			$this->blocks  = new UCNature_Directory_Blocks( $this, $this->query );

			add_action( 'init', array( $this, 'register_post_type_and_taxonomies' ) );
			add_action( 'init', array( $this, 'register_contact_meta' ) );
			add_action( 'admin_init', array( $this, 'maybe_upgrade_database_indexes' ) );
			add_action( 'admin_notices', array( $this, 'maybe_show_acf_dependency_notice' ) );
			add_action( 'admin_head', array( $this->acf, 'output_contact_editor_styles' ) );
			add_action( 'acf/init', array( $this->acf, 'register_acf_fields' ) );
			add_action( 'save_post_' . self::CPT, array( $this->content, 'sync_contact_title_and_sort_name' ), 20, 3 );
			add_action( 'save_post_' . self::CPT, array( $this->query, 'bump_directory_cache_version' ), 30 );
			add_action( 'before_delete_post', array( $this->query, 'maybe_bump_directory_cache_version_for_post' ) );
			add_action( 'transition_post_status', array( $this->content, 'notify_new_contact_published' ), 10, 3 );
			add_action( 'rest_after_insert_' . self::CPT, array( $this->content, 'persist_rest_taxonomies' ), 10, 3 );
			add_action( 'created_term', array( $this->query, 'maybe_bump_directory_cache_version_for_term' ), 10, 3 );
			add_action( 'edited_term', array( $this->query, 'maybe_bump_directory_cache_version_for_term' ), 10, 3 );
			add_action( 'delete_term', array( $this->query, 'maybe_bump_directory_cache_version_for_term' ), 10, 4 );
			add_action( 'template_redirect', array( $this->blocks, 'maybe_render_directory_results_partial' ) );
			add_action( 'init', array( $this->blocks, 'register_block_bindings_sources' ) );
			add_action( 'init', array( $this->blocks, 'register_block_templates' ) );
			add_action( 'init', array( $this->blocks, 'register_blocks' ) );
			add_filter( 'template_include', array( $this->content, 'load_templates' ) );
			add_filter( 'query_vars', array( $this->query, 'register_query_vars' ) );
			add_filter( 'use_block_editor_for_post_type', array( $this->acf, 'disable_block_editor_for_contacts' ), 10, 2 );
			add_action( 'pre_get_posts', array( $this->query, 'filter_directory_archive_query' ) );
			add_action( 'wp_enqueue_scripts', array( $this->content, 'enqueue_assets' ) );
			add_action( 'enqueue_block_editor_assets', array( $this->content, 'enqueue_block_editor_assets' ) );
			register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		}

		public static function activate() {
			$plugin = self::instance();
			$plugin->register_post_type_and_taxonomies();
			$plugin->maybe_add_directory_meta_indexes();
			flush_rewrite_rules();
		}

		public function acf() {
			return $this->acf;
		}

		public function blocks() {
			return $this->blocks;
		}

		public function content() {
			return $this->content;
		}

		public function query() {
			return $this->query;
		}

		public function register_post_type_and_taxonomies() {
			register_post_type(
				self::CPT,
				array(
					'labels' => array(
						'name' => __( 'Directory Contacts', 'ucnature-directory' ),
						'singular_name' => __( 'Directory Contact', 'ucnature-directory' ),
						'add_new' => __( 'Add Contact', 'ucnature-directory' ),
						'add_new_item' => __( 'Add New Contact', 'ucnature-directory' ),
						'edit_item' => __( 'Edit Contact', 'ucnature-directory' ),
						'new_item' => __( 'New Contact', 'ucnature-directory' ),
						'view_item' => __( 'View Contact', 'ucnature-directory' ),
						'search_items' => __( 'Search Contacts', 'ucnature-directory' ),
						'not_found' => __( 'No contacts found.', 'ucnature-directory' ),
						'not_found_in_trash' => __( 'No contacts found in Trash.', 'ucnature-directory' ),
					),
					'public' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'show_in_rest' => true,
					'has_archive' => 'directory',
					'rewrite' => array(
						'slug' => 'directory',
						'with_front' => false,
					),
					'menu_icon' => 'dashicons-id',
					'taxonomies' => array( self::TAX_CAMPUS, self::TAX_RESERVE, self::TAX_ROLE ),
					'supports' => array( 'editor', 'excerpt', 'thumbnail' ),
					'map_meta_cap' => true,
				)
			);

			$this->register_taxonomy(
				self::TAX_CAMPUS,
				__( 'Campuses', 'ucnature-directory' ),
				__( 'Campus', 'ucnature-directory' )
			);

			$this->register_taxonomy(
				self::TAX_RESERVE,
				__( 'Reserves', 'ucnature-directory' ),
				__( 'Reserve', 'ucnature-directory' )
			);

			$this->register_taxonomy(
				self::TAX_ROLE,
				__( 'General Roles', 'ucnature-directory' ),
				__( 'General Role', 'ucnature-directory' )
			);
		}

		public function register_contact_meta() {
			$meta_keys = array(
				self::META_FIRST_NAME,
				self::META_MIDDLE_NAME,
				self::META_LAST_NAME,
				self::META_SUFFIX,
				self::META_PREFERRED_DISPLAY_NAME,
				self::META_PRIMARY_EMAIL,
				self::META_SECONDARY_EMAIL,
				self::META_PHONE,
				self::META_CELL_PHONE,
				self::META_JOB_TITLE,
				self::META_STREET_1,
				self::META_STREET_2,
				self::META_CITY,
				self::META_STATE,
				self::META_POSTAL_CODE,
				self::META_COUNTRY,
				self::META_SORT_NAME,
			);

			foreach ( $meta_keys as $meta_key ) {
				register_post_meta(
					self::CPT,
					$meta_key,
					array(
						'single' => true,
						'type' => 'string',
						'show_in_rest' => true,
					)
				);
			}

			register_post_meta(
				self::CPT,
				self::META_DIRECTORY_VISIBILITY,
				array(
					'single' => true,
					'type' => 'boolean',
					'show_in_rest' => true,
				)
			);
		}

		public function has_acf() {
			return function_exists( 'acf_add_local_field_group' );
		}

		public function maybe_show_acf_dependency_notice() {
			if ( $this->has_acf() || ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( $screen && 'plugins' !== $screen->base && self::CPT !== $screen->post_type ) {
				return;
			}
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s is the plugin name. */
							__( '%s requires Advanced Custom Fields to display and edit directory contact fields. Please install and activate ACF.', 'ucnature-directory' ),
							'<strong>' . esc_html__( 'UC Nature Directory', 'ucnature-directory' ) . '</strong>'
						)
					);
					?>
				</p>
			</div>
			<?php
		}

		private function register_taxonomy( $taxonomy, $plural_label, $singular_label ) {
			register_taxonomy(
				$taxonomy,
				array( self::CPT ),
				array(
					'labels' => array(
						'name' => $plural_label,
						'singular_name' => $singular_label,
					),
					'public' => false,
					'show_ui' => true,
					'show_in_rest' => true,
					'hierarchical' => true,
					'show_admin_column' => true,
				)
			);
		}

		public function maybe_upgrade_database_indexes() {
			if ( get_option( self::DB_INDEX_OPTION ) === self::VERSION ) {
				return;
			}

			$this->maybe_add_directory_meta_indexes();
		}

		private function maybe_add_directory_meta_indexes() {
			global $wpdb;

			$table_name = $wpdb->postmeta;
			$required_indexes = array(
				'ucn_dir_visibility' => "ADD INDEX ucn_dir_visibility (meta_key(191), meta_value(1), post_id)",
				'ucn_dir_sort'       => "ADD INDEX ucn_dir_sort (meta_key(191), meta_value(100), post_id)",
			);

			$existing_indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( ! is_array( $existing_indexes ) ) {
				return;
			}

			$existing_index_names = array_unique( wp_list_pluck( $existing_indexes, 'Key_name' ) );

			foreach ( $required_indexes as $index_name => $sql ) {
				if ( in_array( $index_name, $existing_index_names, true ) ) {
					continue;
				}

				$wpdb->query( "ALTER TABLE {$table_name} {$sql}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			update_option( self::DB_INDEX_OPTION, self::VERSION, false );
		}
	}

	UCNature_Directory_Plugin::instance();
}
