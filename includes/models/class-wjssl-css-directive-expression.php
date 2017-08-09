<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression.php';

/**
 * Class WjsslCssDirectiveExpression
 * @author Neil.zhou
 */
class WjsslCssDirectiveExpression extends WjsslExpression
{

    /**
     * undocumented function
     *
     * @return void
     */
    public function interpret()
    {
        $directivetype = $this->getValue('directivetype');
        $html = $this->getValue('name') . ' ';
        $ruleset_str = $this->collection('rulesets')->interpret();
        $terms_str = $this->collection('terms')->interpret();
        $directives_str = $this->collection('directives')->interpret();
        $declarations_str = $this->collection('declarations')->interpret();
        $mediums_str = $this->mediums_str();

        $selectors = array();
        $blocks = array();

        if (!empty($mediums_str)) {
            $selectors[] = $mediums_str;
        }
        if (!empty($terms_str)) {
            $selectors[] = $terms_str;
        }

        if (!empty($declarations_str)) {
            $blocks[] = $declarations_str;
        }
        if (!empty($directives_str)) {
            $blocks[] = $directives_str;
        }
        if (!empty($ruleset_str)) {
            $blocks[] = $ruleset_str;
        }

        $selectors_str = implode(' ', $selectors);
        $blocks_str = implode(' ', $blocks);

        // @media <media_query_list> {...}
        // @font-face { font-family: <identifier>; src: <fontsrc> [, <fontsrc>]*; <font>; }
        // others, eg. @keyframes <identifier> { <keyframes-blocks> }, @support (rule)[operator (rule)]* { sRules }
        // @import <url> <media_query_list>;
        // @charset <charset>;
        // @page <page-selector-list> {...}
        // @namespace <namespace-prefix>? [ <string> | <url> ];
        $wrap_begin = '{';
        $wrap_end = '}'; 
        if (empty($blocks_str)) {
            // do nothing.
        } elseif (WP_Jssor_Slider_Utils::is_wrapped_by($blocks_str, $wrap_begin, $wrap_end)) {
            $blocks_str = ' ' . $blocks_str;
        } else {
            $blocks_str = ' ' . $wrap_begin . $blocks_str . $wrap_end;
        }

        $html .= $selectors_str . $blocks_str;

        $html = trim($html);
        if (!$this->is_append_end_char($html)) {
            $html .= ';';
        } 
        
        return $html;
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function is_append_end_char($html)
    {
        if (empty($html)) {
            return false;
        }
        $last_char = substr($html, -1);
        return $last_char == '}' || $last_char == ';';
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function collection($key)
    {
        $collection = $this->getValue($key);
        if (empty($collection)) {
            require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-empty-expression.php';
            return new WjsslEmptyExpression();
        }

        require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-expression-component.php';
        return new WjsslExpressionComponent($collection, $this);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    private function mediums_str()
    {
        $medium = $this->getValue('mediums');
        if (empty($medium)) {
            return '';
        }
        $mapping = array(
            'all',
            'aural',
            'braille',
            'embossed',
            'handheld',
            'print',
            'projection',
            'screen',
            'tty',
            'tv',
        );
        $filters = array_filter($mapping, array($this, 'filte_mediums'), ARRAY_FILTER_USE_KEY);
        if (empty($filters)) {
            return '';
        }
        return implode(', ', $filters);
    }
    
    /**
     * undocumented function
     *
     * @return void
     */
    public function filte_mediums($key)
    {
        $medium = $this->getValue('mediums');
        return in_array($key, $medium);
    }
    
}
