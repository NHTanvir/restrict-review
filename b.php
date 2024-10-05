<?php
/**
 * Plugin Name: Custom Review Visibility
 * Description: Hides review functionality for all users except specific user IDs.
 * Version: 1.0
 * Author: Your Name
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register custom review condition to hide review field for all users except specific user IDs.
 */
class Custom_Review_Visibility {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'jet-reviews/user/conditions/register', [ $this, 'register_custom_conditions' ] );
	}

	/**
	 * Register custom condition.
	 */
	public function register_custom_conditions( $conditions_manager ) {
		require_once plugin_dir_path( __FILE__ ) . 'class-user-specific-review.php';
		$conditions_manager->register_condition( '\Jet_Reviews\User\Conditions\User_Specific_Review' );
	}
}

// Initialize the plugin.
// new Custom_Review_Visibility();

?>
