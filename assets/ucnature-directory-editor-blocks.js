( function ( wp ) {
	const { __, sprintf } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const {
		PanelBody,
		ToggleControl,
		TextControl,
		SelectControl,
		RangeControl,
		Placeholder,
	} = wp.components;

	const taxonomyOptions = [
		{ label: __( 'Choose taxonomy', 'ucnature-directory' ), value: '' },
		{ label: __( 'Campus', 'ucnature-directory' ), value: 'ucn_campus' },
		{ label: __( 'Reserve', 'ucnature-directory' ), value: 'ucn_reserve' },
		{ label: __( 'General role', 'ucnature-directory' ), value: 'ucn_general_role' },
	];

	const metaFieldOptions = [
		{ label: __( 'Choose field', 'ucnature-directory' ), value: '' },
		{ label: __( 'Job title', 'ucnature-directory' ), value: 'job_title' },
		{ label: __( 'Primary email', 'ucnature-directory' ), value: 'primary_email' },
		{ label: __( 'Secondary email', 'ucnature-directory' ), value: 'secondary_email' },
		{ label: __( 'Phone', 'ucnature-directory' ), value: 'phone' },
		{ label: __( 'Cell phone', 'ucnature-directory' ), value: 'cell_phone' },
		{ label: __( 'Street address 1', 'ucnature-directory' ), value: 'street_1' },
		{ label: __( 'Street address 2', 'ucnature-directory' ), value: 'street_2' },
		{ label: __( 'City', 'ucnature-directory' ), value: 'city' },
		{ label: __( 'State', 'ucnature-directory' ), value: 'state' },
		{ label: __( 'Postal code', 'ucnature-directory' ), value: 'postal_code' },
		{ label: __( 'Country', 'ucnature-directory' ), value: 'country' },
	];

	function renderPreviewList( items ) {
		return el(
			'ul',
			{ className: 'ucn-directory-placeholder__list' },
			items.map( function ( item ) {
				return el( 'li', { key: item }, item );
			} )
		);
	}

	function getOptionLabel( options, value ) {
		const option = options.find( function ( item ) {
			return item.value === value;
		} );

		return option ? option.label : '';
	}

	function maybeSyncLabel( currentLabel, previousValue, nextValue, options ) {
		const previousLabel = getOptionLabel( options, previousValue );
		const nextLabel = getOptionLabel( options, nextValue );

		if ( ! nextLabel || nextValue === '' ) {
			return currentLabel;
		}

		if ( ! currentLabel || currentLabel === previousLabel ) {
			return nextLabel;
		}

		return currentLabel;
	}

	registerBlockType( 'ucnature/directory', {
		apiVersion: 3,
		title: __( 'Directory Contacts', 'ucnature-directory' ),
		description: __( 'Displays the directory contact archive.', 'ucnature-directory' ),
		category: 'widgets',
		icon: 'groups',
		attributes: {
			showSearch: {
				type: 'boolean',
				default: true,
			},
			showCampusFilter: {
				type: 'boolean',
				default: true,
			},
			postsPerPage: {
				type: 'number',
				default: 24,
			},
		},
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps( { className: 'ucn-directory-placeholder' } );
			const summary = [];

			if ( attributes.showSearch ) {
				summary.push( __( 'Keyword search enabled', 'ucnature-directory' ) );
			}

			if ( attributes.showCampusFilter ) {
				summary.push( __( 'Campus filter enabled', 'ucnature-directory' ) );
			}

			summary.push(
				sprintf(
					__( '%d contacts per page', 'ucnature-directory' ),
					attributes.postsPerPage || 24
				)
			);

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Directory settings', 'ucnature-directory' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Show keyword search', 'ucnature-directory' ),
							checked: !! attributes.showSearch,
							onChange: function ( value ) {
								setAttributes( { showSearch: value } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show campus filter', 'ucnature-directory' ),
							checked: !! attributes.showCampusFilter,
							onChange: function ( value ) {
								setAttributes( { showCampusFilter: value } );
							},
						} ),
						el( RangeControl, {
							label: __( 'Contacts per page', 'ucnature-directory' ),
							value: attributes.postsPerPage || 24,
							onChange: function ( value ) {
								setAttributes( { postsPerPage: value || 24 } );
							},
							min: 1,
							max: 100,
						} )
					)
				),
				el(
					Placeholder,
					blockProps,
					el( 'strong', null, __( 'Directory archive preview', 'ucnature-directory' ) ),
					el( 'p', null, __( 'Visitors will see the live directory search and results here.', 'ucnature-directory' ) ),
					renderPreviewList( summary )
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	registerBlockType( 'ucnature/directory-filters', {
		apiVersion: 3,
		title: __( 'Directory Filters', 'ucnature-directory' ),
		description: __( 'Displays the directory search and filter controls.', 'ucnature-directory' ),
		category: 'widgets',
		icon: 'filter',
		attributes: {
			showSearch: {
				type: 'boolean',
				default: true,
			},
			showCampusFilter: {
				type: 'boolean',
				default: true,
			},
			showOrganize: {
				type: 'boolean',
				default: true,
			},
		},
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps( { className: 'ucn-directory-placeholder' } );
			const items = [];

			if ( attributes.showSearch ) {
				items.push( __( 'Search field', 'ucnature-directory' ) );
			}

			if ( attributes.showCampusFilter ) {
				items.push( __( 'Campus dropdown', 'ucnature-directory' ) );
			}

			if ( attributes.showOrganize ) {
				items.push( __( 'Organize-by dropdown', 'ucnature-directory' ) );
			}

			if ( ! items.length ) {
				items.push( __( 'All controls are currently hidden.', 'ucnature-directory' ) );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Filter settings', 'ucnature-directory' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Show search field', 'ucnature-directory' ),
							checked: !! attributes.showSearch,
							onChange: function ( value ) {
								setAttributes( { showSearch: value } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show campus filter', 'ucnature-directory' ),
							checked: !! attributes.showCampusFilter,
							onChange: function ( value ) {
								setAttributes( { showCampusFilter: value } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show organize control', 'ucnature-directory' ),
							checked: !! attributes.showOrganize,
							onChange: function ( value ) {
								setAttributes( { showOrganize: value } );
							},
						} )
					)
				),
				el(
					Placeholder,
					blockProps,
					el( 'strong', null, __( 'Directory filters preview', 'ucnature-directory' ) ),
					renderPreviewList( items )
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	registerBlockType( 'ucnature/directory-results', {
		apiVersion: 3,
		title: __( 'Directory Results', 'ucnature-directory' ),
		description: __( 'Displays the directory contact results.', 'ucnature-directory' ),
		category: 'widgets',
		icon: 'id',
		attributes: {
			postsPerPage: {
				type: 'number',
				default: 24,
			},
		},
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps( { className: 'ucn-directory-placeholder' } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Results settings', 'ucnature-directory' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Contacts per page', 'ucnature-directory' ),
							value: attributes.postsPerPage || 24,
							onChange: function ( value ) {
								setAttributes( { postsPerPage: value || 24 } );
							},
							min: 1,
							max: 100,
						} )
					)
				),
				el(
					Placeholder,
					blockProps,
					el( 'strong', null, __( 'Directory results preview', 'ucnature-directory' ) ),
					el(
						'p',
						null,
						sprintf(
							__( 'The live directory results will appear here, showing up to %d contacts per page.', 'ucnature-directory' ),
							attributes.postsPerPage || 24
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	registerBlockType( 'ucnature/contact-taxonomy-detail', {
		apiVersion: 3,
		title: __( 'Contact Taxonomy Detail', 'ucnature-directory' ),
		description: __( 'Displays a contact taxonomy label and value only when terms exist.', 'ucnature-directory' ),
		category: 'widgets',
		icon: 'tag',
		attributes: {
			label: {
				type: 'string',
				default: '',
			},
			taxonomy: {
				type: 'string',
				default: '',
			},
		},
		supports: {
			inserter: false,
			html: false,
		},
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const label = attributes.label || __( 'Taxonomy Detail', 'ucnature-directory' );
			const taxonomyLabel =
				taxonomyOptions.find( function ( option ) {
					return option.value === attributes.taxonomy;
				} ) || taxonomyOptions[0];

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Detail settings', 'ucnature-directory' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Label', 'ucnature-directory' ),
							value: attributes.label || '',
							onChange: function ( value ) {
								setAttributes( { label: value } );
							},
							help: __( 'Optional text shown before the taxonomy value.', 'ucnature-directory' ),
						} ),
						el( SelectControl, {
							label: __( 'Taxonomy', 'ucnature-directory' ),
							value: attributes.taxonomy || '',
							options: taxonomyOptions,
							onChange: function ( value ) {
								setAttributes( {
									taxonomy: value,
									label: maybeSyncLabel( attributes.label, attributes.taxonomy, value, taxonomyOptions ),
								} );
							},
						} )
					)
				),
				el(
					'p',
					{ className: 'ucn-contact-taxonomy-detail-placeholder' },
					el( 'strong', null, label + ':' ),
					' ',
					taxonomyLabel.label
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	registerBlockType( 'ucnature/contact-meta-detail', {
		apiVersion: 3,
		title: __( 'Contact Meta Detail', 'ucnature-directory' ),
		description: __( 'Displays a contact meta label and value only when the field has content.', 'ucnature-directory' ),
		category: 'widgets',
		icon: 'admin-users',
		attributes: {
			label: {
				type: 'string',
				default: '',
			},
			metaKey: {
				type: 'string',
				default: '',
			},
		},
		supports: {
			inserter: false,
			html: false,
		},
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const label = attributes.label || __( 'Meta Detail', 'ucnature-directory' );
			const metaLabel =
				metaFieldOptions.find( function ( option ) {
					return option.value === attributes.metaKey;
				} ) || metaFieldOptions[0];

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Detail settings', 'ucnature-directory' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Label', 'ucnature-directory' ),
							value: attributes.label || '',
							onChange: function ( value ) {
								setAttributes( { label: value } );
							},
							help: __( 'Optional text shown before the field value.', 'ucnature-directory' ),
						} ),
						el( SelectControl, {
							label: __( 'Field', 'ucnature-directory' ),
							value: attributes.metaKey || '',
							options: metaFieldOptions,
							onChange: function ( value ) {
								setAttributes( {
									metaKey: value,
									label: maybeSyncLabel( attributes.label, attributes.metaKey, value, metaFieldOptions ),
								} );
							},
						} )
					)
				),
				el(
					'p',
					{ className: 'ucn-contact-meta-detail-placeholder' },
					el( 'strong', null, label + ':' ),
					' ',
					metaLabel.label
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
