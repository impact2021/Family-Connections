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

