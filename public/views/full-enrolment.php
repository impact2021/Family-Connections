<?php
/**
 * Public view: Full Enrolment Form for the Family Connections course (code-gated).
 *
 * Variables available:
 *  $step                 string       – 'code' | 'form'
 *  $applicant            object|null  – applicant row when step = 'form'
 *  $code                 string       – validated approval code
 *  $form_message         string       – success message
 *  $form_error           string       – error message
 *  $ethnicity_options    array        – list of ethnicity options
 *  $relationship_options array        – keyed array of values => labels
 *  $code_of_conduct      string       – CoC text
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prepare CoC HTML for the modal.
$coc_content = get_option( 'fc_code_of_conduct', '' );
if ( '' !== trim( $coc_content ) ) {
	$coc_html = preg_match( '/<(p|ul|ol|h[1-6]|div|br)[^>]*>/i', $coc_content )
		? wp_kses_post( $coc_content )
		: nl2br( esc_html( $coc_content ) );
} else {
	$coc_html = nl2br( esc_html( $code_of_conduct ) );
}
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
<p><?php esc_html_e( 'Enter the approval code from your email to access the enrolment form.', 'fc-courses' ); ?></p>
<form class="fc-eoi-form" method="post">
<?php wp_nonce_field( 'fc_full_enrolment_code', 'fc_full_code_nonce' ); ?>
<div class="fc-eoi-grid">

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_approval_code"><?php esc_html_e( 'Approval Code', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="fc_approval_code" id="fc_approval_code" required
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
<form class="fc-eoi-form" method="post">
<?php wp_nonce_field( 'fc_full_enrolment', 'fc_full_nonce' ); ?>
<input type="hidden" name="fc_approval_code" value="<?php echo esc_attr( $code ); ?>">

<div class="fc-eoi-grid">

<!-- Full Name (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_full_full_name"><?php esc_html_e( 'Full Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="full_name" id="fc_full_full_name" required
	value="<?php echo esc_attr( isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : $applicant->full_name ); ?>">
</div>

<!-- Town / Region (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_full_town_region"><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="town_region" id="fc_full_town_region" required
	value="<?php echo esc_attr( isset( $_POST['town_region'] ) ? sanitize_text_field( wp_unslash( $_POST['town_region'] ) ) : $applicant->town_region ); ?>">
</div>

<!-- Phone (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_full_phone"><?php esc_html_e( 'Phone Number', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="tel" name="phone" id="fc_full_phone" required
	value="<?php echo esc_attr( isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : $applicant->phone ); ?>">
</div>

<!-- Email (pre-filled) -->
<div class="fc-eoi-field">
<label for="fc_full_email"><?php esc_html_e( 'Email', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="email" name="email" id="fc_full_email" required
	value="<?php echo esc_attr( isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : $applicant->email ); ?>">
</div>

<!-- Relationship -->
<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_full_relationship"><?php esc_html_e( 'Relationship to the main person you are attending the course for', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<select name="relationship" id="fc_full_relationship" required>
<option value=""><?php esc_html_e( '— Select —', 'fc-courses' ); ?></option>
<?php
$posted_rel = isset( $_POST['relationship'] ) ? sanitize_text_field( wp_unslash( $_POST['relationship'] ) ) : '';
foreach ( $relationship_options as $val => $lbl ) :
?>
<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $posted_rel, $val ); ?>><?php echo esc_html( $lbl ); ?></option>
<?php endforeach; ?>
</select>
</div>

<!-- Ethnicity -->
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

<!-- Code of Conduct -->
<div class="fc-eoi-field fc-eoi-field--full">
<div class="fc-coc-wrap">
<label class="fc-checkbox-option fc-coc-label">
<input type="checkbox" name="coc_agreed" id="fc_full_coc" value="1" required
	<?php checked( ! empty( $_POST['coc_agreed'] ) ); ?>>
<?php
printf(
	/* translators: %s link to open CoC modal */
	esc_html__( 'I have read and agree to the %s.', 'fc-courses' ),
	'<a href="#" class="fc-coc-open" aria-controls="fc-coc-modal">' . esc_html__( 'Participant Code of Conduct', 'fc-courses' ) . '</a>'
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

<!-- Code of Conduct modal -->
<?php if ( ! $form_message ) : ?>
<div id="fc-coc-modal" class="fc-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Participant Code of Conduct', 'fc-courses' ); ?>" hidden>
<div class="fc-modal-overlay fc-coc-close"></div>
<div class="fc-modal-dialog">
<button class="fc-modal-close fc-coc-close" aria-label="<?php esc_attr_e( 'Close', 'fc-courses' ); ?>">&times;</button>
<div class="fc-modal-body">
<?php echo $coc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitised above ?>
</div>
<div class="fc-modal-footer">
<button class="fc-coc-close button button-primary"><?php esc_html_e( 'Close', 'fc-courses' ); ?></button>
</div>
</div>
</div>
<?php endif; ?>
