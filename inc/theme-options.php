<?php
/**
 * Theme Options — native WP admin page.
 * Manages homepage banner slides (image, mobile image, heading, subheading,
 * link, link target, alt text) with drag-to-reorder.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ALISTCLUB_OPTIONS_KEY  = 'alistclub_theme_options';
const ALISTCLUB_OPTIONS_PAGE = 'alistclub-theme-options';

/**
 * Default options.
 */
function alistclub_default_options() {
	return array(
		'banner_autoplay' => 1,
		'banner_interval' => 5,
		'banners'         => array(),
	);
}

/**
 * Get all options merged with defaults.
 */
function alistclub_get_options() {
	$saved = get_option( ALISTCLUB_OPTIONS_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return array_merge( alistclub_default_options(), $saved );
}

/**
 * Get banner array for front-end use.
 */
function alistclub_get_banners() {
	$opts = alistclub_get_options();
	return is_array( $opts['banners'] ) ? $opts['banners'] : array();
}

/**
 * Get banner display settings.
 */
function alistclub_get_banner_settings() {
	$opts = alistclub_get_options();
	$interval = (int) $opts['banner_interval'];
	if ( $interval < 2 ) {
		$interval = 5;
	}
	return array(
		'autoplay' => ! empty( $opts['banner_autoplay'] ),
		'interval' => $interval,
	);
}

/**
 * Register admin menu.
 */
function alistclub_register_options_menu() {
	add_theme_page(
		__( 'Theme Options', 'alistclub' ),
		__( 'Theme Options', 'alistclub' ),
		'edit_theme_options',
		ALISTCLUB_OPTIONS_PAGE,
		'alistclub_render_options_page'
	);
}
add_action( 'admin_menu', 'alistclub_register_options_menu' );

/**
 * Register the setting + sanitizer.
 */
function alistclub_register_settings() {
	register_setting(
		'alistclub_options_group',
		ALISTCLUB_OPTIONS_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'alistclub_sanitize_options',
			'default'           => alistclub_default_options(),
		)
	);
}
add_action( 'admin_init', 'alistclub_register_settings' );

/**
 * Sanitize submitted options.
 */
