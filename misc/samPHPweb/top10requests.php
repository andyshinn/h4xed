<?
 $stamp = mktime(0,0,0,date('m')-$requestdays,date('d'),date('Y'));
 $now = date('Y-m-d H:i:s',$stamp);
 
 //Handle different types of databases' special needs
 switch($db->login["Type"])
 {
  case "ibase" : $orderby = "ORDER BY count(songlist.ID) DESC"; break;
  case "mysql" : 
  default      : $orderby = "ORDER BY cnt DESC"; break;
 }
  
 $now = $db->FormatTimestamp($now);
 $db->open("SELECT songlist.ID, songlist.title, songlist.artist, count(songlist.ID) as cnt 
            FROM requestlist, songlist 
			WHERE   (requestlist.songID = songlist.ID) AND
			        (requestlist.code=200) AND 
					(requestlist.t_stamp>=$now)
			GROUP BY songlist.ID, songlist.artist, songlist.title
			$orderby",10);
			
if($db->num_rows()>0) 
{

function PutRow($song) 
{
global $i;
Preparesong($song);

?>
 
     <font size="2" color="#003366"><small><? echo $i; ?>.
     <a href="javascript:songinfo(<? echo $song["songid"]; ?>)"><? echo $song["artist"]; ?></a></small></font> <font size="2" color="#9F9F9F"><small>(<? echo $song["cnt"]; ?>)</small></font><br>
	 <font size="2" color="#003366"><small>&nbsp;&nbsp;&nbsp;&nbsp;<? echo $song["title"]; ?></small></font><br>
   
<?}?>
 
<table width="100%" bgcolor="<? echo $lightrow; ?>" border="0" cellspacing="0" cellpadding="5">
<tr bgcolor="<? echo $darkrow; ?>"><td nowrap><b><font size="1" face="Verdana, Arial, Helvetica, sans-serif" color="#555555">Top10 Requests</font></b></td></tr> 
<tr><td nowrap>
<?
 $i=0;
 while($song = $db->row())
 {
   $i++;
   PutRow($song);
 }
?>  
</td></tr>
</table>
<br>
<?}?>