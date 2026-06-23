<?php
/**
 * Template Part: Features Section
 *
 * Feature cards grid with Bootstrap icons, loaded from ACF repeater.
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_features_badge       = get_field( 'features_badge' );
$shp_features_heading     = get_field( 'features_heading' );
$shp_features_description = get_field( 'features_description' );
?>

<section id="features" class="shp-features">
	<div class="container">

		<!-- Section Header -->
		<div class="shp-section-header text-center mb-5">

			<?php if ( ! empty( $shp_features_badge ) ) : ?>
				<span class="shp-section-badge">
					<?php echo esc_html( $shp_features_badge ); ?>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $shp_features_heading ) ) : ?>
				<h2><?php echo wp_kses_post( $shp_features_heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $shp_features_description ) ) : ?>
				<p><?php echo wp_kses_post( $shp_features_description ); ?></p>
			<?php endif; ?>

		</div><!-- .shp-section-header -->

		<!-- Features Grid -->
		<?php if ( have_rows( 'features_repeater' ) ) : ?>
			<div class="row">
				<?php
				while ( have_rows( 'features_repeater' ) ) :
					the_row();
					$shp_feature_icon        = get_sub_field( 'feature_icon' );
					$shp_feature_title       = get_sub_field( 'feature_title' );
					$shp_feature_description = get_sub_field( 'feature_description' );
					$shp_feature_link        = get_sub_field( 'feature_link' );
					?>
					<div class="col-lg-4 col-md-6 mb-4">
						<div class="shp-feature-card shp-animate">

							<?php if ( ! empty( $shp_feature_icon ) ) : ?>
								<div class="shp-feature-icon">
									<img
										src="<?php echo esc_url( $shp_feature_icon['url'] ); ?>"
										alt="<?php echo esc_attr( $shp_feature_icon['alt'] ); ?>"
										loading="lazy"
										decoding="async"
									/>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $shp_feature_title ) ) : ?>
								<h3 class="shp-feature-title">
									<?php echo esc_html( $shp_feature_title ); ?>
								</h3>
							<?php endif; ?>

							<?php if ( ! empty( $shp_feature_description ) ) : ?>
								<p class="shp-feature-description">
									<?php echo esc_html( $shp_feature_description ); ?>
								</p>
							<?php endif; ?>

							<?php if ( $shp_feature_link ) : 
								$link_url = $shp_feature_link['url'];
								$link_title = $shp_feature_link['title'] ? $shp_feature_link['title'] : __( 'Learn More', 'smarthome-pro' );
								$link_target = $shp_feature_link['target'] ? $shp_feature_link['target'] : '_self';
								?>
								<a href="<?php echo esc_url( $link_url ); ?>" class="shp-feature-link" target="<?php echo esc_attr( $link_target ); ?>">
									<?php echo esc_html( $link_title ); ?>
								</a>
							<?php endif; ?>

						</div><!-- .shp-feature-card -->
					</div><!-- .col -->
				<?php endwhile; ?>
			</div><!-- .row -->
		<?php endif; ?>

	</div><!-- .container -->
</section><!-- #features -->
