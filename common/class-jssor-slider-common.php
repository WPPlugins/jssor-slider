<?php

/**
 * The common functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/admin
 * @author     Your Name <email@example.com>
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WP_Jssor_Slider_Common {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wp_jssor_slider    The ID of this plugin.
	 */
	private $wp_jssor_slider;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wp_jssor_slider       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wp_jssor_slider, $version ) {

		$this->wp_jssor_slider = $wp_jssor_slider;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Jssor_Slider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Jssor_Slider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        global $wp_jssor_slider_admin_page;
        //$current = get_current_screen();
        //if ($current->id != $wp_jssor_slider_admin_page) {
        if ($hook != $wp_jssor_slider_admin_page) {
            return;
        }
        wp_enqueue_style( $this->wp_jssor_slider, plugin_dir_url( __FILE__ ) . 'css/blocker.css', array(), $this->version, 'all' );
        //wp_enqueue_style( $this->wp_jssor_slider, plugin_dir_url( __FILE__ ) . 'pages/content/slideo.editor/css/slideo.editor-1.11.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Jssor_Slider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Jssor_Slider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        global $wp_jssor_slider_admin_page;
        //$current = get_current_screen();
        if ($hook != $wp_jssor_slider_admin_page) {
            return;
        }
        wp_enqueue_script( $this->wp_jssor_slider, plugin_dir_url( __FILE__ ) . 'js/jssor-slider-common.js', array(), $this->version, false );
        wp_enqueue_script( $this->wp_jssor_slider . '_blocker', plugin_dir_url( __FILE__ ) . 'js/blocker.js', array('jquery'), $this->version, false );

	}

}
