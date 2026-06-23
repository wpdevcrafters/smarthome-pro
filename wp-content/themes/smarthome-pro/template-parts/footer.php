<?php
/**
 * Template Part: Footer
 *
 * 4-column footer with logo, quick links, newsletter, contact info,
 * bottom bar, and back-to-top button.
 *
 * @package SmartHome_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shp_footer_logo                = get_field( 'footer_logo', 'option' );
$shp_footer_description         = get_field( 'footer_description', 'option' );
$shp_newsletter_heading         = get_field( 'footer_newsletter_heading', 'option' );
$shp_newsletter_description     = get_field( 'footer_newsletter_description', 'option' );
$shp_newsletter_shortcode       = get_field( 'footer_newsletter_shortcode', 'option' );
$shp_footer_phone               = get_field( 'footer_contact_phone', 'option' );
$shp_footer_email               = get_field( 'footer_contact_email', 'option' );
$shp_footer_address             = get_field( 'footer_contact_address', 'option' );
?>

	</main><!-- Close main content wrapper opened in header.php or front-page.php -->

	<footer class="shp-footer">
		<div class="container">
			<div class="row">

				<!-- Column 1: Logo, Description & Social -->
				<div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
					<div class="shp-footer-widget">

						<?php if ( ! empty( $shp_footer_logo['url'] ) ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="shp-footer-logo">
						<img
							src="<?php echo esc_url( $shp_footer_logo['url'] ); ?>"
							alt="<?php echo esc_attr( $shp_footer_logo['alt'] ?? get_bloginfo( 'name' ) ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="shp-footer-logo">
						<img
							src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/footerlogo.png' ); ?>"
							alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</a>
				<?php endif; ?>

						<?php if ( ! empty( $shp_footer_description ) ) : ?>
							<p class="shp-footer-description">
								<?php echo wp_kses_post( $shp_footer_description ); ?>
							</p>
						<?php endif; ?>

						<!-- Social Links -->
						<?php if ( have_rows( 'social_links_repeater', 'option' ) ) : ?>
							<div class="shp-social-links">
								<?php
								while ( have_rows( 'social_links_repeater', 'option' ) ) :
									the_row();
									$shp_social_icon = get_sub_field( 'social_icon' );
									$shp_social_link = get_sub_field( 'social_url' );
									?>
									<?php if ( $shp_social_link && ! empty( $shp_social_icon ) ) : 
										$link_url = $shp_social_link['url'];
										$link_target = $shp_social_link['target'] ? $shp_social_link['target'] : '_blank';
										?>
										<a
											href="<?php echo esc_url( $link_url ); ?>"
											class="shp-social-icon"
											target="<?php echo esc_attr( $link_target ); ?>"
											rel="noopener noreferrer"
											aria-label="<?php echo esc_attr( str_replace( 'bi-', '', $shp_social_icon ) ); ?>"
										>
											<i class="bi <?php echo esc_attr( $shp_social_icon ); ?>"></i>
										</a>
									<?php endif; ?>
								<?php endwhile; ?>
							</div><!-- .shp-social-links -->
						<?php endif; ?>

					</div><!-- .shp-footer-widget -->
				</div><!-- .col -->

				<!-- Column 2: Quick Links -->
				<div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
					<div class="shp-footer-widget">
						<h4 class="shp-footer-title">
							<?php esc_html_e( 'Quick Links', 'smarthome-pro' ); ?>
						</h4>

						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => 'shp-footer-links list-unstyled',
								'fallback_cb'    => 'shp_footer_nav_fallback',
								'depth'          => 1,
								'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							)
						);
						?>
					</div><!-- .shp-footer-widget -->
				</div><!-- .col -->

				<!-- Column 3: Newsletter -->
				<div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
					<div class="shp-footer-widget shp-footer-newsletter">

						<?php if ( ! empty( $shp_newsletter_heading ) ) : ?>
							<h4 class="shp-footer-title">
								<?php echo esc_html( $shp_newsletter_heading ); ?>
							</h4>
						<?php endif; ?>

						<?php if ( ! empty( $shp_newsletter_description ) ) : ?>
							<p><?php echo esc_html( $shp_newsletter_description ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $shp_newsletter_shortcode ) ) : ?>
							<?php echo do_shortcode( $shp_newsletter_shortcode ); ?>
						<?php endif; ?>

					</div><!-- .shp-footer-widget -->
				</div><!-- .col -->

				<!-- Column 4: Contact Info -->
				<div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
					<div class="shp-footer-widget shp-footer-contact">
						<h4 class="shp-footer-title">
							<?php esc_html_e( 'Contact Us', 'smarthome-pro' ); ?>
						</h4>

						<?php if ( ! empty( $shp_footer_phone ) ) : ?>
							<div class="shp-footer-contact-item">
								<i class="bi bi-telephone"></i>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $shp_footer_phone ) ); ?>">
									<?php echo esc_html( $shp_footer_phone ); ?>
								</a>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $shp_footer_email ) ) : ?>
							<div class="shp-footer-contact-item">
								<i class="bi bi-envelope"></i>
								<a href="mailto:<?php echo esc_attr( $shp_footer_email ); ?>">
									<?php echo esc_html( $shp_footer_email ); ?>
								</a>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $shp_footer_address ) ) : ?>
							<div class="shp-footer-contact-item">
								<i class="bi bi-geo-alt"></i>
								<span><?php echo wp_kses_post( $shp_footer_address ); ?></span>
							</div>
						<?php endif; ?>

					</div><!-- .shp-footer-widget -->
				</div><!-- .col -->

			</div><!-- .row -->

			<!-- Footer Bottom Bar -->
			<div class="shp-footer-bottom">
				<div class="row align-items-center">
					<div class="col-md-6 text-center text-md-start">
						<p>
							&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
							<?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
							<?php esc_html_e( 'All Rights Reserved.', 'smarthome-pro' ); ?>
						</p>
					</div>
					<div class="col-md-6 text-center text-md-end">
						<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">
							<?php esc_html_e( 'Privacy Policy', 'smarthome-pro' ); ?>
						</a>
						<span class="shp-footer-separator">|</span>
						<a href="#">
							<?php esc_html_e( 'Terms of Service', 'smarthome-pro' ); ?>
						</a>
						<span class="shp-footer-separator">|</span>
						<a href="#">
							<?php esc_html_e( 'Cookie Policy', 'smarthome-pro' ); ?>
						</a>
					</div>
				</div><!-- .row -->
			</div><!-- .shp-footer-bottom -->

		</div><!-- .container -->
	</footer><!-- .shp-footer -->

	<!-- Back to Top Button -->
	<a href="#home" class="shp-back-to-top" id="backToTop" aria-label="<?php esc_attr_e( 'Back to top', 'smarthome-pro' ); ?>">
		<i class="bi bi-arrow-up"></i>
	</a>

	<?php wp_footer(); ?>

</body>
</html>

<?php
if ( ! function_exists( 'shp_footer_nav_fallback' ) ) {
	/**
	 * Fallback navigation callback for the footer menu.
	 * Outputs hardcoded anchor links matching the single-page sections.
	 */
	function shp_footer_nav_fallback() {
		?>
		<ul class="shp-footer-links list-unstyled">
			<li><a href="#home"><?php esc_html_e( 'Home', 'smarthome-pro' ); ?></a></li>
			<li><a href="#features"><?php esc_html_e( 'Features', 'smarthome-pro' ); ?></a></li>
			<li><a href="#pricing"><?php esc_html_e( 'Pricing', 'smarthome-pro' ); ?></a></li>
			<li><a href="#testimonials"><?php esc_html_e( 'Testimonials', 'smarthome-pro' ); ?></a></li>
			<li><a href="#contact"><?php esc_html_e( 'Contact', 'smarthome-pro' ); ?></a></li>
		</ul>
		<?php
	}
}

