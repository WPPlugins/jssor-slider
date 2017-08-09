<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslCssSelectorExpression
 * @author Neil.zhou
 */
class WjsslCssSelectorExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $context = $this->getContext();
        return implode(' ', $context['items']) . ',';
    }
    
}
