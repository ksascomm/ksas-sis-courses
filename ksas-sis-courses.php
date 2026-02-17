<?php
/**
 * The public bootstrap file
 *
 * @package   KSAS_SIS_Courses
 * @author    KSAS Communications <ksasweb@jhu.edu>
 * @license   GPL-2.0+
 * @link      https://krieger.jhu.edu
 * @copyright 2026 KSAS Communications
 *
 * @wordpress-plugin
 * Plugin Name: KSAS SIS Courses
 * Description: Displays courses from SIS.
 * Version:     4.0.0
 * Author:      KSAS Communications
 * Author URI:  https://krieger.jhu.edu
 * License:     GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KSAS_SIS_COURSES_VERSION', '4.0.0' );

// Load Dependencies.
require_once plugin_dir_path( __FILE__ ) . '/includes/class-pagetemplater.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/class-siscoursessettings.php';

/**
 * 1. Assets: Register and Enqueue Scripts/Styles.
 */
function ksas_sis_register_assets() {
	$version = KSAS_SIS_COURSES_VERSION;

	// Styles.
	wp_enqueue_style( 'data-tables', 'https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css', array(), $version );
	wp_enqueue_style( 'data-tables-select-css', 'https://cdn.datatables.net/select/3.1.3/css/select.dataTables.min.css', array(), '3.1.3' );
	wp_enqueue_style( 'data-tables-searchpanes-css', 'https://cdn.datatables.net/searchpanes/2.3.5/css/searchPanes.dataTables.min.css', array(), '2.3.5' );
	wp_enqueue_style( 'data-tables-responsive-css', 'https://cdn.datatables.net/responsive/3.0.8/css/responsive.dataTables.min.css', array(), '3.0.8' );
	wp_enqueue_style( 'ksas-sis-courses-css', plugins_url( '/css/courses.css', __FILE__ ), array(), '4.0.0' );

	// Scripts.
	wp_enqueue_script( 'data-tables', 'https://cdn.datatables.net/2.3.7/js/dataTables.min.js', array( 'jquery' ), '2.3.4', true );
	wp_enqueue_script( 'data-tables-select', 'https://cdn.datatables.net/select/3.1.3/js/dataTables.select.min.js', array( 'data-tables' ), '3.1.3', true );
	wp_enqueue_script( 'data-tables-searchpanes', 'https://cdn.datatables.net/searchpanes/2.3.5/js/dataTables.searchPanes.min.js', array( 'data-tables', 'data-tables-select' ), '2.3.5', true );
	wp_enqueue_script( 'data-tables-responsive', 'https://cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.min.js', array( 'data-tables' ), '3.0.8', true );

	// Add Defer attribute to DataTables scripts.
	wp_script_add_data( 'data-tables', 'defer', true );
	wp_script_add_data( 'data-tables-searchpanes', 'defer', true );
	wp_script_add_data( 'data-tables-select', 'defer', true );
	wp_script_add_data( 'data-tables-responsive', 'defer', true );

	// Custom JS.
	wp_enqueue_script( 'ksas-sis-courses-js', plugins_url( '/js/courses.js', __FILE__ ), array( 'jquery', 'data-tables' ), '4.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'ksas_sis_register_assets' );

/**
 * 2. ACF: Register Local Field Group.
 */
function ksas_sis_add_acf_fields() {
	if ( function_exists( 'acf_add_local_field_group' ) ) :
		acf_add_local_field_group(
			array(
				'key'      => 'group_6425d800396b8',
				'title'    => 'SIS Course Level',
				'fields'   => array(
					array(
						'key'     => 'field_6425d80032601',
						'label'   => 'Course Level',
						'name'    => 'course_level',
						'type'    => 'radio',
						'choices' => array(
							'Undergraduate' => 'Undergraduate',
							'Graduate'      => 'Graduate',
						),
						'layout'  => 'vertical',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page_template',
							'operator' => '==',
							'value'    => '../templates/courses-undergrad-ksasblocks.php',
						),
					),
				),
				'active'   => true,
			)
		);
	endif;
}
add_action( 'acf/init', 'ksas_sis_add_acf_fields' );

/**
 * 3. Search: Exclude specific Page Templates from search results.
 *
 * @param WP_Query $query The query object.
 */
function ksas_sis_exclude_templates_from_search( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$meta_query = array(
			'relation' => 'OR',
			array(
				'key'     => '_wp_page_template',
				'value'   => '../templates/courses-undergrad-ksasblocks.php',
				'compare' => '!=',
			),
			array(
				'key'     => '_wp_page_template',
				'compare' => 'NOT EXISTS',
			),
		);
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'ksas_sis_exclude_templates_from_search' );