function alistclub_sanitize_options( $input ) {
	$out = alistclub_default_options();

	$out['banner_autoplay'] = ! empty( $input['banner_autoplay'] ) ? 1 : 0;
	$out['banner_interval'] = isset( $input['banner_interval'] ) ? max( 2, min( 30, (int) $input['banner_interval'] ) ) : 5;

	$banners = array();
	if ( ! empty( $input['banners'] ) && is_array( $input['banners'] ) ) {
		foreach ( $input['banners'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$image_id = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;
			if ( ! $image_id ) {
				continue; // require an image
			}
			$banners[] = array(
				'image_id'        => $image_id,
				'image_mobile_id' => isset( $row['image_mobile_id'] ) ? (int) $row['image_mobile_id'] : 0,
				'heading'         => isset( $row['heading'] ) ? sanitize_text_field( $row['heading'] ) : '',
				'subheading'      => isset( $row['subheading'] ) ? sanitize_text_field( $row['subheading'] ) : '',
				'link_url'        => isset( $row['link_url'] ) ? esc_url_raw( trim( $row['link_url'] ) ) : '',
				'link_target'     => ( isset( $row['link_target'] ) && '_blank' === $row['link_target'] ) ? '_blank' : '_self',
				'alt'             => isset( $row['alt'] ) ? sanitize_text_field( $row['alt'] ) : '',
			);
		}
	}
	$out['banners'] = $banners;

	return $out;
}

/**
 * Enqueue admin assets only on our options page.
 */
function alistclub_options_admin_assets( $hook ) {
	if ( 'appearance_page_' . ALISTCLUB_OPTIONS_PAGE !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_style(
		'alistclub-options',
		get_theme_file_uri( 'assets/css/admin-options.css' ),
		array(),
		filemtime( get_theme_file_path( 'assets/css/admin-options.css' ) )
	);
	wp_enqueue_script(
		'alistclub-options',
		get_theme_file_uri( 'assets/js/admin-options.js' ),
		array( 'jquery', 'jquery-ui-sortable' ),
		filemtime( get_theme_file_path( 'assets/js/admin-options.js' ) ),
		true
	);
	wp_localize_script( 'alistclub-options', 'alistclubOptions', array(
		'mediaTitle'  => __( 'Select Banner Image', 'alistclub' ),
		'mediaButton' => __( 'Use this image', 'alistclub' ),
		'removeText'  => __( 'Remove image', 'alistclub' ),
		'confirmRemove' => __( 'Remove this banner?', 'alistclub' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'alistclub_options_admin_assets' );

/**
 * Render the options page.
 */
function alistclub_render_options_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$opts    = alistclub_get_options();
	$banners = is_array( $opts['banners'] ) ? $opts['banners'] : array();
	?>
	<div class="wrap alistclub-options">
		<h1><?php esc_html_e( 'A-List Theme Options', 'alistclub' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'alistclub_options_group' ); ?>

			<section class="alistclub-section" aria-labelledby="alistclub-banner-section-title">
				<header class="alistclub-section__header">
					<h2 id="alistclub-banner-section-title" class="alistclub-section__title">
						<span class="dashicons dashicons-images-alt2" aria-hidden="true"></span>
						<?php esc_html_e( 'Homepage Banner Carousel', 'alistclub' ); ?>
					</h2>
					<p class="alistclub-section__description">
						<?php esc_html_e( 'Manage the rotating banners that appear at the top of the homepage. Each banner can link to a product, page, or external URL.', 'alistclub' ); ?>
					</p>
				</header>

				<div class="alistclub-section__body">
					<div class="alistclub-section__group">
						<h3 class="alistclub-section__group-title"><?php esc_html_e( 'Display settings', 'alistclub' ); ?></h3>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="alistclub_banner_autoplay"><?php esc_html_e( 'Auto-rotate slides', 'alistclub' ); ?></label></th>
								<td>
									<label>
										<input type="checkbox" id="alistclub_banner_autoplay"
											name="<?php echo esc_attr( ALISTCLUB_OPTIONS_KEY ); ?>[banner_autoplay]"
											value="1" <?php checked( ! empty( $opts['banner_autoplay'] ) ); ?>>
										<?php esc_html_e( 'Automatically advance the banner', 'alistclub' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="alistclub_banner_interval"><?php esc_html_e( 'Interval (seconds)', 'alistclub' ); ?></label></th>
								<td>
									<input type="number" id="alistclub_banner_interval"
										name="<?php echo esc_attr( ALISTCLUB_OPTIONS_KEY ); ?>[banner_interval]"
										value="<?php echo esc_attr( (int) $opts['banner_interval'] ); ?>"
										min="2" max="30" class="small-text">
								</td>
							</tr>
						</table>
					</div>

					<div class="alistclub-section__group">
						<h3 class="alistclub-section__group-title"><?php esc_html_e( 'Banners', 'alistclub' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Drag the handle (☰) on a row to reorder. Each banner appears in this order on the homepage.', 'alistclub' ); ?></p>

						<div id="alistclub-banner-list" class="alistclub-banner-list">
							<?php
							if ( ! empty( $banners ) ) {
								foreach ( $banners as $i => $banner ) {
									alistclub_render_banner_row( $i, $banner );
								}
							}
							?>
						</div>

						<p class="alistclub-section__add">
							<button type="button" class="button button-secondary" id="alistclub-add-banner">
								<span class="dashicons dashicons-plus" aria-hidden="true"></span>
								<?php esc_html_e( 'Add Banner', 'alistclub' ); ?>
							</button>
						</p>

						<script type="text/template" id="alistclub-banner-template">
							<?php alistclub_render_banner_row( '__INDEX__', array() ); ?>
						</script>
					</div>
				</div>
			</section>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Render a single banner row (used for both saved rows and the JS template).
 */
function alistclub_render_banner_row( $index, $banner ) {
	$image_id   = isset( $banner['image_id'] ) ? (int) $banner['image_id'] : 0;
	$mobile_id  = isset( $banner['image_mobile_id'] ) ? (int) $banner['image_mobile_id'] : 0;
	$heading    = isset( $banner['heading'] ) ? $banner['heading'] : '';
	$subheading = isset( $banner['subheading'] ) ? $banner['subheading'] : '';
	$link_url   = isset( $banner['link_url'] ) ? $banner['link_url'] : '';
	$link_tgt   = isset( $banner['link_target'] ) ? $banner['link_target'] : '_self';
	$alt        = isset( $banner['alt'] ) ? $banner['alt'] : '';

	$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	$mobile_url = $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'medium' ) : '';

	$name_base = esc_attr( ALISTCLUB_OPTIONS_KEY ) . '[banners][' . esc_attr( $index ) . ']';
	?>
	<div class="alistclub-banner-row" data-index="<?php echo esc_attr( $index ); ?>">
		<div class="alistclub-banner-handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'alistclub' ); ?>">☰</div>

		<div class="alistclub-banner-media">
			<div class="alistclub-image-field">
				<label><?php esc_html_e( 'Banner image', 'alistclub' ); ?></label>
				<div class="alistclub-image-preview <?php echo $image_url ? 'has-image' : ''; ?>">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="">
					<?php endif; ?>
				</div>
				<input type="hidden" class="alistclub-image-id" name="<?php echo $name_base; ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>">
				<button type="button" class="button alistclub-image-select"><?php esc_html_e( 'Choose image', 'alistclub' ); ?></button>
				<button type="button" class="button alistclub-image-remove" <?php echo $image_url ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'alistclub' ); ?></button>
			</div>

			<div class="alistclub-image-field">
				<label><?php esc_html_e( 'Mobile image (optional)', 'alistclub' ); ?></label>
				<div class="alistclub-image-preview <?php echo $mobile_url ? 'has-image' : ''; ?>">
					<?php if ( $mobile_url ) : ?>
						<img src="<?php echo esc_url( $mobile_url ); ?>" alt="">
					<?php endif; ?>
				</div>
				<input type="hidden" class="alistclub-image-id" name="<?php echo $name_base; ?>[image_mobile_id]" value="<?php echo esc_attr( $mobile_id ); ?>">
				<button type="button" class="button alistclub-image-select"><?php esc_html_e( 'Choose image', 'alistclub' ); ?></button>
				<button type="button" class="button alistclub-image-remove" <?php echo $mobile_url ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'alistclub' ); ?></button>
			</div>
		</div>

		<div class="alistclub-banner-fields">
			<p>
				<label><?php esc_html_e( 'Heading (optional)', 'alistclub' ); ?>
					<input type="text" name="<?php echo $name_base; ?>[heading]" value="<?php echo esc_attr( $heading ); ?>" class="regular-text">
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Subheading (optional)', 'alistclub' ); ?>
					<input type="text" name="<?php echo $name_base; ?>[subheading]" value="<?php echo esc_attr( $subheading ); ?>" class="regular-text">
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Link URL', 'alistclub' ); ?>
					<input type="url" name="<?php echo $name_base; ?>[link_url]" value="<?php echo esc_attr( $link_url ); ?>" class="regular-text" placeholder="https://example.com or /shop">
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Link target', 'alistclub' ); ?>
					<select name="<?php echo $name_base; ?>[link_target]">
						<option value="_self"  <?php selected( $link_tgt, '_self' ); ?>><?php esc_html_e( 'Same window', 'alistclub' ); ?></option>
						<option value="_blank" <?php selected( $link_tgt, '_blank' ); ?>><?php esc_html_e( 'New window', 'alistclub' ); ?></option>
					</select>
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Alt text (accessibility)', 'alistclub' ); ?>
					<input type="text" name="<?php echo $name_base; ?>[alt]" value="<?php echo esc_attr( $alt ); ?>" class="regular-text">
				</label>
			</p>
		</div>

		<div class="alistclub-banner-actions">
			<button type="button" class="button-link-delete alistclub-banner-delete"><?php esc_html_e( 'Delete banner', 'alistclub' ); ?></button>
		</div>
	</div>
	<?php
}
