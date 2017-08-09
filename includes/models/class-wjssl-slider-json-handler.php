<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-slider-parser.php';
include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-slider-medias-downloader.php';
include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-medias-saver.php';

/**
 * Class WjsslSliderJsonHandler
 * @author Neil.zhou
 */
class WjsslSliderJsonHandler
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($context)
    {

        $this->processor = $context['processor'];
        $this->json = $context['json'];

        $this->parser = new WjsslSliderParser($this->json);
        $this->slider_model = $context['slider_model'];
    }

    /**
     * undocumented function
     *
     * @return void
     */
    protected function parser()
    {
        return $this->parser;
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public function rebuild()
    {
        try {
            return $this->import_medias();
        } catch (Exception $e) {
            return new WP_Error($e->getCode(), $e->getMessage());
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getJson()
    {
        return $this->parser()->getJson();
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getThumb()
    {
        return $this->parser()->getThumb();
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function import_medias()
    {
        $this->processor()->arrive_at(30, __('Downloading slider images ...', WP_JSSOR_SLIDER_DOMAIN), $this->slider_model()->data_value('file_name'));

        $download_urls = $this->parser()->getMedias();

        $downloader = new WjsslSliderMediasDownloader();

        $downloader->multi_download($download_urls);

        $this->processor()->arrive_at(70, __('Generating slider media resources ...', WP_JSSOR_SLIDER_DOMAIN), $this->slider_model()->data_value('file_name'));

        $download_urls = $downloader->generate_metadatas($download_urls);

        $this->parser()->alter_medias($download_urls);

        return true;
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
