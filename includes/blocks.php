<?php
/**
 * This file contains hooks and functions that override blocks and Gutenberg behavior.
 *
 * @package BopMUPlugin
 */

namespace BopMUPlugin\Blocks;

/**
 * Registers instances where we will override blocks and Gutenberg behavior.
 *
 * @return void
 */
function setup() {
	// Remove inline Gutenberg CSS.
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\remove_wp_block_library_css', 100 );

	// Dequeue WordPress core Block Library styles.
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\deregister_core_block_styles' );

	/**
	 * Filters whether block styles should be loaded separately - only load styles for used blocks.
	 *
	 * Returning false loads all core block assets, regardless of whether they are rendered
	 * in a page or not. Returning true loads core block assets only when they are rendered.
	 *
	 * $load_separate_assets
	 *     (bool) Whether separate assets will be loaded.
	 *     Default false (all block assets are loaded, even when not used).
	 */
	add_filter( 'should_load_separate_core_block_assets', '__return_true', 11 );

	/**
	 * Prevent loading patterns from the WordPress.org pattern directory.
	 */
	add_filter( 'should_load_remote_block_patterns', '__return_false' );

	// Disables wpautop to remove empty p tags in rendered Gutenberg blocks.
	add_filter( 'init', __NAMESPACE__ . '\disable_wpautop_for_gutenberg', 9 );
}

/**
 * Remove Gutenberg block library css from loading on frontend.
 * Make sure to test after enabling, as these may break the styles, depending on the setup.
 *
 * @return void
 */
function remove_wp_block_library_css() {
	// Breaking: removes all block library CSS. Useful, when providing own CSS for core blocks.
	//wp_dequeue_style( 'wp-block-library' );
	// Works only if 'should_load_separate_core_block_assets' is FALSE.
	//wp_dequeue_style( 'global-styles' );
	// Remove WooCommerce block css.
	//wp_dequeue_style( 'wc-block-style' );
}

/**
 * Dequeue WordPress core Block Library styles.
 *
 * @return void
 */
function deregister_core_block_styles() {
	// This will remove the inline styles for the following core blocks.
	$block_styles_to_remove = [
		'heading',
		'paragraph',
		'list',
		'table',
	];

	foreach ( $block_styles_to_remove as $block_style ) {
		wp_deregister_style( 'wp-block-' . $block_style );
	}
}

/**
 * Disables wpautop to remove empty p tags in rendered Gutenberg blocks.
 *
 * @return void
 */
function disable_wpautop_for_gutenberg() {
	// If we have blocks in place, don't add wpautop.
	if ( has_filter( 'the_content', 'wpautop' ) && has_blocks() ) {
		remove_filter( 'the_content', 'wpautop' );
	}
}