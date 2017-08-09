<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslCodeDocumentExpression
 * @author Neil.zhou
 */
class WjsslCodeDocumentExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $nodes = $this->child_nodes();
        return $nodes->interpret();
    }
    
}
