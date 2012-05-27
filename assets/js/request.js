var requesting = $("<h5>").text("Requesting song... ").append($("<img />").attr("src", "/assets/images/shoutbox/loading.gif"));
var div = $("<div>");
$(document).ready(function() {
    $('a.modal').click(function(e) {
        e.preventDefault();
        id = $(this).attr('id');
        $.modal(div, {
            overlayClose : true,
            onOpen : function(dialog) {
                dialog.data.html(requesting);
                dialog.overlay.fadeIn('slow', 'swing');
                dialog.data.fadeIn('slow', 'swing', function() {
                    $.get('/playlist/request/' + id, function(htmldata) {
                        dialog.data.fadeOut('fast', 'swing', function() {
                            dialog.data.html(htmldata).fadeIn('fast');
                        });
                    }, 'html');
                });
                dialog.container.fadeIn('slow', 'swing');

            }, onClose : function(dialog) {
                dialog.container.fadeOut('slow', 'swing');
                dialog.data.fadeOut('slow', 'swing');
                dialog.overlay.fadeOut('slow', function() {
                    $.modal.close();
                });
            }
        });
        return false;
    });
});