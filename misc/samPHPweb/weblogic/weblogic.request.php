<?

// We simply save the request data to file so it can be viewed
// You need to parse this data  and handle it accordingly...
$xmldata = $HTTP_RAW_POST_DATA;
 if(empty($xmldata))
  $xmldata = $HTTP_POST_VARS["variable"];

Str2File("c:\input.request.xml",$xmldata);  


/* 
*  Error code ranges do have a special meaning!
* 200 = success
* 600..699 = Minor errors, ie. song previously played, etc.
* 700..799 = Major errors, ie. IP banned
*/

$code = 200;
$msg = "This is my custom message";
$songinfo = "";

//Fill in our XML response
/* ------------- *
//## PLEASE NOTE
// Passing back song information is totally optional!
// About the ONLY time you want to pass back song info would be
// when you need to change the songID of the song requested...
$songinfo = 
   "<song>
     <songID>$songID</songID>
     <artist>$artist</artist>
     <title>$title</title>
     <album>$album</album>
     <duration>$duration</duration>
   </song>";
/* ------------- */ 
   
 $xml = "<?xml version=\"1.0\"?>
<LOGIC>
   <status>
     <code>$code</code>
	 <message>$msg</message>
   </status>
   $songinfo
</LOGIC>";
 
header("Content-type: text/xml"); 
echo $xml;

//========= FUNCTIONS ===============

function Str2File($fname, $data)
{
$temp = "";
$fd = fopen($fname, "w");
if(!$fd)
 echo "Error writing to $fname";
 
if(!fputs($fd, $data))
 echo "Error writing to $fname";
  
fclose($fd);
}
?>