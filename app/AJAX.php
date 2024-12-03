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
	
		$row_id     		= intval($_POST['job_id']);
		$job_status 		= sanitize_text_field( $_POST['job_status'] );
		$table_name 		= $wpdb->prefix . 'trade_job_submission';
		$notification_table = $wpdb->prefix . 'trade_notifications';
		$job_data = $wpdb->get_row( 
			$wpdb->prepare( 
				"SELECT post_id, user_id, author_id, status 
				 FROM {$table_name} 
				 WHERE id = %d", 
				$row_id 
			), 
			ARRAY_A 
		);
		
		$job_id     		= $job_data['post_id'];
		$user_id  			= $job_data['user_id'];
		$author_id  		= $job_data['author_id'];

		$existing_job_status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				 FROM {$table_name} 
				 WHERE post_id = %d 
				   AND status IN (%s, %s) 
				   AND id != %d",
				$job_id,
				'hired',
				'complete',
				$row_id
			)
		);

	
		if ( $existing_job_status > 0 ) {
			wp_send_json_success( [
				'status'  => trim( $job_status ),
				'message' => 'You have already hired someone. You cannot hire anyone else for this job.',
			] );
		}

		$updated = $wpdb->update(
					$table_name,
					array( 'status' => $job_status ),
					array( 'id' => $row_id ),
					array( '%s' ),
					array( '%d' )
				);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $table_name 
					SET status = %s 
					WHERE post_id = %d AND id != %d",
				'closed',
				$job_id,
				$row_id
			)
		);

		

		$updated_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id FROM $table_name 
				WHERE post_id = %d AND id != %d AND status = %s",
				$job_id,
				$row_id,
				'closed'
			),
			ARRAY_A
		);

		foreach ( $updated_rows as $row ) {
			$wpdb->insert(
				$notification_table,
				array(
					'user_id'        => $row['user_id'], 
					'job_id'         => $job_id,
					'submission_id'  => $row['id'], 
					'type'           => 'closed', 
					'viewed'         => 0,  
				)
			);
		}

		if ($job_status === 'hired') {
			$post = array(
				'ID' => $job_id,
				'post_status' => 'private',
			);
			wp_update_post($post);

			$existing_notification = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$notification_table} WHERE job_id = %d AND submission_id = %d AND type = %s",
					$job_id,
					$row_id,
					'hired'
				)
			);

			if ( $existing_notification ) {
				$wpdb->delete(
					$notification_table,
					array( 'id' => $existing_notification ),
					array( '%d' )
				);
			}

			$wpdb->insert(
				$notification_table,
				array( 
					'user_id' 		 => $user_id,
					'job_id'  		 => $job_id,
					'submission_id'  => $row_id,
					'type'   		 => 'hired',
					'viewed' 		 => 0,
				)
			);

			$user_email 		= get_the_author_meta('user_email', $user_id);
			$post_title 		= get_the_title($job_id);
			$client_name 		= get_the_author_meta('display_name', $author_id);
			$email_description 	= "We are excited to inform you that you have been selected for the job. Please feel free to reach out to your client for further discussions or check your Tradie Dashboard for updates.";
			$subject 			= 'You Are Hired!';
			$message 			= "
				<p>Congratulations! You have been hired for the job <strong>{$post_title}</strong>.</p>
				<strong>Client Name:</strong>
				<p>{$client_name}</p>
				<strong>Job Related Query:</strong>
				<p>{$email_description}</p>
				<br>
				<strong>Important Note:</strong>
				<p>To ensure you get the latest updates on your job, check in on your Need A Tradie Dashboard regularly and please remember to check your junk email folder and mark your Need A Tradie emails as safe.</p>
			";
		
			$message = str_replace('&nbsp;', ' ', $message);
			if (!empty($user_email)) {
				wp_mail($user_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
			}
		}
		
		if ($job_status === 'complete') {
			$post = array(
				'ID' => $job_id,
				'post_status' => 'private',
			);
		
			wp_update_post($post);

			$wpdb->insert($notification_table, array(
				'user_id' 		 => $user_id,
				'job_id' 		 => $job_id,
				'submission_id'  => $row_id,
				'type' 			 => 'complete',
				'viewed' 		 => 0,
			));

			// Check and replace for the user
			$exists_query = $wpdb->prepare(
				"SELECT COUNT(*) FROM $notification_table 
				WHERE user_id = %d AND job_id = %d AND submission_id = %d AND type = %s",
				$user_id,
				$job_id,
				$row_id,
				'review_pending'
			);

			// Delete the existing record, if any
			if ($wpdb->get_var($exists_query) > 0) {
					$wpdb->delete(
						$notification_table,
						array(
							'user_id' 			=> $user_id,
							'job_id'  			=> $job_id,
							'submission_id'  	=> $row_id,
							'type'    			=> 'review_pending'
						),
						array('%d', '%d', '%d', '%s')
				);
			}

			// Insert the new record
			$wpdb->insert(
				$notification_table,
				array(
					'user_id'  			=> $user_id,
					'job_id'   			=> $job_id,
					'submission_id'  	=> $row_id,
					'type'     			=> 'review_pending',
					'viewed'  			=> 0,
				),
				array('%d', '%d','%d', '%s', '%d')
			);

			// Repeat for the author
			$exists_query_author = $wpdb->prepare(
				"SELECT COUNT(*) FROM $notification_table 
				WHERE user_id = %d AND job_id = %d AND submission_id = %d AND type = %s",
				$author_id,
				$job_id,
				$row_id,
				'review_pending'
			);

			// Delete the existing record, if any
			if ($wpdb->get_var($exists_query_author) > 0) {
				$wpdb->delete(
					$notification_table,
					array(
						'user_id' 		=> $author_id,
						'job_id'  		=> $job_id,
						'submission_id' => $row_id,
						'type'    		=> 'review_pending'
					),
					array('%d', '%d','%d', '%s')
				);
			}

			// Insert the new record for the author
			$wpdb->insert(
				$notification_table,
				array(
					'user_id'  		=> $author_id,
					'job_id'   		=> $job_id,
					'submission_id' => $row_id,
					'type'     		=> 'review_pending',
					'viewed'   		=> 0,
				),
				array('%d', '%d','%d','%s', '%d')
			);


			$author_email = get_the_author_meta('user_email', $author_id);
			$post_title   = get_the_title($job_id);
			$tradesperson_name = get_the_author_meta('display_name', $user_id);

			$subject = 'Job Completion - Review Request';
			$message_to_client = "
				<p>Thank you for using Need A Tradie!</p>
				<p>Your job <strong>{$post_title}</strong> has been successfully completed by <strong>{$tradesperson_name}</strong>.</p>
				<strong>How was your experience?</strong>
				<p>Please take a moment to review the tradesperson's work and share your feedback. Your review helps others make informed decisions and supports tradespeople in building their reputation.</p>
				<br>
				<strong>Important Note:</strong>
				<p>To ensure you get the latest updates on your job, check in on your Need A Tradie Dashboard regularly and please remember to check your junk email folder and mark your Need A Tradie emails as safe.</p>
			";

			$message_to_client = str_replace('&nbsp;', ' ', $message_to_client);

			if (!empty($author_email)) {
				wp_mail($author_email, $subject, $message_to_client, array('Content-Type: text/html; charset=UTF-8'));
			}

			$user_email 	= get_the_author_meta('user_email', $user_id);
			$post_title 	= get_the_title($job_id);
			$client_name 	= get_the_author_meta('display_name', $author_id);

			$subject = 'Job Completion - Confirmation';
			$message_to_tradesperson = "
				<p>Congratulations on completing the job <strong>{$post_title}</strong>!</p>
				<strong>Client Name:</strong>
				<p>{$client_name}</p>
				<strong>What happens next?</strong>
				<p>The client has been notified of the job's completion and asked to leave a review of your work. Check your Need A Tradie Dashboard regularly for updates and new opportunities.</p>
				<br>
				<strong>Important Note:</strong>
				<p>To ensure you get the latest updates on your job, check in on your Need A Tradie Dashboard regularly and please remember to check your junk email folder and mark your Need A Tradie emails as safe.</p>
			";

			$message_to_tradesperson = str_replace('&nbsp;', ' ', $message_to_tradesperson);
			if (!empty($user_email)) {
				wp_mail($user_email, $subject, $message_to_tradesperson, array('Content-Type: text/html; charset=UTF-8'));
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
				'message' => 'Your request has been submitted successfully!',
				'status' => trim( $job_status ),
			];
			wp_send_json_success( $data );
		} else {
			wp_send_json_success( [
				'status'  => 'success',
				'message' => 'You have already hired someone. You cannot hire anyone else for this job.',
			] );
		}
	}
}