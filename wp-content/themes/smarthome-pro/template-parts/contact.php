<?php
/**
 * Template Part: Contact Section
 *
 * Two-column layout with benefits list and Contact Form 7 integration.
 * Dark blue/primary background.
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_contact_badge       = get_field( 'contact_badge' );
$shp_contact_heading     = get_field( 'contact_heading' );
$shp_contact_description = get_field( 'contact_description' );
$shp_contact_shortcode   = get_field( 'contact_form_shortcode' );
?>

<section id="contact" class="shp-contact">
	<div class="container">
		<div class="row align-items-center">

			<!-- Left Column: Info & Benefits -->
			<div class="col-lg-5 mb-4 mb-lg-0">
				<div class="shp-contact-info">

					<?php if ( ! empty( $shp_contact_badge ) ) : ?>
						<span class="shp-contact-badge shp-section-badge">
							<?php echo esc_html( $shp_contact_badge ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $shp_contact_heading ) ) : ?>
						<h2><?php echo wp_kses_post( $shp_contact_heading ); ?></h2>
					<?php endif; ?>

					<?php if ( ! empty( $shp_contact_description ) ) : ?>
						<p><?php echo wp_kses_post( $shp_contact_description ); ?></p>
					<?php endif; ?>

					<!-- Benefits List -->
					<?php if ( have_rows( 'contact_benefits_repeater' ) ) : ?>
						<div class="shp-benefits-list">
							<?php
							while ( have_rows( 'contact_benefits_repeater' ) ) :
								the_row();
								$shp_benefit_icon = get_sub_field( 'benefit_icon' );
								$shp_benefit_text = get_sub_field( 'benefit_text' );
								?>
								<div class="shp-benefit-item">
									<div class="shp-benefit-icon">
										<?php if ( ! empty( $shp_benefit_icon ) ) : ?>
											<img
												src="<?php echo esc_url( $shp_benefit_icon['url'] ); ?>"
												alt="<?php echo esc_attr( $shp_benefit_icon['alt'] ); ?>"
												loading="lazy"
												decoding="async"
											/>
										<?php else : ?>
											<i class="bi bi-check-lg"></i>
										<?php endif; ?>
									</div>
									<?php if ( ! empty( $shp_benefit_text ) ) : ?>
										<span class="shp-benefit-text">
											<?php echo esc_html( $shp_benefit_text ); ?>
										</span>
									<?php endif; ?>
								</div><!-- .shp-benefit-item -->
							<?php endwhile; ?>
						</div><!-- .shp-benefits-list -->
					<?php endif; ?>

				</div><!-- .shp-contact-info -->
			</div><!-- .col-lg-5 -->

			<!-- Right Column: Contact Form -->
			<div class="col-lg-7">
				<div class="shp-contact-form-wrapper">

					<h3><?php esc_html_e( 'Request Free Consultation', 'smarthome-pro' ); ?></h3>

					<?php if ( ! empty( $shp_contact_shortcode ) ) : ?>
						<?php echo do_shortcode( $shp_contact_shortcode ); ?>
					<?php else : ?>
						<p class="text-muted">
							<?php esc_html_e( 'Contact form is not configured. Please add a Contact Form 7 shortcode in the theme settings.', 'smarthome-pro' ); ?>
						</p>
					<?php endif; ?>

				</div><!-- .shp-contact-form-wrapper -->
			</div><!-- .col-lg-7 -->

		</div><!-- .row -->
	</div><!-- .container -->
</section><!-- #contact -->
