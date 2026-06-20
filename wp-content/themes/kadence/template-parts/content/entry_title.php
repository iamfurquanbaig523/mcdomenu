<?php
/**
 * Template part for displaying a post's title
 *
 * @package kadence
 */

namespace Kadence;

do_action( 'kadence_single_before_entry_title' );
the_title( '<h1 class="entry-title">', '</h1>' );
if ( is_single() || is_page() ) {
	echo '<div class="mcprices-author-byline" style="margin-top: 5px; margin-bottom: 20px; font-size: 14px; font-weight: 500; color: #555;">';
	echo 'Written by <strong class="author-name">Sarah Jenkins</strong>, Lead Menu Analyst & Senior Editor';
	echo '</div>';
}
do_action( 'kadence_single_after_entry_title' );
