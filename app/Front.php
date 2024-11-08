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
		$unviewed_hires 		= 0;
		$unviewed_completions	= 0;
		$is_client 				= 0;
		$table_name 			= $wpdb->prefix . 'trade_job_submission';
		$user_id 				= get_current_user_id();
		$current_user 			= wp_get_current_user();
		$required_roles 		= array( 'tradesman', '1-day-membership', '1-week-plan', '1-month-plan' );
		if ( array_intersect( $required_roles, $current_user->roles ) ) {
			// Proceed with the query
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
			$unreviewed_jobs 		= $wpdb->get_col($unreviewed_jobs_query);
			$unviewed_hires		 	= $this->count_unviewed_notifications_by_type('hired');
			$unviewed_completions 	= $this->count_unviewed_notifications_by_type('completed');
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
			$is_client =1;
		}


		$unviewed_feedback 		= $this->count_unviewed_notifications_by_type('feedback_received');
				
		$min = defined( 'WPPRR_DEBUG' ) && WPPRR_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", WPPRR ), '', time(), 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", WPPRR ), [ 'jquery' ], time(), true );
		
		$unviewed_count = $this->count_unviewed_jobs();

		$localized = [
			'ajaxurl'       			=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'      			=> wp_create_nonce(),
			'unviewedCount' 			=> $unviewed_count,
			'unreviewedJobs' 			=> $unreviewed_jobs, 
			'unviewedHiresComplete'     => $unviewed_hires + $unviewed_completions,
			'unviewedFeedback'   		=> $unviewed_feedback,
			'is_client'					=> $is_client
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
	
	public function count_unviewed_notifications_by_type($type) {
		global $wpdb;
		$current_user_id = get_current_user_id();
		$table_name = $wpdb->prefix . 'trade_notifications';
	
		$query = $wpdb->prepare(
			"SELECT COUNT(*)
			 FROM $table_name
			 WHERE viewed = %d
			 AND user_id = %d
			 AND type = %s",
			0,
			$current_user_id,
			$type
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