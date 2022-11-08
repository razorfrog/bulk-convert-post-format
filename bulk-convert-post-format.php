<?php
/**
 * @package Bulk Convert Post Format
 * @version 1.1.2
 */
/*
Plugin Name: Bulk Convert Post Format
Plugin URI: https://razorfrog.com/bulk-edit-wordpress-post-format/
Description: Bulk convert posts in a category to a selected post format.
Version: 1.1.2
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
	?>

	<div class="wrap">
		<h1>Bulk Convert Posts to New Post Format</h1>
		
	    <?php
		if ( ! $start_from ) {
			$start_from = 0;
		}
	
		if ($category_post && $post_format_post) {
			$args = array('category' => $category_post, 'posts_per_page' => $posts_per_page, 'offset' => $start_from);
	        $posts_array = get_posts( $args );
	
			if ( ! $posts_array ) { ?>
				<div style="background: #e5e5e5; width: 100%; padding: 20px; margin: 10px 0 0; box-sizing: border-box;">
					<h2 style="margin: 0 0 20px; line-height: 1.6;">All done!</h2>
					<a class="button-primary" href="/wp-admin/tools.php?page=converter" />Convert again</a>
				</div><?php
				return;
			}
	
	        foreach ( $posts_array as $post ) {
	            set_post_format($post->ID, $post_format_post );
			}
	
			$post_count = count($posts_array); ?>
			
			<div style="background: #e5e5e5; width: 100%; padding: 20px; margin: 10px 0 0; box-sizing: border-box;">
				<h2 style="margin: 0; line-height: 1.6;"><?php echo($post_count+$start_from) ?> posts done... This page reloads automatically.</h2><?php
				print("<script>post_to_url('', {'category': '".$category_post."','post_format': '".$post_format_post."','start_from': '".($post_count+$start_from)."','posts_per_page': '".$posts_per_page."'}, 'POST');</script>");?>
			</div><?php
			return;
		}?>
		
	    <form method="POST" action="" id="bulk-convert-post-format">		    
			<label>Convert all post in category: </label>
			<select name="category">';<?php
				$args = array("hide_empty" => 0,
			                  "type"       => "post",      
			                  "orderby"    => "name",
			                  "order"      => "ASC" );
			    $categories = get_categories($args);
				foreach ($categories as $categ) {
					print('<option value="'.$categ->cat_ID.'">'.$categ->name.'</option>');
				}?>
			</select>
			
			<label>To post format: </label>
		    <select name="post_format">';
			<?php
				$formats = get_post_format_slugs();
				foreach ($formats as $format) {
			    	print('<option value="'.$format.'">'. ucfirst($format) .'</option>');
				}
			?>
		    </select>
			
			<label>Posts to process per page reload: </label>
			<input type="number" name="posts_per_page" value="9999">
			<p>Choose a lower value if the tool does not finish.</p>
			
			<input class="button-primary" type="submit" value="Convert" />
	    </form>
	</div>
	
	<style>
		form#bulk-convert-post-format {
		    background: #e5e5e5;
		    width: 100%;
		    padding: 20px;
		    margin: 10px 0 0;
		    box-sizing: border-box;
		}
		
		form#bulk-convert-post-format label {
		    display: block;
		    margin: 20px 0 10px;
		}
		
		form#bulk-convert-post-format label:first-child {
			margin-top: 0 !important;
		}
		
		form#bulk-convert-post-format select, form#bulk-convert-post-format input[type="number"] {
		    width: 200px;
		}
		
		form#bulk-convert-post-format p {
			margin: 10px 0;
			font-style: italic;
		}
		
		form#bulk-convert-post-format input[type="submit"] {
			margin: 10px 0 0;
			padding: 0 20px;
		}
	</style><?php
}

?>