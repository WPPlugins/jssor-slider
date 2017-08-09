<?php
/*
Controller name: Sliders
Controller description: Sliders introspection methods
*/

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class JSON_API_Sliders_Controller {
    const SUCCESS = 0;
    const ERROR_NO_AUTHENTICATE = 1;
    const ERROR_NO_PERMISSION = 2;
    const ERROR_EXISTED = 3;

    /**
     * undocumented function
     *
     * @return void
     */
    public function save()
    {
        $this->output_headers();

        $is_valid = $this->check_valid_request('wjssl-save');
        global $wjssl_json_api;

        if ($is_valid !== true) {
            return $is_valid;
        }
        if (empty($_POST['data'])) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'Post data is empty',
            );
        }

        $slider_path = $this->get_slider_path();
        if(!wp_mkdir_p($slider_path['abs_dir'])){
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'No permission to create directory.'
            );
        }

        $data_obj = json_decode($this->strip_magic_quotes($_POST['data']));

        if (empty($data_obj->content)) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'Request is invalid.'
            );
        }
        $content = $data_obj->content;

        unset($data_obj);

        if(is_object($content)) {
            $content_obj = $content;
            $content = json_encode($content);
        }
        else{
            $content_obj = json_decode($content);
        }

        $slider_name = sanitize_text_field($wjssl_json_api->query->filename);

        $sliderModel = new WP_Jssor_Slider_Slider();
        $slider = $sliderModel->find_by_name($slider_name);

        $thumb_path = WP_Jssor_Slider_Utils::extract_slider_thumbnail_image($content_obj);
        require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';
        if (WP_Jssor_Resource_Utils::is_import_tag_url($thumb_path)) {
            $thumb_path = WP_Jssor_Resource_Utils::convert_import_tag_url($thumb_path);
        }
        //$thumb_path = WP_Jssor_Slider_Utils::trim_jssor_media_info_from_url($thumb_path);

        //no need to rebuild while saving slider from editor
        //list($content, $thumb_path) = $this->rebuild($content, $sliderModel);

        if (empty($slider)) {
            $data = array(
                'file_name' => $slider_name,
                'file_path' => WP_Jssor_Slider_Globals::get_jssor_template_path(),
            );

            // Insert slider, WPDB will escape data automatically
            $status = $sliderModel->save($data);
            if ($status === false) {
                return array(
                    'error' => 2,
                    'status' => 'error',
                    'message' => 'Create the slider failed.'
                );
            }
            // Return insert database ID
            $slider_id = $sliderModel->id;
            $slider_path = $this->get_slider_path($slider_id);
            $abs_path = $slider_path['abs_path'];
            $file_path = $slider_path['rel_path'];
        }
        else {
            $slider_id = $slider['id'];
            $file_path = $slider['file_path'];
            if ($sliderModel->is_template_file($file_path)) {
                $slider_path = $this->get_slider_path($slider_id);
                $abs_path = $slider_path['abs_path'];
                $file_path = $slider_path['rel_path'];
            } else {
                $upload = wp_upload_dir();
                $abs_path = $upload['basedir'] . $file_path;
            }
        }

        if (file_exists($abs_path)) {
            $can_overwrite = empty($wjssl_json_api->query->overwrite)
                ? false
                : intval($wjssl_json_api->query->overwrite);

            if (empty($can_overwrite)) {
                return array(
                    'error' => 2,
                    'status' => 'error',
                    'message' => 'The slider exists already.'
                );
            }
        }

        if (@file_put_contents($abs_path, $content) === false) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'No permission to write file.'
            );
        }

        $sliderModel->delete_code_html_files();

        $data = array(
            'file_path' => $file_path,
            'thumb_path'  => $thumb_path,
            'code_path' => '',
            'html_path' => '',
        );
        $sliderModel->set_data($data);
        $sliderModel->generate_thumbs();
        $status = $sliderModel->save();
        if ($status === false) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'Update the slider failed.'
            );
        }

        return array('error' => 0);

    }


    public function retrieve() {
        $this->output_headers();
        //$uid = 1;
        //$user = get_user_by('id', $uid);
        //if ($user) {
            //wp_set_current_user($uid, $user->user_login);
            //wp_set_auth_cookie( $uid, true, false);
        //}

        //if ($_SERVER["REQUEST_TYPE"] === "OPTIONS") { // special CORS track
            //exit; // no need to do anything else for OPTIONS request
        //}

        $is_valid = $this->check_valid_request('wjssl-retrieve');
        if ($is_valid !== true) {
            return $is_valid;
        }

        global $wjssl_json_api;
        $upload = wp_upload_dir();

        $slider_id = intval($wjssl_json_api->query->id);

        $sliderModel = new WP_Jssor_Slider_Slider();
        $row = $sliderModel->find($slider_id);
        if (empty($row)) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'Slider Not Found!'
            );
        }
        header('Content-Type:Application/json');
        $path = $upload['basedir'] . $row['file_path'];
        return file_get_contents($upload['basedir'] . $row['file_path']);
    }

    /**
     * download jssor resource file
     *
     * @return array | true
     */
    public function getjssorres()
    {
        global $wjssl_json_api;

        $url = sanitize_text_field($wjssl_json_api->query->url);
        $url = esc_url_raw($url);

        if(!empty($url))
        {
            $array = WP_Jssor_Slider_Utils::parse_url($url);
            require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';

            if(WP_Jssor_Resource_Utils::is_valid_resource($array))
            {
                $upload_dir = wp_upload_dir();
                $jssor_path = $upload_dir['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR;
                $jssor_url = $upload_dir['baseurl'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR;

                $rel_path = WP_Jssor_Resource_Utils::normalize_path($array['path']);
                if(stripos($rel_path, '/') !== 0) $rel_path = '/' . $rel_path;
                $file_path = $jssor_path . $rel_path;
                $file_url = $jssor_url . $rel_path;

                if(!file_exists($file_path) || !filesize($file_path))
                {
                    $path = array(
                        array(
                            'local_url' => $file_url,
                            'local_path' => $file_path,
                            'remote_url' => $url,
                            'local_rel_path' => WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $rel_path,
                            'original_url' => $url,
                        )
                    );
                    include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-slider-medias-downloader.php';
                    $downloader = new WjsslSliderMediasDownloader();
                    $status = $downloader->multi_download($path);
                    if($status && WP_Jssor_Resource_Utils::is_image($file_path)) {
                        $downloader->generate_metadatas($path);
                    }
                }
                $this->send_file($file_path);
                return;
            }
        }
        status_header(400);
        exit();
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function send_file($file_path)
    {
        if (file_exists($file_path)) {
            $filemtime = filemtime($file_path);
            $filegmtime = gmdate('D, d M Y H:i:s', $filemtime) .' GMT';

            if (
                isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                &&
                ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $filegmtime)
            )
            {
                status_header(304);
                exit();
            }

            if (function_exists('mime_content_type')) {
                $mime_type = mime_content_type($file_path);
            } else {
                require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';
                $mime_type = WP_Jssor_Resource_Utils::get_mime_content_type($file_path);
            }
            header('content-type:' . $mime_type);

            $client_cache_time = 24 * 60 * 60;
            header('Cache-Control: public, max-age=' . $client_cache_time);
            header('Expires: '.gmdate('D, d M Y H:i:s',time() + $client_cache_time) .' GMT');
            header('Last-Modified: '. $filegmtime);
            readfile($file_path);
        } else {
            status_header(404);
        }
        exit();
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public function crop_img()
    {

        global $wjssl_json_api;

        $image_url = sanitize_text_field($wjssl_json_api->query->url);
        $image_url = esc_url_raw($image_url);
        $crop_size = explode('x', sanitize_text_field($wjssl_json_api->query->size));

        if (!empty($image_url) && !empty($crop_size)) {
            list($crop_w, $crop_h) = $crop_size;

            $upload = WP_Jssor_Slider_Globals::upload_dir(WP_Jssor_Slider_Globals::UPLOAD_THUMB);
            $extension = pathinfo($image_url, PATHINFO_EXTENSION);

            $relative_filename = '/' . pathinfo($image_url, PATHINFO_FILENAME) . '_' . $crop_w . 'x' . $crop_h . '.' . $extension;
            $filename = $upload['path'] . $relative_filename;

            if (file_exists($filename)) {
                return $this->send_file($filename);
            }

            $upload_dir = wp_upload_dir();
            if (stripos($image_url, $upload_dir['baseurl']) === 0) {
                $image_url = $upload_dir['basedir'] . str_replace($upload_dir['baseurl'], '', $image_url);
            }
            $image = wp_get_image_editor($image_url); // Return an implementation that extends WP_Image_Editor
            
            if ( ! is_wp_error( $image ) ) {

                $image->resize($crop_w, $crop_h, true);

                $resp = $image->save($filename);
                if (!is_wp_error($resp)) {
                    $this->send_file($filename);
                    return;
                }
            }
            status_header(500);
        } else {
            status_header(400);
        }
        exit();
    }

    protected function strip_magic_quotes($value)
    {
        return stripslashes($value);
        //if (get_magic_quotes_gpc()) {
            //return stripslashes($value);
        //} else {
            //return $value;
        //}
    }

    /**
     * check if request is valid
     *
     * @return array | true
     */
    protected function check_valid_request($nonce_name)
    {
        if (!is_user_logged_in()) {
            return array(
                'error' => 1,
                'status' => 'error',
                'message' => 'Login Required! Please <a href="' . admin_url() . '" target="_blank">login</a> and then try again.'
            );
        }

        if (!current_user_can('manage_options')) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'Permission Denied!'
            );
        }
        global $wjssl_json_api;
        $nonce = sanitize_text_field($wjssl_json_api->query->nonce);
        if (empty($nonce) || !wp_verify_nonce($nonce, $nonce_name)) {
            return array(
                'error' => 2,
                'status' => 'error',
                'message' => 'The request is invalid.'
            );
        }
        //global $wjssl_json_api;
        //if (empty($wjssl_json_api->query->id)) {
        //    return array(
        //        'error' => 2,
        //        'status' => 'error',
        //        'message' => 'Invalid Request!'
        //    );
        //}
        return true;
    }

    /**
     * common response headers
     *
     * @return void
     */
    protected function output_headers()
    {
        $headers = $this->common_headers();
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
    }


    /**
     * undocumented function
     *
     * @return void
     */
    protected function common_headers()
    {
        //$http_origin            = $_SERVER['HTTP_ORIGIN'];
        //$allowed_http_origins   = array(
        //    'http://jssor.com'   ,
        //    'http://www.jssor.com'  ,
        //    'https://jssor.com'   ,
        //    'https://www.jssor.com'  ,
        //);
        //if ($http_origin && in_array($http_origin, $allowed_http_origins)) {
        //    // do nothing
        //} else {
        //    $http_origin = 'http://www.jssor.com';
        //}

        return array(
            //'Access-Control-Expose-Headers' => 'x-json',
            //'Access-Control-Max-Age' => 1728000,
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
            //'Access-Control-Allow-Origin' => $http_origin,
            'Access-Control-Allow-Credentials' => 'true'
        );
    }

    /**
     * slider path
     *
     * @return array
     */
    protected function get_slider_path($slider_id = 0)
    {
        $upload_dir = wp_upload_dir();

        $file_dir = WP_Jssor_Slider_Globals::UPLOAD_SLIDER . '/'
            . date('Y/m', time());
        $abs_file_dir = $upload_dir['basedir'] . $file_dir;

        $file_path = $file_dir . '/'. $slider_id . '.slider';
        $abs_path = $upload_dir['basedir'] . $file_path;

        return array(
            'rel_path' => $file_path,
            'rel_dir' => $file_dir,
            'abs_path' => $abs_path,
            'abs_dir' => $abs_file_dir,
        );
    }


    /**
     * undocumented function
     *
     * @return void
     */
    private function rebuild($content, $slider_model)
    {
        $obj = json_decode($content);
        unset($content);
        include_once WP_JSSOR_SLIDER_PATH . 'custom/import/class-wp-jssor-empty-processor.php';
        include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-slider-json-handler.php';
        $processor = new WP_Jssor_Empty_Processor();
        $context = array(
            'json' => $obj,
            'processor' => $processor,
            'slider_model' => $slider_model,
);
        $handler = new WjsslSliderJsonHandler($context);
        $handler->rebuild();

        $content = $handler->getJson();
        $thumb_path = $handler->getThumb();

        return array(
            $content,
            $thumb_path
        );
    }

}
