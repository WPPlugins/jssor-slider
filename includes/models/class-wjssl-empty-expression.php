<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/interface-wjssl-expression.php';

/**
 * Class WjsslEmptyExpression
 * @author Neil.zhou
 */
class WjsslEmptyExpression implements IWjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        return '';
    }
    
}
