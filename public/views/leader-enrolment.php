<?php
/**
 * Public view: Full Enrolment Form for the Leaders Training (code-gated).
 *
 * Variables available:
 *  $step              string       – 'code' | 'form'
 *  $applicant         object|null  – leader applicant row when step = 'form'
 *  $code              string       – validated approval code
 *  $form_message      string       – success message
 *  $form_error        string       – error message
 *  $ethnicity_options array        – list of ethnicity options
 *  $training_date_options array        – open upcoming course-date objects for leader training
 *  $leader_coc            string       – Leader CoC text
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prepare Leader CoC HTML for the modal.
$lcoc_content = get_option( 'fc_leader_coc', '' );
$lcoc_source  = '' !== trim( $lcoc_content ) ? $lcoc_content : $leader_coc;
$lcoc_html    = preg_match( '/<(p|ul|ol|h[1-6]|div|br)[^>]*>/i', $lcoc_source )
	? wp_kses_post( $lcoc_source )
	: nl2br( esc_html( $lcoc_source ) );
?>
<div class="fc-eoi-wrap">

<?php if ( $form_message ) : ?>
<div class="fc-notice fc-notice-success"><?php echo esc_html( $form_message ); ?></div>
<?php endif; ?>

<?php if ( $form_error ) : ?>
<div class="fc-notice fc-notice-error"><?php echo esc_html( $form_error ); ?></div>
<?php endif; ?>

<?php if ( ! $form_message && 'code' === $step ) : ?>
<!-- ── Step 1: Code entry ─────────────────────────────────────────── -->
<p><?php esc_html_e( 'Enter the approval code from your email to access the Leaders Training enrolment form.', 'fc-courses' ); ?></p>
<form class="fc-eoi-form" method="post">
<?php wp_nonce_field( 'fc_leader_enrolment_code', 'fc_leader_code_nonce' ); ?>
<div class="fc-eoi-grid">

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_leader_approval_code"><?php esc_html_e( 'Approval Code', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="fc_approval_code" id="fc_leader_approval_code" required
	autocomplete="off" style="text-transform:uppercase;max-width:240px"
	value="<?php echo isset( $_POST['fc_approval_code'] ) ? esc_attr( strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ) ) ) ) : ''; ?>"
	placeholder="<?php esc_attr_e( 'e.g. ABC123DEF456', 'fc-courses' ); ?>">
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Continue', 'fc-courses' ); ?></button>
</div>

</div>
</form>

<?php elseif ( ! $form_message && 'form' === $step && $applicant ) : ?>
<!-- ── Step 2: Full enrolment form (pre-filled from EOI) ─────────── -->
<?php $is_clinician = ( 'clinician' === $applicant->participant_type ); ?>

<form class="fc-eoi-form" method="post" id="fc-leader-full-form">
<?php wp_nonce_field( 'fc_leader_enrolment', 'fc_leader_full_nonce' ); ?>
<input type="hidden" name="fc_approval_code" value="<?php echo esc_attr( $code ); ?>">

<div class="fc-eoi-grid">

<!-- Full Name (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_lf_full_name"><?php esc_html_e( 'Full Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="full_name" id="fc_lf_full_name" required
	value="<?php echo esc_attr( isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : $applicant->full_name ); ?>">
</div>

<!-- Type (read-only display) -->
<div class="fc-eoi-field">
<label><?php esc_html_e( 'Participant Type', 'fc-courses' ); ?></label>
<p class="fc-readonly-value">
<?php echo $is_clinician ? esc_html__( 'Clinician', 'fc-courses' ) : esc_html__( 'Whānau member', 'fc-courses' ); ?>
</p>
</div>

<!-- Ethnicity (full width) -->
<div class="fc-eoi-field fc-eoi-field--full">
<label><?php esc_html_e( 'Ethnicity', 'fc-courses' ); ?></label>
<span class="fc-field-hint"><?php esc_html_e( '(Select all that apply)', 'fc-courses' ); ?></span>
<div class="fc-ethnicity-options">
<?php
$posted_ethnicity = isset( $_POST['ethnicity'] ) && is_array( $_POST['ethnicity'] )
	? array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['ethnicity'] ) )
	: array();
foreach ( $ethnicity_options as $option ) :
?>
<label class="fc-checkbox-option">
<input type="checkbox" name="ethnicity[]" value="<?php echo esc_attr( $option ); ?>"
	<?php checked( in_array( $option, $posted_ethnicity, true ) ); ?>>
<?php echo esc_html( $option ); ?>
</label>
<?php endforeach; ?>
</div>
</div>

<?php if ( $is_clinician ) : ?>
<!-- ── Clinician fields ─────────────────────────────────────────── -->
<div class="fc-eoi-field">
<label for="fc_lf_profession"><?php esc_html_e( 'Profession', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="profession" id="fc_lf_profession" required
	value="<?php echo esc_attr( isset( $_POST['profession'] ) ? sanitize_text_field( wp_unslash( $_POST['profession'] ) ) : $applicant->profession ); ?>">
</div>

<div class="fc-eoi-field">
<label for="fc_lf_employment"><?php esc_html_e( 'Place of Employment', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="place_of_employment" id="fc_lf_employment" required
	value="<?php echo esc_attr( isset( $_POST['place_of_employment'] ) ? sanitize_text_field( wp_unslash( $_POST['place_of_employment'] ) ) : $applicant->place_of_employment ); ?>">
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label><?php esc_html_e( 'DBT trained?', 'fc-courses' ); ?></label>
<div class="fc-radio-group">
<?php
$posted_dbt = isset( $_POST['dbt_trained'] ) ? sanitize_text_field( wp_unslash( $_POST['dbt_trained'] ) ) : $applicant->dbt_trained;
foreach ( array( 'yes' => __( 'Yes', 'fc-courses' ), 'no' => __( 'No', 'fc-courses' ) ) as $val => $lbl ) :
?>
<label class="fc-radio-option">
<input type="radio" name="dbt_trained" value="<?php echo esc_attr( $val ); ?>"
	<?php checked( $posted_dbt, $val ); ?>>
<?php echo esc_html( $lbl ); ?>
</label>
<?php endforeach; ?>
</div>
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label><?php esc_html_e( 'Do you have management approval to attend the training and facilitate 12 week groups?', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<div class="fc-radio-group">
<?php
$posted_mgmt = isset( $_POST['management_approval'] ) ? sanitize_text_field( wp_unslash( $_POST['management_approval'] ) ) : $applicant->management_approval;
foreach ( array( 'yes' => __( 'Yes', 'fc-courses' ), 'no' => __( 'No', 'fc-courses' ), 'unsure' => __( 'Unsure', 'fc-courses' ) ) as $val => $lbl ) :
?>
<label class="fc-radio-option">
<input type="radio" name="management_approval" value="<?php echo esc_attr( $val ); ?>"
	<?php checked( $posted_mgmt, $val ); ?> required>
<?php echo esc_html( $lbl ); ?>
</label>
<?php endforeach; ?>
</div>
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_lf_billing"><?php esc_html_e( 'Billing Contact', 'fc-courses' ); ?></label>
<textarea name="billing_contact" id="fc_lf_billing" rows="3" class="fc-textarea"><?php
echo isset( $_POST['billing_contact'] ) ? esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['billing_contact'] ) ) ) : esc_textarea( $applicant->billing_contact ?? '' );
?></textarea>
</div>

<?php else : ?>
<!-- ── Whānau fields ──────────────────────────────────────────────── -->
<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_lf_fc_participation"><?php esc_html_e( 'Have you participated in a Family Connections Programme in the last two years, and where?', 'fc-courses' ); ?></label>
<textarea name="fc_participation" id="fc_lf_fc_participation" rows="3" class="fc-textarea"><?php
echo isset( $_POST['fc_participation'] ) ? esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['fc_participation'] ) ) ) : esc_textarea( $applicant->fc_participation ?? '' );
?></textarea>
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_lf_leader_endorsement"><?php esc_html_e( 'Which leader endorsed your participation in Leader training?', 'fc-courses' ); ?></label>
<input type="text" name="leader_endorsement" id="fc_lf_leader_endorsement"
	value="<?php echo esc_attr( isset( $_POST['leader_endorsement'] ) ? sanitize_text_field( wp_unslash( $_POST['leader_endorsement'] ) ) : ( $applicant->leader_endorsement ?? '' ) ); ?>">
</div>
<?php endif; ?>

<!-- Town / Region (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_lf_town_region"><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="town_region" id="fc_lf_town_region" required
	value="<?php echo esc_attr( isset( $_POST['town_region'] ) ? sanitize_text_field( wp_unslash( $_POST['town_region'] ) ) : $applicant->town_region ); ?>">
</div>

<!-- Phone (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_lf_phone"><?php esc_html_e( 'Phone Number', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="tel" name="phone" id="fc_lf_phone" required
	value="<?php echo esc_attr( isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : $applicant->phone ); ?>">
</div>

<!-- Email (pre-filled) -->
<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_lf_email"><?php esc_html_e( 'Email Address', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="email" name="email" id="fc_lf_email" required
	value="<?php echo esc_attr( isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : $applicant->email ); ?>">
</div>

<!-- Dates of Training -->
<div class="fc-eoi-field fc-eoi-field--full">
<label><?php esc_html_e( 'Dates of Training', 'fc-courses' ); ?><?php if ( ! empty( $training_date_options ) ) : ?> <span class="fc-required">*</span><?php endif; ?></label>
<?php if ( empty( $training_date_options ) ) : ?>
<p class="fc-field-hint"><?php esc_html_e( 'No training dates are currently available. Please contact us for more information.', 'fc-courses' ); ?></p>
<?php else : ?>
<div class="fc-radio-group">
<?php
$posted_training = isset( $_POST['training_dates'] ) ? absint( $_POST['training_dates'] ) : 0;
foreach ( $training_date_options as $td ) :
	$td_label = wp_date( get_option( 'date_format' ), strtotime( $td->start_date ) );
	if ( ! empty( $td->end_date ) ) {
		$td_label .= ' – ' . wp_date( get_option( 'date_format' ), strtotime( $td->end_date ) );
	}
	if ( ! empty( $td->location ) ) {
		$td_label .= ', ' . $td->location;
	}
?>
<label class="fc-radio-option">
<input type="radio" name="training_dates" value="<?php echo esc_attr( $td->id ); ?>"
	<?php checked( $posted_training, $td->id ); ?> required>
<?php echo esc_html( $td_label ); ?>
</label>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- Payment Details -->
<div class="fc-eoi-field">
<label for="fc_lf_payment_method"><?php esc_html_e( 'Payment Method', 'fc-courses' ); ?></label>
<select name="payment_method" id="fc_lf_payment_method">
<option value=""><?php esc_html_e( '— Select —', 'fc-courses' ); ?></option>
<?php
$posted_pm = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';
foreach ( array(
	'invoice'       => __( 'Invoice', 'fc-courses' ),
	'bank_transfer' => __( 'Bank Transfer', 'fc-courses' ),
	'other'         => __( 'Other', 'fc-courses' ),
) as $val => $lbl ) :
?>
<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $posted_pm, $val ); ?>><?php echo esc_html( $lbl ); ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="fc-eoi-field">
<label for="fc_lf_payment_reference"><?php esc_html_e( 'Payment Reference', 'fc-courses' ); ?></label>
<input type="text" name="payment_reference" id="fc_lf_payment_reference"
	value="<?php echo isset( $_POST['payment_reference'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['payment_reference'] ) ) ) : ''; ?>">
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_lf_payment_notes"><?php esc_html_e( 'Payment Notes', 'fc-courses' ); ?></label>
<textarea name="payment_notes" id="fc_lf_payment_notes" rows="2" class="fc-textarea"><?php
echo isset( $_POST['payment_notes'] ) ? esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['payment_notes'] ) ) ) : '';
?></textarea>
</div>

<!-- Leader Code of Conduct -->
<div class="fc-eoi-field fc-eoi-field--full">
<div class="fc-coc-wrap">
<label class="fc-checkbox-option fc-coc-label">
<input type="checkbox" name="coc_agreed" id="fc_lf_coc" value="1" required
	<?php checked( ! empty( $_POST['coc_agreed'] ) ); ?>>
<?php
printf(
	/* translators: %s link to open Leader CoC modal */
	esc_html__( 'I have read and agree to the %s.', 'fc-courses' ),
	'<a href="#" class="fc-leader-coc-open" aria-controls="fc-leader-coc-modal">' . esc_html__( 'Leader Code of Conduct', 'fc-courses' ) . '</a>'
);
?>
</label>
<span class="fc-required">*</span>
</div>
</div>

