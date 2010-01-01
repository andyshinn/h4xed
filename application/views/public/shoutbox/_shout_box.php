    <legend>Shoutbox</legend>
    <!-- name="add_shout_form"  --> 
  
      <form action="javascript:void(0)" method="post" id="add_shout_form" accept-charset="utf8" style='padding:0'>
        <dl style='margin:0; padding:0'>
          <dt>Username:</dt>  
          <dd>
            <?php
              $data = array(
                            'name'        => 'name',
                            'id'          => 'name',
                            'size'        => '20',
                            'maxlength'   => '30'
                          );
              echo form_input($data);
            ?>
          </dd>  
          <dt>Message:</dt>
          <dd>  
            <?php
              $data = array(
                        'name'        => 'message',
                        'id'          => 'message',
                        'size'        => '62',
                        'maxlength'   => '140'
                       );
              $js = 'onclick="new Ajax.Updater(\'shout_list\', \''.site_url().'shout/add\', {method: \'post\', parameters: $(\'add_shout_form\').serialize(true)}); $(\'message\').value = \'\'; $(\'name\').value = \'\';"';
              echo form_input($data); 
              echo '&nbsp;';
              echo form_submit('submit', 'Shout', $js);
            ?>
          </dd>            
        </dl>
      <?= form_close() ?>
    
      <dl id='shout_list' style='height:220px; border:solid 1px #ccc; overflow:scroll'>
          <? $this->load->view('public/shoutbox/_shout_list', $data); ?>
      </dl>
