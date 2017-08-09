<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-node-type.php';

/**
 * Class WjsslLinkUrl
 * @author Neil.zhou
 */
class WjsslLinkUrl extends WjsslNodeType
{
    /**
     *
     * @return void
     */
    public function node_value()
    {
        $context = $this->context();
        return $context['value'];
    }
    
}
