<?

require("config.php");

$num_hours=6;
$enddate=date("YmdHis");
$startdate=date("YmdHis",mktime(date("h")-$num_hours,date("i"),date("s"),date("m"),date("d"),date("Y")));

$db->open("SELECT historylist.listeners FROM historylist WHERE historylist.date_played BETWEEN $startdate AND $enddate ORDER BY historylist.date_played DESC");
$history=$db->rows();
reset($history);

$params="";
$max_listeners=0;
foreach($history as $index => $song)
{
	if($song["listeners"] > $max_listeners)
		$max_listeners=$song["listeners"];
	$params.="<param name=VAL" . ($index+1) . " value=\"" . ($index+1) . ":" . $song["listeners"] . "\">\n";
}

require("header.php");
?>
		<table align="center" style="border:2px solid black" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td bgcolor="#01669A" colspan="3" align="center">
					<font face="Verdana, Arial, Helvetica" size="2" color="#F5DEB3">
						<b><? echo $station; ?> Listener Count Graph</b>
					</font>
				</td>
			</tr>
			<tr>
				<td bgcolor="#01669A" colspan="3" align="center">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td bgcolor="#01669A" valign="middle">
					<font face="Verdana, Arial, Helvetica" size="2" color="#F5DEB3">
						<b>Number<br>listeners</b>
					</font>
				</td>
				<td bgcolor="#01669A">
					<applet code="linegraph.class" height=320 width=420>
						 <param name=title value="# Listeners for the past <? echo $num_hours; ?> hours (<? echo sizeof($history); ?> songs)">
						 <param name=NumberOfVals value="<? echo sizeof($history); ?>">
						 <param name=LabOffset value="10">
						 <param name=NumberOfLabs value="<? echo $num_hours; ?>">
						 <param name=border value="32">
						 <param name=xmax value="<? echo sizeof($history); ?>">
						 <param name=xmin value="0">
						 <param name=ymax value="<? echo round($max_listeners + ($max_listeners / 3)) ?>">
						 <param name=ymin value="0">
						 <param name=mode value="1">
						 <param name=LineColor_R value="0">
						 <param name=LineColor_G value="0">
						 <param name=LineColor_B value="200">
						 <param name=Grid value="false">
						 <? echo $params; ?>
					</applet>
				</td>
				<td bgcolor="#01669A">
					&nbsp;&nbsp;&nbsp;
				</td>
		  </tr>
			<tr>
				<td bgcolor="#01669A" colspan="3" align="center">
					<font face="Verdana, Arial, Helvetica" size="2" color="#F5DEB3">
						<b>Number songs ago</b>
					</font>
				</td>
			</tr>
		</table>

		<br>
<? require("footer.php");
?>