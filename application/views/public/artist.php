<h1>Artist<span class="orange">Info</span></h1>
<h2>{artist}</h2>
<p>Albums: {albumcount}<br />
Tracks: {titlecount}</p>
<p></p><table id="artist">
	<caption>{artist} - Albums</caption>
	<tr>
		<th>Album Name</th>
		<th>Tracks</th>
		<th>Year</th>
		<th>Genre</th>
	</tr>
	{albums}
	<tr>
		<td><a href="/playlist/artist/{artist_url}/{album_url}">{album_name}</a></td>
		<td>{album_titlecount}</td>
		<td>{album_year}</td>
		<td>{album_genre}</td>	
	</tr>
	{/albums}
</table>
</p>