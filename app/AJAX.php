<?php
/**
 * All AJAX related functions
 */
namespace WPPlugines\Restrict_Reviews\App;
use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage AJAX
 * @author Codexpert <hi@codexpert.io>
 */
class AJAX extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function trade_job_submission() {
		global $wpdb;

		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce']) ) {
			wp_send_json_error('Invalid nonce verification');
			wp_die();
		}

		$required_fields = ['name', 'tradesman_Email', 'subject', 'Field', 'Sub-field', 'email_description', 'post_id'];
		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[$field] ) ) {
				wp_send_json_error("The field $field is required.");
				wp_die();
			}
		}

		$name 				= sanitize_text_field( $_POST['name'] );
		$tradesman_email 	= sanitize_email( $_POST['tradesman_Email'] );
		$subject 			= sanitize_text_field( $_POST['subject'] );
		$field 				= sanitize_text_field( $_POST['Field'] );
		$sub_field 			= sanitize_text_field( $_POST['Sub-field'] );
		$message 			= sanitize_textarea_field( $_POST['email_description'] );
		$post_id 			= intval( $_POST['post_id'] );
		$author_id  		= get_post_field('post_author', $post_id);
		$user_id 			= get_current_user_id();
		$table_name 		= $wpdb->prefix . 'trade_job_submission';
	
		$inserted = $wpdb->insert(
			$table_name,
			[
				'post_id'         => $post_id,
				'author_id'       => $author_id,
				'user_id'         => $user_id, 
				'name'            => $name,
				'tradesman_email' => $tradesman_email,
				'subject'         => $subject,
				'field'           => $field,
				'sub_field'       => $sub_field,
				'message'         => $message,
				'created_at'      => current_time('mysql'),
				'status'          => "hiring"
			],
			[
				'%d', // post_id
				'%d', // author_id
				'%d', // user_id
				'%s', // name
				'%s', // tradesman_email
				'%s', // subject
				'%s', // field
				'%s', // sub_field
				'%s', // message
				'%s',  // created_at
				'%s'  // status
			]
		);
		// Return a JSON response
		if ($inserted) {
			wp_send_json_success('Your request has been submitted successfully!');
		} else {
			wp_send_json_error('Failed to submit your request. Please try again.');
		}
	
		wp_die(); 
	}

	public function update_job_status() {
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce']) ) {
			wp_send_json_error('Invalid nonce verification');
			wp_die();
		}
	
		global $wpdb;
	
		$job_id 			= intval( $_POST['job_id'] );
		$job_status 		= sanitize_text_field( $_POST['job_status'] );
		$table_name 		= $wpdb->prefix . 'trade_job_submission';
		$notification_table = $wpdb->prefix . 'trade_notifications';
		$user_id 			= pc_get_user_or_author_id( $job_id, 'user_id' );
		$author_id 			= pc_get_user_or_author_id( $job_id, 'author_id' );
		$updated 			= $wpdb->update(
					$table_name,
					array( 'status' => $job_status ),
					array( 'post_id' => $job_id ),
					array( '%s' ),
					array( '%d' )
				);
		if ($job_status === 'hired') {
			$post = array(
				'ID' => $job_id,
				'post_status' => 'private',
			);
			wp_update_post($post);

			$wpdb->insert($notification_table, array(
				'user_id' 	=> $user_id,
				'job_id' 	=> $job_id,
				'type' 		=> 'hired',
				'viewed' 	=> 0,
			));
		
			$user_email = get_the_author_meta('user_email', $user_id);
		
			$subject = 'You Are Hired!';
			$message = 'Congratulations! You have been hired for the job with ID ' . $job_id . '.';
			$message = str_replace('&nbsp;', ' ', $message);
			if (!empty($user_email)) {
				wp_mail($user_email, $subject, $message);
			}
		}
		
		if ($job_status === 'complete') {
			$post = array(
				'ID' => $job_id,
				'post_status' => 'private',
			);
		
			wp_update_post($post);

			$wpdb->insert($notification_table, array(
				'user_id' 	=> $user_id,
				'job_id' 	=> $job_id,
				'type' 		=> 'complete',
				'viewed' 	=> 0,
			));

			$wpdb->insert($notification_table, array(
				'user_id' 	=> $user_id,
				'job_id' 	=> $job_id,
				'type' 		=> 'review',
				'viewed' 	=> 0,
			));

			$wpdb->insert($notification_table, array(
				'user_id' 	=> $author_id,
				'job_id' 	=> $job_id,
				'type' 		=> 'review',
				'viewed' 	=> 0,
			));
		
			$author_email = get_the_author_meta('user_email', $author_id);
			$user_email = get_the_author_meta('user_email', $user_id);
		
			$subject = 'Job Completion - Review Request';
			$message = 'The job with ID ' . $job_id . ' has been completed. Please take a moment to provide your review.';
			$message = str_replace('&nbsp;', ' ', $message);
			if (!empty($author_email)) {
				wp_mail($author_email, $subject, $message);
			}
		
			// Send email to the user
			if (!empty($user_email)) {
				wp_mail($user_email, $subject, $message);
			}
		}
			
		if ( $job_status == 'hiring' ) {
			$post = array(
				'ID'           => $job_id,
				'post_status'  => 'publish'
			);
		
			wp_update_post( $post );
		}

		if ( $updated !== false ) {
			$data = [
				'status' => 'success',
				'message' => 'Your request has been submitted successfully!',
				'status' => $job_status
			];
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to update job status.' ) );
		}
	}
}