$.ajax({
    url: '../shoutbox/get_settings',
    async: false,
    success: function(data) {
       h4xed.settings = data;
    },
    dataType: "json"
});

var notice_message = 'Shoutbox loading... ';
var last_id = 0;
var yourname = 'Your name';
var yourmessage = 'Your message';
var update_timeout = 10000;
var ajax_add_url = h4xed.baseUrl + 'shoutbox/ajax_add';
var ajax_update_url = h4xed.baseUrl + 'shoutbox/ajax_update';
var ajax_online_url = h4xed.baseUrl + 'shoutbox/ajax_online_users';
var imagePath = h4xed.baseUrl + "assets/images/emoticons/";
var updating_shout = false;

$(function() {

    shoutbox_form_selector = $("#shoutbox #shoutbox_form");
    form_selector = $("#shoutbox_form form");
    shouts_selector = $("#shouts");
    notice_selector = $("div#notice");
    warning_selector = $("div#warning");
    warning_text = $("div#warning span.warning_text");
    error_selector = $("div#error");
    error_text = $("div#error span.error_text");
    notice_text_selector = $("div#notice span.notice_text");
    input_name = $("#shoutbox_form #name");
    input_message = $("#shoutbox_form #message");
    button_selector = $("#shoutbox_form #button");
    online_container = $('#onlinebox');
    online_ul = $("ul#online;");

    initialLoad("Loading shouts... ");
    setTimeout('updateLoop()', update_timeout);

    $.ajaxSetup({
        cache : false
    });
    

        input_message.keypress(function (e) {
            if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
//                $("#shoutbox_form button[type=submit]").click();
                form_selector.submit();
                return false;
            } else {
                return true;
            }
        }); 
    
    form_selector.submit(function(e) {
        e.preventDefault();
        updating_shout = true;
        showLoading("Posting shout... ");
        $.post(ajax_add_url, {
            message : input_message.val(), name : input_name.val(),
            lastid : last_id,
            h4xed_csrf_token: $.cookie("h4xed_csrf")
        }, function(json) {
            validatePost(json);
        }, "json");

        return false;
    });

    input_message.focus(function() {
        if ($(this).val() == yourmessage) {
            $(this).val('');
        }
    });

    input_name.focus(function() {
        if ($(this).val() == yourname) {
            $(this).val('');
        }
    });
});

function focusForm() {
    if (input_name.val() == $("yourname")) {
        input_name.focus();
    } else {
        input_message.focus();
    }
}

function getLastId() {
    last_id = $("#shouts_list dt:first").attr("id");
}

function initialLoad(notice_message) {
     online_container.hide();
//     online_ul.hide();
    $(":submit", button_selector).attr("disabled", "disabled");
    notice_text_selector.text(notice_message); 
    notice_selector.show().fadeIn('slow', function() {
        populateMessages();
    });
}

function showLoading(notice_message) {
    $(":submit", button_selector).attr("disabled", "disabled");

    notice_text_selector.text(notice_message);
    form_selector.fadeOut('fast', function() {
        input_message.val('');
    });

    notice_selector.fadeIn('fast');

}

function hideLoading() {
    notice_selector.fadeOut('slow', function() {

    });
    $("#shoutbox_form form").fadeIn('slow', function() {
        if (shouts_selector.is(":hidden")) {
            shouts_selector.slideDown('slow', 'swing', function() {
                $(":submit", button_selector).removeAttr('disabled');
            });
        } else {
            $(":submit", button_selector).removeAttr('disabled');
        }

    });
}

function populateMessages() {
    $.post(ajax_update_url, {
        lastid : last_id,
        initial : false,
        h4xed_csrf_token: $.cookie("h4xed_csrf")
    }, function(json) {

        shouts_list_created = $('<dl>').attr('id', 'shouts_list');
        $.each(json.shouts, function(id, shout) {
            shout_dt = $('<dt></dt>')
            .attr('id', shout.id)
            .text(shout.name)
            .append($('<span>').addClass('separator').text(' : '))
            .append($('<abbr>').addClass('timestamp').attr('title', shout.timestamp).text(shout.when));
            shout_dd = $('<dd>').html($().emoticon(shout.message));
            
            shout_dt.appendTo(shouts_list_created);
            shout_dd.appendTo(shouts_list_created);
        });
        shouts_selector.append(shouts_list_created);
        getLastId();
        hideLoading();
        updateOnlineUsers();
    }, 'json');
}

