<?php
/**
 * Public view: Expression of Interest form for the Leaders Training.
 *
 * Variables available:
 *  $form_message  string – success message
 *  $form_error    string – error message
 *
 * Clinician / Whānau conditional fields are toggled via inline JS.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posted_type        = isset( $_POST['participant_type'] ) ? sanitize_text_field( wp_unslash( $_POST['participant_type'] ) ) : '';
$posted_mgmt        = isset( $_POST['management_approval'] ) ? sanitize_text_field( wp_unslash( $_POST['management_approval'] ) ) : '';
?>
<div class="fc-eoi-wrap">

<?php if ( $form_message ) : ?>
<div class="fc-notice fc-notice-success"><?php echo esc_html( $form_message ); ?></div>
<?php endif; ?>

<?php if ( $form_error ) : ?>
<div class="fc-notice fc-notice-error"><?php echo esc_html( $form_error ); ?></div>
<?php endif; ?>

<?php if ( ! $form_message ) : ?>
<form class="fc-eoi-form" method="post" id="fc-leader-eoi-form">
<?php wp_nonce_field( 'fc_leader_eoi', 'fc_leader_eoi_nonce' ); ?>

<div class="fc-eoi-grid">

<!-- Full Name -->
<div class="fc-eoi-field">
<label for="fc_leoi_full_name"><?php esc_html_e( 'Full Name', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="full_name" id="fc_leoi_full_name" required
	value="<?php echo isset( $_POST['full_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) ) : ''; ?>">
</div>

<!-- Clinician / Whānau -->
<div class="fc-eoi-field">
<label for="fc_leoi_participant_type"><?php esc_html_e( 'I am a…', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<select name="participant_type" id="fc_leoi_participant_type" required>
<option value=""><?php esc_html_e( '— Select —', 'fc-courses' ); ?></option>
<option value="clinician" <?php selected( $posted_type, 'clinician' ); ?>><?php esc_html_e( 'Clinician', 'fc-courses' ); ?></option>
<option value="whanau" <?php selected( $posted_type, 'whanau' ); ?>><?php esc_html_e( 'Whānau member', 'fc-courses' ); ?></option>
</select>
</div>

<!-- ── Clinician fields (hidden by default) ───────────────────────── -->
<div class="fc-eoi-field fc-eoi-field--full fc-conditional" id="fc_leoi_clinician_section"
	<?php echo ( 'clinician' !== $posted_type ) ? 'hidden' : ''; ?>>
<div class="fc-eoi-grid" style="margin:0;padding:0">

<div class="fc-eoi-field">
<label for="fc_leoi_profession"><?php esc_html_e( 'Profession', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="profession" id="fc_leoi_profession"
	value="<?php echo isset( $_POST['profession'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['profession'] ) ) ) : ''; ?>">
</div>

<div class="fc-eoi-field">
<label for="fc_leoi_place_of_employment"><?php esc_html_e( 'Place of Employment', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="place_of_employment" id="fc_leoi_place_of_employment"
	value="<?php echo isset( $_POST['place_of_employment'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['place_of_employment'] ) ) ) : ''; ?>">
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label><?php esc_html_e( 'Do you have management approval to attend the training and facilitate 12 week groups?', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<div class="fc-radio-group">
<?php foreach ( array( 'yes' => __( 'Yes', 'fc-courses' ), 'no' => __( 'No', 'fc-courses' ), 'unsure' => __( 'Unsure', 'fc-courses' ) ) as $val => $lbl ) : ?>
<label class="fc-radio-option">
<input type="radio" name="management_approval" value="<?php echo esc_attr( $val ); ?>"
	<?php checked( $posted_mgmt, $val ); ?>>
<?php echo esc_html( $lbl ); ?>
</label>
<?php endforeach; ?>
</div>
</div>

</div><!-- /inner grid -->
</div><!-- /clinician section -->

<!-- ── Whānau fields (hidden by default) ─────────────────────────── -->
<div class="fc-eoi-field fc-eoi-field--full fc-conditional" id="fc_leoi_whanau_section"
	<?php echo ( 'whanau' !== $posted_type ) ? 'hidden' : ''; ?>>
<div class="fc-eoi-grid" style="margin:0;padding:0">

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_leoi_fc_participation"><?php esc_html_e( 'Have you participated in a Family Connections Programme in the last two years, and where?', 'fc-courses' ); ?></label>
<textarea name="fc_participation" id="fc_leoi_fc_participation" rows="3" class="fc-textarea"><?php echo isset( $_POST['fc_participation'] ) ? esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['fc_participation'] ) ) ) : ''; ?></textarea>
</div>

<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_leoi_leader_endorsement"><?php esc_html_e( 'Which leader endorsed your participation in Leader training?', 'fc-courses' ); ?></label>
<input type="text" name="leader_endorsement" id="fc_leoi_leader_endorsement"
	value="<?php echo isset( $_POST['leader_endorsement'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['leader_endorsement'] ) ) ) : ''; ?>">
</div>

</div><!-- /inner grid -->
</div><!-- /whanau section -->

<!-- Town / Region -->
<div class="fc-eoi-field">
<label for="fc_leoi_town_region"><?php esc_html_e( 'Town / Region', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="text" name="town_region" id="fc_leoi_town_region" required
	value="<?php echo isset( $_POST['town_region'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['town_region'] ) ) ) : ''; ?>">
</div>

<!-- Phone -->
<div class="fc-eoi-field">
<label for="fc_leoi_phone"><?php esc_html_e( 'Phone Number', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="tel" name="phone" id="fc_leoi_phone" required
	value="<?php echo isset( $_POST['phone'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : ''; ?>">
</div>

<!-- Email -->
<div class="fc-eoi-field fc-eoi-field--full">
<label for="fc_leoi_email"><?php esc_html_e( 'Email Address', 'fc-courses' ); ?> <span class="fc-required">*</span></label>
<input type="email" name="email" id="fc_leoi_email" required
	value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>">
</div>

<!-- Submit -->
<div class="fc-eoi-field fc-eoi-field--full">
<button type="submit" class="fc-submit-btn button button-primary"><?php esc_html_e( 'Submit Expression of Interest', 'fc-courses' ); ?></button>
</div>

</div>
</form>

<script>
(function () {
	var sel         = document.getElementById('fc_leoi_participant_type');
	var clinSection = document.getElementById('fc_leoi_clinician_section');
	var whanSection = document.getElementById('fc_leoi_whanau_section');
	if ( ! sel || ! clinSection || ! whanSection ) return;

	function toggle() {
		var val = sel.value;

		var showClin = ( val === 'clinician' );
		clinSection.hidden = ! showClin;
		clinSection.querySelectorAll('input, textarea').forEach(function(el){
			if ( el.dataset.alwaysOptional ) return;
			el.required = showClin;
		});
		// management_approval radios
		clinSection.querySelectorAll('input[name="management_approval"]').forEach(function(r){
			r.required = showClin;
		});

		var showWhan = ( val === 'whanau' );
		whanSection.hidden = ! showWhan;
		// Whānau fields are optional (no required enforcement).
	}

	sel.addEventListener('change', toggle);
	toggle();
})();
</script>
<?php endif; ?>

</div>
