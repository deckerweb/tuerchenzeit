<?php
/** Standalone canvas for an explicitly selected Gutenberg landing page. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="ttz-skip" href="#ttz-main"><?php esc_html_e( 'Skip to content', 'tuerchenzeit' ); ?></a>
<main id="ttz-main" class="ttz-landing-content">
<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
</main>
<?php wp_footer(); ?>
</body></html>
