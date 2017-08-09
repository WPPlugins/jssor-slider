<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslCssDeclarationExpression
 * @author Neil.zhou
 */
class WjsslCssDeclarationExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $html = '';
        $context = $this->getContext();
        $html .= $context['name'] . ':';
        $html .= $this->terms()->interpret();
        if (!empty($context['important'])) {
            $html .= ' !important';
        }
        $html = trim($html);
        $html .= ';';
        return $html;
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function terms()
    {
        $context = $this->getContext();
        $selectors = empty($context['terms']) ? array() : $context['terms'];
        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        $component = new WjsslExpressionComponent($selectors, $this);
        return $component;
    }

}
