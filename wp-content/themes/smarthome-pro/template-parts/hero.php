<?php
/**
 * Template Part: Hero Section
 *
 * Full viewport hero with background image, content columns,
 * CTA buttons, mockup image, and statistics bar.
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_hero_bg          = get_field( 'hero_background_image' );
$shp_hero_mockup      = get_field( 'hero_mockup_image' );
$shp_hero_eyebrow     = get_field( 'hero_eyebrow' );
$shp_hero_heading     = get_field( 'hero_heading' );
$shp_hero_description = get_field( 'hero_description' );
$shp_primary_link     = get_field( 'hero_primary_button_url' );
$shp_secondary_link   = get_field( 'hero_secondary_button_url' );

$shp_bg_style = '';
if ( ! empty( $shp_hero_bg['url'] ) ) {
	$shp_bg_style = sprintf( 'background-image: url(%s);', esc_url( $shp_hero_bg['url'] ) );
} else {
	$shp_bg_style = sprintf( 'background-image: url(%s/assets/images/heroimg.png);', esc_url( get_template_directory_uri() ) );
}
?>

<section id="home" class="shp-hero" <?php echo ! empty( $shp_bg_style ) ? 'style="' . esc_attr( $shp_bg_style ) . '"' : ''; ?>>

	<!-- Dark Overlay -->
	<div class="shp-hero-overlay"></div>

	<!-- Hero Content -->
	<div class="shp-hero-content">
		<div class="container">
			<div class="row align-items-center">

				<!-- Left Column: Text Content -->
				<div class="col-lg-6 shp-hero-text">

					<?php if ( ! empty( $shp_hero_eyebrow ) ) : ?>
						<span class="shp-hero-eyebrow">
							<?php echo esc_html( $shp_hero_eyebrow ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $shp_hero_heading ) ) : ?>
						<h1><?php echo wp_kses_post( $shp_hero_heading ); ?></h1>
					<?php endif; ?>

					<?php if ( ! empty( $shp_hero_description ) ) : ?>
						<p><?php echo wp_kses_post( $shp_hero_description ); ?></p>
					<?php endif; ?>

					<div class="shp-hero-buttons d-flex flex-wrap gap-3">
						<?php if ( $shp_primary_link ) : 
							$link_url = $shp_primary_link['url'];
							$link_title = $shp_primary_link['title'];
							$link_target = $shp_primary_link['target'] ? $shp_primary_link['target'] : '_self';
							?>
							<a href="<?php echo esc_url( $link_url ); ?>" class="shp-btn-primary" target="<?php echo esc_attr( $link_target ); ?>">
								<?php echo esc_html( $link_title ); ?>
								<i class="bi bi-arrow-right"></i>
							</a>
						<?php endif; ?>

						<?php if ( $shp_secondary_link ) : 
							$link_url = $shp_secondary_link['url'];
							$link_title = $shp_secondary_link['title'];
							$link_target = $shp_secondary_link['target'] ? $shp_secondary_link['target'] : '_self';
							?>
							<a href="<?php echo esc_url( $link_url ); ?>" class="shp-btn-secondary" target="<?php echo esc_attr( $link_target ); ?>">
								<i class="bi bi-play-circle"></i>
								<?php echo esc_html( $link_title ); ?>
							</a>
						<?php endif; ?>
					</div>

				</div><!-- .shp-hero-text -->

				<!-- Right Column: Mockup Image -->
				<div class="col-lg-6 shp-hero-mockup">
					<?php if ( ! empty( $shp_hero_mockup['url'] ) ) : ?>
						<img
							src="<?php echo esc_url( $shp_hero_mockup['url'] ); ?>"
							alt="<?php echo esc_attr( $shp_hero_mockup['alt'] ?? __( 'SmartHome Pro Dashboard', 'smarthome-pro' ) ); ?>"
							class="img-fluid"
							loading="lazy"
							decoding="async"
						/>
					<?php endif; ?>
				</div><!-- .shp-hero-mockup -->

			</div><!-- .row -->
		</div><!-- .container -->
	</div><!-- .shp-hero-content -->

	<!-- Stats Bar -->
	<?php if ( have_rows( 'hero_statistics_repeater' ) ) : ?>
		<div class="shp-stats-bar-wrapper">
			<div class="container">
				<div class="shp-stats-bar">
					<div class="row align-items-center g-0">
						<?php
						while ( have_rows( 'hero_statistics_repeater' ) ) :
							the_row();
							$shp_stat_icon   = get_sub_field( 'stat_icon' );
							$shp_stat_number = get_sub_field( 'stat_number' );
							$shp_stat_label  = get_sub_field( 'stat_label' );
							?>
							<div class="col-6 col-lg-3 mb-3 mb-lg-0 shp-stat-item">
								<div class="shp-stat-inner d-flex align-items-center justify-content-center gap-3">
									<?php if ( ! empty( $shp_stat_icon ) ) : ?>
										<div class="shp-stat-icon">
											<i class="bi <?php echo esc_attr( $shp_stat_icon ); ?>"></i>
										</div>
									<?php endif; ?>
									<div class="shp-stat-content text-center">
										<?php if ( ! empty( $shp_stat_number ) ) : ?>
											<div class="shp-stat-number">
												<?php echo esc_html( $shp_stat_number ); ?>
											</div>
										<?php endif; ?>
										<?php if ( ! empty( $shp_stat_label ) ) : ?>
											<div class="shp-stat-label">
												<?php echo esc_html( $shp_stat_label ); ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div><!-- .shp-stat-item -->
						<?php endwhile; ?>
					</div><!-- .row -->
				</div><!-- .shp-stats-bar -->
			</div><!-- .container -->
		</div><!-- .shp-stats-bar-wrapper -->
	<?php endif; ?>

</section><!-- #home -->
