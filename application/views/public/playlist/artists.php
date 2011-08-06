<h1>Artist<span class="orange">search</span></h1>
<h2>{letter_links}{link} {/letter_links}</h2>
<div class="pagination">{page_links}</div>
<table id="artist">
	<tr align="left">
		<th>Artist</th>
		<th>Songs</th>
		<th>Albums</th>
	</tr>
	{artist}
	<tr>
		<td><?php echo anchor("/playlist/artist/{artist_url}", "{artist}")?></td>
		<td>{titlecount}</td>
		<td>{albumcount}</td>	
	</tr>
	{/artist}
</table>
<div class="pagination">{page_links}</div>
<p>Artists Found: {artist_count}</p>