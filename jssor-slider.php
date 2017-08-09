<?php

/**
 * Jssor Slider by jssor.com
 *
 * @link              https://www.jssor.com
 * @since             1.0.0
 * @package           WP_Jssor_Slider
 *
 * @wordpress-plugin
 * Plugin Name:       jssor-slider
 * Plugin URI:        https://www.jssor.com
 * Description:       Jssor Slider is touch swipe responsive image/text slider/carousel/slideshow/gallery/banner
 * Version:           1.3.0
 * Author:            Jssor
 * Author URI:        https://profiles.wordpress.org/jssor
 * Text Domain:       jssor-slider
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

if (class_exists('WP_Jssor_Slider')) {
	die('ERROR: It looks like you already have one instance of jssor.slider installed. WordPress cannot activate and handle two instanced at the same time, you need to remove the old version first.');
}

/**
 * The global plugin class that is used to define constants and global class.
 */
if (!defined('WP_JSSOR_SLIDER_VERSION')) {
    define('WP_JSSOR_SLIDER_VERSION', '1.3.0');
}

if (!defined('WP_JSSOR_MIN_JS_VERSION')) {
    define('WP_JSSOR_MIN_JS_VERSION', '25.0.7');
}

if (!defined('WP_JSSOR_SLIDER_PATH')) {
    define('WP_JSSOR_SLIDER_PATH', plugin_dir_path(__FILE__));
}

if (!defined('WP_JSSOR_SLIDER_URL')) {
    define('WP_JSSOR_SLIDER_URL', plugin_dir_url(__FILE__));
}

if (!defined('WP_JSSOR_MEDIA_BROWSER_URL')) {
    define('WP_JSSOR_MEDIA_BROWSER_URL', '?jssor_extension=media_browser');
}

if (!defined('WP_JSSOR_SLIDER_DOMAIN')) {
    define('WP_JSSOR_SLIDER_DOMAIN', 'jssor-slider');
}

if (!defined('WP_JSSOR_SLIDER_PLUGIN_NAME')) {
    define('WP_JSSOR_SLIDER_PLUGIN_NAME', 'jssor-slider');
}

if (!defined('WP_JSSOR_SLIDER_EXTENSION_NAME')) {
    define('WP_JSSOR_SLIDER_EXTENSION_NAME', 'wp_jssor_slider');
}

require_once plugin_dir_path( __FILE__ ) . 'jssor-slider-condition.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/exceptions/FileNotFoundException.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/exceptions/WPErrorException.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/exceptions/ExtensionMissingException.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/exceptions/IllegalArgumentException.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider-globals.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/framework/class-jssor-slider-utils.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/models/class-jssor-slider-slider.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider-output.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jssor-slider-activator.php
 */
function activate_wp_jssor_slider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider-activator.php';
	WP_Jssor_Slider_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jssor-slider-deactivator.php
 */
function deactivate_wp_jssor_slider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider-deactivator.php';
	WP_Jssor_Slider_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_jssor_slider' );
register_deactivation_hook( __FILE__, 'deactivate_wp_jssor_slider' );

// check if update tables & folders in activator when the plugin is updated.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider-activator.php';
add_action('plugins_loaded', 'WP_Jssor_Slider_Activator::update');


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider.php';

function can_load_wp_jssor_slider()
{
    global $pagenow;
    if ($pagenow == 'admin-ajax.php') {
        if (isset($_POST['action'])) {
            // is heartbeat/other specified plugin, return false
            switch ($_POST['action']) {
                case 'heartbeat':
                    return false;
                default:
                    break;
            }
        }
    }
    return true;
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_jssor_slider() {

    if (!can_load_wp_jssor_slider()) {
        return false;
    }
	$plugin = new WP_Jssor_Slider();
	$plugin->run();

}
run_wp_jssor_slider();

include(plugin_dir_path( __FILE__ ) . 'custom/jssor-slider-custom.php');
//include(plugin_dir_path( __FILE__ ) . 'custom/jssor-slider-crons.php');

include(plugin_dir_path( __FILE__ ) . 'json-api/json-api.php');


if(!is_admin()){ //if not admin part
		/**
		 *
		 * put jssor slider on the page.
		 * the data can be slider ID or slider alias.
		 */
		function putJssorSlider($id_or_alias, $show_on_pages = ""){
            $attrs = array(
                'show_on_pages' => $show_on_pages
            );
            if (is_numeric($id_or_alias)) {
                $attrs['id'] = $id_or_alias;
            } else {
                $attrs['alias'] = $id_or_alias;
            }
            $output = new WP_Jssor_Slider_Output($attrs);
            echo $output->get_slider();
        }
}
