<? namespace directory_comparison; ?>

const THIS_URL      = '<?= THIS_URL ?>';
const THIS_DIR      = '<?= THIS_DIR ?>';
const THIS_URL_DIR  = '<?= THIS_URL_DIR ?>';
const PROCESS_URL   = THIS_URL_DIR+'/process/';
const a             = '<?= $_GET['a'] ?>';
const loader_class  = 'loader';
const FILES_LIMIT   = <?= LIMIT_FILES ?>;

function load_jquery(method, wait, waited) {
    const timeout = 50;

    if (!wait)
        wait = 1000;

    if (!waited)
        waited = 0;

    if (waited >= wait) {
        let script = document.createElement('script');
        script.onload = function() {
            if (method)
                method();
        };
        script.src = '//code.jquery.com/jquery-3.6.0.min.js';

        document.head.appendChild(script);

        return;
    }

    if (window.jQuery) {
        if (method)
            method();
    } else {
        setTimeout(function () {
            waited += timeout;

            load_jquery(method, wait, waited);
        }, timeout);
    }
}

function get_url_params(url) {
    var vars = {};
    var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi,
        function(m,key,value) {
            vars[key] = value;
        });
    return vars;
}

function get_url_param(url, param) {
    return get_url_params(url)[param];
}

function get_listing_all() {
    get_listing_files_all();
    get_listing_ignored();
    get_listing_pushed();

    const date  = new Date();
    const m     = date.getMonth() + 1;
    const d     = date.getDate();
    const Y     = date.getFullYear();
    let h       = date.getHours();
    const i     = date.getMinutes();
    const ampm  = h >= 12 ? 'pm' : 'am';

    h = h % 12;
    h = h ? h : 12;

    $('#last_updated').html(m+'/'+d+'/'+Y+' '+h+':'+i+ampm);
}

