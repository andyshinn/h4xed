<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title><?= $title ?></title>
    <script type="text/javascript">
        var baseUrl = "<?php echo site_url()?>";
    </script>
		<?= $_scripts ?>
		<?= $_styles ?>
    
	</head>
	<body>
		<div id="container">

			<div id="main_wrapper">
    					<div id="head">
    		        <?= $head ?>
    			</div>
    			<?php if (!empty($right)): ?>
    			<div id="main" style="margin-right: 375px;">
    			<?php else: ?>
    			<div id="main">
    			<?php endif; ?>
	                <?php echo $main ?>
    			</div>
			
			<?php if (!empty($right)): ?>
			<div id="right">
                <?= $right ?>
        	</div>
			<?php endif; ?>
			</div>
		</div>

<script type='text/javascript'>

var _ues = {
host:'h4xed.userecho.com',
forum:'1342',
lang:'en',
tab_icon_show:false,
tab_corner_radius:0,
tab_font_size:24,
tab_image_hash:'RmVlZGJhY2s%3D',
tab_alignment:'right',
tab_text_color:'#FFFFFF',
tab_bg_color:'#591616',
tab_hover_color:'#8C2920'
};

(function() {
    var _ue = document.createElement('script'); _ue.type = 'text/javascript'; _ue.async = true;
    _ue.src = ('https:' == document.location.protocol ? 'https://s3.amazonaws.com/' : 'http://') + 'cdn.userecho.com/js/widget-1.4.gz.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(_ue, s);
  })();

</script>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-427556-5");
pageTracker._trackPageview();
} catch(err) {}
</script>
	</body>
</html>
