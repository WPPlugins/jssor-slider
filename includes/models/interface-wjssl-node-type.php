<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Interface IWjsslNodeType
 * @author Neil.zhou
 */
interface IWjsslNodeType
{
    const STRING_VTYPE = 0;
    const RESOURCE_URL = 1;
    const LINK_URL = 2;
    const STYLE_VTYPE = 3;

    /**
     *
     * @return void
     */
    public function node_value();
    
}
