<table width="100%" bgcolor="<? echo $lightrow; ?>" border="0" cellspacing="0" cellpadding="5">
<tr bgcolor="<? echo $darkrow; ?>"><td nowrap><b><font size="1" face="Verdana, Arial, Helvetica, sans-serif" color="#555555">Currently Playing</font></b></td></tr> 
<tr><td align="center">
<a href="<? echo $mainsong['buycd']; ?>" target="_blank"><img id="<? NextPicName(); ?>" onError="PictureFail('<? PicName(); ?>')" src="<? echo $mainsong['picture']; ?>" alt="Buy <? echo $mainsong['artist']; ?>'s CD <? echo $mainsong['album']; ?> now!" border="0" width="200"><br>
<font size="2" face="Verdana, Arial, Helvetica, sans-serif"><small>
<? echo $mainsong['artist']; ?>
</small></font>
</a><br>
<font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#003366"><small>
<? echo $mainsong['album']; ?>
</small></font><br>
</td></tr>
</table>
<br>