<!-- Submit -->
<div class="fc-eoi-field fc-eoi-field--full">
<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Submit Enrolment', 'fc-courses' ); ?></button>
</div>

</div>
</form>
<?php endif; ?>

</div>

<!-- Leader Code of Conduct modal -->
<?php if ( ! $form_message ) : ?>
<div id="fc-leader-coc-modal" class="fc-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Leader Code of Conduct', 'fc-courses' ); ?>" hidden>
<div class="fc-modal-overlay fc-leader-coc-close"></div>
<div class="fc-modal-dialog">
<button class="fc-modal-close fc-leader-coc-close" aria-label="<?php esc_attr_e( 'Close', 'fc-courses' ); ?>">&times;</button>
<div class="fc-modal-body">
<?php echo $lcoc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitised above ?>
</div>
<div class="fc-modal-footer">
<button class="fc-leader-coc-close button button-primary"><?php esc_html_e( 'Close', 'fc-courses' ); ?></button>
</div>
</div>
</div>

<script>
(function () {
	function bindModal(openClass, modalId, closeClass) {
		var modal = document.getElementById(modalId);
		if (!modal) return;
		document.querySelectorAll('.' + openClass).forEach(function(el){
			el.addEventListener('click', function(e){ e.preventDefault(); modal.hidden = false; });
		});
		document.querySelectorAll('.' + closeClass).forEach(function(el){
			el.addEventListener('click', function(){ modal.hidden = true; });
		});
		document.addEventListener('keydown', function(e){ if (e.key === 'Escape') modal.hidden = true; });
	}
	bindModal('fc-leader-coc-open', 'fc-leader-coc-modal', 'fc-leader-coc-close');
})();
</script>
<?php endif; ?>
