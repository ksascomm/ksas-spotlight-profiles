<?php
/**
 * Plugin Name: KSAS Student/Faculty Profiles and Spotlights
 * Plugin URI: http://krieger.jhu.edu/
 * Description: Creates a custom post type for profiles.  Link to http://siteurl/profiles/*profiketype-slug* to display profile archive.  Plugin also creates a widget to display a random profile by type in the sidebar. Widget displays thumbnail and pull quote. If no pull quote exists it displays the excerpt.
 * Version: 4.0
 * Author: KSAS Communications
 * Author URI: mailto:ksasweb@jhu.edu
 * License: GPL2
 */

/** Registration code for profile post type */
function register_profile_posttype() {
	$labels = array(
		'name'               => _x( 'Profiles', 'post type general name' ),
		'singular_name'      => _x( 'Profile', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'Profile' ),
		'add_new_item'       => __( 'Add New Profile ' ),
		'edit_item'          => __( 'Edit Profile ' ),
		'new_item'           => __( 'New Profile ' ),
		'view_item'          => __( 'View Profile ' ),
		'search_items'       => __( 'Search Profiles ' ),
		'not_found'          => __( 'No Profile found' ),
		'not_found_in_trash' => __( 'No Profiles found in Trash' ),
		'parent_item_colon'  => '',
	);

	$taxonomies = array( 'affiliation' );

	$supports = array( 'title', 'editor', 'revisions', 'thumbnail', 'excerpt' );

	$post_type_args = array(
		'labels'             => $labels,
		'singular_label'     => __( 'Profile' ),
		'public'             => true,
		'show_ui'            => true,
		'publicly_queryable' => true,
		'query_var'          => true,
		'capability_type'    => 'profile',
		'capabilities'       => array(
			'publish_posts'       => 'publish_profiles',
			'edit_posts'          => 'edit_profiles',
			'edit_others_posts'   => 'edit_others_profiles',
			'delete_posts'        => 'delete_profiles',
			'delete_others_posts' => 'delete_others_profiles',
			'read_private_posts'  => 'read_private_profiles',
			'edit_post'           => 'edit_profile',
			'delete_post'         => 'delete_profile',
			'read_post'           => 'read_profile',
		),
		'has_archive'        => false,
		'hierarchical'       => false,
		'rewrite'            => array(
			'slug'       => 'profiles',
			'with_front' => false,
		),
		'supports'           => $supports,
		'menu_position'      => 5,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-id-alt',
		'taxonomies'         => $taxonomies,
	);
	register_post_type( 'profile', $post_type_args );
}
	add_action( 'init', 'register_profile_posttype' );

/** Registration code for profiletype taxonomy */
function register_profiletype_tax() {
	$labels = array(
		'name'               => _x( 'Profile Types', 'taxonomy general name' ),
		'singular_name'      => _x( 'Profile Type', 'taxonomy singular name' ),
		'add_new'            => _x( 'Add New Profile Type', 'Profile Type' ),
		'add_new_item'       => __( 'Add New Profile Type' ),
		'edit_item'          => __( 'Edit Profile Type' ),
		'new_item'           => __( 'New Profile Type' ),
		'view_item'          => __( 'View Profile Type' ),
		'search_items'       => __( 'Search Profile Types' ),
		'not_found'          => __( 'No Profile Type found' ),
		'not_found_in_trash' => __( 'No Profile Type found in Trash' ),
	);

	$pages = array( 'profile' );

	$args = array(
		'labels'            => $labels,
		'singular_label'    => __( 'Profile Type' ),
		'public'            => true,
		'show_ui'           => true,
		'hierarchical'      => true,
		'show_tagcloud'     => false,
		'show_in_nav_menus' => false,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'       => 'profiletype',
			'with_front' => false,
		),
	);
	register_taxonomy( 'profiletype', $pages, $args );
}
add_action( 'init', 'register_profiletype_tax' );

