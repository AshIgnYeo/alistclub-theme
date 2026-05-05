<?php
/**
 * FAQ custom post type.
 * Nests under the "A-List Club" admin menu and is queried by the search
 * route in inc/search-route.php (which checks post_type_exists('faq')).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alistclub_register_faq_cpt() {
	$labels = array(
		'name'                  => _x( 'FAQs', 'post type general name', 'alistclub' ),
		'singular_name'         => _x( 'FAQ', 'post type singular name', 'alistclub' ),
		'menu_name'             => _x( 'FAQ', 'admin menu', 'alistclub' ),
		'name_admin_bar'        => _x( 'FAQ', 'add new on admin bar', 'alistclub' ),
		'add_new'               => __( 'Add New', 'alistclub' ),
		'add_new_item'          => __( 'Add New FAQ', 'alistclub' ),
		'new_item'              => __( 'New FAQ', 'alistclub' ),
		'edit_item'             => __( 'Edit FAQ', 'alistclub' ),
		'view_item'             => __( 'View FAQ', 'alistclub' ),
		'all_items'             => __( 'FAQs', 'alistclub' ),
		'search_items'          => __( 'Search FAQs', 'alistclub' ),
		'not_found'             => __( 'No FAQs found.', 'alistclub' ),
		'not_found_in_trash'    => __( 'No FAQs found in Trash.', 'alistclub' ),
		'featured_image'        => __( 'FAQ Image', 'alistclub' ),
		'archives'              => __( 'FAQ Archives', 'alistclub' ),
		'attributes'            => __( 'FAQ Attributes', 'alistclub' ),
		'insert_into_item'      => __( 'Insert into FAQ', 'alistclub' ),
		'uploaded_to_this_item' => __( 'Uploaded to this FAQ', 'alistclub' ),
	);

	register_post_type( 'faq', array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => defined( 'ALISTCLUB_MENU_SLUG' ) ? ALISTCLUB_MENU_SLUG : true,
		'show_in_rest'       => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'faq', 'with_front' => false ),
		'menu_icon'          => 'dashicons-editor-help',
		'supports'           => array( 'title', 'editor', 'excerpt', 'revisions', 'page-attributes' ),
		'capability_type'    => 'post',
		'hierarchical'       => false,
	) );
}
add_action( 'init', 'alistclub_register_faq_cpt' );

/**
 * Flush rewrite rules on theme activation so the FAQ archive URL works.
 */
function alistclub_faq_flush_rewrites() {
	alistclub_register_faq_cpt();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'alistclub_faq_flush_rewrites' );

/**
 * Replace the "Add title" placeholder on FAQ edit screens with "Question".
 */
function alistclub_faq_title_placeholder( $placeholder, $post ) {
	if ( $post && 'faq' === $post->post_type ) {
		return __( 'Question', 'alistclub' );
	}
	return $placeholder;
}
add_filter( 'enter_title_here', 'alistclub_faq_title_placeholder', 10, 2 );
