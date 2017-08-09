<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Class WjsslSliderThumbGenerator
 * @author Neil.zhou
 */
class WjsslSliderThumbGenerator
{
    private $image_url;
    private $attach_id;
    private $meta_data;
    private $rel_path;
    private $abs_path;

    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($image_url)
    {
        if(empty($image_url))
        {
            throw new IllegalArgumentException('$image_url is required');
        }

        $this->image_url = $image_url;
        $this->attach_id = WP_Jssor_Slider_Utils::get_image_id_by_url($image_url);

        if($this->attach_id > 0) {
            $meta_data = wp_get_attachment_metadata($this->attach_id);
            $this->meta_data = $meta_data;

            if(!empty($meta_data)) {
                $this->rel_path = '/' . $meta_data['file'];
                $upload = wp_upload_dir();
                $this->abs_path = $upload['basedir'] . $this->rel_path;
                $this->size = getimagesize($this->abs_path);
            }
        }
    }

    public function media_exists()
    {
        if(isset($this->abs_path)) {
            return file_exists($this->abs_path);
        }

        return false;
    }

    public function ensure_thumb_sizes()
    {
        if (!$this->media_exists()) {
            throw new FileNotFoundException($this->abs_path);
        }

        $thumb_sizes = WP_Jssor_Slider_Globals::get_jssor_slider_thumb_sizes();

        $thumb_sizes_to_generate = array();

        foreach($thumb_sizes as $thumb_key => $thumb_size) {
            $thumb_size_exists = isset($this->meta_data['sizes'], $this->meta_data['sizes'][$thumb_key]) ? $this->meta_data['sizes'][$thumb_key] : null;
            if(empty($thumb_size_exists)) {
                $thumb_sizes_to_generate[$thumb_key] = $thumb_size;
            }
        }
        if(!empty($thumb_sizes_to_generate)) {
            $image = wp_get_image_editor($this->abs_path); // Return an implementation that extends WP_Image_Editor
            if ( !is_wp_error( $image ) ) {
                $sizes_generated = $image->multi_resize($thumb_sizes_to_generate);
                $this->meta_data['sizes'] = array_merge($this->meta_data['sizes'], $sizes_generated);
                wp_update_attachment_metadata( $this->attach_id, $this->meta_data );
            }
            else {
                throw new WPErrorException($image);
            }
        }
    }

    private function get_thumb_rel_path($thumb_key)
    {
        $rel_path = null;

        $thumb_size = isset($this->meta_data['sizes'], $this->meta_data['sizes'][$thumb_key])
            ? $this->meta_data['sizes'][$thumb_key] : null;

        if(!empty($thumb_size)) {
            $dir = dirname($this->meta_data['file']);
            $rel_path = '/' . $dir . '/' . $thumb_size['file'];
        } elseif (!$this->can_resize_dimensions($thumb_key)) {
            $rel_path = $this->rel_path;
        }

        return $rel_path;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function can_resize_dimensions($thumb_key)
    {
        $thumb_sizes = WP_Jssor_Slider_Globals::get_jssor_slider_thumb_sizes();
        $key_size = isset($thumb_sizes[$thumb_key]) ? $thumb_sizes[$thumb_key] : null;
        if (empty($key_size) || empty($this->size)) {
            return false;
        }
        return image_resize_dimensions(
            $this->size[0],
            $this->size[1],
            $key_size['width'],
            $key_size['height'],
            $key_size['crop']
        );
    }
    
    public function get_grid_thumb_rel_path()
    {
        return $this->get_thumb_rel_path('jssor-grid-thumb');
    }

    public function get_list_thumb_rel_path()
    {
        return $this->get_thumb_rel_path('jssor-list-thumb');
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function run($size)
    {
        if (!file_exists($this->abs_path)) {
            throw new FileNotFoundException($this->abs_path);
        }
        $pathinfo = pathinfo($this->abs_path);
        $filename = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $size['width'] . 'x' . $size['height'] . '.' . $pathinfo['extension'];

        $upload = wp_upload_dir();
        $rel_filename = str_replace($upload['basedir'], '', $filename);
        $path = array(
            'original_path' => $this->rel_path,
            'abs_path' => $filename,
            'rel_path' => $rel_filename
        );

        if (file_exists($filename)) {
            return $path;
        }

        $image = wp_get_image_editor($this->abs_path); // Return an implementation that extends WP_Image_Editor
        if ( ! is_wp_error( $image ) ) {
            $image->resize($size['width'], $size['height'], true);

            $resp = $image->save($filename);
            if (!is_wp_error($resp)) {
                return $path;
            }
            throw new WPErrorException($resp);
        }
        throw new WPErrorException($image);
    }
}
