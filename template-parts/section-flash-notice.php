<?php
/**
 * Flash Notice modal — site-wide popup with a configurable message and buttons.
 */

if ( ! function_exists( 'alistclub_get_flash_notice' ) ) {
	return;
}

$flash = alistclub_get_flash_notice();
if ( ! $flash ) {
	return;
}

$config = array(
	'showOnce'   => (bool) $flash['show_once'],
	'cookieDays' => (int) $flash['cookie_days'],
	'version'    => (string) $flash['version'],
);
?>
<section
	id="flash-notice"
	class="flash-notice"
	role="dialog"
	aria-modal="true"
	aria-labelledby="flash-notice-title"
	aria-hidden="true">
	<div class="flash-notice__dialog" role="document">
		<button type="button" class="flash-notice__close" id="flash-notice-close" aria-label="<?php esc_attr_e( 'Close notice', 'alistclub' ); ?>">
			<i class="far fa-2x fa-times-circle" aria-hidden="true"></i>
		</button>

		<h2 id="flash-notice-title" class="screen-reader-text"><?php esc_html_e( 'Notice', 'alistclub' ); ?></h2>

		<div class="flash-notice__message">
			<?php echo wp_kses_post( wpautop( $flash['message'] ) ); ?>
		</div>

		<?php if ( ! empty( $flash['buttons'] ) ) : ?>
			<div class="flash-notice__buttons">
				<?php foreach ( $flash['buttons'] as $btn ) :
					$href   = ! empty( $btn['url'] ) ? $btn['url'] : '#';
					$target = '_blank' === $btn['target'] ? '_blank' : '_self';
					$rel    = '_blank' === $target ? 'noopener noreferrer' : '';
					?>
					<a class="flash-notice__button"
						href="<?php echo esc_url( $href ); ?>"
						target="<?php echo esc_attr( $target ); ?>"
						<?php if ( $rel ) : ?>rel="<?php echo esc_attr( $rel ); ?>"<?php endif; ?>
						style="background-color: <?php echo esc_attr( $btn['color'] ); ?>;"
						data-flash-action>
						<?php echo esc_html( $btn['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<script type="application/json" id="flash-notice-config"><?php echo wp_json_encode( $config ); ?></script>
