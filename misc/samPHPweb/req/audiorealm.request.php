function request(songid)
{
 var samhost = "<? echo $sam["host"]; ?>";
 var samport = "<? echo $sam["port"]; ?>";
 var path = "http://request.audiorealm.com/req/";

 reqwin = window.open(path+"req.html?songID="+songid+'&samport='+samport+'&samhost='+samhost, "_AR_request", "location=no,status=no,menubar=no,scrollbars=no,resizeable=yes,height=350,width=550");
}
