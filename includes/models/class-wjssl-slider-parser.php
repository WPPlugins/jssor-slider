<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Class WjsslSliderParser
 * @author Neil.zhou
 */
class WjsslSliderParser
{
    private $slider = null;
    private $medias = array();
    private $thumb = null;
    private $altered = false;

    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($json)
    {
        if (is_string($json)) {
            $this->slider = json_decode($json);
        } else {
            $this->slider = $json;
        }
        $this->rebuild();

        $this->thumb = WP_Jssor_Slider_Utils::extract_slider_thumbnail_image($this->slider);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getSlider()
    {
        return $this->slider;
    }

    public function getJson()
    {
        return json_encode($this->slider);
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

    public function alter_medias($medias)
    {
        $this->medias = $medias;
        $this->rebuild();
        $this->altered = true;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getThumb()
    {
        $thumb_url = $this->thumb;

        if(!empty($thumb_url)) {
            $media_record = $this->ensure_media_record($this->thumb);
            if(!empty($media_record)) {
                if(!empty($media_record['local_url_plus'])) {
                    $thumb_url = $media_record['local_url_plus'];
                }
                else if(!empty($media_record['local_url'])) {
                    $thumb_url = $media_record['local_url'];
                }
                else if(!empty($media_record['remote_url'])) {
                    $thumb_url = $media_record['remote_url'];
                }
            }
        }

        return $thumb_url;
    }

    private function ensure_media_record($rel_remote_url)
    {
        $rel_remote_url = strtolower($rel_remote_url);

        if(isset($this->medias[$rel_remote_url]))
        {
            $path = $this->medias[$rel_remote_url];
        }
        else {
            $path = $this->build_media_path($rel_remote_url);
            $this->medias[$rel_remote_url] = $path;
        }

        return $path;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function rebuild()
    {
        if($this->altered)
            throw new Exception("Media has been altered.");

        $json = $this->slider;
        if (!empty($json->slides)) {
            foreach ($json->slides as $key=>$slide) {
               if (!empty($slide->image)) {
                   $path = $this->ensure_media_record($slide->image);
                   if(array_key_exists('local_url_plus', $path))
                   {
                        $slide->image = $path['local_url_plus'];
                   }
               }

               if (!empty($slide->thumb) && !empty($slide->thumb->images)) {
                   foreach ($slide->thumb->images as $t_key => $t_value) {
                       if (empty($t_value)) {
                           continue;
                       }
                       $path = $this->ensure_media_record($t_value);
                       if(array_key_exists('local_url_plus', $path))
                       {
                           $slide->thumb->images[$t_key] = $path['local_url_plus'];
                       }
                   }
               }
               if (!empty($slide->layers)) {
                   foreach($slide->layers as $l_key => $l_layer) {
                       $slide->layers[$l_key] = $this->iterator_rebuild($l_layer);
                   }
               }
               $json->slides[$key] = $slide;
            }
        }
        $this->slider = $json;
    }

    private function build_media_path($rel_remote_url) {
        $jssor_path = WP_Jssor_Slider_Globals::upload_dir(WP_Jssor_Slider_Globals::UPLOAD_JSSOR);
        $jssor_url = WP_Jssor_Slider_Globals::URL_JSSOR;

        $path = array('original_url' => $rel_remote_url);

        // if remote_url is https:// or http://
        if (filter_var($rel_remote_url, FILTER_VALIDATE_URL) !== FALSE) {
            $path['remote_url'] = esc_url_raw($rel_remote_url);
            $path['is_absolute_url'] = true;

            $array = WP_Jssor_Slider_Utils::parse_url($rel_remote_url);

            if(strcasecmp($array['host'], 'jssor.com') === 0 || strcasecmp($array['host'], 'www.jssor.com') === 0)
            {
                $url_path = $array['path'];
                $url_path_esc = esc_url_raw( $url_path);

                if(empty($url_path_esc))
                {
                    //ignore invalid url
                    $path['ignore'] = true;
                    $path['ignore_download'] = true;
                }
                else {
                    $path['remote_url'] = $jssor_url . $url_path_esc;
                    $path['local_url'] = $jssor_path['baseurl'] . $url_path_esc;
                    $path['local_path'] = $jssor_path['basedir'] . $url_path_esc;
                    $path['local_rel_path'] = WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $url_path_esc;
                }
            }
            else {
                //ignore url not refers to jssor.com
                $path['ignore'] = true;
                $path['ignore_download'] = true;
            }
        }
        else if(substr($rel_remote_url, 0, 1) === "/") {
            $url_path = $rel_remote_url;
            $url_path_esc = esc_url_raw( $url_path);

            if(empty($url_path_esc))
            {
                //ignore invalid url
                $path['ignore'] = true;
                $path['ignore_download'] = true;
            }
            else {
                $path['remote_url'] = $jssor_url . $url_path_esc;
                $path['local_url'] = $jssor_path['baseurl'] . $url_path_esc;
                $path['local_path'] = $jssor_path['basedir'] . $url_path_esc;
                $path['local_rel_path'] = WP_Jssor_Slider_Globals::UPLOAD_JSSOR . $url_path_esc;
            }
        }
        else {
            //ignore invalid url
            $path['ignore'] = true;
            $path['ignore_download'] = true;
        }

        return $path;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function iterator_rebuild($layer)
    {
        if (!empty($layer->image)) {
            $layer->image = $this->change_resource_url($layer->image);
        }

        if (!empty($layer->bgImage)) {
            $layer->bgImage = $this->change_resource_url($layer->bgImage);
        }

        if (!empty($layer->children)) {
            foreach ($layer->children as $key => $child) {
                $layer->children[$key] = $this->iterator_rebuild($child);
            }
        }

        return $layer;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function change_resource_url($resource_url)
    {
        if (empty($resource_url)) {
            return $resource_url;
        }
        $path = $this->ensure_media_record($resource_url);
        if(array_key_exists('local_url_plus', $path))
        {
            return $path['local_url_plus'];
        }
        return $resource_url;
    }

}