function get_listing_files(from, elem, change_order, order_type, order_value) {
    show_loading(elem);

    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'get_listing_files',
            from:from,
            a:a,
            load_all:$('#load_all').val(),
            change_order:change_order,
            order_type:order_type,
            order_value:order_value,
        },
        success:function(result) {
            if (result.success)
                $(elem).html(result.data.html);
            else
                $(elem).html(result.msg);

            if (result.data.all_loaded || ($('#stag_load_all').val() == 1 && $('#prod_load_all').val() == 1))
                hide_load_all();
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function get_listing_files_all(change_order, order_type, order_value) {
    if (!change_order)
        change_order = false;

    if (!order_type)
        order_type = '';

    if (!order_value)
        order_value = '';

    get_listing_files('stag', '#listing_files_stag', change_order, order_type, order_value);
    get_listing_files('prod', '#listing_files_prod', change_order, order_type, order_value);
}

function get_listing_ignored() {
    const elem = '#listing_files_ignored';

    show_loading(elem);

    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'get_ignored_files',
            a:a,
        },
        success:function(result) {
            if (result.success)
                $(elem).html(result.data.html);
            else
                $(elem).html(result.msg);
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function get_listing_pushed(pag) {
    const elem = '#listing_files_pushed';

    show_loading(elem);

    if (!pag)
        pag = 1;

    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'get_pushed_files',
            a:a,
            pag:pag,
        },
        success:function(result) {
            if (result.success)
                $(elem).html(result.data.html);
            else
                $(elem).html(result.msg);
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function push_file(file, from) {
    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'push',
            file:file,
            from:from,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
            get_listing_files_all();
            get_listing_pushed();
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function delete_file(file, from) {
    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'delete',
            file:file,
            from:from,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
            get_listing_all();
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function ignore_file(file, type) {
    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'ignore',
            file:file,
            type:type,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
            get_listing_all();
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function unignore_file(file) {
    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'unignore',
            file:file,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
            get_listing_all();
        },
		error: function (jqXhr, textStatus, errorMessage) {
			console.error(errorMessage);
		},
    });
}

function save_notes(notes) {
    $.ajax({
        url:PROCESS_URL,
        type:'POST',
        dataType:'json',
        data:{
            act:'save_notes',
            notes:notes,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
        },
    });
}

function show_msg(text, title, type) {
    var background_color = '#CFE2FF';
    var color = '#084298';

    switch (type) {
        case 'success':
            background_color = '#D1E7DD';
            color = '#0f5132';
            break;
        case 'error':
            background_color = '#F8D7DA';
            color = '#842029';
            break;
        case 'warning':
            background_color = '#fff3cd';
            color = '#664d03';
            break;
    }

    let msg_html = '';
    msg_html += '<div style="position:fixed;bottom:15px;right:15px;border-radius:5px;background-color:'+background_color+';color:'+color+';width:400px;padding:15px 20px;display:none;">';
    msg_html += '<div style="font-size:16px;font-weight:600;word-wrap:break-word;">';
    msg_html += title;
    msg_html += '</div>';
    msg_html += '<div style="font-size:14px;">';
    msg_html += text;
    msg_html += '</div>';
    msg_html += '</div>';

    let $msg_html = $(msg_html);

    $('body').append($msg_html);

    $msg_html.fadeIn('fast');

    setTimeout(function() {
        $msg_html.fadeOut('fast');
    }, 4000);
}

function show_loading(elem) {
    const animate = function(element) {
        $(element).css({
            transition:'none',
            transform:'rotate(0deg)',
        });

        setTimeout(function() {
            $(element).css({
                transition:'all ' + transition_time + 'ms',
                transform:'rotate(360deg)',
                'transition-timing-function':'linear',
            });

            setTimeout(function () {
                animate(element);
            }, transition_time);
        }, 0);
    };

    const transition_time   = 750;
    const loader_selector   = elem+' .'+loader_class;

    let $html = $('<div class="'+loader_class+'-container"></div>')
        .css({
            'position':'absolute',
            'display':'flex',
            'justify-content':'center',
            'align-items':'center',
            'background-color':'rgba(255,255,255,0.5)',
            'border-radius':'4px',
            'padding':'30px',
            'top':0,
            'right':0,
            'bottom':0,
            'left':0,
        })
        .append('<i style="font-size:40px;color:#2369A3;width:100%;text-align:center;" class="'+loader_class+' fa fa-circle-o-notch" aria-hidden="true"></i>');

    $(elem)
        .css('width','100%')
        .css('min-height','100px')
        .css('position','relative')
        .append($html);

    animate(loader_selector);
}

function hide_loading(elem) {
    $(elem).find('.'+loader_class+'-container').remove();
}

function load_all() {
    $('#load_all').val(1);

    get_listing_files_all();

    hide_load_all();
}

function hide_load_all() {
    $('head').append('<style class="stylesheet-load-more">.listing-files .load-more{display:none;}</style>');
}

load_jquery(function() {

    const CSRF_TOKEN    = $('meta[name="csrf-token"]').attr('content');

    $.ajaxSetup({
        headers : {
            'CsrfToken':CSRF_TOKEN
        }
    });

    $(function() {
        get_listing_all();

        // on refresh
        $('#refresh').on('click', function() {
            const $refresh = $(this).find('svg');
            const transition_time = 500;

            $refresh.css({
                transition:'all '+transition_time+'ms',
                transform:'rotate(720deg)',
            });

            setTimeout(function() {
                $refresh.css({
                    transition:'none',
                    transform:'rotate(0)',
                });
            }, transition_time);

            $('#load_all').val(0);
            $('.stylesheet-load-more').remove();

            get_listing_all();
        });
    });

    // on pushed files listing first button click
    $(document).on('click', '#pag_pushed .pag-first', function() {
        const current = $(this).parents('.pag').data('current');

        if (current > 1) {
            get_listing_pushed(1);
        }
    });

    // on pushed files listing prev button click
    $(document).on('click', '#pag_pushed .pag-prev', function() {
        const current = $(this).parents('.pag').data('current');
        const next = current - 1;

        if (next >= 1) {
            get_listing_pushed(next);
        }
    });

    // on pushed files listing next button click
    $(document).on('click', '#pag_pushed .pag-next', function() {
        const current   = $(this).parents('.pag').data('current');
        const total     = $(this).parents('.pag').data('total');
        const next      = current + 1;

        if (next <= total) {
            get_listing_pushed(next);
        }
    });

    // on pushed files listing last button click
    $(document).on('click', '#pag_pushed .pag-last', function() {
        const current   = $(this).parents('.pag').data('current');
        const total     = $(this).parents('.pag').data('total');

        if (current < total) {
            get_listing_pushed(total);
        }
    });

    // on file listing action click
    $(document).on('click', '.listing .listing-action', function() {
        const from = $(this).parents('.listing-files-table').attr('data-from');
        const file = $(this).parents('.row-file').attr('data-file');
        const act = $(this).attr('data-act');

        switch (act) {
            case 'push':
                push_file(file, from);
                break;
            case 'delete':
                delete_file(file, from);
                break;
            case 'ignore':
                ignore_file(file);
                break;
            case 'unignore':
                unignore_file(file);
                break;
        }
    });

    // on add ignore click
    $(document).on('click', '#add_ignore', function() {
        $('#modal_ignore').modal();
    });

    // on ordering column click
    $(document).on('click', '.col-order', function() {
        const order_type   = $(this).attr('data-type');
        const order_value   = $(this).attr('data-value');

        // turn comma separated list into array
        get_listing_files_all(true, order_type, order_value);
    });

    // on file row click
    $(document).on('click', '.listing-files .row-file td:not(.col-action)', function() {
        const $THIS_ROW = $(this).parent('.row-file');

        // toggle checkbox
        const $checkbox = $THIS_ROW.find('input[type=checkbox]');

        $checkbox.prop('checked', !$checkbox.prop('checked'));

        if ($checkbox.prop('checked'))
            $THIS_ROW.attr('data-selected', 'true');
        else
            $THIS_ROW.attr('data-selected', 'false');

        // toggle options in bulk action box
        const listings = ['#listing_form_stag', '#listing_form_prod'];

        for (let i = 0; i < listings.length; i++) {

            const listing       = listings[i];
            const $bulk_select  = $(listing+' .filter-bulk');
            var type_no         = false;
            var type_diff       = false;
            var one_checked     = false;

            // iterate through rows and get change types
            $(listing+' .row-file').each(function (index, element) {
                const $checkbox = $(element).find('.file-checkbox');

                if ($checkbox.is(':checked')) {
                    let types = $(element).attr('data-types');
                    types = types.split(',');

                    if (types.includes('no'))
                        type_no = true;

                    if (types.includes('diff'))
                        type_diff = true;

                    one_checked = true;
                }
            });

            // if at least one is checked
            if (one_checked) {

                // enable select and submit
                $bulk_select.removeAttr('disabled');
                $(listing).find('.bulk-sub').removeAttr('disabled');

                // disable/enable push option
                if (type_no) {
                    $bulk_select.find('option[value="push"]').attr('disabled', 'disabled');

                    if ($bulk_select.find('option[value="push"]').is(':selected'))
                        $bulk_select.val($bulk_select.find('option:first').val());
                } else
                    $bulk_select.find('option[value="push"]').removeAttr('disabled');

                // disable/enable delete option
                if (type_diff || type_no) {
                    $bulk_select.find('option[value="delete"]').attr('disabled', 'disabled');

                    if ($bulk_select.find('option[value="delete"]').is(':selected'))
                        $bulk_select.val($bulk_select.find('option:first').val());
                } else
                    $bulk_select.find('option[value="delete"]').removeAttr('disabled');
            } else {
                // disable select and submit
                $bulk_select
                    .attr('disabled', 'disabled')
                    .val($bulk_select.find('option:first').val());

                $(listing).find('.bulk-sub').attr('disabled', 'disabled');
            }
        }
    });

    // on load more click
    $(document).on('click', '.load-more', function() {
        load_all();
    });

    // on modal close click
    $('#modal_ignore_close').on('click', function() {
        $('#modal_ignore').modal('close');
    });

    // on modal save click
    $('#modal_ignore_save').on('click', function() {
        const val   = $('#modal_ignore_path').val();
        const type  = $('#modal_ignore_type').val();

        if (val !== '' && val !== ' ') {
            ignore_file(val, type);

            $('#modal_ignore').modal('close');
        } else
            alert('Ignore path cannot be blank.');
    });

    // on notes save click
    $('#notes_submit').on('click', function() {
        const notes = $('#notes').val();

        save_notes(notes);
    });
});