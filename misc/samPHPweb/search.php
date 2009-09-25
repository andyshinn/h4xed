<form method="POST" action="playlist.php">
  <p>Search 
<? InputText("search",$search,'',20); ?> <input type="submit" value="Go" name="B1">
&nbsp;&nbsp;Display <? InputCombo("limit",$limit,25,'5,10,25,50,100'); ?> results

</p>
</form>

Search by Artist:<br><a href='?letter=0'>0 - 9</a><?
 for($c=ord('A');$c<=ord('Z');$c++)
 {
  $v = chr($c);
  echo ", <a href='?letter=$v'>$v</a>";
 }
?>
<br>
