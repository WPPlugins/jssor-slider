<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

header_remove("X-Frame-Options");
header('Jssor-Extension: MEDIABROWSER');

if(!is_user_logged_in())
{
    $output = json_encode(array(
        'error' => 1,
        'message' => 'Login Required.'
        )
    );

    echo '/*json*/';
    echo $output;
    die;
}

if(!current_user_can('manage_options')) {
    $output = json_encode(array(
        'error' => 2,
        'message' => 'Permission deinied to open media browser.'
        )
    );

    echo '/*json*/';
    echo $output;
    die;
}

ob_clean();
ob_end_clean();
?>

<?php wp_enqueue_media(); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php wp_print_styles(); ?>
    <?php wp_print_head_scripts(); ?>
</head>
<body <?php body_class(); ?>>
    <div id="page" class="hfeed site">
        <div id="content" class="site-content">
            <?php wp_print_footer_scripts(); ?>
            <?php /*wp_underscore_playlist_templates();*/ ?>
            <?php wp_print_media_templates(); ?>

            <?php
            function get_jssor_media_init_options()
            {
                $init_options = new WP_Jssor_Slider_API_Info();

                if(!is_user_logged_in())
                {
                    $init_options->error = 1;
                    $init_options->message = "You are not logged in, please <a href='" . admin_url() . "' target='_blank'>login</a> and then try again.";
                }

                return $init_options;
            }
            ?>

            <script>
                new wp_jssor_media_browser_init(<?php echo json_encode(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_info()); ?>, <?php echo json_encode(get_jssor_media_init_options()); ?>);
            </script>
        </div>
    </div>
</body>
</html>
