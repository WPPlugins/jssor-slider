<?php
/*
 * input parameters
 * import: {
 *     src: "source url",
 *     name: "filename.slider",
 *     overwrite: 0
 * }
 *
 * output methods:
 * progress(job, progress, item) {
 *    //job: current job that working on
 *    //progress: overall progress. e.g. 0.25 means 25%
 *    //item: current item that working on
 * }
 *
 * fail(errorCode, message) {
 *     //errorCode:
 *       1: not authenticated, login required
 *       2: authenticated, permission deinied
 *       3: slider exists already
 *     //message: detailed error message
 * }
 *
 * success(id, filename, thumbnailurl) {
 *     //id: id of the slider
 *     //filename: file name
 *     //thumbnailurl: thumbnail url of slider
 * }
 *
 * @version 1.0
 * @author jssor
*/

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

ini_set('zlib.output_compression', 0);
ini_set('output_buffering', 0);
ini_set('implicit_flush', 1);
set_time_limit(30 * 60);

header('X-Accel-Buffering: no');
//header('Content-Encoding: utf-8');
//header('Content-Encoding: none;');
//header('Transfer-Encoding: chunked');
header('Content-type: text/html; charset=utf-8' );
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

@ob_end_flush();
@ob_implicit_flush(1);

include(WP_JSSOR_SLIDER_PATH . 'custom/class-jssor-slider-custom.php');
require_once WP_JSSOR_SLIDER_PATH . 'custom/import/class-wp-jssor-push-processor.php';

$jssor_push = new WP_Jssor_Push();
$jssor_push->write('<!DOCTYPE html>');
$jssor_push->write('<html xmlns="http://www.w3.org/1999/xhtml">');
$jssor_push->write('<head>');
$jssor_push->write('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');

//write script library

$push_server_scripts = WP_Jssor_Slider_Condition::get_push_server_scripts();
foreach($push_server_scripts as $script) {
    $jssor_push->write('<script type="text/javascript" src="'. $script . '"></script>');
}

//$push_server_script_paths = WP_Jssor_Slider_Condition::get_push_server_script_paths();
//foreach($push_server_script_paths as $script_path) {
//    $jssor_push->write('<script type="text/javascript">');
//    $file = file_get_contents(WP_JSSOR_SLIDER_PATH . $script_path);
//    $jssor_push->write($file);
//    $jssor_push->write('</script>');
//}

$jssor_push->write('</head>');
$jssor_push->write('<body>');

$jssor_push->write('<script>wp_jssor_push_server_init();</script>');

$jssor_push->begin();

//start to push progress
$processor = new WP_Jssor_Push_Processor(array('jssor_push' => $jssor_push));

$error = '';
$array = json_decode(stripslashes(sanitize_text_field($_GET['import'])), true);

if (
    empty($array)
    ||
    empty($array['filename'])
    ||
    empty($array['src'])
) {
    $error = __("The request is invalid.", WP_JSSOR_SLIDER_DOMAIN);

}

// validate
if (empty($error)) {
    $array['filename'] = sanitize_file_name($array['filename']);

    if (!current_user_can('manage_options')) {
        $error = __("Permission Denied!", WP_JSSOR_SLIDER_DOMAIN);

    } elseif (empty($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'wjssl-import')) {
        $error = __("The request is invalid.", WP_JSSOR_SLIDER_DOMAIN);

    } elseif (empty($array['filename'])) {
        $error = __("Please input slider name", WP_JSSOR_SLIDER_DOMAIN);
    }
}


if ($error) {
    $jssor_push->push('fail', array(2, $error));
} else {

    $slider_model = new WP_Jssor_Slider_Slider(array('file_name' => $array['filename']));

    if (!$slider_model->validate()) {
        $jssor_push->push('fail', array(2, $slider_model->last_error()));

    } elseif(empty($array['overwrite']) && $slider_model->is_name_existed()) {
        $jssor_push->push('fail', array(3, $slider_model->data_value('file_name') . ' exists already.'));
    } else {
        $processor->arrive_at(10, 'Start importing ...', $slider_model->data_value('file_name'));

        require_once WP_JSSOR_SLIDER_PATH . 'custom/import/class-jssor-slider-importer.php';
        $context = array(
            'remote_slider' => $array['src'],
            'processor' => $processor,
            'slider_name' => $array['filename'],
            'slider_model' => $slider_model
        );
        $importer = new WP_Jssor_Slider_Importer($context);
        $status = $importer->import();

        if (is_wp_error($status)) {
            $jssor_push->push('fail', array(2, $status->get_error_message()));
        } else {

            $title = $slider_model->data_value('file_name');
            $slider_id = $slider_model->id;
            $slider_edit_url = WP_Jssor_Slider_Globals::get_jssor_edit_slider_url($slider_id, $title);
            $slider_preview_url = WP_Jssor_Slider_Globals::get_jssor_preview_slider_url($slider_id, $title);

            $sliderInfo = array(
                    'slider_id' => $slider_id,
                    'slider_name' => $title,
                    'edit_url' => $slider_edit_url,
                    'preview_url' => $slider_preview_url,
                    'grid_thumb_url' => $slider_model->grid_thumb_url(),
                    'list_thumb_url' => $slider_model->list_thumb_url(),
                    'shortcode' => WP_Jssor_Slider_Globals::get_shortcode_templ($slider_id, $title),
                    'message' => __('The slider['.$title.'] imported successfully!', WP_JSSOR_SLIDER_DOMAIN),
                );

            $jssor_push->push('success', array($sliderInfo));
        }
    }
}


//stop pushing progress
$jssor_push->end();

$jssor_push->write('</body>');
$jssor_push->write('</html>');

$jssor_push->close();
?>
