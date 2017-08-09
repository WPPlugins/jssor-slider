<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WJSSL_JSON_API {

  function __construct() {
    $this->query = new WJSSL_JSON_API_Query();
    //$this->introspector = new WJSSL_JSON_API_Introspector();
    $this->response = new WJSSL_JSON_API_Response();
    add_action('template_redirect', array(&$this, 'template_redirect'));
    //add_action('update_option_json_api_base', array(&$this, 'flush_rewrite_rules'));
    //add_action('pre_update_option_json_api_controllers', array(&$this, 'update_controllers'));
  }

  function template_redirect() {
    // Check to see if there's an appropriate API controller + method
    $controller = strtolower($this->query->get_controller());

    if ($controller) {

      if (empty($this->query->dev)) {
        error_reporting(0);
      }

      $controller_path = $this->controller_path($controller);
      if (file_exists($controller_path)) {
        require_once $controller_path;
      }
      $controller_class = $this->controller_class($controller);

      if (!class_exists($controller_class)) {
        $this->error("Unknown controller '$controller_class'.");
      }

      $this->controller = new $controller_class();
      $method = $this->query->get_method($controller);

      if ($method) {

        $this->response->setup();

        // Run action hooks for method
        do_action("wjssl_json_api", $controller, $method);
        do_action("wjssl_json_api-{$controller}-$method");

        // Error out if nothing is found
        if ($method == '404') {
          $this->error('Not found');
        }

        // Run the method
        $result = $this->controller->$method();

        // Handle the result
        $this->response->respond($result);

        // Done!
        exit;
      }
      else {
          $result = array('error' => 101, 'message' => 'Unsupported operation, please update jssor slider wordpress plugin.');
          $this->response->respond($result);
      }
    }
  }

  function get_method_url($controller, $method, $options = '') {
    $url = get_bloginfo('url');
    // $permalink_structure = get_option('permalink_structure', '');
    if (!empty($options) && is_array($options)) {
      $args = array();
      foreach ($options as $key => $value) {
        $args[] = urlencode($key) . '=' . urlencode($value);
      }
      $args = implode('&', $args);
    } else {
      $args = $options;
    }
    return "$url?method=$method&$args";
  }

  function save_option($id, $value) {
    $option_exists = (get_option($id, null) !== null);
    if ($option_exists) {
      update_option($id, $value);
    } else {
      add_option($id, $value);
    }
  }

  function get_controllers() {
    $controllers = array();
    $dir = wjssl_json_api_dir();
    $this->check_directory_for_controllers("$dir/controllers", $controllers);
    return array_map('strtolower', $controllers);
  }

  function check_directory_for_controllers($dir, &$controllers) {
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
      if (preg_match('/(.+)\.php$/i', $file, $matches)) {
        $src = file_get_contents("$dir/$file");
        if (preg_match("/class\s+JSON_API_{$matches[1]}_Controller/i", $src)) {
          $controllers[] = $matches[1];
        }
      }
    }
  }

  function controller_is_active($controller) {
    $default = 'sliders';
    $active_controllers = explode(',', $default);
    return (in_array($controller, $active_controllers));
  }

  function update_controllers($controllers) {
    if (is_array($controllers)) {
      return implode(',', $controllers);
    } else {
      return $controllers;
    }
  }

  function controller_info($controller) {
    $path = $this->controller_path($controller);
    $class = $this->controller_class($controller);
    $response = array(
      'name' => $controller,
      'description' => '(No description available)',
      'methods' => array()
    );
    if (file_exists($path)) {
      $source = file_get_contents($path);
      if (preg_match('/^\s*Controller name:(.+)$/im', $source, $matches)) {
        $response['name'] = trim($matches[1]);
      }
      if (preg_match('/^\s*Controller description:(.+)$/im', $source, $matches)) {
        $response['description'] = trim($matches[1]);
      }
      if (preg_match('/^\s*Controller URI:(.+)$/im', $source, $matches)) {
        $response['docs'] = trim($matches[1]);
      }
      if (!class_exists($class)) {
        require_once($path);
      }
      $response['methods'] = get_class_methods($class);
      return $response;
    } else if (is_admin()) {
      return "Cannot find controller class '$class' (filtered path: $path).";
    } else {
      $this->error("Unknown controller '$controller'.");
    }
    return $response;
  }

  function controller_class($controller) {
    return "json_api_{$controller}_controller";
  }

  function controller_path($controller) {
    $json_api_dir = wjssl_json_api_dir();
    $json_api_path = "$json_api_dir/controllers/$controller.php";

    if (file_exists($json_api_path)) {
      $path = $json_api_path;
    } else {
      $path = null;
    }
    return $path;
  }

  function get_nonce_id($controller, $method) {
    $controller = strtolower($controller);
    $method = strtolower($method);
    return "json_api-$controller-$method";
  }

  function flush_rewrite_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  function error($message = 'Unknown error', $http_status = 404) {
    $this->response->respond(array(
        'error' => 2,
        'error_msg' => $message
    ), 'error', $http_status);
  }

  function include_value($key) {
    return $this->response->is_value_included($key);
  }

}

?>
