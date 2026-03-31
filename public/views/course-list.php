<?php
/**
 * Public view: Course list.
 *
 * Variables available:
 *  $courses  array  – published courses
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $courses ) {
	echo '<p>' . esc_html__( 'No courses are currently available.', 'fc-courses' ) . '</p>';
	return;
}
?>
<div class="fc-course-list">
	<?php foreach ( $courses as $c ) : ?>
	<div class="fc-course-card">
		<h3 class="fc-course-title"><?php echo esc_html( $c->title ); ?></h3>
		<?php if ( $c->description ) : ?>
			<div class="fc-course-description"><?php echo wp_kses_post( $c->description ); ?></div>
		<?php endif; ?>
		<p class="fc-course-price">
			<?php if ( 'paid' === $c->course_type ) : ?>
				<strong><?php echo esc_html( FC_Courses_Shortcodes::currency_symbol( $c->currency ) . number_format( (float) $c->price, 2 ) ); ?></strong>
			<?php else : ?>
				<strong><?php esc_html_e( 'Free', 'fc-courses' ); ?></strong>
			<?php endif; ?>
		</p>
		<a href="?fc_register=<?php echo esc_attr( $c->id ); ?>" class="fc-register-link button"><?php esc_html_e( 'Register', 'fc-courses' ); ?></a>
	</div>
	<?php endforeach; ?>
</div>
