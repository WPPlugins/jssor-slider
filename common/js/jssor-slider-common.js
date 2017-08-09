/**
 * @param message string
 * @param notice_type string 'success', 'info', 'warning', 'error',
 * @param show_dismiss_btn boolean true show delete button.
 */
function Notice(message, notice_type, show_dismiss_btn){
    show_dismiss_btn = show_dismiss_btn ? show_dismiss_btn : true;
    notice_type = notice_type ? notice_type : 'info';

    var register_delete_notice = function(dismiss_obj) {
        jQuery(dismiss_obj).on('click', function(e){
            var $obj = jQuery(this);
            var $notice = $obj.closest('.notice');
            $notice.remove();
        });
    };
    var build_html = function(){
        var content = '<div class="notice notice-' 
            + notice_type 
            + ' is-dismissible custom-notice">'
            + '<p>' + message + '</p>';

        if (show_dismiss_btn) {
            content += '<button type="button" class="notice-dismiss">'
                + '<span class="screen-reader-text">Dismiss this notice.</span>'
                + '</button>' 
        }

        content += '</div>';
        return content;
    };
    this.clear = function(parent) {
        parent = parent ? parent : 'body';
        jQuery(parent).find('.custom-notice').each(function(){
            jQuery(this).remove();
        });
        return this;
    };
    this.show = function(parent, position) {
        var html = build_html();
        position = position ? position : 'append';
        var $parent = jQuery(parent);
        if ($parent.length == 0) {
            return this;
        }

        $parent[position](html);

        if (show_dismiss_btn) {
            if (position == 'before' || position == 'after') {
                var elem = $parent.parent().find('.custom-notice .notice-dismiss')[0];
            } else {
                var elem = $parent.find('.custom-notice .notice-dismiss')[0];
            }
            register_delete_notice(elem);
        }
        return this;
    };
    return this;
};

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your common JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
