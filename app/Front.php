<?php
/**
 * All public facing functions
 */
namespace WPPlugines\Restrict_Reviews\App;
use Codexpert\Plugin\Base;
use WPPlugines\Restrict_Reviews\Helper;
/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author Codexpert <hi@codexpert.io>
 */
class Front extends Base {

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

	public function head() {}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'WPPRR_DEBUG' ) && WPPRR_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", WPPRR ), '', time(), 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", WPPRR ), [ 'jquery' ], time(), true );
		
		$unviewed_count = $this->count_unviewed_jobs();

		$localized = [
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'_wpnonce'      => wp_create_nonce(),
			'unviewedCount' => $unviewed_count, 
		];
		wp_localize_script( $this->slug, 'WPPRR', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function count_unviewed_jobs() {
		global $wpdb;
	
		// Get the current user ID
		$current_user_id = get_current_user_id();
	
		// Table name
		$table_name = $wpdb->prefix . 'trade_job_submission';
	
		// Prepare the query to count unviewed jobs
		$query = $wpdb->prepare(
			"SELECT COUNT(*) 
			 FROM $table_name 
			 WHERE viewed = %d 
			 AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_author = %d)",
			0, // looking for unviewed jobs
			$current_user_id
		);
	
		// Execute the query and return the count
		return $wpdb->get_var($query);
	}
	

	public function modal() {
		echo '
		<div id="plugin-client-modal" style="display: none">
			<img id="plugin-client-modal-loader" src="' . esc_attr( WPPRR_ASSET . '/img/loader.gif' ) . '" />
		</div>';
	}
}