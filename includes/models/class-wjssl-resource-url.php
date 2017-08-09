<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-node-type.php';
require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';

/**
 * Class WjsslResourceUrl
 * @author Neil.zhou
 */
class WjsslResourceUrl extends WjsslNodeType
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function node_value()
    {

        $context = $this->context();
        $context['value'] = trim($context['value']);
        if (empty($context['value'])) {
            return '';

        } elseif($this->_is_import_tag_url($context['value'])) {
            return $this->_convert_import_tag_url($context['value']); 

        } elseif ($this->_is_url_allowed_download()) {
            return $this->_save_resource_local_path();

        } elseif (stripos($context['value'], '/') === 0) {
            $value = ltrim($context['value'], '/');
            $site_base_url = WP_Jssor_Resource_Utils::site_url_without_subdir();
            return trailingslashit($site_base_url) . $value; 
        }
        return $context['value'];
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function _is_import_tag_url($url)
    {
        return WP_Jssor_Resource_Utils::is_import_tag_url($url);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function _convert_import_tag_url($url)
    {
        return WP_Jssor_Resource_Utils::convert_import_tag_url($url);
    }
    
    /**
     *
     * @return string
     */
    private function _save_resource_local_path()
    {
        $upload_dir = wp_upload_dir();
        $context = $this->context();

        $filename = $upload_dir['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR. $context['value'];

        if ((!file_exists($filename)) || (filesize($filename) == 0)) {
            $remote_resource_url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE() . $context['value'];
            return $this->_convert_import_tag_url($remote_resource_url);
        }

        return $upload_dir['baseurl'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $context['value'];
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function _is_url_allowed_download()
    {
        return $this->_is_script_url()
            || $this->_is_template_url()
            || $this->_is_demo_url()
            || $this->_is_theme_url();
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function _is_script_url()
    {
        $context = $this->context();
        return stripos($context['value'], '/script/') === 0;
    }
    
    /**
     *
     * @return boolean
     */
    private function _is_template_url()
    {
        $context = $this->context();
        return stripos($context['value'], '/template/') === 0;
    }
    
    /**
     *
     * @return boolean
     */
    private function _is_theme_url()
    {
        $context = $this->context();
        return stripos($context['value'], '/theme/') === 0;
    }
    
    /**
     *
     * @return boolean
     */
    private function _is_demo_url()
    {
        $context = $this->context();
        return stripos($context['value'], '/demos/') === 0;
    }
}
