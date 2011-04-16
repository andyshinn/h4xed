<div id="shoutbox_history">
<h1><span>Shout</span><span class="orange">Box</span><span>History</span></h1>
	<div id="shoutbox">
			<div id="shouts">
				<dl id="shouts_list">
				<?php foreach($shouts as $shout): ?>
				<dt id="<?=$shout->id?>"><?=$shout->name?><span class="separator"> : </span><abbr class="timestamp"><?=when($shout->timestamp)?></abbr></dt>
				<dd><?=$shout->message?></dd>
				<?php endforeach;?>
				</dl>
			</div>
	</div>
</div>