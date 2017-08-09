<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 */

/**
 * @ignore
 */
if( !defined( 'ABSPATH') ) exit();


/**
 * define constants of plugin
 *
 * This class defines all constants and global variables
 *
 * @since      1.0.0
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 * @author     Your Name <email@example.com>
 */

class WP_Jssor_Slider_Globals
{
    const TABLE_SLIDERS = 'jssor_slider_sliders';
    //const TABLE_SLIDES = 'jssor_slider_slides';

    const DIR_RESOURCES = '/resources';
    const DIR_RESOURCES_UPLOAD = '/resources/upload';
    const DIR_RESOURCES_TEMPLATE = '/resources/upload/template';
    const DIR_RESOURCES_THEME = '/resources/upload/theme';
    const DIR_RESOURCES_SCRIPT = '/resources/upload/script';
    const DIR_CUSTOM_IMPORT= '/custom/import';

    const UPLOAD_DIR = '/jssor-slider';
    const UPLOAD_SLIDER = '/jssor-slider/slider';
    const UPLOAD_TEMPLATE = '/jssor-slider/jssor.com/template';
    const UPLOAD_THEME = '/jssor-slider/jssor.com/theme';
    const UPLOAD_GENCODES = '/jssor-slider/gencodes';
    const UPLOAD_SCRIPTS = '/jssor-slider/jssor.com/script';
    const UPLOAD_GENSLIDER_HTML = '/jssor-slider/genslider_html';
    const UPLOAD_THUMB = '/jssor-slider/thumbnails';
    const UPLOAD_JSSOR = '/jssor-slider/jssor.com';

    const URL_JSSOR = 'https://www.jssor.com';

    //const URL_JSSOR_EDIT_SLIDER = '/jssor-slider-wordpress-editor.aspx?jssorext=%s&hosturl=%s&adminurl=%s&instid=%s&instver=%s&actcode=%s&extver=%s&id=%d&filename=%s&restnounce=%s&lzw=%d';
    const URL_JSSOR_EDIT_SLIDER = '/wordpress/%s/=edit.wordpress?jssorext=%s&hosturl=%s&adminurl=%s&instid=%s&instver=%s&actcode=%s&extver=%s&id=%d&filename=%s&restnounce=%s&lzw=%d';
    const URL_JSSOR_ACTIVATE = '/api2/activation.ashx?method=activate';
    const URL_JSSOR_DEACTIVATE = '/api2/activation.ashx?method=deactivate';
    const URL_JSSOR_GENCODE = '/api2/jssor_slider_coding.ashx?method=gencode';
    const URL_JSSOR_IMPORT = '/api2/jssor_slider_repository.ashx?method=GetSliderDocument';

    const REQUIREMENTS_MIN_UPLOAD_FILE_SIZE = 2097152;//2 * 1024 * 1024;
    const REQUIREMENTS_MIN_POST_FILE_SIZE = 8388608;//8 * 1024 * 1024;

    public static function URL_JSSOR_SECURE()
    {
        return self::URL_JSSOR;
    }

    /**
     * get jssor edit slider url
     *
     * @return string
     */
    public static function get_jssor_edit_slider_url($slider_id, $slider_filename, $lzw = 0)
    {
        //$rest_nounce = wp_create_nonce('wp_rest');
        // $rest_root = esc_url_raw(rest_url());
        $instance_id = get_option('wp_jssor_slider_instance_id', '');
        $activation_code = get_option('wjssl_actcode', '');

        global $wp_version;
        $instance_version = $wp_version;
        //return wp_sprintf(self::URL_JSSOR . self::URL_JSSOR_EDIT_SLIDER, WP_JSSOR_SLIDER_DOMAIN, urlencode(site_url()), urlencode(admin_url()), $instance_id, $instance_version, $activation_code, WP_JSSOR_SLIDER_VERSION, $slider_id, $slider_filename, $lzw);
        return wp_sprintf(self::URL_JSSOR . self::URL_JSSOR_EDIT_SLIDER, $slider_filename, WP_JSSOR_SLIDER_DOMAIN, urlencode(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url()), urlencode(admin_url()), $instance_id, $instance_version, $activation_code, WP_JSSOR_SLIDER_VERSION, $slider_id, $lzw);
    }

