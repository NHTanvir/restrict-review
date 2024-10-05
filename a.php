<?php
namespace Jet_Reviews\User\Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class User_Specific_Review extends Base_Condition {

	/**
	 * Condition slug.
	 * @var string
	 */
	private $slug = 'user-specific-review';

	/**
	 * Invalid message.
	 * @var boolean
	 */
	private $invalid_message = false;


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->invalid_message = __( '*You are not allowed to submit a review', 'jet-reviews' );
	}

	/**
	 * Get the condition type.
	 * @return string
	 */
	public function get_type() {
		return 'can-review';
	}

	/**
	 * Get the condition slug.
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the invalid message.
	 * @return string
	 */
	public function get_invalid_message() {
		return apply_filters( 'jet-reviews/user/conditions/invalid-message/' . $this->slug, $this->invalid_message, $this );
	}

	/**
	 * Check the condition.
	 * @param string $source
	 * @param array  $user_data
	 * @return bool
	 */
	public function check( $source = 'post', $user_data = [] ) {

		// Hide review for all users except those in allowed_user_ids
		// if ( ! in_array( $user_data['id'], $this->allowed_user_ids ) ) {
		// 	return false;
		// }

        if( get_current_user_id() == 2 ) {
            return false;
        }

		return true;
	}
}
?>
