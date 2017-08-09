<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslExpression
 * @author Neil.zhou
 */
class WjsslExpressionComponent extends WjsslExpression
{
    private $component = array();

    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($context = array(), $parent = null)
    {
        $context = empty($context) ? array() : $context;
        parent::__construct($context, $parent);
        foreach ($context as $item) {
            $this->component[] = WjsslExpressionFactory::create_expression($item, $parent);
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function add(IWjsslExpression $express)
    {
        $this->component[] = $express;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $html = '';

        foreach ($this->component as $c) {
            $html .= $c->interpret();
        }
        
        return trim($html);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    public function setParent($parent)
    {
        foreach ($this->component as $item) {
            $item->setParent($parent);
        }
    }
    
}
