<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="wp-block-group alignwide" style="padding:2rem 1rem;">
	<header class="wp-block-group" style="margin-bottom:2rem;">
		<h1><?php post_type_archive_title(); ?></h1>
		<p>UC Nature staff directory</p>
	</header>

	<?php
	echo do_blocks(
		'<!-- wp:ucnature/directory {"showSearch":true,"showCampusFilter":true,"postsPerPage":24} /-->'
	);
	?>
</main>

<?php
get_footer();