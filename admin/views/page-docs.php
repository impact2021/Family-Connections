<?php
/**
 * Admin view: Docs / Help.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap fc-courses-wrap fc-courses-docs">
	<h1><?php esc_html_e( 'FC Courses — Documentation', 'fc-courses' ); ?></h1>

	<div class="fc-docs-container">

		<h2><?php esc_html_e( 'Quick Start', 'fc-courses' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Go to FC Courses → Settings and fill in your Stripe keys, bank details, and notification email.', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'Go to FC Courses → Courses to review or edit the default courses (Family Connections Course, Train the Trainer).', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'Click "Dates" next to a course to add scheduled course dates and set the maximum number of places.', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'Create any discount codes you need under FC Courses → Discount Codes.', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'Place shortcodes on your pages — see the full reference below.', 'fc-courses' ); ?></li>
		</ol>

		<!-- ============================================================ -->
		<h2 id="fc-shortcodes"><?php esc_html_e( 'Shortcode Reference', 'fc-courses' ); ?></h2>
		<p><?php esc_html_e( 'All shortcodes can be placed in any WordPress page, post, or widget that renders shortcodes.', 'fc-courses' ); ?></p>

		<!-- ---- fc_course_list ---- -->
		<h3><code>[fc_course_list]</code></h3>
		<p><?php esc_html_e( 'Displays a card grid of all published courses with title, description, price, and a Register link.', 'fc-courses' ); ?></p>

		<table class="widefat fc-docs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Default', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Description', 'fc-courses' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>type</code></td>
					<td><em><?php esc_html_e( '(empty)', 'fc-courses' ); ?></em></td>
					<td><?php esc_html_e( 'Filter by course type. Accepted values: "paid", "free". Leave empty to show all courses.', 'fc-courses' ); ?></td>
				</tr>
			</tbody>
		</table>

		<p><strong><?php esc_html_e( 'Examples:', 'fc-courses' ); ?></strong></p>
		<ul>
			<li><code>[fc_course_list]</code> — <?php esc_html_e( 'all published courses', 'fc-courses' ); ?></li>
			<li><code>[fc_course_list type="paid"]</code> — <?php esc_html_e( 'paid courses only', 'fc-courses' ); ?></li>
			<li><code>[fc_course_list type="free"]</code> — <?php esc_html_e( 'free courses only', 'fc-courses' ); ?></li>
		</ul>

		<!-- ---- fc_course_calendar ---- -->
		<h3><code>[fc_course_calendar]</code></h3>
		<p><?php esc_html_e( 'Displays a table of upcoming open course dates ordered by date, showing course name, date, time, location, available places, price, and a Register button.', 'fc-courses' ); ?></p>

		<table class="widefat fc-docs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Default', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Description', 'fc-courses' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>course_id</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Limit the calendar to a single course. Use the course ID from FC Courses → Courses. 0 shows dates for all courses.', 'fc-courses' ); ?></td>
				</tr>
				<tr>
					<td><code>limit</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Maximum number of date rows to display. 0 shows all upcoming dates.', 'fc-courses' ); ?></td>
				</tr>
			</tbody>
		</table>

		<p><strong><?php esc_html_e( 'Examples:', 'fc-courses' ); ?></strong></p>
		<ul>
			<li><code>[fc_course_calendar]</code> — <?php esc_html_e( 'all upcoming dates for all courses', 'fc-courses' ); ?></li>
			<li><code>[fc_course_calendar course_id="1"]</code> — <?php esc_html_e( 'upcoming dates for course ID 1 only', 'fc-courses' ); ?></li>
			<li><code>[fc_course_calendar limit="5"]</code> — <?php esc_html_e( 'the next 5 upcoming dates across all courses', 'fc-courses' ); ?></li>
			<li><code>[fc_course_calendar course_id="2" limit="3"]</code> — <?php esc_html_e( 'the next 3 dates for course ID 2', 'fc-courses' ); ?></li>
		</ul>

		<!-- ---- fc_course_registration ---- -->
		<h3><code>[fc_course_registration]</code></h3>
		<p><?php esc_html_e( 'Displays the enrolment / sign-up form. When no course_id is given, a course selector dropdown is shown first. The form collects name, email, phone, organisation, optional discount code, and payment method (Stripe card or bank transfer).', 'fc-courses' ); ?></p>
		<p><?php esc_html_e( 'After a successful submission the registrant receives a confirmation email. The admin also receives a notification. If Stripe is chosen, the visitor is redirected to Stripe Checkout; on completion they are sent to the Payment Success page configured in Settings.', 'fc-courses' ); ?></p>

		<table class="widefat fc-docs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Default', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Description', 'fc-courses' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>course_id</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Pre-select and lock the form to a specific course. Use the course ID from FC Courses → Courses. When 0, the visitor can choose from all published courses.', 'fc-courses' ); ?></td>
				</tr>
			</tbody>
		</table>

		<p><strong><?php esc_html_e( 'Examples:', 'fc-courses' ); ?></strong></p>
		<ul>
			<li><code>[fc_course_registration]</code> — <?php esc_html_e( 'registration form with course selector (all published courses)', 'fc-courses' ); ?></li>
			<li><code>[fc_course_registration course_id="1"]</code> — <?php esc_html_e( 'registration form locked to the Family Connections Course (ID 1)', 'fc-courses' ); ?></li>
			<li><code>[fc_course_registration course_id="2"]</code> — <?php esc_html_e( 'registration form locked to Train the Trainer (ID 2)', 'fc-courses' ); ?></li>
		</ul>

		<!-- ============================================================ -->
		<h2><?php esc_html_e( 'Typical Page Setup', 'fc-courses' ); ?></h2>
		<p><?php esc_html_e( 'A common setup uses three pages:', 'fc-courses' ); ?></p>
		<table class="widefat fc-docs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'fc-courses' ); ?></th>
					<th><?php esc_html_e( 'Shortcode to add', 'fc-courses' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Courses / What\'s On', 'fc-courses' ); ?></td>
					<td><code>[fc_course_list]</code></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Course Dates / Schedule', 'fc-courses' ); ?></td>
					<td><code>[fc_course_calendar]</code></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Register / Book a Place', 'fc-courses' ); ?></td>
					<td><code>[fc_course_registration]</code></td>
				</tr>
			</tbody>
		</table>
		<p><?php esc_html_e( 'You can also embed a combined experience on a single page by stacking the course list, calendar, and registration form shortcodes together.', 'fc-courses' ); ?></p>

		<!-- ============================================================ -->
		<h2><?php esc_html_e( 'Enrolments', 'fc-courses' ); ?></h2>
		<p><?php esc_html_e( 'The Enrolments page shows everyone who has signed up. You can filter by course or payment status, and search by name or email. Click Edit on any row to update the payment status or add notes.', 'fc-courses' ); ?></p>

		<!-- ============================================================ -->
		<h2><?php esc_html_e( 'Discount Codes', 'fc-courses' ); ?></h2>
		<p><?php esc_html_e( 'Create percentage or fixed-amount discount codes. A value of 100% makes the course fully free. You can restrict a code to a specific course or leave it open for all courses. Set an expiry date and a maximum number of uses if needed.', 'fc-courses' ); ?></p>

		<!-- ============================================================ -->
		<h2><?php esc_html_e( 'Payment — Stripe', 'fc-courses' ); ?></h2>
		<p><?php esc_html_e( 'FC Courses uses Stripe Checkout. You do not need the Stripe PHP library — all API calls are made directly over HTTPS using wp_remote_post().', 'fc-courses' ); ?></p>
		<ol>
			<li><?php esc_html_e( 'Create a Stripe account at stripe.com if you do not already have one.', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'Copy your Publishable Key and Secret Key from the Stripe Dashboard → Developers → API Keys.', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'In the Stripe Dashboard → Developers → Webhooks, add a new endpoint.', 'fc-courses' ); ?></li>
			<li>
				<?php
				printf(
					/* translators: webhook URL */
					esc_html__( 'The webhook URL is: %s', 'fc-courses' ),
					'<code>' . esc_html( rest_url( 'fc-courses/v1/stripe-webhook' ) ) . '</code>'
				);
				?>
			</li>
			<li><?php esc_html_e( 'Listen for the "checkout.session.completed" event.', 'fc-courses' ); ?></li>
			<li><?php esc_html_e( 'Copy the Signing Secret from the webhook details page and paste it into FC Courses → Settings → Stripe → Webhook Signing Secret.', 'fc-courses' ); ?></li>
		</ol>

		<!-- ============================================================ -->
		<h2><?php esc_html_e( 'Payment — Bank Transfer', 'fc-courses' ); ?></h2>
		<p><?php esc_html_e( 'Fill in your bank details in Settings. When a registrant chooses bank transfer, they receive a confirmation email containing the bank details and a unique reference number (FC-[enrollment_id]). You can then manually mark their payment as "Paid" in the Enrolments page.', 'fc-courses' ); ?></p>

		<!-- ============================================================ -->
		<h2><?php esc_html_e( 'WP Pusher Updates', 'fc-courses' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: GitHub repository slug */
				esc_html__( 'This plugin can be installed and auto-updated via WP Pusher. Point WP Pusher at the GitHub repository: %s', 'fc-courses' ),
				'<code>impact2021/Family-Connections</code>'
			);
			?>
		</p>
		<p><?php esc_html_e( 'WP Pusher will detect new commits to the main branch and offer one-click updates from the WordPress dashboard.', 'fc-courses' ); ?></p>

	</div>
</div>

