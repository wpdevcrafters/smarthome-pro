<?php
/**
 * SmartHome Pro Theme Setup
 *
 * Registers theme supports, navigation menus, image sizes,
 * and Bootstrap-compatible nav menu filters.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @return void
 */
function smarthome_pro_theme_setup() {

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 */
	add_theme_support( 'post-thumbnails' );

	/*
	 * Enable support for custom logo.
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	/*
	 * Switch core markup to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	/*
	 * Register navigation menus.
	 */
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'smarthome-pro' ),
			'footer'  => esc_html__( 'Footer Menu', 'smarthome-pro' ),
		)
	);

	/*
	 * Register custom image sizes.
	 */
	add_image_size( 'shp-hero', 1920, 1080, true );
	add_image_size( 'shp-testimonial', 100, 100, true );
	add_image_size( 'shp-feature-icon', 80, 80, true );
}
add_action( 'after_setup_theme', 'smarthome_pro_theme_setup' );

/**
 * Add 'nav-item' class to navigation menu list items for Bootstrap compatibility.
 *
 * @param string[] $classes Array of the CSS classes that are applied to the menu item's <li> element.
 * @param WP_Post  $menu_item The current menu item object.
 * @param stdClass $args      An object of wp_nav_menu() arguments.
 * @param int      $depth     Depth of menu item.
 * @return string[] Modified array of CSS classes.
 */
function smarthome_pro_nav_menu_css_class( $classes, $menu_item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
		$classes[] = 'nav-item';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'smarthome_pro_nav_menu_css_class', 10, 4 );

/**
 * Add Bootstrap and shp- classes to navigation menu link attributes.
 *
 * @param array    $atts      The HTML attributes applied to the menu item's <a> element.
 * @param WP_Post  $menu_item The current menu item object.
 * @param stdClass $args      An object of wp_nav_menu() arguments.
 * @param int      $depth     Depth of menu item.
 * @return array Modified HTML attributes.
 */
function smarthome_pro_nav_menu_link_attributes( $atts, $menu_item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
		$existing_classes = isset( $atts['class'] ) ? $atts['class'] : '';
		$atts['class']    = trim( $existing_classes . ' nav-link shp-nav-link' );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'smarthome_pro_nav_menu_link_attributes', 10, 4 );

/**
 * Output Schema.org Organization structured data in the document head.
 *
 * @return void
 */
function smarthome_pro_output_schema() {
	if ( ! is_page_template( 'page-smarthome-pro.php' ) && ! is_front_page() ) {
		return;
	}

	$shp_schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);

	if ( function_exists( 'get_field' ) ) {
		$shp_logo = get_field( 'site_logo', 'option' );
		if ( ! empty( $shp_logo['url'] ) ) {
			$shp_schema['logo'] = $shp_logo['url'];
		}

		$shp_phone = get_field( 'footer_contact_phone', 'option' );
		if ( ! empty( $shp_phone ) ) {
			$shp_schema['telephone'] = $shp_phone;
		}

		$shp_email = get_field( 'footer_contact_email', 'option' );
		if ( ! empty( $shp_email ) ) {
			$shp_schema['email'] = $shp_email;
		}

		$shp_address = get_field( 'footer_contact_address', 'option' );
		if ( ! empty( $shp_address ) ) {
			$shp_schema['address'] = array(
				'@type'         => 'PostalAddress',
				'streetAddress' => $shp_address,
			);
		}

		if ( have_rows( 'social_links_repeater', 'option' ) ) {
			$shp_social_urls = array();
			while ( have_rows( 'social_links_repeater', 'option' ) ) {
				the_row();
				$shp_social_url = get_sub_field( 'social_url' );
				if ( ! empty( $shp_social_url ) ) {
					$shp_social_urls[] = $shp_social_url;
				}
			}
			if ( ! empty( $shp_social_urls ) ) {
				$shp_schema['sameAs'] = $shp_social_urls;
			}
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $shp_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
}
add_action( 'wp_head', 'smarthome_pro_output_schema' );

