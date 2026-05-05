<?php
/**
 * Homepage banner carousel.
 * Banners are managed in WP Admin → Appearance → Theme Options.
 */

$banners  = function_exists( 'alistclub_get_banners' ) ? alistclub_get_banners() : array();
$settings = function_exists( 'alistclub_get_banner_settings' ) ? alistclub_get_banner_settings() : array( 'autoplay' => true, 'interval' => 5 );

if ( empty( $banners ) ) {
	return;
}

$slide_count = count( $banners );
?>
<section
	id="banner-carousel"
	class="banner-carousel"
	aria-roledescription="carousel"
	aria-label="<?php esc_attr_e( 'Homepage banners', 'alistclub' ); ?>"
	data-autoplay="<?php echo $settings['autoplay'] ? '1' : '0'; ?>"
	data-interval="<?php echo esc_attr( (int) $settings['interval'] * 1000 ); ?>">

	<div class="banner-carousel__track">
		<?php foreach ( $banners as $i => $b ) :
			$image_id  = isset( $b['image_id'] ) ? (int) $b['image_id'] : 0;
			$mobile_id = isset( $b['image_mobile_id'] ) ? (int) $b['image_mobile_id'] : 0;
			if ( ! $image_id ) {
				continue;
			}

			$desktop_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( ! $desktop_url ) {
				continue;
			}
			$mobile_url = $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'large' ) : '';

			$meta   = wp_get_attachment_metadata( $image_id );
			$width  = ! empty( $meta['width'] ) ? (int) $meta['width'] : 1600;
			$height = ! empty( $meta['height'] ) ? (int) $meta['height'] : 600;

			$heading    = isset( $b['heading'] ) ? (string) $b['heading'] : '';
			$subhead    = isset( $b['subheading'] ) ? (string) $b['subheading'] : '';
			$href       = isset( $b['link_url'] ) ? (string) $b['link_url'] : '';
			$target     = isset( $b['link_target'] ) ? (string) $b['link_target'] : '_self';
			$rel        = '_blank' === $target ? 'noopener noreferrer' : '';
			$alt_input  = isset( $b['alt'] ) ? (string) $b['alt'] : '';
			$attach_alt = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );

			$alt = $alt_input;
			if ( '' === $alt ) {
				$alt = $heading !== '' ? $heading : $attach_alt;
			}

			$is_first = ( 0 === $i );

			$srcset = wp_get_attachment_image_srcset( $image_id, 'full' );
			$sizes  = '(max-width: 739px) 100vw, 100vw';

			$picture  = '<picture>';
			if ( $mobile_url ) {
				$picture .= '<source media="(max-width: 739px)" srcset="' . esc_url( $mobile_url ) . '">';
			}
			$picture .= sprintf(
				'<img src="%1$s"%2$s sizes="%3$s" alt="%4$s" width="%5$d" height="%6$d" %7$s decoding="async">',
				esc_url( $desktop_url ),
				$srcset ? ' srcset="' . esc_attr( $srcset ) . '"' : '',
				esc_attr( $sizes ),
				esc_attr( $alt ),
				$width,
				$height,
				$is_first ? 'fetchpriority="high"' : 'loading="lazy"'
			);
			$picture .= '</picture>';

			$caption = '';
			if ( $heading !== '' || $subhead !== '' ) {
				$caption  = '<div class="banner-carousel__caption">';
				if ( $heading !== '' ) {
					$caption .= '<h2 class="banner-carousel__heading">' . esc_html( $heading ) . '</h2>';
				}
				if ( $subhead !== '' ) {
					$caption .= '<p class="banner-carousel__subheading">' . esc_html( $subhead ) . '</p>';
				}
				$caption .= '</div>';
			}

			$inner = $picture . $caption;
		?>
			<div
				class="banner-carousel__slide<?php echo $is_first ? ' is-active' : ''; ?>"
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr( sprintf( __( 'Slide %1$d of %2$d', 'alistclub' ), $i + 1, $slide_count ) ); ?>"
				<?php echo $is_first ? '' : 'aria-hidden="true"'; ?>>
				<?php
				if ( $href ) {
					printf(
						'<a class="banner-carousel__link" href="%1$s"%2$s%3$s>%4$s</a>',
						esc_url( $href ),
						$target ? ' target="' . esc_attr( $target ) . '"' : '',
						$rel ? ' rel="' . esc_attr( $rel ) . '"' : '',
						$inner
					);
				} else {
					echo $inner;
				}
				?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $slide_count > 1 ) : ?>
		<button type="button" class="banner-carousel__nav banner-carousel__prev" aria-label="<?php esc_attr_e( 'Previous slide', 'alistclub' ); ?>">
			<i class="fas fa-chevron-left" aria-hidden="true"></i>
		</button>
		<button type="button" class="banner-carousel__nav banner-carousel__next" aria-label="<?php esc_attr_e( 'Next slide', 'alistclub' ); ?>">
			<i class="fas fa-chevron-right" aria-hidden="true"></i>
		</button>

		<div class="banner-carousel__dots" role="tablist" aria-label="<?php esc_attr_e( 'Select slide', 'alistclub' ); ?>">
			<?php for ( $i = 0; $i < $slide_count; $i++ ) : ?>
				<button
					type="button"
					class="banner-carousel__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'alistclub' ), $i + 1 ) ); ?>"
					data-slide="<?php echo (int) $i; ?>"></button>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
</section>
