<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	$job_title  = get_post_meta( $post_id, 'job_title', true );
	$email      = get_post_meta( $post_id, 'primary_email', true );
	$phone      = get_post_meta( $post_id, 'phone', true );
	$cell_phone = get_post_meta( $post_id, 'cell_phone', true );
	$phone_link = preg_replace( '/[^0-9+]/', '', (string) $phone );
	$cell_phone_link = preg_replace( '/[^0-9+]/', '', (string) $cell_phone );

	$street_1    = get_post_meta( $post_id, 'street_1', true );
	$street_2    = get_post_meta( $post_id, 'street_2', true );
	$city        = get_post_meta( $post_id, 'city', true );
	$state       = get_post_meta( $post_id, 'state', true );
	$postal_code = get_post_meta( $post_id, 'postal_code', true );
	$country     = get_post_meta( $post_id, 'country', true );

	$campuses = get_the_terms( $post_id, 'ucn_campus' );
	$reserves = get_the_terms( $post_id, 'ucn_reserve' );

	$city_state_postal = trim(
		implode(
			', ',
			array_filter(
				array(
					$city,
					$state,
					$postal_code,
				)
			)
		)
	);
	?>

	<main class="ucn-contact-single-wrap">
		<article class="ucn-contact-single">
			<h1 class="ucn-contact-single__title"><?php the_title(); ?></h1>

			<?php if ( $job_title ) : ?>
				<p class="ucn-contact-single__job-title">
					<strong><?php echo esc_html( $job_title ); ?></strong>
				</p>
			<?php endif; ?>

			<?php if ( $email ) : ?>
				<p class="ucn-contact-single__email">
					<strong>Email:</strong>
					<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>">
						<?php echo esc_html( antispambot( $email ) ); ?>
					</a>
				</p>
			<?php endif; ?>

			<?php if ( $phone ) : ?>
				<p class="ucn-contact-single__phone">
					<strong>Phone:</strong>
					<a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( $cell_phone ) : ?>
				<p class="ucn-contact-single__cell">
					<strong>Cell:</strong>
					<a href="tel:<?php echo esc_attr( $cell_phone_link ); ?>"><?php echo esc_html( $cell_phone ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( $campuses && ! is_wp_error( $campuses ) ) : ?>
				<p class="ucn-contact-single__campus">
					<strong>Campus:</strong>
					<?php echo esc_html( implode( ', ', wp_list_pluck( $campuses, 'name' ) ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $reserves && ! is_wp_error( $reserves ) ) : ?>
				<p class="ucn-contact-single__reserve">
					<strong>Reserve:</strong>
					<?php echo esc_html( implode( ', ', wp_list_pluck( $reserves, 'name' ) ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $street_1 || $street_2 || $city_state_postal || $country ) : ?>
				<div class="ucn-contact-single__address">
					<strong>Address:</strong>
					<address>
						<?php if ( $street_1 ) : ?>
							<?php echo esc_html( $street_1 ); ?><br>
						<?php endif; ?>

						<?php if ( $street_2 ) : ?>
							<?php echo esc_html( $street_2 ); ?><br>
						<?php endif; ?>

						<?php if ( $city_state_postal ) : ?>
							<?php echo esc_html( $city_state_postal ); ?><br>
						<?php endif; ?>

						<?php if ( $country ) : ?>
							<?php echo esc_html( $country ); ?>
						<?php endif; ?>
					</address>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="ucn-contact-single__content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>
		</article>
	</main>

<?php
endwhile;

get_footer();
