<html>

<head>

<title>Add News Item</title>
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

<?php echo form_open('news/add'); ?>

<p>Title: <input type="text" name="title" value="" size="50" /></p>

<p>Body: <textarea class="wymeditor" name="body" id="body" cols="80" rows="40"></textarea></p>

<p>Poster: <input type="text" name="poster" value="" size="50" /></p>

<p>Time Posted: <input disabled="true" type="text" name="timestamp" value="<?php echo $standard_date ?>" size="30" /></p>

<p>Visible: <select name="visible">
<option value="0" <?php echo set_select('visible', '0', TRUE); ?> >Not Visible</option>
<option value="1" <?php echo set_select('visible', '1'); ?> >Visible</option>
</select></p>

<input type="submit" value="Submit" class="wymupdate" />

</form>

</body>
</html>