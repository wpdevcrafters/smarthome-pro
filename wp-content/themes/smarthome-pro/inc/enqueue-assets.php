<?php
/**
 * SmartHome Pro Asset Enqueue
 *
 * Enqueues all styles and scripts for the SmartHome Pro theme,
 * including vendor libraries (Bootstrap, Swiper), Google Fonts,
 * and custom theme assets.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end styles and scripts.
 *
 * @return void
 */
function smarthome_pro_enqueue_assets() {

	$theme_uri = get_template_directory_uri();

	/*
	 * ---------- Styles ----------
	 */

	// 1. Google Fonts — Inter (400, 500, 600, 700, 800).
	wp_enqueue_style(
		'google-fonts-inter',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null // Google Fonts URL should not be versioned.
	);

	// 2. Bootstrap CSS.
	wp_enqueue_style(
		'bootstrap-css',
		$theme_uri . '/assets/vendor/bootstrap/css/bootstrap.min.css',
		array(),
		'5.3.3'
	);

	// 3. Bootstrap Icons.
	wp_enqueue_style(
		'bootstrap-icons',
		$theme_uri . '/assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css',
		array(),
		'1.11.3'
	);

	// 4. Swiper CSS.
	wp_enqueue_style(
		'swiper-css',
		$theme_uri . '/assets/vendor/swiper/swiper-bundle.min.css',
		array(),
		'11.0.0'
	);

	// 5. Theme custom styles — depends on all vendor styles.
	wp_enqueue_style(
		'smarthome-pro-styles',
		$theme_uri . '/assets/css/landing-page.css',
		array( 'google-fonts-inter', 'bootstrap-css', 'bootstrap-icons', 'swiper-css' ),
		SHP_THEME_VERSION
	);

	/*
	 * ---------- Scripts ----------
	 */

	// 1. Bootstrap JS (bundle includes Popper).
	wp_enqueue_script(
		'bootstrap-js',
		$theme_uri . '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
		array(),
		'5.3.3',
		true
	);

	// 2. Swiper JS.
	wp_enqueue_script(
		'swiper-js',
		$theme_uri . '/assets/vendor/swiper/swiper-bundle.min.js',
		array(),
		'11.0.0',
		true
	);

	// 3. Theme custom scripts — depends on Bootstrap and Swiper.
	wp_enqueue_script(
		'smarthome-pro-scripts',
		$theme_uri . '/assets/js/landing-page.js',
		array( 'bootstrap-js', 'swiper-js' ),
		SHP_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'smarthome_pro_enqueue_assets' );

/**
 * Add preconnect resource hints for Google Fonts.
 *
 * @param string[] $urls          Array of URLs to print for resource hints.
 * @param string   $relation_type The relation type the URLs are printed for (e.g. 'preconnect').
 * @return string[] Modified array of URLs.
 */
function smarthome_pro_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => '',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'smarthome_pro_resource_hints', 10, 2 );