    public static function get_jssor_preview_slider_url($slider_id, $slider_filename)
    {
        $site_url = WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url();

        return $site_url.'?jssor_extension=preview_slider&id='.$slider_id.'&filename='.urlencode($slider_filename);
    }

    public static function get_jssor_slider_thumb_sizes()
    {
        return array(
            'jssor-grid-thumb' => array('width' => 220, 'height' => 160, 'crop' => true),
            'jssor-list-thumb' => array('width' => 80, 'height' => 31, 'crop' => true)
            );
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function get_jssor_template_path()
    {
        return self::UPLOAD_TEMPLATE . '/001.slider';
    }

    /**
     * is jssor template path
     *
     * @return boolean
     */
    public static function is_jssor_template_path($path)
    {
        return stripos($path, self::UPLOAD_TEMPLATE) !== false;
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public static function get_shortcode_templ($id = false, $alias = false)
    {
        $templ = '[jssor-slider alias="%s"]';
        return self::get_shortcode_templ_str($templ, $id, $alias);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function get_shortcode_php_templ($id = false, $alias = false)
    {
        $templ = '<?php echo do_shortcode("[jssor-slider alias=\'%s\']")?>';
        return self::get_shortcode_templ_str($templ, $id, $alias);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private static function get_shortcode_templ_str($templ, $id = false, $alias = false)
    {
        if (empty($alias) && is_numeric($id)) {
            $model = new WP_Jssor_Slider_Slider();
            $data = $model->find($id);
            $alias = $data['file_name'];
        }
        if (is_string($alias)) {
            $templ = sprintf($templ, $alias);
        }
        return $templ;
    }

    public static function get_jssor_wordpress_site_url()
    {
        $site_url = site_url();

        if(substr($site_url, -1) != '/') {
            $site_url = $site_url . '/';
        }

        return $site_url;
    }

    public static function get_jssor_wordpress_site_info()
    {
        $siteInfo = new WP_Jssor_Slider_Site_Info ();

        $siteInfo->instid = get_option('wp_jssor_slider_instance_id', '');
        $siteInfo->hosturl = WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url();

        return $siteInfo;
    }

    public static function get_jssor_wordpress_admin_info()
    {
        $adminInfo = new WP_Jssor_Slider_Admin_Info ();

        $hosturl = WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url();
        $adminurl = admin_url();
        $pluginurl = WP_JSSOR_SLIDER_URL;

        if(substr($adminurl, -1) != '/') {
            $adminurl = $adminurl . '/';
        }

        if(substr($pluginurl, -1) != '/') {
            $pluginurl = $pluginurl . '/';
        }

        $adminInfo->instid = get_option('wp_jssor_slider_instance_id', '');
        $adminInfo->hosturl = $hosturl;
        $adminInfo->adminurl = $adminurl;
        $adminInfo->pluginurl = $pluginurl;
        $adminInfo->importsliderurl = $hosturl . '?jssor_extension=import_slider_with_progress';
        $adminInfo->mediabrowserurl = $hosturl . WP_JSSOR_MEDIA_BROWSER_URL;
        $adminInfo->actcode = get_option('wjssl_actcode');

        return $adminInfo;
    }

    public static function get_jssor_wordpress_updates_info()
    {
        //version info
        $latest_version = get_option('wjssl-latest-version', WP_JSSOR_SLIDER_VERSION);
        $stable_version = get_option('wjssl-stable-version', '1.2.3');
        $beta_version = get_option('wjssl-beta-version', WP_JSSOR_SLIDER_VERSION);
        $stable_update_available = version_compare(WP_JSSOR_SLIDER_VERSION, $stable_version, '<');
        $latest_update_available = version_compare(WP_JSSOR_SLIDER_VERSION, $latest_version, '<');
        $beta_update_available = version_compare(WP_JSSOR_SLIDER_VERSION, $beta_version, '<');

        return array(
                'version' => WP_JSSOR_SLIDER_VERSION,
                'stable_version' => $stable_version,
                'latest_version' => $latest_version,
                'beta_version' => $beta_version,
                'stable_update_available' => $stable_update_available,
                'latest_update_available' => $latest_update_available,
                'beta_update_available' => $beta_update_available,
                'update_available' => $stable_update_available || $latest_update_available || $beta_update_available
            );
    }

    public static function get_jssor_wordpress_status_info()
    {
        global $wp_version;

        //requirements info
        $can_connect = get_option('wjssl-connection', false);

        $upload_max_filesize = ini_get('upload_max_filesize');
        $upload_max_filesize_byte = WP_Jssor_Slider_Utils::get_upload_max_filesize_byte();
        $post_max_size = ini_get('post_max_size');
        $post_max_size_byte = WP_Jssor_Slider_Utils::get_post_max_size_byte();

        $upload_folder_writeable = WP_Jssor_Slider_Utils::get_upload_folder_writable();
        $upload_max_filesize_problem = ($upload_max_filesize_byte < WP_Jssor_Slider_Globals::REQUIREMENTS_MIN_UPLOAD_FILE_SIZE); //2M
        $post_max_size_problem = ($post_max_size_byte < WP_Jssor_Slider_Globals::REQUIREMENTS_MIN_POST_FILE_SIZE);  //8M

        $gd_installed = WP_Jssor_Slider_Utils::get_gd_library_installed();

        $status = array(
            'updates' => WP_Jssor_Slider_Globals::get_jssor_wordpress_updates_info(),
            'instver' => $wp_version,
            'can_connect' => $can_connect,
            'upload_max_filesize' => $upload_max_filesize,
            'upload_max_filesize_byte' => $upload_max_filesize_byte,
            'post_max_size' => $post_max_size,
            'post_max_size_byte' => $post_max_size_byte,
            'writable_problem' => !$upload_folder_writeable,
            'upload_max_filesize_problem' => $upload_max_filesize_problem,
            'post_max_size_problem' => $post_max_size_problem,
            'gd_library_problem' => !$gd_installed
        );

        return $status;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function upload_dir($upload_path, $time = null)
    {
        $upload = wp_upload_dir();
        if (!empty($time) && is_int($time)) {
            $time_str = date('Y/m', $time);
        } else {
            $time_str = date('Y/m', time());
        }
        $abs_dir = $upload['basedir'] . $upload_path;
        $abs_url = $upload['baseurl'] . $upload_path;
        $error = '';
        if (!wp_mkdir_p($abs_dir . '/' . $time_str)) {
           $error = 'No permission to create directory';
        }
        return array(
            'basedir' => $abs_dir,
            'baseurl' => $abs_url,
            'subdir' => '/' . $time_str,
            'path' => $abs_dir . '/' . $time_str,
            'url' => $abs_url . '/' . $time_str,
            'error' => $error
        );
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function slider_path($id)
    {
        $slider_path = self::upload_dir(self::UPLOAD_SLIDER);
        $name = $id . '.slider';
        $slider_file = $slider_path['path'] . '/' . $name;
        $rel_file = self::UPLOAD_SLIDER . $slider_path['subdir'] . '/' . $name;
        return array_merge($slider_path, array(
            'path' => $slider_file,
            'rel_path' => $rel_file,
            'url' => $slider_path['url'] . '/' . $name,
        ));
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function trim_bom($contents)
    {
        $rest = $contents;
        $charset[1] = substr($contents, 0, 1);
        $charset[2] = substr($contents, 1, 1);
        $charset[3] = substr($contents, 2, 1);
        if(ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
            $rest = substr($contents, 3);
        }
        return $rest;
    }

}

class WP_Jssor_Slider_API_Info
{
    public $error = 0;
    public $message = null;
}

class WP_Jssor_Slider_Site_Info
{
    public $instid = null;
    public $hosturl = null;
}

class WP_Jssor_Slider_Admin_Info
{
    public $instid = null;
    public $hosturl = null;
    public $adminurl = null;
    public $pluginurl = null;
    public $importsliderurl = null;
    public $mediabrowserurl = null;
    public $actcode = null;
}
