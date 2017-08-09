<?php


/**
 * Class WP_Jssor_Resource_Utils
 * @author Neil.zhou
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WP_Jssor_Resource_Utils
{

    /**
    * Normalise a file path string so that it can be checked safely.
    *
    * Attempt to avoid invalid encoding bugs by transcoding the path. Then
    * remove any unnecessary path components including '.', '..' and ''.
    *
    * @param $path string
    * The path to normalise.
    * @param $encoding string
    * The name of the path iconv() encoding.
    * @return string
    * The path, normalised.
    */
    public static function normalize_path($path) {
        // Attempt to avoid path encoding problems.
        if (function_exists('wp_normalize_path')) {
            $path = wp_normalize_path($path);
        } else {
            $path = self::_wp_normalize_path($path);
        }
        // Process the components
        $parts = explode('/', $path);
        $safe = array();
        foreach ($parts as $idx => $part) {
            if (empty($part) || ('.' == $part)) {
                continue;
            } elseif ('..' == $part) {
                array_pop($safe);
                continue;
            } else {
                $safe[] = $part;
            }
        }
        // Return the "clean" path
        $path = implode('/', $safe);
        return $path;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private static function _wp_normalize_path($path)
    {
        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '|(?<=.)/+|', '/', $path );
        if ( ':' === substr( $path, 1, 1 ) ) {
            $path = ucfirst( $path );
        }
        return $path;
    }


    public static function add_import_tag_for_slider($slider_content)
    {
        $jssor_base_url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE();
        $add_slashes_url = addcslashes($jssor_base_url, '/');
        $slider_content = preg_replace('/(\(|")(\\\\\/(theme|template|image|demos|script|help-slideo)\\\\\/)/', '$1@Import\/' . $add_slashes_url . '$2', $slider_content);
        return preg_replace('/(\(|")(\/(theme|template|image|demos|script|help-slideo)\/)/', '$1@Import/' . $jssor_base_url . '$2', $slider_content);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function get_mime_content_type($filename)
    {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function site_url_without_subdir()
    {
        $site_url_array = WP_Jssor_Slider_Utils::parse_url(site_url());
        $port_str = empty($site_url_array['port']) ? '' : ':' . $site_url_array['port'];
        return $site_url_array['scheme'] . '://' . $site_url_array['host'] . $port_str;
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    public static function is_valid_resource($url_array)
    {
        if (is_string($url_array)) {
            $url_array = WP_Jssor_Slider_Utils::parse_url($url_array);
        }
        if ($url_array['scheme'] != 'http' && $url_array['scheme'] != "https") {
            return false;
        }
        $ext = strtolower(pathinfo($url_array['path'], PATHINFO_EXTENSION));
        $allowed_exts = array(
            'jpg', 'jpeg', 'png', 'gif', 'psd', 'tiff', 'bmp', 'svg', 'txt', 'text', 'css', 'html'
        );
        return in_array($ext, $allowed_exts);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function is_image_extension($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed_exts = array(
            'jpg', 'jpeg', 'png', 'gif', 'psd', 'tiff', 'bmp', 'svg'
        );
        return in_array($ext, $allowed_exts);
    }

    public static function is_image($path)
    {
        $a = getimagesize($path);
        $image_type = $a[2];

        if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
        {
            return true;
        }
        return false;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function is_existed($abs_path)
    {
        return file_exists($abs_path) && (filesize($abs_path) > 0) && is_file($abs_path);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function convert_to_import_tag($path)
    {
        if (stripos($path, '/') === 0) {
            $upload_dir = wp_upload_dir();
            $path = wp_normalize_path($path);
            $abs_path = $upload_dir['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $path;

            if (file_exists($abs_path) && is_file($abs_path) && filesize($abs_path) > 0) {
                return $upload_dir['baseurl'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $path;
            }

            $jssor_base_url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE();
            return '@Import/' . $jssor_base_url . $path;
        }
        return $path;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function is_import_tag_url($url)
    {
        return stripos($url, '@Import/') === 0;
    }


    public static function convert_import_tag_url($url)
    {
        $url = str_replace('@Import/', '', $url);

        $url_array = WP_Jssor_Slider_Utils::parse_url($url);

        $rel_path = WP_Jssor_Resource_Utils::normalize_path($url_array['path']);
        if(stripos($rel_path, '/') !== 0) $rel_path = '/' . $rel_path;

        $upload_dir = wp_upload_dir();
        $jssor_path = $upload_dir['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR;
        $jssor_url = $upload_dir['baseurl'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR;
        $file_path = $jssor_path . $rel_path;
        $file_url = $jssor_url . $rel_path;

        if(!file_exists($file_path) || !is_file($file_path) || !filesize($file_path))
        {
            return self::_format_wp_import_url($url);
        }

        return $file_url;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function extract_resources_from_string($string)
    {
        $result = $matches = array();
        $rgx = '(theme|template|image|demos|script|help-slideo)';

        preg_match_all('#\\\\"(/' . $rgx . '.*?)\\\\"#', $string, $matches);
        $result = $matches[1];

        preg_match_all('#\\\\\((/' . $rgx . '.*?)\\\\\)#', $string, $matches);
        $result = array_merge($result, $matches[1]);

        $jssor_base_url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE();

        $string = preg_replace('#(\\\\")(/' . $rgx . '.*?\\\\")#', '$1@Import/' . $jssor_base_url . '$2', $string);
        $string = preg_replace('#(\\\\\()(/' . $rgx . '.*?\\\\\))#', '$1@Import/' . $jssor_base_url . '$2', $string);


        preg_match_all('#"(/' . $rgx . '.*?)"#', $string, $matches);
        $result = array_merge($result, $matches[1]);

        preg_match_all('#\((/' . $rgx . '.*?)\)#', $string, $matches);
        $result = array_merge($result, $matches[1]);

        $resources = array();
        if ($result) {
            $upload = wp_upload_dir();
            $rel_jssor = WP_Jssor_Slider_Globals::UPLOAD_JSSOR;
            $jssor_host = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE();
            foreach ($result as $key => $value) {
                $resources[$value] = array(
                    'remote_url' => $jssor_host .  $value,
                    'local_url' => $upload['baseurl'] . $rel_jssor . $value,
                    'local_path' => $upload['basedir'] . $rel_jssor . $value,
                    'original_url' => $jssor_host .  $value,
                );
            }
        }
        return $resources;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function is_http_url($url)
    {
        return (stripos($url, 'http://') === 0) || (stripos($url, 'https://') === 0);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private static function _format_wp_import_url($url)
    {
        $url = str_replace('@Import/', '', $url);
        $template = '%s?jssorextver=%s&method=getjssorres&url=%s';
        return wp_sprintf($template, WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url(), WP_JSSOR_SLIDER_VERSION, urlencode($url));
    }

}
