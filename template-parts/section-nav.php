<nav id="main-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'alistclub' ); ?>">
	<div class="container center">
		<div class="row">

			<div class="nav__logo-wrapper">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img
						src="<?php echo esc_url( get_theme_file_uri( 'assets/images/alist-logo-white.png' ) ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
						width="150" height="60" decoding="async">
				</a>
			</div>

			<button
				type="button"
				class="nav__toggle"
				aria-controls="primary-menu"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Toggle menu', 'alistclub' ); ?>">
				<span class="nav__toggle-bar" aria-hidden="true"></span>
				<span class="nav__toggle-bar" aria-hidden="true"></span>
				<span class="nav__toggle-bar" aria-hidden="true"></span>
			</button>

			<div class="nav__main-wrapper" id="primary-menu">
				<ul class="link-list list-inverse-color list-row"></ul>
			</div>

			<div class="nav__account-wrapper">
				<ul class="link-list list-inverse-color list-row">
					<li id="open-search-overlay" class="list-item nav-link">
						<a href="#search" aria-label="<?php esc_attr_e( 'Open search', 'alistclub' ); ?>"><i class="fas fa-search" aria-hidden="true"></i></a>
					</li>
					<?php if ( is_user_logged_in() ) : ?>
						<li class="list-item nav-link has-submenu">
							<a href="<?php echo esc_url( home_url( '/my-account' ) ); ?>" aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e( 'Account menu', 'alistclub' ); ?>">
								<i class="fas fa-user" aria-hidden="true"></i>
							</a>
							<ul class="sub-menu">
								<li class="list-item nav-link"><a href="<?php echo esc_url( home_url( '/my-account' ) ); ?>"><?php esc_html_e( 'Account', 'alistclub' ); ?></a></li>
								<li class="list-item nav-link"><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Logout', 'alistclub' ); ?></a></li>
							</ul>
						</li>
					<?php else : ?>
						<li class="list-item nav-link">
							<a href="<?php echo esc_url( home_url( '/login' ) ); ?>" aria-label="<?php esc_attr_e( 'Login', 'alistclub' ); ?>"><i class="fas fa-user" aria-hidden="true"></i></a>
						</li>
					<?php endif; ?>
					<li class="list-item nav-link">
						<a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/basket' ) ); ?>" aria-label="<?php esc_attr_e( 'Basket', 'alistclub' ); ?>"><i class="fa fa-shopping-basket" aria-hidden="true"></i></a>
					</li>
				</ul>
			</div>

		</div>
	</div>
</nav>
