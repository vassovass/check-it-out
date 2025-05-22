<?php

function handl_surecart_wp_head() {
	echo '<style>.handl-hidden{display: none}</style>';
}
add_action( 'wp_head', 'handl_surecart_wp_head' );
