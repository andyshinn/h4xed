<?
 require("config.php"); 
 
 $db->open("SELECT songlist.*, historylist.listeners as listeners, historylist.requestID as requestID, historylist.date_played as starttime FROM historylist,songlist WHERE (historylist.songID = songlist.ID) AND (songlist.songtype='S') ORDER BY historylist.date_played DESC",6);
 $history = $db->rows();
 reset($history);
 
 $db->open("SELECT songlist.*, queuelist.requestID as requestID FROM queuelist, songlist WHERE (queuelist.songID = songlist.ID)  AND (songlist.songtype='S') AND (songlist.artist <> '') ORDER BY queuelist.sortID ASC",2);
 $queue = $db->rows();
 reset($queue);
 
 
 //### Calculate the bezt time to refresh the webpage in order to show new updated song information
 //================================================================================================== 
 list($key, $song) = each($history);
 $listeners = $song["listeners"];

 $starttime = strtotime($song["date_played"]);
 $curtime = time(); 
 $timeleft = $starttime+round($song["duration"]/1000)-$curtime;

  //Set refesh interval
 if($timeleft>0) # 30 second minimum wait
   { $timeout = $timeleft;}		# if timeleft is valid, refresh on timeleft (should be end of song)
 else
   { $timeout = 90; }			# otherwise, fall back on 90 second refresh
   
 if(($timeout>180) or ($timeout==0)) $timeout = 180;
 if($timeout<30) $timeout  = 30;
   
 $refreshURL = "playing.php?buster=".date('dhis').rand(1,1000);
 //==================================================================================================
 

$pic_cnt = 0;
function PicName()
{
 global $pic_cnt;
 echo "Picture".$pic_cnt; 
}
 
function NextPicName()
{ 
 global $pic_cnt;
 $pic_cnt += 1;
 PicName();
} 

function PutSongRow(&$song) 
{
 global $rc, $showpic, $darkrow, $lightrow;
 PrepareSong($song);
 
 $rc++;
 $bgcolor = $darkrow;
 if(($rc % 2)==0) $bgcolor = $lightrow;
 
?> 
  <tr bgcolor="<? echo $bgcolor; ?>"> 
  
<?if($showpic){?>
    <td valign="middle" width="1%"> 
<? if($song["haspicture"]) {?>	
  	  <a href="<? echo $song["buycd"]; ?>" target="_blank"><img id="<? NextPicName(); ?>" onError="PictureFail('<? PicName(); ?>')" width="60" height="60" src="<? echo $song["picture"]; ?>" alt="Buy CD!" border=0></a>
<?};?>		  
	</td>
<?}?>	


    <td <?if(!$showpic) echo "colspan=2"?>><font size="2" color="#003366"><small><? 
	  echo $song["combine"]; 
	  if($song["requestid"]!=0) { echo " ~requested~ "; } 
	  ?></small></font></td>
    <td nowrap width="1%"> 
      <p align="center"><font size="2" color="#003366"><a href="<? echo $song["buycd"]; ?>" target="_blank"><img
    src="images/buy.gif" alt="Buy this CD now!" border="0"></a></font> 
    </td>
    <td nowrap width="1%"> 
      <p align="center"><font size="2" color="#003366"><a href="<? echo $song["website"]; ?>" target="_blank"><img
    src="images/home.gif" alt="Artist homepage" border="0"></a></font> 
    </td>
	
	<td nowrap align="center" nowrap width="1%"> 
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

<SCRIPT LANGUAGE="JavaScript">
<!---
 var refreshID = "";
 refreshID = setTimeout("DoRefresh()", <? echo ($timeout*1000); ?>);
 
function DoRefresh()
{
  document.location.href = '<? echo $refreshURL; ?>';
}
//--->
</SCRIPT>

<?if($listeners>0)
echo "There are currently $listeners listeners tuned into this station!<br><br>";
?>
<table border="0" width="98%" cellspacing="0" cellpadding="4">
  <tr bgcolor="#002E5B"> 
    <td colspan="2" nowrap align="left"> 
      <p><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b>Currently Playing</b></font>
    </td>
	<td colspan="3" nowrap align="center"> 
      <p><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b>Links</b></font>
    </td>
    <td nowrap align="left"> 
      <p><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b>Album</b></font>
    </td>
	<td nowrap align="Right"> 
      <p><font face="Verdana, Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b>Time</b></font>
    </td>
  </tr>

<? 
  $rc=0;
  PutSongRow($song); 
  $mainsong = $song;
?>
  

<?
 if(count($queue)>0){?>
<tr bgcolor="<? echo $lightrow; ?>"><td colspan="7">
<b><font size="2" color="#777777">Coming up:</font></b>

<font size="2" color="003366"><b>
<?
 $i=0;
 while(list($key, $song) = each($queue))
 {
  if(empty($song["artist"])) 
   $song["artist"] = 'Unknown';
  
   if($i>0) echo ", ";
   echo $song["artist"]; 
   if($song["requestid"]!=0)
	{ echo " ~requested~"; }
   $i++;
  
 }
?>
</b></font></td></tr>
<?}?>

  <tr bgcolor="#002E5B"> 
    <td colspan="7" nowrap> 
      <p align="left"><b><font size="1" face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF">Recently 
        played songs</font></b> 
    </td>
  </tr>
  
<? 
  $rc=0;
  while(list($key, $song) = each($history))
    PutSongRow($song); 
?>
</table>

</td><td valign='top' align='center'>

<?
####################
# Request Dedication
 require("dedication.php");
#===================   
?>  

<? 
####################
# BuyCD image 
if($mainsong["haspicture"])
 require("buycd.php");
#=================== 
?>

  
<? 
##################
# Top 10 requests
if($showtoprequests) 
  require("top10requests.php");
#===================
?>   
   
   
</td>   
  
<? require("footer.php"); ?>

