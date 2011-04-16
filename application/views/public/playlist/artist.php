<h1>Artist<span class="orange">Info</span></h1>
<h2>{artist}</h2>
<dl>
	<dt>Albums</dt>
	<dd>{albumcount}</dd>
	<dt>Tracks</dt>
	<dd>{titlecount}</dd>
</dl>

<table id="artist">
	<caption>Albums</caption>
	<tr>
		<th>Album Name</th>
		<th>Tracks</th>
		<th>Year</th>
		<th>Genre</th>
	</tr>
	{albums}
	<tr>
		<td><a>{album_name}</a></td>
		<td>{album_titlecount}</td>
		<td>{album_year}</td>
		<td>{album_genre}</td>
	</tr>
	{/albums}
</table>