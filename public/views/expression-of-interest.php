<?php
/**
 * Public view: Expression of Interest form for the Family Connections Course.
 *
 * Variables available:
 *  $form_message  string – success message
 *  $form_error    string – error message
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fc-eoi-wrap">

<?php if ( $form_message ) : ?>
<div class="fc-notice fc-notice-success"><?php echo esc_html( $form_message ); ?></div>
<?php endif; ?>

<?php if ( $form_error ) : ?>
<div class="fc-notice fc-notice-error"><?php echo esc_html( $form_error ); ?></div>
<?php endif; ?>

<?php if ( ! $form_message ) : ?>
<form class="fc-eoi-form" method="post">
<?php wp_nonce_field( 'fc_expression_of_interest', 'fc_eoi_nonce' ); ?>

<div class="fc-eoi-grid">

<!-- Full Name -->
<div class="fc-eoi-field">
<label for="fc_eoi_full_name"><?php esc_html_e( 'Full Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="full_name" id="fc_eoi_full_name" required
	value="<?php echo isset( $_POST['full_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) ) : ''; ?>">
</div>

<!-- Town / Region -->
<div class="fc-eoi-field">
<label for="fc_eoi_town_region"><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="town_region" id="fc_eoi_town_region" required
	value="<?php echo isset( $_POST['town_region'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['town_region'] ) ) ) : ''; ?>">
</div>

<!-- Phone -->
<div class="fc-eoi-field">
<label for="fc_eoi_phone"><?php esc_html_e( 'Phone Number', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="tel" name="phone" id="fc_eoi_phone" required
	value="<?php echo isset( $_POST['phone'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : ''; ?>">
</div>

<!-- Email -->
<div class="fc-eoi-field">
<label for="fc_eoi_email"><?php esc_html_e( 'Email', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="email" name="email" id="fc_eoi_email" required
	value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>">
</div>

<!-- Age of loved one -->
<div class="fc-eoi-field">
<label for="fc_eoi_loved_one_age"><?php esc_html_e( 'Age of your loved one', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="number" name="loved_one_age" id="fc_eoi_loved_one_age" required min="0" max="120" style="max-width:100px"
	value="<?php echo isset( $_POST['loved_one_age'] ) ? esc_attr( absint( $_POST['loved_one_age'] ) ) : ''; ?>">
</div>

<!-- Mental health – current (full width) -->
<div class="fc-eoi-field fc-eoi-field--full">
<label><?php esc_html_e( 'Is your loved one currently under a public mental health service?', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<div class="fc-radio-group">
<?php
$mh_current = isset( $_POST['mental_health_current'] ) ? sanitize_text_field( wp_unslash( $_POST['mental_health_current'] ) ) : '';
foreach ( array( 'yes' => __( 'Yes', 'fc-courses' ), 'no' => __( 'No', 'fc-courses' ), 'unsure' => __( 'Unsure', 'fc-courses' ) ) as $val => $lbl ) :
?>
<label class="fc-radio-option">
<input type="radio" name="mental_health_current" value="<?php echo esc_attr( $val ); ?>"
	<?php checked( $mh_current, $val ); ?> required>
<?php echo esc_html( $lbl ); ?>
</label>
<?php endforeach; ?>
</div>
</div>

<!-- Mental health – past (conditional: shown when current is not "yes") -->
<div class="fc-eoi-field fc-eoi-field--full" id="fc_eoi_past_wrap"<?php echo ( 'yes' === $mh_current ) ? ' hidden' : ''; ?>>
<label><?php esc_html_e( 'If not currently, have they ever been under a public mental health service?', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<div class="fc-radio-group">
<?php
$mh_past = isset( $_POST['mental_health_past'] ) ? sanitize_text_field( wp_unslash( $_POST['mental_health_past'] ) ) : '';
foreach ( array( 'yes' => __( 'Yes', 'fc-courses' ), 'no' => __( 'No', 'fc-courses' ), 'unsure' => __( 'Unsure', 'fc-courses' ) ) as $val => $lbl ) :
?>
<label class="fc-radio-option">
<input type="radio" name="mental_health_past" value="<?php echo esc_attr( $val ); ?>"
	<?php checked( $mh_past, $val ); ?>>
<?php echo esc_html( $lbl ); ?>
</label>
<?php endforeach; ?>
</div>
</div>

<!-- Submit -->
<div class="fc-eoi-field fc-eoi-field--full">
<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Submit Expression of Interest', 'fc-courses' ); ?></button>
</div>

</div>
</form>

<script>
(function () {
	var radios = document.querySelectorAll('input[name="mental_health_current"]');
	var wrap   = document.getElementById('fc_eoi_past_wrap');
	if ( ! wrap ) return;
	function toggle() {
		var checked = document.querySelector('input[name="mental_health_current"]:checked');
		var hide = checked && checked.value === 'yes';
		wrap.hidden = hide;
		wrap.querySelectorAll('input[type="radio"]').forEach(function(r){ r.required = !hide; });
	}
	radios.forEach(function(r){ r.addEventListener('change', toggle); });
	toggle();
})();
</script>
<?php endif; ?>

</div>


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Convert CoC plain text to HTML paragraphs for display in the modal, or allow safe HTML if admin saved HTML.
$coc_content = get_option( 'fc_code_of_conduct', '' );
if ( '' !== trim( $coc_content ) ) {
	// If the saved content looks like plain text (no block-level HTML tags), convert newlines.
	if ( ! preg_match( '/<(p|ul|ol|h[1-6]|div|br)[^>]*>/i', $coc_content ) ) {
		$coc_html = nl2br( esc_html( $coc_content ) );
	} else {
		$coc_html = wp_kses_post( $coc_content );
	}
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

<?php if ( ! $form_message ) : ?>
<form class="fc-eoi-form" method="post">
<?php wp_nonce_field( 'fc_expression_of_interest', 'fc_eoi_nonce' ); ?>

<div class="fc-eoi-grid">

<!-- Full Name -->
<div class="fc-eoi-field">
<label for="fc_eoi_full_name"><?php esc_html_e( 'Full Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="full_name" id="fc_eoi_full_name" required
     value="<?php echo isset( $_POST['full_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) ) : ''; ?>">
</div>

<!-- Town / Region -->
<div class="fc-eoi-field">
<label for="fc_eoi_town_region"><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="town_region" id="fc_eoi_town_region" required
     value="<?php echo isset( $_POST['town_region'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['town_region'] ) ) ) : ''; ?>">
</div>

<!-- Phone -->
<div class="fc-eoi-field">
<label for="fc_eoi_phone"><?php esc_html_e( 'Phone Number', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="tel" name="phone" id="fc_eoi_phone" required
     value="<?php echo isset( $_POST['phone'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : ''; ?>">
</div>

<!-- Email -->
<div class="fc-eoi-field">
<label for="fc_eoi_email"><?php esc_html_e( 'Email', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="email" name="email" id="fc_eoi_email" required
     value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>">
</div>

<!-- Relationship -->
<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_eoi_relationship"><?php esc_html_e( 'Relationship to the main person you are attending the course for', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<select name="relationship" id="fc_eoi_relationship" required>
<option value=""><?php esc_html_e( '— Select —', 'fc-courses' ); ?></option>
<?php foreach ( $relationship_options as $value => $label ) : ?>
<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $_POST['relationship'] ) ? sanitize_text_field( wp_unslash( $_POST['relationship'] ) ) : '', $value ); ?>>
<?php echo esc_html( $label ); ?>
</option>
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
<input type="checkbox" name="coc_agreed" id="fc_eoi_coc" value="1" required
       <?php checked( ! empty( $_POST['coc_agreed'] ) ); ?>>
<?php
printf(
	/* translators: %s link to open modal */
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
<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Submit Expression of Interest', 'fc-courses' ); ?></button>
</div>

</div>
</form>
<?php endif; ?>

</div>

<!-- Code of Conduct modal -->
<div id="fc-coc-modal" class="fc-modal" role="dialog" aria-modal="true" aria-labelledby="fc-coc-modal-title" hidden>
<div class="fc-modal-overlay fc-coc-close"></div>
<div class="fc-modal-dialog">
<button class="fc-modal-close fc-coc-close" aria-label="<?php esc_attr_e( 'Close', 'fc-courses' ); ?>">&times;</button>
<div class="fc-modal-body">
<?php echo $coc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped via esc_html() + nl2br() above ?>
</div>
<div class="fc-modal-footer">
<button class="fc-coc-close button button-primary"><?php esc_html_e( 'Close', 'fc-courses' ); ?></button>
</div>
</div>
</div>
