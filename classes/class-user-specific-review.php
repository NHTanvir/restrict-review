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
        $this->invalid_message = __( '*You are not allowed to submit a review the user must apply and complete any of your jobs.', 'restrict-reviews' );
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

        // Check if user data is provided
        if ( empty( $user_data ) ) {
            return true;
        }
    
        $source_instance = jet_reviews()->reviews_manager->sources->get_source_instance( $source );
        $source_id = $source_instance->get_current_id();
        $user_id = $user_data['id'];
    
        // Step 1: Get the user’s completed jobs
        global $wpdb;
        $post_id = get_the_ID(); // Current post ID
        $post_user_id = get_post_meta( $post_id, 'user_id', true );
    
        $table_name = $wpdb->prefix . 'trade_job_submission';
        
        // Count completed jobs for the specific user
        $completed_jobs_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND status = %s",
            $post_user_id,
            'complete'
        );
    
        $completed_jobs_count = $wpdb->get_var( $completed_jobs_query );
    
        $reviews_table = jet_reviews()->db->tables( 'reviews', 'name' );
    
        $reviews_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $reviews_table WHERE source = %s AND post_id = %s AND author = %s AND approved = 1",
            $source,
            $source_id,
            $user_id
        );
    
        $submitted_reviews_count = $wpdb->get_var( $reviews_query );
    
        if ( $submitted_reviews_count >= $completed_jobs_count ) {
            return false; 
        }
    
        return true; 
    }
 
}
