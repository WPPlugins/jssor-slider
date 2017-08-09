<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Class WjsslSliderMediasDownloader
 * @author Neil.zhou
 */
class WjsslSliderMediasDownloader
{
    private $max_simultaneous = 5;

    /* Timeout is the timeout used for curl_multi_select. */
    private $timeout = 10;

    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function is_self_site($url)
    {
        return stripos($url, WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url()) !== false;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function get_media_name($name)
    {
        return preg_replace( '/\.[^.]+$/', '', $name );
    }

    private function filter_download_medias($download_urls)
    {
        $medias_to_download = array();

        foreach($download_urls as $item)
        {
            if (empty($item) || empty($item['remote_url'])) {
                $url = '';
            } else {
                $url = esc_url_raw($item['remote_url']);
            }
            if (
                (isset($item['ignore_download']) && $item['ignore_download'] === true)
                ||
                empty($url)
                ||
                empty($item['local_path'])
                ||
                (file_exists($item['local_path']) && (filesize($item['local_path']) > 0) && is_file($item['local_path']))
            ) {
                $item['ignore_download'] = true;
            }
            else {
                $medias_to_download[] = $item;
            }
        }

        return $medias_to_download;
    }

    //private function attachment_exists($title, $guid) {
    //    global $wpdb;

    //    return $wpdb->get_var( $wpdb->prepare(
    //        "SELECT count(*) FROM $wpdb->posts WHERE guid = %s AND post_title = %s AND post_type = 'attachment' ", $guid, $title
    //    ));
    //}

    /**
     * undocumented function
     *
     * @return void
     */
    private function ensure_metadata($item)
    {
        // $filename should be the path to a file in the upload directory.
        $filename = $item['local_path'];
        $local_url = $item['local_url'];

        $attach_id = WP_Jssor_Slider_Utils::get_image_id_by_url($local_url);

        if($attach_id == 0) {
            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filetype = wp_check_filetype( basename( $filename ), null );

            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid'           => substr($item['local_rel_path'], 1),    //unique id of the attachment
                'post_mime_type' => $filetype['type'],
                'post_title'     => $this->get_media_name(basename($filename)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            // Insert the attachment.
            $attach_id = wp_insert_attachment($attachment, $filename);
        }
        else {
            $attach_data = WP_Jssor_Slider_Utils::get_attachment_metadata_by_url($local_url);

            if(!empty($attach_data))
            {
                return $this->attach_meta_data($attach_id, $attach_data);
            }
        }

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $this->attach_meta_data($attach_id, $attach_data);
    }

    public function generate_metadatas($download_urls)
    {
        $urls = array();

        foreach ($download_urls as $item) {
            if (
                (!isset($item['ignore']) || $item['ignore'] !== true)
                &&
                (file_exists($item['local_path']) && (filesize($item['local_path']) > 0))
            ) {
                $attach_data = $this->ensure_metadata($item);
                if (empty($attach_data['width'])) {
                    $attach_data['width'] = 0;
                }
                if (empty($attach_data['height'])) {
                    $attach_data['height'] = 0;
                }
                $extmedia = array(
                    "id" => $attach_data['id'],
                    "mime" => $attach_data['mime'],
                    "width" => $attach_data['width'],
                    "height" => $attach_data['height']//,
                    //"alt" => $attach_data->alt,
                    //"caption" => $attach_data->caption,
                    //"description" => $attach_data->description
                    );
                $local_url_plus = $item['local_url'] . '?jssorext=' . WP_JSSOR_SLIDER_EXTENSION_NAME . '&extsite=' . rawurlencode(json_encode(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_info())) . '&extmedia=' . rawurlencode(json_encode($extmedia));
                $item['local_url_plus'] = $local_url_plus;

                $urls[$item['original_url']] = $item;
            }
            //prevent from throwing error to go through download process
            //else {
            //    throw new Exception(__('Some error happened when download media['.$item['remote_url'] .']', WP_JSSOR_SLIDER_DOMAIN), 'NO-PERMISSION');
            //}
        }

        return $urls;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function multi_download($download_urls)
    {
        $succeeded = true;

        $medias_to_download = $this->filter_download_medias($download_urls);

        if (empty($medias_to_download)) {
            return true;
        }

        $mh = curl_multi_init();
        $conn = array();
        $active = null;

        $medias_count = count($medias_to_download);
        $max_simultaneous = min($this->max_simultaneous, $medias_count);
        $common_options = array(
            CURLOPT_HEADER         => 0,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        );

        for ($i = 0; $i < $max_simultaneous; $i++) {
            $item = $medias_to_download[$i];
            wp_mkdir_p(dirname($item['local_path']));
            $url = esc_url_raw($item['remote_url']);

            $ch = curl_init($url);
            curl_setopt_array($ch, $common_options);
            curl_multi_add_handle($mh, $ch);
            $key = (string) $ch;
            $conn[$key] = $i;
        }

        if (empty($conn)) {
            curl_multi_close($mh);
            return true;
        }
        // do {
        //     $mrc = curl_multi_exec($mh, $active);
        //     curl_multi_select($mh);
        // } while ($active > 0);

        do {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            if ($mrc != CURLM_OK) {
                break;
            }

            // a request was just completed -- find out which one
            while($done = curl_multi_info_read($mh)) {
                // get the info and content returned on the request
                $info = curl_getinfo($done['handle']);
                $data = curl_multi_getcontent($done['handle']);
                // send the return values to the callback function.

                $key = (string) $done['handle'];

                if (isset($conn[$key]) && ($done['result'] == CURLE_OK)) {
                    $key_value = $conn[$key];
                    $item = $medias_to_download[$key_value];
                    $g = $item['local_path'];

                    $fp = null;

                    try {
                        $fp = fopen($g, "w");
                        fwrite($fp, $data);
                    }
                    catch(Exception $e)
                    {
                        $succeeded = false;
                    }
                    if($fp != null)
                    {
                        fclose($fp);
                    }
                }

                if (($i < $medias_count) && isset($medias_to_download[$i])) {
                    $item = $medias_to_download[$i];
                    wp_mkdir_p(dirname($item['local_path']));
                    $url = esc_url_raw($item['remote_url']);


                    $ch = curl_init($url);
                    curl_setopt_array($ch, $common_options);
                    curl_multi_add_handle($mh, $ch);

                    $key = (string) $ch;
                    $conn[$key] = $i;

                    $i ++;
                }

                // remove the curl handle that just completed
                curl_multi_remove_handle($mh, $done['handle']);
                curl_close($done['handle']);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active) {
                curl_multi_select($mh, $this->timeout);
            }

        } while ($active);

        curl_multi_close($mh);

        return $succeeded;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function attach_meta_data($attach_id, $data)
    {
        if (empty($attach_id)) {
            return $data;
        }
        $mime_type = get_post_mime_type($attach_id);

        $data = array_merge(
            $data,
            array(
                'id' => $attach_id,
                'mime' => $mime_type,
            )
        );
        return $data;
    }

}
