<?php
/**
 * Plugin Name: WP RequireJS
 * Plugin URI:  http://digitallyconscious.com
 * Description: Load scripts enqueued with wp_enqueue_script asynchronously with requirejs
 * Version:     0.1.0
 * Author:      Rinat Khaziev
 * Author URI:  
 * License:     GPLv2+
 * Text Domain: wprjs
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2013 Rinat Khaziev (email : rinat.khaziev@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using grunt-wp-plugin-oo
 * Copyright (c) 2013 Rinat Khaziev
 *
 * grunt-wp-plugin-oo is based on
 * grunt-wp-plugin
 * Copyright (c) 2013 10up, LLC
 * https://github.com/10up/grunt-wp-plugin
 */

// Useful global constants
define( 'WPRJS_VERSION', '0.1.0' );
define( 'WPRJS_URL',     plugin_dir_url( __FILE__ ) );
define( 'WPRJS_PATH',    dirname( __FILE__ ) . '/' );

class WP_RequireJS {

	// Doesn't do anything for now
	private $only_theme_scripts = true;
	public $theme_url;
	// Holds the scripts to be loaded with requirejs
	public $requirejs_scripts = array();
	// Do not requirejs excluded handles
	public $exclude_handles = array( 'my-sync-script' );

	function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
		$this->theme_url = get_stylesheet_directory_uri();
	}

	/**
	 * [action_init description]
	 * @return [type] [description]
	 */
	function action_init() {
		// Run late to be sure we didn't miss a script
		add_action( 'wp_enqueue_scripts', array( $this, 'convert_enqueued_scripts_to_requirejs' ), 1473 );

		// Run early to print requirejs config before loading require.js itself
		add_action( 'wp_footer', array( $this, 'print_require' ), -34417 );
		$this->exclude_handles = apply_filters( 'wprjs_exclude_handles', $this->exclude_handles );
	}

	/**
	 * Iterate over enqueued scripts and make a requirejs config instead of printing them
	 *
	 * NB: the plugin doesn't check if your scripts are AMD-compatible
	 * Making a script loadable with requirejs is easy:
	 * @see http://stackoverflow.com/questions/10918063/how-to-make-a-jquery-plugin-loadable-with-requirejs
	 *
	 * @return [type] [description]
	 */
	function convert_enqueued_scripts_to_requirejs() {
		global $wp_scripts;
		foreach( $wp_scripts->registered as $handle => $script ) {
			// Only process in-theme scripts

			if ( false === stripos( $script->src, $this->theme_url ) || in_array( $handle, (array) $this->exclude_handles ) )
				continue;

			// @todo shim support
			$this->requirejs_scripts[$handle] = array(
				'handle' =>  $handle,
				'path' => str_replace( '.js', '', $script->src ),
				'deps' => $script->deps
			);
		}

		// Dequeue and remove
		$wp_scripts->dequeue( array_keys( $this->requirejs_scripts ) );
		$wp_scripts->remove( array_keys( $this->requirejs_scripts ) );
	}

	function print_require() {
	// Add any extra paths with filter
	$rjs_scripts = (array) apply_filters( 'wprjs_extra_paths', array() );
	$rjs_shim = array();
	foreach( $this->requirejs_scripts as $handle => $script ) {
		$rjs_scripts[$handle] = $script['path'];
		// Hardcode shim for now
		$rjs_shim[$handle] = array( 'deps' => array_merge( array( 'jquery' ), (array) $script['deps'] ) );
	}

?>
<script>
	var wprjs_paths = <?php echo json_encode( $rjs_scripts ) ?>;
	var wprjs_shim = <?php echo json_encode( $rjs_shim ) ?>;
</script>
<?php
	}
}

global $wp_requirejs;
$wp_requirejs = new WP_RequireJS;