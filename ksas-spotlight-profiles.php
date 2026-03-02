<?php
/**
 * Plugin Name: KSAS Student/Faculty Profiles and Spotlights
 * Plugin URI: http://krieger.jhu.edu/
 * Description: Creates a custom post type for profiles.
 * Version: 6.0
 * Author: KSAS Communications
 * Author URI: mailto:ksasweb@jhu.edu
 * License: GPL2
 *
 * @package KSAS_Profiles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registration code for profile post type.
 *
 *  @return void
 */
function ksas_register_profile_posttype() {
	$labels = array(
		'name'               => _x( 'Profiles', 'post type general name', 'ksas_profiles' ),
		'singular_name'      => _x( 'Profile', 'post type singular name', 'ksas_profiles' ),
		'add_new'            => _x( 'Add New', 'Profile', 'ksas_profiles' ),
		'add_new_item'       => __( 'Add New Profile ', 'ksas_profiles' ),
		'edit_item'          => __( 'Edit Profile ', 'ksas_profiles' ),
		'new_item'           => __( 'New Profile ', 'ksas_profiles' ),
		'view_item'          => __( 'View Profile ', 'ksas_profiles' ),
		'search_items'       => __( 'Search Profiles ', 'ksas_profiles' ),
		'not_found'          => __( 'No Profile found', 'ksas_profiles' ),
		'not_found_in_trash' => __( 'No Profiles found in Trash', 'ksas_profiles' ),
		'parent_item_colon'  => '',
	);

	$taxonomies = array( 'affiliation' );
	$supports   = array( 'title', 'editor', 'revisions', 'thumbnail', 'excerpt' );

	$post_type_args = array(
		'labels'             => $labels,
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
add_action( 'init', 'ksas_register_profile_posttype' );

/**
 * Registration code for profiletype taxonomy.
 */
function ksas_register_profiletype_tax() {
	$labels = array(
		'name'               => _x( 'Profile Types', 'taxonomy general name', 'ksas_profiles' ),
		'singular_name'      => _x( 'Profile Type', 'taxonomy singular name', 'ksas_profiles' ),
		'add_new_item'       => __( 'Add New Profile Type', 'ksas_profiles' ),
		'edit_item'          => __( 'Edit Profile Type', 'ksas_profiles' ),
		'new_item'           => __( 'New Profile Type', 'ksas_profiles' ),
		'view_item'          => __( 'View Profile Type', 'ksas_profiles' ),
		'search_items'       => __( 'Search Profile Types', 'ksas_profiles' ),
		'not_found'          => __( 'No Profile Type found', 'ksas_profiles' ),
		'not_found_in_trash' => __( 'No Profile Type found in Trash', 'ksas_profiles' ),
	);

	$args = array(
		'labels'            => $labels,
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
	register_taxonomy( 'profiletype', array( 'profile' ), $args );
}
add_action( 'init', 'ksas_register_profiletype_tax' );

/**
 * Automatically insert default terms if they don't exist.
 */
function ksas_check_profiletype_terms() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'profiletype',
			'hide_empty' => false,
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		$default_terms = array(
			array(
				'name' => 'Undergraduate',
				'slug' => 'undergraduate-profile',
			),
			array(
				'name' => 'Graduate',
				'slug' => 'graduate-profile',
			),
			array(
				'name' => 'Spotlight',
				'slug' => 'spotlight',
			),
		);
		foreach ( $default_terms as $term ) {
			if ( ! term_exists( $term['name'], 'profiletype' ) ) {
				wp_insert_term( $term['name'], 'profiletype', array( 'slug' => $term['slug'] ) );
			}
		}
	}
}
add_action( 'init', 'ksas_check_profiletype_terms' );

/**
 * Add Pull Quote Meta Box.
 */
function ksas_add_pullquote_meta_box() {
	add_meta_box(
		'ksas_pullquote',
		__( 'Pull Quote', 'ksas_profiles' ),
		'ksas_render_pullquote_box',
		'profile',
		'normal',
		'default'
	);
}
add_action( 'admin_menu', 'ksas_add_pullquote_meta_box' );

/**
 * Render Pull Quote Meta Box content.
 *
 * @param WP_Post $post The current post object.
 */
function ksas_render_pullquote_box( $post ) {
	wp_nonce_field( 'ksas_pullquote_save', 'ksas_pullquote_nonce' );
	$value = get_post_meta( $post->ID, 'ecpt_pull_quote', true );
	?>
	<table class="form-table">
		<tr>
			<th style="width:20%">
				<label for="ecpt_pull_quote"><?php esc_html_e( 'Pull Quote or Research Topic', 'ksas_profiles' ); ?></label>
			</th>
			<td>
				<textarea name="ecpt_pull_quote" id="ecpt_pull_quote" cols="60" rows="8" style="width:97%"><?php echo esc_textarea( $value ); ?></textarea>
				<p class="description"><?php esc_html_e( 'This is the text shown on profile index page, widgets and homepage sliders', 'ksas_profiles' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save Pull Quote Meta Box data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function ksas_save_pullquote_meta( $post_id ) {
	if ( ! isset( $_POST['ksas_pullquote_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ksas_pullquote_nonce'] ), 'ksas_pullquote_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['ecpt_pull_quote'] ) ) {
		update_post_meta( $post_id, 'ecpt_pull_quote', sanitize_textarea_field( wp_unslash( $_POST['ecpt_pull_quote'] ) ) );
	}
}
add_action( 'save_post', 'ksas_save_pullquote_meta' );

/**
 * Customize Profile Admin Columns.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function ksas_profile_columns( $columns ) {
	$columns = array(
		'cb'        => '<input type="checkbox" />',
		'title'     => __( 'Name', 'ksas_profiles' ),
		'type'      => __( 'Type', 'ksas_profiles' ),
		'quote'     => __( 'Excerpt', 'ksas_profiles' ),
		'thumbnail' => __( 'Thumbnail', 'ksas_profiles' ),
		'date'      => __( 'Date', 'ksas_profiles' ),
	);
	return $columns;
}
add_filter( 'manage_edit-profile_columns', 'ksas_profile_columns' );

/**
 * Display content for custom admin columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function ksas_render_profile_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'type':
			$terms = get_the_terms( $post_id, 'profiletype' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url(
							add_query_arg(
								array(
									'post_type'   => 'profile',
									'profiletype' => $term->slug,
								),
								'edit.php'
							)
						),
						esc_html( $term->name )
					);
				}
				echo wp_kses_post( implode( ', ', $out ) );
			} else {
				esc_html_e( 'No Type Assigned', 'ksas_profiles' );
			}
			break;

		case 'quote':
			$quote = get_post_meta( $post_id, 'ecpt_pull_quote', true );
			echo $quote ? esc_html( $quote ) : esc_html( wp_trim_words( get_the_excerpt( $post_id ), 20 ) );
			break;

		case 'thumbnail':
			if ( has_post_thumbnail( $post_id ) ) {
				the_post_thumbnail( array( 50, 50 ) );
			} else {
				esc_html_e( 'No Photo', 'ksas_profiles' );
			}
			break;
	}
}
add_action( 'manage_profile_posts_custom_column', 'ksas_render_profile_columns', 10, 2 );

/**
 * Include the Widget Class.
 */
$ksas_widget_path = plugin_dir_path( __FILE__ ) . 'includes/class-spotlight-profiles-widget.php';

if ( file_exists( $ksas_widget_path ) ) {
	require_once $ksas_widget_path;
}

/**
 * Register the widget.
 * This function should remain in the main file or a setup file.
 */
function ksas_register_profile_widgets() {
	if ( class_exists( 'Spotlight_Profiles_Widget' ) ) {
		register_widget( 'Spotlight_Profiles_Widget' );
	}
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
