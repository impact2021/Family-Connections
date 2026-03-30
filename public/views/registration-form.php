<?php
/**
 * Public view: Course registration form.
 *
 * Variables available:
 *  $course       object|null  – specific course (if course_id given in shortcode)
 *  $courses      array        – all published courses
 *  $dates        array        – upcoming dates for $course (if set)
 *  $form_message string       – success message
 *  $form_error   string       – error message
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fc-registration-wrap">

	<?php if ( $form_message ) : ?>
		<div class="fc-notice fc-notice-success"><?php echo esc_html( $form_message ); ?></div>
	<?php endif; ?>

	<?php if ( $form_error ) : ?>
		<div class="fc-notice fc-notice-error"><?php echo esc_html( $form_error ); ?></div>
	<?php endif; ?>

	<?php if ( ! $form_message ) : ?>
	<form class="fc-registration-form" method="post">
		<?php wp_nonce_field( 'fc_course_register', 'fc_register_nonce' ); ?>

		<!-- Course selector (shown when no specific course is given) -->
		<?php if ( ! $course && $courses ) : ?>
		<div class="fc-field">
			<label for="fc_course_select"><?php esc_html_e( 'Select Course', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
			<select name="fc_course_select" id="fc_course_select" required data-action="fc_get_course_dates">
				<option value=""><?php esc_html_e( '— Choose a course —', 'fc-courses' ); ?></option>
				<?php foreach ( $courses as $c ) : ?>
					<option value="<?php echo esc_attr( $c->id ); ?>"
					        data-type="<?php echo esc_attr( $c->course_type ); ?>"
					        data-price="<?php echo esc_attr( $c->price ); ?>"
					        data-currency="<?php echo esc_attr( $c->currency ); ?>">
						<?php echo esc_html( $c->title ); ?>
						<?php if ( 'paid' === $c->course_type ) : ?>
							(<?php echo esc_html( $c->currency . ' ' . number_format( (float) $c->price, 2 ) ); ?>)
						<?php endif; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php else : ?>
			<input type="hidden" id="fc_course_select" value="<?php echo $course ? esc_attr( $course->id ) : ''; ?>"
			       data-price="<?php echo $course ? esc_attr( $course->price ) : '0'; ?>"
			       data-currency="<?php echo $course ? esc_attr( $course->currency ) : ''; ?>"
			       data-type="<?php echo $course ? esc_attr( $course->course_type ) : ''; ?>">
			<h3><?php echo $course ? esc_html( $course->title ) : ''; ?></h3>
		<?php endif; ?>

		<!-- Date selector -->
		<div class="fc-field" id="fc-date-field">
			<label for="course_date_id"><?php esc_html_e( 'Select Date', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
			<select name="course_date_id" id="course_date_id" required>
				<?php if ( $course && $dates ) : ?>
					<option value=""><?php esc_html_e( '— Choose a date —', 'fc-courses' ); ?></option>
					<?php foreach ( $dates as $d ) :
						$capacity = $d->max_enrolees > 0 ? sprintf( __( ' (%d/%d places)', 'fc-courses' ), (int) $d->enrolment_count, (int) $d->max_enrolees ) : '';
						$location = $d->is_online ? __( 'Online', 'fc-courses' ) : $d->location;
					?>
						<option value="<?php echo esc_attr( $d->id ); ?>">
							<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $d->start_date ) ) . ' — ' . $location . $capacity ); ?>
						</option>
					<?php endforeach; ?>
				<?php else : ?>
					<option value=""><?php esc_html_e( '— Choose a course first —', 'fc-courses' ); ?></option>
				<?php endif; ?>
			</select>
		</div>

		<!-- Personal details -->
		<div class="fc-field-row">
			<div class="fc-field">
				<label for="first_name"><?php esc_html_e( 'First Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
				<input type="text" name="first_name" id="first_name" required value="<?php echo isset( $_POST['first_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) ) : ''; ?>">
			</div>
			<div class="fc-field">
				<label for="last_name"><?php esc_html_e( 'Last Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
				<input type="text" name="last_name" id="last_name" required value="<?php echo isset( $_POST['last_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) ) : ''; ?>">
			</div>
		</div>

		<div class="fc-field">
			<label for="email"><?php esc_html_e( 'Email Address', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
			<input type="email" name="email" id="email" required value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>">
		</div>

		<div class="fc-field">
			<label for="phone"><?php esc_html_e( 'Phone', 'fc-courses' ); ?></label>
			<input type="tel" name="phone" id="phone" value="<?php echo isset( $_POST['phone'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : ''; ?>">
		</div>

		<div class="fc-field">
			<label for="organisation"><?php esc_html_e( 'Organisation', 'fc-courses' ); ?></label>
			<input type="text" name="organisation" id="organisation" value="<?php echo isset( $_POST['organisation'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['organisation'] ) ) ) : ''; ?>">
		</div>

		<!-- Discount code -->
		<div class="fc-field fc-discount-field" id="fc-discount-field" style="display:none">
			<label for="discount_code"><?php esc_html_e( 'Discount Code', 'fc-courses' ); ?></label>
			<div class="fc-discount-row">
				<input type="text" name="discount_code" id="discount_code" placeholder="<?php esc_attr_e( 'Enter code', 'fc-courses' ); ?>" style="text-transform:uppercase" value="<?php echo isset( $_POST['discount_code'] ) ? esc_attr( strtoupper( sanitize_text_field( wp_unslash( $_POST['discount_code'] ) ) ) ) : ''; ?>">
				<button type="button" class="fc-apply-code button"><?php esc_html_e( 'Apply', 'fc-courses' ); ?></button>
			</div>
			<span class="fc-discount-message"></span>
		</div>

		<!-- Price summary -->
		<div class="fc-price-summary" id="fc-price-summary" style="display:none">
			<p class="fc-price-label">
				<?php esc_html_e( 'Amount due:', 'fc-courses' ); ?>
				<strong id="fc-price-amount"></strong>
			</p>
		</div>

		<!-- Payment method -->
		<div class="fc-field fc-payment-field" id="fc-payment-field" style="display:none">
			<label><?php esc_html_e( 'Payment Method', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
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
		</div>

		<div class="fc-field fc-submit-field">
			<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Register', 'fc-courses' ); ?></button>
		</div>
	</form>
	<?php endif; ?>

</div>
