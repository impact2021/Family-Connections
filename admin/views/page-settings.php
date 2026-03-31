<?php
/**
 * Admin view: Settings.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap fc-courses-wrap">
	<h1><?php esc_html_e( 'FC Courses — Settings', 'fc-courses' ); ?></h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'fc-courses' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="fc_save_settings">
		<?php wp_nonce_field( 'fc_save_settings' ); ?>

		<!-- General -->
		<h2><?php esc_html_e( 'General', 'fc-courses' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="fc_currency"><?php esc_html_e( 'Currency', 'fc-courses' ); ?></label></th>
				<td>
					<input type="hidden" name="fc_currency" id="fc_currency" value="NZD">
					<span>NZD</span>
				</td>
			</tr>
			<tr>
				<th><label for="fc_admin_email"><?php esc_html_e( 'Admin notification email', 'fc-courses' ); ?></label></th>
				<td><input type="email" name="fc_admin_email" id="fc_admin_email" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_admin_email', get_option( 'admin_email' ) ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="fc_from_name"><?php esc_html_e( 'From Name', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_from_name" id="fc_from_name" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_from_name', get_bloginfo( 'name' ) ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="fc_from_email"><?php esc_html_e( 'From Email', 'fc-courses' ); ?></label></th>
				<td><input type="email" name="fc_from_email" id="fc_from_email" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_from_email', get_option( 'admin_email' ) ) ); ?>"></td>
			</tr>
		</table>

		<!-- Pages -->
		<h2><?php esc_html_e( 'Pages', 'fc-courses' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="fc_success_page_id"><?php esc_html_e( 'Payment Success Page', 'fc-courses' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages( array(
						'name'             => 'fc_success_page_id',
						'id'               => 'fc_success_page_id',
						'selected'         => (int) get_option( 'fc_success_page_id', 0 ),
						'show_option_none' => __( '— Select page —', 'fc-courses' ),
					) );
					?>
					<p class="description"><?php esc_html_e( 'Page shown after a successful Stripe payment.', 'fc-courses' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="fc_cancel_page_id"><?php esc_html_e( 'Payment Cancel Page', 'fc-courses' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages( array(
						'name'             => 'fc_cancel_page_id',
						'id'               => 'fc_cancel_page_id',
						'selected'         => (int) get_option( 'fc_cancel_page_id', 0 ),
						'show_option_none' => __( '— Select page —', 'fc-courses' ),
					) );
					?>
					<p class="description"><?php esc_html_e( 'Page shown when a user cancels a Stripe payment.', 'fc-courses' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="fc_registration_page_id"><?php esc_html_e( 'Registration Page', 'fc-courses' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages( array(
						'name'             => 'fc_registration_page_id',
						'id'               => 'fc_registration_page_id',
						'selected'         => (int) get_option( 'fc_registration_page_id', 0 ),
						'show_option_none' => __( '— Select page —', 'fc-courses' ),
					) );
					?>
					<p class="description"><?php esc_html_e( 'Page containing the [fc_course_registration] shortcode. Used by the Register button in the course calendar.', 'fc-courses' ); ?></p>
				</td>
			</tr>
		</table>

		<!-- Stripe -->
		<h2><?php esc_html_e( 'Stripe', 'fc-courses' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Test Mode', 'fc-courses' ); ?></th>
				<td><label><input type="checkbox" name="fc_stripe_test_mode" value="1" <?php checked( get_option( 'fc_stripe_test_mode', '0' ), '1' ); ?>> <?php esc_html_e( 'Enable Stripe test mode', 'fc-courses' ); ?></label></td>
			</tr>
			<tr>
				<th><label for="fc_stripe_publishable_key"><?php esc_html_e( 'Publishable Key', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_stripe_publishable_key" id="fc_stripe_publishable_key" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_stripe_publishable_key', '' ) ); ?>" placeholder="pk_live_…"></td>
			</tr>
			<tr>
				<th><label for="fc_stripe_secret_key"><?php esc_html_e( 'Secret Key', 'fc-courses' ); ?></label></th>
				<td>
					<input type="password" name="fc_stripe_secret_key" id="fc_stripe_secret_key" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_stripe_secret_key', '' ) ); ?>" placeholder="sk_live_…">
					<p class="description"><?php esc_html_e( 'Never share this key. It is stored in the WordPress options table.', 'fc-courses' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="fc_stripe_webhook_secret"><?php esc_html_e( 'Webhook Signing Secret', 'fc-courses' ); ?></label></th>
				<td>
					<input type="password" name="fc_stripe_webhook_secret" id="fc_stripe_webhook_secret" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_stripe_webhook_secret', '' ) ); ?>" placeholder="whsec_…">
					<p class="description">
						<?php
						printf(
							/* translators: webhook URL */
							esc_html__( 'Set this in your Stripe Dashboard. The webhook endpoint URL is: %s', 'fc-courses' ),
							'<code>' . esc_html( rest_url( 'fc-courses/v1/stripe-webhook' ) ) . '</code>'
						);
						?>
					</p>
				</td>
			</tr>
		</table>

		<!-- Bank Transfer -->
		<h2><?php esc_html_e( 'Bank Transfer', 'fc-courses' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Enable Bank Transfer', 'fc-courses' ); ?></th>
				<td><label><input type="checkbox" name="fc_enable_bank_transfer" value="1" <?php checked( get_option( 'fc_enable_bank_transfer', '1' ), '1' ); ?>> <?php esc_html_e( 'Show bank transfer as a payment option', 'fc-courses' ); ?></label></td>
			</tr>
			<tr>
				<th><label for="fc_bank_name"><?php esc_html_e( 'Bank Name', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_bank_name" id="fc_bank_name" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_bank_name', '' ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="fc_bank_account_name"><?php esc_html_e( 'Account Name', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_bank_account_name" id="fc_bank_account_name" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_bank_account_name', '' ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="fc_bank_sort_code"><?php esc_html_e( 'Sort Code', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_bank_sort_code" id="fc_bank_sort_code" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_bank_sort_code', '' ) ); ?>" placeholder="00-00-00"></td>
			</tr>
			<tr>
				<th><label for="fc_bank_account_number"><?php esc_html_e( 'Account Number', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_bank_account_number" id="fc_bank_account_number" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_bank_account_number', '' ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="fc_bank_iban"><?php esc_html_e( 'IBAN', 'fc-courses' ); ?></label></th>
				<td><input type="text" name="fc_bank_iban" id="fc_bank_iban" class="regular-text" value="<?php echo esc_attr( get_option( 'fc_bank_iban', '' ) ); ?>"></td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Settings', 'fc-courses' ) ); ?>

		<!-- Registration Form Fields -->
		<h2><?php esc_html_e( 'Registration Form Fields', 'fc-courses' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Customise the label, visibility, and required state of each registration form field. First Name, Last Name, and Email Address are always shown and always required.', 'fc-courses' ); ?></p>

		<table class="widefat striped" style="max-width:860px;margin-top:1em">
			<thead>
				<tr>
					<th style="width:180px"><?php esc_html_e( 'Field', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Custom Label', 'fc-courses' ); ?></th>
					<th style="width:90px;text-align:center"><?php esc_html_e( 'Visible', 'fc-courses' ); ?></th>
					<th style="width:90px;text-align:center"><?php esc_html_e( 'Required', 'fc-courses' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$form_fields      = FC_Courses_Shortcodes::get_form_fields();
				$locked_fields    = array( 'first_name', 'last_name', 'email' );
				foreach ( $form_fields as $key => $field ) :
					$is_locked = in_array( $key, $locked_fields, true );
				?>
				<tr>
					<td><strong><?php echo esc_html( $field['default_label'] ); ?></strong></td>
					<td>
						<input type="text"
						       name="fc_form_fields[<?php echo esc_attr( $key ); ?>][label]"
						       class="regular-text"
						       value="<?php echo esc_attr( $field['label'] ); ?>"
						       placeholder="<?php echo esc_attr( $field['default_label'] ); ?>">
					</td>
					<td style="text-align:center">
						<?php if ( $is_locked ) : ?>
							<input type="checkbox" checked disabled title="<?php esc_attr_e( 'Always visible', 'fc-courses' ); ?>">
							<input type="hidden" name="fc_form_fields[<?php echo esc_attr( $key ); ?>][enabled]" value="1">
						<?php else : ?>
							<input type="checkbox"
							       name="fc_form_fields[<?php echo esc_attr( $key ); ?>][enabled]"
							       value="1"
							       <?php checked( $field['enabled'], '1' ); ?>>
						<?php endif; ?>
					</td>
					<td style="text-align:center">
						<?php if ( $is_locked ) : ?>
							<input type="checkbox" checked disabled title="<?php esc_attr_e( 'Always required', 'fc-courses' ); ?>">
							<input type="hidden" name="fc_form_fields[<?php echo esc_attr( $key ); ?>][required]" value="1">
						<?php else : ?>
							<input type="checkbox"
							       name="fc_form_fields[<?php echo esc_attr( $key ); ?>][required]"
							       value="1"
							       <?php checked( $field['required'], '1' ); ?>>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php submit_button( __( 'Save Settings', 'fc-courses' ) ); ?>
	</form>
</div>
