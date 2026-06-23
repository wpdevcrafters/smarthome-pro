<?php
/**
 * Theme Header
 *
 * Standard WordPress header template that wraps the document head and
 * includes the header template part used on non-landing pages.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo esc_attr( get_bloginfo( 'description', 'display' ) ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'smarthome-pro' ); ?></a>

<?php get_template_part( 'template-parts/header' ); ?>
