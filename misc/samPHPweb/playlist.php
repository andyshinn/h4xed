<?
 require("config.php"); 
 
 $where = " WHERE (songtype='S') AND (status=0) ";
 
 Def($start,0);
 Def($limit,50);
 Def($search,"");

 //########## BUILD SEARCH STRING ################
 
 if(!empty($search))
 {
   $words = Array();
   $temp = explode(' ',$search);
   reset($temp);
   while(list($key,$val) = each($temp))
   {
    $val = trim($val);
    if(!empty($val))
	 $words[] = $val;
   }
	 

   $where2 = "";	  
   reset($words);
   while(list($key,$val) = each($words))	 
   {
     if(!empty($where2)) $where2 .= " OR ";
	 $val = "%$val%";
	 $db->AddStr($val); $db->AddStr($val); $db->AddStr($val);
     $where2 .= " (title like :val1) OR (artist like :val2) OR (album like :val3) ";
   }
   $where .= "AND ($where2) ";
}
 
 if((isset($letter)) && (!$letter==""))
 {
  $nextletter = chr(ord($letter)+1);
  if($letter=='0')
   $where .= " AND NOT((artist>='A') AND (artist<'ZZZZZZZZZZZ')) ";
  else
   {
    $db->AddStr($letter); $db->AddStr($nextletter);
    $where .= " AND ((artist>=:letter) AND (artist<:nextletter)) ";
   }
 }
 else
 {
		$letter="";
 }
 
 //########## =================== ################ 
 
 //Calculate total
 $tmp = $db->params; //Save params for second query
 $db->open("SELECT count(*) as cnt FROM songlist $where ");
 $row = $db->row();
 $cnt = $row["cnt"];
 
 //Now grab a section of that
 $db->params = $tmp; //Restore params
 $db->open("SELECT * FROM songlist $where ORDER BY artist ASC, title ASC", $limit, $start);
 
 $first = $start+1;
 $last  = min($cnt,$start+$limit);
 $rc    = $start;
 
 $prevlnk = "";
 $nextlnk = "";
 if($cnt>0)
 {
 
 if(!isset($search))
	 { $search=""; }
  $searchstr = urlencode($search);
  $prev = max(0,$start-$limit);
  if($start>0)
    $prevlnk = "<a href='?start=$prev&limit=$limit&letter=$letter&search=$searchstr'>&lt;&lt; Previous</a>";
   
  $tmp = ($start+$limit);
  if($tmp<$cnt) 
    $nextlnk = "<a href='?start=$tmp&limit=$limit&letter=$letter&search=$searchstr'>Next &gt;&gt;</a>";
 }
    
function PutSongRow($song) 
{
 global $rc, $start, $darkrow, $lightrow;
 
 $rc++;
 $bgcolor = $darkrow;
 if(($rc % 2)==0) $bgcolor = $lightrow;
 
 
 PrepareSong($song);
?> 
  <tr bgcolor="<? echo $bgcolor; ?>"> 
    <td nowrap align="right" width="1%"><font size="2" color="#003366"><small><? echo "$rc"; ?></small></font></td>
    <td nowrap><font size="2" color="#003366">&nbsp;<small><? echo $song["combine"]; ?></small></font></td>
	
	<td nowrap width="1%"> 
      <p align="center"><font size="2" color="#003366"><a href="<? echo $song["request"]; ?>"><img
    src="images/request.gif" alt="Request this song now!" border="0"></a></font> 
    </td>
	
    <td nowrap width="1%"> 
      <p align="center"><font size="2" color="#003366"><a href="<? echo $song["buycd"]; ?>" target="_blank"><img
    src="images/buy.gif" alt="Buy this CD now!" border="0"></a></font> 
    </td>
    <td nowrap width="1%"> 
      <p align="center"><font size="2" color="#003366"><a href="<? echo $song["website"]; ?>" target="_blank"><img
    src="images/home.gif" alt="Artist homepage" border="0"></a></font> 
    </td>
	
	<td nowrap align="center" width="1%"> 
      <font size="2" color="#003366"><a href="javascript:songinfo(<? echo $song["songid"]; ?>)"><img
    src="images/info.gif" alt="Song information" border="0"></a></font> 
    </td>
	
    <td nowrap><font color="#003366" size="2"><small><? echo $song["album"]; ?></small></font></td>
    <td nowrap> 
      <p align="right"><font color="#003366" size="2"><small><strong><? echo $song["mmss"]; ?></strong></small></font>
    </td>
  </tr>
<?
}//PutSongRow

/* ## ===================================================================== ## */
?>

<? require("header.php"); ?>

<? require("search.php"); ?>
<br>

<table border="0" width="98%" cellspacing="0" cellpadding="4">
  <tr bgcolor="#002E5B"> 
    <td colspan="8" nowrap align="left"> 
      <b><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#FFFFFF">Playlist results</font></b>
    </td>
  </tr>	
<? 
  while($song = $db->row())
   PutSongRow($song); 
?>
  
  <tr bgcolor="#E0E0E0"> 
    <td colspan="8" nowrap align="center">
	<? echo "$prevlnk"; ?>
 &nbsp; ( Showing <? echo "$first to $last of $cnt"; ?> ) &nbsp; 	 
	<? echo "$nextlnk"; ?></td>
  </tr>	
  
</table>

<br>
<? require("search.php"); ?>  
<? require("footer.php"); ?>

