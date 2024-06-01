<?php
/**
 * @package Bulk Convert Post Format
 * @version 1.1.4
 */
/*
Plugin Name: Bulk Convert Post Format
Plugin URI: https://razorfrog.com/bulk-edit-wordpress-post-format/
Description: Bulk convert posts in a category to a selected post format.
Version: 1.1.4
Author: Razorfrog Web Design
Author URI: https://razorfrog.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Menu
add_action('admin_menu', 'register_my_custom_menu_page');

function register_my_custom_menu_page() {
		$page = add_management_page('Bulk Edit Post Format', 'Bulk Edit Post Format', 'manage_options', 'converter', 'category_to_post_format_page');
		add_action("admin_print_scripts-$page", 'loadjs_admin_head');
}

function loadjs_admin_head() {
		wp_enqueue_script('loadjs', plugins_url('/post_to_url.js', __FILE__));
}

function category_to_post_format_page() {
		$category_post = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
		$post_format_post = isset($_POST['post_format']) ? sanitize_text_field($_POST['post_format']) : '';
		$start_from = isset($_POST['start_from']) ? absint($_POST['start_from']) : 0;
		$posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : 9999;
		$total_converted = isset($_POST['total_converted']) ? absint($_POST['total_converted']) : 0;

		if ($category_post && $post_format_post) {
				// Use category__in to ensure only the selected category is included
				$args = array(
						'category__in' => array($category_post),
						'posts_per_page' => $posts_per_page,
						'offset' => $start_from,
						'post_type' => 'post',
						'post_status' => 'publish',
				);
				$posts_array = get_posts($args);

				if (empty($posts_array)) {
						echo '<div style="background: #e5e5e5; width: 100%; padding: 20px; margin: 10px 0 0; box-sizing: border-box;">
														<h2 style="margin: 0; line-height: 1.6;">Done!</h2>
														<p style="margin: 10px 0 20px;">' . esc_html($total_converted) . ' post(s) have been converted to ' . esc_html($post_format_post) . ' format.</p>
														<a class="button-primary" href="/wp-admin/tools.php?page=converter">Convert again</a>
												</div>';
						return;
				}

				foreach ($posts_array as $post) {
						set_post_format($post->ID, $post_format_post);
				}

				$post_count = count($posts_array);
				$total_converted += $post_count;
				echo '<div style="background: #e5e5e5; width: 100%; padding: 20px; margin: 10px 0 0; box-sizing: border-box;">
												<h2 style="margin: 0; line-height: 1.6;">This page reloads automatically.</h2>
												<p style="margin: 10px 0 0;">Converting...  ' . esc_html($total_converted) . ' out of '. esc_html($total_converted) .' posts done. </p>';
				echo "<script>post_to_url('', {
														'category': '" . esc_js($category_post) . "',
														'post_format': '" . esc_js($post_format_post) . "',
														'start_from': '" . esc_js($total_converted) . "',
														'posts_per_page': '" . esc_js($posts_per_page) . "',
														'total_converted': '" . esc_js($total_converted) . "'
										}, 'POST');
								</script>";
				echo '</div>';
				return;
		}

		$categories = get_categories(array("hide_empty" => 0, "type" => "post", "orderby" => "name", "order" => "ASC"));
		$formats = get_post_format_slugs();

		?>
		<div class="wrap">
				<h1>Bulk Convert Posts to New Post Format</h1>
				<form method="POST" action="" id="bulk-convert-post-format">
						<label>Convert all post in category:</label>
						<select name="category">
								<?php
								$categories = get_categories(array(
										"hide_empty" => 0,
										"type" => "post",
										"orderby" => "name",
										"order" => "ASC",
										"parent" => 0,
								));
								echo get_category_options($categories);
								?>
						</select>
						<label>To post format:</label>
						<select name="post_format">
								<?php foreach ($formats as $format): ?>
										<option value="<?php echo esc_attr($format); ?>"><?php echo esc_html(ucfirst($format)); ?></option>
								<?php endforeach; ?>
						</select>
						<label>Posts to process per page reload:</label>
						<input type="number" name="posts_per_page" value="100">
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
				form#bulk-convert-post-format select,
				form#bulk-convert-post-format input[type="number"] {
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
		</style>
		<?php
}

function get_category_options($categories, $depth = 0) {
		$options = '';
		foreach ($categories as $category) {
				$category_count = $category->count;
				$options .= '<option value="' . esc_attr($category->cat_ID) . '">' . str_repeat('— ', $depth) . esc_html($category->name) . ' (' . $category_count . ')</option>';
				$child_categories = get_categories(array(
						"hide_empty" => 0,
						"type" => "post",
						"orderby" => "name",
						"order" => "ASC",
						"parent" => $category->term_id,
				));
				if (!empty($child_categories)) {
						$options .= get_category_options($child_categories, $depth + 1);
				}
		}
		return $options;
}
?>
