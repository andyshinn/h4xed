<html>
<head>
<title>Edit News Item</title>
<script src="<?php echo site_url('/assets/js/jquery-1.4.2.min.js')?>" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo site_url('/assets/js/jquery.wymeditor.min.js')?>"></script>
<script type="text/javascript">
jQuery(function() {
    jQuery('.wymeditor').wymeditor();
});
</script>
</head>
<body>

<?php echo validation_errors(); ?>

<?php echo form_open('news/edit/'.$news_id); ?>

<p>Title: <input type="text" name="title" value="<?php echo set_value('title', $title)?>" size="50" /></p>

<p>Body: <textarea class="wymeditor" name="body" id="body" cols="65" rows="10"><?php echo set_value('body', $body)?></textarea></p>

<p>Poster: <input type="text" name="poster" value="<?php echo set_value('poster', $poster)?>" size="50" /></p>

<p>Time Posted: <input disabled="true" type="text" name="timestamp" value="<?php echo set_value('timestamp', $timestamp)?>" size="30" /></p>

<p>Visible: <select name="visible">
<option value="0" <?php echo set_select('visible', '0'); ?> >Not Visible</option>
<option value="1" <?php echo set_select('visible', '1', TRUE); ?> >Visible</option>
</select></p>

<input type="submit" value="Submit" class="wymupdate" />

</form>

</body>
</html>