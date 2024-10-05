<?php
/**
 * All Shortcode related functions
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
 * @subpackage Shortcode
 * @author Codexpert <hi@codexpert.io>
 */
class Shortcode extends Base {

    public $plugin;

    /**
     * Constructor function
     */
    public function __construct( $plugin ) {
        $this->plugin   = $plugin;
        $this->slug     = $this->plugin['TextDomain'];
        $this->name     = $this->plugin['Name'];
        $this->version  = $this->plugin['Version'];
    }

    public function job_submissions() {
        global $wpdb;
        
        $current_user_id = get_current_user_id();
    
        if ( $current_user_id == 0 ) {
            return '<p>You must be logged in to view your job submissions.</p>';
        }
    
        $table_name = $wpdb->prefix . 'trade_job_submission';
        $query = "
            SELECT submissions.*
            FROM $table_name as submissions
            INNER JOIN {$wpdb->posts} as posts
            ON submissions.post_id = posts.ID
            WHERE posts.post_author = %d
            ORDER BY submissions.created_at DESC
        ";
        
        $results = $wpdb->get_results( $wpdb->prepare( $query, $current_user_id ) );
    
        if ( empty( $results ) ) {
            return '<p>No Applications found .</p>';
        }
    
        // Start building the HTML table
        $output = '<table border="1" cellpadding="10" cellspacing="0">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Job ID</th>';
        $output .= '<th>Name</th>';
        $output .= '<th>Tradesman Email</th>';
        $output .= '<th>Subject</th>';
        $output .= '<th>Field</th>';
        $output .= '<th>Sub Field</th>';
        $output .= '<th>Message</th>';
        $output .= '<th>Status</th>';
        $output .= '<th>Created At</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
    
        // Loop through each submission and add a row to the table
        foreach ( $results as $row ) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html( $row->post_id ) . '</td>';
            $output .= '<td>' . esc_html( $row->name ) . '</td>';
            $output .= '<td>' . esc_html( $row->tradesman_email ) . '</td>';
            $output .= '<td>' . esc_html( $row->subject ) . '</td>';
            $output .= '<td>' . esc_html( $row->field ) . '</td>';
            $output .= '<td>' . esc_html( $row->sub_field ) . '</td>';
            $output .= '<td>' . esc_html( $row->message ) . '</td>';
            $output .= '<td>' . esc_html( $row->status ) . '</td>';
            $output .= '<td>' . esc_html( $row->created_at ) . '</td>';
            $output .= '</tr>';
        }
    
        $output .= '</tbody>';
        $output .= '</table>';
    
        return $output;
    }
    
}