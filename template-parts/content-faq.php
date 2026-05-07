<?php
/**
 * FAQ page content
 */
$faqContent = new WP_Query( [
	'post_type'      => 'faq',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
] );
?>

<div class="faq-page">
	<div class="faq-search">
		<label for="faq-search-input" class="screen-reader-text"><?php esc_html_e( 'Search FAQs', 'alistclub' ); ?></label>
		<input
			type="search"
			id="faq-search-input"
			class="faq-search__input"
			placeholder="<?php esc_attr_e( 'Search frequently asked questions…', 'alistclub' ); ?>"
			autocomplete="off"
			aria-controls="faq-list"
		/>
	</div>

	<p id="faq-empty" class="faq-empty" hidden><?php esc_html_e( 'No FAQs match your search.', 'alistclub' ); ?></p>

	<div id="faq-list" class="faq-list">
		<?php while ( $faqContent->have_posts() ) :
			$faqContent->the_post();
			$haystack = wp_strip_all_tags( get_the_title() . ' ' . get_the_content() );
			?>
			<div class="faq-item" data-faq-search="<?php echo esc_attr( strtolower( $haystack ) ); ?>">
				<h3><?php the_title(); ?></h3>
				<div class="faq-item__answer"><?php the_content(); ?></div>
			</div>
		<?php endwhile; ?>
	</div>
</div>
<?php wp_reset_postdata(); ?>

<script>
( function () {
	var input = document.getElementById( 'faq-search-input' );
	var list  = document.getElementById( 'faq-list' );
	var empty = document.getElementById( 'faq-empty' );
	if ( ! input || ! list ) { return; }

	var items = Array.prototype.slice.call( list.querySelectorAll( '.faq-item' ) );

	function filter() {
		var q = input.value.trim().toLowerCase();
		var visible = 0;
		items.forEach( function ( item ) {
			var match = q === '' || item.dataset.faqSearch.indexOf( q ) !== -1;
			item.hidden = ! match;
			if ( match ) { visible++; }
		} );
		empty.hidden = visible !== 0;
	}

	input.addEventListener( 'input', filter );
} )();
</script>
