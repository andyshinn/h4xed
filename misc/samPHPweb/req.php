<?
 require_once("config.php"); 
 require_once("common/xml.php");
 
 if(empty($requestid))
   require_once("req/req.php");
 else
   require_once("req/req.dedication.php");
?>