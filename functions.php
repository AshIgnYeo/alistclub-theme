<?php
/**
 * A-List Club theme functions
 */

define( 'ALISTCLUB_THEME_VERSION', '1.1.0' );

/**
 * Enqueue front-end styles and scripts.
 */
function alistclub_styles_and_scripts() {
	$theme_dir = get_stylesheet_directory();

	wp_enqueue_style(
		'alistclub-main',
		get_stylesheet_uri(),
		array(),
		file_exists( $theme_dir . '/style.css' ) ? filemtime( $theme_dir . '/style.css' ) : ALISTCLUB_THEME_VERSION
	);

	wp_enqueue_style(
		'alistclub-modern',
		get_theme_file_uri( 'assets/css/modern.css' ),
		array( 'alistclub-main' ),
		file_exists( $theme_dir . '/assets/css/modern.css' ) ? filemtime( $theme_dir . '/assets/css/modern.css' ) : ALISTCLUB_THEME_VERSION
	);

	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
		array(),
		'6.5.2'
	);

	wp_enqueue_script(
		'alistclub-main',
		get_theme_file_uri( 'assets/js/Main.js' ),
		array(),
		file_exists( $theme_dir . '/assets/js/Main.js' ) ? filemtime( $theme_dir . '/assets/js/Main.js' ) : ALISTCLUB_THEME_VERSION,
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);

	if ( is_front_page() && class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'wc-add-to-cart' );
		wp_enqueue_script( 'wc-cart-fragments' );
	}

	wp_localize_script( 'alistclub-main', 'localData', array(
		'siteUrl'    => esc_url( get_site_url() ),
		'restUrl'    => esc_url_raw( rest_url( 'alistclub/v1/search' ) ),
		'productsUrl'=> esc_url_raw( rest_url( 'alistclub/v1/products' ) ),
		'restNonce'  => wp_create_nonce( 'wp_rest' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'alistclub_styles_and_scripts' );

/**
 * Theme support.
 */
function alistclub_add_theme_support() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
	) );

	add_image_size( 'homepage-banner', 1600, 400, true );

	register_nav_menus( array(
		'primary'    => __( 'Main Header Navigation', 'alistclub' ),
		'categories' => __( 'Sidebar Store Menu', 'alistclub' ),
		'footer'     => __( 'Footer Navigation', 'alistclub' ),
	) );
}
add_action( 'after_setup_theme', 'alistclub_add_theme_support' );

/**
 * WooCommerce theme support.
 */
function alistclub_add_woocommerce_support() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 300,
		'single_image_width'    => 600,
		'product_grid'          => array(
			'default_rows'    => 5,
			'min_rows'        => 1,
			'max_rows'        => 10,
			'default_columns' => 3,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'alistclub_add_woocommerce_support' );

/**
 * Load WooCommerce template overrides only when WC is active.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/wc-archive-modifications.php';
	require get_template_directory() . '/inc/wc-single-product-modifications.php';
	require get_template_directory() . '/inc/wc-cart-modifications.php';
}

/**
 * Custom REST search endpoint.
 */
require get_theme_file_path( '/inc/search-route.php' );

/**
 * Custom REST products endpoint (homepage Store grid).
 */
require get_theme_file_path( '/inc/products-route.php' );

/**
 * Theme options (native WP admin page).
 */
require get_theme_file_path( '/inc/theme-options.php' );
require get_theme_file_path( '/inc/maintenance-mode.php' );

/**
 * FAQ custom post type.
 */
require get_theme_file_path( '/inc/cpt-faq.php' );

/**
 * Redirect users to homepage after logout.
 */
function alistclub_redirect_after_logout() {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'wp_logout', 'alistclub_redirect_after_logout' );

/**
 * Inject Google Analytics (GA4) — set ALISTCLUB_GA_ID via wp-config.php
 * or replace the default below with your GA4 measurement ID (G-XXXXXXX).
 */
function alistclub_add_google_analytics_script() {
	$ga_id = defined( 'ALISTCLUB_GA_ID' ) ? ALISTCLUB_GA_ID : '';
	if ( empty( $ga_id ) ) {
		return;
	}
	$ga_id = esc_attr( $ga_id );
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga_id; ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo $ga_id; ?>');
	</script>
	<?php
}
add_action( 'wp_head', 'alistclub_add_google_analytics_script' );

/**
 * Custom WP login page brand link.
 */
function alistclub_login_header_url() {
	return esc_url( home_url( '/' ) );
}
add_filter( 'login_headerurl', 'alistclub_login_header_url' );

/**
 * Login screen styles.
 */
function alistclub_load_admin_css() {
	wp_enqueue_style(
		'alistclub-login',
		get_stylesheet_uri(),
		array(),
		ALISTCLUB_THEME_VERSION
	);
}
add_action( 'login_enqueue_scripts', 'alistclub_load_admin_css' );

/**
 * Custom new-user notification email.
 */
function alistclub_custom_new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
	$key = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return $wp_new_user_notification_email;
	}

	$link = network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user->user_login ), 'login' );

	$logo_url = esc_url( 'https://beautya-list.com/wp-content/uploads/2020/04/alist-logo-paypal.png' );

	$message  = '<img src="' . $logo_url . '" alt="Beauty A-List" /><br /><br />';
	$message .= '<h3>' . esc_html__( 'Beauty A-List has upgraded!', 'alistclub' ) . '</h3>';
	$message .= esc_html__( 'We understand there have been some technical issues lately and have upgraded to a new platform!', 'alistclub' ) . '<br />';
	$message .= esc_html__( 'We have migrated your account over to the new site and will need you to reset your password to ensure safe and secure browsing.', 'alistclub' ) . '<br /><br />';
	$message .= '<h4>' . esc_html__( 'Points', 'alistclub' ) . '</h4>';
	$message .= '<ol>';
	$message .= '<li>' . esc_html__( 'All existing points will be retained, however, they will be inaccessible for the time being.', 'alistclub' ) . '</li>';
	$message .= '<li>' . esc_html__( 'Any purchases made during this period will also be included into your total accumulated points.', 'alistclub' ) . '</li>';
	$message .= '</ol><br /><br />';
	$message .= '<h4>' . esc_html__( 'Account', 'alistclub' ) . '</h4>';
	$message .= sprintf( esc_html__( 'Username: %s', 'alistclub' ), esc_html( $user->user_login ) ) . '<br /><br />';
	$message .= esc_html__( 'To set your password, please click the following link:', 'alistclub' ) . '<br /><br />';
	$message .= '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Click here to set Password', 'alistclub' ) . '</a><br /><br />';

	$wp_new_user_notification_email = array(
		'to'      => $user->user_email,
		/* translators: %s: site name */
		'subject' => sprintf( __( '[%s] Login Details', 'alistclub' ), $blogname ),
		'message' => $message,
		'headers' => 'Content-Type: text/html;',
	);

	return $wp_new_user_notification_email;
}
add_filter( 'wp_new_user_notification_email', 'alistclub_custom_new_user_notification_email', 10, 3 );

/**
 * Add loading="lazy" / decoding="async" defaults are handled by WP core 5.5+/6.3+,
 * but ensure the first hero image doesn't lazy-load (LCP).
 */
function alistclub_skip_lazy_for_first_image( $value, $image, $context ) {
	static $first = true;
	if ( $first && 'the_content' === $context ) {
		$first = false;
		return false;
	}
	return $value;
}
add_filter( 'wp_img_tag_add_loading_attr', 'alistclub_skip_lazy_for_first_image', 10, 3 );
