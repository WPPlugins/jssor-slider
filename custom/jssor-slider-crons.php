<?php

    // Exit if accessed directly
    if( !defined( 'ABSPATH') ) exit();

    add_filter('cron_schedules', 'wjssl_add_one_minutes_cron');
    add_action( 'wjssl_check_slider_files_hook',  'wjssl_check_slider_files');

    if ( ! wp_next_scheduled( 'wjssl_check_slider_files_hook' ) ) {
        wp_schedule_event( time(), 'wjssl_minutely', 'wjssl_check_slider_files_hook' );
    }

    /**
     * undocumented function
     *
     * @return void
     */
    function wjssl_add_one_minutes_cron($schedules)
    {
        $schedules['wjssl_minutely'] = array(
            'interval' => 60,
            'display' => esc_html__('Every One Minute', WP_JSSOR_SLIDER_DOMAIN),
        );
        return $schedules;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    function wjssl_check_slider_files()
    {
       $slider_model = new WP_Jssor_Slider_Slider();
       $results = $slider_model->find_sliders_without_files(7);
       if (!class_exists('WP_Jssor_Slider_Output')) {
          require_once plugin_dir_path( __FILE__ ) . 'includes/class-jssor-slider-output.php';
       }
       foreach ($results as $item) {
           $attrs = array(
               'id' => $item->id,
               'alias' => $item->file_name
           );
           $object = new WP_Jssor_Slider_Output($attrs);
           $object->get_slider();
       }
       return true;
    }
