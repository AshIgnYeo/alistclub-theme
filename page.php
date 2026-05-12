<?php
$is_account = function_exists( 'is_account_page' ) && is_account_page();
$main_class = $is_account ? 'is-account' : '';

get_header();
get_template_part( 'template-parts/section', 'nav' );
if ( ! $is_account ) {
	get_template_part( 'template-parts/section', 'banner' );
}
?>
<main id="page-content" tabindex="-1" class="<?php echo esc_attr( $main_class ); ?>">
	<div class="container">
		<div class="row">
			<div class="col-full">
				<?php
				$content_type = get_post_field( 'post_name', get_the_ID() );
				$slug_part    = sanitize_file_name( $content_type );
				if ( $slug_part && locate_template( 'template-parts/content-' . $slug_part . '.php' ) ) {
					get_template_part( 'template-parts/content', $slug_part );
				} else {
					get_template_part( 'template-parts/content', 'page' );
				}
				?>
			</div>
		</div>
	</div>
</main>

<?php
get_template_part( 'template-parts/section', 'footer' );
get_footer();
