<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslCssTermExpression
 * @author Neil.zhou
 */
class WjsslCssTermExpression extends WjsslExpression
{
    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $context = $this->getContext();

        return $this->get_vtype_content();
    }

    protected function get_vtype_content(){
        $context = $this->getContext();

        require_once WP_JSSOR_SLIDER_PATH
            . 'includes/models/class-wjssl-node-type-factory.php';
        $factory = WjsslNodeTypeFactory::create_node($context);

        $html = $factory->node_value();

        $is_url = $context['vType'] == 1;

        if (!empty($context['quote'])) {
            if (!WP_Jssor_Slider_Utils::is_wrapped_by($html, $context['quote'], $context['quote'])) {
                $html = $context['quote'] . $html . $context['quote'];
            }
        }

        if ($is_url) {
            $html = "url($html)";
        }
        //elseif($this->_is_numeric_tag() && preg_match('/\A[0-9]+\z/', $html)) {
        //    $html = $html . 'px';
        //}

        if (!empty($context['seperator'])) {
            $html = $context['seperator'] . $html;
        }
        else {
            $html = ' ' . $html; // prefix with blank space to join the term.
        }
        return $html;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function _is_numeric_tag()
    {
        $name = $this->getParent()->getValue('name');
        $array = array(
            'width',
            'height',
            'left',
            'right',
            'top',
            'bottom',
            'background',
            'border',
            'padding',
            'margin',
        );
        $expand_str = array(
            'background-',
            'border-',
            'padding-',
            'margin-',
        );
        if (array_search($name, $array) !== false) {
            return true;
        }
        foreach ($expand_str as $value) {
            if (stripos($name, $value) === 0) {
                return true;
            }
        }
        return false;
    }

}
