<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WP_Jssor_Slider_Utils
{

    /**
     * Returns a GUIDv4 string
     *
     * Uses the best cryptographically secure method
     * for all supported pltforms with fallback to an older,
     * less secure version.
     *
     * @param bool $trim
     * @return string
     */
    public static function create_guid($trim = true)
    {
        // Windows
        if (function_exists('com_create_guid') === true) {
            if ($trim === true)
                return trim(com_create_guid(), '{}');
            else
                return com_create_guid();
        }

        // OSX/Linux
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        // Fallback (PHP 4.2+)
        mt_srand((double)microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);                  // "-"
        $lbrace = $trim ? "" : chr(123);    // "{"
        $rbrace = $trim ? "" : chr(125);    // "}"
        $guidv4 = $lbrace.
            substr($charid,  0,  8).$hyphen.
            substr($charid,  8,  4).$hyphen.
            substr($charid, 12,  4).$hyphen.
            substr($charid, 16,  4).$hyphen.
            substr($charid, 20, 12).
            $rbrace;
        return $guidv4;
    }

    public static function parse_url( $url ) {
        $parts = @parse_url( $url );
        if ( ! $parts ) {
            // < PHP 5.4.7 compat, trouble with relative paths including a scheme break in the path
            if ( '/' == $url[0] && false !== strpos( $url, '://' ) ) {
                // Since we know it's a relative path, prefix with a scheme/host placeholder and try again
                if ( ! $parts = @parse_url( 'placeholder://placeholder' . $url ) ) {
                    return $parts;
                }
                // Remove the placeholder values
                unset( $parts['scheme'], $parts['host'] );
            } else {
                return $parts;
            }
        }

        // < PHP 5.4.7 compat, doesn't detect schemeless URL's host field
        if ( '//' == substr( $url, 0, 2 ) && ! isset( $parts['host'] ) ) {
            $path_parts = explode( '/', substr( $parts['path'], 2 ), 2 );
            $parts['host'] = $path_parts[0];
            if ( isset( $path_parts[1] ) ) {
                $parts['path'] = '/' . $path_parts[1];
            } else {
                unset( $parts['path'] );
            }
        }

	return $parts;
}

    public static function trim_jssor_media_info_from_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return $url;
        }

        $array = WP_Jssor_Slider_Utils::parse_url($url);
        $port_suffix = '';
        if(!empty($array['port'])) {
            $port_suffix = ':' . $array['port'];
        }
        $path = isset($array['path']) ? $array['path'] : '';
        $url = $array['scheme'] . '://' . $array['host'] . $port_suffix . $path;

        if (isset($array['query'])) {
            $query_array = wp_parse_args($array['query']);
            unset($query_array['jssorext']);
            unset($query_array['extsite']);
            unset($query_array['extmedia']);
            $query_str = build_query( $query_array);
        } else {
            $query_str = '';
        }

        if (!empty($query_str)) {
            $url .= '?' . $query_str;
        }
        return $url;
    }

    public static function get_jssor_media_info_from_url($url) {

        if (filter_var($url, FILTER_VALIDATE_URL) !== FALSE) {
            $array = WP_Jssor_Slider_Utils::parse_url($url);

            if (isset($array['query'])) {
                $media_info = new stdClass();
                $query_array = wp_parse_args($array['query']);

                foreach($query_array as $key => $value) {
                    if(!empty($value)) {
                        $media_info->$key = json_decode($value);
                    }
                }

                return $media_info;
            }
        }

        return null;
    }

    /**
     * retrieve the image id from the given image url
     * @since: 1.0
     */
    public static function get_image_id_by_url($image_url) {
        global $wpdb;

        $attachment_id = 0;

        if(!empty($image_url)) {
            $media_info = WP_Jssor_Slider_Utils::get_jssor_media_info_from_url($image_url);
            if(!empty($media_info) && !empty($media_info->extsite) && isset($media_info->extsite->instid) && $media_info->extsite->instid == get_option('wp_jssor_slider_instance_id', ''))
            {
                if(!empty($media_info->extmedia->id))
                {
                    return $media_info->extmedia->id;
                }
            }

            $image_url = WP_Jssor_Slider_Utils::trim_jssor_media_info_from_url($image_url);

            if(function_exists('attachment_url_to_postid')){
                $attachment_id = attachment_url_to_postid($image_url); //0 if failed
            }
            if (0 == $attachment_id){
                //for WP < 4.0.0

                // Get the upload directory paths
                $upload_dir_paths = wp_upload_dir();

                // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
                if ( false !== strpos( $image_url, $upload_dir_paths['baseurl'] ) ) {

                    // If this is the URL of an auto-generated thumbnail, get the URL of the original image
                    $image_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $image_url );

                    // Remove the upload path base directory from the attachment URL
                    $image_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $image_url );

                    // Finally, run a custom database query to get the attachment ID from the modified attachment URL
                    $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $image_url ) );

                    if(isset($post_id))
                    {
                        $attachment_id = $post_id;
                    }
                }
            }
        }

        return $attachment_id;
    }

    public static function get_attachment_metadata_by_url($local_url)
    {
        $attachment_id = WP_Jssor_Slider_Utils::get_image_id_by_url($local_url);

        if($attachment_id > 0) {
            return wp_get_attachment_metadata($attachment_id);
        }
        return array();
    }

    public static function attachment_exists($local_url)
    {
        $attachment_id = WP_Jssor_Slider_Utils::get_image_id_by_url($local_url);
        return $attachment_id > 0;
    }

    public static function extract_slider_thumbnail_image($slider)
    {
        if (!empty($slider->slides)) {
            if (isset($slider->slides[0]->image)) {
                return $slider->slides[0]->image;
            }
        }

        return null;
    }

    public static function get_upload_folder_writable()
    {
        $dir = wp_upload_dir();
        return wp_is_writable($dir['basedir'].'/');
    }

    public static function get_upload_max_filesize_byte()
    {
        $upload_max_filesize = ini_get('upload_max_filesize');
        $upload_max_filesize_byte = wp_convert_hr_to_bytes($upload_max_filesize);

        return $upload_max_filesize_byte;
    }

    public static function get_post_max_size_byte()
    {
        $post_max_size = ini_get('post_max_size');
        $post_max_size_byte = wp_convert_hr_to_bytes($post_max_size);

        return $post_max_size_byte;
    }

    public static function get_gd_library_installed()
    {
        return extension_loaded('gd') && function_exists('gd_info');
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function delete_slider_relative_file($rel_file)
    {
        $upload_dir = wp_upload_dir();
        $abs_path = $upload_dir['basedir'] . $rel_file;
        if ($rel_file && file_exists($abs_path)) {
            return @unlink($abs_path);
        }
        return false;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function is_wrapped_by($str, $begin='{', $end='}')
    {
        return
            (strpos($str, $begin) === 0)
            &&
            (strrpos($str, $end) === (strlen($str) - strlen($end)));
    }
}
