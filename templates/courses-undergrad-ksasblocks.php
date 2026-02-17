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
?>

<?php
// 1. Load Zebra Curl.
require_once plugin_dir_path( __DIR__ ) . '/lib/Zebra_cURL.php';

// 2. Set query string variables.
$sis_options = get_option( 'sis_courses_plugin_option_name' );

// Use rawurlencode to safely handle spaces, ampersands, etc.
$department = isset( $sis_options['department_name_0'] )
	? rawurlencode( $sis_options['department_name_0'] )
	: '';

// Note: Ensure these dates are correct for the current academic cycle.
$fall   = rawurlencode( 'Fall 2025' );
$spring = rawurlencode( 'Spring 2026' );
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

// Cache configuration (14 days).
$site_name = wp_parse_url( get_site_url(), PHP_URL_PATH );
$site_name = trim( $site_name, '/' ); // Clean up slashes.
$course_curl->cache( WP_CONTENT_DIR . '/sis-cache/' . $site_name, 1209600 );

// 4. Construct API URL
// We manually concat Term because SIS often requires "Term=X&Term=Y" format
// which standard http_build_query doesn't always handle the way SIS likes.
$base_url      = 'https://sis.jhu.edu/api/classes';
$query_string  = "?key={$key}&School=" . rawurlencode( 'Krieger School of Arts and Sciences' );
$query_string .= "&Term={$spring}&Term={$summer}&Term={$fall}";
$query_string .= "&Department=AS%20{$department}";

$courses_url = $base_url . $query_string;

$course_data = array();

// 5. Fetch Data
$course_curl->get(
	$courses_url,
	// Pass $key into the closure with 'use' so we don't need to hardcode it again.
	function ( $result ) use ( &$course_data, $key ) {

		if ( empty( $result->body ) ) {
			return;
		}

		// Decode JSON safely.
		$body = ! is_array( $result->body ) ? json_decode( html_entity_decode( $result->body ) ) : $result->body;

		if ( ! is_array( $body ) ) {
			return;
		}

		// PERFORMANCE FIX: Get the WP field ONCE, outside the loop.
		$wp_course_level = get_field( 'course_level' );
		$target_level    = '';

		if ( $wp_course_level === 'Graduate' ) {
			$target_level = 'Graduate';
		} elseif ( $wp_course_level === 'Undergraduate' ) {
			$target_level = 'Undergraduate';
		}

		foreach ( $body as $course ) {
			$level   = $course->{'Level'} ?? '';
			$section = $course->{'SectionName'} ?? '';

			// Logic Check:
			// 1. Is the course level (Grad/Undergrad) found in the SIS Level string?
			// 2. Or is the SIS Level missing?
			// 3. Or is it "Independent Academic Work" AND we are looking for Undergrad?
			$match_found = false;

			if ( $target_level && strpos( $level, $target_level ) !== false ) {
				$match_found = true;
			} elseif ( $level === '' ) {
				$match_found = true;
			} elseif ( $level === 'Independent Academic Work' && $target_level === 'Undergraduate' ) {
				$match_found = true;
			}

			if ( $match_found ) {
				$number       = $course->{'OfferingName'};
				$clean_number = preg_replace( '/[^A-Za-z0-9\-]/', '', $number );

				// Safe URL encoding for the term lookup.
				$term_raw   = $course->{'Term_IDR'};
				$clean_term = rawurlencode( $term_raw );

				// Construct details URL.
				$details_url = "https://sis.jhu.edu/api/classes/{$clean_number}{$section}/{$clean_term}?key={$key}";

				$course_data[] = $details_url;
			}
		}
	}
);

	// Now that we have the first set of data.
	$course_curl->get(
		$course_data,
		function ( $result ) use ( &$output ) {
			$body = ! is_array( $result->body ) ? json_decode( html_entity_decode( $result->body ) ) : $result->body;

			if ( empty( $body ) || ! isset( $body[0] ) ) {
				return;
			}

			$course              = $body[0];
			$title               = $course->{'Title'} ?? 'No Title';
			$term                = $course->{'Term_IDR'} ?? '';
			$clean_term          = str_replace( ' ', '-', $term );
			$course_number       = $course->{'OfferingName'} ?? '';
			$clean_course_number = preg_replace( '/[^A-Za-z0-9\-]/', '', $course_number );
			$section_number      = $course->{'SectionName'} ?? '';
			$instructor          = $course->{'InstructorsFullName'} ?? 'Staff';
			$location            = $course->{'Location'} ?? '';
			$meetings            = $course->{'Meetings'} ?? 'TBA';
			$status              = $course->{'Status'} ?? 'N/A';
			$seats               = $course->{'SeatsAvailable'} ?? '0';

			$section_details = $course->{'SectionDetails'}[0] ?? null;
			$description     = $section_details->{'Description'} ?? 'No description available.';
			$credits         = $section_details->{'Credits'} ?? '';
			// --- NEW ROOM LOGIC ---
			$room        = $section_details->{'Meetings'}[0]->{'Building'} ?? '';
			$roomnumber  = $section_details->{'Meetings'}[0]->{'Room'} ?? '';
			$room2       = '';
			$roomnumber2 = '';
			if ( isset( $section_details->{'Meetings'}[1] ) && is_object( $section_details->{'Meetings'}[1] ) ) {
				$second_meeting = $section_details->{'Meetings'}[1];
				$room2          = $second_meeting->{'Building'} ?? '';
				$roomnumber2    = $second_meeting->{'Room'} ?? '';
			}
			$room_info = trim( $room . ' ' . $roomnumber );
			if ( ! empty( $room2 ) || ! empty( $roomnumber2 ) ) {
				$room_info .= '; ' . trim( $room2 . ' ' . $roomnumber2 );
			}

			$location_display = ( strtolower( trim( $location ) ) === 'online' ) ? 'Online' : $room_info;

			// --- NEW TAGS LOGIC ---
			$tags = array();
			if ( isset( $section_details->{'PosTags'} ) && is_array( $section_details->{'PosTags'} ) ) {
				foreach ( $section_details->{'PosTags'} as $tag ) {
					$tags[] = $tag->{'Tag'};
				}
			}
			$print_tags = empty( $tags ) ? 'n/a' : implode( ', ', $tags );

			// Modal ID - must be consistent for JS to find it.
			$unique_id = esc_attr( 'course-' . $clean_course_number . $section_number . $clean_term );

			ob_start(); // Buffer the output to avoid PHPCS "Direct echo" warnings in some contexts.
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

	?>

<main id="site-content" class="mx-auto prose site-main sm:prose lg:prose-lg">

	<?php
	while ( have_posts() ) :
		the_post();

		get_template_part( 'template-parts/content', 'page' );

	endwhile; // End of the loop.
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
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $output;
			?>
		</tbody>
	</table>
	</div>
</main><!-- #main -->

<?php
get_footer();
