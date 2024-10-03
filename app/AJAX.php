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
		$name = sanitize_text_field($_POST['name']);
		$tradesman_email = sanitize_email($_POST['tradesman_Email']);
		$subject = sanitize_text_field($_POST['subject']);
		$field = sanitize_text_field($_POST['Field']);
		$sub_field = sanitize_text_field($_POST['Sub-field']);
		$message = sanitize_textarea_field($_POST['message']);
	
		// Insert data into the database
		$table_name = $wpdb->prefix . 'trade_job_submission';
		$result = $wpdb->insert($table_name, array(
			'name' => $name,
			'tradesman_email' => $tradesman_email,
			'subject' => $subject,
			'field' => $field,
			'sub_field' => $sub_field,
			'message' => $message,
		));
	
		if ($result) {
			wp_send_json_success('Data inserted successfully!');
		} else {
			wp_send_json_error('Data insertion failed.');
		}
	
		wp_die(); 
	}

}