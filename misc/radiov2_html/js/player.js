document.write('<object id="player" ');
                            
 if (-1 != navigator.userAgent.indexOf("MSIE")) {
document.write('classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" ');
document.write('width=210 height=34>');
}
else {
document.write('type="application/x-ms-wmp" ');
document.write('width=210 height=34>');
//document.write('<embed type="application/x-ms-wmp" pluginspage="http://www.microsoft.com/windows/windowsmedia/download/alldownloads.aspx" src="http://sc-01.h4xed.us:7080" align="middle" width=210 height=34 defaultframe="rightFrame" showstatusbar=false autostart=false />');
}
                            
document.write('<param name="URL" value="http://sc-01.h4xed.us:7080" />');
document.write('<param name="autoStart" value="false" />');
document.write('</object>');