const THIS_URL      = '<?= THIS_URL ?>';
const THIS_DIR      = '<?= THIS_DIR ?>';
const THIS_URL_DIR  = '<?= THIS_URL_DIR ?>';
const PROCESS_URL   = THIS_URL_DIR+'/process.ajax.php';
const a             = '<?= $_GET['a'] ?>';

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

function get_listing_files(from, elem) {
    show_loading(elem);

    $.ajax({
	    url:PROCESS_URL,
	    type:'GET',
	    data:{
	        act:'get_listing_files',
		    from:from,
            a:a,
	    },
        success:function(result) {
            $(elem).html(result);
        },
    })
}

function get_listing_files_all() {
    get_listing_files('stag', '#listing_files_stag');
    get_listing_files('prod', '#listing_files_prod');
}

function get_listing_ignored() {
    show_loading('#listing_files_ignored');

    $.ajax({
        url:PROCESS_URL,
        type:'GET',
        data:{
            act:'get_ignored_files',
            a:a,
        },
        success:function(result) {
            $('#listing_files_ignored').html(result);
        },
    })
}

function get_listing_pushed(pag) {
    show_loading('#listing_files_pushed');

    if (!pag)
        pag = 1;

    $.ajax({
        url:PROCESS_URL,
        type:'GET',
        data:{
            act:'get_pushed_files',
            a:a,
            pag:pag,
        },
        success:function(result) {
            $('#listing_files_pushed').html(result);
        },
    })
}

function push_file(file, from) {
    $.ajax({
        url:PROCESS_URL,
        type:'GET',
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
    });
}

function delete_file(file, from) {
    $.ajax({
        url:PROCESS_URL,
        type:'GET',
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
    });
}

function ignore_file(file) {
    $.ajax({
        url:PROCESS_URL,
        type:'GET',
        dataType:'json',
        data:{
            act:'ignore',
            file:file,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
            get_listing_all();
        },
    });
}

function unignore_file(file) {
    $.ajax({
        url:PROCESS_URL,
        type:'GET',
        dataType:'json',
        data:{
            act:'unignore',
            file:file,
        },
        success:function(result) {
            show_msg(result.msg, result.data.title, result.data.type);
            get_listing_all();
        },
    });
}

function show_msg(title, text, type) {
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
    const loader_class      = 'loader';
    const loader_selector   = elem+' .'+loader_class;

    let $html = $('<div></div>')
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

$(function() {
    get_listing_all();

    $('#refresh').on('click', function() {
        const $refresh = $(this);
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

        get_listing_all();
    });
});

$(document).on('click', '#pag_pushed .pag-first', function() {
    const current = $(this).parents('.pag').data('current');

    if (current > 1) {
        get_listing_pushed(1);
    }
});

$(document).on('click', '#pag_pushed .pag-prev', function() {
    const current = $(this).parents('.pag').data('current');
    const next = current - 1;

    if (next >= 1) {
        get_listing_pushed(next);
    }
});

$(document).on('click', '#pag_pushed .pag-next', function() {
    const current   = $(this).parents('.pag').data('current');
    const total     = $(this).parents('.pag').data('total');
    const next      = current + 1;

    if (next <= total) {
        get_listing_pushed(next);
    }
});

$(document).on('click', '#pag_pushed .pag-last', function() {
    const current   = $(this).parents('.pag').data('current');
    const total     = $(this).parents('.pag').data('total');

    if (current < total) {
        get_listing_pushed(total);
    }
});

// see more pushed files
$(document).on('click', '.more', function() {
    
});

$(document).on('click', '.listing a', function(e) {
    e.preventDefault();

    const href = $(this).attr('href');

    if (href) {
        const act   = get_url_param(href, 'act');
        const file  = get_url_param(href, 'file');
        var from    = null;

        if (act && file) {
            if (act == 'push' || act == 'delete')
                from = get_url_param(href, 'from');
            else
                from = true;

            if (from) {

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
            } else
                alert('From parameter not found');
        }
    }
});