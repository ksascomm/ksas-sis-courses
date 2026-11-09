<?php
/**
 * Template Name: SIS Courses
 * Description: Display SIS courses in KSAS Blocks & Department Theme
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package KSAS_Blocks
 */

get_header();

// 1. Load Zebra Curl.
require_once plugin_dir_path( __DIR__ ) . '/lib/Zebra_cURL.php';

// 2. Set query string variables.
$sis_options = get_option( 'sis_courses_plugin_option_name', array() );

$department = isset( $sis_options['department_name_0'] )
	? rawurlencode( $sis_options['department_name_0'] )
	: '';

// Term parameters for academic cycle.
$fall   = rawurlencode( 'Fall 2026' );
$spring = rawurlencode( 'Spring 2027' );
$summer = rawurlencode( 'Summer 2026' );

$key = '0jCaUO1bHwbG1sFEKQd3iXgBgxoDUOhR';

// 3. Create Zebra Curl instance.
$course_curl = new Zebra_cURL();
$course_curl->option(
	array(
		CURLOPT_TIMEOUT        => 60,
		CURLOPT_CONNECTTIMEOUT => 60,
	)
);

// 4. Cache configuration (14 days).
// 4a. Parse home_url() to handle mapped domains on subdirectory installs smoothly.
$parsed_home = wp_parse_url( home_url() );
$host        = isset( $parsed_home['host'] ) ? $parsed_home['host'] : '';
$parsed_path = isset( $parsed_home['path'] ) ? trim( $parsed_home['path'], '/' ) : '';

// 4b. Construct a unique folder key based on mapping structure.
if ( ! empty( $parsed_path ) ) {
	// Standard subdirectory site (e.g., domain.com/biology -> biology).
	$folder_name = sanitize_key( $parsed_path );
} elseif ( ! empty( $host ) ) {
	// Mapped domain sitting at root path (e.g., biology.jhu.edu -> biology-jhu-edu).
	$folder_name = sanitize_key( str_replace( '.', '-', $host ) );
} else {
	// Fallback to blog ID if URL parsing fails.
	$folder_name = 'site-' . get_current_blog_id();
}

// 4c. Define the unique cache directory path.
$cache_dir = WP_CONTENT_DIR . '/sis-cache/' . $folder_name;

if ( ! function_exists( 'WP_Filesystem' ) ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
}

// Force the direct method so WP doesn't attempt FTP fallback.
WP_Filesystem( array(), false, true );
global $wp_filesystem;

if ( $wp_filesystem && method_exists( $wp_filesystem, 'is_dir' ) ) {
	if ( ! $wp_filesystem->is_dir( $cache_dir ) ) {
		$wp_filesystem->mkdir( $cache_dir, FS_CHMOD_DIR );
	}
} elseif ( ! file_exists( $cache_dir ) ) {
	wp_mkdir_p( $cache_dir );
}

$course_curl->cache( $cache_dir, 1209600 );

// 5. Construct API URL.
$base_url      = 'https://sis.jhu.edu/api/classes';
$query_string  = "?key={$key}&School=" . rawurlencode( 'Krieger School of Arts and Sciences' );
$query_string .= "&Term={$spring}&Term={$summer}&Term={$fall}";
$query_string .= "&Department=AS%20{$department}";

$courses_url = $base_url . $query_string;

$course_data = array();

// 6. Fetch Initial Course List.
$course_curl->get(
	$courses_url,
	function ( $result ) use ( &$course_data, $key ) {
		if ( empty( $result->body ) ) {
			return;
		}

		$body = ! is_array( $result->body ) ? json_decode( html_entity_decode( $result->body ), true ) : json_decode( wp_json_encode( $result->body ), true );

		if ( ! is_array( $body ) ) {
			return;
		}

		// Safeguard ACF function call.
		$wp_course_level = function_exists( 'get_field' ) ? get_field( 'course_level' ) : '';
		$target_level    = '';

		if ( 'Graduate' === $wp_course_level ) {
			$target_level = 'Graduate';
		} elseif ( 'Undergraduate' === $wp_course_level ) {
			$target_level = 'Undergraduate';
		}

		foreach ( $body as $course ) {
			// Cast or access via snake_case array keys.
			$level   = $course['Level'] ?? '';
			$section = $course['SectionName'] ?? '';

			$match_found = false;

			if ( $target_level && false !== strpos( $level, $target_level ) ) {
				$match_found = true;
			} elseif ( '' === $level ) {
				$match_found = true;
			} elseif ( 'Independent Academic Work' === $level && 'Undergraduate' === $target_level ) {
				$match_found = true;
			}

			if ( $match_found ) {
				$number       = $course['OfferingName'] ?? '';
				$clean_number = preg_replace( '/[^A-Za-z0-9\-]/', '', $number );

				$term_raw   = $course['Term_IDR'] ?? '';
				$clean_term = rawurlencode( $term_raw );

				$details_url   = "https://sis.jhu.edu/api/classes/{$clean_number}{$section}/{$clean_term}?key={$key}";
				$course_data[] = $details_url;
			}
		}
	}
);

