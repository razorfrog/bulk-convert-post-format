<?php

/**
 * Plugin Name:       Bulk Convert Post Format
 * Plugin URI:        https://razorfrog.com/bulk-edit-wordpress-post-format/
 * Description:       Bulk convert posts in a category to a selected post format.
 * Version:           1.2.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Razorfrog Web Design
 * Author URI:        https://razorfrog.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bulk-convert-post-format
 */

defined( 'ABSPATH' ) || exit;

define( 'BULK_CONVERT_POST_FORMAT_VERSION', '1.2.0' );

// Menu
add_action('admin_menu', 'bulk_convert_post_format_menu');

function bulk_convert_post_format_menu() {
		$page = add_management_page('Bulk Edit Post Format', 'Bulk Edit Post Format', 'manage_options', 'converter', 'bulk_convert_post_format_page');
		add_action("admin_print_scripts-$page", 'bulk_convert_post_format_scripts');
}

function bulk_convert_post_format_admin_styles( $hook_suffix ) {
		if ( 'tools_page_converter' !== $hook_suffix ) {
				return;
		}
		wp_enqueue_style(
				'bulk-convert-post-format-admin',
				plugins_url( 'assets/admin.css', __FILE__ ),
				array(),
				(string) filemtime( plugin_dir_path( __FILE__ ) . 'assets/admin.css' )
		);
}
add_action( 'admin_enqueue_scripts', 'bulk_convert_post_format_admin_styles' );

function bulk_convert_post_format_scripts() {
		// Loaded in the head: the progress page calls post_to_url() inline.
		wp_enqueue_script(
				'bulk-convert-post-format',
				plugins_url( '/post_to_url.js', __FILE__ ),
				array(),
				(string) filemtime( plugin_dir_path( __FILE__ ) . 'post_to_url.js' ),
				false
		);
}

function bulk_convert_post_format_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'bulk-convert-post-format' ) );
		}

		// Check nonce first.
		$has_valid_nonce = isset( $_POST['bulk_convert_post_format_nonce'] ) && wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['bulk_convert_post_format_nonce'] ) ),
				'bulk_convert_post_format_action'
		);

		// Read and sanitize POST values only if nonce is valid.
		$category_post   = ( $has_valid_nonce && isset( $_POST['category'] ) ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		$post_format_post = ( $has_valid_nonce && isset( $_POST['post_format'] ) ) ? sanitize_text_field( wp_unslash( $_POST['post_format'] ) ) : '';
		$start_from      = ( $has_valid_nonce && isset( $_POST['start_from'] ) ) ? absint( wp_unslash( $_POST['start_from'] ) ) : 0;
		$posts_per_page  = ( $has_valid_nonce && isset( $_POST['posts_per_page'] ) ) ? absint( wp_unslash( $_POST['posts_per_page'] ) ) : 9999;
		$total_converted = ( $has_valid_nonce && isset( $_POST['total_converted'] ) ) ? absint( wp_unslash( $_POST['total_converted'] ) ) : 0;

		if ( $has_valid_nonce && $category_post && $post_format_post ) {
				// Use category__in to ensure only the selected category is included.
				$args = array(
						'category__in'   => array( $category_post ),
						'posts_per_page' => $posts_per_page,
						'offset'         => $start_from,
						'post_type'      => 'post',
						'post_status'    => 'publish',
				);
				$posts_array = get_posts( $args );

				if ( empty( $posts_array ) ) {
						echo '<div style="background: #e5e5e5; width: 100%; padding: 20px; margin: 10px 0 0; box-sizing: border-box;">
														<h2 style="margin: 0; line-height: 1.6;">Done!</h2>
														<p style="margin: 10px 0 20px;">' . esc_html( $total_converted ) . ' post(s) have been converted to ' . esc_html( $post_format_post ) . ' format.</p>
														<a class="button-primary" href="' . esc_url( admin_url( 'tools.php?page=converter' ) ) . '">Convert again</a>
												</div>';
						return;
				}

				foreach ( $posts_array as $post ) {
						set_post_format( $post->ID, $post_format_post );
				}

				$post_count       = count( $posts_array );
				$total_converted += $post_count;

				echo '<div style="background: #e5e5e5; width: 100%; padding: 20px; margin: 10px 0 0; box-sizing: border-box;">
												<h2 style="margin: 0; line-height: 1.6;">This page reloads automatically.</h2>
												<p style="margin: 10px 0 0;">Converting... ' . esc_html( $total_converted ) . ' post(s) converted so far.</p>';

				$nonce = sanitize_text_field( wp_unslash( $_POST['bulk_convert_post_format_nonce'] ) );

				echo "<script>post_to_url('', {
														'bulk_convert_post_format_nonce': '" . esc_js( $nonce ) . "',
														'category': '" . esc_js( $category_post ) . "',
														'post_format': '" . esc_js( $post_format_post ) . "',
														'start_from': '" . esc_js( $total_converted ) . "',
														'posts_per_page': '" . esc_js( $posts_per_page ) . "',
														'total_converted': '" . esc_js( $total_converted ) . "'
										}, 'POST');
								</script>";
				echo '</div>';
				return;
		}

		$categories = get_categories( array(
				'hide_empty' => 0,
				'type'       => 'post',
				'orderby'    => 'name',
				'order'      => 'ASC',
		) );
		$formats    = get_post_format_slugs();
		?>
		<div class="wrap bcpf-wrap">
				<div class="bcpf-header">
			<img src="<?php echo esc_url( plugins_url( 'assets/razorfrog-icon.png', __FILE__ ) ); ?>" alt="" width="48" height="48" />
			<div>
				<h1><?php esc_html_e( 'Bulk Convert Post Format', 'bulk-convert-post-format' ); ?></h1>
				<p>
					<?php
					printf(
						/* translators: %s: Razorfrog, linked to its website. */
						esc_html__( 'by %s', 'bulk-convert-post-format' ),
						'<a href="https://razorfrog.com/" target="_blank" rel="noopener noreferrer">Razorfrog</a>'
					);
					?>
					&middot; <?php echo esc_html( sprintf( /* translators: %s: plugin version. */ __( 'Version %s', 'bulk-convert-post-format' ), BULK_CONVERT_POST_FORMAT_VERSION ) ); ?>
				</p>
			</div>
		</div>
				<h2 class="bcpf-title">Bulk Convert Posts to New Post Format</h2>
				<div class="bcpf-layout">
				<div class="bcpf-main">
				<form method="POST" action="" id="bulk-convert-post-format">
						<?php wp_nonce_field( 'bulk_convert_post_format_action', 'bulk_convert_post_format_nonce' ); ?>
						<label>Convert all post in category:</label>
						<select name="category">
								<?php
								$categories = get_categories( array(
										'hide_empty' => 0,
										'type'       => 'post',
										'orderby'    => 'name',
										'order'      => 'ASC',
										'parent'     => 0,
								) );
								bulk_convert_post_format_category_options( $categories );
								?>
						</select>
						<label>To post format:</label>
						<select name="post_format">
								<?php foreach ( $formats as $format ) : ?>
										<option value="<?php echo esc_attr( $format ); ?>"><?php echo esc_html( ucfirst( $format ) ); ?></option>
								<?php endforeach; ?>
						</select>
						<label>Posts to process per page reload:</label>
						<input type="number" name="posts_per_page" value="100">
						<p>Choose a lower value if the tool does not finish.</p>
						<input class="button-primary" type="submit" value="Convert" />
				</form>
				</div>
				<?php bulk_convert_post_format_sidebar(); ?>
				</div>
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

