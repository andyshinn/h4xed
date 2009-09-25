<?
 $data = Array();
 $data["msg"] = "$rmessage";
 $data["name"] = "$rname";
 
 //Make sure it is an integer to avoid SQL injection
 settype($requestid,"integer"); 
 settype($songid,"integer"); 
 
 $db = new DBTable();
 $db->connect($samlogin);
 $db->update("requestlist",$data,"(ID = $requestid)");
 
 $db->open("SELECT * FROM songlist WHERE (ID = $songid)");
 $song = $db->row();
 $song["requestid"] = $requestid;
 PrepareSong($song);
 $dedicated = true;
 
 require("req.success.html");
?>