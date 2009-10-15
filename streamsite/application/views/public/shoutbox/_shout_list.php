<?php
  if(count($shout_list) > 0) 
  {
    foreach($shout_list as $shout):
  ?>      
      <dt style='width:6em'><?=$shout['name']?>:</dt>
      <dd style='margin:-1.4em 0 0.54em 6em'><?=$shout['message']?></dd>
<?php
    endforeach;
  }else{
?>
      <dt>No shouts</dt>
<?php } ?>