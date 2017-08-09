<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 * @author     Your Name <email@example.com>
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WP_Jssor_Slider_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        self::common();
	}

    /**
     * update when load plugins
     *
     * @return void
     */
    public static function update()
    {
        self::common();
    }

    /**
     * common functions called on activate & update plugin.
     *
     * @return void
     */
    private static function common()
    {
        if (null === get_option('wp_jssor_slider_instance_id', null)) {
            update_option('wp_jssor_slider_instance_id', WP_Jssor_Slider_Utils::create_guid());
        }

        $cur_ver = get_option('wp_jssor_slider_db_version', '1.0.0');
        if (version_compare($cur_ver, WP_JSSOR_SLIDER_VERSION) < 0) {
            self::overwrite_resources();
        } else {
            self::create_upload_folders();
        }

        self::migrate_dbs();
        self::check_slider_script();
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private static function overwrite_resources()
    {
        $upload_dir_paths = wp_upload_dir();

        $upload_slider_dir = $upload_dir_paths['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_SLIDER;
        if (! is_dir($upload_slider_dir)) {
            wp_mkdir_p($upload_slider_dir);
        }

        $resource_dir = realpath(WP_JSSOR_SLIDER_PATH . WP_Jssor_Slider_Globals::DIR_RESOURCES_UPLOAD);
        $todir = $upload_dir_paths['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR;

        if (! is_dir($todir)) {
            wp_mkdir_p($todir);
        }

        self::rcopy($resource_dir, $todir);
    }

    /**
     * create upload folders for jssor-slider
     *
     * @return void
     */
    private static function create_upload_folders()
    {
        $upload_dir_paths = wp_upload_dir();

        $upload_slider_dir = $upload_dir_paths['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_SLIDER;
        if (! is_dir($upload_slider_dir)) {
            wp_mkdir_p($upload_slider_dir);
        }

        $resource_dir = realpath(WP_JSSOR_SLIDER_PATH . WP_Jssor_Slider_Globals::DIR_RESOURCES_UPLOAD);
        $todir = $upload_dir_paths['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_JSSOR;

        if (! is_dir($todir)) {
            wp_mkdir_p($todir);
        }
        $files = scandir($resource_dir);
        foreach ($files as $f) {
            if ($f == '.' || $f == '..') {
                continue;
            }
            $to = $todir . '/' . $f;
            $from = $resource_dir . '/' . $f;
            if (file_exists($to)) {
                continue;
            }
            self::rcopy($from, $to);
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private static function check_slider_script()
    {
        $upload_dir = wp_upload_dir();
        $script_path = $upload_dir['basedir'] . WP_Jssor_Slider_Globals::UPLOAD_SCRIPTS;
        $script_name = 'jssor.slider' . '-' . WP_JSSOR_MIN_JS_VERSION . '.min.js';
        $script_file = $script_path . '/' . $script_name;

        if (file_exists($script_file)) {
            return true;
        }

        $resource_dir = realpath(WP_JSSOR_SLIDER_PATH . WP_Jssor_Slider_Globals::DIR_RESOURCES_SCRIPT);
        $target_file = $resource_dir . '/' . $script_name;
        if ($resource_dir && file_exists($target_file)) {
            self::rcopy($target_file, $script_file);
            return true;
        }
        return false;
    }
    

    private static function migrate_dbs() {
        global $wpdb;
        $cur_ver = get_option('wp_jssor_slider_db_version', '1.0.0');

        if (version_compare($cur_ver, '1.0.13') < 0) {
            $table_name = $wpdb->prefix . WP_Jssor_Slider_Globals::TABLE_SLIDERS;
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                file_name varchar(100) NOT NULL default '',
                file_path varchar(255) NOT NULL default '',
                code_path varchar(255) NOT NULL default '',
                html_path varchar(255) NOT NULL default '',
                thumb_path text,
                grid_thumb_path text,
                list_thumb_path text,
                created_at datetime,
                updated_at datetime,
                PRIMARY KEY  (id),
                UNIQUE KEY file_name (file_name)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
        update_option('wp_jssor_slider_db_version', WP_JSSOR_SLIDER_VERSION);
    }

    // copies files and non-empty directories
    private static function rcopy($src, $dst) {
        if (is_dir($src)) {
            wp_mkdir_p($dst);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    self::rcopy("$src/$file", "$dst/$file");
                }
            }
        } else if (file_exists($src)) {
            copy($src, $dst);
        }
    }
}
