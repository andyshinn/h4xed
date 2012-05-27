<div id="now_playing"><h1>Now<span class="orange">Playing</span></h1>
<div id="song_info">
<h2>{song_current}</h2>
<h6><span class="orange">Played at {date_played} PST with {listeners} current listeners</span></h6>
<ul>
    <li>We have played <strong>{title}</strong> {count_played} times</li>
    <li>There are {song_count} songs and {album_count} albums in our <?php echo anchor('playlist', 'playlist') ?> for <strong>{artist}</strong></li>
    <li><a rel="external" href="http://last.fm/music/{artist}">Information on <strong>{artist}</strong>, similar artists, and more at Last.fm</a></li>
</ul>
</div>
</div>