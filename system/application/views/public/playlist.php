<h1>Play<span class="orange">List</span></h1>
<h2><div align="center">{letter_links}{link} {/letter_links}</div></h2>
<p><div id="pagination">{page_links}</div><br /></p>
<table id="playlist">
	<tr>
		<th>Song ID</th>
		<th>Artist</th>
		<th>Title</th>
		<th>Album</th>
		<!-- <th>Count Played</th> -->
	</tr>
	{playlist}
	<tr>
		<td><a href="/playlist/song/{song_id}" class="lbOn">{song_id}</a></td>
		<td>{song_artist}</td>
		<td><a href="/playlist/song/{song_id}" rel="gb_page_center[550, 300]">{song_title}</a></td>
		<td>{song_album}</td>
		<!-- <td>{count_played}</td> -->
		
	</tr>
	{/playlist}
</table>
<br />
<p><div id="pagination">{page_links}</div></p>
<p>Songs Found: {playlist_count}</p>