<?php
/**
 * Admin view: Enrolments dashboard.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Filters.
$filter_course   = isset( $_GET['filter_course'] ) ? absint( $_GET['filter_course'] ) : 0;
$filter_status   = isset( $_GET['filter_status'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_status'] ) ) : '';
$filter_search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$courses = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}fc_courses ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// Build WHERE.
$where_parts = array( '1=1' );
$args        = array();

if ( $filter_course > 0 ) {
	$where_parts[] = 'e.course_id = %d';
	$args[]        = $filter_course;
}
if ( $filter_status ) {
	$where_parts[] = 'e.payment_status = %s';
	$args[]        = $filter_status;
}
if ( $filter_search ) {
	$where_parts[] = '(e.first_name LIKE %s OR e.last_name LIKE %s OR e.email LIKE %s)';
	$like          = '%' . $wpdb->esc_like( $filter_search ) . '%';
	$args[]        = $like;
	$args[]        = $like;
	$args[]        = $like;
}

$where = implode( ' AND ', $where_parts );
$sql   = "SELECT e.*, c.title AS course_title, cd.start_date AS date_start
          FROM {$wpdb->prefix}fc_enrollments e
          LEFT JOIN {$wpdb->prefix}fc_courses c ON c.id = e.course_id
          LEFT JOIN {$wpdb->prefix}fc_course_dates cd ON cd.id = e.course_date_id
          WHERE {$where}
          ORDER BY e.enrolled_at DESC";

$enrollments = $args
	? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	: $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// Selected enrollment for edit panel.
$edit_enrollment = isset( $_GET['edit'] ) ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_enrollments WHERE id = %d", absint( $_GET['edit'] ) ) ) : null;
?>
<div class="wrap fc-courses-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'FC Courses — Enrolments', 'fc-courses' ); ?></h1>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Enrolment updated.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Enrolment deleted.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>

	<!-- Filters -->
	<form method="get" class="fc-filters">
		<input type="hidden" name="page" value="fc-courses">
		<select name="filter_course">
			<option value=""><?php esc_html_e( '— All Courses —', 'fc-courses' ); ?></option>
			<?php foreach ( $courses as $c ) : ?>
				<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $filter_course, $c->id ); ?>><?php echo esc_html( $c->title ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="filter_status">
			<option value=""><?php esc_html_e( '— All Statuses —', 'fc-courses' ); ?></option>
			<?php foreach ( array( 'pending', 'paid', 'cancelled', 'refunded' ) as $s ) : ?>
				<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $filter_status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="search" name="s" value="<?php echo esc_attr( $filter_search ); ?>" placeholder="<?php esc_attr_e( 'Search name / email…', 'fc-courses' ); ?>">
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'fc-courses' ); ?></button>
	</form>

	<!-- Edit panel -->
	<?php if ( $edit_enrollment ) : ?>
	<div class="fc-edit-panel">
		<h2><?php esc_html_e( 'Edit Enrolment', 'fc-courses' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fc_update_enrollment">
			<input type="hidden" name="enrollment_id" value="<?php echo esc_attr( $edit_enrollment->id ); ?>">
			<?php wp_nonce_field( 'fc_update_enrollment' ); ?>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Name', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_enrollment->first_name . ' ' . $edit_enrollment->last_name ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Email', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_enrollment->email ); ?></td>
				</tr>
				<tr>
					<th><label for="payment_status"><?php esc_html_e( 'Payment Status', 'fc-courses' ); ?></label></th>
					<td>
						<select name="payment_status" id="payment_status">
							<?php foreach ( array( 'pending', 'paid', 'cancelled', 'refunded' ) as $s ) : ?>
								<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $edit_enrollment->payment_status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="notes"><?php esc_html_e( 'Notes', 'fc-courses' ); ?></label></th>
					<td><textarea name="notes" id="notes" rows="4" class="large-text"><?php echo esc_textarea( $edit_enrollment->notes ); ?></textarea></td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Update Enrolment', 'fc-courses' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
			</p>
		</form>
	</div>
	<?php endif; ?>

	<!-- Table -->
	<table class="wp-list-table widefat fixed striped fc-enrollments-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Name', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Email', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Course', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Date', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Payment', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Status', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Enrolled', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'fc-courses' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $enrollments ) : ?>
				<?php foreach ( $enrollments as $e ) : ?>
				<tr>
					<td><?php echo esc_html( $e->id ); ?></td>
					<td><?php echo esc_html( $e->first_name . ' ' . $e->last_name ); ?></td>
					<td><?php echo esc_html( $e->email ); ?></td>
					<td><?php echo esc_html( $e->course_title ); ?></td>
					<td><?php echo $e->date_start ? esc_html( wp_date( get_option( 'date_format' ), strtotime( $e->date_start ) ) ) : '—'; ?></td>
					<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $e->payment_method ) ) ); ?></td>
					<td><span class="fc-status fc-status-<?php echo esc_attr( $e->payment_status ); ?>"><?php echo esc_html( ucfirst( $e->payment_status ) ); ?></span></td>
					<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $e->enrolled_at ) ) ); ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses&edit=' . $e->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'fc-courses' ); ?></a>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fc_delete_enrollment&enrollment_id=' . $e->id ), 'fc_delete_enrollment' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this enrolment?', 'fc-courses' ); ?>')"><?php esc_html_e( 'Delete', 'fc-courses' ); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="9"><?php esc_html_e( 'No enrolments found.', 'fc-courses' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
