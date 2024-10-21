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
 * Version:     3.0.0
 * Author:      KSAS Communications
 * Author URI:  https://krieger.jhu.edu
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define( 'KSAS_SIS_COURSES_VERSION', '3.0.0' );

require plugin_dir_path( __FILE__ ) . '/includes/class-pagetemplater.php';
require plugin_dir_path( __FILE__ ) . '/includes/class-siscoursessettings.php';


if ( ! function_exists( 'register_script' ) ) {
	/**
	 * The code that register script(s) and style(s) on initialization.
	 *
	 * @since 1.0.0
	 */
	function register_script() {
		wp_enqueue_style( 'data-tables', '//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css', array(), true );

		wp_enqueue_style( 'data-tables-searchpanes', '//cdn.datatables.net/searchpanes/2.3.3/css/searchPanes.dataTables.min.css', array(), true );

		wp_enqueue_style( 'data-tables-responsive', '//cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css', array(), true );

		wp_register_style( 'courses-css', plugins_url( '/css/courses.css', __FILE__ ), false, '1.0.4', 'all' );

		wp_enqueue_script( 'data-tables', '//cdn.datatables.net/2.1.8/js/dataTables.min.js', array(), '2.1.8', false );
		wp_script_add_data( 'data-tables', 'defer', true );

		wp_enqueue_script( 'data-tables-searchpanes', '//cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js', array(), '2.3.3', false );
		wp_script_add_data( 'data-tables-searchpanes', 'defer', true );

		wp_enqueue_script( 'data-tables-select', '//cdn.datatables.net/select/2.1.0/js/dataTables.select.min.js', array(), '2.1.0', false );
		wp_script_add_data( 'data-tables-select', 'defer', true );

		wp_enqueue_script( 'data-tables-responsive', '//cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js', array(), '3.0.3', false );
		wp_script_add_data( 'data-tables-responsive', 'defer', true );

		wp_register_script( 'courses-js', plugins_url( '/js/courses.js', __FILE__ ), array( 'jquery' ), '1.0.2', true );

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


if ( function_exists( 'acf_add_local_field_group' ) ) :
	/**
	 * The code that sets the Course Level using ACF.
	 *
	 * @since 2.0.0
	 */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_6425d800396b8',
			'title'                 => 'SIS Course Level',
			'fields'                => array(
				array(
					'key'               => 'field_6425d80032601',
					'label'             => 'Course Level',
					'name'              => 'course_level',
					'aria-label'        => '',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'Undergraduate' => 'Undergraduate',
						'Graduate'      => 'Graduate',
					),
					'default_value'     => '',
					'return_format'     => 'value',
					'allow_null'        => 0,
					'other_choice'      => 0,
					'layout'            => 'vertical',
					'save_other_choice' => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => '../templates/courses-undergrad-ksasblocks.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);

	endif;

/**
 * Exclude the created Page Template from search results
 */
function exclude_page_templates_from_search( $query ) {

	global $wp_the_query;
	if ( ( $wp_the_query === $query ) && ( is_search() ) && ( ! is_admin() ) ) {
		$meta_query =
			array(
				// set OR, default is AND.
						'relation' => 'OR',
				// remove pages with foo.php template from results.
				array(
					'key'     => '_wp_page_template',
					'value'   => '../templates/courses-undergrad-ksasblocks.php',
					'compare' => '!=',
				),
				// show all entries that do not have a key '_wp_page_template'.
						array(
							'key'     => '_wp_page_template',
							'value'   => 'page-thanks.php',
							'compare' => 'NOT EXISTS',
						),
			);
		$query->set( 'meta_query', $meta_query );
	}

}
add_filter( 'pre_get_posts', 'exclude_page_templates_from_search' );
