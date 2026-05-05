<?php
/**
 * FAQ page content
 */
$faqContent = new WP_Query([
	"post_type" => "faq",
	"posts_per_page" => -1
]);
while ($faqContent->have_posts()) {
	$faqContent->the_post(); ?>

	<div class="faq-item">
		<h3><?php the_title(); ?></h3>
		<p><?php the_content(); ?></p>
	</div>
		
<?php }
wp_reset_postdata();
?>