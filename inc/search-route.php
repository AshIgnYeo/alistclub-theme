<?php
/**
 * Custom REST search endpoint: /wp-json/alistclub/v1/search?query=...
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'alistclub_register_search_route' );

function alistclub_register_search_route() {
	register_rest_route(
		'alistclub/v1',
		'search',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'alistclub_search_results',
			'permission_callback' => '__return_true',
			'args'                => array(
				'query' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $v ) {
						return is_string( $v ) && strlen( $v ) >= 1 && strlen( $v ) <= 100;
					},
				),
			),
		)
	);
}

function alistclub_search_results( WP_REST_Request $request ) {
	$query = sanitize_text_field( (string) $request->get_param( 'query' ) );

	$results = array(
		'products' => array(),
		'posts'    => array(),
		'faqs'     => array(),
	);

	if ( '' === $query ) {
		return rest_ensure_response( $results );
	}

	// Products.
	if ( function_exists( 'wc_get_product' ) ) {
		$products = new WP_Query( array(
			'post_type'      => 'product',
			'posts_per_page' => 20,
			's'              => $query,
			'no_found_rows'  => true,
		) );
		while ( $products->have_posts() ) {
			$products->the_post();
			$wcp = wc_get_product( get_the_ID() );
			if ( ! $wcp ) {
				continue;
			}
			$results['products'][] = array(
				'name'          => $wcp->get_name(),
				'regular_price' => $wcp->get_regular_price(),
				'sale_price'    => $wcp->get_sale_price(),
				'description'   => wp_strip_all_tags( $wcp->get_short_description() ),
				'in_stock'      => 'instock' === $wcp->get_stock_status() ? 1 : 0,
				'image'         => wp_get_attachment_url( $wcp->get_image_id() ),
				'slug'          => $wcp->get_slug(),
			);
		}
		wp_reset_postdata();
	}

	// Posts.
	$posts = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 20,
		's'              => $query,
		'no_found_rows'  => true,
	) );
	while ( $posts->have_posts() ) {
		$posts->the_post();
		$results['posts'][] = array(
			'title'   => get_the_title(),
			'content' => wp_trim_words( wp_strip_all_tags( get_the_content() ), 30 ),
			'url'     => get_permalink(),
		);
	}
	wp_reset_postdata();

	// FAQs.
	if ( post_type_exists( 'faq' ) ) {
		$faq = new WP_Query( array(
			'post_type'      => 'faq',
			'posts_per_page' => 20,
			's'              => $query,
			'no_found_rows'  => true,
		) );
		while ( $faq->have_posts() ) {
			$faq->the_post();
			$results['faqs'][] = array(
				'title'   => get_the_title(),
				'content' => wp_trim_words( wp_strip_all_tags( get_the_content() ), 30 ),
				'url'     => get_permalink(),
			);
		}
		wp_reset_postdata();
	}

	return rest_ensure_response( $results );
}
