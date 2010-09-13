<h3>Shout<span class="orange">Box</span></h3>
<div class="box">

<form id="shoutbox" action="<?php echo site_url("shoutbox/backend") ?>">
    <div id="form">
	<div id="fields">
            <input id="name" name="name" type="text" value="{name}" maxlength="35" />
            <textarea id="content" name="content" cols="20" rows="4">Your message!</textarea>
        </div>
	<div id="button">
            <input name="post" type="submit" value="Post" />
        </div>
    </div>
    <!-- Begin Shoutbox Messages -->
    <div id="messages"></div>
    <!-- End SHoutbox Messages -->
</form>
</div>
