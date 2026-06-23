<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required theme files (the other being style.css).
 * WordPress requires this file to exist so it can recognise the theme.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" role="main">
	<div class="container" style="padding: 100px 0; min-height: 60vh;">

		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php
						if ( is_singular() ) :
							the_title( '<h1 class="entry-title">', '</h1>' );
						else :
							the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
						endif;
						?>
					</header>

					<div class="entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'smarthome-pro' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>
				</article>

			<?php endwhile; ?>

			<?php the_posts_navigation(); ?>

		<?php else : ?>

			<p><?php esc_html_e( 'No content found.', 'smarthome-pro' ); ?></p>

		<?php endif; ?>

	</div>

<?php
get_footer();
