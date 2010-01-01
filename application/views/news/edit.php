<html>
<head>
<title>Edit News Item</title>
<script src="<?php echo site_url('/assets/js/nicedit/nicEdit.js')?>" type="text/javascript"></script>
<script type="text/javascript">//<![CDATA[
  bkLib.onDomLoaded(function() {
        new nicEditor({xhtml : true, code : true}).panelInstance('body');

  });
  //]]>
</script>
</head>
<body>

<?php echo validation_errors(); ?>

<?php echo form_open('news/edit/'.$news_id); ?>

<p>Title: <input type="text" name="title" value="<?=set_value('title', $title)?>" size="50" /></p>

<p>Body: <textarea name="body" id="body" cols="65" rows="10"><?=set_value('body', $body)?></textarea></p>

<p>Poster: <input type="text" name="poster" value="<?=set_value('poster', $poster)?>" size="50" /></p>

<p>Time Posted: <input disabled="true" type="text" name="timestamp" value="<?=set_value('timestamp', $timestamp)?>" size="30" /></p>

<p>Visible: <select name="visible">
<option value="0" <?php echo set_select('visible', '0'); ?> >Not Visible</option>
<option value="1" <?php echo set_select('visible', '1', TRUE); ?> >Visible</option>
</select></p>

<input type="submit" value="Submit" />

</form>

</body>
</html>