function validatePost(json) {
    if (json.status != 'error') {
        addMessage(json);
    } else if (json.status == 'error') {
        displayError(json);
    }
}

function displayError(json) {
    error_text.text(json.errors[0]);
    hideaLoading();
    notice_selector.fadeOut('slow', function() {
        error_selector.fadeIn('slow').delay(2000).fadeOut('slow');
    });
}

function addMessage(json) {
    if (json.row_count > 0) {
        shouts_list_selector = $("#shouts dl#shouts_list");
        $.each(json.shouts, function(id, shout) {
            if (shout.id > last_id) {
            shout_dt = $('<dt>')
            .attr('id', shout.id)
            .text(shout.name)
            .append($('<span>').addClass('separator').text(' : '))
            .append($('<abbr>').addClass('timestamp').attr('title', shout.timestamp).text(shout.when));
            
            shout_dd = $('<dd>').html($().emoticon(shout.message));
            
//            shout_object = $.extend(shout_dt, shout_dd);
            
            shout_dd.hide().css("opacity", 0).prependTo(shouts_list_selector).slideDown(
                  'slow', 'swing').animate({
                  opacity : 1
              });
            shout_dt.hide().css("opacity", 0).prependTo(shouts_list_selector).slideDown(
                  'slow', 'swing').animate({
                  opacity : 1
              });
            
//            shout_object.hide().css("opacity", 0).prependTo(shouts_list_selector).slideDown(
//                            'slow', 'swing').animate({
//                                opacity : 1
//                            });
            }
            else {
                var time_selector = $("dt#"+shout.id+" abbr");
                if (time_selector.text() !== shout.when) {
                    time_selector.fadeOut('fast', function() {
                        $(this).text(shout.when).fadeIn('fast');
                    });
                }
            }
        });

        shouts_list_selector.children("dt, dd").filter(":gt(39)").fadeThenSlideToggle(null, null, function() {
            $(this).remove();
            });

        hideLoading();
        getLastId();
        updating_shout = false;
    } else {
        updating_shout = false;
        return false;
    }
}

function updateOnlineUsers() {
    $.get(ajax_online_url, function(json) {
        var items = [];
        $.each(json.users, function(id, online) {
            cssclass = (online.is_me) ? 'me' : '';
            online_user = $("<li>").addClass(cssclass).text(online.name);
            items.push(online_user.outerHtml());
        });
        
        online_text = (json.count > 0) ? 'There are <strong>' + json.count + '</strong> users online.' : 'There are no users online at the moment.';
        online_container.html($('<p>').html(online_text));
        online_container.append($('<ul>').attr('id', 'online').html(items.join('')));
        
        $("#onlinebox li").not(":last").append(', ');

        if (online_container.is(':hidden')) {
            online_container.fadeThenSlideToggle('slow', 'swing');
        }
    }, 'json');
}

function updateLoop() {
    if (!updating_shout) {
        $.get(ajax_update_url, function(json) {
            addMessage(json);
            updateOnlineUsers();
        }, 'json');
        var shoutsTimer = setTimeout('updateLoop()', update_timeout);
    } else {
        var shoutsTimer = setTimeout('updateLoop()', 2000);
    }
}

$.fn.fadeThenSlideToggle = function(speed, easing, callback) {
    if (this.is(":hidden")) {
        return this.slideDown(speed, easing).fadeTo(speed, 1, easing, callback);
    } else {
        return this.fadeTo(speed, 0, easing).slideUp(speed, easing, callback);
    }
};

$.fn.outerHtml = function(include_scripts) {
    if(include_scripts === undefined){ include_scripts = false; }
    var clone = this.clone();
    var items = jQuery.map(clone, function(element){
        if(jQuery.nodeName(element, "script")){
            if(include_scripts){
                var attributes;
                if(element.attributes){
                    attributes = jQuery.map(element.attributes, function(attribute){
                    return attribute.name + '="' + attribute.value + '" ';
                  });
                }
                return '<' + element.nodeName + ' ' + attributes.join(' ') + ">" + jQuery(element).html() + "</" + element.nodeName +'>';
            } else {
                return '';
            }
        } else {
            return jQuery('<div>').append(element).remove().html();
        }
    });
    return items.join('');
};