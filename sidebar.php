<aside id="sidebar" aria-label="<?php esc_attr_e( 'Store categories', 'alistclub' ); ?>">
	<div class="category-menu-section">
		<ul>
			<li><a href="<?php echo esc_url( add_query_arg( 'orderby', 'date', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'New Arrivals', 'alistclub' ); ?></a></li>
			<li><a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Best Sellers', 'alistclub' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'All Products', 'alistclub' ); ?></a></li>
		</ul>
	</div>
	<?php
	wp_nav_menu( array(
		'theme_location'  => 'categories',
		'container_class' => 'category-menu-section',
		'fallback_cb'     => false,
	) );
	?>
</aside>
