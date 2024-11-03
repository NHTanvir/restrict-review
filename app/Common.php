<?php
/**
 * All common functions to load in both admin and front
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
 * @subpackage Common
 * @author Codexpert <hi@codexpert.io>
 */
class Common extends Base {

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

	public function register_custom_conditions( $conditions_manager ) {
		$conditions_manager->register_condition( 'WPPlugines\\Restrict_Reviews\\App\\Conditions\\User_Specific_Review' );
	}

	public function my_custom_user_registration_action($user_id) {
		$user_info = get_userdata($user_id);
		
		$post_data = array(
			'post_title'   => $user_info->user_login,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'users',
		);
	
		$post_id = wp_insert_post($post_data);
	
		if ($post_id) {
			update_post_meta($post_id, 'user_id', $user_id);
			update_post_meta($post_id, 'email', $user_info->user_email);
			update_post_meta($post_id, 'tr_user_name', $user_info->user_login);
			$roles = $user_info->roles;
			if (!empty($roles)) {
				update_post_meta($post_id, 'user_role', $roles[0]);
			}
		}
	}	
}