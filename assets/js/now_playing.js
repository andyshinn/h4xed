"use strict";
/*global clearInterval: false, clearTimeout: false, document: false, event: false, frames: false, history: false, Image: false, location: false, name: false, navigator: false, Option: false, parent: false, screen: false, setInterval: false, setTimeout: false, window: false, XMLHttpRequest: false, console: false, $: false */

h4xed.settings = {
	ajax_url_now_playing : h4xed.baseUrl + 'playlist/ajax_now_playing'
};
h4xed.now_playing = {
	first_load : true,
	remaining_seconds : 10,
	last_song : false
};

function updateCurrentTimer(remaining) {
	var currentSongTimer = (remaining > 0) ? setTimeout(mainLoop, ((remaining + 5) * 1000)) : setTimeout(mainLoop, 10000);
}

function updateSongHistory(data) {
	var song_item = $("<li>").text(data.song.artist + " - " + data.song.title);
	$("ul.history").children("li").filter(":gt(3)").slideUp('slow', function() {
		$(this).remove();
		song_item.css("display", "list-item").hide().css("opacity", 0).prependTo("ul.history").slideDown('slow').animate({
			opacity : 1
		});
	});
}

function updateNowPlaying(data, now_playing) {
	var song_info_div = $("#song_info");
	song_info_div.animate({
		left : parseInt(song_info_div.css('left'), 10) === 0 ? -song_info_div.outerWidth() : 0
	}, 1000, 'swing', function() {
		song_info_div.animate({
			opacity : 0
		}, 0, 'linear', function() {
			// Build song HTML
			song_info_div.html($("<h2>").text(data.song.artist + " - " + data.song.title));
			song_info_div.append($("<h6>").text("Played at " + data.song.date_played + " with " + data.song.listeners + " listeners"));
			song_info_div.append($("<ul>").append($("<li>").html("We have played <strong>" + data.song.title + "</strong> " + data.song.count_played + " times")).append($("<li>").html("There are " + data.artist_info.titlecount + " songs and " + data.artist_info.albumcount + " albums in our <a href=\"/playlist\">playlist</a> for <strong>" + data.song.artist + "</strong>")).append($("<li>").html("<a rel=\"external\" href=\"http://last.fm/music/" + data.song.artist + "\">Information on <strong>" + data.song.artist + "</strong>, similar artists, and more at Last.fm</a>")));

			song_info_div.animate({
				left : parseInt(song_info_div.css('left'), 10) === 0 ? -song_info_div.outerWidth() : 0
			}, 0, function() {
				song_info_div.animate({
					opacity : 1
				}, 'slow', function() {
					updateSongHistory(now_playing);
				});
			});
		});
	});
}

function mainLoop() {
	$.get(h4xed.settings.ajax_url_now_playing, function(data) {
		if(h4xed.now_playing.first_load) {
			h4xed.now_playing.first_load = false;
			updateCurrentTimer(data.remaining_seconds);
			h4xed.now_playing.last_song = data;
		} else if(data.song.ID > h4xed.now_playing.last_song.song.ID) {
			updateCurrentTimer(data.remaining_seconds);
			updateNowPlaying(data, h4xed.now_playing.last_song);
			h4xed.now_playing.last_song = data;
		} else {
			updateCurrentTimer(data.remaining_seconds);
		}
		h4xed.now_playing.remaining_seconds = data.remaining_seconds;
	}, 'json');
}

$.fn.fadeThenSlideToggle = function(speed, easing, callback) {
	if(this.is(":hidden")) {
		return this.slideDown(speed, easing).fadeTo(speed, 1, easing, callback);
	} else {
		return this.fadeTo(speed, 0, easing).slideUp(speed, easing, callback);
	}
};

$(document).ready(function() {
	mainLoop();
});
