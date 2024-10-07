<?php
/**
 * All Shortcode related functions
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
        return Helper::get_template( 'applications', 'views/tabs' );
    }

    public function my_feedbacks() {
        return Helper::get_template( 'my-feedbacks', 'views/tabs' );
    }

    public function feedbacks_received() {
        return Helper::get_template( 'feedbacks-received', 'views/tabs' );
    }
}
