<html>

<head>
<title><? echo $station; ?></title>
<? require("style.css"); ?>
<script language="JavaScript1.2"><? require("songinfo.js"); ?></script>
<script language='JavaScript1.2' src='http://www.audiorealm.com/player/player.js.html?srefID=1&subscription=no'></script>
<? require("req/request.java.php"); ?>
<script language="JavaScript1.2">
function PictureFail(picname) {
    if (document.images)
	 {
        document.images[picname].width   = 1;
		document.images[picname].height  = 1;
	 }
 }
</script>
</head>

<body>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="1%" align="center"><a href="playing.php"><img border="0" src="<? echo $logo; ?>"></a><br>
      <img border="0" src="images/spacer.gif" width="15" height="13"></td>
    <td width="99%" valign="middle" align="left"><b><font size="5"> &nbsp; <? echo $station; ?></font></b></td>
  </tr>
  <tr>
    <td width="100%" colspan="2" bgcolor="#000080"><img border="0" src="images/spacer.gif" width="1" height="1"></td>
  </tr>
</table>

<br>

<table border=0 cellspacing=5 cellpadding=5>
<tr>
 <td align="center" valign="top" width="1%">
 <? require("nav.php"); ?>
 <br>
 <br>
 <? require("partners.php"); ?>
 
 </td>
 <td align="left" valign="top" width="99%">



