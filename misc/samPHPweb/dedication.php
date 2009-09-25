<?
$requestid = $mainsong['requestid'];
if($requestid>0)
{
 settype($requestid,"integer"); //Make sure it is an integer to avoid SQL injection
 $db->open("SELECT name, msg FROM requestlist WHERE (ID = $requestid)",1);
			
if(($info = $db->row()) and (!empty($info['msg'])))
{ 
?>
<table width="100%" bgcolor="<? echo $lightrow; ?>" border="0" cellspacing="0" cellpadding="5">
<tr bgcolor="<? echo $darkrow; ?>"><td nowrap><b><font size="1" face="Verdana, Arial, Helvetica, sans-serif" color="#555555">Dedication</font></b></td></tr> 
<tr><td>
<font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#464646"><small>
<? 
$info['msg'] = stripslashes($info['msg']);
echo Text2Html(trim($info['msg'])); ?><br>
</small></font>
<br>
<font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#003366"><small>Dedicated by <br>&nbsp;&nbsp;&nbsp;<b><? echo stripslashes($info['name']); ?></b></small></font>

</td></tr>
</table>
<br>
<?}
}?>