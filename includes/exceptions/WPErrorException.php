<?php

/**
 * Class WPErrorException
 * @author Neil.zhou
 */

// Exit if accessed directly
if( !defined( 'ABSPATH') ) exit();

class WPErrorException extends Exception
{
    protected $wp_error = null;

    /**
     * undocumented function
     *
     * @return void
     */
    public function __construct($wp_error = null, $previous = NULL)
    {
        if (empty($wp_error)) {
            $wp_error = new WP_Error();
        }
        $this->wp_error = $wp_error;
        $message = $wp_error->get_error_message();
        parent::__construct($message, 0, $previous);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getWPError()
    {
        return $this->wp_error;
    }
    
}
