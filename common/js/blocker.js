var Blocker = {};
(function($){
    Blocker.show = function(){
        if ($('.custom-modal.blocker').length == 0) {
            $('<div class="custom-modal blocker"><div class="modal-spinner" style="z-index:999;display:inline-block;vertical-align:middle;position:relative;left:initial;top:initial;margin:0;"></div></div>').appendTo($('body'));
        } else {
            $('.custom-modal.modal-spinner').show();
        }
        return this;
    };
    Blocker.hide = function(){
        if ($('.custom-modal.blocker').length) {
            $('.custom-modal.blocker').remove();
        }
        return this;
    }
})(jQuery);
