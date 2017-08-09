<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WJSSL_JSON_API_Query {

  // Default values
  protected $defaults = array(
    'date_format' => 'Y-m-d H:i:s',
    'read_more' => 'Read more'
  );

  function __construct() {
    // Register JSON API query vars
    add_filter('query_vars', array(&$this, 'query_vars'));
  }

  function get($key) {
    if (is_array($key)) {
      $result = array();
      foreach ($key as $k) {
        $result[$k] = $this->get($k);
      }
      return $result;
    }
    $query_var = (isset($_REQUEST[$key])) ? $_REQUEST[$key] : null;
    $wp_query_var = $this->wp_query_var($key);
    if ($wp_query_var) {
      return $wp_query_var;
    } else if ($query_var) {
      return $this->strip_magic_quotes($query_var);
    } else if (isset($this->defaults[$key])) {
      return $this->defaults[$key];
    } else {
      return null;
    }
  }

  function __get($key) {
    return $this->get($key);
  }

  function __isset($key) {
    return ($this->get($key) !== null);
  }

  function wp_query_var($key) {
    $wp_translation = array(
      'jssorapi' =>       'jssorapi',
    );
    if ($key == 'date') {
      $date = null;
      if (get_query_var('year')) {
        $date = get_query_var('year');
      }
      if (get_query_var('monthnum')) {
        $month = get_query_var('monthnum');
        if ($month < 10) {
          $month = "0$month";
        }
        $date .= $month;
      }
      if (get_query_var('day')) {
        $day = get_query_var('day');
        if ($day < 10) {
          $day = "0$day";
        }
        $date .= $day;
      }
      return $date;
    } else if (isset($wp_translation[$key])) {
      return get_query_var($wp_translation[$key]);
    } else {
      return null;
    }
  }

  function strip_magic_quotes($value) {
    if (get_magic_quotes_gpc()) {
      return stripslashes($value);
    } else {
      return $value;
    }
  }

  function query_vars($wp_vars) {
    $wp_vars[] = 'jssorapi';
    return $wp_vars;
  }

  function get_controller() {
    $json = $this->get('jssorextver');
    $json2 = $this->get('jssorapi');
    if (empty($json) && empty($json2)) {
      return false;
    }
    return 'sliders';
  }

  function get_legacy_controller($json) {
    return 'sliders';
  }

  function get_method($controller) {

    $method = $this->get('method');
    if (strpos($method, '/') !== false) {
      $method = substr($method, strpos($method, '/') + 1);
    } else if (strpos($method, '.') !== false) {
      $method = substr($method, strpos($method, '.') + 1);
    }

    if (empty($method)) {
      // Case 1: we're not being invoked (done!)
      return false;
    } else if (method_exists("JSON_API_{$controller}_Controller", $method)) {
      // Case 2: an explicit method was specified
      return $method;
    }
    // Case 4: either the method doesn't exist or we don't support the page implicitly
    return '404';
  }

}
