$.ajax({
    url: '../shoutbox/get_settings',
    async: false,
    success: function(data) {
       h4xed.settings = data;
    },
    dataType: "json"
});

$(function() {
    $("#main tbody tr:even").css("background-color", "#dddddd");
});