/** Function to check profile terms */
function check_profiletype_terms() {

	/** See if we already have populated any terms */
	$term = get_terms( 'profiletype', array( 'hide_empty' => false ) );

	/** If no terms then lets add our terms */
	if ( empty( $term ) ) {
		$terms = define_profiletype_terms();
		foreach ( $terms as $term ) {
			if ( ! term_exists( $term['name'], 'profiletype' ) ) {
				wp_insert_term( $term['name'], 'profiletype', array( 'slug' => $term['slug'] ) );
			}
		}
	}
}

add_action( 'init', 'check_profiletype_terms' );

/** Define Custom Taxonomies */
function define_profiletype_terms() {

	$terms = array(
		'0' => array(
			'name' => 'undergraduate',
			'slug' => 'undergraduate-profile',
		),
		'1' => array(
			'name' => 'graduate',
			'slug' => 'graduate-profile',
		),
		'2' => array(
			'name' => 'spotlight',
			'slug' => 'spotlight',
		),
	);

	return $terms;
}

/** Add pull quote box */
$pullquote_7_metabox = array(
	'id'       => 'pullquote',
	'title'    => 'Pull Quote',
	'page'     => array( 'profile' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields'   => array(
		array(
			'name'        => 'Pull Quote or Research Topic',
			'desc'        => 'This is the text shown on profile index page, widgets and homepage sliders',
			'id'          => 'ecpt_pull_quote',
			'class'       => 'ecpt_pull_quote',
			'type'        => 'textarea',
			'rich_editor' => 0,
			'max'         => 0,
			'std'         => '',
		),
	),
);


add_action( 'admin_menu', 'ecpt_add_pullquote_7_meta_box' );
/** Add pull quote box to admin */
function ecpt_add_pullquote_7_meta_box() {

	global $pullquote_7_metabox;

	foreach ( $pullquote_7_metabox['page'] as $page ) {
		add_meta_box( $pullquote_7_metabox['id'], $pullquote_7_metabox['title'], 'ecpt_show_pullquote_7_box', $page, 'normal', 'default', $pullquote_7_metabox );
	}
}

/** Function to show meta boxes */
function ecpt_show_pullquote_7_box() {
	global $post;
	global $pullquote_7_metabox;
	global $ecpt_prefix;
	global $wp_version;

	/** Use nonce for verification */
	echo '<input type="hidden" name="ecpt_pullquote_7_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $pullquote_7_metabox['fields'] as $field ) {
		/** Get current post meta data */

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;
			case 'textarea':
				if ( $field['rich_editor'] == 1 ) {
					echo wp_editor(
						$meta,
						$field['id'],
						array(
							'textarea_name' => $field['id'],
							'wpautop'       => false,
						)
					);
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : $field['std'], '</textarea></div>', '', $field['desc'];
				}

				break;
		}
		echo '<td>',
			'</tr>';
	}

	echo '</table>';
}

add_action( 'save_post', 'ecpt_pullquote_7_save' );

/** Save data from meta box */
function ecpt_pullquote_7_save( $post_id ) {
	global $post;
	global $pullquote_7_metabox;

	/** Verify nonce */
	if ( ! isset( $_POST['ecpt_pullquote_7_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_pullquote_7_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	/** Check autosave */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	/** Check permissions */
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $pullquote_7_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				update_post_meta( $post_id, $field['id'], $new );

			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}

/** CREATE COLUMNS IN ADMIN */

add_filter( 'manage_edit-profile_columns', 'my_profile_columns' );

function my_profile_columns( $columns ) {

	$columns = array(
		'cb'        => '<input type="checkbox" />',
		'title'     => __( 'Name' ),
		'type'      => __( 'Type' ),
		'quote'     => __( 'Excerpt' ),
		'thumbnail' => __( 'Thumbnail' ),
		'date'      => __( 'Date' ),

	);

	return $columns;
}

add_action( 'manage_profile_posts_custom_column', 'my_manage_profile_columns', 10, 2 );

function my_manage_profile_columns( $column, $post_id ) {
	global $post;

	switch ( $column ) {

		/* If displaying the 'role' column. */
		case 'type':
			/* Get the roles for the post. */
			$terms = get_the_terms( $post_id, 'profiletype' );

			/* If terms were found. */
			if ( ! empty( $terms ) ) {

				$out = array();

				/* Loop through each term, linking to the 'edit posts' page for the specific term. */
				foreach ( $terms as $term ) {
					$out[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url(
							add_query_arg(
								array(
									'post_type' => $post->post_type,
									'role'      => $term->slug,
								),
								'edit.php'
							)
						),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'role', 'display' ) )
					);
				}

				/* Join the terms, separating them with a comma. */
				echo join( ', ', $out );
			} else {
				/* If no terms were found, output a default message. */
				_e( 'No Type Assigned' );
			}

			break;
		case 'quote':
			if ( get_post_meta( $post->ID, 'ecpt_pull_quote', true ) ) {
				echo get_post_meta( $post->ID, 'ecpt_pull_quote', true );
			} else {
				the_excerpt();
			}
			break;
		case 'thumbnail':
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail' );
			} else {
				echo __( 'No Photo' );
			}
			break;
		/* Just break out of the switch statement for everything else. */
		default:
			break;
	}
}

