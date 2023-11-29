<?php
/**
 * GravityCharts
 *
 * @package   GravityCharts
 * @wordpress-plugin
 *
 * Plugin Name:       GravityCharts
 * Plugin URI:        https://www.gravitykit.com/products/gravitycharts/
 * Description:       Display your Gravity Forms data in charts.
 * Version:           1.6
 * Author:            GravityKit
 * Author URI:        https://www.gravitykit.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gk-gravitycharts
 */

use GravityKit\GravityCharts\Plugin;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor_prefixed/gravitykit/foundation/src/preflight_check.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

if ( ! GravityKit\GravityCharts\Foundation\should_load( __FILE__ ) ) {
	return;
}

const GK_GRAVITYCHARTS_PLUGIN_FILE = __FILE__;

const GK_GRAVITYCHARTS_PLUGIN_VERSION = '1.6';

const GK_GRAVITYCHARTS_MIN_GF_VERSION = '2.5';

const GK_GRAVITYCHARTS_CHART_JS_VERSION = '4.1.0';

$autoload = __DIR__ . '/vendor/autoload.php';

$autoload_prefixed = __DIR__ . '/vendor_prefixed/autoload.php';

if ( ! file_exists( $autoload ) || ! file_exists( $autoload_prefixed ) ) {
	$message = wpautop( esc_html__( 'GravityCharts is missing some core files. Please re-install the plugin.', 'gk-gravitycharts' ) );

	gk_gravitycharts_init_error( $message );
	return;
}

/**
 * Adds an error message to the admin notices.
 *
 * @param string $message The error message to show.
 */
function gk_gravitycharts_init_error( $message ) {
	$show_error_message = function () use ( $message ) {
		echo wp_kses_post( "<div class='error' style='padding: 1.25em 0 1.25em 1em;'>$message</div>" );
	};

	add_action( 'admin_notices', $show_error_message );
}

require_once $autoload;

require_once $autoload_prefixed;

GravityKit\GravityCharts\Foundation\Core::register( __FILE__ );

new Plugin();
