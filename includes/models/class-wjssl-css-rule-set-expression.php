<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslCssRuleSetExpression
 * @author Neil.zhou
 */
class WjsslCssRuleSetExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $selectors = $this->selectors()->interpret();
        $selectors = trim($selectors, ',');

        $declarations = $this->declarations()->interpret();

        return $selectors . '{' . $declarations . '}';
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function selectors()
    {
        $context = $this->getContext();
        $selectors = empty($context['selectors']) ? array() : $context['selectors'];
        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        return new WjsslExpressionComponent($selectors, $this);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function declarations()
    {
        $context = $this->getContext();
        $selectors = empty($context['declarations']) ? array() : $context['declarations'];
        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        return new WjsslExpressionComponent($selectors, $this);
    }
}
