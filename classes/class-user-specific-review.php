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
        $this->invalid_message = __( '*You are unable to submit a review. In order to submit a review you need to both apply and complete a job.', 'restrict-reviews' );
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
        if ( empty( $_COOKIE['submission_id'] ) ) {
            return false; 
        }
    
        global $wpdb;
    
        $submission_id  = intval( $_COOKIE['submission_id'] ); 
        $table_name     = $wpdb->prefix . 'trade_job_submission';
        $required_roles = [ 'tradesman', '1-day-membership', '1-week-plan', '1-month-plan' ];
        $current_user   = wp_get_current_user(); 
        $user_match     = null;
        $author_match   = null;
    
        if ( array_intersect( $required_roles, $current_user->roles ) ) {
            $user_match = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table_name 
                    WHERE id = %d 
                    AND user_id = %d 
                    AND user_review = 0
                    AND status = 'complete'",
                    $submission_id, get_current_user_id()
                )
            );
        }
   
        if ( current_user_can( 'client' ) ) {
            $author_match = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table_name 
                    WHERE id = %d 
                    AND author_id = %d 
                    AND author_review = 0
                    AND status = 'complete'",
                    $submission_id, get_current_user_id()
                )
            );
        }

        return ! empty( $user_match ) || ! empty( $author_match );
    }
    
}
