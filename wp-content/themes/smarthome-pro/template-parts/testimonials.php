<?php
/**
 * Template Part: Testimonials Section
 *
 * Swiper-powered testimonial carousel with star ratings,
 * loaded from ACF repeater.
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_testimonials_badge       = get_field( 'testimonials_badge' );
$shp_testimonials_heading     = get_field( 'testimonials_heading' );
$shp_testimonials_description = get_field( 'testimonials_description' );
?>

<section id="testimonials" class="shp-testimonials">
	<div class="container">

		<!-- Section Header -->
		<div class="shp-section-header text-center mb-5">

			<?php if ( ! empty( $shp_testimonials_badge ) ) : ?>
				<span class="shp-section-badge">
					<?php echo esc_html( $shp_testimonials_badge ); ?>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $shp_testimonials_heading ) ) : ?>
				<h2><?php echo wp_kses_post( $shp_testimonials_heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $shp_testimonials_description ) ) : ?>
				<p><?php echo wp_kses_post( $shp_testimonials_description ); ?></p>
			<?php endif; ?>

		</div><!-- .shp-section-header -->

		<!-- Swiper Slider -->
		<?php if ( have_rows( 'testimonials_repeater' ) ) : ?>
			<div class="swiper shp-testimonials-slider">
				<div class="swiper-wrapper">

					<?php
					while ( have_rows( 'testimonials_repeater' ) ) :
						the_row();
						$shp_customer_image       = get_sub_field( 'customer_image' );
						$shp_customer_name        = get_sub_field( 'customer_name' );
						$shp_customer_designation = get_sub_field( 'customer_designation' );
						$shp_customer_rating      = absint( get_sub_field( 'customer_rating' ) );
						$shp_customer_review      = get_sub_field( 'customer_review' );
						?>
						<div class="swiper-slide">
							<div class="shp-testimonial-card shp-animate">

								<!-- Quote Icon -->
								<i class="bi bi-quote shp-testimonial-quote-icon"></i>

								<!-- Star Rating -->
								<div class="shp-testimonial-rating">
									<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
										<?php if ( $i <= $shp_customer_rating ) : ?>
											<i class="bi bi-star-fill"></i>
										<?php else : ?>
											<i class="bi bi-star"></i>
										<?php endif; ?>
									<?php endfor; ?>
								</div>

								<!-- Review Text -->
								<?php if ( ! empty( $shp_customer_review ) ) : ?>
									<p class="shp-testimonial-text">
										<?php echo esc_html( $shp_customer_review ); ?>
									</p>
								<?php endif; ?>

								<!-- Author Info -->
								<div class="shp-testimonial-author">
									<?php if ( ! empty( $shp_customer_image['url'] ) ) : ?>
										<img
											src="<?php echo esc_url( $shp_customer_image['url'] ); ?>"
											alt="<?php echo esc_attr( $shp_customer_image['alt'] ?? $shp_customer_name ); ?>"
											class="shp-testimonial-avatar"
											loading="lazy"
											decoding="async"
										/>
									<?php endif; ?>

									<div class="shp-testimonial-info">
										<?php if ( ! empty( $shp_customer_name ) ) : ?>
											<span class="shp-testimonial-name">
												<?php echo esc_html( $shp_customer_name ); ?>
											</span>
										<?php endif; ?>

										<?php if ( ! empty( $shp_customer_designation ) ) : ?>
											<span class="shp-testimonial-designation">
												<?php echo esc_html( $shp_customer_designation ); ?>
											</span>
										<?php endif; ?>
									</div><!-- .shp-testimonial-info -->
								</div><!-- .shp-testimonial-author -->

							</div><!-- .shp-testimonial-card -->
						</div><!-- .swiper-slide -->
					<?php endwhile; ?>

				</div><!-- .swiper-wrapper -->

				<!-- Swiper Controls -->
				<div class="swiper-pagination"></div>
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>

			</div><!-- .swiper -->
		<?php endif; ?>

	</div><!-- .container -->
</section><!-- #testimonials -->
