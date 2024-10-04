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

		$name = sanitize_text_field($_POST['name']);
		$tradesman_email = sanitize_email($_POST['tradesman_Email']);
		$subject = sanitize_text_field($_POST['subject']);
		$field = sanitize_text_field($_POST['Field']);
		$sub_field = sanitize_text_field($_POST['Sub-field']);
		$message = sanitize_textarea_field($_POST['email_description']);
		$post_id = intval($_POST['post_id']);
	
		$table_name = $wpdb->prefix . 'trade_job_submission';
	
		$inserted = $wpdb->insert(
			$table_name,
			[
				'post_id'         => $post_id,
				'name'            => $name,
				'tradesman_email' => $tradesman_email,
				'subject'         => $subject,
				'field'           => $field,
				'sub_field'       => $sub_field,
				'message'         => $message,
				'created_at'      => current_time('mysql') ,
				'status'			=> "pending"
			],
			[
				'%d', // post_id
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

}