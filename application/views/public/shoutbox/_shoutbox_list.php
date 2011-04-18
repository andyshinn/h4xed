<div class="box">
	<div id="shoutbox">
		<div id="shoutbox_notice">
			<div id="notice" style="display: none;"><span class="notice_text">Loading... </span> <?=img('assets/images/shoutbox/loading.gif')?></div>
			<div id="error" style="display: none;"><span class="error_text">Error... </span> <?=img('assets/images/shoutbox/notification_error.png')?></div>
			<div id="warning" style="display: none;"><span class="warning_text">Warning... </span> <?=img('assets/images/shoutbox/notification_warning.png')?></div>
		</div>
    	<div id="shoutbox_form">
    		<form action="shoutbox/post" style="display: none;">
				<div id="fields">
            		<input id="name" name="name" type="text" value="<?=$name?>" maxlength="35" />
            		<textarea id="message" name="message" cols="10" rows="3">Your message</textarea>
        		</div>
        		<div id="button">
            		<input name="post" type="submit" value="Post" />
        		</div>
        		<div id="history">
        		    <?php echo anchor('shoutbox/history', 'View complete shoutbox history'); ?>
        		</div>
        	</form>
    	</div>
    	<div id="onlinebox">
    	<ul id="online">
    		<li>No online users</li>
    	</ul>
    	</div>
			<div id="shouts" style="display: none; ">

			</div>
	</div>
</div>