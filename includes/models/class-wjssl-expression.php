<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/interface-wjssl-expression.php';

/**
 * Class WjsslExpression
 * @author Neil.zhou
 */
abstract class WjsslExpression implements IWjsslExpression
{
    private $context;
    private $parent = null;

    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($context, $parent = null)
    {
        $this->context = $context;
        $this->setParent($parent);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    protected function getValue($key)
    {
        return isset($this->context[$key]) ? $this->context[$key] : null;
    }
    
    
    /**
     * undocumented function
     *
     * @return void
     */
    protected function child_nodes()
    {
        $context = $this->getContext();
        $child_nodes = $this->getValue('childNodes');
        $nodes = empty($context) || empty($child_nodes) ? array() : $child_nodes;

        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        return new WjsslExpressionComponent($nodes, $this);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getRoot()
    {
        $parent = $this->getParent();
        if (empty($parent)) {
            return $this;
        }
        return $parent->getRoot();
    }
    

    /**
     * undocumented function
     *
     * @return void
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    public function getParent()
    {
        return $this->parent;
    }
    
}