/*************Profile Widget*****************/
	/**
	 * Register widget with WordPress.
	 */
class Profile_Widget extends WP_Widget {
	public function __construct() {
		$widget_options  = array(
			'classname'   => 'ksas_profile',
			'description' => __( 'Displays a random profile', 'ksas_profile' ),
		);
		$control_options = array(
			'width'   => 300,
			'height'  => 350,
			'id_base' => 'ksas_profile-widget',
		);
		parent::__construct( 'ksas_profile-widget', __( 'Profile/Spotlight', 'ksas_profile' ), $widget_options, $control_options );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title']           = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['category_choice'] = isset( $new_instance['category_choice'] ) ? wp_strip_all_tags( $new_instance['category_choice'] ) : '';
		$instance['random']          = isset( $new_instance['random'] ) ? wp_strip_all_tags( $new_instance['random'] ) : '';
		$instance['link']            = isset( $new_instance['link'] ) ? wp_strip_all_tags( $new_instance['link'] ) : '';

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title'           => __( 'Spotlight', 'ksas_profile' ),
			'category_choice' => '1',
			'random'          => 'rand',
			'link'            => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'hybrid' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Choose Profile Type: Select Box -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_choice' ) ); ?>"><?php _e( 'Choose Testimonial Type:', 'ksas_testimonial' ); ?></label> 
			<select id="<?php echo esc_attr( $this->get_field_id( 'category_choice' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_choice' ) ); ?>" class="widefat" style="width:100%;">
			<?php
			global $wpdb;
				$categories = get_categories(
					array(
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => 1,
						'taxonomy'   => 'profiletype',
					)
				);
			foreach ( $categories as $category ) {
				$category_choice = $category->slug;
				$category_title  = $category->name;
				?>
			<option value="<?php echo esc_html( $category_choice ); ?>" 
					<?php
					if ( $category_choice == $instance['category_choice'] ) {
							echo 'selected="selected"';}
					?>
					><?php echo esc_html( $category_title ); ?>
			</option>
			<?php } ?>
			</select>
		</p>
		<!-- Widget Link: Archive Link -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php _e( 'Link to Testimonial Type Archive:', 'hybrid' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" value="<?php echo $instance['link']; ?>" style="width:100%;" />
		</p>
		<!-- Order: Latest or Random -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>"><?php esc_html_e( 'Order (Latest or Random)', 'ksas_testimonial' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'random' ) ); ?>" class="widefat" style="width:100%;">
			<option value="date" 
			<?php
			if ( 'date' === $instance['random'] ) {
				echo 'selected="selected"';}
			?>
			>Latest Only</option>
			<option value="rand" 
			<?php
			if ( 'rand' === $instance['random'] ) {
				echo 'selected="selected"';}
			?>
			>Random</option>
			</select>
		</p>

		<?php
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		/* Our variables from the widget settings. */
		$title               = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$category_choice     = isset( $instance['category_choice'] ) ? $instance['category_choice'] : '';
		$acf_category_choice = get_field( 'choose_profile_type' );
		$random              = isset( $instance['random'] ) ? $instance['random'] : '';
		$archive_link        = isset( $instance['link'] ) ? $instance['link'] : '';
		echo $args['before_widget'];

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		/** Create a new filtering function that will add our where clause to the query */
		global $post;
		$profile_widget_query = new WP_Query(
			array(
				'post_type'      => 'profile',
				'profiletype'    => $category_choice,
				'orderby'        => $random,
				'year'           => $age,
				'posts_per_page' => 1,
			)
		);

		if ( $profile_widget_query->have_posts() ) :
			while ( $profile_widget_query->have_posts() ) :
				$profile_widget_query->the_post();
				?>
				<article class="row" aria-labelledby="post-<?php the_ID(); ?>" >	
					<div class="small-12 columns">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail(
							'medium',
							array(
								'class' => 'floatleft',
								'alt'   => get_the_title(),
							)
						); }
					?>
						<div>
						<h5 class="spotlight-profile-title"><a href="<?php the_permalink(); ?>" id="post-<?php the_ID(); ?>" ><?php the_title(); ?><span class="link"></span></a></h5>
						<p class="spotlight-profile-content">
						<?php
						if ( get_post_meta( $post->ID, 'ecpt_pull_quote', true ) ) {
							echo esc_html( get_post_meta( $post->ID, 'ecpt_pull_quote', true ) );
						} else {
							echo wp_trim_words( get_the_excerpt(), 35, '...' ); }
						?>
						</div>
						</p>
					</div>
				</article>
					<?php endwhile; ?>
		<article aria-label="spotlight archives">
			<p class="view-more-link">
				<a href="<?php echo ( esc_url( $archive_link ) ); ?>">View more Spotlights <span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a>
			</p>
		</article>
				<?php
	endif;
			echo $args['after_widget'];
	}

}

