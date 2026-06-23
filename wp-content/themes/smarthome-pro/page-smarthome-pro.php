<?php
/**
 * Template Name: SmartHome Pro Landing Page
 *
 * Full-width landing page template for SmartHome Pro.
 * Outputs the content sections using modular template parts.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" role="main">
	<?php get_template_part( 'template-parts/hero' ); ?>
	<?php get_template_part( 'template-parts/features' ); ?>
	<?php get_template_part( 'template-parts/testimonials' ); ?>
	<?php get_template_part( 'template-parts/pricing' ); ?>
	<?php get_template_part( 'template-parts/contact' ); ?>

<?php
get_footer();
