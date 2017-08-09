<?php

if( !defined( 'ABSPATH') ) exit();

/**
 * Class class-jssor-slider-output
 * @author Neil.zhou
 */
class WP_Jssor_Slider_Output
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($attrs)
    {
        $attrs = array_merge(
            array(
                'id'            => '',
                'alias'         => '',
                'show_on_pages' => '' // 'homepage' or post/page id'
            ),
            $attrs
        );
        $this->slider_id = intval($attrs['id']);
        $this->slider_alias = sanitize_file_name($attrs['alias']);
        $this->show_on_pages = $attrs['show_on_pages'];
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function preview($slider_data = false)
    {
        if ($this->is_invalid_request()) {
            return $this->render_error('Invalid Request');
        }
        if (empty($slider_data)) {
            return $this->get_slider();
        }
        $slider_data = stripslashes($slider_data);
        $code_content = $this->get_remote_slider_code($this->slider_id, '', $slider_data);
        if (is_wp_error($code_content)) {
            return $this->render_error($code_content->get_error_message());
        }
        $html = $this->parse_code_to_html($code_content);
        if (is_wp_error($html)) {
            return $this->render_error($html->get_error_message());
        }
        return $this->addScriptLibraries($html);
    }


    /**
     * undocumented function
     *
     * @return void
     */
    public function is_put_in($emptyIsFalse = false)
    {
        $putIn = $this->show_on_pages;

		$putIn = strtolower($putIn);
		$putIn = trim($putIn);

        if($emptyIsFalse && empty($putIn)) {
			return false;
        }

		if($putIn == 'homepage') {		//filter by homepage
            if(is_front_page() == false) {
				return false;
            }
		} elseif(!empty($putIn)) { //case filter by pages
			$arrPutInPages = array();
            $arrPagesTemp = explode(",", $putIn);

			foreach($arrPagesTemp as $page){
				$page = trim($page);
                if(is_numeric($page) || $page == 'homepage') {
					$arrPutInPages[] = $page;
                }
			}
			if(!empty($arrPutInPages)) {

				//get current page id
				$currentPageID = "";
				if(is_front_page() == true){
					$currentPageID = 'homepage';
				}else{
					global $post;
                    if(isset($post->ID)) {
                        $currentPageID = $post->ID;
                    }
				}

				//do the filter by pages
                if(array_search($currentPageID, $arrPutInPages) === false) {
                    return false;
                }
			}
		}

		return true;
    }

	public function get_slider() {

        $output = '';

        if ($this->is_invalid_request()) {
            return $this->render_error('Invalid Request');
        }
		$isPutIn = $this->is_put_in();

        if($isPutIn == false) {
            return $output;
        }

        $slider_model = new WP_Jssor_Slider_Slider();
        $slider = false;
        if (!empty($this->slider_id)) {
            $slider = $slider_model->find($this->slider_id);
        } elseif(!empty($this->slider_alias)) {
            $slider = $slider_model->find_by_name($this->slider_alias);
        }

        if (empty($slider)) {
            return $this->render_error('the slider does not exist');
        }

        $upload = wp_upload_dir();
        if (!empty($slider['html_path']) && file_exists($upload['basedir'] . $slider['html_path'])) {
            return $this->addScriptLibraries(file_get_contents($upload['basedir'] . $slider['html_path']));
        }

        if (empty($slider['code_path']) || !file_exists($upload['basedir'] . $slider['code_path'])) {
            $code_path = $this->save_and_get_slider_code_path($slider);
            if (is_wp_error($code_path)) {
                return $this->render_error($code_path->get_error_message());
            }
        } else {
            $code_path = $slider['code_path'];
        }
        $abs_path = $upload['basedir'] . $code_path;

        if (!@file_exists($abs_path)) {
            return $this->render_error("no permission to write the file '$abs_path'");
        }

        $content = file_get_contents($abs_path);
        $html = $this->parse_code_to_html($content);
        if (is_wp_error($html)) {
            return $this->render_error($html->get_error_message());
        }

        $html_rel_path = WP_Jssor_Slider_Globals::UPLOAD_GENSLIDER_HTML . '/' . date('Y/m/') . $slider['id'] . '.html';
        $html_abs_path = $upload['basedir'] . $html_rel_path;

        wp_mkdir_p(dirname($html_abs_path));

        if(@file_put_contents($html_abs_path, $html)) {
            $sliderModel = new WP_Jssor_Slider_Slider();
            $sliderModel->save(array(
                'html_path' => $html_rel_path,
                'id' => $slider['id']
            ));
        }
        return $this->addScriptLibraries($html);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function render_error($error)
    {
        $slider_name = empty($this->slider_alias) ? 'Unknown-slider' : $this->slider_alias;
        $error = empty($error) ? 'Unknown error' : $error;
        return "Render the slider '$slider_name' error: " . $error . ", Please refresh page and try again.";
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function is_invalid_request()
    {
        if (empty($this->slider_id) && empty($this->slider_alias)) {
            return true;
        }
        return false;
    }

    private function save_and_get_slider_code_path($slider)
    {
        if (empty($slider) || empty($slider['id']) || empty($slider['file_path'])) {
            return new WP_Error('EMPTY-SLIDER', 'Invalid Request');
        }
        $upload = wp_upload_dir();
        $slider_content_path = $upload['basedir'] . $slider['file_path'];

        if (!file_exists($slider_content_path)) {
            return new WP_Error('NO-SLIDER-FILE', 'The slider file does not exist in server.');
        }
        $slider_content = file_get_contents($slider_content_path);

        $code_content = $this->get_remote_slider_code($slider['id'], $slider['file_name'], $slider_content);
        if (is_wp_error($code_content)) {
            return $code_content;
        }

        $upload = wp_upload_dir();
        $rel_dir = WP_Jssor_Slider_Globals::UPLOAD_GENCODES . '/' . date('Y/m');
        $abs_dir = $upload['basedir'] . $rel_dir;
        if (!wp_mkdir_p($abs_dir)) {
            return new WP_Error('NO-PERMISSION', 'No permission to create directory.');
        }

        $rel_path = $rel_dir. '/' . $slider['id']. '.code';
        $abs_path = $upload['basedir'] . $rel_path;

        if(!@file_put_contents($abs_path, $code_content)) {
            return new WP_Error('WRITE-FILE-FAILED', 'Write slider codes to file failed.');
        }
        $sliderModel = new WP_Jssor_Slider_Slider();

        $status = $sliderModel->save(array(
            'code_path' => $rel_path,
            'id' => $slider['id']
        ));
        if ($status === false) {
            return new WP_Error('SAVE-CODE-PATH-FAILED', $sliderModel->last_error());
        }
        return $rel_path;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function parse_code_to_html($code_content)
    {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-wjssl-expression-factory.php';
        $content_arr = json_decode($code_content, true);

        try {
            $factory = WjsslExpressionFactory::create_expression($content_arr);
            return $factory->interpret();
        } catch (Exception $e) {
            return new WP_Error($e->getCode(), $e->getMessage());
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function get_remote_slider_code($slider_id, $slider_name, $slider_content)
    {
        $url = WP_Jssor_Slider_Globals::URL_JSSOR_SECURE() . WP_Jssor_Slider_Globals::URL_JSSOR_GENCODE;
        $acckey = get_option('wjssl_acckey', '');
        $instance_id = get_option('wp_jssor_slider_instance_id', '');
        $data = array(
            'jssorext' => WP_JSSOR_SLIDER_EXTENSION_NAME,
            'hosturl' => esc_url_raw(WP_Jssor_Slider_Globals::get_jssor_wordpress_site_url()),
            'instid' => $instance_id,
            'acckey' => $acckey,
            'id' => $slider_id,
            'name' => $slider_name,
            'content' => $slider_content,
            'options' => array(
                'minimizeCss' => 0,
                'scriptFormat' => 0
            )
        );

        $params = array('data' => json_encode($data));
        $headers = array();
        if (function_exists('gzencode')) {
            $params = gzencode(http_build_query($params));
            $headers = array('Content-Encoding' => 'gzip');
        }

        $response = wp_remote_post(esc_url_raw($url), array(
            'body' => $params, 
            'headers' => $headers, 
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }
        $response_arr = json_decode($response['body']);

        if (!empty($response_arr->error)) {
            return new WP_Error($response_arr->error, $response_arr->message);
        }

        //it works still even if not registered
        //if (!empty($response_arr['regstatus'])) {
        //    $error_map = array(
        //        1 => 'Not registered',
        //        2 => 'Invalid',
        //        3 => 'Host url does not match'
        //    );
        //    $error = isset($error_map[$response_arr['regstatus']]) ?
        //        $error_map[$response_arr['regstatus']] : 'Other error';
        //    return new WP_Error($response_arr['regstatus'], $error);
        //}

        if (is_array($response_arr->code) || is_object($response_arr->code)) {
            $response_arr->code = json_encode($response_arr->code);
        }

        if (empty($response_arr->code)) {
            return new WP_Error('EMPTY-CODE-CONTENT', __("Service is temporarily unavailable, please try again later.", WP_JSSOR_SLIDER_DOMAIN));
        }
        return $response_arr->code;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function addScriptLibraries($content)
    {
        $html = '';
        $upload = wp_upload_dir();
        $script_name = 'jssor.slider';
        $src_prefix = $upload['baseurl'] . WP_Jssor_Slider_Globals::UPLOAD_SCRIPTS;
        $min_js_path = $src_prefix . '/' . $script_name . '-' . WP_JSSOR_MIN_JS_VERSION . '.min.js';
        $html .= "<script src='$min_js_path' data-library='$script_name' data-version='" . WP_JSSOR_MIN_JS_VERSION . "'></script>";
        return $html . $content;
    }

}
