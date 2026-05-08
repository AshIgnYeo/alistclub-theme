<?php
/**
 * Store sidebar — sort + filter controls.
 */

$brand_tax = function_exists( 'alistclub_brand_taxonomy' ) ? alistclub_brand_taxonomy() : '';
$brands    = $brand_tax ? get_terms( array(
	'taxonomy'   => $brand_tax,
	'hide_empty' => true,
) ) : array();
$cats = taxonomy_exists( 'product_cat' ) ? get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'exclude'    => array( get_option( 'default_product_cat' ) ),
) ) : array();
?>
<aside id="sidebar" aria-label="<?php esc_attr_e( 'Store filters', 'alistclub' ); ?>">
	<div class="sidebar-group">
		<h3 class="sidebar-heading" data-sort-label><?php esc_html_e( 'Sort by', 'alistclub' ); ?></h3>
		<ul class="sort-list">
			<li>
				<button type="button" class="sort-btn is-active" data-sort="all" data-sort-name="<?php esc_attr_e( 'Store', 'alistclub' ); ?>">
					<?php esc_html_e( 'All products', 'alistclub' ); ?>
				</button>
			</li>
			<li>
				<button type="button" class="sort-btn" data-sort="popularity" data-sort-name="<?php esc_attr_e( 'Best Sellers', 'alistclub' ); ?>">
					<?php esc_html_e( 'Best sellers', 'alistclub' ); ?>
				</button>
			</li>
			<li>
				<button type="button" class="sort-btn" data-sort="date" data-sort-name="<?php esc_attr_e( 'Newest', 'alistclub' ); ?>">
					<?php esc_html_e( 'Newest', 'alistclub' ); ?>
				</button>
			</li>
		</ul>
	</div>

	<?php if ( ! empty( $brands ) || ! empty( $cats ) ) : ?>
	<div class="sidebar-group">
		<h3 class="sidebar-heading"><?php esc_html_e( 'Filter by', 'alistclub' ); ?></h3>

		<?php if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) : ?>
		<details class="filter-group" open>
			<summary><?php esc_html_e( 'Brands', 'alistclub' ); ?></summary>
			<ul class="filter-list">
				<?php foreach ( $brands as $brand ) : ?>
					<li>
						<label>
							<input type="checkbox" class="filter-input" data-filter="brands" value="<?php echo esc_attr( $brand->slug ); ?>">
							<span><?php echo esc_html( $brand->name ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</details>
		<?php endif; ?>

		<?php if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) : ?>
		<details class="filter-group" open>
			<summary><?php esc_html_e( 'Categories', 'alistclub' ); ?></summary>
			<ul class="filter-list">
				<?php foreach ( $cats as $cat ) : ?>
					<li>
						<label>
							<input type="checkbox" class="filter-input" data-filter="categories" value="<?php echo esc_attr( $cat->slug ); ?>">
							<span><?php echo esc_html( $cat->name ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</details>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</aside>
