<?php
/**
 * Security functions.
 *
 * Enable or disable certain functionality to harden WordPress.
 *
 * @package BopMUPlugin
 */

namespace BopMUPlugin\Security;

use WP_Error;

/**
 * Default setup routine.
 *
 * @return void
 */
function setup() {
	/**
	 * Disable Anonymous Access to WordPress Rest API.
	 *
	 * The WordPress REST API is enabled for all users by default. To improve the security of a WordPress site, you can
	 * disable the WordPress REST API for anonymous requests, to avoid exposing admin users. This action improves site
	 * safety and reduces unexpected errors that can result in compromised WordPress core functionalities.
	 *
	 * The following function ensures that anonymous access to your site's REST API is disabled and that only authenticated
	 * requests will work.
	 *
	 * @link https://pantheon.io/docs/wordpress-best-practices#disable-anonymous-access-to-wordpress-rest-api
	 */
	// Disable WP Users REST API for non-authenticated users (allows anyone to see username list at /wp-json/wp/v2/users).
	add_filter( 'rest_authentication_errors', function ( $result ) {
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_not_logged_in', __( 'You are not currently logged in.' ), array( 'status' => 401 ) );
		}

		return $result;
	} );

	/**
	 * Remove generator meta tags.
	 *
	 * @see https://developer.wordpress.org/reference/functions/the_generator/
	 */
	add_filter( 'the_generator', '__return_empty_string' );

	/**
	 * Disable XML RPC.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/xmlrpc_enabled/
	 */
	add_filter( 'xmlrpc_enabled', '__return_false' );

	/**
	 * Change REST-API header from "null" to "*".
	 *
	 * @see https://w3c.github.io/webappsec-cors-for-developers/#avoid-returning-access-control-allow-origin-null
	 */
	function cors_control() {
		header( 'Access-Control-Allow-Origin: *' );
	}

	add_action( 'rest_api_init', __NAMESPACE__ . '\cors_control' );

	/**
	 * Disable use X-Pingback.
	 *
	 * @param $headers
	 *
	 * @return mixed
	 */
	function disable_x_pingback( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	add_filter( 'wp_headers', __NAMESPACE__ . '\disable_x_pingback' );

	/**
	 * Login page customizations.
	 *
	 * @return string
	 */
	function add_login_message() {
		return '<p class="message"><strong>Tip:</strong> Use a unique and complex password to keep your login secure.</p>';
	}

	add_filter( 'login_message', __NAMESPACE__ . '\add_login_message' );

	/**
	 * Show less info to users on failed login for security.
	 * On a failed login attempt, WordPress shows errors that tell users whether their username was incorrect or
	 * the password. These login hints can be used by someone for malicious attempts.
	 * (Will not let a valid username be known.)
	 *
	 * @return string
	 */
	function no_wordpress_errors() {
		return '<strong>ERROR</strong>: Something is wrong!';
	}

	add_filter( 'login_errors', __NAMESPACE__ . '\no_wordpress_errors' );
}