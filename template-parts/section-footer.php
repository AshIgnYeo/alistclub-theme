<section id="footer">
	<footer>
		<div class="container">
			<div class="row">
				<div class="footer__sitemap-wrapper">
					<h4 class="title"><?php esc_html_e( 'Sitemap', 'alistclub' ); ?></h4>
					<ul class="link-list">
						<li class="list-item"><a href="<?php echo esc_url( home_url( '/about' ) ); ?>"><?php esc_html_e( 'About', 'alistclub' ); ?></a></li>
						<li class="list-item"><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'alistclub' ); ?></a></li>
						<li class="list-item"><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact', 'alistclub' ); ?></a></li>
					</ul>
				</div>
				<div class="footer__privacy-wrapper">
					<h4 class="title"><?php esc_html_e( 'Privacy', 'alistclub' ); ?></h4>
					<ul class="link-list">
						<li class="list-item"><a href="<?php echo esc_url( home_url( '/terms-and-conditions' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'alistclub' ); ?></a></li>
						<li class="list-item"><a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'alistclub' ); ?></a></li>
					</ul>
				</div>
				<div class="footer__mailing-wrapper">
					<h4 class="title"><?php esc_html_e( 'Get in the know', 'alistclub' ); ?></h4>
					<p><?php esc_html_e( 'Sign up for our mailing list to be the first to know about our promotions.', 'alistclub' ); ?></p>
					<?php get_template_part( 'template-parts/snippet', 'mailchimp-form' ); ?>
				</div>
			</div>
			<small>
				<?php
				/* translators: %s: current year */
				printf( esc_html__( '© %s A-List Club Pte Ltd. All rights reserved.', 'alistclub' ), esc_html( gmdate( 'Y' ) ) );
				?>
			</small>
		</div>
	</footer>
</section>
