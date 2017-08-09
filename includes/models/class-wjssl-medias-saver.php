<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Class WjsslMediasSaver
 * @author Neil.zhou
 */
class WjsslMediasSaver
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($medias)
    {
        $this->medias = $medias;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getMedias()
    {
        return $this->medias;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function filte()
    {
        $download_urls = $this->getMedias();
        $added_urls = array();
        foreach ($download_urls as $key => $item) {
            $filename = $item['local_path'];
            $filename = $this->get_media_name(basename($filename));
            if (
                $this->is_self_site($item['remote_url'])
                ||
                (array_search($item['remote_url'], $added_urls) !== false)
                ||
                (
                    file_exists($item['local_path'])
                    &&
                    $this->is_attachment_existed($filename, $item['local_url'])
                )
            ) {
                unset($download_urls[$key]);
            } else {
                $added_urls[] = $item['remote_url'];
            }
        }
        $this->medias = $download_urls;
        unset($download_urls);
        return $this->getMedias();
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function save()
    {
        $download_urls = $this->getMedias();

        foreach ($download_urls as $item) {
            if (file_exists($item['local_path'])) {
                $this->save_media($item);
            } else {
                throw new Exception(__('Some error happened when download media['.$item['remote_url'] .']', WP_JSSOR_SLIDER_DOMAIN), 'NO-PERMISSION');
            }
        }
        return true;
    }

    private function is_attachment_existed($title, $guid) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare(
            "SELECT count(*) FROM $wpdb->posts WHERE guid = %s AND post_title = %s AND post_type = 'attachment' ", $guid, $title
        ));
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

    /**
     * undocumented function
     *
     * @return void
     */
    private function save_media($item)
    {
        // $filename should be the path to a file in the upload directory.
        $filename = $item['local_path'];

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype( basename( $filename ), null );
        if ($this->is_attachment_existed($this->get_media_name(basename($filename)), $item['local_url'])) {
            return true;
        }

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid'           => $item['local_url'],
            'post_mime_type' => $filetype['type'],
            'post_title'     => $this->get_media_name(basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $filename);

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        return true;
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

}
