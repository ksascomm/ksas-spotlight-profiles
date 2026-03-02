<?php
/**
 * Spotlight Profiles Widget Class.
 *
 * @package KSAS_Profiles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Spotlight_Profiles_Widget Class
 *
 * Displays a random or latest profile based on taxonomy selection.
 * Filename must be class-spotlight-profiles-widget.php for WPCS compliance.
 */
class Spotlight_Profiles_Widget extends WP_Widget {

	/**
	 * Sets up the widget name and description.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'spotlight_profiles_widget',
			'description'                 => __( 'Displays a random or latest profile.', 'ksas_profiles' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'spotlight_profiles_widget', __( 'Profile/Spotlight', 'ksas_profiles' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget on the frontend.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title           = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$category_choice = ! empty( $instance['category_choice'] ) ? $instance['category_choice'] : '';
		$random          = ! empty( $instance['random'] ) ? $instance['random'] : 'rand';
		$archive_link    = ! empty( $instance['link'] ) ? $instance['link'] : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$query_args = array(
			'post_type'      => 'profile',
			'posts_per_page' => 1,
			'orderby'        => $random,
			'tax_query'      => array(
				array(
					'taxonomy' => 'profiletype',
					'field'    => 'slug',
					'terms'    => $category_choice,
				),
			),
		);

		$profile_query = new WP_Query( $query_args );

		if ( $profile_query->have_posts() ) :
			while ( $profile_query->have_posts() ) :
				$profile_query->the_post();
				?>
				<div class="spotlight-widget-item">
					<p>
						<?php
						$quote = get_post_meta( get_the_ID(), 'ecpt_pull_quote', true );
						echo $quote ? esc_html( $quote ) : esc_html( wp_trim_words( get_the_excerpt(), 35 ) );
						?>
					</p>
					<div class="spotlight-image-meta">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) );
						}
						?>
						<div class="spotlight-author">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							
							<?php if ( have_rows( 'custom_profile_fields' ) ) : ?>
								<?php
								while ( have_rows( 'custom_profile_fields' ) ) :
									the_row();
									?>
									<span class="custom-title"><?php the_sub_field( 'custom_title' ); ?>:</span> 
									<span class="custom-content"><?php the_sub_field( 'custom_content' ); ?></span><br>
								<?php endwhile; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<?php if ( $archive_link ) : ?>
					<p class="view-more-link">
						<a href="<?php echo esc_url( $archive_link ); ?>">
							<?php
							/* translators: %s: The widget or profile type title */
							printf( esc_html__( 'View more %s', 'ksas_profiles' ), esc_html( $title ) );
							?>
							<span class="fa fa-chevron-circle-right" aria-hidden="true"></span>
						</a>
					</p>
				<?php endif; ?>

				<?php
			endwhile;
			wp_reset_postdata();
		endif;

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title           = isset( $instance['title'] ) ? $instance['title'] : '';
		$category_choice = isset( $instance['category_choice'] ) ? $instance['category_choice'] : '';
		$random          = isset( $instance['random'] ) ? $instance['random'] : 'rand';
		$link            = isset( $instance['link'] ) ? $instance['link'] : '';

		$categories = get_terms(
			array(
				'taxonomy'   => 'profiletype',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'ksas_profiles' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_choice' ) ); ?>"><?php esc_html_e( 'Profile Type:', 'ksas_profiles' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'category_choice' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_choice' ) ); ?>" class="widefat">
				<?php if ( ! is_wp_error( $categories ) ) : ?>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $category_choice, $cat->slug ); ?>><?php echo esc_html( $cat->name ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>"><?php esc_html_e( 'Order:', 'ksas_profiles' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'random' ) ); ?>" class="widefat">
				<option value="date" <?php selected( $random, 'date' ); ?>><?php esc_html_e( 'Latest', 'ksas_profiles' ); ?></option>
				<option value="rand" <?php selected( $random, 'rand' ); ?>><?php esc_html_e( 'Random', 'ksas_profiles' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Archive Link:', 'ksas_profiles' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="url" value="<?php echo esc_attr( $link ); ?>" />
		</p>
		<?php
	}

	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['title']           = sanitize_text_field( $new_instance['title'] );
		$instance['category_choice'] = sanitize_text_field( $new_instance['category_choice'] );
		$instance['random']          = sanitize_text_field( $new_instance['random'] );
		$instance['link']            = esc_url_raw( $new_instance['link'] );
		return $instance;
	}
}