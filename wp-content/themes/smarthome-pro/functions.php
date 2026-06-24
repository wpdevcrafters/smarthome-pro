<?php
/**
 * SmartHome Pro Theme Functions
 *
 * Main bootstrap file for the SmartHome Pro theme.
 * Loads all required includes from the inc/ directory.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define theme constants.
 */
define( 'SHP_THEME_VERSION', '1.0.0' );
define( 'SHP_THEME_DIR', get_template_directory() );
define( 'SHP_THEME_URI', get_template_directory_uri() );

/**
 * Load theme setup — theme supports, nav menus, image sizes.
 */
require_once SHP_THEME_DIR . '/inc/theme-setup.php';

/**
 * Load asset enqueue — styles, scripts, preconnect hints.
 */
require_once SHP_THEME_DIR . '/inc/enqueue-assets.php';

/**
 * Load SVG upload support — allows SVG in media library with sanitization.
 */
require_once SHP_THEME_DIR . '/inc/svg-support.php';

/**
 * Load WebP upload support — fixes "cannot generate responsive image sizes"
 * error on Windows XAMPP and environments without WebP MIME registration.
 */
require_once SHP_THEME_DIR . '/inc/webp-support.php';

/**
 * Load ACF field registrations.
 *
 * Only loaded when Advanced Custom Fields PRO is active.
 */
if ( class_exists( 'ACF' ) ) {
	require_once SHP_THEME_DIR . '/inc/acf-fields.php';
} else {
	/**
	 * Display an admin notice when ACF Pro is not active.
	 */
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %s: Plugin name. */
						esc_html__( '%s requires Advanced Custom Fields PRO to be installed and activated.', 'smarthome-pro' ),
						'<strong>SmartHome Pro</strong>'
					);
					?>
				</p>
			</div>
			<?php
		}
	);
}

/**
 * Load custom REST API endpoints for headless support.
 */
require_once SHP_THEME_DIR . '/inc/rest-endpoints.php';

/**
 * Load custom Contact Form 7 database log and Entries dashboard submenu.
 */
require_once SHP_THEME_DIR . '/inc/form-entries.php';

