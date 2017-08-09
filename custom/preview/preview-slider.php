<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

$filename = empty($_GET['filename']) ? '' : sanitize_file_name($_GET['filename']);
$slider_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Preview <?php echo esc_html($filename) ?></title>
</head>
<body style="margin:0;padding:0;font-family: Arial,Helvetica,Verdana,Geneva,sans-serif,-apple-system,BlinkMacSystemFont,'Open Sans',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue','Segoe UI',Tahoma;">
    <?php
if (empty($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'wjssl-preview')) {
    echo __("The request is invalid.", WP_JSSOR_SLIDER_DOMAIN);
} elseif (current_user_can('manage_options')) {
    $attrs = array();
    $attrs['id'] = $slider_id;
    $attrs['alias'] = $filename;
    $slider_data = empty($_POST['data']) ? false : $_POST['data'];

    $output = new WP_Jssor_Slider_Output($attrs);
    echo $output->preview($slider_data);
} else {
    echo __("Permission Denied!", WP_JSSOR_SLIDER_DOMAIN);
}
    ?>
</body>
</html>
