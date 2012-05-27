<h3>Tune<span class="orange">In</span></h3>
<div class="box">
<ul>
	<li>MP3 160k
<?php
echo anchor('radio/tunein/7080/pls', 'PLS')?> / <?php
echo anchor('radio/tunein/7080/m3u', 'M3U')?> / <?php
echo anchor('radio/tunein/7080/asx', 'ASX')?>
        </li>
	<li>AAC+ 96k
<?php
echo anchor('radio/tunein/7082/pls', 'PLS')?> / <?php
echo anchor('radio/tunein/7082/m3u', 'M3U')?> / <?php
echo anchor('radio/tunein/7082/asx', 'ASX')?>
        </li>
</ul>
</div>
<h3>Song<span class="orange">History</span></h3>
<div class="box">
<ul class="history">
	{song_history}
	<li>{song}</li>
	{/song_history}
</ul>
</div>

<h3>Shout<span class="orange">Box</span></h3>
<?php $this->load->view('public/shoutbox/_shoutbox_list', array($shouts, "{name}"))?>

<h3>About<span class="orange">Us</span></h3>
<div id="about">
<p>H4XED Metal is a H4XED Radio streaming internet radio station
dedicated to bringing you new, lesser played melodic death, technical
death, thrash, doom, and other metal!</p>
</div>

<h3>Friends<span class="orange">Links</span></h3>
<div class="box">
<ul>
	<li><a rel="external" href="http://leechmaster.com.ar">Leechmaster</a></li>
	<li><a rel="external" href="http://www.warpath-online.com/">Warpath</a></li>
	<li><a rel="external" href="http://www.myspace.com/labeledband">Labeled</a></li>
	<li><a rel="external" href="http://melodicmetal.ru/">MelodicMetal.ru</a></li>
	<li><a rel="external" href="http://www.myspace.com/nothingleftfortomorrow">Nothing Left For Tomorrow</a></li>
	<li><a rel="external" href="http://www.myspace.com/oshiego">Oshiego</a></li>
	<li><a rel="external" href="http://www.internet-radio.org.uk">Internet-Radio.org.uk</a></li>
</ul>
</div>