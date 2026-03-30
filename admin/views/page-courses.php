<?php
/**
 * Admin view: Courses management.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Which course are we viewing dates for?
$view_course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0;
$edit_course_id = isset( $_GET['edit_course'] ) ? absint( $_GET['edit_course'] ) : 0;
$edit_date_id   = isset( $_GET['edit_date'] ) ? absint( $_GET['edit_date'] ) : 0;

$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_courses ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$edit_course = $edit_course_id
	? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE id = %d", $edit_course_id ) )
	: null;

$view_course = $view_course_id
	? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE id = %d", $view_course_id ) )
	: null;

$edit_date = $edit_date_id
	? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_course_dates WHERE id = %d", $edit_date_id ) )
	: null;

$dates = $view_course
	? $wpdb->get_results( $wpdb->prepare(
		"SELECT cd.*, (SELECT COUNT(*) FROM {$wpdb->prefix}fc_enrollments e WHERE e.course_date_id = cd.id) AS enrolment_count
		 FROM {$wpdb->prefix}fc_course_dates cd WHERE cd.course_id = %d ORDER BY cd.start_date ASC",
		$view_course->id
	) )
	: array();
?>
<div class="wrap fc-courses-wrap">
	<h1><?php esc_html_e( 'FC Courses — Courses', 'fc-courses' ); ?></h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved successfully.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Deleted.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>

	<div class="fc-courses-columns">

		<!-- LEFT: Course list -->
		<div class="fc-courses-list-col">
			<h2><?php esc_html_e( 'All Courses', 'fc-courses' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Type', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Price', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Status', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'fc-courses' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $courses ) : ?>
						<?php foreach ( $courses as $c ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $c->title ); ?></strong></td>
							<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $c->course_type ) ) ); ?></td>
							<td><?php echo 'paid' === $c->course_type ? esc_html( $c->currency . ' ' . number_format( (float) $c->price, 2 ) ) : esc_html__( 'Free', 'fc-courses' ); ?></td>
							<td><?php echo esc_html( ucfirst( $c->status ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses&edit_course=' . $c->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'fc-courses' ); ?></a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses&course_id=' . $c->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Dates', 'fc-courses' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fc_delete_course&course_id=' . $c->id ), 'fc_delete_course' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this course?', 'fc-courses' ); ?>')"><?php esc_html_e( 'Delete', 'fc-courses' ); ?></a>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="5"><?php esc_html_e( 'No courses yet.', 'fc-courses' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- RIGHT: Add / Edit Course form -->
		<div class="fc-courses-form-col">
			<h2><?php echo $edit_course ? esc_html__( 'Edit Course', 'fc-courses' ) : esc_html__( 'Add New Course', 'fc-courses' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fc_save_course">
				<?php if ( $edit_course ) : ?>
					<input type="hidden" name="course_id" value="<?php echo esc_attr( $edit_course->id ); ?>">
				<?php endif; ?>
				<?php wp_nonce_field( 'fc_save_course' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="title"><?php esc_html_e( 'Title', 'fc-courses' ); ?> *</label></th>
						<td><input type="text" name="title" id="title" class="regular-text" required value="<?php echo esc_attr( $edit_course->title ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="description"><?php esc_html_e( 'Description', 'fc-courses' ); ?></label></th>
						<td><textarea name="description" id="description" rows="4" class="large-text"><?php echo esc_textarea( $edit_course->description ?? '' ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="course_type"><?php esc_html_e( 'Type', 'fc-courses' ); ?></label></th>
						<td>
							<select name="course_type" id="course_type">
								<option value="free" <?php selected( $edit_course->course_type ?? 'free', 'free' ); ?>><?php esc_html_e( 'Free', 'fc-courses' ); ?></option>
								<option value="paid" <?php selected( $edit_course->course_type ?? '', 'paid' ); ?>><?php esc_html_e( 'Paid', 'fc-courses' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="price"><?php esc_html_e( 'Price', 'fc-courses' ); ?></label></th>
						<td><input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo esc_attr( $edit_course->price ?? '0.00' ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><label for="currency"><?php esc_html_e( 'Currency', 'fc-courses' ); ?></label></th>
						<td>
							<select name="currency" id="currency">
								<?php foreach ( array( 'NZD', 'GBP', 'EUR', 'USD' ) as $cur ) : ?>
									<option value="<?php echo esc_attr( $cur ); ?>" <?php selected( $edit_course->currency ?? 'NZD', $cur ); ?>><?php echo esc_html( $cur ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="max_enrolees"><?php esc_html_e( 'Max enrolees (0 = unlimited)', 'fc-courses' ); ?></label></th>
						<td><input type="number" name="max_enrolees" id="max_enrolees" min="0" value="<?php echo esc_attr( $edit_course->max_enrolees ?? 0 ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><label for="status"><?php esc_html_e( 'Status', 'fc-courses' ); ?></label></th>
						<td>
							<select name="status" id="status">
								<option value="publish" <?php selected( $edit_course->status ?? 'publish', 'publish' ); ?>><?php esc_html_e( 'Published', 'fc-courses' ); ?></option>
								<option value="draft" <?php selected( $edit_course->status ?? '', 'draft' ); ?>><?php esc_html_e( 'Draft', 'fc-courses' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" class="button button-primary"><?php echo $edit_course ? esc_html__( 'Update Course', 'fc-courses' ) : esc_html__( 'Add Course', 'fc-courses' ); ?></button>
					<?php if ( $edit_course ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</div>

	<!-- Course Dates section -->
	<?php if ( $view_course ) : ?>
	<hr>
	<h2><?php printf( esc_html__( 'Dates for: %s', 'fc-courses' ), esc_html( $view_course->title ) ); ?></h2>

	<div class="fc-courses-columns">
		<div class="fc-courses-list-col">
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
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
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $d->start_date ) ) ); ?></td>
							<td><?php echo $d->end_date ? esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $d->end_date ) ) ) : '—'; ?></td>
							<td><?php echo $d->is_online ? esc_html__( 'Online', 'fc-courses' ) : esc_html( $d->location ); ?></td>
							<td><?php echo $d->max_enrolees > 0 ? esc_html( $d->max_enrolees ) : esc_html__( '∞', 'fc-courses' ); ?></td>
							<td><?php echo esc_html( $d->enrolment_count ); ?></td>
							<td><?php echo esc_html( ucfirst( $d->status ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses&course_id=' . $view_course->id . '&edit_date=' . $d->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'fc-courses' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fc_delete_course_date&date_id=' . $d->id . '&course_id=' . $view_course->id ), 'fc_delete_course_date' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this date?', 'fc-courses' ); ?>')"><?php esc_html_e( 'Delete', 'fc-courses' ); ?></a>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No dates added yet.', 'fc-courses' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="fc-courses-form-col">
			<h3><?php echo $edit_date ? esc_html__( 'Edit Date', 'fc-courses' ) : esc_html__( 'Add New Date', 'fc-courses' ); ?></h3>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fc_save_course_date">
				<input type="hidden" name="course_id" value="<?php echo esc_attr( $view_course->id ); ?>">
				<?php if ( $edit_date ) : ?>
					<input type="hidden" name="date_id" value="<?php echo esc_attr( $edit_date->id ); ?>">
				<?php endif; ?>
				<?php wp_nonce_field( 'fc_save_course_date' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="start_date"><?php esc_html_e( 'Start Date/Time', 'fc-courses' ); ?> *</label></th>
						<td><input type="datetime-local" name="start_date" id="start_date" required value="<?php echo $edit_date ? esc_attr( wp_date( 'Y-m-d\TH:i', strtotime( $edit_date->start_date ) ) ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="end_date"><?php esc_html_e( 'End Date/Time', 'fc-courses' ); ?></label></th>
						<td><input type="datetime-local" name="end_date" id="end_date" value="<?php echo $edit_date && $edit_date->end_date ? esc_attr( wp_date( 'Y-m-d\TH:i', strtotime( $edit_date->end_date ) ) ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="location"><?php esc_html_e( 'Location', 'fc-courses' ); ?></label></th>
						<td><input type="text" name="location" id="location" class="regular-text" value="<?php echo esc_attr( $edit_date->location ?? '' ); ?>"></td>
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
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses&course_id=' . $view_course->id ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</div>
	<?php endif; ?>
</div>
