<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

/**
 * Interface IWjsslExpression
 * @author Neil.zhou
 */
interface IWjsslExpression
{
    const CODE_DOCUMENT = 0;
    const HTML_ELEMENT = 1;
    const HTML_TEXT = 2;
    const HTML_COMMENT = 3;
    const HTML_ATTRIBUTE = 4;

    const CSS_RULE_SET = 101;
    const CSS_SELECTOR = 102;
    const CSS_DECLARATION = 103;
    const CSS_TERM = 104;

    const CSS_DIRECTIVE = 105;

    public function interpret();
}
