<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>H4XED Radio</title>
<link rel=stylesheet href="/assets/css/style-01.css" type="text/css">
</head>
<body class="oneColElsCtrHdr">
<script charset="utf-8" type="text/javascript" src="http://ws.amazon.com/widgets/q?ServiceVersion=20070822&MarketPlace=US&ID=V20070822/US/hr0d-20/8005/75bcf33d-b8e4-4b1c-955a-c8b585098323"></script>
<div id="container">
<div id="header">
<h1><img src="/assets/images/h4xed_radio-01.png" /> H4XED Metal</h1>
</div>
<div id="mainContent">
<h1>H4XED Radio</h1>
<p>H4XED Metal is the H4XED Radio station bringing you the newest metal, melodeath, and metalcore!</p>
<h2>New Site</h2>
H4XED Radio has a new website coming soon! Apart from a new design, we will have a way to request songs, play station in a popup player, and news on new artist additions to the library.
<h2>Station Information</h2>

<h3>Tune-in Links</h3>
<p>MP3 160k: <a href="http://sc-01.h4xed.us:7080/listen.pls">http://sc-01.h4xed.us:7080</a><br />
aacPlus 96k: <a href="http://sc-01.h4xed.us:7082/listen.pls">http://sc-01.h4xed.us:7082</a></p>
<iframe src="http://rcm.amazon.com/e/cm?t=hr0d-20&o=1&p=8&l=st1&mode=music&search=<?= htmlentities($current->artist, null, 'UTF-8') ?> <?= htmlentities($current->album, null, 'UTF-8') ?>&fc1=000000&lt1=_blank&lc1=3366FF&npa=1&bg1=FFFFFF&f=ifr" marginwidth="0" marginheight="0" width="120" height="240" border="0" frameborder="0" style="border:none; float:right;" scrolling="no"></iframe>
<h3>Currently Playing</h3>


<p><ul>
<li><a type="amzn" search="<?= htmlentities($current->artist, null, 'UTF-8') ?> <?= htmlentities($current->album, null, 'UTF-8') ?>" category="music"><?= $current->artist ?> - <?= htmlentities($current->title, null, 'UTF-8') ?></a> (<?= mdate('%h:%i %A', human_to_unix($current->date_played))?>)</li>
</ul></p>

<h3>History</h3>
<ul>
<?php foreach($history as $song) : ?>
<li><a type="amzn" search="<?= htmlentities($song->artist, null, 'UTF-8') ?> <?= htmlentities($song->album, null, 'UTF-8') ?>" category="music"><?= $song->artist ?> - <?= htmlentities($song->title, null, 'UTF-8') ?></a> (<?= mdate('%h:%i %A', human_to_unix($song->date_played))?>)</li>
<?php endforeach; ?>
</ul>

<h3>Statistics</h3>
<p><img src="<?= $graph_url ?>" /></p>

<h3>Questions? Comments? Requests?</h3>
<p><embed src="http://w.digsby.com/dw.swf?c=wlk8o5bm94240usz" type="application/x-shockwave-flash" wmode="transparent" width="600" height="250"></embed></p>
</div>
<div id="footer">
<p>H4XED Radio</p>
</div>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-427556-5");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
