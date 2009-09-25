<? if($privaterequests){?>
<script language='JavaScript1.2'><? require('req/private.request.php'); ?></script>
<?} else {?>
<script language='JavaScript1.2'><? require('req/audiorealm.request.php'); ?></script>
<?}?>