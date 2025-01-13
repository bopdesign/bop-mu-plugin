<?php
/**
 * This file contains hooks and functions that override the behavior of WP Core.
 *
 * @package BopMUPlugin
 */

namespace BopMUPlugin\Overrides;

/**
 * Registers instances where we will override default WP Core behavior.
 *
 * @return void
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\unregister_tags' );

	/**
	 * Clean up WordPress Header
	 */
	remove_action( 'wp_head', 'wp_resource_hints', 2 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

	// Remove jQuery migrate.
	add_action( 'wp_default_scripts', __NAMESPACE__ . '\dequeue_jquery_migrate' );

	// Remove WP Search Widget.
	add_action( 'widgets_init', __NAMESPACE__ . '\remove_search_widget' );

	// Remove customizer options.
	add_action( 'customize_register', __NAMESPACE__ . '\customize_register' );

	// Remove self pings.
	add_action( 'pre_ping', __NAMESPACE__ . '\remove_self_ping' );

	// Slow down the default heartbeat.
	add_filter( 'heartbeat_settings', __NAMESPACE__ . '\customize_heartbeat_interval' );
}

/**
 * Remove jQuery migrate.
 *
 * @param $scripts
 *
 * @return void
 */
function dequeue_jquery_migrate( $scripts ) {
	if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, [ 'jquery-migrate' ] );
	}
}

/**
 * Remove tags support from posts.
 *
 * @link   https://developer.wordpress.org/reference/functions/unregister_taxonomy_for_object_type/
 *
 * @return void
 */
function unregister_tags() {
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}

/**
 * Remove WP Search Widget.
 *
 * @return void
 */
function remove_search_widget() {
	unregister_widget( 'WP_Widget_Search' );
}

/**
 * Remove theme options.
 *
 * @param $wp_customize
 *
 * @return void
 */
function customize_register( $wp_customize ) {
	$wp_customize->remove_section( 'colors' );
	$wp_customize->remove_section( 'background_image' );
	$wp_customize->remove_section( 'header_image' );
}

/**
 * Remove self ping.
 *
 * @param $links
 *
 * @return void
 */
function remove_self_ping( &$links ) {
	$home = get_option( 'home' );

	foreach ( $links as $l => $link ) {
		if ( 0 === strpos( $link, $home ) ) {
			unset( $links[ $l ] );
		}
	}
}

/**
 * Customizes the interval for the WordPress heartbeat API.
 *
 * @param array $settings The current heartbeat settings.
 *
 * @return array Modified heartbeat settings with the updated interval.
 */
function customize_heartbeat_interval( $settings ) {
	// 60 seconds.
	$settings['interval'] = 60;

	return $settings;
};