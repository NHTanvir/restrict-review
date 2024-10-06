<?php
namespace WPPlugines\Restrict_Reviews\App\Conditions;
use WPPlugines\Restrict_Reviews\Helper;
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class User_Specific_Review {

    /**
     * Condition slug.
     * @var string
     */
    private $slug = 'user-specific-review';

    /**
     * Invalid message.
     * @var string
     */
    private $invalid_message = '';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->invalid_message = __( '*You are not allowed to submit a review', 'restrict-reviews' );
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
        return apply_filters( 'restrict-reviews/user/conditions/invalid-message/' . $this->slug, $this->invalid_message, $this );
    }

    /**
     * Check the condition.
     * @param string $source
     * @param array  $user_data
     * @return bool
     */
    public function check( $source = 'post', $user_data = [] ) {
        Helper::pri( $user_data );
        if( get_current_user_id() == 2 ) {
            return false;
        }

        return false;
    }
}
