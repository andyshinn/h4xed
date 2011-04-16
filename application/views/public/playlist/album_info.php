<h1>Album<span class="orange">Info</span></h1>
<h2>{song_artist} - {song_title}</h2>
<table>
    <tr>
        <td>
            Song ID
        </td>
        <td>
            <a href="{site_url}playlist/song/{song_id}">{song_id}</a>
        </td>
    </tr>
    <tr>
        <td>
            Artist
        </td>
        <td>
            {song_artist}
        </td>
    </tr>
    <tr>
        <td>
            Title
        </td>
        <td>
            <a href="{site_url}playlist/song/{song_id}">{song_title}</a>
        </td>
    </tr>
    <tr>
        <td>
            Album
        </td>
        <td>
            {song_album}
        </td>
    </tr>
    <tr>
        <td>
            Count Played
        </td>
        <td>
            {count_played}
        </td>
    </tr>
</table>
<br/>
<p>
    <div id="pagination">
        {page_links}
    </div>
</p>
<p>
    Songs Found: {playlist_count}W
</p>