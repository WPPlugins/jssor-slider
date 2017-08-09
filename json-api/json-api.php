<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

$dir = wjssl_json_api_dir();
@include_once "$dir/singletons/api.php";
@include_once "$dir/singletons/query.php";
@include_once "$dir/singletons/response.php";

function wjssl_json_api_init() {
  global $wjssl_json_api;

  $wjssl_json_api = new WJSSL_JSON_API();
}

function wjssl_json_api_dir() {
    return dirname(__FILE__);
}

// Add initialization and activation hooks
add_action('init', 'wjssl_json_api_init');
?>
