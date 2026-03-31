<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UCNature_Directory_Content' ) ) {
	final class UCNature_Directory_Content {

		private $plugin;

		public function __construct( $plugin ) {
			$this->plugin = $plugin;
		}

		public function sync_contact_title_and_sort_name( $post_id, $post, $update ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			$first_name = trim( (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_FIRST_NAME, true ) );
			$middle_name = trim( (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_MIDDLE_NAME, true ) );
			$last_name = trim( (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_LAST_NAME, true ) );
			$suffix = trim( (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_SUFFIX, true ) );
			$preferred_display_name = trim( (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_PREFERRED_DISPLAY_NAME, true ) );

			$display_name = $preferred_display_name;

			if ( '' === $display_name ) {
				$parts = array_filter( array( $first_name, $middle_name, $last_name, $suffix ) );
				$display_name = implode( ' ', $parts );
			}

			if ( '' === $display_name ) {
				$display_name = __( 'Untitled Contact', 'ucnature-directory' );
			}

			$sort_name = trim( implode( ', ', array_filter( array( $last_name, trim( $first_name . ' ' . $middle_name ) ) ) ) );

			remove_action( 'save_post_' . UCNature_Directory_Plugin::CPT, array( $this, 'sync_contact_title_and_sort_name' ), 20 );

			wp_update_post(
				array(
					'ID' => $post_id,
					'post_title' => $display_name,
					'post_name' => sanitize_title( $display_name ),
				)
			);

			add_action( 'save_post_' . UCNature_Directory_Plugin::CPT, array( $this, 'sync_contact_title_and_sort_name' ), 20, 3 );

			update_post_meta( $post_id, UCNature_Directory_Plugin::META_SORT_NAME, $sort_name );
		}

		public function notify_new_contact_published( $new_status, $old_status, $post ) {
			if ( ! $post instanceof WP_Post || UCNature_Directory_Plugin::CPT !== $post->post_type ) {
				return;
			}

			if ( 'publish' !== $new_status || 'publish' === $old_status ) {
				return;
			}

			$post_id = (int) $post->ID;
			$contact_email = trim( (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_PRIMARY_EMAIL, true ) );
			$roles = get_the_terms( $post_id, UCNature_Directory_Plugin::TAX_ROLE );
			$role_names = '';

			if ( ! empty( $roles ) && ! is_wp_error( $roles ) ) {
				$role_names = implode( ', ', wp_list_pluck( $roles, 'name' ) );
			}

			$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
			$subject = sprintf(
				__( '[%1$s] New Directory Contact: %2$s', 'ucnature-directory' ),
				$site_name,
				$post->post_title
			);

			$lines = array(
				__( 'A new directory contact has been published.', 'ucnature-directory' ),
				'',
				sprintf( __( 'Name: %s', 'ucnature-directory' ), $post->post_title ),
				sprintf( __( 'Email: %s', 'ucnature-directory' ), $contact_email ? $contact_email : __( '(none)', 'ucnature-directory' ) ),
				sprintf( __( 'General Roles: %s', 'ucnature-directory' ), $role_names ? $role_names : __( '(none)', 'ucnature-directory' ) ),
				'',
				sprintf( __( 'Edit contact: %s', 'ucnature-directory' ), get_edit_post_link( $post_id, '' ) ),
			);

			$message = implode( "\n", $lines );
			$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
			$recipients = array();

			if ( $admin_email ) {
				$recipients[] = $admin_email;
			}

			$legacy_recipient = (string) apply_filters( 'ucnature_directory_new_contact_notification_recipient', '' );
			if ( $legacy_recipient ) {
				$recipients[] = $legacy_recipient;
			}

			$extra_recipients = apply_filters( 'ucnature_directory_new_contact_notification_recipients', array() );
			if ( is_string( $extra_recipients ) && '' !== $extra_recipients ) {
				$extra_recipients = explode( ',', $extra_recipients );
			}
			if ( is_array( $extra_recipients ) ) {
				$recipients = array_merge( $recipients, $extra_recipients );
			}

			$recipients = array_values(
				array_unique(
					array_filter(
						array_map( 'sanitize_email', $recipients )
					)
				)
			);

			if ( empty( $recipients ) ) {
				return;
			}

			wp_mail( $recipients, $subject, $message );
		}

		public function persist_rest_taxonomies( $post, $request, $creating ) {
			if ( ! $post instanceof WP_Post || UCNature_Directory_Plugin::CPT !== $post->post_type ) {
				return;
			}

			$taxonomy_map = array(
				UCNature_Directory_Plugin::TAX_CAMPUS,
				UCNature_Directory_Plugin::TAX_RESERVE,
				UCNature_Directory_Plugin::TAX_ROLE,
			);

			foreach ( $taxonomy_map as $taxonomy ) {
				$terms = $request->get_param( $taxonomy );

				if ( null === $terms ) {
					continue;
				}

				if ( ! is_array( $terms ) ) {
					$terms = array_filter( array_map( 'trim', explode( ',', (string) $terms ) ) );
				}

				wp_set_object_terms( $post->ID, $terms, $taxonomy, false );
			}

			$this->plugin->query()->bump_directory_cache_version();
		}

		public function load_templates( $template ) {
			if ( is_post_type_archive( UCNature_Directory_Plugin::CPT ) && ! $this->plugin->blocks()->can_use_plugin_block_templates() ) {
				$archive_template = UCNATURE_DIRECTORY_DIR . 'templates/archive-ucn_contact.php';
				if ( file_exists( $archive_template ) ) {
					return $archive_template;
				}
			}

			if ( is_singular( UCNature_Directory_Plugin::CPT ) && ! $this->plugin->blocks()->can_use_plugin_block_templates() ) {
				$single_template = UCNATURE_DIRECTORY_DIR . 'templates/single-ucn_contact.php';
				if ( file_exists( $single_template ) ) {
					return $single_template;
				}
			}

			return $template;
		}

		public function enqueue_assets() {
			if ( ! is_post_type_archive( UCNature_Directory_Plugin::CPT ) && ! is_singular( UCNature_Directory_Plugin::CPT ) ) {
				return;
			}

			wp_enqueue_style(
				'ucnature-directory',
				UCNATURE_DIRECTORY_URL . 'assets/ucnature-directory.css',
				array(),
				UCNature_Directory_Plugin::VERSION
			);

			if ( $this->plugin->blocks()->can_use_interactivity_api() && is_post_type_archive( UCNature_Directory_Plugin::CPT ) ) {
				wp_register_script_module(
					'ucnature-directory-interactivity',
					UCNATURE_DIRECTORY_URL . 'assets/ucnature-directory-interactivity.js',
					array( '@wordpress/interactivity' ),
					UCNature_Directory_Plugin::VERSION
				);
				wp_enqueue_script_module( 'ucnature-directory-interactivity' );
			}
		}

		public function enqueue_block_editor_assets() {
			if ( function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();

				if ( $screen && isset( $screen->post_type ) && UCNature_Directory_Plugin::CPT !== $screen->post_type && 'site-editor' !== $screen->base ) {
					return;
				}
			}

			wp_enqueue_style(
				'ucnature-directory-editor',
				UCNATURE_DIRECTORY_URL . 'assets/ucnature-directory-editor.css',
				array( 'wp-edit-blocks' ),
				UCNature_Directory_Plugin::VERSION
			);

			wp_enqueue_script(
				'ucnature-directory-editor-blocks',
				UCNATURE_DIRECTORY_URL . 'assets/ucnature-directory-editor-blocks.js',
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor' ),
				UCNature_Directory_Plugin::VERSION,
				true
			);
		}
	}
}
