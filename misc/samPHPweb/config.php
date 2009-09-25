<?php
 
/* ## ======================================== ## */  
  $commonpath = "./common";
  
  //Station general details
  $station  = "My station name";
  $email    = "email@mailbox.com";
  $logo     = "images/logo.gif";
  
  $stationid   = 102003;           //The ID of your registered station on AudioRealm.com
  $sam["host"] = "fizone.com"; //The IP address of the machine SAM is running on (DO NOT use a local IP address like 127.0.0.1 or 192.x.x.x)
  $sam["port"] = "1221";      //The port SAM handles HTTP requests on. Usually 1221.
  
  
  //General options
  $privaterequests = true;  //If False, AudioRealm.com will handle the requests
  $showtoprequests = true;  //Must we show the top 10 requests on the now playing page?
  $requestdays     = 30;    //Show the top10 requests for the last xx days

  $showpic     = false; //Must we show pictures in now playing section?   
  $picture_dir = "pictures/"; //Directory where all your album pictures are stored
  $picture_na  = $picture_dir."na.gif"; //Use this picture if the song has no picture
  
  //Row colors used
  $darkrow  = "#dadada";
  $lightrow = "#F6F6F6";  
 
/* ## ======================================== ## */
  
 $metabasepath = "$commonpath/metabase";
 require("$metabasepath/metabase_interface.php");
 require("$metabasepath/metabase_database.php");

 require_once("common/form.php");
 require_once("common/db.php");
 require_once("common/functions.php");
 
 // Load EGPCS vars into globals (emulates register_globals = On in php.ini)
if (!empty($HTTP_ENV_VARS)) while(list($name, $value) = each($HTTP_ENV_VARS)) $$name = $value;
if (!empty($HTTP_GET_VARS)) while(list($name, $value) = each($HTTP_GET_VARS)) $$name = $value;
if (!empty($HTTP_POST_VARS)) while(list($name, $value) = each($HTTP_POST_VARS)) $$name = $value;
if (!empty($HTTP_COOKIE_VARS)) while(list($name, $value) = each($HTTP_COOKIE_VARS)) $$name = $value;
if (!empty($HTTP_SERVER_VARS)) while(list($name, $value) = each($HTTP_SERVER_VARS)) $$name = $value;
   

 $db = new DBTable();
 
 //Your REMOTE MySQL database login details
 //IMPORTANT: This is the database login details for the database located on the WEBSERVER.
 $db->ReadXMLConfig("dbconfig.xml.php");
 
 //Your LOCAL MySQL database login details
 //This is the login details the webserver will use to contact the local database on the SAM Broadcaster server.
 //This is only used for making dedications from the request window.
 //$db->ReadXMLConfig("samdb.xml.php");
 $samlogin = $db->login; //In most cases the remote database will be the same as the local database.
  
 //Finally connect to the database
 $db->connect();
?>