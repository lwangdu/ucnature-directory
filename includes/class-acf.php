<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UCNature_Directory_ACF' ) ) {
	final class UCNature_Directory_ACF {

		private $plugin;

		public function __construct( $plugin ) {
			$this->plugin = $plugin;
		}

		public function register_acf_fields() {
			if ( ! function_exists( 'acf_add_local_field_group' ) ) {
				return;
			}

			acf_add_local_field_group(
				array(
					'key' => 'group_ucn_directory_contact',
					'title' => 'Directory Contact Fields',
					'fields' => array(
						array(
							'key' => 'field_ucn_identity_tab',
							'label' => 'Name',
							'type' => 'tab',
							'instructions' => 'Enter the contact name as it should appear in the directory.',
						),
						$this->text_field( UCNature_Directory_Plugin::META_FIRST_NAME, 'First name', 20, 50, 'Shown in the public directory and used to build the title automatically.', 'Jane' ),
						$this->text_field( UCNature_Directory_Plugin::META_MIDDLE_NAME, 'Middle name', 20, 50, 'Optional.', '' ),
						$this->text_field( UCNature_Directory_Plugin::META_LAST_NAME, 'Last name', 20, 50, 'Shown in the public directory and used for sorting.', 'Doe' ),
						$this->text_field( UCNature_Directory_Plugin::META_SUFFIX, 'Suffix', 20, 20, 'Optional suffix such as Jr., Sr., or Ph.D.', 'Ph.D.' ),
						$this->text_field( UCNature_Directory_Plugin::META_PREFERRED_DISPLAY_NAME, 'Preferred display name', 20, 100, 'Optional. Use this when the public display name should differ from the first/middle/last name fields.', 'Dr. Jane Doe' ),
						array(
							'key' => 'field_ucn_contact_tab',
							'label' => 'Contact',
							'type' => 'tab',
							'instructions' => 'Add the public contact details visitors should use.',
						),
						$this->email_field( UCNature_Directory_Plugin::META_PRIMARY_EMAIL, 'Primary email', 'Main public email shown on the directory card and profile page.', 'jane.doe@example.org' ),
						$this->email_field( UCNature_Directory_Plugin::META_SECONDARY_EMAIL, 'Secondary email', 'Optional backup email address.', 'team@example.org' ),
						$this->text_field( UCNature_Directory_Plugin::META_PHONE, 'Phone', 50, 50, 'Use the public-facing phone number. It will be turned into a tap-to-call link.', '(555) 123-4567' ),
						$this->text_field( UCNature_Directory_Plugin::META_CELL_PHONE, 'Cell phone', 50, 50, 'Optional mobile number.', '(555) 234-5678' ),
						array(
							'key' => 'field_ucn_position_tab',
							'label' => 'Position',
							'type' => 'tab',
							'instructions' => 'Use this section for job title and visibility settings.',
						),
						$this->text_field( UCNature_Directory_Plugin::META_JOB_TITLE, 'Job title', 40, 100, 'Short public title shown below the name.', 'Program Director' ),
						array(
							'key' => 'field_directory_visibility',
							'label' => 'Directory visibility',
							'name' => UCNature_Directory_Plugin::META_DIRECTORY_VISIBILITY,
							'type' => 'true_false',
							'default_value' => 1,
							'ui' => 1,
							'message' => 'Show this contact in the public directory',
							'instructions' => 'Turn this off to keep the entry saved in WordPress without showing it on the public directory page.',
						),
						array(
							'key' => 'field_ucn_address_tab',
							'label' => 'Address',
							'type' => 'tab',
							'instructions' => 'Optional mailing or office address shown on the single contact page.',
						),
						$this->text_field( UCNature_Directory_Plugin::META_STREET_1, 'Street address 1', 60, 120, 'First line of the address.', '123 Forest Lane' ),
						$this->text_field( UCNature_Directory_Plugin::META_STREET_2, 'Street address 2', 60, 120, 'Optional second line such as suite, building, or mail stop.', 'Suite 200' ),
						$this->text_field( UCNature_Directory_Plugin::META_CITY, 'City', 30, 60, '', 'Berkeley' ),
						$this->text_field( UCNature_Directory_Plugin::META_STATE, 'State', 30, 40, '', 'CA' ),
						$this->text_field( UCNature_Directory_Plugin::META_POSTAL_CODE, 'Postal code', 30, 20, '', '94720' ),
						$this->text_field( UCNature_Directory_Plugin::META_COUNTRY, 'Country', 30, 60, '', 'USA' ),
						array(
							'key' => 'field_ucn_admin_tab',
							'label' => 'Admin',
							'type' => 'tab',
							'instructions' => 'Reference fields used internally to keep the directory organized.',
						),
						array(
							'key' => 'field_sort_name',
							'label' => 'Sort name',
							'name' => UCNature_Directory_Plugin::META_SORT_NAME,
							'type' => 'text',
							'readonly' => 1,
							'instructions' => 'Generated automatically on save.',
						),
					),
					'location' => array(
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => UCNature_Directory_Plugin::CPT,
							),
						),
					),
					'position' => 'acf_after_title',
					'style' => 'default',
					'active' => true,
				)
			);
		}

		public function disable_block_editor_for_contacts( $use_block_editor, $post_type ) {
			if ( UCNature_Directory_Plugin::CPT === $post_type ) {
				return false;
			}

			return $use_block_editor;
		}

		public function output_contact_editor_styles() {
			if ( ! function_exists( 'get_current_screen' ) ) {
				return;
			}

			$screen = get_current_screen();

			if ( ! $screen || UCNature_Directory_Plugin::CPT !== $screen->post_type ) {
				return;
			}
			?>
			<style>
				#acf-group_ucn_directory_contact .acf-field[data-type="text"],
				#acf-group_ucn_directory_contact .acf-field[data-type="email"],
				#acf-group_ucn_directory_contact .acf-field[data-type="true_false"] {
					display: grid;
					grid-template-areas:
						"label"
						"input"
						"desc";
					align-content: start;
				}

				#acf-group_ucn_directory_contact .acf-field[data-type="text"] > .acf-label,
				#acf-group_ucn_directory_contact .acf-field[data-type="email"] > .acf-label,
				#acf-group_ucn_directory_contact .acf-field[data-type="true_false"] > .acf-label {
					display: contents;
				}

				#acf-group_ucn_directory_contact .acf-field[data-type="text"] > .acf-label > label,
				#acf-group_ucn_directory_contact .acf-field[data-type="email"] > .acf-label > label,
				#acf-group_ucn_directory_contact .acf-field[data-type="true_false"] > .acf-label > label {
					grid-area: label;
					margin: 0 0 0.35rem;
				}

				#acf-group_ucn_directory_contact .acf-field[data-type="text"] > .acf-input,
				#acf-group_ucn_directory_contact .acf-field[data-type="email"] > .acf-input,
				#acf-group_ucn_directory_contact .acf-field[data-type="true_false"] > .acf-input {
					grid-area: input;
				}

				#acf-group_ucn_directory_contact .acf-field[data-type="text"] > .acf-label .description,
				#acf-group_ucn_directory_contact .acf-field[data-type="email"] > .acf-label .description,
				#acf-group_ucn_directory_contact .acf-field[data-type="true_false"] > .acf-label .description {
					grid-area: desc;
					margin: 0.45rem 0 0;
					color: #64748b;
					line-height: 1.45;
				}
			</style>
			<?php
		}

		private function text_field( $name, $label, $wrapper_width = 50, $maxlength = 100, $instructions = '', $placeholder = '' ) {
			return array(
				'key' => 'field_' . $name,
				'label' => $label,
				'name' => $name,
				'type' => 'text',
				'wrapper' => array(
					'width' => $wrapper_width,
				),
				'maxlength' => $maxlength,
				'instructions' => $instructions,
				'placeholder' => $placeholder,
			);
		}

		private function email_field( $name, $label, $instructions = '', $placeholder = '' ) {
			return array(
				'key' => 'field_' . $name,
				'label' => $label,
				'name' => $name,
				'type' => 'email',
				'wrapper' => array(
					'width' => 50,
				),
				'instructions' => $instructions,
				'placeholder' => $placeholder,
			);
		}
	}
}
