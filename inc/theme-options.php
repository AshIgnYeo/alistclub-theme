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
		'banner_autoplay'   => 1,
		'banner_interval'   => 5,
		'banners'           => array(),
		'flash_enabled'     => 0,
		'flash_message'     => '',
		'flash_show_once'   => 0,
		'flash_cookie_days' => 7,
		'flash_buttons'     => array(),
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
 * Get the flash notice config for the front end.
 * Returns null when disabled or there's nothing to show.
 */
function alistclub_get_flash_notice() {
	$opts = alistclub_get_options();
	if ( empty( $opts['flash_enabled'] ) ) {
		return null;
	}
	$message = isset( $opts['flash_message'] ) ? (string) $opts['flash_message'] : '';
	$buttons = isset( $opts['flash_buttons'] ) && is_array( $opts['flash_buttons'] ) ? $opts['flash_buttons'] : array();

	if ( '' === trim( wp_strip_all_tags( $message ) ) && empty( $buttons ) ) {
		return null;
	}

	$cookie_days = isset( $opts['flash_cookie_days'] ) ? (int) $opts['flash_cookie_days'] : 7;
	$show_once   = ! empty( $opts['flash_show_once'] );

	$version = substr( md5( wp_json_encode( array( $message, $buttons ) ) ), 0, 10 );

	return array(
		'message'     => $message,
		'buttons'     => $buttons,
		'show_once'   => $show_once,
		'cookie_days' => max( 0, min( 365, $cookie_days ) ),
		'version'     => $version,
	);
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

	// Flash Notice.
	$out['flash_enabled']     = ! empty( $input['flash_enabled'] ) ? 1 : 0;
	$out['flash_show_once']   = ! empty( $input['flash_show_once'] ) ? 1 : 0;
	$out['flash_cookie_days'] = isset( $input['flash_cookie_days'] ) ? max( 0, min( 365, (int) $input['flash_cookie_days'] ) ) : 7;
	$out['flash_message']     = isset( $input['flash_message'] ) ? wp_kses_post( $input['flash_message'] ) : '';

	$buttons = array();
	if ( ! empty( $input['flash_buttons'] ) && is_array( $input['flash_buttons'] ) ) {
		foreach ( $input['flash_buttons'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
			if ( '' === $label ) {
				continue;
			}
			$color = isset( $row['color'] ) ? sanitize_hex_color( $row['color'] ) : '';
			if ( ! $color ) {
				$color = '#111111';
			}
			$buttons[] = array(
				'label'  => $label,
				'color'  => $color,
				'url'    => isset( $row['url'] ) ? esc_url_raw( trim( $row['url'] ) ) : '',
				'target' => ( isset( $row['target'] ) && '_blank' === $row['target'] ) ? '_blank' : '_self',
			);
		}
	}
	$out['flash_buttons'] = $buttons;

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
		'confirmRemoveButton' => __( 'Remove this button?', 'alistclub' ),
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
		<form method="post" action="options.php" class="alistclub-options__layout">
			<?php settings_fields( 'alistclub_options_group' ); ?>

			<nav class="alistclub-options__nav" aria-label="<?php esc_attr_e( 'Theme options sections', 'alistclub' ); ?>">
				<ul>
					<li><a href="#section-banners" class="alistclub-nav-link is-active"><span class="dashicons dashicons-images-alt2" aria-hidden="true"></span><?php esc_html_e( 'Homepage Banner', 'alistclub' ); ?></a></li>
					<li><a href="#section-flash" class="alistclub-nav-link"><span class="dashicons dashicons-megaphone" aria-hidden="true"></span><?php esc_html_e( 'Flash Notice', 'alistclub' ); ?></a></li>
				</ul>
			</nav>

			<div class="alistclub-options__content">
			<section id="section-banners" class="alistclub-section" aria-labelledby="alistclub-banner-section-title">
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

			<?php alistclub_render_flash_notice_section( $opts ); ?>

			<?php submit_button(); ?>
			</div>
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

/**
 * Render the Flash Notice section.
 */
function alistclub_render_flash_notice_section( $opts ) {
	$enabled     = ! empty( $opts['flash_enabled'] );
	$message     = isset( $opts['flash_message'] ) ? (string) $opts['flash_message'] : '';
	$show_once   = ! empty( $opts['flash_show_once'] );
	$cookie_days = isset( $opts['flash_cookie_days'] ) ? (int) $opts['flash_cookie_days'] : 7;
	$buttons     = isset( $opts['flash_buttons'] ) && is_array( $opts['flash_buttons'] ) ? $opts['flash_buttons'] : array();
	$key         = ALISTCLUB_OPTIONS_KEY;
	?>
	<section id="section-flash" class="alistclub-section" aria-labelledby="alistclub-flash-section-title">
		<header class="alistclub-section__header">
			<h2 id="alistclub-flash-section-title" class="alistclub-section__title">
				<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>
				<?php esc_html_e( 'Flash Notice', 'alistclub' ); ?>
			</h2>
			<p class="alistclub-section__description">
				<?php esc_html_e( 'Show a site-wide modal popup with a message and optional action buttons. Dismissals are remembered via a cookie.', 'alistclub' ); ?>
			</p>
		</header>

		<div class="alistclub-section__body">
			<div class="alistclub-section__group">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Flash Notice', 'alistclub' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[flash_enabled]" value="1" <?php checked( $enabled ); ?>>
								<?php esc_html_e( 'Show the modal popup to visitors', 'alistclub' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Show once per visitor', 'alistclub' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[flash_show_once]" value="1" <?php checked( $show_once ); ?>>
								<?php esc_html_e( 'Once dismissed, do not show again (1 year cookie)', 'alistclub' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="alistclub_flash_cookie_days"><?php esc_html_e( 'Cookie lifetime (days)', 'alistclub' ); ?></label></th>
						<td>
							<input type="number" id="alistclub_flash_cookie_days"
								name="<?php echo esc_attr( $key ); ?>[flash_cookie_days]"
								value="<?php echo esc_attr( $cookie_days ); ?>"
								min="0" max="365" class="small-text">
							<p class="description"><?php esc_html_e( 'Used when "Show once" is off. 0 = session cookie (clears when browser closes). Editing the message or buttons resets the cookie for everyone.', 'alistclub' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<div class="alistclub-section__group">
				<h3 class="alistclub-section__group-title"><?php esc_html_e( 'Message', 'alistclub' ); ?></h3>
				<?php
				wp_editor(
					$message,
					'alistclub_flash_message',
					array(
						'textarea_name' => $key . '[flash_message]',
						'textarea_rows' => 8,
						'media_buttons' => true,
					)
				);
				?>
			</div>

			<div class="alistclub-section__group">
				<h3 class="alistclub-section__group-title"><?php esc_html_e( 'Action buttons', 'alistclub' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Buttons appear below the message inside the modal. Drag the handle (☰) to reorder.', 'alistclub' ); ?></p>

				<div id="alistclub-flash-button-list" class="alistclub-flash-button-list">
					<?php
					if ( ! empty( $buttons ) ) {
						foreach ( $buttons as $i => $btn ) {
							alistclub_render_flash_button_row( $i, $btn );
						}
					}
					?>
				</div>

				<p class="alistclub-section__add">
					<button type="button" class="button button-secondary" id="alistclub-add-flash-button">
						<span class="dashicons dashicons-plus" aria-hidden="true"></span>
						<?php esc_html_e( 'Add Button', 'alistclub' ); ?>
					</button>
				</p>

				<script type="text/template" id="alistclub-flash-button-template">
					<?php alistclub_render_flash_button_row( '__INDEX__', array() ); ?>
				</script>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render a single flash-notice button row.
 */
function alistclub_render_flash_button_row( $index, $btn ) {
	$label  = isset( $btn['label'] ) ? $btn['label'] : '';
	$color  = isset( $btn['color'] ) && $btn['color'] ? $btn['color'] : '#111111';
	$url    = isset( $btn['url'] ) ? $btn['url'] : '';
	$target = isset( $btn['target'] ) ? $btn['target'] : '_self';
	$name   = esc_attr( ALISTCLUB_OPTIONS_KEY ) . '[flash_buttons][' . esc_attr( $index ) . ']';
	?>
	<div class="alistclub-flash-button-row" data-index="<?php echo esc_attr( $index ); ?>">
		<div class="alistclub-banner-handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'alistclub' ); ?>">☰</div>

		<div class="alistclub-flash-button-fields">
			<p>
				<label><?php esc_html_e( 'Label', 'alistclub' ); ?>
					<input type="text" name="<?php echo $name; ?>[label]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Shop now', 'alistclub' ); ?>">
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'URL', 'alistclub' ); ?>
					<input type="url" name="<?php echo $name; ?>[url]" value="<?php echo esc_attr( $url ); ?>" class="regular-text" placeholder="https://example.com or /shop">
				</label>
			</p>
			<p class="alistclub-flash-button-inline">
				<label><?php esc_html_e( 'Color', 'alistclub' ); ?>
					<input type="color" name="<?php echo $name; ?>[color]" value="<?php echo esc_attr( $color ); ?>">
				</label>
				<label><?php esc_html_e( 'Target', 'alistclub' ); ?>
					<select name="<?php echo $name; ?>[target]">
						<option value="_self"  <?php selected( $target, '_self' ); ?>><?php esc_html_e( 'Same window', 'alistclub' ); ?></option>
						<option value="_blank" <?php selected( $target, '_blank' ); ?>><?php esc_html_e( 'New window', 'alistclub' ); ?></option>
					</select>
				</label>
			</p>
		</div>

		<div class="alistclub-banner-actions">
			<button type="button" class="button-link-delete alistclub-flash-button-delete"><?php esc_html_e( 'Delete button', 'alistclub' ); ?></button>
		</div>
	</div>
	<?php
}
