<?php
/**
 * Default template for page content
 * Used as fallback for when no other content-{type} is found
 */
while (have_posts()): the_post();
	 the_content(); 
endwhile
?>