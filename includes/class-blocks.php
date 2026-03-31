<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UCNature_Directory_Blocks' ) ) {
	final class UCNature_Directory_Blocks {

		private $plugin;
		private $query;

		public function __construct( $plugin, $query ) {
			$this->plugin = $plugin;
			$this->query = $query;
		}

		public function register_block_bindings_sources() {
			if ( ! function_exists( 'register_block_bindings_source' ) ) {
				return;
			}

			register_block_bindings_source(
				'ucnature-directory/contact-field',
				array(
					'label' => __( 'UC Nature Directory Field', 'ucnature-directory' ),
					'uses_context' => array( 'postId', 'postType' ),
					'get_value_callback' => array( $this, 'get_bound_contact_field_value' ),
				)
			);
		}

		public function register_block_templates() {
			if ( ! $this->can_use_plugin_block_templates() ) {
				return;
			}

			$archive_content = $this->get_block_template_content( 'archive-ucn_contact.html' );
			if ( '' !== $archive_content ) {
				register_block_template(
					'ucnature-directory//archive-ucn_contact',
					array(
						'title' => __( 'Directory Contact Archive', 'ucnature-directory' ),
						'description' => __( 'Block template for the directory archive.', 'ucnature-directory' ),
						'content' => $archive_content,
						'post_types' => array( UCNature_Directory_Plugin::CPT ),
						'plugin' => 'ucnature-directory',
					)
				);
			}

			$content = $this->get_block_template_content( 'single-ucn_contact.html' );

			if ( '' === $content ) {
				return;
			}

			register_block_template(
				'ucnature-directory//single-ucn_contact',
				array(
					'title' => __( 'Single Directory Contact', 'ucnature-directory' ),
					'description' => __( 'Block template for single directory contacts.', 'ucnature-directory' ),
					'content' => $content,
					'post_types' => array( UCNature_Directory_Plugin::CPT ),
					'plugin' => 'ucnature-directory',
				)
			);
		}

		public function register_blocks() {
			register_block_type(
				'ucnature/directory',
				array(
					'api_version' => 3,
					'render_callback' => array( $this, 'render_directory_block' ),
					'attributes' => array(
						'showSearch' => array(
							'type' => 'boolean',
							'default' => true,
						),
						'showCampusFilter' => array(
							'type' => 'boolean',
							'default' => true,
						),
						'postsPerPage' => array(
							'type' => 'number',
							'default' => 24,
						),
					),
				)
			);

			register_block_type(
				'ucnature/directory-filters',
				array(
					'api_version' => 3,
					'title' => __( 'Directory Filters', 'ucnature-directory' ),
					'description' => __( 'Displays the directory search and filter controls.', 'ucnature-directory' ),
					'category' => 'widgets',
					'icon' => 'filter',
					'render_callback' => array( $this, 'render_directory_filters_block' ),
					'attributes' => array(
						'showSearch' => array(
							'type' => 'boolean',
							'default' => true,
						),
						'showCampusFilter' => array(
							'type' => 'boolean',
							'default' => true,
						),
						'showOrganize' => array(
							'type' => 'boolean',
							'default' => true,
						),
					),
				)
			);

			register_block_type(
				'ucnature/directory-results',
				array(
					'api_version' => 3,
					'title' => __( 'Directory Results', 'ucnature-directory' ),
					'description' => __( 'Displays the directory contact results.', 'ucnature-directory' ),
					'category' => 'widgets',
					'icon' => 'id',
					'render_callback' => array( $this, 'render_directory_results_block' ),
					'attributes' => array(
						'postsPerPage' => array(
							'type' => 'number',
							'default' => 24,
						),
					),
				)
			);

			register_block_type(
				'ucnature/contact-taxonomy-detail',
				array(
					'api_version' => 3,
					'title' => __( 'Contact Taxonomy Detail', 'ucnature-directory' ),
					'description' => __( 'Displays a contact taxonomy label and value only when terms exist.', 'ucnature-directory' ),
					'category' => 'widgets',
					'icon' => 'tag',
					'render_callback' => array( $this, 'render_contact_taxonomy_detail_block' ),
					'attributes' => array(
						'label' => array(
							'type' => 'string',
							'default' => '',
						),
						'taxonomy' => array(
							'type' => 'string',
							'default' => '',
						),
					),
					'uses_context' => array( 'postId', 'postType' ),
					'supports' => array(
						'inserter' => false,
						'html' => false,
					),
				)
			);

			register_block_type(
				'ucnature/contact-meta-detail',
				array(
					'api_version' => 3,
					'title' => __( 'Contact Meta Detail', 'ucnature-directory' ),
					'description' => __( 'Displays a contact meta label and value only when the field has content.', 'ucnature-directory' ),
					'category' => 'widgets',
					'icon' => 'admin-users',
					'render_callback' => array( $this, 'render_contact_meta_detail_block' ),
					'attributes' => array(
						'label' => array(
							'type' => 'string',
							'default' => '',
						),
						'metaKey' => array(
							'type' => 'string',
							'default' => '',
						),
					),
					'uses_context' => array( 'postId', 'postType' ),
					'supports' => array(
						'inserter' => false,
						'html' => false,
					),
				)
			);
		}

		public function render_contact_taxonomy_detail_block( $attributes, $content, $block ) {
			$post_id = $this->get_block_post_id( $block );

			if ( ! $post_id ) {
				return '';
			}

			$taxonomy = isset( $attributes['taxonomy'] ) ? sanitize_key( $attributes['taxonomy'] ) : '';

			if ( '' === $taxonomy ) {
				return '';
			}

			$value = $this->get_term_list_string( $post_id, $taxonomy );

			if ( '' === $value ) {
				return '';
			}

			$label = isset( $attributes['label'] ) ? trim( (string) $attributes['label'] ) : '';

			if ( '' !== $label ) {
				return sprintf(
					'<p class="ucn-contact-taxonomy-detail"><strong>%s:</strong> %s</p>',
					esc_html( $label ),
					esc_html( $value )
				);
			}

			return sprintf( '<p class="ucn-contact-taxonomy-detail">%s</p>', esc_html( $value ) );
		}

		public function render_contact_meta_detail_block( $attributes, $content, $block ) {
			$post_id = $this->get_block_post_id( $block );

			if ( ! $post_id ) {
				return '';
			}

			$meta_key = isset( $attributes['metaKey'] ) ? sanitize_key( $attributes['metaKey'] ) : '';

			if ( '' === $meta_key || ! in_array( $meta_key, $this->get_allowed_public_meta_keys(), true ) ) {
				return '';
			}

			$value = trim( (string) get_post_meta( $post_id, $meta_key, true ) );

			if ( '' === $value ) {
				return '';
			}

			$label = isset( $attributes['label'] ) ? trim( (string) $attributes['label'] ) : '';

			if ( '' !== $label ) {
				return sprintf(
					'<p class="ucn-contact-meta-detail"><strong>%s:</strong> %s</p>',
					esc_html( $label ),
					esc_html( $value )
				);
			}

			return sprintf( '<p class="ucn-contact-meta-detail">%s</p>', esc_html( $value ) );
		}

		public function render_directory_block( $attributes ) {
			$filters = $this->render_directory_filters_block(
				array(
					'showSearch' => $attributes['showSearch'] ?? true,
					'showCampusFilter' => $attributes['showCampusFilter'] ?? true,
					'showOrganize' => true,
				)
			);
			$results = $this->render_directory_results_block(
				array(
					'postsPerPage' => $attributes['postsPerPage'] ?? 24,
				)
			);

			$wrapper_attributes = 'class="ucn-directory"';

			if ( $this->can_use_interactivity_api() && is_post_type_archive( UCNature_Directory_Plugin::CPT ) ) {
				$wrapper_attributes .= sprintf(
					' data-wp-interactive="ucnature-directory" data-wp-context="%s"',
					esc_attr(
						wp_json_encode(
							array(
								'archiveUrl' => get_post_type_archive_link( UCNature_Directory_Plugin::CPT ),
							)
						)
					)
				);
			}

			return sprintf( '<div %s>%s%s</div>', $wrapper_attributes, $filters, $results );
		}

		public function render_directory_filters_block( $attributes ) {
			if ( empty( $attributes['showSearch'] ) && empty( $attributes['showCampusFilter'] ) && empty( $attributes['showOrganize'] ) ) {
				return '';
			}

			$state = $this->query->get_directory_request_state();
			$cache_key = $this->query->get_directory_cache_key( 'filters', $attributes, $state );
			$cached = get_transient( $cache_key );

			if ( false !== $cached ) {
				return $cached;
			}

			$campus_terms = get_terms(
				array(
					'taxonomy' => UCNature_Directory_Plugin::TAX_CAMPUS,
					'hide_empty' => false,
				)
			);

			if ( is_wp_error( $campus_terms ) || ! is_array( $campus_terms ) ) {
				$campus_terms = array();
			}

			ob_start();
			?>
			<form
				class="ucn-directory__filters"
				method="get"
				action="<?php echo esc_url( get_post_type_archive_link( UCNature_Directory_Plugin::CPT ) ); ?>"
				aria-controls="ucn-directory-results"
				<?php echo $this->can_use_interactivity_api() && is_post_type_archive( UCNature_Directory_Plugin::CPT ) ? 'data-wp-on--submit="actions.submitFilters" data-wp-on--click="actions.handleFilterClick"' : ''; ?>
			>
				<?php if ( ! empty( $attributes['showSearch'] ) ) : ?>
					<div class="ucn-directory__field">
						<label for="ucn_search"><?php esc_html_e( 'Search by name', 'ucnature-directory' ); ?></label>
						<input id="ucn_search" name="ucn_search" type="search" value="<?php echo esc_attr( $state['search'] ); ?>" />
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $attributes['showCampusFilter'] ) ) : ?>
					<div class="ucn-directory__field">
						<label for="ucn_campus"><?php esc_html_e( 'Campus', 'ucnature-directory' ); ?></label>
						<select id="ucn_campus" name="ucn_campus">
							<option value=""><?php esc_html_e( 'All campuses', 'ucnature-directory' ); ?></option>
							<?php foreach ( $campus_terms as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $state['campus'], $term->slug ); ?>>
									<?php echo esc_html( $term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $attributes['showOrganize'] ) ) : ?>
					<div class="ucn-directory__field">
						<label for="ucn_organize"><?php esc_html_e( 'Organize by', 'ucnature-directory' ); ?></label>
						<select id="ucn_organize" name="ucn_organize">
							<option value="lastname" <?php selected( $state['organize'], 'lastname' ); ?>><?php esc_html_e( 'Last name', 'ucnature-directory' ); ?></option>
							<option value="campus" <?php selected( $state['organize'], 'campus' ); ?>><?php esc_html_e( 'Campus', 'ucnature-directory' ); ?></option>
							<option value="reserve" <?php selected( $state['organize'], 'reserve' ); ?>><?php esc_html_e( 'Reserve', 'ucnature-directory' ); ?></option>
						</select>
					</div>
				<?php endif; ?>

				<div class="ucn-directory__actions">
					<button type="submit"><?php esc_html_e( 'Filter', 'ucnature-directory' ); ?></button>
					<a class="ucn-directory__reset" href="<?php echo esc_url( get_post_type_archive_link( UCNature_Directory_Plugin::CPT ) ); ?>"><?php esc_html_e( 'Clear', 'ucnature-directory' ); ?></a>
				</div>
			</form>
			<p id="ucn-directory-status" class="screen-reader-text" aria-live="polite" aria-atomic="true"></p>
			<?php

			$output = ob_get_clean();
			set_transient( $cache_key, $output, HOUR_IN_SECONDS );

			return $output;
		}

		public function render_directory_results_block( $attributes ) {
			$state = $this->query->get_directory_request_state();
			$cache_key = $this->query->get_directory_cache_key( 'results', $attributes, $state );
			$cached = get_transient( $cache_key );

			if ( false !== $cached ) {
				return $cached;
			}

			$query = $this->query->get_directory_results_query( $attributes, $state );

			ob_start();

			$results_wrapper_attributes = 'id="ucn-directory-results" class="ucn-directory__results" data-ucn-directory-results tabindex="-1"';

			if ( $this->can_use_interactivity_api() && is_post_type_archive( UCNature_Directory_Plugin::CPT ) ) {
				$results_wrapper_attributes .= ' data-wp-on--click="actions.handleResultsClick"';
			}

			echo '<div ' . $results_wrapper_attributes . '>';

			if ( $query->have_posts() ) {
				if ( 'lastname' === $state['organize'] ) {
					?>
					<div class="ucn-directory__grid" role="list">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$this->render_directory_contact_card( get_the_ID() );
						endwhile;
						?>
					</div>

					<div class="ucn-directory__pagination">
						<?php
						$pagination = paginate_links(
							array(
								'total' => $query->max_num_pages,
								'current' => $state['paged'],
							)
						);

						if ( is_string( $pagination ) && '' !== $pagination ) {
							echo wp_kses_post( $pagination );
						}
						?>
					</div>
					<?php
				} else {
					$this->render_grouped_directory_results( $query->posts, $state['organize'] );
				}
			} else {
				echo '<p>' . esc_html__( 'No contacts found.', 'ucnature-directory' ) . '</p>';
			}

			echo '</div>';

			wp_reset_postdata();

			$output = ob_get_clean();
			set_transient( $cache_key, $output, HOUR_IN_SECONDS );

			return $output;
		}

		public function maybe_render_directory_results_partial() {
			if ( is_admin() || ! is_post_type_archive( UCNature_Directory_Plugin::CPT ) ) {
				return;
			}

			if ( 'results' !== get_query_var( 'ucn_partial' ) ) {
				return;
			}

			nocache_headers();
			echo $this->render_directory_results_block(
				array(
					'postsPerPage' => 24,
				)
			);
			exit;
		}

		public function can_use_plugin_block_templates() {
			return function_exists( 'register_block_template' ) && function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
		}

		public function can_use_interactivity_api() {
			return function_exists( 'wp_register_script_module' ) && function_exists( 'wp_enqueue_script_module' );
		}

		public function get_bound_contact_field_value( $source_args, $block_instance, $attribute_name ) {
			$post_id = 0;

			if ( isset( $block_instance->context['postId'] ) ) {
				$post_id = (int) $block_instance->context['postId'];
			}

			if ( ! $post_id ) {
				$post_id = get_the_ID();
			}

			if ( ! $post_id || UCNature_Directory_Plugin::CPT !== get_post_type( $post_id ) ) {
				return '';
			}

			$field = ( is_array( $source_args ) && isset( $source_args['key'] ) ) ? sanitize_key( $source_args['key'] ) : '';

			if ( '' === $field ) {
				return '';
			}

			switch ( $field ) {
				case 'display_name':
					return get_the_title( $post_id );
				case 'primary_email_obfuscated':
					$email = (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_PRIMARY_EMAIL, true );
					return $email ? antispambot( $email ) : '';
				case 'primary_email_mailto':
					$email = (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_PRIMARY_EMAIL, true );
					return $email ? 'mailto:' . antispambot( $email ) : '';
				case 'phone_link':
					$phone = preg_replace( '/[^0-9+]/', '', (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_PHONE, true ) );
					return $phone ? 'tel:' . $phone : '';
				case 'cell_phone_link':
					$cell_phone = preg_replace( '/[^0-9+]/', '', (string) get_post_meta( $post_id, UCNature_Directory_Plugin::META_CELL_PHONE, true ) );
					return $cell_phone ? 'tel:' . $cell_phone : '';
				case 'campus_list':
					return $this->get_term_list_string( $post_id, UCNature_Directory_Plugin::TAX_CAMPUS );
				case 'reserve_list':
					return $this->get_term_list_string( $post_id, UCNature_Directory_Plugin::TAX_RESERVE );
				case 'address_string':
					return $this->get_address_string( $post_id );
				default:
					if ( ! in_array( $field, $this->get_allowed_public_meta_keys(), true ) ) {
						return '';
					}

					return (string) get_post_meta( $post_id, $field, true );
			}
		}

		private function render_grouped_directory_results( $posts, $organize ) {
			$taxonomy = 'campus' === $organize ? UCNature_Directory_Plugin::TAX_CAMPUS : UCNature_Directory_Plugin::TAX_RESERVE;
			$groups = array();
			$post_ids = wp_list_pluck( $posts, 'ID' );

			if ( ! empty( $post_ids ) ) {
				update_meta_cache( 'post', $post_ids );
				update_object_term_cache( $post_ids, UCNature_Directory_Plugin::CPT );
			}

			foreach ( $posts as $post ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					$groups[ __( 'Unassigned', 'ucnature-directory' ) ][] = $post->ID;
					continue;
				}

				foreach ( $terms as $term ) {
					$groups[ $term->name ][] = $post->ID;
				}
			}

			if ( empty( $groups ) ) {
				echo '<p>' . esc_html__( 'No contacts found.', 'ucnature-directory' ) . '</p>';
				return;
			}

			ksort( $groups, SORT_NATURAL | SORT_FLAG_CASE );

			foreach ( $groups as $group_label => $group_post_ids ) {
				echo '<section class="ucn-directory-group">';
				echo '<h2 class="ucn-directory-group__title">' . esc_html( $group_label ) . '</h2>';
				echo '<div class="ucn-directory__grid">';

				foreach ( $group_post_ids as $post_id ) {
					$this->render_directory_contact_card( $post_id );
				}

				echo '</div>';
				echo '</section>';
			}
		}

		private function render_directory_contact_card( $post_id ) {
			$job_title = get_post_meta( $post_id, UCNature_Directory_Plugin::META_JOB_TITLE, true );
			$email = get_post_meta( $post_id, UCNature_Directory_Plugin::META_PRIMARY_EMAIL, true );
			$phone = get_post_meta( $post_id, UCNature_Directory_Plugin::META_PHONE, true );
			$phone_link = preg_replace( '/[^0-9+]/', '', (string) $phone );
			$campus_list = $this->get_term_list_string( $post_id, UCNature_Directory_Plugin::TAX_CAMPUS );
			?>
			<article class="ucn-directory-card" role="listitem">
				<h3 class="ucn-directory-card__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
				<?php if ( $job_title ) : ?>
					<p class="ucn-directory-card__job-title"><?php echo esc_html( $job_title ); ?></p>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<p><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<p><a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a></p>
				<?php endif; ?>
				<?php if ( $campus_list ) : ?>
					<p><?php echo esc_html( $campus_list ); ?></p>
				<?php endif; ?>
			</article>
			<?php
		}

		private function get_block_template_content( $template_file ) {
			$path = UCNATURE_DIRECTORY_DIR . 'templates/block-templates/' . $template_file;

			if ( ! file_exists( $path ) ) {
				return '';
			}

			$content = file_get_contents( $path );

			return false === $content ? '' : (string) $content;
		}

		private function get_block_post_id( $block ) {
			$post_id = 0;

			if ( isset( $block->context['postId'] ) ) {
				$post_id = (int) $block->context['postId'];
			}

			if ( ! $post_id ) {
				$post_id = get_the_ID();
			}

			if ( ! $post_id || UCNature_Directory_Plugin::CPT !== get_post_type( $post_id ) ) {
				return 0;
			}

			return $post_id;
		}

		private function get_term_list_string( $post_id, $taxonomy ) {
			$terms = get_the_terms( $post_id, $taxonomy );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return '';
			}

			return implode( ', ', wp_list_pluck( $terms, 'name' ) );
		}

		private function get_allowed_public_meta_keys() {
			$allowed_keys = array(
				UCNature_Directory_Plugin::META_FIRST_NAME,
				UCNature_Directory_Plugin::META_MIDDLE_NAME,
				UCNature_Directory_Plugin::META_LAST_NAME,
				UCNature_Directory_Plugin::META_SUFFIX,
				UCNature_Directory_Plugin::META_PREFERRED_DISPLAY_NAME,
				UCNature_Directory_Plugin::META_PRIMARY_EMAIL,
				UCNature_Directory_Plugin::META_SECONDARY_EMAIL,
				UCNature_Directory_Plugin::META_PHONE,
				UCNature_Directory_Plugin::META_CELL_PHONE,
				UCNature_Directory_Plugin::META_JOB_TITLE,
				UCNature_Directory_Plugin::META_STREET_1,
				UCNature_Directory_Plugin::META_STREET_2,
				UCNature_Directory_Plugin::META_CITY,
				UCNature_Directory_Plugin::META_STATE,
				UCNature_Directory_Plugin::META_POSTAL_CODE,
				UCNature_Directory_Plugin::META_COUNTRY,
			);

			return apply_filters( 'ucnature_directory_allowed_public_meta_keys', $allowed_keys );
		}

		private function get_address_string( $post_id ) {
			$parts = array_filter(
				array(
					get_post_meta( $post_id, UCNature_Directory_Plugin::META_STREET_1, true ),
					get_post_meta( $post_id, UCNature_Directory_Plugin::META_STREET_2, true ),
					trim(
						implode(
							', ',
							array_filter(
								array(
									get_post_meta( $post_id, UCNature_Directory_Plugin::META_CITY, true ),
									get_post_meta( $post_id, UCNature_Directory_Plugin::META_STATE, true ),
									get_post_meta( $post_id, UCNature_Directory_Plugin::META_POSTAL_CODE, true ),
								)
							)
						)
					),
					get_post_meta( $post_id, UCNature_Directory_Plugin::META_COUNTRY, true ),
				)
			);

			return implode( ' | ', $parts );
		}
	}
}
