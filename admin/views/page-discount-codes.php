<?php
/**
 * Admin view: Discount codes.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$edit_id   = isset( $_GET['edit_code'] ) ? absint( $_GET['edit_code'] ) : 0;
$edit_code = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_discount_codes WHERE id = %d", $edit_id ) ) : null;
$courses   = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}fc_courses ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$codes     = $wpdb->get_results( "SELECT dc.*, c.title AS course_title FROM {$wpdb->prefix}fc_discount_codes dc LEFT JOIN {$wpdb->prefix}fc_courses c ON c.id = dc.course_id ORDER BY dc.created_at DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
?>
<div class="wrap fc-courses-wrap">
	<h1><?php esc_html_e( 'FC Courses — Discount Codes', 'fc-courses' ); ?></h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Discount code saved.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Discount code deleted.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>

	<div class="fc-courses-columns">

		<!-- Codes list -->
		<div class="fc-courses-list-col">
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Code', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Discount', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Course', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Uses', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Expires', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Status', 'fc-courses' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'fc-courses' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $codes ) : ?>
						<?php foreach ( $codes as $dc ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $dc->code ); ?></strong></td>
							<td>
								<?php
								if ( 'percentage' === $dc->discount_type ) {
									echo esc_html( (int) $dc->discount_value . '%' );
								} else {
									echo esc_html( get_option( 'fc_currency', 'NZD' ) . ' ' . number_format( (float) $dc->discount_value, 2 ) );
								}
								?>
							</td>
							<td><?php echo $dc->course_title ? esc_html( $dc->course_title ) : esc_html__( 'All courses', 'fc-courses' ); ?></td>
							<td><?php echo esc_html( $dc->uses_count ) . ( $dc->max_uses > 0 ? ' / ' . esc_html( $dc->max_uses ) : '' ); ?></td>
							<td><?php echo $dc->expires_at ? esc_html( wp_date( get_option( 'date_format' ), strtotime( $dc->expires_at ) ) ) : '—'; ?></td>
							<td><span class="fc-status fc-status-<?php echo esc_attr( $dc->status ); ?>"><?php echo esc_html( ucfirst( $dc->status ) ); ?></span></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-discount-codes&edit_code=' . $dc->id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'fc-courses' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=fc_delete_discount_code&code_id=' . $dc->id ), 'fc_delete_discount_code' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this discount code?', 'fc-courses' ); ?>')"><?php esc_html_e( 'Delete', 'fc-courses' ); ?></a>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No discount codes yet.', 'fc-courses' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Add / Edit form -->
		<div class="fc-courses-form-col">
			<h2><?php echo $edit_code ? esc_html__( 'Edit Discount Code', 'fc-courses' ) : esc_html__( 'Add New Code', 'fc-courses' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fc_save_discount_code">
				<?php if ( $edit_code ) : ?>
					<input type="hidden" name="code_id" value="<?php echo esc_attr( $edit_code->id ); ?>">
				<?php endif; ?>
				<?php wp_nonce_field( 'fc_save_discount_code' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="code"><?php esc_html_e( 'Code', 'fc-courses' ); ?> *</label></th>
						<td>
							<input type="text" name="code" id="code" class="regular-text" required value="<?php echo esc_attr( $edit_code->code ?? '' ); ?>" style="text-transform:uppercase">
							<p class="description"><?php esc_html_e( 'Alphanumeric, no spaces. Will be stored upper-case.', 'fc-courses' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="dc_description"><?php esc_html_e( 'Description', 'fc-courses' ); ?></label></th>
						<td><input type="text" name="description" id="dc_description" class="regular-text" value="<?php echo esc_attr( $edit_code->description ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="discount_type"><?php esc_html_e( 'Discount Type', 'fc-courses' ); ?></label></th>
						<td>
							<select name="discount_type" id="discount_type">
								<option value="percentage" <?php selected( $edit_code->discount_type ?? 'percentage', 'percentage' ); ?>><?php esc_html_e( 'Percentage (%)', 'fc-courses' ); ?></option>
								<option value="fixed" <?php selected( $edit_code->discount_type ?? '', 'fixed' ); ?>><?php esc_html_e( 'Fixed amount', 'fc-courses' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="discount_value"><?php esc_html_e( 'Value', 'fc-courses' ); ?> *</label></th>
						<td>
							<input type="number" name="discount_value" id="discount_value" step="0.01" min="0" max="100" required value="<?php echo esc_attr( $edit_code->discount_value ?? '' ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'For percentage: 100 = fully free, 50 = half price. For fixed: amount to subtract.', 'fc-courses' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="max_uses"><?php esc_html_e( 'Max Uses (0 = unlimited)', 'fc-courses' ); ?></label></th>
						<td><input type="number" name="max_uses" id="max_uses" min="0" value="<?php echo esc_attr( $edit_code->max_uses ?? 0 ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><label for="dc_course_id"><?php esc_html_e( 'Restrict to Course', 'fc-courses' ); ?></label></th>
						<td>
							<select name="course_id" id="dc_course_id">
								<option value=""><?php esc_html_e( '— All courses —', 'fc-courses' ); ?></option>
								<?php foreach ( $courses as $c ) : ?>
									<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $edit_code->course_id ?? '', $c->id ); ?>><?php echo esc_html( $c->title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="expires_at"><?php esc_html_e( 'Expires At', 'fc-courses' ); ?></label></th>
						<td><input type="datetime-local" name="expires_at" id="expires_at" value="<?php echo $edit_code && $edit_code->expires_at ? esc_attr( wp_date( 'Y-m-d\TH:i', strtotime( $edit_code->expires_at ) ) ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="dc_status"><?php esc_html_e( 'Status', 'fc-courses' ); ?></label></th>
						<td>
							<select name="status" id="dc_status">
								<option value="active" <?php selected( $edit_code->status ?? 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'fc-courses' ); ?></option>
								<option value="inactive" <?php selected( $edit_code->status ?? '', 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'fc-courses' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" class="button button-primary"><?php echo $edit_code ? esc_html__( 'Update Code', 'fc-courses' ) : esc_html__( 'Add Code', 'fc-courses' ); ?></button>
					<?php if ( $edit_code ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=fc-courses-discount-codes' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'fc-courses' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</div>
</div>
