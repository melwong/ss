<?php
/**
 * Plugin Name:       	GravityView - Featured Entries Extension
 * Plugin URI:        	https://www.gravitykit.com/extensions/featured-entries/
 * Description:       	Promote entries as featured in Views
 * Version:          	2.0.9
 * Author:            	GravityKit
 * Author URI:        	https://www.gravitykit.com
 * Text Domain:       	gravityview-featured-entries
 * License:           	GPLv2 or later
 * License URI: 		https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:			/languages
 */

/** @since 2.0.4 */
define( 'GV_FEATURED_ENTRIES_VERSION', '2.0.9' );

/** @since 2.0.8 */
define( 'GV_FEATURED_ENTRIES_FILE', __FILE__ );

add_action( 'plugins_loaded', 'gv_extension_featured_entries_load' );

/**
 * Wrapper function to make sure GravityView_Extension has loaded
 * @return void
 */
function gv_extension_featured_entries_load() {

	if( !class_exists( 'GravityView_Extension' ) ) {

		if( class_exists('GravityView_Plugin') && is_callable(array('GravityView_Plugin', 'include_extension_framework')) ) {
			GravityView_Plugin::include_extension_framework();
		} else {
			// We prefer to use the one bundled with GravityView, but if it doesn't exist, go here.
			include_once plugin_dir_path( __FILE__ ) . 'lib/class-gravityview-extension.php';
		}
	}

	/**
	 * Load the plugin class
	 */
	include_once plugin_dir_path( __FILE__ ) . 'class-gravityview-featured-entries.php';

}
