<?php
/**
 * Public view: Upcoming course dates calendar / schedule table.
 *
 * Variables available:
 *  $dates  array  – upcoming open course dates (with course_title, enrolment_count, etc.)
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

				// Price label.
				if ( 'paid' === $d->course_type && (float) $d->price > 0 ) {
					$price_label = esc_html( $d->currency . ' ' . number_format( (float) $d->price, 2 ) );
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
						<a href="<?php echo esc_url( add_query_arg( array( 'fc_date' => $d->id, 'fc_course' => $d->course_id ), get_permalink() ) ); ?>" class="fc-register-link button">
							<?php esc_html_e( 'Register', 'fc-courses' ); ?>
						</a>
					<?php else : ?>
						<span class="fc-full-label"><?php esc_html_e( 'Full', 'fc-courses' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
