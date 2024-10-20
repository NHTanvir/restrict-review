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
		global $wpdb;
		$unreviewed_jobs  		= 0;
		$table_name 			= $wpdb->prefix . 'trade_job_submission';
		$user_id 				= get_current_user_id();
		if ( current_user_can( 'tradesman' ) ) {
			$unreviewed_jobs_query = $wpdb->prepare(
				"SELECT post_id 
				 FROM $table_name 
				 WHERE user_id = %d 
				 AND status = %s 
				 AND user_review = %d", 
				$user_id,
				'complete',
				0 
			);
			$unreviewed_jobs = $wpdb->get_col($unreviewed_jobs_query);
		}
		if ( current_user_can( 'client' ) ) {
			$unreviewed_jobs_query = $wpdb->prepare(
				"SELECT post_id 
				 FROM $table_name 
				 WHERE author_id = %d 
				 AND status = %s 
				 AND author_review = %d", 
				$user_id,
				'complete',
				0 
			);
			$unreviewed_jobs = $wpdb->get_col($unreviewed_jobs_query);
		}
		
		$min = defined( 'WPPRR_DEBUG' ) && WPPRR_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", WPPRR ), '', time(), 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", WPPRR ), [ 'jquery' ], time(), true );
		
		$unviewed_count = $this->count_unviewed_jobs();

		$localized = [
			'ajaxurl'       	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'      	=> wp_create_nonce(),
			'unviewedCount' 	=> $unviewed_count,
			'unreviewedJobs' 	=> $unreviewed_jobs, 
		];
		wp_localize_script( $this->slug, 'WPPRR', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function count_unviewed_jobs() {
		global $wpdb;
		$current_user_id = get_current_user_id();
		$table_name = $wpdb->prefix . 'trade_job_submission';
	
		$query = $wpdb->prepare(
			"SELECT COUNT(*)
			 FROM $table_name
			 WHERE viewed = %d 
			 AND author_id = %d",
			0, 
			$current_user_id 
		);
	
		return $wpdb->get_var($query);
	}
	

	public function modal() {
		echo '
		<div id="plugin-client-modal" style="display: none">
			<img id="plugin-client-modal-loader" src="' . esc_attr( WPPRR_ASSET . '/img/loader.gif' ) . '" />
		</div>';
	}
}