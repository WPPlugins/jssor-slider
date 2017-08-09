<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 * @author     Your Name <email@example.com>
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WP_Jssor_Slider {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Jssor_Slider_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $wp_jssor_slider    The string used to uniquely identify this plugin.
	 */
	protected $wp_jssor_slider;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->wp_jssor_slider = 'jssor-slider';
		$this->version = WP_JSSOR_SLIDER_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

        //common features hasn't been implemented yet
		//$this->define_common_hooks();
		//$this->define_public_hooks();

        $this->init_update();
        $this->setup_shortcode();

	}

    /**
     * undocumented function
     *
     * @return void
     */
    private function init_update()
    {
        if (!is_admin()) {
            return false;
        }
		//add common scripts there
        $arg = array(
            'version' => $this->get_version(),
        );

        $upgrade = new WP_Jssor_Slider_Update($arg);
        if (empty($_GET['checkforupdates'])) {
            $force_check = false;
        } else {
            $force_check = true;
            $upgrade->check_version_info($force_check);
        }

		//$validated = get_option('wjssl-valid', 'false');
		$current_version = $this->get_version();
		$latestv = get_option('wjssl-latest-version', '0');

        if(empty($latestv) || version_compare($current_version, $latestv, '<')) {
            $upgrade->add_update_check();
        }
        $this->add_check_updates_notice();
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function contact_remote_server_notice()
    {
        if (get_option('wjssl-connection')) {
            $this->show_notice(__('Contact www.jssor.com successfully.', WP_JSSOR_SLIDER_DOMAIN), 'success');
        } else {
            $this->show_notice(__('Failed to contact www.jssor.com', WP_JSSOR_SLIDER_DOMAIN), 'error');
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function check_updates_version_notice()
    {
        $latestv = get_option('wjssl-latest-version');
        $message = __('The latest available version is %s.', WP_JSSOR_SLIDER_DOMAIN);
        $message = sprintf($message, $latestv);
        $this->show_notice($message, 'success');
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function show_notice($message, $status = 'success')
    {
        $class = 'notice';

        $class .= ' notice-' . $status;
        $class .= ' is-dismissible wjssl-updates-notice';

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }


    /**
     * undocumented function
     *
     * @return void
     */
    private function add_check_updates_notice()
    {
        if (empty($_GET['page']) || $_GET['page']!= 'jssor-slider-admin-menu') {
            return false;
        }
        if (empty($_GET['checkforupdates']) || $_GET['checkforupdates']!= 'true') {
            return false;
        }
        if (!empty($_GET['checkremoteserver']) && $_GET['checkremoteserver'] == 'true') {
            add_action( 'admin_notices', array($this, 'contact_remote_server_notice'));
            return ;
        }
        add_action( 'admin_notices', array($this, 'check_updates_version_notice'));
    }


    /**
     * register shortcode for plugin
     *
     * @return void
     */
    private function setup_shortcode()
    {
        add_shortcode('jssor-slider', array($this, 'register_shortcode'));
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function register_shortcode($attrs)
    {
        $output = new WP_Jssor_Slider_Output($attrs);
        return $output->get_slider();
    }


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Jssor_Slider_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Jssor_Slider_i18n. Defines internationalization functionality.
	 * - WP_Jssor_Slider_Admin. Defines all hooks for the admin area.
	 * - WP_Jssor_Slider_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jssor-slider-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jssor-slider-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-jssor-slider-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-jssor-slider-public.php';

		/**
		 * The class responsible for defining all actions that occur in the common-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/class-jssor-slider-common.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jssor-slider-update.php';

		$this->loader = new WP_Jssor_Slider_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Jssor_Slider_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Jssor_Slider_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

        if (!is_admin()) {
            return false;
        }

		$plugin_admin = new WP_Jssor_Slider_Admin( $this->get_wp_jssor_slider(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu');

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_wjssl_add_new_slider', $plugin_admin, 'save_update_slider' );
		$this->loader->add_action( 'wp_ajax_wjssl_delete_slider', $plugin_admin, 'delete_slider' );
		$this->loader->add_action( 'wp_ajax_wjssl_activate_plugin', $plugin_admin, 'activate_plugin' );
		$this->loader->add_action( 'wp_ajax_wjssl_deactivate_plugin', $plugin_admin, 'deactivate_plugin' );
		$this->loader->add_action( 'wp_ajax_wjssl_duplicate_slider', $plugin_admin, 'duplicate_slider' );

		$this->loader->add_action( 'wp_ajax_wjssl_check_for_updates', $plugin_admin, 'check_for_updates' );
		$this->loader->add_action( 'wp_ajax_wjssl_connect_jssor_com_server', $plugin_admin, 'connect_jssor_com_server' );

		$this->loader->add_action( 'wp_ajax_wjssl_check_status', $plugin_admin, 'check_status' );
	}

	/**
     * Register all of the hooks related to the common area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_common_hooks() {

		$plugin_admin = new WP_Jssor_Slider_Common( $this->get_wp_jssor_slider() . '-common', $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

        if (is_admin()) {
            return false;
        }
		$plugin_public = new WP_Jssor_Slider_Public( $this->get_wp_jssor_slider(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_wp_jssor_slider() {
		return $this->wp_jssor_slider;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WP_Jssor_Slider_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
