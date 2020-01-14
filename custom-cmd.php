<?php
/**
 * Plugin Name:     Create Gutenberg-Friendly Test Data with WP-CLI
 * Plugin URI:      https://wpwomenofcolor.com/
 * Description:     Create Gutenberg-Friendly Test Data with WP-CLI
 * Author:          Ebonie Butler
 * Author URI:      https://wpwomenofcolor.com/
 * Text Domain:     custom-test-data-cmd
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Custom_Test_Data_Cmd
 */

/**
 * Generates 10 posts.
 *
 * @param array $args
 * @param array $assoc_args
 *
 * Usage: `wp eb-test-data --post_type=post`
 */
function eb_test_data( $args = array(), $assoc_args = array() ) {
	// Get arguments.
	$arguments = wp_parse_args(
		$assoc_args,
		array(
			'title'     => 'Sample ',
			'post_type' => 'post',
		)
	);

	// Get post content filled with Gutenberg blocks.
	ob_start();
	include 'data.test';
	$contents = ob_get_clean();

	// Create 10 posts.
	for ( $i = 0; $i < 10; $i++ ) {

		// Insert new post.
		$post    = array(
			'post_title'   => $arguments['title'] . ' ' . ucfirst( $arguments['post_type'] ) . ' ' . $i,
			'post_type'    => $arguments['post_type'],
			'post_content' => $contents,
			'post_status'  => 'publish',
		);
		$success = wp_insert_post( $post );

		// Assign a random image as the post thumbnail.
		if ( false === ( $image_ids = get_transient( 'image_ids' ) ) ) {
			$query_images_args = array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
			);
			$images    = new WP_Query( $query_images_args );
			$image_ids = wp_list_pluck( $images->posts, 'ID' );
			set_transient( 'image_ids', $image_ids, 12 * HOUR_IN_SECONDS );
		}
		if ( ! empty( $image_ids ) ) {
			$index = wp_rand( 0, count( $image_ids ) - 1 );
			set_post_thumbnail( $success, $image_ids[ $index ] );
		}

		// Close out.
		if ( 0 === $success || is_wp_error( $success ) ) {
			WP_CLI::error( 'Error occurred. Try again later.' );
		} else {
			WP_CLI::success( 'Post ID ' . $success . ' created successfully.' );
		}
	}
}

// Add the command.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'eb-test-data', 'eb_test_data' );
}
