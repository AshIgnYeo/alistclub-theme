<section
	id="search"
	role="dialog"
	aria-modal="true"
	aria-labelledby="search-input-label"
	aria-hidden="true">
	<button type="button" id="close-search-overlay" aria-label="<?php esc_attr_e( 'Close search', 'alistclub' ); ?>">
		<i class="far fa-2x fa-times-circle" aria-hidden="true"></i>
	</button>
	<div class="search__input-wrapper">
		<label id="search-input-label" for="search-input"><?php esc_html_e( 'Search Beauty A-List', 'alistclub' ); ?></label>
		<input
			type="search"
			id="search-input"
			autocomplete="off"
			placeholder="<?php esc_attr_e( 'Start typing to search for something', 'alistclub' ); ?>">
	</div>
	<div id="search-results" aria-live="polite"></div>
</section>
