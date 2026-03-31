<?php if ( $street_1 || $street_2 || $city_state_postal || $country ) : ?>
	<p class="ucn-directory-card__address">
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
	</p>
<?php endif; ?>