// 7. Fetch Details and Build Output.
$output = '';

if ( ! empty( $course_data ) ) {
	$course_curl->get(
		$course_data,
		function ( $result ) use ( &$output ) {
			if ( empty( $result->body ) ) {
				return;
			}

			$body = ! is_array( $result->body ) ? json_decode( html_entity_decode( $result->body ), true ) : json_decode( wp_json_encode( $result->body ), true );

			if ( empty( $body ) || ! isset( $body[0] ) ) {
				return;
			}

			$course         = $body[0];
			$title          = $course['Title'] ?? 'No Title';
			$term           = $course['Term_IDR'] ?? '';
			$course_number  = $course['OfferingName'] ?? '';
			$section_number = $course['SectionName'] ?? '';
			$instructor     = $course['InstructorsFullName'] ?? 'Staff';
			$location       = $course['Location'] ?? '';
			$meetings       = $course['Meetings'] ?? 'TBA';
			$status         = $course['Status'] ?? 'N/A';
			$seats          = $course['SeatsAvailable'] ?? '0';

			$section_details = isset( $course['SectionDetails'][0] ) ? $course['SectionDetails'][0] : null;
			$description     = $section_details['Description'] ?? 'No description available.';
			$credits         = $section_details['Credits'] ?? '';

			// Room parsing.
			$room        = $section_details['Meetings'][0]['Building'] ?? '';
			$roomnumber  = $section_details['Meetings'][0]['Room'] ?? '';
			$room2       = '';
			$roomnumber2 = '';

			if ( isset( $section_details['Meetings'][1] ) && is_array( $section_details['Meetings'][1] ) ) {
				$second_meeting = $section_details['Meetings'][1];
				$room2          = $second_meeting['Building'] ?? '';
				$roomnumber2    = $second_meeting['Room'] ?? '';
			}

			$room_info = trim( $room . ' ' . $roomnumber );
			if ( ! empty( $room2 ) || ! empty( $roomnumber2 ) ) {
				$room_info .= '; ' . trim( $room2 . ' ' . $roomnumber2 );
			}

			$location_display = ( 'online' === strtolower( trim( $location ) ) ) ? 'Online' : $room_info;

			// Tags parsing.
			$tags = array();
			if ( isset( $section_details['PosTags'] ) && is_array( $section_details['PosTags'] ) ) {
				foreach ( $section_details['PosTags'] as $tag_item ) {
					if ( isset( $tag_item['Tag'] ) ) {
						$tags[] = $tag_item['Tag'];
					}
				}
			}
			$print_tags = empty( $tags ) ? 'n/a' : implode( ', ', $tags );

			ob_start();
			?>
			<tr>
				<td><?php echo esc_html( $course_number ); ?>&nbsp;(<?php echo esc_html( $section_number ); ?>)</td>
				<td><?php echo esc_html( $title ); ?></td>
				<td><?php echo esc_html( $meetings ); ?></td>
				<td><?php echo esc_html( $instructor ); ?></td>
				<td><?php echo esc_html( $location_display ); ?></td>
				<td><?php echo esc_html( $term ); ?></td>
				<td class="none">
					<div class="course-details-accordion">
						<ul class="additional-info">
							<li><strong>Description:</strong> <?php echo wp_kses_post( $description ); ?></li>
							<li><strong>Credits:</strong> <?php echo esc_html( $credits ); ?></li>
							<li><strong>Status:</strong> <?php echo esc_html( $status ); ?></li>
							<li><strong>Seats Available:</strong> <?php echo esc_html( $seats ); ?></li>
							<li><strong>Tags:</strong> <?php echo esc_html( $print_tags ); ?></li>
						</ul>
					</div>
				</td>
			</tr>
			<?php
			$output .= ob_get_clean();
		}
	);
}
?>

<main id="site-content" class="mx-auto prose site-main sm:prose lg:prose-lg">

	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', 'page' );
	endwhile;
	?>

	<div class="course-listings all-courses">
		<table aria-describedby="tblDescfall" class="course-table">
			<thead>
				<tr>
					<th>Course # (Section)</th>
					<th>Title</th>
					<th>Day/Times</th>
					<th>Instructor</th>
					<th>Location</th>
					<th>Term</th>
					<th class="none">Additional Details</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $output ) ) {
					echo wp_kses_post( $output );
				} else {
					?>
					<tr>
						<td colspan="7"><?php esc_html_e( 'No course listings found.', 'ksas-blocks' ); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
</main><!-- #main -->

<?php
get_footer();