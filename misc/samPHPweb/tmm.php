<? 
 //This is old obsolete code for iM Tuner devices.
 //We recommend leaving this in your template for backwards compatibility with old devices that
 //still uses the iM Networks tuner interface.
 
 require("config.php"); 

 $db->open("
  SELECT songlist.*
  FROM historylist, songlist 
  WHERE (historylist.songID = songlist.ID) AND (songlist.songtype='S') 
  ORDER BY historylist.date_played DESC",1);

 $song = $db->row();

 echo "song: ".$song["title"]."\n";
 echo "artist: ".$song["artist"]."\n";
 echo "CD_name: ".$song["album"]."\n";
 echo "ecommerceID: ".$song["buycd"]."\n";
?>