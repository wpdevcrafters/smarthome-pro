<?php
/**
 * Template Part: Pricing Section
 *
 * Pricing cards with featured plan highlight, loaded from ACF repeater.
 * Features list parsed from textarea (one feature per line).
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_pricing_badge       = get_field( 'pricing_badge' );
$shp_pricing_heading     = get_field( 'pricing_heading' );
$shp_pricing_description = get_field( 'pricing_description' );
?>

<section id="pricing" class="shp-pricing">
	<div class="container">

		<!-- Section Header -->
		<div class="shp-section-header text-center mb-5">

			<?php if ( ! empty( $shp_pricing_badge ) ) : ?>
				<span class="shp-section-badge">
					<?php echo esc_html( $shp_pricing_badge ); ?>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $shp_pricing_heading ) ) : ?>
				<h2><?php echo wp_kses_post( $shp_pricing_heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $shp_pricing_description ) ) : ?>
				<p><?php echo wp_kses_post( $shp_pricing_description ); ?></p>
			<?php endif; ?>

		</div><!-- .shp-section-header -->

		<!-- Pricing Cards Grid -->
		<?php if ( have_rows( 'pricing_repeater' ) ) : ?>
			<div class="row justify-content-center">
				<?php
				while ( have_rows( 'pricing_repeater' ) ) :
					the_row();
					$shp_plan_name       = get_sub_field( 'plan_name' );
					$shp_plan_price      = get_sub_field( 'plan_price' );
					$shp_plan_duration   = get_sub_field( 'plan_duration' );
					$shp_plan_features   = get_sub_field( 'plan_features' );
					$shp_plan_btn_link   = get_sub_field( 'plan_button_url' );
					$shp_is_featured     = get_sub_field( 'is_featured' );

					// Build card classes.
					$shp_card_classes = 'shp-pricing-card shp-animate';
					if ( $shp_is_featured ) {
						$shp_card_classes .= ' shp-pricing-featured';
					}

					// Parse features from textarea.
					$shp_features_list = array();
					if ( ! empty( $shp_plan_features ) ) {
						$shp_features_list = array_filter(
							array_map( 'trim', explode( "\n", $shp_plan_features ) )
						);
					}

					// Button class based on featured status.
					$shp_btn_class = $shp_is_featured ? 'btn shp-pricing-btn shp-pricing-btn-solid' : 'btn shp-pricing-btn shp-pricing-btn-outline';
					?>
					<div class="col-lg-4 col-md-6 mb-4">
						<div class="<?php echo esc_attr( $shp_card_classes ); ?>">

							<?php if ( $shp_is_featured ) : ?>
								<span class="shp-pricing-badge">
									<?php esc_html_e( 'MOST POPULAR', 'smarthome-pro' ); ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $shp_plan_name ) ) : ?>
								<h3 class="shp-pricing-name">
									<?php echo esc_html( $shp_plan_name ); ?>
								</h3>
							<?php endif; ?>

							<div class="shp-pricing-price">
								<?php if ( ! empty( $shp_plan_price ) ) : ?>
									<span class="shp-pricing-amount">
										<?php echo esc_html( $shp_plan_price ); ?>
									</span>
								<?php endif; ?>

								<?php if ( ! empty( $shp_plan_duration ) ) : ?>
									<span class="shp-pricing-duration">
										<?php echo esc_html( $shp_plan_duration ); ?>
									</span>
								<?php endif; ?>
							</div><!-- .shp-pricing-price -->

							<?php if ( ! empty( $shp_features_list ) ) : ?>
								<ul class="shp-pricing-features">
									<?php foreach ( $shp_features_list as $shp_feature ) : ?>
										<li>
											<i class="bi bi-check-circle-fill"></i>
											<?php echo esc_html( $shp_feature ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if ( $shp_plan_btn_link ) : 
								$link_url = $shp_plan_btn_link['url'];
								$link_title = $shp_plan_btn_link['title'] ? $shp_plan_btn_link['title'] : __( 'Get Started', 'smarthome-pro' );
								$link_target = $shp_plan_btn_link['target'] ? $shp_plan_btn_link['target'] : '_self';
								?>
								<a href="<?php echo esc_url( $link_url ); ?>" class="<?php echo esc_attr( $shp_btn_class ); ?>" target="<?php echo esc_attr( $link_target ); ?>">
									<?php echo esc_html( $link_title ); ?>
								</a>
							<?php endif; ?>

						</div><!-- .shp-pricing-card -->
					</div><!-- .col -->
				<?php endwhile; ?>
			</div><!-- .row -->
		<?php endif; ?>

	</div><!-- .container -->
</section><!-- #pricing -->
