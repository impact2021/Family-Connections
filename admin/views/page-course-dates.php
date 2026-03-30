<?php
/**
 * Admin view: Course Dates management.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$filter_course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0;
$edit_date_id     = isset( $_GET['edit_date'] ) ? absint( $_GET['edit_date'] ) : 0;

$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_courses ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$edit_date = $edit_date_id
	? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_course_dates WHERE id = %d", $edit_date_id ) )
	: null;

// When editing a date, default the filter to that date's course.
if ( $edit_date && ! $filter_course_id ) {
	$filter_course_id = (int) $edit_date->course_id;
}

$sql  = "SELECT cd.*, c.title AS course_title,
                (SELECT COUNT(*) FROM {$wpdb->prefix}fc_enrollments e WHERE e.course_date_id = cd.id) AS enrolment_count
         FROM {$wpdb->prefix}fc_course_dates cd
         INNER JOIN {$wpdb->prefix}fc_courses c ON c.id = cd.course_id";
$args = array();

if ( $filter_course_id > 0 ) {
	$sql   .= ' WHERE cd.course_id = %d';
	$args[] = $filter_course_id;
}

$sql .= ' ORDER BY cd.start_date DESC';

$dates = $args
	? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	: $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$nz_cities = apply_filters( 'fc_courses_nz_cities', array(
	'Auckland',
	'Wellington',
	'Christchurch',
	'Hamilton',
	'Tauranga',
	'Napier',
	'Hastings',
	'Palmerston North',
	'Nelson',
	'Rotorua',
	'New Plymouth',
	'Whangarei',
	'Dunedin',
	'Invercargill',
	'Other',
) );
?>
<div class="wrap fc-courses-wrap">
	<h1><?php esc_html_e( 'FC Courses — Course Dates', 'fc-courses' ); ?></h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved successfully.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Deleted.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>

	<!-- Course filter -->
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin-bottom:1em">
		<input type="hidden" name="page" value="fc-courses-course-dates">
		<select name="course_id" id="fc_filter_course">
			<option value="0"><?php esc_html_e( '— All Courses —', 'fc-courses' ); ?></option>
			<?php foreach ( $courses as $c ) : ?>
				<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $filter_course_id, (int) $c->id ); ?>>
					<?php echo esc_html( $c->title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'fc-courses' ); ?></button>
		<?php if ( $filter_course_id ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-course-dates' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'fc-courses' ); ?></a>
		<?php endif; ?>
	</form>

	<div class="fc-courses-columns">

		<!-- LEFT: Dates list -->
		<div class="fc-courses-list-col">
			<h2><?php esc_html_e( 'All Course Dates', 'fc-courses' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Course', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Start Date', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'End Date', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Location', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Max', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Enrolled', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Status', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'fc-courses' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $dates ) : ?>
						<?php foreach ( $dates as $d ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $d->course_title ); ?></strong></td>
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $d->start_date ) ) ); ?></td>
							<td><?php echo $d->end_date ? esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $d->end_date ) ) ) : '—'; ?></td>
							<td><?php echo $d->is_online ? esc_html__( 'Online', 'fc-courses' ) : esc_html( $d->location ); ?></td>
							<td><?php echo $d->max_enrolees > 0 ? esc_html( $d->max_enrolees ) : esc_html__( '∞', 'fc-courses' ); ?></td>
							<td><?php echo esc_html( $d->enrolment_count ); ?></td>
							<td><?php echo esc_html( ucfirst( $d->status ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-course-dates&course_id=' . $d->course_id . '&edit_date=' . $d->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'fc-courses' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fc_delete_course_date&date_id=' . $d->id . '&course_id=' . $d->course_id ), 'fc_delete_course_date' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this date?', 'fc-courses' ); ?>')"><?php esc_html_e( 'Delete', 'fc-courses' ); ?></a>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="8"><?php esc_html_e( 'No dates found.', 'fc-courses' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- RIGHT: Add / Edit form -->
		<div class="fc-courses-form-col">
			<h2><?php echo $edit_date ? esc_html__( 'Edit Date', 'fc-courses' ) : esc_html__( 'Add New Date', 'fc-courses' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fc_save_course_date">
				<?php if ( $edit_date ) : ?>
					<input type="hidden" name="date_id" value="<?php echo esc_attr( $edit_date->id ); ?>">
				<?php endif; ?>
				<?php wp_nonce_field( 'fc_save_course_date' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="course_id"><?php esc_html_e( 'Course', 'fc-courses' ); ?> *</label></th>
						<td>
							<select name="course_id" id="course_id" required>
								<option value=""><?php esc_html_e( '— Select a course —', 'fc-courses' ); ?></option>
								<?php foreach ( $courses as $c ) : ?>
									<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $edit_date ? (int) $edit_date->course_id : $filter_course_id, (int) $c->id ); ?>>
										<?php echo esc_html( $c->title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="start_date"><?php esc_html_e( 'Start Date/Time', 'fc-courses' ); ?> *</label></th>
						<td><input type="datetime-local" name="start_date" id="start_date" required value="<?php echo $edit_date ? esc_attr( wp_date( 'Y-m-d\TH:i', strtotime( $edit_date->start_date ) ) ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="end_date"><?php esc_html_e( 'End Date/Time', 'fc-courses' ); ?></label></th>
						<td><input type="datetime-local" name="end_date" id="end_date" value="<?php echo $edit_date && $edit_date->end_date ? esc_attr( wp_date( 'Y-m-d\TH:i', strtotime( $edit_date->end_date ) ) ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="location"><?php esc_html_e( 'City / Region', 'fc-courses' ); ?></label></th>
						<td>
							<select name="location" id="location">
								<option value=""><?php esc_html_e( '— Select city —', 'fc-courses' ); ?></option>
								<?php foreach ( $nz_cities as $city ) : ?>
									<option value="<?php echo esc_attr( $city ); ?>" <?php selected( $edit_date->location ?? '', $city ); ?>><?php echo esc_html( $city ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'New Zealand city or region where this session will be held.', 'fc-courses' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Online', 'fc-courses' ); ?></th>
						<td><label><input type="checkbox" name="is_online" value="1" <?php checked( $edit_date->is_online ?? 0, 1 ); ?>> <?php esc_html_e( 'This is an online session', 'fc-courses' ); ?></label></td>
					</tr>
					<tr>
						<th><label for="date_max"><?php esc_html_e( 'Max enrolees (0 = use course default)', 'fc-courses' ); ?></label></th>
						<td><input type="number" name="max_enrolees" id="date_max" min="0" value="<?php echo esc_attr( $edit_date->max_enrolees ?? 0 ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><label for="date_status"><?php esc_html_e( 'Status', 'fc-courses' ); ?></label></th>
						<td>
							<select name="status" id="date_status">
								<?php foreach ( array( 'open', 'closed', 'full' ) as $s ) : ?>
									<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $edit_date->status ?? 'open', $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" class="button button-primary"><?php echo $edit_date ? esc_html__( 'Update Date', 'fc-courses' ) : esc_html__( 'Add Date', 'fc-courses' ); ?></button>
					<?php if ( $edit_date ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-course-dates&course_id=' . $edit_date->course_id ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</div>
</div>