/** Register widgets */
function ksas_register_profile_widgets() {
	register_widget( 'Profile_Widget' );
}
add_action( 'widgets_init', 'ksas_register_profile_widgets' );

/* Add ACF field group for Custom Meta Fields */

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_63458aee3773b',
			'title'                 => 'Custom Profile Fields',
			'fields'                => array(
				array(
					'key'               => 'field_6345911629e05',
					'label'             => 'Custom Profile Fields',
					'name'              => 'custom_profile_fields',
					'type'              => 'repeater',
					'instructions'      => 'Enter any custom title and content fields below. Suggestions include Profile title/affiliation (alumni, class, major) or profile detail (job title, internship location, applied experience location, etc). 
	
	You can add a new set of custom fields by clicking the "Add New Fields" button.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'layout'            => 'row',
					'pagination'        => 0,
					'min'               => 0,
					'max'               => 0,
					'collapsed'         => '',
					'button_label'      => 'Add New Fields',
					'rows_per_page'     => 20,
					'sub_fields'        => array(
						array(
							'key'               => 'field_6345912f29e06',
							'label'             => 'Custom Title',
							'name'              => 'custom_title',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 'Class Of',
							'maxlength'         => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'parent_repeater'   => 'field_6345911629e05',
						),
						array(
							'key'               => 'field_6345913f29e07',
							'label'             => 'Custom Content',
							'name'              => 'custom_content',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 2022,
							'maxlength'         => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'parent_repeater'   => 'field_6345911629e05',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'profile',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);

endif;


/*
 Add ACF field group for Profile Taxonomy select for template spotlight-profiles.php
* and Widget
*/

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_6345b91aa2fb7',
			'title'                 => 'Profile Taxonomy Selector',
			'fields'                => array(
				array(
					'key'               => 'field_6345b91f6cd9c',
					'label'             => 'Profile Type',
					'name'              => 'profile_type',
					'type'              => 'taxonomy',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'taxonomy'          => 'profiletype',
					'add_term'          => 1,
					'save_terms'        => 0,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'field_type'        => 'radio',
					'allow_null'        => 0,
					'multiple'          => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_template',
						'operator' => '==',
						'value'    => 'page-templates/spotlight-profiles.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);
endif;
