<p>
<table id="tracks">
	<caption>{album_name} - Tracks</caption>
	<tr>
		<th>Track #</th>
		<th>Title</th>
		<th>Album</th>
		<th>Year</th>
		<th>Genre</th>
		<th>Duration</th>
	</tr>
	{tracks}
	<tr>
		<td><a href="/playlist/song/{song_id}" rel="gb_page_center[550, 300]">{track_no}</a></td>
		<td><a href="/playlist/song/{song_id}" rel="gb_page_center[550, 300]">{track_title}</a></td>
		<td>{album_name}</td>
		<td>{track_year}</td>
		<td>{track_genre}</td>
		<td>{track_time2}</td>	
	</tr>
	{/tracks}
</table>
</p>