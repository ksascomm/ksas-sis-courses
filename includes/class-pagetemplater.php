<?php
/**
 * Creates page templates via this plugin.
 *
 * @link       https://krieger.jhu.edu
 * @since      1.0.0
 *
 * @package    KSAS_SIS_Courses
 * @subpackage KSAS_SIS_Courses/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PageTemplater' ) ) {
	/**
	 * Class PageTemplater
	 *
	 * Handles the registration and loading of custom page templates from the plugin.
	 */
	class PageTemplater {

		/**
		 * A reference to an instance of this class.
		 *
		 * @var PageTemplater
		 */
		private static $instance;

		/**
		 * The array of templates that this plugin tracks.
		 *
		 * @var array
		 */
		protected $templates;

		/**
		 * Returns an instance of this class.
		 *
		 * @return PageTemplater
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new PageTemplater();
			}
			return self::$instance;
		}

		/**
		 * Initializes the plugin by setting filters and administration functions.
		 */
		private function __construct() {
			$this->templates = array();

			// Add a filter to the attributes metabox to inject template into the list.
			add_filter(
				'theme_page_templates',
				array( $this, 'add_new_template' )
			);

			// Add a filter to the save post to inject our template into the page cache.
			add_filter(
				'wp_insert_post_data',
				array( $this, 'register_project_templates' )
			);

			// Add a filter to determine if the page has our template assigned and return its path.
			add_filter(
				'template_include',
				array( $this, 'view_project_template' )
			);

			// Check current theme compatibility.
			$ksas_theme = wp_get_theme();
			if ( 'KSAS Blocks' === $ksas_theme->name || 'KSAS Department Tailwind' === $ksas_theme->name || 'KSAS Blocks' === $ksas_theme->parent_theme ) {
				$this->templates = array(
					'../templates/courses-undergrad-ksasblocks.php' => 'SIS Courses',
				);
			}
		}

		/**
		 * Adds our template to the page dropdown.
		 *
		 * @param array $posts_templates Array of existing templates.
		 * @return array Combined array of templates.
		 */
		public function add_new_template( $posts_templates ) {
			$posts_templates = array_merge( $posts_templates, $this->templates );
			return $posts_templates;
		}

		/**
		 * Registers the templates into the theme cache.
		 *
		 * @param array $atts Attributes array.
		 * @return array
		 */
		public function register_project_templates( $atts ) {
			$ksas_cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

			$ksas_templates = wp_get_theme()->get_page_templates();
			if ( empty( $ksas_templates ) ) {
				$ksas_templates = array();
			}

			wp_cache_delete( $ksas_cache_key, 'themes' );
			$ksas_templates = array_merge( $ksas_templates, $this->templates );
			wp_cache_add( $ksas_cache_key, $ksas_templates, 'themes', 1800 );

			return $atts;
		}

		/**
		 * Checks if the template is assigned to the page and loads the file from the plugin.
		 *
		 * @param string $template Path to the template file.
		 * @return string
		 */
		public function view_project_template( $template ) {
			global $post;

			if ( ! $post ) {
				return $template;
			}

			$ksas_assigned_template = get_post_meta( $post->ID, '_wp_page_template', true );

			if ( ! isset( $this->templates[ $ksas_assigned_template ] ) ) {
				return $template;
			}

			// Path relative to this include file.
			$ksas_file = plugin_dir_path( __FILE__ ) . $ksas_assigned_template;

			if ( file_exists( $ksas_file ) ) {
				return $ksas_file;
			}

			return $template;
		}
	}

	// Initialize the class.
	add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );
}
