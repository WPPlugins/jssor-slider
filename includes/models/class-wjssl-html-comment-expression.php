<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslHtmlCommentExpression
 * @author Neil.zhou
 */
class WjsslHtmlCommentExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $context = $this->getContext();
        $html = $context['comment'];
        if ($this->_is_commented()) {
            return $html;
        }
        return '<!-- ' . $html . ' -->';
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function _is_commented()
    {
        $context = $this->getContext();
        return stripos($context['comment'], '<!--') === 0;
    }
    
}
