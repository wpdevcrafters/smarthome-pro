<?php
/**
 * SmartHome Pro Custom REST API Endpoints
 *
 * Exposes page layouts, menus, and global theme settings
 * to the React.js headless frontend.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Custom REST API Routes.
 */
function shp_register_rest_routes() {
	register_rest_route(
		'shp/v1',
		'/landing-data',
		array(
			'methods'             => 'GET',
			'callback'            => 'shp_get_landing_data',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'shp_register_rest_routes' );

/**
 * Callback for the landing-data REST endpoint.
 *
 * Aggregates site logos, contact numbers, social lists, menus,
 * and ACF landing page sections.
 *
 * @return WP_REST_Response|WP_Error
 */
function shp_get_landing_data() {
	$page_id = 14; // Landing Page ID

	if ( ! function_exists( 'get_field' ) ) {
		return new WP_Error( 'acf_missing', 'Advanced Custom Fields is not active', array( 'status' => 500 ) );
	}

	// 1. Site settings and logos from ACF Options
	$logo_array = get_field( 'site_logo', 'option' );
	$logo_url   = isset( $logo_array['url'] ) ? $logo_array['url'] : '';

	$footer_logo_array = get_field( 'footer_logo', 'option' );
	$footer_logo_url   = isset( $footer_logo_array['url'] ) ? $footer_logo_array['url'] : '';
	$favicon_url       = get_site_icon_url();
	$newsletter_shortcode = get_field( 'footer_newsletter_shortcode', 'option' );

	$socials = array();
	if ( have_rows( 'social_links_repeater', 'option' ) ) {
		while ( have_rows( 'social_links_repeater', 'option' ) ) {
			the_row();
			$social_url_array = get_sub_field( 'social_url' );
			$socials[]        = array(
				'platform' => get_sub_field( 'social_icon' ),
				'url'      => isset( $social_url_array['url'] ) ? $social_url_array['url'] : '#',
			);
		}
	}

	// 2. Navigation Menus
	$locations    = get_nav_menu_locations();
	$primary_menu = array();
	if ( isset( $locations['primary'] ) ) {
		$menu_items = wp_get_nav_menu_items( $locations['primary'] );
		if ( $menu_items ) {
			foreach ( $menu_items as $item ) {
				// Normalize URLs to relative paths for React Router
				$path           = str_replace( home_url(), '', $item->url );
				$primary_menu[] = array(
					'label' => $item->title,
					'path'  => $path ? $path : '/',
				);
			}
		}
	}

	$footer_menu = array();
	if ( isset( $locations['footer'] ) ) {
		$menu_items = wp_get_nav_menu_items( $locations['footer'] );
		if ( $menu_items ) {
			foreach ( $menu_items as $item ) {
				$path          = str_replace( home_url(), '', $item->url );
				$footer_menu[] = array(
					'label' => $item->title,
					'path'  => $path ? $path : '/',
				);
			}
		}
	}

	// 3. Hero Stats
	$stats = array();
	if ( have_rows( 'hero_statistics_repeater', $page_id ) ) {
		while ( have_rows( 'hero_statistics_repeater', $page_id ) ) {
			the_row();
			$stats[] = array(
				'stat_icon'   => get_sub_field( 'stat_icon' ),
				'stat_number' => get_sub_field( 'stat_number' ),
				'stat_label'  => get_sub_field( 'stat_label' ),
			);
		}
	}

	// 4. Features
	$features_list = array();
	if ( have_rows( 'features_repeater', $page_id ) ) {
		while ( have_rows( 'features_repeater', $page_id ) ) {
			the_row();
			$icon_array      = get_sub_field( 'feature_icon' );
			$link_array      = get_sub_field( 'feature_link' );
			$features_list[] = array(
				'id'          => get_row_index(),
				'icon'        => isset( $icon_array['url'] ) ? $icon_array['url'] : '',
				'title'       => get_sub_field( 'feature_title' ),
				'description' => get_sub_field( 'feature_description' ),
				'link'        => isset( $link_array['url'] ) ? $link_array['url'] : '#',
			);
		}
	}

	// 5. Testimonials
	$testimonials_list = array();
	if ( have_rows( 'testimonials_repeater', $page_id ) ) {
		while ( have_rows( 'testimonials_repeater', $page_id ) ) {
			the_row();
			$image_array         = get_sub_field( 'customer_image' );
			$testimonials_list[] = array(
				'id'     => get_row_index(),
				'quote'  => get_sub_field( 'customer_review' ),
				'name'   => get_sub_field( 'customer_name' ),
				'role'   => get_sub_field( 'customer_designation' ),
				'image'  => isset( $image_array['url'] ) ? $image_array['url'] : '',
				'rating' => (int) get_sub_field( 'customer_rating' ) ? (int) get_sub_field( 'customer_rating' ) : 5,
			);
		}
	}

	// 6. Pricing Plans
	$pricing_list = array();
	if ( have_rows( 'pricing_repeater', $page_id ) ) {
		while ( have_rows( 'pricing_repeater', $page_id ) ) {
			the_row();
			$button_array   = get_sub_field( 'plan_button_url' );
			$pricing_list[] = array(
				'id'          => get_row_index(),
				'name'        => get_sub_field( 'plan_name' ),
				'price'       => get_sub_field( 'plan_price' ),
				'period'      => str_replace( '/', '', get_sub_field( 'plan_duration' ) ? get_sub_field( 'plan_duration' ) : 'month' ),
				'features'    => array_filter( array_map( 'trim', explode( "\n", get_sub_field( 'plan_features' ) ? get_sub_field( 'plan_features' ) : '' ) ) ),
				'highlighted' => get_sub_field( 'is_featured' ) === true || get_sub_field( 'is_featured' ) == '1',
				'buttonText'  => get_sub_field( 'plan_button_text' ) ? get_sub_field( 'plan_button_text' ) : 'Get Started',
				'buttonUrl'   => isset( $button_array['url'] ) ? $button_array['url'] : '#contact',
			);
		}
	}

	// 7. Contact Benefits
	$benefits_list = array();
	if ( have_rows( 'contact_benefits_repeater', $page_id ) ) {
		while ( have_rows( 'contact_benefits_repeater', $page_id ) ) {
			the_row();
			$icon_array      = get_sub_field( 'benefit_icon' );
			$benefits_list[] = array(
				'id'   => get_row_index(),
				'icon' => isset( $icon_array['url'] ) ? $icon_array['url'] : '',
				'text' => get_sub_field( 'benefit_text' ),
			);
		}
	}

	// Retrieve Hero fields
	$p_btn               = get_field( 'hero_primary_button_url', $page_id );
	$s_btn               = get_field( 'hero_secondary_button_url', $page_id );
	$mockup              = get_field( 'hero_mockup_image', $page_id );
	$bg                  = get_field( 'hero_background_image', $page_id );

	// Package combined response payload
	$data = array(
		'id'                => $page_id,
		'title'             => get_the_title( $page_id ) ? get_the_title( $page_id ) : 'SmartHome Pro',
		'logo'              => $logo_url,
		'footerLogo'        => $footer_logo_url,
		'favicon'           => $favicon_url ? $favicon_url : '',
		'footerNewsletterShortcode' => $newsletter_shortcode ? $newsletter_shortcode : '',
		'footerDescription' => get_field( 'footer_description', 'option' ),
		'footerPhone'       => get_field( 'footer_contact_phone', 'option' ),
		'footerEmail'       => get_field( 'footer_contact_email', 'option' ),
		'footerAddress'     => get_field( 'footer_contact_address', 'option' ),
		'socials'           => $socials,
		'menus'             => array(
			'primary' => ! empty( $primary_menu ) ? $primary_menu : null,
			'footer'  => ! empty( $footer_menu ) ? $footer_menu : null,
		),
		'hero'              => array(
			'eyebrow'         => get_field( 'hero_eyebrow', $page_id ),
			'heading'         => get_field( 'hero_heading', $page_id ),
			'description'     => get_field( 'hero_description', $page_id ),
			'primaryText'     => get_field( 'hero_primary_button_text', $page_id ),
			'primaryUrl'      => isset( $p_btn['url'] ) ? $p_btn['url'] : '#contact',
			'secondaryText'   => get_field( 'hero_secondary_button_text', $page_id ),
			'secondaryUrl'    => isset( $s_btn['url'] ) ? $s_btn['url'] : '#',
			'mockupImage'     => isset( $mockup['url'] ) ? $mockup['url'] : null,
			'backgroundImage' => isset( $bg['url'] ) ? $bg['url'] : null,
			'stats'           => $stats,
		),
		'features'          => array(
			'badge'       => get_field( 'features_badge', $page_id ) ? get_field( 'features_badge', $page_id ) : 'FEATURES',
			'heading'     => get_field( 'features_heading', $page_id ),
			'description'     => get_field( 'features_description', $page_id ),
			'list'        => $features_list,
		),
		'testimonials'      => array(
			'badge'       => get_field( 'testimonials_badge', $page_id ) ? get_field( 'testimonials_badge', $page_id ) : 'TESTIMONIALS',
			'heading'     => get_field( 'testimonials_heading', $page_id ),
			'description'     => get_field( 'testimonials_description', $page_id ),
			'list'        => $testimonials_list,
		),
		'pricing'           => array(
			'badge'       => get_field( 'pricing_badge', $page_id ) ? get_field( 'pricing_badge', $page_id ) : 'PRICING',
			'heading'     => get_field( 'pricing_heading', $page_id ),
			'description'     => get_field( 'pricing_description', $page_id ),
			'list'        => $pricing_list,
		),
		'contact'           => array(
			'badge'         => get_field( 'contact_badge', $page_id ) ? get_field( 'contact_badge', $page_id ) : 'CONTACT US',
			'heading'       => get_field( 'contact_heading', $page_id ),
			'description'       => get_field( 'contact_description', $page_id ),
			'benefits'      => $benefits_list,
			'formShortcode' => get_field( 'contact_form_shortcode', $page_id ),
		),
	);

	return new WP_REST_Response( $data, 200 );
}

/**
 * Enable CORS for React Frontend requests.
 */
function shp_enable_cors_headers() {
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter(
		'rest_pre_serve_request',
		function ( $value ) {
			$origin = get_http_origin();
			if ( $origin ) {
				header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
			} else {
				header( 'Access-Control-Allow-Origin: *' );
			}
			header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE' );
			header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-WP-Nonce' );
			header( 'Access-Control-Allow-Credentials: true' );
			return $value;
		}
	);
}
add_action( 'rest_api_init', 'shp_enable_cors_headers', 15 );
