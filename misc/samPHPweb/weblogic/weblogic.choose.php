<?
 
//Do simple SQL query that selects the Least Recently Played song
mysql_pconnect("localhost","root","");
$res = mysql_db_query("SAMDB","SELECT * FROM songlist ORDER BY date_played ASC LIMIT 1");
$row = mysql_fetch_array($res);

//Fill our variables with XML "friendly" data
$songID = htmlspecialchars($row["ID"]);
$artist = htmlspecialchars($row["artist"]);
$title = htmlspecialchars($row["title"]);
$album = htmlspecialchars($row["album"]);
$duration = $row["duration"];

//Fill in our XML response
 $xml = "<?xml version=\"1.0\"?>
<LOGIC>
   <song>
     <songID>$songID</songID>
     <artist>$artist</artist>
     <title>$title</title>
     <album>$album</album>
     <duration>$duration</duration>
   </song>
</LOGIC>";
 
header("Content-type: text/xml"); 
echo $xml;

?>