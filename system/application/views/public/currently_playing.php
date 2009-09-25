<h1>Now<span class="orange">Playing</span></h1>
<h2>{song_current}</h2>
<div class="timestamp">
    Played {date_played} PST - {listeners} Listeners
</div>
<ul>
    <li>
        We have played <strong>{title}</strong> {count_played} times
    </li>
    <li>
        There are {song_count} songs and {album_count} albums in our <?php echo anchor('playlist', 'playlist') ?> for <strong>{artist}</strong>
    </li>
    <li>
        <a href="http://last.fm/music/{artist}" target="_blank">Information on <strong>{artist}</strong>, similar artists, and more at Last.fm</a>
    </li>
</ul>