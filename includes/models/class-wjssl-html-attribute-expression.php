<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslHtmlAttributeExpression
 * @author Neil.zhou
 */
class WjsslHtmlAttributeExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $context = $this->getContext();
        $name = $context['name'];

        if (isset($context['vType'])) {
            require_once WP_JSSOR_SLIDER_PATH
                . 'includes/models/class-wjssl-node-type-factory.php';
            $node_type = WjsslNodeTypeFactory::create_node($context);
            $value = $node_type->node_value();
        } elseif (isset($context['value'])) {
            $value = $context['value'];
        } elseif (isset($context['childNodes'])) {
            $value = $this->child_nodes()->interpret();
        } else {
            $value = '';
        }

        $value = WP_Jssor_Slider_Utils::trim_jssor_media_info_from_url($value);
        if (isset($context['preferSize'])) {
            $value = $this->thumbnail_crop($value, $context['preferSize']);
        }

        if (strcasecmp($name, 'data-library') == 0) {
            $parent = $this->getParent();
            if (method_exists($parent, 'setIgnored')) {
                $parent->setIgnored();
            }
        }
        return "$name=\"$value\" ";
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function thumbnail_crop($image_url, $prefer_size)
    {
        if (empty($image_url)) {
            return '';
        }
        if (stripos($image_url, '/') === 0) {
            $image_url = ltrim($image_url, '/');
            require_once WP_JSSOR_SLIDER_PATH . 'includes/framework/class-wp-jssor-resource-utils.php';
            $site_base_url = WP_Jssor_Resource_Utils::site_url_without_subdir();
            $image_url = trailingslashit($site_base_url) . $image_url; 
        }
        $check_image_url = esc_url_raw($image_url);
        if (empty($check_image_url)) {
            return $image_url;
        }

        $image_array = WP_Jssor_Slider_Utils::parse_url($image_url);

        $port_suffix = '';
        if(!empty($image_array['port'])) {
            $port_suffix = ':' . $image_array['port'];
        }

        $image_url = $image_array['scheme'] . '://' . $image_array['host'] . $port_suffix . $image_array['path'];
        if (!empty($image_array['query'])) {
            $query_str = '?' . $image_array['query'];
        } else {
            $query_str = '';
        }

        $exist_wp_thumbnail = $this->get_wp_thumbnail($prefer_size);
        if ($exist_wp_thumbnail) {
            return $exist_wp_thumbnail . $query_str;
        }

        $upload = WP_Jssor_Slider_Globals::upload_dir(WP_Jssor_Slider_Globals::UPLOAD_THUMB);
        $relative_filename = '/' . pathinfo($image_url, PATHINFO_FILENAME) . '_' . $prefer_size['w'] . 'x' . $prefer_size['h'] . '.' . pathinfo($image_url, PATHINFO_EXTENSION);
        $filename = $upload['path'] . $relative_filename;

        if (file_exists($filename)) {
            return $upload['url'] . $relative_filename . $query_str;
        }

        return WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url() . '?jssorextver=' . WP_JSSOR_SLIDER_VERSION . '&method=crop_img&size=' . $prefer_size['w'] . 'x' . $prefer_size['h'] . '&url=' . urlencode($image_url . $query_str);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function get_wp_thumbnail($prefer_size)
    {
        $media = $this->get_media_props();
        if (empty($media) || empty($media->id)) {
            return null;
        }
        $metadata = wp_get_attachment_metadata($media->id);
        if (empty($metadata)) {
            return null;
        }
        foreach ($metadata['sizes'] as $size) {
            if (
                ($size['width'] == $prefer_size['w'])
                &&
                ($size['height'] == $prefer_size['h'])
            ) {
                return wp_get_attachment_image_url($media['id'], array($prefer_size['w'], $prefer_size['h']));
            }
        }
        return null;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function get_media_props()
    {
        $context = $this->getContext();
        $url = $context['value'];

        $media_info = WP_Jssor_Slider_Utils::get_jssor_media_info_from_url($url);

        if(!empty($media_info) && !empty($media_info->extmedia)) {
            return $media_info->extmedia;
        }

        return null;

        //$context = $this->getContext();
        //$url = $context['value'];

        //if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
        //    return null;
        //}

        //$array = WP_Jssor_Slider_Utils::parse_url($url);
        //if (empty($array['query'])) {
        //    return null;
        //}
        //$query_array = wp_parse_args($array['query']);
        //if ($query_array['extmedia']) {
        //    return json_decode($query_array['extmedia'], true);
        //}
        //return null;
    }

}
