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

$edit_course_id = isset( $_GET['edit_course'] ) ? absint( $_GET['edit_course'] ) : 0;

$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_courses ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$edit_course = $edit_course_id
	? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE id = %d", $edit_course_id ) )
	: null;

// The form is shown by default only when editing an existing course.
$show_form = (bool) $edit_course;
?>
<div class="wrap fc-courses-wrap">
	<h1>
		<?php esc_html_e( 'FC Courses — Courses', 'fc-courses' ); ?>
		<?php if ( ! $edit_course ) : ?>
			<button type="button" id="fc-toggle-course-form" class="page-title-action fc-add-toggle">
				<?php esc_html_e( 'Add New Course', 'fc-courses' ); ?>
			</button>
		<?php endif; ?>
	</h1>

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
							<td><?php echo 'paid' === $c->course_type ? esc_html( FC_Courses_Shortcodes::currency_symbol( $c->currency ) . number_format( (float) $c->price, 2 ) ) : esc_html__( 'Free', 'fc-courses' ); ?></td>
							<td><?php echo esc_html( ucfirst( $c->status ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses&edit_course=' . $c->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'fc-courses' ); ?></a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-course-dates&course_id=' . $c->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Dates', 'fc-courses' ); ?></a>
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
		<div class="fc-courses-form-col" id="fc-course-form-col" <?php echo $show_form ? '' : 'style="display:none"'; ?>>
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
						<th><label for="price"><?php esc_html_e( 'Price (NZ$)', 'fc-courses' ); ?></label></th>
						<td><input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo esc_attr( $edit_course->price ?? '0.00' ); ?>" class="small-text"></td>
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
				<input type="hidden" name="currency" value="NZD">
				<p class="submit">
					<button type="submit" class="button button-primary"><?php echo $edit_course ? esc_html__( 'Update Course', 'fc-courses' ) : esc_html__( 'Add Course', 'fc-courses' ); ?></button>
					<?php if ( $edit_course ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-courses' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
					<?php else : ?>
						<button type="button" id="fc-cancel-course-form" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></button>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</div>
</div>
