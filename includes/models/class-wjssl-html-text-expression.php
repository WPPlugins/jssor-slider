<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslHtmlTextExpression
 * @author Neil.zhou
 */
class WjsslHtmlTextExpression extends WjsslExpression
{
    /**
     *
     * @return string
     */
    public function interpret()
    {
        $context = $this->getContext();
        return $context['text'];
    }
    
}
