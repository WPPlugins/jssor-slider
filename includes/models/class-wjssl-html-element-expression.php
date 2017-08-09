<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes/modal
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Jssor_Slider
 * @subpackage WP_Jssor_Slider/includes
 * @author     Your Name <email@example.com>
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH
    . 'includes/models/class-wjssl-expression.php';

class WjsslHtmlElementExpression extends WjsslExpression{

    private $attributes = false;
    private $child_nodes = false;

    private $is_ignored = false;

    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $context = $this->getContext();
        $tag_name = $this->getValue('name');
        $is_empty = $this->getValue('isEmpty');

        $html = '<' . $tag_name . ' ' . $this->attributes()->interpret();
        if ($is_empty) {
            $html .= '/>';
        } else {
            $html .= '>';
            $html .= $this->child_nodes()->interpret();
            $html .= '</' . $tag_name . '>';
        }

        if ($this->is_ignored) {
            return '';
        }
        return $html;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function setIgnored($ignore = true)
    {
        $this->is_ignored = $ignore;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    private function attributes()
    {
        $context = $this->getContext();
        $attributes = $this->getValue('attributes');
        $attributes = empty($attributes) ? array() : $attributes;

        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        return new WjsslExpressionComponent($attributes, $this);
    }
}
