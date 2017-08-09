<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Class WP_Jssor_Slider_Importer
 * @author Neil.zhou
 */
class WP_Jssor_Slider_Importer
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($context)
    {
        $this->remote_url = esc_url_raw($context['remote_slider']);
        $this->processor = $context['processor'];
        $this->slider_name = $context['slider_name'];
        $this->slider_model = $context['slider_model'];
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function import()
    {
        $json = $this->copy_from_template();
        if (empty($json)) {
            $json = $this->fetch_remote_slider();
        }

        if (is_wp_error($json)) {
            return $json;
        }

        $thumb_path = '';

        $this->processor()->arrive_at(20, __('Parsing the slider ...', WP_JSSOR_SLIDER_DOMAIN), $this->remote_url());
        include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-slider-json-handler.php';
        $context = array(
            'json' => $json,
            'processor' => $this->processor(),
            'slider_model' => $this->slider_model()
        );
        $handler = new WjsslSliderJsonHandler($context);
        $status = $handler->rebuild();
        if (is_wp_error($status)) {
            return $status;
        }
        $json = $handler->getJson();
        $thumb_path = $handler->getThumb();

        require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';
        $json = WP_Jssor_Resource_Utils::add_import_tag_for_slider($json);
        return $this->save_slider($json, $thumb_path);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function fetch_remote_slider()
    {
        $this->processor()->arrive_at(15, __('Fetching remote slider resources ...', WP_JSSOR_SLIDER_DOMAIN), $this->remote_url());

        global $wp_version;

        $import_api_url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE() . WP_Jssor_Slider_Globals::URL_JSSOR_IMPORT;
        $data = array(
            'jssorext' => WP_JSSOR_SLIDER_EXTENSION_NAME,
            'hosturl' => esc_url_raw(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url()),
            'instid' => get_option('wp_jssor_slider_instance_id', ''),
            'acckey' => get_option('wjssl_acckey', ''),
            'instver' => $wp_version,
            'extver' => WP_JSSOR_SLIDER_VERSION,
            'fileurl' => esc_url_raw($this->remote_url)
        );

        $params = array('data' => json_encode($data));
        $headers = array();
        if (function_exists('gzencode')) {
            $params = gzencode(http_build_query($params));
            $headers = array('Content-Encoding' => 'gzip');
        }

        $resp = wp_remote_post(esc_url_raw($import_api_url), array(
            'body' => $params, 
            'headers' => $headers, 
            'timeout' => 60,
        ));

        if (is_wp_error($resp)) {
            return $resp;
        }

        $json = json_decode($resp['body']);

        if (empty($json)) {
            return new WP_Error('JSON-DECODE-ERROR', $resp['body']);
        }

        if(!empty($json->error))
        {
            return new WP_Error('IMPORT-SLIDER-ERROR', $json->message);
        }
        return $json->document;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function copy_from_template()
    {
        $url_array = WP_Jssor_Slider_Utils::parse_url($this->remote_url);
        $rel_path = $url_array['path'];
        $upload_dir = wp_upload_dir();
        $template_path = $upload_dir['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $rel_path;

        if (!file_exists($template_path)) {
            return false;
        }

        $slider_content = file_get_contents($template_path);
        $json = json_decode($slider_content);
        if (empty($json)) {
            return false;
        }
        return $json;
    }


    private function save_slider($json, $thumb_path)
    {
        $this->slider_model()->find_by_name($this->slider_name());
        $this->slider_model()->set_value('thumb_path', WP_Jssor_Slider_Utils::trim_jssor_media_info_from_url($thumb_path));
        $this->slider_model()->generate_thumbs();

        $this->processor()->arrive_at(99, __('Saving slider ...', WP_JSSOR_SLIDER_DOMAIN), $this->slider_model()->data_value('file_name'));

        $this->slider_model()->delete_code_html_files();

        if (!$this->slider_model()->id) {
            if(!$this->slider_model()->save()) {
                return new WP_Error('SAVE-SLIDER-ERROR', $this->slider_model()->last_error());
            }
        }

        $slider_path = WP_Jssor_Slider_Globals::slider_path($this->slider_model()->id);
        wp_mkdir_p(dirname($slider_path['path']));
        if(!@file_put_contents($slider_path['path'], $json)) {
            return new WP_Error('NO-PERMISSION', __('No permission to write slider file['.$slider_path['path'].'].', WP_JSSOR_SLIDER_DOMAIN));
        }

        if(!$this->slider_model()->save(array(
            'file_path' => $slider_path['rel_path']
        ))) {
            return new WP_Error('SAVE-SLIDER-ERROR', $this->slider_model()->last_error());
        }
        return true;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function remote_url()
    {
        return $this->remote_url;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function remote_view_text_url()
    {
        return $this->remote_url()  .  '/=view.text';
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function processor()
    {
        return $this->processor;
    }

    private function slider_name()
    {
        return $this->slider_name;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function slider_model()
    {
        return $this->slider_model;
    }

}
