<?php
/**
 * Admin view: Leader Applicants dashboard (Leaders Training expressions of interest).
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Filters.
$filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_status'] ) ) : '';
$filter_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$where_parts = array( '1=1' );
$args        = array();

if ( $filter_status ) {
	$where_parts[] = 'a.status = %s';
	$args[]        = $filter_status;
}
if ( $filter_search ) {
	$where_parts[] = '(a.full_name LIKE %s OR a.email LIKE %s OR a.town_region LIKE %s)';
	$like          = '%' . $wpdb->esc_like( $filter_search ) . '%';
	$args[]        = $like;
	$args[]        = $like;
	$args[]        = $like;
}

$where = implode( ' AND ', $where_parts );
$sql   = "SELECT a.* FROM {$wpdb->prefix}fc_leader_applicants a WHERE {$where} ORDER BY a.applied_at DESC";

$applicants = $args
	? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	: $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$edit_applicant = isset( $_GET['edit'] ) ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_leader_applicants WHERE id = %d", absint( $_GET['edit'] ) ) ) : null;
?>
<div class="wrap fc-courses-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'FC Courses — Leader Applicants (Leaders Training)', 'fc-courses' ); ?></h1>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Applicant updated.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Applicant deleted.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>

	<!-- Filters -->
	<form method="get" class="fc-filters">
		<input type="hidden" name="page" value="fc-courses-leader-applicants">
		<select name="filter_status">
			<option value=""><?php esc_html_e( '— All Statuses —', 'fc-courses' ); ?></option>
			<?php foreach ( array( 'pending', 'approved', 'rejected', 'enrolled' ) as $s ) : ?>
				<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $filter_status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="search" name="s" value="<?php echo esc_attr( $filter_search ); ?>" placeholder="<?php esc_attr_e( 'Search name / email…', 'fc-courses' ); ?>">
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'fc-courses' ); ?></button>
	</form>

	<!-- Edit / decision panel -->
	<?php if ( $edit_applicant ) : ?>
	<div class="fc-edit-panel">
		<h2><?php esc_html_e( 'Review Leader Applicant', 'fc-courses' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fc_update_leader_applicant">
			<input type="hidden" name="applicant_id" value="<?php echo esc_attr( $edit_applicant->id ); ?>">
			<?php wp_nonce_field( 'fc_update_leader_applicant' ); ?>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Full Name', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->full_name ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Type', 'fc-courses' ); ?></th>
					<td><?php echo 'clinician' === $edit_applicant->participant_type ? esc_html__( 'Clinician', 'fc-courses' ) : esc_html__( 'Whānau member', 'fc-courses' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Email', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->email ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Phone', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->phone ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->town_region ); ?></td>
				</tr>
				<?php if ( 'clinician' === $edit_applicant->participant_type ) : ?>
				<tr>
					<th><?php esc_html_e( 'Profession', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->profession ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Place of Employment', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->place_of_employment ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Management Approval', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( ucfirst( $edit_applicant->management_approval ) ); ?></td>
				</tr>
				<?php if ( ! empty( $edit_applicant->dbt_trained ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'DBT Trained', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( ucfirst( $edit_applicant->dbt_trained ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->billing_contact ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Billing Contact', 'fc-courses' ); ?></th>
					<td><?php echo nl2br( esc_html( $edit_applicant->billing_contact ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php else : ?>
				<?php if ( ! empty( $edit_applicant->fc_participation ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'FC Participation', 'fc-courses' ); ?></th>
					<td><?php echo nl2br( esc_html( $edit_applicant->fc_participation ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->leader_endorsement ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Leader Endorsement', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->leader_endorsement ); ?></td>
				</tr>
				<?php endif; ?>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->ethnicity ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Ethnicity', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->ethnicity ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->training_dates ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Training Dates', 'fc-courses' ); ?></th>
					<td><?php echo nl2br( esc_html( $edit_applicant->training_dates ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->payment_method ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Payment Method', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( ucwords( str_replace( '_', ' ', $edit_applicant->payment_method ) ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->payment_reference ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Payment Reference', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( $edit_applicant->payment_reference ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $edit_applicant->approval_code ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Approval Code', 'fc-courses' ); ?></th>
					<td><code><?php echo esc_html( $edit_applicant->approval_code ); ?></code></td>
				</tr>
				<?php endif; ?>
				<tr>
					<th><?php esc_html_e( 'Applied', 'fc-courses' ); ?></th>
					<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $edit_applicant->applied_at ) ) ); ?></td>
				</tr>
				<tr>
					<th><label for="applicant_status"><?php esc_html_e( 'Decision', 'fc-courses' ); ?></label></th>
					<td>
						<select name="status" id="applicant_status">
							<?php foreach ( array( 'pending', 'approved', 'rejected' ) as $s ) : ?>
								<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $edit_applicant->status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Setting to Approved generates a unique code and emails it to the applicant. Setting to Rejected sends the rejection email.', 'fc-courses' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="applicant_notes"><?php esc_html_e( 'Internal Notes', 'fc-courses' ); ?></label></th>
					<td><textarea name="notes" id="applicant_notes" rows="4" class="large-text"><?php echo esc_textarea( $edit_applicant->notes ); ?></textarea></td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Decision', 'fc-courses' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-leader-applicants' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
			</p>
		</form>
	</div>
	<?php endif; ?>

	<!-- Table -->
	<table class="wp-list-table widefat fixed striped fc-enrollments-table">
		<thead>
			<tr>
				<th style="width:40px"><?php esc_html_e( 'ID', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Name', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Type', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Email', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Phone', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Status', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Applied', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'fc-courses' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $applicants ) : ?>
				<?php foreach ( $applicants as $a ) : ?>
				<tr>
					<td><?php echo esc_html( $a->id ); ?></td>
					<td><?php echo esc_html( $a->full_name ); ?></td>
					<td><?php echo 'clinician' === $a->participant_type ? esc_html__( 'Clinician', 'fc-courses' ) : esc_html__( 'Whānau', 'fc-courses' ); ?></td>
					<td><?php echo esc_html( $a->email ); ?></td>
					<td><?php echo esc_html( $a->phone ); ?></td>
					<td><?php echo esc_html( $a->town_region ); ?></td>
					<td><span class="fc-status fc-status-<?php echo esc_attr( $a->status ); ?>"><?php echo esc_html( ucfirst( $a->status ) ); ?></span></td>
					<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $a->applied_at ) ) ); ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-leader-applicants&edit=' . $a->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Review', 'fc-courses' ); ?></a>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fc_delete_leader_applicant&applicant_id=' . $a->id ), 'fc_delete_leader_applicant' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this applicant?', 'fc-courses' ); ?>')"><?php esc_html_e( 'Delete', 'fc-courses' ); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="9"><?php esc_html_e( 'No leader applicants found.', 'fc-courses' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
