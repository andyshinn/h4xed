var ajax_url_now_playing = '/playlist/ajax_now_playing';
var first_load = true;
var remaining_seconds = 0;

$(document).ready(function() {
    updateNowPlaying();
});

function updateNowPlaying() {
    var song_info_div = $("#song_info");
    
    $.get(ajax_url_now_playing, function(data) {

        var currentSongTimer = (data.remaining_seconds > 0) ? setTimeout(updateNowPlaying, ((data.remaining_seconds+5)*1000)) : setTimeout(updateNowPlaying, 10000);
        
        if (first_load) {
            first_load = false;
        }
        else if (data.remaining_seconds > remaining_seconds) {
            song_info_div.animate({
                left: parseInt(song_info_div.css('left'),10) == 0 ? -song_info_div.outerWidth() : 0
              }, 1000, 'swing', function() {
                    song_info_div.animate({
                        opacity: 0
                    }, 0, 'linear', function() {
                        // Build song HTML
                        console.log(song_info_div);
                      song_info_div.html($("<h2>").text(data.song.artist + " - " + data.song.title));
                      song_info_div.append($("<h6>").text("Played at " + data.song.date_played + " with " + data.song.listeners + " listeners"));
                      song_info_div.append(
                              $("<ul>")
                              .append($("<li>")
                                      .html("We have played <strong>" + data.song.title + "</strong> " + data.song.count_played + " times"))
                              .append($("<li>")
                                      .html("There are " + data.artist_info.titlecount + " songs and " + data.artist_info.albumcount + " albums in our <a href=\"/playlist\">playlist</a> for <strong>" + data.song.artist + "</strong>"))
                              .append($("<li>")
                                      .html("<a rel=\"external\" href=\"http://last.fm/music/" + data.song.artist + "\">Information on <strong>" + data.song.artist + "</strong>, similar artists, and more at Last.fm</a>"))
                                      );
                        
                        song_info_div.animate({
                            left: parseInt(song_info_div.css('left'),10) == 0 ? -song_info_div.outerWidth() : 0
                        }, 0, function() {
                            song_info_div.animate({
                                opacity: 1
                            }, 'slow');
                        });
                    });

              });
        }
        remaining_seconds = data.remaining_seconds;
    }, 'json');
}