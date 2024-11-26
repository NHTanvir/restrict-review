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

        if ( empty( $user_data ) ) {
            return false; 
        }
        global $wpdb;
        $source_instance    = jet_reviews()->reviews_manager->sources->get_source_instance( $source );
        $source_id          = $source_instance->get_current_id();
        $user_id            = $user_data['id'];
        $post_id            = get_the_ID();
        $post_user_id       = get_post_meta( $post_id, 'user_id', true );
        $table_name         = $wpdb->prefix . 'trade_job_submission';

        $completed_jobs_query = $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM $table_name 
             WHERE user_id = %d 
             AND author_id = %d 
             AND status = %s",
            $post_user_id, 
            $user_id,
            'complete'
        );
        
        $completed_jobs_query_2 = $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) 
             FROM $table_name 
             WHERE user_id = %d 
             AND author_id = %d 
             AND status = %s",
            $user_id, 
            $post_user_id,
            'complete'
        );
        
        $completed_jobs_count   = $wpdb->get_var($completed_jobs_query);
        $completed_jobs_count2  = $wpdb->get_var($completed_jobs_query_2);
        $reviews_table          = jet_reviews()->db->tables( 'reviews', 'name' );
    
        $reviews_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $reviews_table WHERE source = %s AND post_id = %s AND author = %s AND approved = 1",
            $source,
            $source_id,
            $user_id
        );
    
        $submitted_reviews_count = $wpdb->get_var( $reviews_query );

        if ( $completed_jobs_count > $submitted_reviews_count ) {
            return true;
        }
        if ( $completed_jobs_count2 > $submitted_reviews_count ) {
            return true;
        }
    
        return false; 
    }
    
}
