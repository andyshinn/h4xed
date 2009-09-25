<? 

 require("config.php"); 
 
 $db->AddInt($songid);
 $db->open("SELECT * FROM songlist WHERE ID = :songid");
 $song = $db->row();
 
 PrepareSong($song);
?>



<html>
<head>
<title>Song information</title>
<? require("style.css"); ?>
<? require("req/request.java.php"); ?>
</head>

<body bgcolor="#FFFFFF">

<table border=0 width="100%">
<tr><td align="center" valign="top" width="100%">

<div align="center"><center>

<table border="0" width="98%" cellspacing="1" cellpadding="2">
  <tr bgcolor="#002E5B"> 
    <td colspan="5"> 
      <p align="center"><font size="2"><b><font color="#FFFFFF" size="1" face="Verdana, Arial, Helvetica, sans-serif">Song Information</font></b></font>
    </td>
  </tr>
  <tr> 
    <td colspan="5"><img src="images/spacer.gif" width="15" height="13"></td>
  </tr>
  <tr> 
    <td bgcolor="EAEAEA" align="right"><font color="#333333" size="2">Title<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="eaeaea"><b><font color="#003366" size="2"><? echo $song["title"]; ?></font></b></td>
    <td bgcolor="eaeaea" align="right"><font color="#333333" size="2">BuyCD<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="eaeaea" align="center"><a href="<? echo $song["buycd"]; ?>" target="_blank"><img src="images/buy.gif" alt="Buy this CD now!" border="0"></td>
    <td bgcolor="EAEAEA"> 
      <p align="center"><font color="#333333" size="2">Picture</font>
    </td>
  </tr>
  <tr> 
    <td bgcolor="#FFFFFF" align="right"><font color="#333333" size="2">Artist<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="#FFFFFF"><b><font color="#003366" size="2"><? echo $song["artist"]; ?></font></b></td>
    <td bgcolor="#FFFFFF"></td>
    <td bgcolor="#FFFFFF"></td>
    <td bgcolor="#FFFFFF" rowspan="5" valign="middle"> 
      <p align="center"><a href="<? echo $song["buycd"]; ?>" target="_blank"><img src="<? echo $song["picture"]; ?>" alt="Buy CD!" border=0></a>
    </td>
  </tr>

  <tr> 
    <td bgcolor="EAEAEA" align="right"><font color="#333333" size="2">Album<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="eaeaea"><b><font color="#003366" size="2"><? echo $song["album"]; ?></font></b></td>
    <td bgcolor="eaeaea" align="right"><font color="#333333" size="2">Home<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="eaeaea" align="center"><a href="<? echo $song["website"]; ?>" target="_blank"><img src="images/home.gif" alt="Artist homepage" border="0"></a></td>
  </tr>

  <tr> 
    <td bgcolor="#FFFFFF" align="right"><font color="#333333" size="2">Year<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="#FFFFFF"><b><font color="#003366" size="2"><? echo $song["albumyear"]; ?></font></b></td>
    <td bgcolor="#FFFFFF"><font color="#333333" size="2"></font></td>
    <td bgcolor="#FFFFFF"></td>
  </tr>
  <tr> 
    <td bgcolor="EAEAEA" align="right"><font color="#333333" size="2">Genre<img src="images/spacer.gif" width="15" height="13"></font></td>
    <td bgcolor="eaeaea"><b><font color="#003366" size="2"><? echo $song["genre"]; ?></font></b></td>
     <td bgcolor="eaeaea"> 
      <p align="right"><font color="#333333" size="2">Request<img src="images/spacer.gif" width="15" height="13"></font>
    </td>
    <td bgcolor="eaeaea" align="center"><a href="<? echo $song["request"]; ?>"><img src="images/request.gif" alt="Request this song now!" border="0"></a></td>
  </tr>
  
<? if(!empty($song["lyrics"])){?>    
  <tr> 
    <td colspan="5"><img src="images/spacer.gif" width="15" height="13"></td>
  </tr>
  <tr bgcolor="#002E5B"> 
    <td align="right" colspan="5"> 
      <p align="center"><b><font color="#FFFFFF" size="1" face="Verdana, Arial, Helvetica, sans-serif">Lyrics</font></b>
    </td>
  </tr>
  <tr bgcolor="eaeaea"> 
    <td align="right" colspan="5"> 
      <p align="left"><font color="#003366" size="2"><b><? echo $song["lyrics"]; ?></b></font>
    </td>
  </tr>
  
<?}?>  
  
<? if(!empty($song["info"])){?>  
  <tr> 
    <td colspan="5"><img src="images/spacer.gif" width="15" height="13"></td>
  </tr>
  <tr bgcolor="#002E5B"> 
    <td align="right" colspan="5"> 
      <p align="center"><font color="#FFFFFF" size="2"><b><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Information</font></b></font>
    </td>
  </tr>
  <tr bgcolor="eaeaea"> 
    <td align="right" colspan="5"> 
      <p align="left"><font color="#003366" size="2"><b><? echo $song["info"]; ?></b></font>
    </td>
  </tr>
<?}?>  
  
</table>

<? require("footer.php"); ?>
