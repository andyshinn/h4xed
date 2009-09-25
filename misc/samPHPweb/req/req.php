<?
Def($samhost, $sam["host"]);
Def($samport, $sam["port"]);
Def($dedicated, false);

if ( empty($samhost))DoError(800);
//if(($samhost=="127.0.0.1") OR ($samhost=="localhost")) DoError(801);
if ( empty($songid))DoError(802);

settype($songid, "integer"); //Make sure songID is an integer to avoid SQL injection

$host = $GLOBALS["REMOTE_ADDR"];

$request = "GET /req/?songID=$songid&host=".urlencode($host)." HTTP\1.0\r\n\r\n";

$xmldata = "";
$fd = @fsockopen($samhost, $samport, $errno, $errstr, 30);
//$fd = fopen("http://$samhost:$samport/req/?songID=$songID&host=".urlencode($host),"r");
//echo "fd=$fd";
if (! empty($fd))
{
    fputs($fd, $request);
    $line = "";
    while (!($line == "\r\n"))
    {
        $line = fgets($fd, 128);
    } // strip out the header
    while ($buffer = fgets($fd, 4096))
    {
        $xmldata .= $buffer;
    }
    fclose($fd);
}
else DoError(803);

if ( empty($xmldata))DoError(804);

//$xmldata = File2Str($url);

//Header("Content-type:text/xml");
// echo $xmldata;

//#################################
//      Initialize data
//#################################
$tree = XML2Array($xmldata);
$request = Keys2Lower($tree["REQUEST"]);

$code = $request["status"]["code"];
$message = $request["status"]["message"];
$requestid = $request["status"]["requestid"];
if ( empty($code))DoError(804);

if ($requestid > 0)
{
    /*== If your database is located on a remote host, you must specify local SAM login details here ===*/
    $db = new DBTable();
    $db->connect($samlogin);
    $db->open("SELECT songlist.*, songlist.ID as songID FROM requestlist, songlist 
           WHERE (songlist.ID = requestlist.songID) AND (requestlist.ID = $requestid)", 1);

    if ($song = $db->row())
    {
        if (! isset ($song["songid"]))
        {
            $song["songid"] = 0;
        }
        $song["requestid"] = $requestid;
        PrepareSong($song);
    }

}


if ($code == 200)
{
    require ("req/req.success.html");
}
else
{
    require ("req/req.failed.html");
}


function DoError($code)
{
    global $samhost, $samport, $errno, $errstr;


    switch($code)
    {
        case 800:
            $message = "SAM host must be specified";
            break;
        case 801:
            $message = "SAM host can not be 127.0.0.1 or localhost";
            break;
        case 802:
            $message = "Song ID must be valid";
            break;
        case 803:
            $message = "Unable to connect to $samhost:$samport. Station might be offline.<br>The error returned was $errstr ($errno).";
            break;
        case 804:
            $message = "Invalid data returned!";
            break;
    }
    require ("req/req.failed.html");
    exit ;
}
?>
