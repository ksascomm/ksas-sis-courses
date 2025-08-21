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
	// Load Zebra Curl.
	require plugin_dir_path( __DIR__ ) . '/lib/Zebra_cURL.php';

	// Set query string variables.
	$sis_courses_plugin_options = get_option( 'sis_courses_plugin_option_name' );
	$department_unclean         = $sis_courses_plugin_options['department_name_0'];
	$department                 = str_replace( ' ', '%20', $department_unclean );
	$department                 = str_replace( '&', '%26', $department );
	$fall                       = 'fall%202025';
	$summer                     = 'summer%202025';
	$spring                     = 'spring%202025';
	$open                       = 'open';
	$approval                   = 'approval%20required';
	$closed                     = 'closed';
	$waitlist                   = 'waitlist%20only';
	$reserved_open              = 'reserved%20open';
	$key                        = '0jCaUO1bHwbG1sFEKQd3iXgBgxoDUOhR';

	// Create first Zebra Curl class.
	$course_curl = new Zebra_cURL();
	$course_curl->option(
		array(
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_CONNECTTIMEOUT => 60,
		)
	);
	// Cache for 14 days.
	$site_name = get_site()->path;
	$course_curl->cache( WP_CONTENT_DIR . '/sis-cache/' . $site_name, 1209600 );

	// Create API Url calls.
	$courses_fall_url = 'https://sis.jhu.edu/api/classes?key=' . $key . '&School=Krieger%20School%20of%20Arts%20and%20Sciences&Term=' . $spring . '&Term=' . $summer . '&Term=' . $fall . '&Department=AS%20' . $department . '&status=' . $open . '&status=' . $approval . '&status=' . $waitlist . '&status=' . $reserved_open;

	$course_data = array();
	$output      = '';

	// get the first set of data.
	$course_curl->get(
		$courses_fall_url,
		function ( $result ) use ( &$course_data ) {

			$key = '0jCaUO1bHwbG1sFEKQd3iXgBgxoDUOhR';

			if ( ( is_array( $result ) && ! empty( $result ) ) || is_object( $result ) ) {

				$result->body = ! is_array( $result->body ) ? json_decode( html_entity_decode( $result->body ) ) : $result->body;

				foreach ( $result->body as $course ) {

					$section = $course->{'SectionName'};
					$level   = $course->{'Level'};
					$course_level_field = get_field( 'course_level' );
					if ( get_field( 'course_level' ) === 'Graduate' ) {
						$parent = 'Graduate';
					} elseif ( get_field( 'course_level' ) === 'Undergraduate' ) {
						$parent = 'Undergraduate';
					}
					if (
						strpos( $level, $parent ) !== false ||
						$level === '' ||
						( $level === 'Independent Academic Work' && $course_level_field === 'Undergraduate' )
					) {
						$number       = $course->{'OfferingName'};
						$clean_number = preg_replace( '/[^A-Za-z0-9\-]/', '', $number );
						$dirty_term   = $course->{'Term_IDR'};
						$clean_term   = str_replace( ' ', '%20', $dirty_term );
						$details_url  = 'https://sis.jhu.edu/api/classes/' . $clean_number . $section . '/' . $clean_term . '?key=' . $key;

						// add to array!
						$course_data[] = $details_url;
					}
				}
			}
		}
	);

	// Now that we have the first set of data.
	$course_curl->get(
		$course_data,
		function ( $result ) use ( &$output ) {

			$result->body = ! is_array( $result->body ) ? json_decode( html_entity_decode( $result->body ) ) : $result->body;

			$title               = $result->body[0]->{'Title'};
			$term                = $result->body[0]->{'Term_IDR'};
			$clean_term          = str_replace( ' ', '-', $term );
			$meetings            = $result->body[0]->{'Meetings'};
			$status              = $result->body[0]->{'Status'};
			$seatsavailable      = $result->body[0]->{'SeatsAvailable'};
			$course_number       = $result->body[0]->{'OfferingName'};
			$clean_course_number = preg_replace( '/[^A-Za-z0-9\-]/', '', $course_number );
			$credits             = $result->body[0]->{'Credits'};
			$section_number      = $result->body[0]->{'SectionName'};
			$instructor          = $result->body[0]->{'InstructorsFullName'};
			$course_level        = $result->body[0]->{'Level'};
			$location            = $result->body[0]->{'Location'};
			$description         = $result->body[0]->{'SectionDetails'}[0]->{'Description'};
			$room                = $result->body[0]->{'SectionDetails'}[0]->{'Meetings'}[0]->{'Building'};
			$roomnumber          = $result->body[0]->{'SectionDetails'}[0]->{'Meetings'}[0]->{'Room'};
			$room2 = '';
			$roomnumber2 = '';
			if (
				isset( $result->body[0]->{'SectionDetails'}[0]->{'Meetings'}[1] ) &&
				is_object( $result->body[0]->{'SectionDetails'}[0]->{'Meetings'}[1] )
			) {
				$second_meeting = $result->body[0]->{'SectionDetails'}[0]->{'Meetings'}[1];
				$room2 = isset( $second_meeting->{'Building'} ) ? $second_meeting->{'Building'} : '';
				$roomnumber2 = isset( $second_meeting->{'Room'} ) ? $second_meeting->{'Room'} : '';
			}
			// Build room info with or without the second room.
			$room_info = $room . '&nbsp;' . $roomnumber;
			// Add second room only if it exists.
			if ( ! empty( $room2 ) || ! empty( $roomnumber2 ) ) {
				$room_info .= '; ' . $room2 . '&nbsp;' . $roomnumber2;
			}
			$sectiondetails      = $result->body[0]->{'SectionDetails'}[0];
			$tags                = array();

			if ( isset( $sectiondetails->{'PosTags'} ) ) {
				if ( ! empty( $sectiondetails->{'PosTags'} ) ) {
						$postag = $sectiondetails->{'PosTags'};
					foreach ( $postag as $tag ) {
						$tags[] = $tag->{'Tag'};
					}
				}
			}
			$print_tags = empty( $tags ) ? 'n/a' : implode( ', ', $tags );

			$output .= '<tr><td>' . $course_number . '&nbsp;(' . $section_number . ')</td><td>' . $title . '</td><td class="show-for-medium">' . $meetings . '</td><td class="show-for-medium">' . $instructor . '</td><td class="show-for-medium">' . $room_info . '</td><td class="show-for-medium">' . $term . '</td>';

			$output .= '<td><p class="hidden">' . $description . '</p><button class="px-2 text-white modal-button bg-blue hover:text-black hover:bg-blue-light" href="#course-' . $clean_course_number . $section_number . $clean_term . '">More Info<span class="sr-only">-' . $title . '-' . $section_number . '</span></button></td></tr>';

			$output .= '<div class="modal" id="course-' . $clean_course_number . $section_number . $clean_term . '"><div class="modal-content"><div class="modal-header"><span class="close">×</span><h1 id="' . $clean_term . $course_number . '-' . $section_number . '">' . $title . '<br><small>' . $course_number . '&nbsp;(' . $section_number . ')</small></h1></div><div class="modal-body"><p>' . $description . '</p><ul><li><strong>Days/Times:</strong> ' . $meetings . ' </li><li><strong>Instructor:</strong> ' . $instructor . ' </li><li><strong>Room:</strong> ' . $room_info . '</li><li><strong>Status:</strong> ' . $status . '</li><li><strong>Seats Available:</strong> ' . $seatsavailable . '</li><li><strong>PosTag(s):</strong> ' . $print_tags . '</li></ul></div></div></div>';
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
				<th data-priority="1">Course # (Section)</th>
				<th>Title</th>
				<th>Day/Times</th>
				<th>Instructor</th>
				<th>Location</th>
				<th>Term</th>
				<th data-priority="2">Course Details</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $output; ?>
		</tbody>
	</table>
	</div>
</main><!-- #main -->

<?php
get_footer();
