<h3>
	<span>Shout</span><span class="orange">Box</span><span>History</span>
</h3>

<div id="shoutbox">
<dl>
<!-- Begin Shoutbox Messages -->
	<div id="messages">
		<?php foreach($shoutbox as $shout): ?>
		<dt>
			<?php echo $shout->name ?>
			<span id="timestamp">
				:
			</span>
			<?php echo when($shout->time) ?>
		</dt>
		<dd>
			<?php echo $shout->message ?>
		</dd>
		<?php endforeach; ?>
	</div>
<!-- End Shoutbox Messages -->
</dl>
</div>