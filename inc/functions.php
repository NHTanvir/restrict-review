<?php
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'pc_site_url' ) ) :
	/**
	 * Get the site's base URL.
	 *
	 * @return string Site URL.
	 */
	function pc_site_url() {
		return get_bloginfo( 'url' );
	}
endif;

if ( ! function_exists( 'pc_get_user_or_author_id' ) ) :
	/**
	 * Retrieve user_id or author_id from a job submission.
	 *
	 * @param int    $job_id   Job ID.
	 * @param string $id_type  ID type ('user_id' or 'author_id').
	 * @return int|null
	 */
	function pc_get_user_or_author_id( $job_id, $id_type ) {
		global $wpdb;

		if ( ! in_array( $id_type, array( 'user_id', 'author_id' ), true ) ) {
			return null;
		}

		$query  = $wpdb->prepare(
			"SELECT $id_type FROM {$wpdb->prefix}trade_job_submission WHERE post_id = %d",
			$job_id
		);
		$result = $wpdb->get_var( $query );

		return $result ? intval( $result ) : null;
	}
endif;

/**
 * Remove "Private:" or "Protected:" prefixes from post titles.
 */
add_filter( 'the_title', 'pc_remove_private_prefix' );
function pc_remove_private_prefix( $title ) {
	return str_replace( array( 'Private: ', 'Protected: ' ), '', $title );
}

/**
 * Add custom CSS classes to posts based on the role of their user.
 */
add_filter( 'post_class', 'pc_add_custom_user_role_classes', 10, 3 );
function pc_add_custom_user_role_classes( $classes, $class, $post_id ) {
	$user_id = get_post_meta( $post_id, 'user_id', true );

	if ( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		if ( $user ) {
			if ( in_array( 'client', (array) $user->roles, true ) ) {
				$classes[] = 'has-client-role';
			}

			$specific_roles = array( 'tradesman', '1-day-membership', '1-week-plan', '1-month-plan' );
			if ( array_intersect( $specific_roles, (array) $user->roles ) ) {
				$classes[] = 'has-tradesman-role';
			}
		}
	}

	return $classes;
}
