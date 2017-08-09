<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-node-type.php';

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression-factory.php';

/**
 * Class WjsslStyleVtype
 * @author Neil.zhou
 */
class WjsslStyleVtype extends WjsslNodeType
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function node_value()
    {
        $context = $this->context();

        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        $component = new WjsslExpressionComponent($context['childNodes']);
        return $component->interpret();
    }
    
}
