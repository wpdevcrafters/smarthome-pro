<?php
/**
 * SmartHome Pro ACF Field Registration
 *
 * Registers all ACF Pro field groups, options pages, and fields
 * for the SmartHome Pro theme. This is the single source of truth
 * for all custom field definitions.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register ACF Options Pages.
 *
 * Creates the main SmartHome Pro settings page and a
 * Footer Settings sub-page in the WordPress admin.
 *
 * @return void
 */
function smarthome_pro_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'SmartHome Pro Settings', 'smarthome-pro' ),
			'menu_title' => __( 'SmartHome Pro', 'smarthome-pro' ),
			'menu_slug'  => 'smarthome-pro-settings',
			'capability' => 'edit_posts',
			'icon_url'   => 'dashicons-admin-home',
			'redirect'   => false,
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Footer Settings', 'smarthome-pro' ),
			'menu_title'  => __( 'Footer Settings', 'smarthome-pro' ),
			'parent_slug' => 'smarthome-pro-settings',
		)
	);
}
add_action( 'acf/init', 'smarthome_pro_acf_options_pages' );

/**
 * Register all ACF field groups and fields.
 *
 * @return void
 */
function smarthome_pro_acf_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/*
	 * =========================================================================
	 * Field Group 1: Global Theme Settings (Options Page)
	 * =========================================================================
	 */
	acf_add_local_field_group(
		array(
			'key'      => 'group_shp_global',
			'title'    => 'Global Theme Settings',
			'fields'   => array(

				// ---- Site Logo ----
				array(
					'key'           => 'field_shp_site_logo',
					'label'         => 'Site Logo',
					'name'          => 'site_logo',
					'type'          => 'image',
					'instructions'  => 'Upload the main site logo. Recommended size: 200×60 px.',
					'required'      => 1,
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'library'       => 'all',
					'mime_types'    => 'png,jpg,jpeg,svg,webp',
				),

				// ---- Header CTA Text ----
				array(
					'key'          => 'field_shp_header_cta_text',
					'label'        => 'Header CTA Button Text',
					'name'         => 'header_cta_text',
					'type'         => 'text',
					'instructions' => 'Text displayed on the header call-to-action button.',
					'placeholder'  => 'Get Started',
					'default_value' => 'Get Started',
				),

				// ---- Header CTA URL ----
				array(
					'key'           => 'field_shp_header_cta_url',
					'label'         => 'Header CTA Link',
					'name'          => 'header_cta_url',
					'type'          => 'link',
					'instructions'  => 'Link for the header call-to-action button.',
					'return_format' => 'array',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'smarthome-pro-settings',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	/*
	 * =========================================================================
	 * Field Group 2: Landing Page Settings (Page Template)
	 * =========================================================================
	 */
	acf_add_local_field_group(
		array(
			'key'      => 'group_shp_landing',
			'title'    => 'Landing Page Settings',
			'fields'   => array(

				/*
				 * -----------------------------------------------------------------
				 * Hero Tab
				 * -----------------------------------------------------------------
				 */
				array(
					'key'   => 'field_shp_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
					'placement' => 'top',
				),

				// Hero Background Image.
				array(
					'key'           => 'field_shp_hero_background_image',
					'label'         => 'Hero Background Image',
					'name'          => 'hero_background_image',
					'type'          => 'image',
					'instructions'  => 'Upload a high-resolution background image. Recommended: 1920×1080 px.',
					'required'      => 0,
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'library'       => 'all',
					'mime_types'    => 'png,jpg,jpeg,webp',
				),

				// Hero Mockup Image.
				array(
					'key'           => 'field_shp_hero_mockup_image',
					'label'         => 'Hero Mockup Image',
					'name'          => 'hero_mockup_image',
					'type'          => 'image',
					'instructions'  => 'Upload a product mockup or app screenshot to display in the hero section.',
					'required'      => 0,
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'library'       => 'all',
					'mime_types'    => 'png,jpg,jpeg,webp,svg',
				),

				// Hero Eyebrow.
				array(
					'key'           => 'field_shp_hero_eyebrow',
					'label'         => 'Hero Eyebrow Text',
					'name'          => 'hero_eyebrow',
					'type'          => 'text',
					'instructions'  => 'Small text displayed above the main heading.',
					'placeholder'   => '🏠 The Future of Smart Living',
					'default_value' => '',
				),

				// Hero Heading.
				array(
					'key'          => 'field_shp_hero_heading',
					'label'        => 'Hero Heading',
					'name'         => 'hero_heading',
					'type'         => 'text',
					'instructions' => 'Main hero headline. Keep it concise and impactful.',
					'required'     => 1,
					'placeholder'  => 'The Ultimate Smart Home Solution',
				),

				// Hero Description.
				array(
					'key'          => 'field_shp_hero_description',
					'label'        => 'Hero Description',
					'name'         => 'hero_description',
					'type'         => 'textarea',
					'instructions' => 'Supporting text below the hero heading.',
					'rows'         => 3,
					'new_lines'    => 'br',
					'placeholder'  => 'Transform your home into an intelligent, connected ecosystem.',
				),

				// Hero Primary Button Text.
				array(
					'key'           => 'field_shp_hero_primary_button_text',
					'label'         => 'Primary Button Text',
					'name'          => 'hero_primary_button_text',
					'type'          => 'text',
					'instructions'  => 'Text for the primary call-to-action button.',
					'placeholder'   => 'Start Free Trial',
					'default_value' => 'Start Free Trial',
				),

				// Hero Primary Button URL.
				array(
					'key'           => 'field_shp_hero_primary_button_url',
					'label'         => 'Primary Button Link',
					'name'          => 'hero_primary_button_url',
					'type'          => 'link',
					'instructions'  => 'Link for the primary call-to-action button.',
					'return_format' => 'array',
				),

				// Hero Secondary Button Text.
				array(
					'key'           => 'field_shp_hero_secondary_button_text',
					'label'         => 'Secondary Button Text',
					'name'          => 'hero_secondary_button_text',
					'type'          => 'text',
					'instructions'  => 'Text for the secondary (outline) button.',
					'placeholder'   => 'Watch Demo',
					'default_value' => 'Watch Demo',
				),

				// Hero Secondary Button URL.
				array(
					'key'           => 'field_shp_hero_secondary_button_url',
					'label'         => 'Secondary Button Link',
					'name'          => 'hero_secondary_button_url',
					'type'          => 'link',
					'instructions'  => 'Link for the secondary button.',
					'return_format' => 'array',
				),

				// Hero Statistics Repeater.
				array(
					'key'          => 'field_shp_hero_statistics_repeater',
					'label'        => 'Hero Statistics',
					'name'         => 'hero_statistics_repeater',
					'type'         => 'repeater',
					'instructions' => 'Add up to 4 statistics displayed below the hero buttons.',
					'min'          => 1,
					'max'          => 4,
					'layout'       => 'table',
					'button_label' => 'Add Statistic',
					'sub_fields'   => array(
						array(
							'key'          => 'field_shp_stat_icon',
							'label'        => 'Stat Icon',
							'name'         => 'stat_icon',
							'type'         => 'text',
							'instructions' => 'Bootstrap Icon class e.g. bi-people-fill',
							'placeholder'  => 'bi-people-fill',
							'required'     => 1,
						),
						array(
							'key'          => 'field_shp_stat_number',
							'label'        => 'Stat Number',
							'name'         => 'stat_number',
							'type'         => 'text',
							'instructions' => 'e.g. 50K+, 99.9%, 4.8/5',
							'placeholder'  => '50K+',
							'required'     => 1,
						),
						array(
							'key'          => 'field_shp_stat_label',
							'label'        => 'Stat Label',
							'name'         => 'stat_label',
							'type'         => 'text',
							'instructions' => 'e.g. Active Users, Uptime, Rating',
							'placeholder'  => 'Active Users',
							'required'     => 1,
						),
					),
				),

				/*
				 * -----------------------------------------------------------------
				 * Features Tab
				 * -----------------------------------------------------------------
				 */
				array(
					'key'   => 'field_shp_features_tab',
					'label' => 'Features Section',
					'name'  => '',
					'type'  => 'tab',
					'placement' => 'top',
				),

				// Features Badge.
				array(
					'key'           => 'field_shp_features_badge',
					'label'         => 'Section Badge',
					'name'          => 'features_badge',
					'type'          => 'text',
					'instructions'  => 'Small badge text above the section heading.',
					'placeholder'   => 'Features',
					'default_value' => 'Features',
				),

				// Features Heading.
				array(
					'key'          => 'field_shp_features_heading',
					'label'        => 'Section Heading',
					'name'         => 'features_heading',
					'type'         => 'text',
					'instructions' => 'Main heading for the features section.',
					'required'     => 1,
					'placeholder'  => 'Everything You Need for a Smarter Home',
				),

				// Features Description.
				array(
					'key'          => 'field_shp_features_description',
					'label'        => 'Section Description',
					'name'         => 'features_description',
					'type'         => 'textarea',
					'instructions' => 'Supporting text below the features heading.',
					'rows'         => 3,
					'new_lines'    => 'br',
					'placeholder'  => 'Discover how SmartHome Pro transforms your living space.',
				),

				// Features Repeater.
				array(
					'key'          => 'field_shp_features_repeater',
					'label'        => 'Features',
					'name'         => 'features_repeater',
					'type'         => 'repeater',
					'instructions' => 'Add individual feature cards.',
					'min'          => 1,
					'layout'       => 'block',
					'button_label' => 'Add Feature',
					'sub_fields'   => array(
						array(
							'key'           => 'field_shp_feature_icon',
							'label'         => 'Feature Icon',
							'name'          => 'feature_icon',
							'type'          => 'image',
							'instructions'  => 'Upload an icon image for this feature card. SVG, PNG, or WebP recommended.',
							'required'      => 1,
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
							'library'       => 'all',
							'mime_types'    => 'svg,png,jpg,jpeg,webp',
						),
						array(
							'key'          => 'field_shp_feature_title',
							'label'        => 'Feature Title',
							'name'         => 'feature_title',
							'type'         => 'text',
							'instructions' => 'Title for this feature card.',
							'placeholder'  => 'Smart Automation',
							'required'     => 1,
						),
						array(
							'key'          => 'field_shp_feature_description',
							'label'        => 'Feature Description',
							'name'         => 'feature_description',
							'type'         => 'textarea',
							'instructions' => 'Brief description of this feature.',
							'rows'         => 3,
							'new_lines'    => 'br',
							'placeholder'  => 'Automate your home with intelligent routines.',
						),
						array(
							'key'           => 'field_shp_feature_link',
							'label'         => 'Feature Link',
							'name'          => 'feature_link',
							'type'          => 'link',
							'instructions'  => 'Optional link for "Learn More".',
							'return_format' => 'array',
						),
					),
				),

				/*
				 * -----------------------------------------------------------------
				 * Testimonials Tab
				 * -----------------------------------------------------------------
				 */
				array(
					'key'   => 'field_shp_testimonials_tab',
					'label' => 'Testimonials Section',
					'name'  => '',
					'type'  => 'tab',
					'placement' => 'top',
				),

				// Testimonials Badge.
				array(
					'key'           => 'field_shp_testimonials_badge',
					'label'         => 'Section Badge',
					'name'          => 'testimonials_badge',
					'type'          => 'text',
					'instructions'  => 'Small badge text above the section heading.',
					'placeholder'   => 'Testimonials',
					'default_value' => 'Testimonials',
				),

				// Testimonials Heading.
				array(
					'key'          => 'field_shp_testimonials_heading',
					'label'        => 'Section Heading',
					'name'         => 'testimonials_heading',
					'type'         => 'text',
					'instructions' => 'Main heading for the testimonials section.',
					'required'     => 1,
					'placeholder'  => 'What Our Customers Say',
				),

				// Testimonials Description.
				array(
					'key'          => 'field_shp_testimonials_description',
					'label'        => 'Section Description',
					'name'         => 'testimonials_description',
					'type'         => 'textarea',
					'instructions' => 'Supporting text below the testimonials heading.',
					'rows'         => 3,
					'new_lines'    => 'br',
					'placeholder'  => 'Hear from homeowners who transformed their living spaces.',
				),

				// Testimonials Repeater.
				array(
					'key'          => 'field_shp_testimonials_repeater',
					'label'        => 'Testimonials',
					'name'         => 'testimonials_repeater',
					'type'         => 'repeater',
					'instructions' => 'Add customer testimonials.',
					'min'          => 1,
					'layout'       => 'block',
					'button_label' => 'Add Testimonial',
					'sub_fields'   => array(
						array(
							'key'           => 'field_shp_customer_image',
							'label'         => 'Customer Photo',
							'name'          => 'customer_image',
							'type'          => 'image',
							'instructions'  => 'Upload a customer avatar. Recommended: 100×100 px.',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
							'library'       => 'all',
							'mime_types'    => 'png,jpg,jpeg,webp',
						),
						array(
							'key'          => 'field_shp_customer_name',
							'label'        => 'Customer Name',
							'name'         => 'customer_name',
							'type'         => 'text',
							'placeholder'  => 'Jane Doe',
							'required'     => 1,
						),
						array(
							'key'          => 'field_shp_customer_designation',
							'label'        => 'Customer Designation',
							'name'         => 'customer_designation',
							'type'         => 'text',
							'instructions' => 'Job title or role.',
							'placeholder'  => 'Homeowner',
						),
						array(
							'key'           => 'field_shp_customer_rating',
							'label'         => 'Rating',
							'name'          => 'customer_rating',
							'type'          => 'number',
							'instructions'  => 'Star rating from 1 to 5.',
							'required'      => 1,
							'default_value' => 5,
							'min'           => 1,
							'max'           => 5,
							'step'          => 1,
						),
						array(
							'key'          => 'field_shp_customer_review',
							'label'        => 'Review Text',
							'name'         => 'customer_review',
							'type'         => 'textarea',
							'instructions' => 'The customer\'s testimonial text.',
							'rows'         => 4,
							'new_lines'    => 'br',
							'required'     => 1,
							'placeholder'  => 'SmartHome Pro completely transformed our home...',
						),
					),
				),

				/*
				 * -----------------------------------------------------------------
				 * Pricing Tab
				 * -----------------------------------------------------------------
				 */
				array(
					'key'   => 'field_shp_pricing_tab',
					'label' => 'Pricing Section',
					'name'  => '',
					'type'  => 'tab',
					'placement' => 'top',
				),

				// Pricing Badge.
				array(
					'key'           => 'field_shp_pricing_badge',
					'label'         => 'Section Badge',
					'name'          => 'pricing_badge',
					'type'          => 'text',
					'instructions'  => 'Small badge text above the section heading.',
					'placeholder'   => 'Pricing',
					'default_value' => 'Pricing',
				),

				// Pricing Heading.
				array(
					'key'          => 'field_shp_pricing_heading',
					'label'        => 'Section Heading',
					'name'         => 'pricing_heading',
					'type'         => 'text',
					'instructions' => 'Main heading for the pricing section.',
					'required'     => 1,
					'placeholder'  => 'Simple, Transparent Pricing',
				),

				// Pricing Description.
				array(
					'key'          => 'field_shp_pricing_description',
					'label'        => 'Section Description',
					'name'         => 'pricing_description',
					'type'         => 'textarea',
					'instructions' => 'Supporting text below the pricing heading.',
					'rows'         => 3,
					'new_lines'    => 'br',
					'placeholder'  => 'Choose the plan that fits your home.',
				),

				// Pricing Repeater.
				array(
					'key'          => 'field_shp_pricing_repeater',
					'label'        => 'Pricing Plans',
					'name'         => 'pricing_repeater',
					'type'         => 'repeater',
					'instructions' => 'Add up to 4 pricing plans.',
					'min'          => 1,
					'max'          => 4,
					'layout'       => 'block',
					'button_label' => 'Add Plan',
					'sub_fields'   => array(
						array(
							'key'          => 'field_shp_plan_name',
							'label'        => 'Plan Name',
							'name'         => 'plan_name',
							'type'         => 'text',
							'placeholder'  => 'Professional',
							'required'     => 1,
						),
						array(
							'key'          => 'field_shp_plan_price',
							'label'        => 'Plan Price',
							'name'         => 'plan_price',
							'type'         => 'text',
							'instructions' => 'Price amount including currency symbol, e.g. $29',
							'placeholder'  => '$29',
							'required'     => 1,
						),
						array(
							'key'           => 'field_shp_plan_duration',
							'label'         => 'Plan Duration',
							'name'          => 'plan_duration',
							'type'          => 'text',
							'instructions'  => 'Billing cycle text, e.g. /month, /year',
							'placeholder'   => '/month',
							'default_value' => '/month',
						),
						array(
							'key'          => 'field_shp_plan_features',
							'label'        => 'Plan Features',
							'name'         => 'plan_features',
							'type'         => 'textarea',
							'instructions' => 'One feature per line. Each line will be displayed as a checklist item.',
							'rows'         => 6,
							'new_lines'    => '',
							'placeholder'  => "Up to 10 devices\nBasic automation\nEmail support\n7-day free trial",
						),
						array(
							'key'           => 'field_shp_plan_button_text',
							'label'         => 'Button Text',
							'name'          => 'plan_button_text',
							'type'          => 'text',
							'placeholder'   => 'Get Started',
							'default_value' => 'Get Started',
						),
						array(
							'key'           => 'field_shp_plan_button_url',
							'label'         => 'Button Link',
							'name'          => 'plan_button_url',
							'type'          => 'link',
							'instructions'  => 'Link for the plan button.',
							'return_format' => 'array',
						),
						array(
							'key'           => 'field_shp_is_featured',
							'label'         => 'Featured Plan?',
							'name'          => 'is_featured',
							'type'          => 'true_false',
							'instructions'  => 'Mark this plan as featured/recommended to highlight it.',
							'default_value' => 0,
							'ui'            => 1,
							'ui_on_text'    => 'Yes',
							'ui_off_text'   => 'No',
						),
					),
				),

				/*
				 * -----------------------------------------------------------------
				 * Contact Tab
				 * -----------------------------------------------------------------
				 */
				array(
					'key'   => 'field_shp_contact_tab',
					'label' => 'Contact Section',
					'name'  => '',
					'type'  => 'tab',
					'placement' => 'top',
				),

				// Contact Badge.
				array(
					'key'           => 'field_shp_contact_badge',
					'label'         => 'Section Badge',
					'name'          => 'contact_badge',
					'type'          => 'text',
					'instructions'  => 'Small badge text above the section heading.',
					'placeholder'   => 'Contact Us',
					'default_value' => 'Contact Us',
				),

				// Contact Heading.
				array(
					'key'          => 'field_shp_contact_heading',
					'label'        => 'Section Heading',
					'name'         => 'contact_heading',
					'type'         => 'text',
					'instructions' => 'Main heading for the contact section.',
					'required'     => 1,
					'placeholder'  => 'Get in Touch',
				),

				// Contact Description.
				array(
					'key'          => 'field_shp_contact_description',
					'label'        => 'Section Description',
					'name'         => 'contact_description',
					'type'         => 'textarea',
					'instructions' => 'Supporting text below the contact heading.',
					'rows'         => 3,
					'new_lines'    => 'br',
					'placeholder'  => 'Have questions? We\'d love to hear from you.',
				),

				// Contact Benefits Repeater.
				array(
					'key'          => 'field_shp_contact_benefits_repeater',
					'label'        => 'Contact Benefits',
					'name'         => 'contact_benefits_repeater',
					'type'         => 'repeater',
					'instructions' => 'List of benefits displayed alongside the contact form.',
					'min'          => 1,
					'layout'       => 'table',
					'button_label' => 'Add Benefit',
					'sub_fields'   => array(
						array(
							'key'           => 'field_shp_benefit_icon',
							'label'         => 'Icon',
							'name'          => 'benefit_icon',
							'type'          => 'image',
							'instructions'  => 'Upload an icon image for this benefit. SVG, PNG, or WebP recommended.',
							'required'      => 1,
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
							'library'       => 'all',
							'mime_types'    => 'svg,png,jpg,jpeg,webp',
						),
						array(
							'key'          => 'field_shp_benefit_text',
							'label'        => 'Benefit Text',
							'name'         => 'benefit_text',
							'type'         => 'text',
							'placeholder'  => '24/7 Expert Support',
							'required'     => 1,
						),
					),
				),

				// Contact Form Shortcode.
				array(
					'key'          => 'field_shp_contact_form_shortcode',
					'label'        => 'Contact Form Shortcode',
					'name'         => 'contact_form_shortcode',
					'type'         => 'text',
					'instructions' => 'Paste your Contact Form 7 shortcode here, e.g. <code>[contact-form-7 id="123" title="Contact Form"]</code>',
					'placeholder'  => '[contact-form-7 id="123" title="Contact Form"]',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-smarthome-pro.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	/*
	 * =========================================================================
	 * Field Group 3: Footer Settings (Options Sub-Page)
	 * =========================================================================
	 */
	acf_add_local_field_group(
		array(
			'key'      => 'group_shp_footer',
			'title'    => 'Footer Settings',
			'fields'   => array(

				// ---- Footer Logo ----
				array(
					'key'           => 'field_shp_footer_logo',
					'label'         => 'Footer Logo',
					'name'          => 'footer_logo',
					'type'          => 'image',
					'instructions'  => 'Upload a logo for the footer area. Use a light version for dark backgrounds.',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'library'       => 'all',
					'mime_types'    => 'png,jpg,jpeg,svg,webp',
				),

				// ---- Footer Description ----
				array(
					'key'          => 'field_shp_footer_description',
					'label'        => 'Footer Description',
					'name'         => 'footer_description',
					'type'         => 'textarea',
					'instructions' => 'Short description displayed below the footer logo.',
					'rows'         => 3,
					'new_lines'    => 'br',
					'placeholder'  => 'Making homes smarter, safer, and more efficient with cutting-edge IoT technology.',
				),

				// ---- Newsletter Heading ----
				array(
					'key'           => 'field_shp_footer_newsletter_heading',
					'label'         => 'Newsletter Heading',
					'name'          => 'footer_newsletter_heading',
					'type'          => 'text',
					'instructions'  => 'Heading for the newsletter signup section.',
					'placeholder'   => 'Stay Updated',
					'default_value' => 'Stay Updated',
				),

				// ---- Newsletter Description ----
				array(
					'key'          => 'field_shp_footer_newsletter_description',
					'label'        => 'Newsletter Description',
					'name'         => 'footer_newsletter_description',
					'type'         => 'textarea',
					'instructions' => 'Text displayed above the newsletter form.',
					'rows'         => 2,
					'new_lines'    => 'br',
					'placeholder'  => 'Subscribe to our newsletter for the latest updates and smart home tips.',
				),

				// ---- Newsletter Shortcode ----
				array(
					'key'          => 'field_shp_footer_newsletter_shortcode',
					'label'        => 'Newsletter Form Shortcode',
					'name'         => 'footer_newsletter_shortcode',
					'type'         => 'text',
					'instructions' => 'Paste your CF7 newsletter shortcode here, e.g. <code>[contact-form-7 id="456" title="Newsletter"]</code>',
					'placeholder'  => '[contact-form-7 id="456" title="Newsletter"]',
				),

				// ---- Contact Phone ----
				array(
					'key'          => 'field_shp_footer_contact_phone',
					'label'        => 'Contact Phone',
					'name'         => 'footer_contact_phone',
					'type'         => 'text',
					'instructions' => 'Phone number displayed in the footer.',
					'placeholder'  => '+1 (555) 123-4567',
				),

				// ---- Contact Email ----
				array(
					'key'          => 'field_shp_footer_contact_email',
					'label'        => 'Contact Email',
					'name'         => 'footer_contact_email',
					'type'         => 'email',
					'instructions' => 'Email address displayed in the footer.',
					'placeholder'  => 'hello@smarthomepro.com',
				),

				// ---- Contact Address ----
				array(
					'key'          => 'field_shp_footer_contact_address',
					'label'        => 'Contact Address',
					'name'         => 'footer_contact_address',
					'type'         => 'textarea',
					'instructions' => 'Physical address displayed in the footer.',
					'rows'         => 2,
					'new_lines'    => 'br',
					'placeholder'  => '123 Smart Street, Tech City, TC 12345',
				),

				// ---- Social Links Repeater ----
				array(
					'key'          => 'field_shp_social_links_repeater',
					'label'        => 'Social Media Links',
					'name'         => 'social_links_repeater',
					'type'         => 'repeater',
					'instructions' => 'Add social media profile links.',
					'layout'       => 'table',
					'button_label' => 'Add Social Link',
					'sub_fields'   => array(
						array(
							'key'          => 'field_shp_social_icon',
							'label'        => 'Icon',
							'name'         => 'social_icon',
							'type'         => 'text',
							'instructions' => 'Bootstrap Icon class, e.g. <code>bi-facebook</code>, <code>bi-twitter-x</code>, <code>bi-linkedin</code>',
							'placeholder'  => 'bi-facebook',
							'required'     => 1,
						),
						array(
							'key'           => 'field_shp_social_url',
							'label'         => 'URL',
							'name'          => 'social_url',
							'type'          => 'link',
							'instructions'  => 'Link to the social media profile.',
							'return_format' => 'array',
							'required'      => 1,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'acf-options-footer-settings',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'smarthome_pro_acf_register_fields' );
