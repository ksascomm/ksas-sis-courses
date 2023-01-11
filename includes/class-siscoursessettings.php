<?php
if ( ! class_exists( 'SISCoursesSettings' ) ) {
	class SISCoursesSettings {
		private $sis_courses_plugin_options;

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'sis_courses_plugin_add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'sis_courses_plugin_page_init' ) );
		}

		public function sis_courses_plugin_add_plugin_page() {
			add_options_page(
				'SIS Courses', // page_title
				'SIS Courses', // menu_title
				'manage_options', // capability
				'sis-courses', // menu_slug
				array( $this, 'sis_courses_plugin_create_admin_page' ) // function
			);
		}

		public function sis_courses_plugin_create_admin_page() {
			$this->sis_courses_plugin_options = get_option( 'sis_courses_plugin_option_name' ); ?>

		<div class="wrap">
			<h2>SIS Courses</h2>
			<p></p>
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

		public function sis_courses_plugin_page_init() {
			register_setting(
				'sis_courses_plugin_option_group', // option_group
				'sis_courses_plugin_option_name', // option_name
				array( $this, 'sis_courses_plugin_sanitize' ) // sanitize_callback
			);

			add_settings_section(
				'sis_courses_plugin_setting_section', // id
				'Settings', // title
				array( $this, 'sis_courses_plugin_section_info' ), // callback
				'sis-courses-admin' // page
			);

			add_settings_field(
				'department_name_0', // id
				'Department Name', // title
				array( $this, 'department_name_0_callback' ), // callback
				'sis-courses-admin', // page
				'sis_courses_plugin_setting_section' // section
			);

			/*
			add_settings_field(
			'results_limit_1', // id
			'Results Limit', // title
			array( $this, 'results_limit_1_callback' ), // callback
			'sis-courses-admin', // page
			'sis_courses_plugin_setting_section' // section
			);

			add_settings_field(
			'start_date_2', // id
			'Start Date', // title
			array( $this, 'start_date_2_callback' ), // callback
			'sis-courses-admin', // page
			'sis_courses_plugin_setting_section' // section
			);*/
		}

		public function sis_courses_plugin_sanitize( $input ) {
			$sanitary_values = array();
			if ( isset( $input['department_name_0'] ) ) {
				$sanitary_values['department_name_0'] = sanitize_text_field( $input['department_name_0'] );
			}

			/*
			if ( isset( $input['results_limit_1'] ) ) {
			$sanitary_values['results_limit_1'] = sanitize_text_field( $input['results_limit_1'] );
			}

			if ( isset( $input['start_date_2'] ) ) {
			$sanitary_values['start_date_2'] = sanitize_text_field( $input['start_date_2'] );
			}*/

			return $sanitary_values;
		}

		public function sis_courses_plugin_section_info() {

		}

		public function department_name_0_callback() {
			printf(
				'<input class="regular-text" type="text" name="sis_courses_plugin_option_name[department_name_0]" id="department_name_0" value="%s">',
				isset( $this->sis_courses_plugin_options['department_name_0'] ) ? esc_attr( $this->sis_courses_plugin_options['department_name_0'] ) : ''
			);
		}

		/*
		public function results_limit_1_callback() {
		printf(
			'<input class="regular-text" type="text" name="sis_courses_plugin_option_name[results_limit_1]" id="results_limit_1" value="%s">',
			isset( $this->sis_courses_plugin_options['results_limit_1'] ) ? esc_attr( $this->sis_courses_plugin_options['results_limit_1']) : ''
		);
		}

		public function start_date_2_callback() {
		printf(
			'<input class="regular-text" type="text" name="sis_courses_plugin_option_name[start_date_2]" id="start_date_2" value="%s">',
			isset( $this->sis_courses_plugin_options['start_date_2'] ) ? esc_attr( $this->sis_courses_plugin_options['start_date_2']) : ''
		);
		}*/

	}
	if ( is_admin() ) {
		$sis_courses_plugin = new SISCoursesSettings();
	}
}

/*
 * Retrieve this value with:
 * $sis_courses_plugin_options = get_option( 'sis_courses_plugin_option_name' ); // Array of All Options
 * $department_name_0 = $sis_courses_plugin_options['department_name_0']; // API Key
 * $results_limit_1 = $sis_courses_plugin_options['results_limit_1']; // Results Limit
 * $start_date_2 = $sis_courses_plugin_options['start_date_2']; // Start Date
 */
