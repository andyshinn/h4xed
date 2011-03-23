<h3>Shout<span class="orange">Box</span> History</h3>
     <dl>
    <!-- Begin Shoutbox Messages -->
    <div id="messages">
<?php foreach($shoutbox as $shout): ?>
<dt><?=$shout->name ?> <span id="timestamp"> : </span><?=when($shout->time) ?> </dt>
<dd><?=$shout->message ?></dd>
<?php endforeach; ?>
</div>
    <!-- End SHoutbox Messages -->
    </dl>
