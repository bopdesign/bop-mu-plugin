<?php
/**
 * Admin setup, hooks and filters.
 *
 * @package BopMUPlugin
 */

namespace BopMUPlugin\Admin;

function setup() {
	// Unnecessary WordPress menus.
	add_action( 'admin_menu', __NAMESPACE__ . '\remove_unnecessary_wordpress_menus', 999 );

	// Admin columns.
	add_filter( 'manage_posts_columns', __NAMESPACE__ . '\posts_columns', 5 );
	add_action( 'manage_posts_custom_column', __NAMESPACE__ . '\posts_custom_columns', 5, 2 );
	add_filter( 'manage_pages_columns', __NAMESPACE__ . '\posts_columns', 5 );
	add_action( 'manage_pages_custom_column', __NAMESPACE__ . '\posts_custom_columns', 5, 2 );

	// Check for file uploads duplicates.
	add_filter( 'wp_handle_upload_prefilter', __NAMESPACE__ . '\prevent_same_name_file_upload' );

	// Enable custom mime types.
	add_filter( 'upload_mimes', __NAMESPACE__ . '\custom_mime_types' );

	// Most of the clients do not use this functionality. Disable it.
	disable_comments_and_menus();

	// WordPress login screen various customizations.
	login_page_customization();
}

/**
 * Disables comments and related functionality across all post types
 * and ensures that corresponding menu items and admin bar links
 * are removed from the WordPress admin interface.
 *
 * @return void
 */
function disable_comments_and_menus() {
	add_action( 'admin_init', function () {
		// Redirect any user trying to access comments page.
		global $pagenow;

		if ( $pagenow === 'edit-comments.php' ) {
			wp_safe_redirect( admin_url() );
			exit;
		}

		// Remove comments meta box from dashboard.
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

		// Disable support for comments and trackbacks in post types
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	} );

	/* Close comments on the front-end. */
	add_filter( 'comments_open', '__return_false', 20, 2 );
	add_filter( 'pings_open', '__return_false', 20, 2 );

	/* Hide existing comments. */
	add_filter( 'comments_array', '__return_empty_array', 10, 2 );

	// Remove comments page in menu.
	add_action( 'admin_menu', function () {
		remove_menu_page( 'edit-comments.php' );
	} );

	// Remove comments links from admin bar.
	add_action( 'init', function () {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	} );
}

/**
 * @return void
 */
function login_page_customization() {
	// Split login screen in half, with login form on the left, and color background on the right.
	add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\login_page_layout' );
	add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\login_logo' );
	add_filter( 'login_headerurl', function () {
		return home_url();
	} );
}

/**
 * WordPress login screen layout customization.
 *
 * @return void
 */
function login_page_layout() {
	$blog_name = get_bloginfo( 'name' );
	?>
	<style>
		/*body.login {*/
		/*	font-family: Avenir, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;*/
		/*}*/

		body.login::before {
			background: #1d2327;
			content: '';
			display: block;
			z-index: 0;
		}

		body.login div#login {
			background: #fff;
			border: 1px solid #d8dde6;
			border-radius: 0.25rem;
			padding: 32px 32px 16px;
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			z-index: 1;
		}

		body.login div#login form {
			border: 0 none;
			box-shadow: none;
			padding: 0 2px;
		}

		@media (max-width: 782px) {
			body.login::before {
				color: #fff;
				content: 'Sign in to <?php echo esc_html( $blog_name ); ?>';
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: clamp(1.5rem, 1.1537rem + 1.7316vw, 2rem);
				font-weight: 600;
				text-align: center;
				line-height: 1.2;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 80px;
			}

			body.login div#login {
				left: 50%;
				transform: translate(-50%, -50%);
			}
		}

		@media (min-width: 783px) {
			body.login::before {
				position: absolute;
				top: 0;
				right: 0;
				width: 50vw;
				height: 100vh;
			}

			body.login div#login {
				margin: 0 calc((50% - 324px * 1.2) / 2) 0;
			}
		}
	</style>
	<?php
}

/**
 * WordPress login screen logo customization.
 *
 * @return void
 */
function login_logo() {
	if ( ! class_exists( 'ACF' ) ) {
		return;
	}

	// Return Array to get image sizes.
	$logo = get_field( 'admin_logo', 'option' );

	if ( ! empty( $logo ) && $logo['width'] && $logo['height'] ) {
		$logo_url    = $logo['url'];
		$logo_width  = $logo['width'] . 'px';
		$logo_height = $logo['height'] . 'px';
		?>
		<style>
			body.login div#login h1 a {
				background-image: url("<?php echo $logo_url; ?>");
				background-position: center;
				background-size: <?php echo $logo_width; ?> <?php echo $logo_height; ?>;
				width: <?php echo $logo_width; ?>;
				height: <?php echo $logo_height; ?>;
			}
		</style>
		<?php
	}
}

/**
 * Remove unused menus.
 *
 * @return void
 */
function remove_unnecessary_wordpress_menus() {
	global $submenu;

	if ( isset( $submenu['themes.php'] ) ):
		foreach ( $submenu['themes.php'] as $menu_index => $theme_menu ) {
			if ( $theme_menu[0] == 'Header' || $theme_menu[0] == 'Background' ) {
				unset( $submenu['themes.php'][ $menu_index ] );
			}
		}
	endif;
}

/**
 * Show Featured Images in admin columns.
 *
 * @link https://www.isitwp.com/add-featured-thumbnail-to-admin-post-columns/
 */
function posts_columns( $defaults ) {
	$defaults['bop_plugin_post_thumbs'] = __( 'Featured Image' );

	return $defaults;
}

function posts_custom_columns( $column_name, $id ) {
	if ( 'bop_plugin_post_thumbs' === $column_name ) {
		the_post_thumbnail( 'thumbnail' );
	}
}

/**
 * Prevent upload in WordPress media library if there is already a file with the same name.
 *
 * @return array
 */
function prevent_same_name_file_upload( $file ) {
	$uploads       = wp_upload_dir();
	$use_yearmonth = get_option( 'uploads_use_yearmonth_folders' );

	if ( boolval( $use_yearmonth ) ) {
		// If upload to year month based folders is enabled check current target.
		$year   = date( 'Y' );
		$month  = date( 'm' );
		$target = $uploads['path'] . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $file['name'];
	} else {
		// Uploads dir.
		$target = $uploads['path'] . DIRECTORY_SEPARATOR . $file['name'];
	}

	if ( file_exists( $target ) ) {
		$file['error'] = 'File with the same name already exists. Either overwrite/replace the file via FTP, or rename your file before uploading. Remember to (S)FTP overwrite/replace the @2x version of the image file if needed.';
	}

	return $file;
}

/**
 * Enable custom mime types.
 *
 * @param array $mimes Current allowed mime types.
 *
 * @return array Mime types.
 *
 * @package Bopper
 */
function custom_mime_types( $mimes ) : array {
	// SVG images.
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}