(function($) {

    modal_loaded = false;
    modals = {};

    $.modal = function(options) {

        // settings
        var settings = $.extend({
            src:'',
        }, options);

        // function cases
        switch (options) {
            case 'init':
            default:
                init();
                break;
        }

        function init(callback) {

            $.when($('.modal').appendTo('body').wrap('<div class="modal-container"></div>')).done(function() {
                $('.modal').show();

                if (callback)
                    callback();

                modal_loaded = true;
            });

        }

        $('.modal-container .modal').each(function() {
            modals[$(this).attr('id')] = {
                selector:this,
            }
        });

        // event listeners
        const $container = $('.modal-container');

        // on modal container click
        $container.on('mousedown', function(e) {
            // const $modal = $(e.target).closest('.modal');
            const $modal = $(e.target).children('.modal');

            // on modal container click but not modal click
            if ($modal.length > 0)
                $modal.modal('close');
        });

    }

    $(document).ready(function() {
        $.modal('init');
    });

    /** @param method_1 Either a jQuery method or a callback function
     ** @param method_2 Callback function
     **/

    $.fn.modal = function(param_1, param_2) {

        main(this, param_1, param_2);

        function defer_loaded(method) {
            if (modal_loaded) {
                method();
            } else {
                setTimeout(function() { defer_loaded(method); }, 50);
            }
        }

        function toggle(that, callback) {
            const $container = $(that).parents('.modal-container');

            if ($container.is(':hidden'))
                show(that, callback);
            else
                hide(that, callback);

        }

        function show(that, callback) {
            const $container = $(that).parents('.modal-container');

            // lock scrollbar
            $('body').css('overflow-y', 'hidden');

            $container.fadeIn('fast', function() {
                if (callback)
                    callback();
            });
        }

        function hide(that, callback) {
            const id            = $(that).attr('id');
            const $container    = $(that).parents('.modal-container');
            var go              = true;

            if ('close_if' in modals[id]) {
                if (typeof modals[id].close_if === 'function')
                    go = modals[id].close_if();
                else
                    go = modals[id].close_if;
            }

            if (go) {
                if ('close_callback' in modals[id])
                    modals[id].close_callback();

                // unlock scrollbar
                $('body').css('overflow-y', 'initial');

                $container.fadeOut('fast', function() {
                    if (callback)
                        callback();

                    if ('close_callback_after' in modals[id])
                        modals[id].close_callback_after();
                });
            }
        }

        function get_method_from_string(string) {
            switch (string) {
                case 'toggle':
                default:
                    return toggle;
                case 'hide':
                case 'close':
                    return hide;
                case 'show':
                case 'appear':
                    return show;
            }
        }

        function main(that, param_1, param_2) {
            const id            = $(that).attr('id');
            const $container    = $(that).parents('.modal-container');
            var overlay_opacity = 0.25;
            var method          = toggle;
            var callback        = false;

            // if function
            if (typeof param_1 === 'function')
                callback = param_1;
            // if string
            else if (typeof param_1 === 'string') {
                method = get_method_from_string(param_1);
            } else if (typeof param_1 === 'object') {
                if ('action' in param_1)
                    method = get_method_from_string(param_1.action);

                if ('close_callback' in param_1)
                    modals[id].close_callback = param_1.close_callback;

                if ('close_callback_after' in param_1)
                    modals[id].close_callback_after = param_1.close_callback_after;

                if ('callback' in param_1)
                    callback = param_1.callback;

                if ('close_if' in param_1)
                    modals[id].close_if = param_1.close_if;

                if ('width' in param_1)
                    $(that).css('width', param_1.width+'px');

                if ('overlay_opacity' in param_1)
                    overlay_opacity = param_1.overlay_opacity;
            } else
                method = toggle;

            // if method_2 is set, it's a callback
            if (typeof param_2 === 'function')
                callback = param_2;

            $container.css('background-color', 'rgba(0,0,0,'+overlay_opacity+')');

            defer_loaded(function() {
                method(that, callback);
            });

        }
    }
}(jQuery));