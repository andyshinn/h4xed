var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};

BrowserDetect.init();

document.write('<object id="player" ');

if (BrowserDetect.browser == "MSIE" || "Explorer") {
    document.write('classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" ');
    document.write('width=210 height=34>');
}
else 
    if (BrowserDetect.browser == "Apple") {
        document.write('classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" ');
        document.write('codebase="http://www.apple.com/qtactivex/qtplugin.cab"');
        document.write('width="210" height="100">');
        document.write('<param name="src" value="icy://sc-01.h4xed.us:7080" />');
        document.write('<param name="autoplay" value="false" />');
        document.write('<embed pluginspage="http://www.apple.com/quicktime/download/"');
        document.write('srv="UNeedQT.qtif" type="image/x-quicktime"');
        document.write('width="210" height="100"');
        document.write('qtsrc="icy://sc-01.h4xed.us:7080"');
        document.write('autoplay="false"> ');
        document.write('</embed>');
    }
    else {
    
        document.write('type="application/x-ms-wmp" ');
        document.write('width=210 height=34>');
    //document.write('<embed type="application/x-ms-wmp" pluginspage="http://www.microsoft.com/windows/windowsmedia/download/alldownloads.aspx" src="http://sc-01.h4xed.us:7080" align="middle" width=210 height=34 defaultframe="rightFrame" showstatusbar=false autostart=false />');
    }

document.write('<param name="URL" value="http://sc-01.h4xed.us:7080" />');
document.write('<param name="autoStart" value="false" />');
document.write('</object>');
