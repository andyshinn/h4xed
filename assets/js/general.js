$.ajax({
    url: '../shoutbox/get_settings',
    async: false,
    success: function(data) {
       settings = data;
    },
    dataType: "json"
});

$(function() {
    $("#main tbody tr:even").css("background-color", "#dddddd");
});