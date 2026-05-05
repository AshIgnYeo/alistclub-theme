<?php
/**
 * Site header.
 */
$og_title = wp_get_document_title();
$og_desc  = is_singular() ? wp_strip_all_tags( get_the_excerpt() ) : get_bloginfo( 'description' );
$og_url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
$og_image = '';
if ( is_singular() && has_post_thumbnail() ) {
	$og_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
}
if ( empty( $og_image ) ) {
	$og_image = get_theme_file_uri( 'assets/images/alist-logo-paypal.png' );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#000000">

	<meta name="description" content="<?php echo esc_attr( $og_desc ); ?>">

	<meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $og_url ); ?>">
	<meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>">

	<meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>">
	<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
	<meta name="twitter:card" content="summary_large_image">

	<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#page-content"><?php esc_html_e( 'Skip to main content', 'alistclub' ); ?></a>
