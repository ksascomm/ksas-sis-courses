<?php
/**
 * The public bootstrap file
 *
 * @package   KSAS_SIS_Courses
 * @author    KSAS Communications <ksasweb@jhu.edu>
 * @license   GPL-2.0+
 * @link      https://krieger.jhu.edu
 * @copyright 2022 KSAS Communications
 *
 * @wordpress-plugin
 * Plugin Name: KSAS SIS Courses
 * Plugin URI:  http://www.wpexplorer.com/wordpress-page-templates-plugin/
 * Description: Displays courses from SIS
 * Version:     1.0.0
 * Author:      KSAS Communications
 * Author URI:  https://krieger.jhu.edu
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define( 'KSAS_SIS_COURSES_VERSION', '1.0.0' );

require plugin_dir_path( __FILE__ ) . '/includes/class-pagetemplater.php';
require plugin_dir_path( __FILE__ ) . '/includes/class-siscoursessettings.php';


if ( ! function_exists( 'register_script' ) ) {
	/**
	 * The code that register script(s) and style(s) on initialization.
	 *
	 * @since 1.0.0
	 */
	function register_script() {
		wp_enqueue_style( 'data-tables', '//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), true );

		wp_enqueue_style( 'data-tables-searchpanes', '//cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.dataTables.min.css', array(), true );

		wp_register_style( 'courses-css', plugins_url( '/css/courses.css', __FILE__ ), false, '1.0.0', 'all' );

		wp_enqueue_script( 'data-tables', '//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array(), '1.13.4', false );
		wp_script_add_data( 'data-tables', 'defer', true );

		wp_enqueue_script( 'data-tables-searchpanes', '//cdn.datatables.net/searchpanes/2.1.1/js/dataTables.searchPanes.min.js', array(), '2.1.1', false );
		wp_script_add_data( 'data-tables-searchpanes', 'defer', true );

		wp_enqueue_script( 'data-tables-select', '//cdn.datatables.net/select/1.4.0/js/dataTables.select.min.js', array(), '1.4.0', false );
		wp_script_add_data( 'data-tables-select', 'defer', true );

		wp_register_script( 'courses-js', plugins_url( '/js/courses.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

	}
	add_action( 'init', 'register_script' );
}


if ( ! function_exists( 'enqueue_style' ) ) {
	/**
	 * The code that enqueues the registered script(s) and style(s) above.
	 *
	 * @since 1.0.0
	 */
	function enqueue_style() {
		wp_enqueue_script( 'courses-js' );
		wp_enqueue_style( 'courses-css' );
	}
	add_action( 'wp_enqueue_scripts', 'enqueue_style' );
}
