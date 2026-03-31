<?php
/**
 * Public view: Upcoming course dates calendar / schedule table.
 *
 * Variables available:
 *  $dates             array        – upcoming open course dates (with course_title, enrolment_count, etc.)
 *  $calendar_course   object|null  – course object when course_id is set in shortcode
 *  $course_id         int          – course ID from shortcode (0 = all courses)
 *  $fields            array        – form field configuration
 *  $participant_types array        – allowed participant type options
 *  $form_message      string       – success message (after inline form submission)
 *  $form_error        string       – error message (after inline form submission)
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $dates ) {
	echo '<p class="fc-no-dates">' . esc_html__( 'No upcoming dates are currently scheduled. Please check back soon.', 'fc-courses' ) . '</p>';
	return;
}
?>
<div class="fc-calendar-wrap">

	<?php if ( $form_message ) : ?>
		<div class="fc-notice fc-notice-success"><?php echo esc_html( $form_message ); ?></div>
	<?php endif; ?>

	<?php if ( ! $form_message ) : ?>

	<table class="fc-calendar-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Course', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Date', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Time', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Location', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Places', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Price', 'fc-courses' ); ?></th>
				<th><?php esc_html_e( 'Register', 'fc-courses' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $dates as $d ) :
				$ts       = strtotime( $d->start_date );
				$ts_end   = $d->end_date ? strtotime( $d->end_date ) : null;
				$location = $d->is_online ? __( 'Online', 'fc-courses' ) : ( $d->location ?: '—' );

				// Places remaining.
				if ( $d->max_enrolees > 0 ) {
					$remaining = max( 0, (int) $d->max_enrolees - (int) $d->enrolment_count );
					/* translators: number of places remaining */
					$places_label = $remaining > 0
						? sprintf( _n( '%d place left', '%d places left', $remaining, 'fc-courses' ), $remaining )
						: __( 'Full', 'fc-courses' );
					$places_class = $remaining > 0 ? 'fc-places-available' : 'fc-places-full';
				} else {
					$places_label = __( 'Open', 'fc-courses' );
					$places_class = 'fc-places-available';
				}

				// Price label — use NZ$ symbol instead of currency code.
				if ( 'paid' === $d->course_type && (float) $d->price > 0 ) {
					$price_label = esc_html( FC_Courses_Shortcodes::currency_symbol( $d->currency ) . number_format( (float) $d->price, 2 ) );
				} else {
					$price_label = esc_html__( 'Free', 'fc-courses' );
				}

				// Time string.
				$time_str = esc_html( wp_date( 'H:i', $ts ) );
				if ( $ts_end ) {
					$time_str .= ' – ' . esc_html( wp_date( 'H:i', $ts_end ) );
				}
			?>
			<tr class="fc-calendar-row">
				<td class="fc-cal-course"><?php echo esc_html( $d->course_title ); ?></td>
				<td class="fc-cal-date"><?php echo esc_html( wp_date( get_option( 'date_format' ), $ts ) ); ?></td>
				<td class="fc-cal-time"><?php echo $time_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already escaped above ?></td>
				<td class="fc-cal-location"><?php echo esc_html( $location ); ?></td>
				<td class="fc-cal-places"><span class="<?php echo esc_attr( $places_class ); ?>"><?php echo esc_html( $places_label ); ?></span></td>
				<td class="fc-cal-price"><?php echo $price_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already escaped above ?></td>
				<td class="fc-cal-register">
					<?php if ( 'fc-places-full' !== $places_class ) : ?>
						<?php if ( $calendar_course ) : ?>
							<!-- Inline register: show the form below the table -->
							<button type="button" class="fc-cal-register-btn button"
								data-date-id="<?php echo esc_attr( $d->id ); ?>"
								data-date-label="<?php echo esc_attr( wp_date( get_option( 'date_format' ) . ' H:i', $ts ) . ' — ' . $location ); ?>"
								data-type="<?php echo esc_attr( $d->course_type ); ?>"
								data-price="<?php echo esc_attr( $d->price ); ?>"
								data-currency="<?php echo esc_attr( $d->currency ); ?>">
								<?php esc_html_e( 'Register', 'fc-courses' ); ?>
							</button>
						<?php else : ?>
							<!-- Fallback for multi-course calendar (no course_id set): use the
							     fc_registration_page_id option if it has been configured in the DB. -->
							<?php
							$reg_page_id      = (int) get_option( 'fc_registration_page_id', 0 );
							$registration_url = $reg_page_id > 0 ? get_permalink( $reg_page_id ) : '';
							if ( $registration_url ) :
							?>
							<a href="<?php echo esc_url( add_query_arg( array( 'fc_date' => $d->id, 'fc_course' => $d->course_id ), $registration_url ) ); ?>" class="fc-register-link button">
								<?php esc_html_e( 'Register', 'fc-courses' ); ?>
							</a>
							<?php endif; ?>
						<?php endif; ?>
					<?php else : ?>
						<span class="fc-full-label"><?php esc_html_e( 'Full', 'fc-courses' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $calendar_course ) : ?>
	<!-- Inline registration form – shown when Register is clicked -->
	<div id="fc-calendar-register-wrap" class="fc-calendar-register-wrap" style="display:none">
		<div class="fc-cal-reg-header">
			<h3 id="fc-cal-reg-heading"><?php echo esc_html( $calendar_course->title ); ?></h3>
			<p id="fc-cal-reg-date-label" class="fc-cal-reg-date"></p>
			<?php if ( $form_error ) : ?>
				<div class="fc-notice fc-notice-error"><?php echo esc_html( $form_error ); ?></div>
			<?php endif; ?>
		</div>

		<form class="fc-registration-form fc-cal-inline-form" method="post">
			<?php wp_nonce_field( 'fc_course_register', 'fc_register_nonce' ); ?>
			<input type="hidden" name="fc_inline_calendar" value="1">
			<!-- Date is set by JS when Register button is clicked -->
			<input type="hidden" name="course_date_id" id="fc-cal-date-id" value="">
			<!-- Course data for JS paid/free field toggling -->
			<input type="hidden" id="fc-cal-course-data"
				data-id="<?php echo esc_attr( $calendar_course->id ); ?>"
				data-type="<?php echo esc_attr( $calendar_course->course_type ); ?>"
				data-price="<?php echo esc_attr( $calendar_course->price ); ?>"
				data-currency="<?php echo esc_attr( $calendar_course->currency ); ?>">

			<table class="fc-form-table">
				<!-- First Name -->
				<tr>
					<th><label for="fc_cal_first_name"><?php echo esc_html( $fields['first_name']['label'] ); ?> <span class="fc-required">*</span></label></th>
					<td><input type="text" name="first_name" id="fc_cal_first_name" required value="<?php echo isset( $_POST['first_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) ) : ''; ?>"></td>
				</tr>

				<!-- Last Name -->
				<tr>
					<th><label for="fc_cal_last_name"><?php echo esc_html( $fields['last_name']['label'] ); ?> <span class="fc-required">*</span></label></th>
					<td><input type="text" name="last_name" id="fc_cal_last_name" required value="<?php echo isset( $_POST['last_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) ) : ''; ?>"></td>
				</tr>

				<!-- Email -->
				<tr>
					<th><label for="fc_cal_email"><?php echo esc_html( $fields['email']['label'] ); ?> <span class="fc-required">*</span></label></th>
					<td><input type="email" name="email" id="fc_cal_email" required value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>"></td>
				</tr>

				<?php if ( '1' === $fields['phone']['enabled'] ) : ?>
				<!-- Phone -->
				<tr>
					<th>
						<label for="fc_cal_phone">
							<?php echo esc_html( $fields['phone']['label'] ); ?>
							<?php if ( '1' === $fields['phone']['required'] ) : ?><span class="fc-required">*</span><?php endif; ?>
						</label>
					</th>
					<td>
						<input type="tel" name="phone" id="fc_cal_phone"
							<?php echo '1' === $fields['phone']['required'] ? 'required' : ''; ?>
							value="<?php echo isset( $_POST['phone'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : ''; ?>">
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( '1' === $fields['organisation']['enabled'] ) : ?>
				<!-- Organisation -->
				<tr>
					<th>
						<label for="fc_cal_organisation">
							<?php echo esc_html( $fields['organisation']['label'] ); ?>
							<?php if ( '1' === $fields['organisation']['required'] ) : ?><span class="fc-required">*</span><?php endif; ?>
						</label>
					</th>
					<td>
						<input type="text" name="organisation" id="fc_cal_organisation"
							<?php echo '1' === $fields['organisation']['required'] ? 'required' : ''; ?>
							value="<?php echo isset( $_POST['organisation'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['organisation'] ) ) ) : ''; ?>">
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( '1' === $fields['participant_type']['enabled'] ) : ?>
				<!-- Participant Type -->
				<tr>
					<th>
						<label for="fc_cal_participant_type">
							<?php echo esc_html( $fields['participant_type']['label'] ); ?>
							<?php if ( '1' === $fields['participant_type']['required'] ) : ?><span class="fc-required">*</span><?php endif; ?>
						</label>
					</th>
					<td>
						<select name="participant_type" id="fc_cal_participant_type"
							<?php echo '1' === $fields['participant_type']['required'] ? 'required' : ''; ?>>
							<option value=""><?php esc_html_e( '— Select —', 'fc-courses' ); ?></option>
							<?php foreach ( $participant_types as $type ) : ?>
								<option value="<?php echo esc_attr( $type ); ?>" <?php selected( isset( $_POST['participant_type'] ) ? sanitize_text_field( wp_unslash( $_POST['participant_type'] ) ) : '', $type ); ?>>
									<?php echo esc_html( $type ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<?php endif; ?>

				<!-- Discount code (shown via JS for paid courses) -->
				<tr class="fc-discount-field fc-cal-discount-field" style="display:none">
					<th><label for="fc_cal_discount_code"><?php esc_html_e( 'Discount Code', 'fc-courses' ); ?></label></th>
					<td>
						<div class="fc-discount-row">
							<input type="text" name="discount_code" id="fc_cal_discount_code" placeholder="<?php esc_attr_e( 'Enter code', 'fc-courses' ); ?>" style="text-transform:uppercase" value="<?php echo isset( $_POST['discount_code'] ) ? esc_attr( strtoupper( sanitize_text_field( wp_unslash( $_POST['discount_code'] ) ) ) ) : ''; ?>">
							<button type="button" class="fc-apply-code fc-cal-apply-code button"><?php esc_html_e( 'Apply', 'fc-courses' ); ?></button>
						</div>
						<span class="fc-discount-message fc-cal-discount-message"></span>
					</td>
				</tr>

				<!-- Price summary (shown via JS for paid courses) -->
				<tr class="fc-price-summary fc-cal-price-summary" style="display:none">
					<th><?php esc_html_e( 'Amount Due', 'fc-courses' ); ?></th>
					<td><strong class="fc-cal-price-amount"></strong></td>
				</tr>

				<!-- Payment method (shown via JS for paid courses) -->
				<tr class="fc-payment-field fc-cal-payment-field" style="display:none">
					<th><label><?php esc_html_e( 'Payment Method', 'fc-courses' ); ?> <span class="fc-required">*</span></label></th>
					<td>
						<div class="fc-payment-options">
							<?php if ( get_option( 'fc_stripe_publishable_key', '' ) ) : ?>
							<label class="fc-payment-option">
								<input type="radio" name="payment_method" value="stripe" checked>
								<?php esc_html_e( 'Pay by card (Stripe)', 'fc-courses' ); ?>
							</label>
							<?php endif; ?>
							<?php if ( '1' === get_option( 'fc_enable_bank_transfer', '1' ) ) : ?>
							<label class="fc-payment-option">
								<input type="radio" name="payment_method" value="bank_transfer" <?php echo ! get_option( 'fc_stripe_publishable_key', '' ) ? 'checked' : ''; ?>>
								<?php esc_html_e( 'Bank transfer', 'fc-courses' ); ?>
							</label>
							<?php endif; ?>
						</div>
					</td>
				</tr>

				<!-- Submit / Back -->
				<tr>
					<th></th>
					<td>
						<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Register', 'fc-courses' ); ?></button>
						<button type="button" class="button fc-cal-back-btn" style="margin-left:8px"><?php esc_html_e( '← Back', 'fc-courses' ); ?></button>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?php endif; ?>

	<?php endif; // end !$form_message ?>

</div>
