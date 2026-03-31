import { store } from '@wordpress/interactivity';

const getDirectoryRoot = ( element ) =>
	element?.closest( '[data-wp-interactive="ucnature-directory"]' ) || null;

const getArchiveBaseUrl = ( root, fallbackUrl = '' ) => {
	if ( ! root?.dataset?.wpContext ) {
		return fallbackUrl;
	}

	try {
		return JSON.parse( root.dataset.wpContext ).archiveUrl || fallbackUrl;
	} catch ( error ) {
		return fallbackUrl;
	}
};

const buildArchiveUrl = ( form, archiveUrl ) => {
	const url = new URL( archiveUrl || form.action || window.location.href, window.location.origin );
	const formData = new FormData( form );
	const params = new URLSearchParams();

	for ( const [ key, value ] of formData.entries() ) {
		if ( value ) {
			params.set( key, value.toString() );
		}
	}

	url.search = params.toString();

	return url;
};

const fetchResults = async ( root, archiveUrl ) => {
	if ( ! root ) {
		window.location.href = archiveUrl.toString();
		return;
	}

	const results = root.querySelector( '[data-ucn-directory-results]' );
	const status = root.querySelector( '#ucn-directory-status' );

	if ( ! results ) {
		window.location.href = archiveUrl.toString();
		return;
	}

	const partialUrl = new URL( archiveUrl.toString() );
	partialUrl.searchParams.set( 'ucn_partial', 'results' );

	root.setAttribute( 'aria-busy', 'true' );
	if ( status ) {
		status.textContent = 'Updating directory results.';
	}

	try {
		const response = await fetch( partialUrl.toString(), {
			headers: {
				'X-Requested-With': 'fetch',
			},
		} );

		if ( ! response.ok ) {
			throw new Error( `Directory request failed with ${ response.status }` );
		}

		results.outerHTML = await response.text();
		window.history.pushState( {}, '', archiveUrl.toString() );

		const nextResults = root.querySelector( '#ucn-directory-results' );

		if ( status ) {
			status.textContent = 'Directory results updated.';
		}

		if ( nextResults ) {
			nextResults.focus();
		}
	} catch ( error ) {
		window.location.href = archiveUrl.toString();
	} finally {
		root.removeAttribute( 'aria-busy' );
	}
};

store( 'ucnature-directory', {
	actions: {
		async submitFilters( event ) {
			event.preventDefault();

			const form = event.currentTarget;
			const root = getDirectoryRoot( form );
			const archiveUrl = buildArchiveUrl( form, getArchiveBaseUrl( root, form.action ) );

			await fetchResults( root, archiveUrl );
		},

		async handleFilterClick( event ) {
			const link = event.target.closest( 'a.ucn-directory__reset' );

			if ( ! link ) {
				return;
			}

			event.preventDefault();
			await fetchResults( getDirectoryRoot( link ), new URL( link.href, window.location.origin ) );
		},

		async handleResultsClick( event ) {
			const link = event.target.closest( 'a' );

			if ( ! link ) {
				return;
			}

			if ( ! link.closest( '.ucn-directory__pagination' ) ) {
				return;
			}

			event.preventDefault();
			await fetchResults( getDirectoryRoot( link ), new URL( link.href, window.location.origin ) );
		},
	},
} );
