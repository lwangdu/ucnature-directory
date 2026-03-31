<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UCNature_Directory_Query' ) ) {
	final class UCNature_Directory_Query {

		private $plugin;

		public function __construct( $plugin ) {
			$this->plugin = $plugin;
		}

		public function register_query_vars( $vars ) {
			$vars[] = 'ucn_search';
			$vars[] = 'ucn_campus';
			$vars[] = 'ucn_role';
			$vars[] = 'ucn_reserve';
			$vars[] = 'ucn_organize';
			$vars[] = 'ucn_partial';
			return $vars;
		}

		public function filter_directory_archive_query( $query ) {
			if ( is_admin() || ! $query->is_main_query() ) {
				return;
			}

			if ( ! $query->is_post_type_archive( UCNature_Directory_Plugin::CPT ) ) {
				return;
			}

			$query->set( 'posts_per_page', 24 );
			$query->set( 'meta_key', UCNature_Directory_Plugin::META_SORT_NAME );
			$query->set( 'orderby', 'meta_value title' );
			$query->set( 'order', 'ASC' );
			$query->set( 'ignore_sticky_posts', true );

			$meta_query = array(
				array(
					'key' => UCNature_Directory_Plugin::META_DIRECTORY_VISIBILITY,
					'value' => '1',
					'compare' => '=',
				),
			);

			$tax_query = array();
			$search = get_query_var( 'ucn_search' );
			$campus = get_query_var( 'ucn_campus' );
			$role = get_query_var( 'ucn_role' );
			$reserve = get_query_var( 'ucn_reserve' );
			$organize = get_query_var( 'ucn_organize' );

			if ( ! in_array( $organize, array( 'lastname', 'campus', 'reserve' ), true ) ) {
				$organize = 'lastname';
			}

			if ( $search ) {
				$query->set( 's', sanitize_text_field( $search ) );
			}

			if ( $campus ) {
				$tax_query[] = array(
					'taxonomy' => UCNature_Directory_Plugin::TAX_CAMPUS,
					'field' => 'slug',
					'terms' => sanitize_title( $campus ),
				);
			}

			if ( $role ) {
				$tax_query[] = array(
					'taxonomy' => UCNature_Directory_Plugin::TAX_ROLE,
					'field' => 'slug',
					'terms' => sanitize_title( $role ),
				);
			}

			if ( $reserve ) {
				$tax_query[] = array(
					'taxonomy' => UCNature_Directory_Plugin::TAX_RESERVE,
					'field' => 'slug',
					'terms' => sanitize_title( $reserve ),
				);
			}

			$query->set( 'meta_query', $meta_query );

			if ( 'lastname' !== $organize ) {
				$query->set( 'posts_per_page', -1 );
				$query->set( 'no_found_rows', true );
			}

			if ( ! empty( $tax_query ) ) {
				if ( count( $tax_query ) > 1 ) {
					$tax_query['relation'] = 'AND';
				}
				$query->set( 'tax_query', $tax_query );
			}
		}

		public function get_directory_request_state() {
			$state = array(
				'paged' => max( 1, (int) get_query_var( 'paged', 1 ) ),
				'search' => isset( $_GET['ucn_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ucn_search'] ) ) : '',
				'campus' => isset( $_GET['ucn_campus'] ) ? sanitize_title( wp_unslash( $_GET['ucn_campus'] ) ) : '',
				'organize' => isset( $_GET['ucn_organize'] ) ? sanitize_key( wp_unslash( $_GET['ucn_organize'] ) ) : 'lastname',
			);

			if ( ! in_array( $state['organize'], array( 'lastname', 'campus', 'reserve' ), true ) ) {
				$state['organize'] = 'lastname';
			}

			return $state;
		}

		public function get_directory_results_query( $attributes, $state ) {
			global $wp_query;

			if (
				$wp_query instanceof WP_Query &&
				! is_admin() &&
				$wp_query->is_main_query() &&
				$wp_query->is_post_type_archive( UCNature_Directory_Plugin::CPT )
			) {
				return $wp_query;
			}

			return new WP_Query( $this->get_directory_query_args( $attributes, $state ) );
		}

		public function get_directory_query_args( $attributes, $state ) {
			$args = array(
				'post_type' => UCNature_Directory_Plugin::CPT,
				'post_status' => 'publish',
				'posts_per_page' => 'lastname' === $state['organize'] ? ( isset( $attributes['postsPerPage'] ) ? (int) $attributes['postsPerPage'] : 24 ) : -1,
				'paged' => $state['paged'],
				'meta_key' => UCNature_Directory_Plugin::META_SORT_NAME,
				'orderby' => 'meta_value title',
				'order' => 'ASC',
				'ignore_sticky_posts' => true,
				'meta_query' => array(
					array(
						'key' => UCNature_Directory_Plugin::META_DIRECTORY_VISIBILITY,
						'value' => '1',
						'compare' => '=',
					),
				),
			);

			if ( 'lastname' !== $state['organize'] ) {
				$args['no_found_rows'] = true;
			}

			if ( $state['search'] ) {
				$args['s'] = $state['search'];
			}

			if ( $state['campus'] ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => UCNature_Directory_Plugin::TAX_CAMPUS,
						'field' => 'slug',
						'terms' => $state['campus'],
					),
				);
			}

			return $args;
		}

		public function get_directory_cache_key( $prefix, $attributes, $state ) {
			$payload = array(
				'version' => $this->get_directory_cache_version(),
				'prefix' => $prefix,
				'attributes' => $attributes,
				'state' => $state,
				'archive' => is_post_type_archive( UCNature_Directory_Plugin::CPT ),
			);

			return 'ucn_dir_' . md5( wp_json_encode( $payload ) );
		}

		public function get_directory_cache_version() {
			return (int) get_option( UCNature_Directory_Plugin::CACHE_VERSION_OPTION, 1 );
		}

		public function bump_directory_cache_version() {
			update_option( UCNature_Directory_Plugin::CACHE_VERSION_OPTION, time(), false );
		}

		public function maybe_bump_directory_cache_version_for_term( $term_id, $tt_id = 0, $taxonomy = '', $deleted_term = null ) {
			if ( ! in_array( $taxonomy, array( UCNature_Directory_Plugin::TAX_CAMPUS, UCNature_Directory_Plugin::TAX_RESERVE, UCNature_Directory_Plugin::TAX_ROLE ), true ) ) {
				return;
			}

			$this->bump_directory_cache_version();
		}

		public function maybe_bump_directory_cache_version_for_post( $post_id ) {
			if ( UCNature_Directory_Plugin::CPT !== get_post_type( $post_id ) ) {
				return;
			}

			$this->bump_directory_cache_version();
		}
	}
}
