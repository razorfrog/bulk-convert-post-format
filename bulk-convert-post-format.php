<?php
/**
 * @package Bulk Convert Post Format
 * @version 1.0
 */
/*
Plugin Name: Bulk Convert Post Format
Plugin URI: https://razorfrog.com/bulk-edit-wordpress-post-format/
Description: Bulk convert posts in a category to a selected post format.
Version: 1.0.2
Author: Razorfrog Web Design
Author URI: https://razorfrog.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Menu
add_action( 'admin_menu', 'register_my_custom_menu_page' );

function register_my_custom_menu_page(){
    $page = add_management_page( 'Bulk Edit Post Format', 'Bulk Edit Post Format', 'manage_options', 'converter', 'category_to_post_format_page', "" );
	add_action( "admin_print_scripts-$page", 'loadjs_admin_head' );
	
}

function loadjs_admin_head() {
	wp_enqueue_script('loadjs', plugins_url( '/post_to_url.js' , __FILE__ ));
	
}

function category_to_post_format_page() {
	$category_post = $_POST[ 'category' ];
	$post_format_post = $_POST[ 'post_format' ];
	$start_from = $_POST[ 'start_from' ];
	$posts_per_page = $_POST[ 'posts_per_page' ];

	if ( ! $start_from ) { $start_from = 0; }

	if ($category_post && $post_format_post) {
		$args = array('category' => $category_post, 'posts_per_page' => $posts_per_page, 'offset' => $start_from);
	        $posts_array = get_posts( $args );

			if ( ! $posts_array ) {
				print("<h2>All done!</h2>");
				return;
			}
	
	        foreach ( $posts_array as $post ) {
	                set_post_format($post->ID, $post_format_post );
			}

		$post_count = count($posts_array);
		
		print("<h2>".($post_count+$start_from)." posts done... this page reloads automatically.</h2><script>post_to_url('', {'category': '".$category_post."','post_format': '".$post_format_post."','start_from': '".($post_count+$start_from)."','posts_per_page': '".$posts_per_page."'}, 'POST');</script>");
		return;
	} // end if
	
		echo '<h1>Bulk Convert Posts to New Post Format</h1>
        <form method="POST" action="">
		<label>Convert all post in category </label>
		<select name="category">';

		$args = array("hide_empty" => 0,
                    "type"      => "post",      
                    "orderby"   => "name",
                    "order"     => "ASC" );
                $categories = get_categories($args);
		foreach ($categories as $categ):
			print('<option value="'.$categ->cat_ID.'">'.$categ->name.'</option>');
		endforeach;

		echo '</select>
		<label> to post format </label>
        <select name="post_format">';

		$formats = get_post_format_slugs();
		foreach ($formats as $format) {
                	print('<option value="'.$format.'">'.$format.'</option>');
		}

        echo '</select>
		<input type="submit" value="Do it!" />
		<br><br>
		<label>Posts to process per page reload - put a lower value if the tool does not finish: </label>
		<input type="text" name="posts_per_page" value="9999">
        </form>';
 	} // end function
?>