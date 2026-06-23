<?php
/**
 * Template Part: Header
 *
 * Bootstrap 5 navbar with ACF options integration.
 * Transparent by default, .scrolled class added via JS.
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_site_logo    = get_field( 'site_logo', 'option' );
$shp_cta_link     = get_field( 'header_cta_url', 'option' );
?>

<header class="shp-header">
	<nav class="navbar navbar-expand-lg shp-navbar" aria-label="<?php esc_attr_e( 'Primary Navigation', 'smarthome-pro' ); ?>">
		<div class="container">

			<!-- Logo -->
			<a class="navbar-brand shp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( ! empty( $shp_site_logo['url'] ) ) : ?>
					<img
						src="<?php echo esc_url( $shp_site_logo['url'] ); ?>"
						alt="<?php echo esc_attr( $shp_site_logo['alt'] ?? get_bloginfo( 'name' ) ); ?>"
						class="shp-logo-img"
					/>
				<?php else : ?>
					<img
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/headerlogo.png' ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
						class="shp-logo-img"
					/>
				<?php endif; ?>
			</a>

			<!-- Mobile Toggle -->
			<button
				class="navbar-toggler"
				type="button"
				data-bs-toggle="offcanvas"
				data-bs-target="#shpOffcanvasNav"
				aria-controls="shpOffcanvasNav"
				aria-label="<?php esc_attr_e( 'Toggle navigation', 'smarthome-pro' ); ?>"
			>
				<span class="navbar-toggler-icon"></span>
			</button>

			<!-- Offcanvas Navigation -->
			<div
				class="offcanvas offcanvas-end text-bg-dark"
				tabindex="-1"
				id="shpOffcanvasNav"
				aria-labelledby="shpOffcanvasNavLabel"
			>
				<div class="offcanvas-header justify-content-end">
					<button
						type="button"
						class="btn-close btn-close-white"
						data-bs-dismiss="offcanvas"
						aria-label="<?php esc_attr_e( 'Close', 'smarthome-pro' ); ?>"
					></button>
				</div>

				<div class="offcanvas-body">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'navbar-nav ms-auto mb-2 mb-lg-0 align-items-center shp-nav-menu',
							'fallback_cb'    => 'shp_primary_nav_fallback',
							'depth'          => 1,
							'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							'link_before'    => '',
							'link_after'     => '',
						)
					);
					?>

					<?php if ( $shp_cta_link ) : 
						$link_url = $shp_cta_link['url'];
						$link_title = $shp_cta_link['title'];
						$link_target = $shp_cta_link['target'] ? $shp_cta_link['target'] : '_self';
						?>
						<div class="d-lg-none mt-3">
							<a href="<?php echo esc_url( $link_url ); ?>" class="btn shp-cta-btn w-100" target="<?php echo esc_attr( $link_target ); ?>">
								<?php echo esc_html( $link_title ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Desktop CTA Button -->
			<?php if ( $shp_cta_link ) : 
				$link_url = $shp_cta_link['url'];
				$link_title = $shp_cta_link['title'];
				$link_target = $shp_cta_link['target'] ? $shp_cta_link['target'] : '_self';
				?>
				<a href="<?php echo esc_url( $link_url ); ?>" class="btn shp-cta-btn d-none d-lg-inline-block ms-3" target="<?php echo esc_attr( $link_target ); ?>">
					<?php echo esc_html( $link_title ); ?>
				</a>
			<?php endif; ?>

		</div><!-- .container -->
	</nav>
</header>

<?php
if ( ! function_exists( 'shp_primary_nav_fallback' ) ) {
	/**
	 * Fallback navigation callback when no menu is assigned to the 'primary' location.
	 * Outputs hardcoded anchor links for single-page navigation.
	 */
	function shp_primary_nav_fallback() {
		?>
		<ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center shp-nav-menu">
			<li class="nav-item">
				<a class="nav-link shp-nav-link active" href="#home"><?php esc_html_e( 'Home', 'smarthome-pro' ); ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link shp-nav-link" href="#features"><?php esc_html_e( 'Features', 'smarthome-pro' ); ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link shp-nav-link" href="#pricing"><?php esc_html_e( 'Pricing', 'smarthome-pro' ); ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link shp-nav-link" href="#testimonials"><?php esc_html_e( 'Testimonials', 'smarthome-pro' ); ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link shp-nav-link" href="#contact"><?php esc_html_e( 'Contact', 'smarthome-pro' ); ?></a>
			</li>
		</ul>
		<?php
	}
}

