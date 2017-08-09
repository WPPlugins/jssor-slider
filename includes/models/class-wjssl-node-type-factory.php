<?php

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

require_once WP_JSSOR_SLIDER_PATH . 'includes/models/interface-wjssl-node-type.php';

/**
 * Class WjsslNodeTypeFactory
 * @author Neil.zhou
 */
class WjsslNodeTypeFactory
{
    /**
     *
     * @return IWjsslNodeType
     */
    public static function create_node($context)
    {
        switch ($context['vType']) {
        case IWjsslNodeType::RESOURCE_URL:

            require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-resource-url.php';
            return new WjsslResourceUrl($context);

        case IWjsslNodeType::LINK_URL:
            require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-link-url.php';
            return new WjsslLinkUrl($context);

        case IWjsslNodeType::STYLE_VTYPE:
            require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-style-vtype.php';
            return new WjsslStyleVtype($context);

        case IWjsslNodeType::STRING_VTYPE:
        default:
            require_once WP_JSSOR_SLIDER_PATH . 'includes/models/class-wjssl-string-vtype.php';
            return new WjsslStringVtype($context);
        }
    }
    
}
