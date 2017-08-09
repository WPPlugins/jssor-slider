<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/interface-wjssl-node-type.php';

/**
 * Class WjsslNodeType
 * @author Neil.zhou
 */
abstract class WjsslNodeType implements IWjsslNodeType
{
    private $context;
    /**
     *
     * @return void
     */
    public function __construct($context)
    {
        $this->context = $context;
    }
    
    /**
     *
     * @return mix
     */
    public function context()
    {
        return $this->context;
    }
    
}
