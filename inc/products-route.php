<?php
/**
 * Custom REST products endpoint: /wp-json/alistclub/v1/products
 *
 * Powers the homepage Store grid. Supports sort + brand/category filters,
 * search, and paging (the grid loads further pages as the visitor scrolls).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Products per batch in the Store grid: the homepage's first render and
 * every REST page after it.
 */
define( 'ALISTCLUB_STORE_PER_PAGE', 12 );

add_action( 'rest_api_init', 'alistclub_register_products_route' );

function alistclub_register_products_route() {
	register_rest_route(
		'alistclub/v1',
		'products',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'alistclub_products_results',
			'permission_callback' => '__return_true',
			'args'                => array(
				'orderby'    => array(
					'required'          => false,
					'sanitize_callback' => 'sanitize_key',
					'validate_callback' => static function ( $v ) {
						return in_array( $v, array( 'all', 'popularity', 'date' ), true );
					},
				),
				'brands'     => array(
					'required'          => false,
					'sanitize_callback' => 'alistclub_sanitize_slug_list',
				),
				'categories' => array(
					'required'          => false,
					'sanitize_callback' => 'alistclub_sanitize_slug_list',
				),
				'limit'      => array(
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				'search'     => array(
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'page'       => array(
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

function alistclub_sanitize_slug_list( $value ) {
	if ( is_string( $value ) ) {
		$value = array_filter( array_map( 'trim', explode( ',', $value ) ) );
	}
	if ( ! is_array( $value ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'sanitize_title', $value ) ) );
}

function alistclub_brand_taxonomy() {
	foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand' ) as $tax ) {
		if ( taxonomy_exists( $tax ) ) {
			return $tax;
		}
	}
	return '';
}

/**
 * WP_Query args for the Store grid. Shared by the homepage's server-rendered
 * first page and the REST endpoint, so REST page 2 continues exactly where
 * the homepage's page 1 left off.
 *
 * @param array $opts orderby, brands, categories, search, page, per_page.
 * @return array
 */
function alistclub_store_query_args( $opts = array() ) {
	$opts = wp_parse_args(
		$opts,
		array(
			'orderby'    => 'all',
			'brands'     => array(),
			'categories' => array(),
			'search'     => '',
			'page'       => 1,
			'per_page'   => ALISTCLUB_STORE_PER_PAGE,
		)
	);

	$args = array(
		'post_type'           => 'product',
		'posts_per_page'      => (int) $opts['per_page'],
		'paged'               => max( 1, (int) $opts['page'] ),
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
	);

	if ( $opts['search'] !== '' ) {
		$args['s'] = $opts['search'];
	}

	// Each order ends on ID so ties (e.g. many products with 0 sales) sort
	// the same way on every request; otherwise pages can repeat or skip items.
	switch ( $opts['orderby'] ) {
		case 'popularity':
			$args['meta_key'] = 'total_sales';
			$args['orderby']  = array( 'meta_value_num' => 'DESC', 'ID' => 'DESC' );
			break;
		case 'date':
			$args['orderby'] = array( 'date' => 'DESC', 'ID' => 'DESC' );
			break;
		default:
			$args['orderby'] = array( 'menu_order' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC' );
			break;
	}

	$tax_query = array();
	$brand_tax = alistclub_brand_taxonomy();
	if ( $brand_tax && ! empty( $opts['brands'] ) ) {
		$tax_query[] = array(
			'taxonomy' => $brand_tax,
			'field'    => 'slug',
			'terms'    => $opts['brands'],
		);
	}
	if ( ! empty( $opts['categories'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $opts['categories'],
		);
	}

	// Leave out products WC_Product::is_visible() would hide (catalog
	// visibility, and out-of-stock when the store hides those) in the query
	// itself, so every page is full and the page count is accurate.
	if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$visibility = wc_get_product_visibility_term_ids();
		$hidden     = array( $visibility['exclude-from-catalog'] );
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$hidden[] = $visibility['outofstock'];
		}
		$hidden = array_filter( $hidden );
		if ( $hidden ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $hidden,
				'operator' => 'NOT IN',
			);
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query;
	}

	return $args;
}

function alistclub_products_results( WP_REST_Request $request ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return rest_ensure_response( array( 'products' => array(), 'page' => 1, 'total_pages' => 0 ) );
	}

	$orderby = (string) $request->get_param( 'orderby' );
	$page    = max( 1, (int) $request->get_param( 'page' ) );
	$limit   = (int) $request->get_param( 'limit' );
	if ( $limit < 1 || $limit > 60 ) {
		$limit = ALISTCLUB_STORE_PER_PAGE;
	}

	$query = new WP_Query(
		alistclub_store_query_args(
			array(
				'orderby'    => $orderby ? $orderby : 'all',
				'brands'     => (array) $request->get_param( 'brands' ),
				'categories' => (array) $request->get_param( 'categories' ),
				'search'     => trim( (string) $request->get_param( 'search' ) ),
				'page'       => $page,
				'per_page'   => $limit,
			)
		)
	);

	$brand_tax = alistclub_brand_taxonomy();
	$products  = array();

	while ( $query->have_posts() ) {
		$query->the_post();
		$wcp = wc_get_product( get_the_ID() );
		if ( ! $wcp || ! $wcp->is_visible() ) {
			continue;
		}

		$brand_name = '';
		if ( $brand_tax ) {
			$terms = get_the_terms( get_the_ID(), $brand_tax );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$brand_name = $terms[0]->name;
			}
		}

		$add_to_cart = apply_filters(
			'woocommerce_loop_add_to_cart_link',
			sprintf(
				'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
				esc_url( $wcp->add_to_cart_url() ),
				esc_attr( 1 ),
				esc_attr( implode( ' ', array_filter( array(
					'button',
					'product_type_' . $wcp->get_type(),
					$wcp->is_purchasable() && $wcp->is_in_stock() ? 'add_to_cart_button' : '',
					$wcp->supports( 'ajax_add_to_cart' ) && $wcp->is_purchasable() && $wcp->is_in_stock() ? 'ajax_add_to_cart' : '',
				) ) ) ),
				wc_implode_html_attributes( array(
					'data-product_id'  => $wcp->get_id(),
					'data-product_sku' => $wcp->get_sku(),
					'aria-label'       => $wcp->add_to_cart_description(),
					'rel'              => 'nofollow',
				) ),
				esc_html( $wcp->add_to_cart_text() )
			),
			$wcp
		);

		$products[] = array(
			'id'              => $wcp->get_id(),
			'name'            => $wcp->get_name(),
			'brand'           => $brand_name,
			'price_html'      => $wcp->get_price_html(),
			'image'           => wp_get_attachment_image_url( $wcp->get_image_id(), 'woocommerce_thumbnail' ),
			'permalink'       => get_permalink( $wcp->get_id() ),
			'add_to_cart_html'=> $add_to_cart,
		);
	}
	wp_reset_postdata();

	return rest_ensure_response(
		array(
			'products'    => $products,
			'page'        => $page,
			'total_pages' => (int) $query->max_num_pages,
		)
	);
}