/**
 * The Razorfrog sidebar: who makes the plugin, where to get help, and our other plugins.
 */
function bulk_convert_post_format_sidebar() {
	$wporg   = 'https://wordpress.org/plugins/';
	$plugins = array(
			'cinza-grid' => array( 'Cinza Grid', __( 'A minimal grid plugin built with Isotope.', 'bulk-convert-post-format' ) ),
			'cinza-slider' => array( 'Cinza Slider', __( 'A minimal slider plugin built with Flickity.', 'bulk-convert-post-format' ) ),
			'easy-content-lists' => array( 'Easy Content Lists', __( 'Shortcodes for listing your pages, posts, taxonomies and tags.', 'bulk-convert-post-format' ) ),
			'woo-product-dropdown-widget' => array( 'Product Dropdown Widget for WooCommerce', __( 'A dropdown of WooCommerce products, by category, sorted by price, reviews or more.', 'bulk-convert-post-format' ) ),
			'spinitron-player' => array( 'Spinitron Player', __( 'A streaming player for radio stations that use Spinitron.', 'bulk-convert-post-format' ) ),
			'link-in-bio-report' => array( 'Link in Bio Report', __( 'Counts taps on your Instagram links page and emails a weekly report.', 'bulk-convert-post-format' ) ),
	);
	?>
	<div class="bcpf-sidebar">
		<div class="bcpf-box bcpf-about">
			<a href="https://razorfrog.com/" target="_blank" rel="noopener noreferrer" class="bcpf-logo">
				<img src="<?php echo esc_url( plugins_url( 'assets/razorfrog-icon.png', __FILE__ ) ); ?>" alt="" width="40" height="40" />
				<span>Razorfrog</span>
			</a>
			<p><?php esc_html_e( 'Bulk Convert Post Format is a free plugin by Razorfrog, a web agency in San Francisco.', 'bulk-convert-post-format' ); ?></p>
			<p class="bcpf-buttons">
				<a class="button" href="https://wordpress.org/support/plugin/bulk-convert-post-format/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get help', 'bulk-convert-post-format' ); ?></a>
				<a class="button" href="https://wordpress.org/support/plugin/bulk-convert-post-format/reviews/#new-post" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'bulk-convert-post-format' ); ?></a>
			</p>
		</div>
		<div class="bcpf-box">
			<h2><?php esc_html_e( 'More free plugins from Razorfrog', 'bulk-convert-post-format' ); ?></h2>
			<ul class="bcpf-plugins">
				<?php foreach ( $plugins as $plugin_slug => $plugin ) : ?>
					<li>
						<a href="<?php echo esc_url( $wporg . $plugin_slug . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $plugin[0] ); ?></a>
						<span><?php echo esc_html( $plugin[1] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php
}

function bulk_convert_post_format_category_options( $categories, $depth = 0 ) {
	 foreach ( $categories as $category ) {
		  $category_count = (int) $category->count;

		  echo '<option value="' . esc_attr( $category->cat_ID ) . '">'
			. esc_html( str_repeat( '— ', $depth ) )
			. esc_html( $category->name )
			. ' (' . esc_html( $category_count ) . ')</option>';

		  $child_categories = get_categories(
				array(
					 'hide_empty' => 0,
					 'type'       => 'post',
					 'orderby'    => 'name',
					 'order'      => 'ASC',
					 'parent'     => $category->term_id,
				)
		  );

		  if ( ! empty( $child_categories ) ) {
				bulk_convert_post_format_category_options( $child_categories, $depth + 1 );
		  }
	 }
}

?>
