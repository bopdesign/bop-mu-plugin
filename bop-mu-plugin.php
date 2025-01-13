<?php
/*
 * Plugin Name:       Bop Design Helper Plugin
 * Plugin URI:        https://github.com/bopdesign/bop-mu-plugin
 * Description:       A helper plugin for actions and filters to modify WP functionality, and enhance security.
 * Version:           0.1.0
 * Author:            Bop Design
 * Author URI:        https://www.bopdesign.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       bop-mu-plugin
 *
 * @package           BopMUPlugin
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define global constants.
define( 'BOP_MU_PLUGIN_VERSION', '0.1.0' );
define( 'BOP_MU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BOP_MU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOP_MU_PLUGIN_INC', BOP_MU_PLUGIN_PATH . 'includes/' );

// Include files.
require_once BOP_MU_PLUGIN_INC . 'security.php';
require_once BOP_MU_PLUGIN_INC . 'core.php';
require_once BOP_MU_PLUGIN_INC . 'overrides.php';
require_once BOP_MU_PLUGIN_INC . 'admin.php';
require_once BOP_MU_PLUGIN_INC . 'blocks.php';

// Bootstrap.
BopMUPlugin\Security\setup();
BopMUPlugin\Core\setup();
BopMUPlugin\Overrides\setup();
BopMUPlugin\Admin\setup();
BopMUPlugin\Blocks\setup();