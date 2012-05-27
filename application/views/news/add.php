<html>
<head>
<title>Add News Item</title>
<script src="<?php echo site_url('/assets/js/nicedit/nicEdit.js')?>" type="text/javascript"></script>
<script type="text/javascript">//<![CDATA[
  bkLib.onDomLoaded(function() {
        new nicEditor({fullPanel : true, xhtml : true }).panelInstance('body');

  });
  //]]>
</script>
</head>
<body>

<?php echo validation_errors(); ?>

<?php echo form_open('news/add'); ?>

<p>Title: <input type="text" name="title" value="" size="50" /></p>

<p>Body: <textarea name="body" id="body" cols="80" rows="10"></textarea></p>

<p>Poster: <input type="text" name="poster" value="" size="50" /></p>

<p>Time Posted: <input disabled="disabled" type="text" name="timestamp" value="<?=standard_date()?>" size="30" /></p>

<p>Visible: <select name="visible">
<option value="0" <?php echo set_select('visible', '0', TRUE); ?> >Not Visible</option>
<option value="1" <?php echo set_select('visible', '1'); ?> >Visible</option>
</select></p>

<input type="submit" value="Submit" />

<?=form_close(); ?>

</body>
</html>