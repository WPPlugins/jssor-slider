<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes/modal
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 * @author     Your Name <email@example.com>
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WP_Jssor_Slider_Slider {
    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($data = array())
    {
        global $wpdb;
        $this->table = $wpdb->prefix . WP_Jssor_Slider_Globals::TABLE_SLIDERS;
        $this->rebuild_data($data);
        $this->data = $data;
        $this->error = '';

        if (empty($data['id'])) {
            $this->id = 0;
        } else {
            $this->id = $data['id'];
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function find_sliders_without_files($limit = 10)
    {
        if (empty($limit) || !is_int($limit)) {
            $limit = 10;
        }
        global $wpdb;
        $table = $wpdb->prefix . WP_Jssor_Slider_Globals::TABLE_SLIDERS;
        return $wpdb->get_results(
            "SELECT * FROM $table WHERE code_path = '' or html_path = '' ORDER BY id desc LIMIT $limit"
            );
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public static function clear_all_code_html_path()
    {
        global $wpdb;
        $table = $wpdb->prefix . WP_Jssor_Slider_Globals::TABLE_SLIDERS;
        return $wpdb->query(
            "UPDATE $table SET `code_path` = '', `html_path` = ''"
            );
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public static function update_all($data, $where, $format = null, $where_format = null)
    {
        global $wpdb;
        $table = $wpdb->prefix . WP_Jssor_Slider_Globals::TABLE_SLIDERS;
        return $wpdb->update($table, $data, $where, $format, $where_format);
    }

    public function save($data = array(), $need_validate = true)
    {
        global $wpdb;

        if (!empty($data['id'])) {
            $this->id = $data['id'];
        }

        $now = date('Y-m-d H:i:s');
        $data = array_merge(
            array(
                'updated_at' => $now
            ),
            $data
        );
        $this->data = array_merge($this->data, $data);
        $this->rebuild_data($this->data);

        if (!empty($need_validate) && !$this->validate()) {
            return false;
        }

        if (!empty($this->id)) {
            unset($this->data['id']);
            return $this->update($this->data, array('id' => $this->id));
        }

        $this->data = array_merge(array(
            'created_at' => $now,
        ), $this->data);

        // Insert slider, WPDB will escape data automatically
        $status = $wpdb->insert($this->table, $this->data);
        if ($status) {
            // Return insert database ID
            $this->id = $wpdb->insert_id;
        }
        else {
            $db_error = $wpdb->last_error;
            if(empty($db_error)) {
                $db_error = 'Failed to insert ' . $this->data['file_name'] . ' into database.';
            }
            $this->error = $db_error;
        }

        return $status;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function set_value($key, $value)
    {
        $this->data[$key] = $value;
        $this->rebuild_data($this->data);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function set_data($data)
    {
        $this->data = array_merge($this->data, $data);
        $this->rebuild_data($this->data);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function get_data()
    {
        return $this->data;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function clear_data()
    {
        $this->data = array();
        $this->id = 0;
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public function find($id = false)
    {
        if (empty($id)) {
            $id = $this->id;
        }
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "
            SELECT *
            FROM $this->table
            WHERE id = %d
            ",
            $id
        ), ARRAY_A );

        if (empty($row)) {
            $this->id = 0;
            $row = array('id' => 0);
        } else{
            $this->id = $row['id'];
        }

        if (empty($this->data)) {
            $this->data = array();
        }
        $this->data = array_merge($this->data, $row);
        return $row;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function is_name_existed($filename = '')
    {
        if (empty($id)) {
            $id = $this->id;
        }
        if (empty($filename)) {
            $filename = $this->data['file_name'];
        }
        $filename = $this->reset_slider_name($filename);
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare(
            "
            SELECT count(*)
            FROM $this->table
            WHERE file_name = %s
            AND id <> %d
            ",
            $filename,
            $id
        ) );
    }

    /**
     * find by name
     *
     * @return void
     */
    public function find_by_name($name)
    {
        global $wpdb;
        $name = $this->reset_slider_name($name);
        $row = $wpdb->get_row( $wpdb->prepare(
            "
            SELECT *
            FROM $this->table
            WHERE file_name = %s
            ",
            $name
        ), ARRAY_A );

        if (empty($row)) {
            $this->id = 0;
            $row = array('id' => 0);
        } else{
            $this->id = $row['id'];
        }
        if (empty($this->data)) {
            $this->data = array();
        }
        $this->data = array_merge($this->data, $row);
        return $row;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function validate()
    {
        $this->error = '';
        $name = $this->data_value('file_name');
        if ('new.slider' === strtolower($name)) {
            $this->error = 'The new.slider is reserved word, please specify another name.';
            return false;
        } else if(preg_match('/[\?:\*"\'<>\|%\/\\\]/', $name)) {
            $this->error = 'The slider name could not contain these characters: \ / : * ? " \' < > | %';
            return false;
        }
        return true;
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public function update($data, $where)
    {
        global $wpdb;
        $this->rebuild_data($data);

        $data = array_merge(array(
            'updated_at' => date('Y-m-d H:i:s')
        ), $data);

        return $wpdb->update($this->table, $data, $where);
    }

    /**
     * delete
     *
     * @return void
     */
    public function delete($id = false)
    {
        if (empty($id) && !empty($this->id)) {
            $id = $this->id;
        }
        $slider = $this->find($id);

        global $wpdb;
        $status = $wpdb->delete($this->table, array( 'id' => $id ), array('%d') );
        if ($status && !empty($slider)) {
            $uplode = wp_upload_dir();
            if (!empty($slider['file_path'])) {
                @unlink($uplode['basedir'] . $slider['file_path']);
            }
            if (!empty($slider['code_path'])) {
                @unlink($uplode['basedir'] . $slider['code_path']);
            }
        }
        return $status;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function is_template_file($file_path)
    {
        if ($file_path === false && isset($this->data['file_path'])) {
            $file_path = $this->data['file_path'];
        }
        return WP_Jssor_Slider_Globals::is_jssor_template_path($file_path);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function data_value($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function last_error()
    {
        if (empty($this->error)) {
            return $this->last_db_error();
        }
        return $this->error;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function last_db_error()
    {
        global $wpdb;
        return $wpdb->last_error;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function generate_thumbs()
    {
        $data = $this->data;
        if (empty($data['thumb_path'])) {
            $this->error = 'No thumb path.';
            return false;
        }

        try {
            include_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-slider-thumb-generator.php';
            $generator = new WjsslSliderThumbGenerator($data['thumb_path']);
            if(!$generator->media_exists())
                return false;

            $generator->ensure_thumb_sizes();
            $this->data['grid_thumb_path'] = $generator->get_grid_thumb_rel_path();
            $this->data['list_thumb_path'] = $generator->get_list_thumb_rel_path();

            //$path = $generator->run(array('width' => 220, 'height' => 160));
            //$this->data['grid_thumb_path'] = $path['rel_path'];

            //$path = $generator->run(array('width' => 80, 'height' => 31));
            //$this->data['list_thumb_path'] = $path['rel_path'];

        } catch(Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function grid_thumb_url()
    {
        $upload = wp_upload_dir();
        $grid_filepath = $this->data_value('grid_thumb_path');

        if (empty($grid_filepath)) {
            return '';
        }

        $grid_filepath = $upload['baseurl'] . $grid_filepath;
        return $grid_filepath;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function list_thumb_url()
    {

        $upload = wp_upload_dir();
        $list_filepath = $this->data_value('list_thumb_path');

        if (empty($list_filepath)) {
            return '';
        }

        $list_filepath = $upload['baseurl'] . $list_filepath;
        return $list_filepath;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function delete_code_html_files()
    {
        $files = array();
        $files[] = $this->data_value('code_path');
        $files[] = $this->data_value('html_path');

        foreach ($files as $rel_file) {
            WP_Jssor_Slider_Utils::delete_slider_relative_file($rel_file);
        }
        return true;
    }


    /**
     * undocumented function
     *
     * @return void
     */
    private function rebuild_data(&$data)
    {
        if (isset($data['file_name'])) {
            $data['file_name'] = $this->reset_slider_name($data['file_name']);
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function has_slider_suffix($filename)
    {
        return strtolower(substr($filename, -7)) === '.slider';
    }

    private function reset_slider_name($filename)
    {
        $filename = sanitize_file_name($filename);
        if (!$this->has_slider_suffix($filename)) {
            return $filename . '.slider';
        }
        return $filename;
    }
}
