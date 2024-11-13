<?php
if( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Gets the site's base URL
 * 
 * @uses get_bloginfo()
 * 
 * @return string $url the site URL
 */
if( ! function_exists( 'pc_site_url' ) ) :
function pc_site_url() {
	$url = get_bloginfo( 'url' );

	return $url;
}
endif;

/**
 * Retrieves the user or author ID based on job ID.
 *
 * @param int $job_id The job ID to search for.
 * @param string $id_type The type of ID to retrieve ('user_id' or 'author_id').
 * 
 * @return int|null The requested ID or null if not found.
 */
if ( ! function_exists( 'pc_get_user_or_author_id' ) ) :
	function pc_get_user_or_author_id( $job_id, $id_type ) {
		global $wpdb;
	
		if ( ! in_array( $id_type, array( 'user_id', 'author_id' ) ) ) {
			return null;
		}

		$query = $wpdb->prepare(
			"SELECT $id_type FROM {$wpdb->prefix}trade_job_submission WHERE post_id = %d",
			$job_id
		);
	
		$result = $wpdb->get_var( $query );
		return $result ? intval( $result ) : null;
	}
endif;

add_filter('the_title', 'remove_private_prefix');
function remove_private_prefix($title) {
    $title = str_replace(['Private: ', 'Protected: '], '', $title);
    return $title;
}
