<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
		<?php echo $header ?>
		<?php echo $_styles ?>
                <?php echo $_scripts ?>

    </head>
    <body>
        <div id="all">
            <div id="head">
                <?php echo $hlinks ?>
            </div>
            <div id="right">
                <?php echo $right ?>
            </div>
            <div id="main">
                <?php echo $main ?>
            </div>
        </div>
<?php $this->load->view('include/footer') ?>
