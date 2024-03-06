<?php

/**
 * Template Name: velo_selectors
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

wp_head();

echo do_shortcode('[velo_show_product_selector id="' . get_the_ID() . '"]');

get_footer();
