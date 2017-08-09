<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once(WP_JSSOR_SLIDER_PATH. 'includes/models/interface-wjssl-expression.php');

/**
 * Class WjsslExpressionFactory
 * @author Neil.zhou
 */
class WjsslExpressionFactory
{
    /**
     * IWjsslExpression
     *
     * @return void
     */
    public static function create_expression($context, $parent = null)
    {
        switch ($context['type']) {
            case IWjsslExpression::CODE_DOCUMENT:
                require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-code-document-expression.php';
                return new WjsslCodeDocumentExpression($context, $parent);

            case IWjsslExpression::HTML_ELEMENT:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-html-element-expression.php';
                return new WjsslHtmlElementExpression($context, $parent);

            case IWjsslExpression::HTML_TEXT:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-html-text-expression.php';
                return new WjsslHtmlTextExpression($context, $parent);
                    
            case IWjsslExpression::HTML_COMMENT:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-html-comment-expression.php';
                return new WjsslHtmlCommentExpression($context, $parent);
                        
            case IWjsslExpression::HTML_ATTRIBUTE:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-html-attribute-expression.php';
                return new WjsslHtmlAttributeExpression($context, $parent);
                            
            case IWjsslExpression::CSS_RULE_SET:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-css-rule-set-expression.php';
                return new WjsslCssRuleSetExpression($context, $parent);
                                
            case IWjsslExpression::CSS_SELECTOR:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-css-selector-expression.php';
                return new WjsslCssSelectorExpression($context, $parent);
                                    
            case IWjsslExpression::CSS_DECLARATION:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-css-declaration-expression.php';
                return new WjsslCssDeclarationExpression($context, $parent);
                                        
            case IWjsslExpression::CSS_TERM:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-css-term-expression.php';
                return new WjsslCssTermExpression($context, $parent);

            case IWjsslExpression::CSS_DIRECTIVE:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-css-directive-expression.php';
                return new WjsslCssDirectiveExpression($context, $parent);
            
            default:
                require_once WP_JSSOR_SLIDER_PATH. 'includes/models/class-wjssl-empty-expression.php';
                return new WjsslEmptyExpression();
        }
    }
    
}
