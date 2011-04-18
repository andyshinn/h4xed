$(document).ready(function() {
    var news_items = $('#main div.news_item');
    
    $.each(news_items, function(id, data) {
        var imgWidth = $(this).find("img").width();
        
        if (imgWidth > 200) {
            var subject = $(this).children("h2");
            var time = $(this).children("h6");
            var subject_text = subject.text();
            var time_text = time.text();
            time.remove();
            subject.remove();
            var first_image = $(this).find("div.body img:first");
            var news_image_html = $("<div class=\"news_image\" />");
            $(first_image).wrap(news_image_html);
            var news_image = $(this).find("div.news_image");
            $(news_image).append("<h2><span class=\"image_text\">" + subject_text + "</span></h2>");
            $(news_image).append("<h6><span class=\"image_text\">" + time_text + "</span></h6>");
        }

    });
    
});