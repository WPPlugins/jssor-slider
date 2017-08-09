<?php

/**
 * The admin-specific functionality of the plugin.
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

class WP_Jssor_Slider_Admin {

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
        wp_enqueue_style( $this->wp_jssor_slider.'new', plugin_dir_url( __FILE__ ) . 'css/jssor-slider-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'jssor-slideo-eidtor-css', WP_JSSOR_SLIDER_URL . 'public/content/slideo.editor/css/slideo.editor.min.css', array(), $this->version, 'all' );

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

        $admin_main_script_name = 'jssor-slider-admin-init-script';
        WP_Jssor_Slider_Condition::enqueue_admin_init_script();

        $title_nonce = wp_create_nonce( 'wjssl-add-slider' );
        wp_localize_script($admin_main_script_name, 'wjssl_ajax_new_slider_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $title_nonce,
            'action' => 'wjssl_add_new_slider',
            'delete_action' => 'wjssl_delete_slider',
            'duplicate_action' => 'wjssl_duplicate_slider',
        ) );

        $nonce = wp_create_nonce('wjssl-purchase');
        wp_localize_script($admin_main_script_name, 'wjssl_ajax_purchase_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $nonce,
            'activate_action' => 'wjssl_activate_plugin',
            'deactivate_action' => 'wjssl_deactivate_plugin',
        ) );

        $nonce = wp_create_nonce('wjssl-update');
        wp_localize_script($admin_main_script_name, 'wjssl_ajax_update_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $nonce,
            'update_now_action' => 'wjssl_update_now',
            'check_for_updates_action' => 'wjssl_check_for_updates',
        ) );

        $nonce = wp_create_nonce('wjssl-requirements');
        wp_localize_script($admin_main_script_name, 'wjssl_ajax_requirements_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $nonce,
            'connect_jssor_com_server_action' => 'wjssl_connect_jssor_com_server',
        ) );

        $nonce = wp_create_nonce('wjssl-status');
        wp_localize_script($admin_main_script_name, 'wjssl_ajax_status_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $nonce,
            'check_status' => 'wjssl_check_status',
        ) );

        wp_localize_script($admin_main_script_name, 'wjssl_slider_nonces_obj', array(
            'preview'    => wp_create_nonce('wjssl-preview'),
            'import'    => wp_create_nonce('wjssl-import'),
            'retrieve'    => wp_create_nonce('wjssl-retrieve'),
            'save'    => wp_create_nonce('wjssl-save'),
        ) );
		if(function_exists("wp_enqueue_media"))
			wp_enqueue_media();
	}

    /**
     * Register the admin menu for the admin menu panel
     *
     * @return void
     */
    public function add_admin_menu()
    {
        global $wp_jssor_slider_admin_page;
        $page_title = 'Admin Dashboard - Jssor Slider WordPress Plugin';
        $wp_jssor_slider_admin_page = add_menu_page(
            $page_title,
            'Jssor Slider',
            'manage_options',
            'jssor-slider-admin-menu',
            array($this, 'show_editor_page'),
            WP_JSSOR_SLIDER_URL . 'admin/images/jssor-icon-16.png'
        );
        // add_submenu_page(
            //'jssor-slider-admin-menu',
            //$page_title,
            //'Editor',
            //'manage_options',
            //'jssor-slider-admin-editor-menu',
            //array($this, 'show_editor_page')
        //);

        add_action('admin_head-' . $wp_jssor_slider_admin_page , array($this, 'add_admin_header'));
    }

    /**
     * show admin editor page for jssor slider
     *
     * @return void
     */
    public function show_editor_page()
    {
        //include plugin_dir_path(__FILE__) . 'pages/editor.php';
        include plugin_dir_path(__FILE__) . 'partials/jssor-slider-admin-display.php';
    }


    /**
     * Register admin header function for add some meta tags.
     *
     * @return void
     */
    public function add_admin_header()
    {
        //echo '<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>';
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function delete_slider()
    {
        check_ajax_referer('wjssl-add-slider');
        $this->ajax_check_permissions();

		// Slider data
        $slider_id = empty($_POST['slider_id']) ? 0 : intval($_POST['slider_id']);
        $slider_id = empty($slider_id) ? 0 : $slider_id;

        if (empty($slider_id)) {
            wp_send_json_error(array('message' => __('The request is invalid.', WP_JSSOR_SLIDER_DOMAIN)));
        }

        $sliderModel = new WP_Jssor_Slider_Slider(array(
            'id' => $slider_id,
        ));
        $status = $sliderModel->delete();
        if ($status === false) {
            wp_send_json_error(array('message' => $wpdb->last_error));
        }

        wp_send_json_success(array('slider_id' => $slider_id, 'message' => __('The slider[%s] is deleted.', WP_JSSOR_SLIDER_DOMAIN)));
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public function save_update_slider()
    {
        check_ajax_referer('wjssl-add-slider');
        $this->ajax_check_permissions();

        global $wpdb;

		// Slider data
        $title = sanitize_file_name($_POST['slider_name']);
        if (empty($_POST['slider_id'])) {
            $slider_id = 0;
        } else {
            $slider_id = intval($_POST['slider_id']);
        }
        $slider_id = empty($slider_id) ? 0 : $slider_id;

        if (empty($title)) {
            wp_send_json_error(array('message' => __('Please input slider name', WP_JSSOR_SLIDER_DOMAIN)));
        }

        $sliderModel = new WP_Jssor_Slider_Slider(array(
            'id' => $slider_id,
            'file_name' => $title
        ));

        // set the meta_key to the appropriate custom field meta key
        $existed = $sliderModel->is_name_existed();
        if ($existed) {
            wp_send_json_error(array('message' => __('The slider exists already!', WP_JSSOR_SLIDER_DOMAIN)));
        }

        $title = $sliderModel->data['file_name'];
        $is_create_action = empty($slider_id);
        if (empty($slider_id)) {

            $data = array(
                'file_path' => WP_Jssor_Slider_Globals::get_jssor_template_path(),
            );
            $result_data = array(
                'message' => sprintf(__('The slider[%s] created!', WP_JSSOR_SLIDER_DOMAIN), $title),
            );
        } else {
            $data = array();
            $result_data = array(
                'message' => sprintf(__('The slider[%s] updated!', WP_JSSOR_SLIDER_DOMAIN), $title),
            );
        }

        $status = $sliderModel->save($data);
        // Return insert database ID
        $slider_id = $sliderModel->id;

        if ($status === false) {
            wp_send_json_error(array('message' => $sliderModel->last_error()));
        }

        if ($is_create_action) {
            $upload = wp_upload_dir();
            $template_path = $upload['basedir'] . WP_Jssor_Slider_Globals::get_jssor_template_path();
            $slider_content = file_get_contents($template_path);

            require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';
            $slider_content = WP_Jssor_Resource_Utils::add_import_tag_for_slider($slider_content);

            $slider_path = WP_Jssor_Slider_Globals::slider_path($slider_id);
            file_put_contents($slider_path['path'], $slider_content);
            $sliderModel->save(array(
                'file_path' => $slider_path['rel_path']
            ));
        }

        $slider_edit_url = WP_Jssor_Slider_Globals::get_jssor_edit_slider_url($slider_id, $title);
        $slider_preview_url = WP_Jssor_Slider_Globals::get_jssor_preview_slider_url($slider_id, $title);
        // fetch thumb url
        $sliderModel->find($slider_id);

        wp_send_json_success(array_merge(array(
            'slider_id' => $slider_id,
            'slider_name' => $title,
            'shortcode' => WP_Jssor_Slider_Globals::get_shortcode_templ($slider_id),
            'edit_url' => $slider_edit_url,
            'grid_thumb_url' => $sliderModel->grid_thumb_url(),
            'list_thumb_url' => $sliderModel->list_thumb_url(),
            'preview_url' => $slider_preview_url
        ), $result_data));
    }

    /**
     * activate plugin
     *
     * @return void
     */
    public function activate_plugin()
    {
        check_ajax_referer('wjssl-purchase');
        $this->ajax_check_permissions();

        $purchase_code = sanitize_text_field($_POST['purchase_code']);
        if (empty($purchase_code)) {
            wp_send_json_error(array(
                'message' => __('The purchase code is empty.', WP_JSSOR_SLIDER_DOMAIN)
            ));
        }
        $last_time = get_option('wjssl_activate_request_time', 0);
        update_option('wjssl_activate_request_time', time());
        // last request should be 6sec ago
        $time_elapsed = time() - $last_time;
        if($time_elapsed < 6) {
            sleep(6 - $time_elapsed);
        }
        //if ((time() - $last_time) < 6) {
        //    wp_send_json_error(array(
        //        'message' => __('The time interval of the activation request should be more than 6 seconds.', WP_JSSOR_SLIDER_DOMAIN)
        //    ));
        //}
        $url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE() . WP_Jssor_Slider_Globals::URL_JSSOR_ACTIVATE;

        $instance_id = get_option('wp_jssor_slider_instance_id', '');
        $data = array(
            'jssorext' => WP_JSSOR_SLIDER_EXTENSION_NAME,
            'hosturl' => esc_url_raw(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url()),
            'instid' => $instance_id,
            'purchcode' => $purchase_code,
        );

        $response = wp_remote_post(esc_url_raw($url), array(
            'body' => array(
                'data' => json_encode($data)
            ),
            'timeout' => 30
        ));

        update_option('wjssl_activate_request_time', time());
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }

        $return = json_decode($response['body'], true);
        if (empty($return['error'])) {
            // code...
            if (!empty($return['newinst'])) {
                update_option('wp_jssor_slider_instance_id', WP_Jssor_Slider_Utils::create_guid());
                wp_send_json_error(array(
                    'message' => 'This is a copy of another instance, a new instance id has been generated, please re-active this plugin.'
                ));
            }
            update_option('wjssl_actcode', $return['actcode']);
            update_option('wjssl_acckey', $return['acckey']);
            update_option('wjssl_purchcode', $purchase_code);

            WP_Jssor_Slider_Slider::clear_all_code_html_path();

            //update_option('wjssl-valid', 'true');

            wp_send_json_success(array(
                'actcode' => $return['actcode'],
                'message' => __('Purchase Code Successfully Activated.', WP_JSSOR_SLIDER_DOMAIN)
            ));
        }

        wp_send_json_error(array(
            'message' => $return['message']
        ));
    }

    /**
     * deactivate plugin
     *
     * @return void
     */
    public function deactivate_plugin()
    {
        check_ajax_referer('wjssl-purchase');
        $this->ajax_check_permissions();

        $url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE() . WP_Jssor_Slider_Globals::URL_JSSOR_DEACTIVATE;
        $instance_id = get_option('wp_jssor_slider_instance_id', '');
        $purchcode = get_option('wjssl_purchcode', '');
        $acckey = get_option('wjssl_acckey', '');

        $data = array(
            'jssorext' => WP_JSSOR_SLIDER_EXTENSION_NAME,
            'hosturl' => esc_url_raw(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url()),
            'instid' => $instance_id,
            'purchcode' => $purchcode,
            'acckey' => $acckey
        );

        $response = wp_remote_post(esc_url_raw($url), array(
            'body' => array(
                'data' => json_encode($data)
            ),
            'timeout' => 30
        ));
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }

        $return = json_decode($response['body'], true);
        if (empty($return['error'])) {
            if (!empty($return['newinst'])) {
                update_option('wp_jssor_slider_instance_id', WP_Jssor_Slider_Utils::create_guid());
            }
            delete_option('wjssl_actcode');
            delete_option('wjssl_acckey');
            delete_option('wjssl_purchcode');
            //delete_option('wjssl-valid');
            //
            WP_Jssor_Slider_Slider::clear_all_code_html_path();

            wp_send_json_success(array('message' => __('Successfully removed validation.', WP_JSSOR_SLIDER_DOMAIN)));
        }

        wp_send_json_error(array('message' => $return['message']));
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function duplicate_slider()
    {
        check_ajax_referer('wjssl-add-slider');
        $this->ajax_check_permissions();

        $slider_id = empty($_POST['slider_id']) ? 0 : intval($_POST['slider_id']);
        $new_slider_name = empty($_POST['new_slider_name']) ? '' : sanitize_file_name($_POST['new_slider_name']);

        if (empty($slider_id)) {
            wp_send_json_error(array('message' => __("The request is invalid.", WP_JSSOR_SLIDER_DOMAIN)));
        }
        if (empty($new_slider_name)) {
            wp_send_json_error(array('message' => __("Please input slider name.", WP_JSSOR_SLIDER_DOMAIN)));
        }

        $sliderModel = new WP_Jssor_Slider_Slider();
        $data = $sliderModel->find($slider_id);
        $sliderModel->clear_data();

        $code_path = $data['code_path'];
        $html_path = $data['html_path'];
        $file_path = $data['file_path'];

        unset($data['id']);
        unset($data['created_at']);
        unset($data['modified_at']);
        unset($data['code_path']);
        unset($data['html_path']);
        unset($data['file_path']);
        $data['file_name'] = $new_slider_name;

        $sliderModel->set_data($data);
        if ($sliderModel->is_name_existed()) {
            $error = __('The %s exists already, please specify another name', WP_JSSOR_SLIDER_DOMAIN);
            wp_send_json_error(array('message' => wp_sprintf($error, $sliderModel->data_value('file_name'))));
            return;
        }

        if($sliderModel->save()) {
            $new_id = $sliderModel->id;

            $new_data = array();
            $new_data['code_path'] = $this->copy_slider_path($code_path, $new_id);
            $new_data['html_path'] = $this->copy_slider_path($html_path, $new_id);
            $new_data['file_path'] = $this->copy_slider_path($file_path, $new_id);
            $sliderModel->save($new_data);

            $new_title = $sliderModel->data_value('file_name');
            $slider_edit_url = WP_Jssor_Slider_Globals::get_jssor_edit_slider_url($new_id, $new_title);
            $slider_preview_url = WP_Jssor_Slider_Globals::get_jssor_preview_slider_url($new_id, $new_title);

            $upload = wp_upload_dir();
            $grid_filepath = $sliderModel->data_value('grid_thumb_path');
            $list_filepath = $sliderModel->data_value('list_thumb_path');

            if (!empty($grid_filepath)) {
                $grid_filepath = $upload['baseurl'] . $grid_filepath;
            }
            if (!empty($list_filepath)) {
                $list_filepath = $upload['baseurl'] . $list_filepath;
            }

            wp_send_json_success(
                array(
                'slider_id' => $new_id,
                'slider_name' => $new_title,
                'shortcode' => WP_Jssor_Slider_Globals::get_shortcode_templ($new_id, $new_title),
                'edit_url' => $slider_edit_url,
                'grid_thumb_url' => $grid_filepath,
                'list_thumb_url' => $list_filepath,
                'preview_url' => $slider_preview_url
                )
            );
        } else {
            wp_send_json_error(array('message' => $sliderModel->last_error()));
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function copy_slider_path($src_path, $id)
    {
        if (empty($src_path) || empty($id)) {
            return '';
        }
        $upload = wp_upload_dir();
        $abs_src_path = $upload['basedir'] . $src_path;
        $extension = pathinfo($src_path, PATHINFO_EXTENSION);

        $dst_dir = dirname($src_path);
        if (WP_Jssor_Slider_Globals::is_jssor_template_path($dst_dir)) {
            $path = WP_Jssor_Slider_Globals::upload_dir(WP_Jssor_Slider_Globals::UPLOAD_SLIDER);
            $dst_dir = WP_Jssor_Slider_Globals::UPLOAD_SLIDER . $path['subdir'];
        }
        $dst_path = $dst_dir . '/' . $id . '.' . $extension;

        $abs_dst_path = $upload['basedir'] . $dst_path;
        @copy($abs_src_path, $abs_dst_path);

        return $dst_path;
    }

    /**
     * check for updates
     */
    public function check_for_updates()
    {
        check_ajax_referer('wjssl-update');
        $this->ajax_check_permissions();

        $jssor_slider_update = new WP_Jssor_Slider_Update();
        $force_check = true;
        if (isset($_POST['noforce'])
            &&
            (!empty($_POST['noforce']))
        ) {
            $force_check = false;
        }

        try {
		    $jssor_slider_update->check_version_info($force_check);

            if($jssor_slider_update->has_error()) {
                return wp_send_json_error(array(
                    'message' => $jssor_slider_update->get_error_message()
                ));
            }
        }
        catch(Exception $e) {
            return wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }

        wp_send_json_success(WP_Jssor_Slider_Globals::get_jssor_wordpress_updates_info());
    }

    /**
     * connect jssor.com server
     */
    public function connect_jssor_com_server ()
    {
        check_ajax_referer('wjssl-requirements');
        $this->ajax_check_permissions();

        $upgrade = new WP_Jssor_Slider_Update();

        try {
		    $upgrade->check_version_info(true);
        }
        catch(Exception $e) {
            return wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }

        $can_connect = get_option('wjssl-connection', false);
        wp_send_json_success(array(
            'can_connect' => $can_connect
        ));
    }

    public function check_status()
    {
        check_ajax_referer('wjssl-status');
        $this->ajax_check_permissions();

        $jssor_slider_update = new WP_Jssor_Slider_Update();
        $force_check = true;
        if (isset($_POST['noforce'])
            &&
            (!empty($_POST['noforce']))
        ) {
            $force_check = false;
        }

        try {
		    $jssor_slider_update->check_version_info($force_check);

            //if($jssor_slider_update->has_error()) {
            //    return wp_send_json_error(array(
            //        'message' => $jssor_slider_update->get_error_message()
            //    ));
            //}
        }
        catch(Exception $e) {
            return wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }

        $status = WP_Jssor_Slider_Globals::get_jssor_wordpress_status_info();

        if($jssor_slider_update->has_error()) {
            $status['error'] = 1;
            $status['message'] = $jssor_slider_update->get_error_message();
        }

        wp_send_json_success($status);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function check_permissions()
    {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function ajax_check_permissions()
    {
        if ($this->check_permissions()) {
            return true;
        }
        return wp_send_json_error(array(
            'message' => __("Permision Denied!", WP_JSSOR_SLIDER_DOMAIN)
        ));
    }

}
