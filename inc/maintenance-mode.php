<?php
/**
 * Maintenance mode — driven by the theme option toggle
 * (A-List Club → Settings → Maintenance Mode).
 *
 * Themes load after WP's own wp_maintenance() check, so we short-circuit at
 * template_redirect instead. wp-admin and wp-login.php are unaffected, and
 * users with manage_options keep seeing the live site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', 'alistclub_maintenance_gate', 0 );
function alistclub_maintenance_gate() {
	if ( is_admin() ) {
		return;
	}

	$config = alistclub_get_maintenance_config();
	if ( empty( $config['enabled'] ) ) {
		return;
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	alistclub_render_maintenance_page( $config );
	exit;
}

function alistclub_render_maintenance_page( $config ) {
	$heading = $config['heading'] !== '' ? $config['heading'] : __( 'We&rsquo;ll be right back', 'alistclub' );
	$message = $config['message'] !== '' ? $config['message'] : __( 'The site is briefly offline for maintenance. Please check back shortly.', 'alistclub' );
	$site    = get_bloginfo( 'name' );

	nocache_headers();
	status_header( 503 );
	header( 'Retry-After: 3600' );
	header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );

	?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?php echo esc_html( $heading . ' — ' . $site ); ?></title>
<style>
	:root { --bg:#fdfaf6; --fg:#1a1a1a; --muted:#6b6257; --accent:#111; }
	*,*::before,*::after { box-sizing:border-box; }
	html,body { margin:0; padding:0; height:100%; }
	body { background:var(--bg); color:var(--fg); font-family:Georgia,'Times New Roman',serif; line-height:1.55; -webkit-font-smoothing:antialiased; }
	.wrap { min-height:100%; display:flex; align-items:center; justify-content:center; padding:2rem 1.25rem; }
	.card { max-width:560px; width:100%; text-align:center; }
	.brand { font-family:'Helvetica Neue',Arial,sans-serif; font-size:.75rem; letter-spacing:.3em; text-transform:uppercase; color:var(--muted); margin-bottom:2.5rem; }
	h1 { font-size:clamp(2rem, 5vw, 3rem); margin:0 0 1rem; font-weight:400; letter-spacing:-.01em; }
	.message { font-size:1.0625rem; color:var(--muted); }
	.message p { margin:.6em 0; }
	.message a { color:var(--accent); }
</style>
</head>
<body>
	<main class="wrap">
		<div class="card">
			<div class="brand"><?php echo esc_html( $site ); ?></div>
			<h1><?php echo wp_kses_post( $heading ); ?></h1>
			<div class="message"><?php echo wp_kses_post( wpautop( $message ) ); ?></div>
		</div>
	</main>
</body>
</html><?php
}
