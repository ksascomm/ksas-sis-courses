<?php
/**
 * Class SISCoursesSettings
 * * Handles the administrative settings page for SIS Courses.
 *
 * @package KSAS_SIS_Courses
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SISCoursesSettings' ) ) {

	class SISCoursesSettings {

		/**
		 * Holds the values to be used in the fields callbacks.
		 * @var array
		 */
		private $options;

		/**
		 * Start up.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
		}

		/**
		 * Add options page under "Settings".
		 */
		public function add_plugin_page() {
			add_options_page(
				'SIS Courses Settings', // Page title
				'SIS Courses',          // Menu title
				'manage_options',       // Capability
				'sis-courses',          // Menu slug
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Options page callback.
		 */
		public function create_admin_page() {
			// Set class property.
			$this->options = get_option( 'sis_courses_plugin_option_name' );
			?>
			<div class="wrap">
				<h1>SIS Courses</h1>
				<?php settings_errors(); ?>

				<form method="post" action="options.php">
					<?php
					settings_fields( 'sis_courses_plugin_option_group' );
					do_settings_sections( 'sis-courses-admin' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Register and add settings.
		 */
		public function page_init() {
			register_setting(
				'sis_courses_plugin_option_group', // Option group
				'sis_courses_plugin_option_name',  // Option name
				array( $this, 'sanitize' )         // Sanitize
			);

			add_settings_section(
				'sis_courses_plugin_setting_section', // ID
				'API Configuration',                  // Title
				array( $this, 'section_info' ),        // Callback
				'sis-courses-admin'                   // Page
			);

			add_settings_field(
				'department_name_0',                  // ID
				'Department Name',                    // Title
				array( $this, 'department_callback' ), // Callback
				'sis-courses-admin',                  // Page
				'sis_courses_plugin_setting_section'  // Section
			);
		}

		/**
		 * Sanitize each setting field as needed.
		 *
		 * @param array $input Contains all settings fields as array keys.
		 * @return array
		 */
		public function sanitize( $input ) {
			$new_input = array();
			if ( isset( $input['department_name_0'] ) ) {
				$new_input['department_name_0'] = sanitize_text_field( $input['department_name_0'] );
			}

			return $new_input;
		}

		/**
		 * Print the Section text.
		 */
		public function section_info() {
			echo 'Enter your SIS Department identifier below:';
		}

		/**
		 * Get the settings option array and print one of its values.
		 */
		public function department_callback() {
			$value = isset( $this->options['department_name_0'] ) ? esc_attr( $this->options['department_name_0'] ) : '';
			printf(
				'<input class="regular-text" type="text" name="sis_courses_plugin_option_name[department_name_0]" id="department_name_0" value="%s">',
				$value
			);
		}
	}

	// Initialize the class in the admin area.
	if ( is_admin() ) {
		$sis_courses_settings = new SISCoursesSettings();
	}
}

/**
 * Usage example:
 * $options = get_option( 'sis_courses_plugin_option_name' );
 * $dept    = $options['department_name_0'] ?? '';
 */