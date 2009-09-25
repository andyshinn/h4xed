<?php

class DBTable {

var $login;
var $link;
var $result;
var $sql;
var $metadb;
var $column_names;
var $res_rows;
var $row_pos;
var $row_cnt;

var $boolvalue; //Default boolean values to use for true/false in parameterized SQL statements
var $params;    //SQL Parameters

function DBTable()
{
 
  $this->boolvalue = array(false => "0", True => "1");
  $this->ClearParams;
}

function DoError($sql = "")
{
$error=MetabaseError($this->metadb);
echo "status=err<br>";
echo "SQL error=$error<br>";
//echo "SQL=$sql<br>";
exit;
}

//===============================================================================
function FindSQLParams(&$sql)
{
 $escaped = false;
 $escapechars = explode(",","\",',`");
 $res = "";
 $cnt = 0;
 for($i=0;$i<strlen($sql);$i++)
 {
   $char = substr($sql,$i,1);
   if(in_array($char,$escapechars))
    //Check if the char itself is not escaped
	if(!(($escaped) && ($i>0) && (substr($sql,$i-1,1)=="\\")))
	  $escaped = !$escaped; 
	  
   if($char==":" && !$escaped)
    {
	  //Add parameterized string
	  $res .= ":p$cnt:";
	  $cnt++;
	  
	  //Find the end of the parameter
	  for($c=$i+1;$c<strlen($sql);$c++)
	   {
	     $char2 = strtolower(substr($sql,$c,1));
		 if(!(($char2>='a' && $char2<='z') || ($char2>='0' && $char2<='9') || ($char2=="_"))) break;
		 $i++;
	   }
	}
   else
     $res .= $char;
 }
 $sql = $res;
 
 return $cnt;
}

//Returns a unix timestamp.
//If $val is a string, it is converted to a timestamp using the strtotime function
function AsTimeStamp($val)
{
 $stamp = $val;
 if(!is_numeric($stamp)) $stamp = strtotime($stamp);		                         
 return $stamp;
}

//Checks SQL string and assign parameters to string.
function FillSQLParams($sql,$params)
{
  if(is_array($params) && (count($params)>0)) 
  {
    $cnt = $this->FindSQLParams($sql);
	$c=0;
    foreach($params as $param)
	{
	  if($param["type"]=="auto")
	   $param["type"] = gettype($param["value"]);
	   
	   switch(strtolower($param["type"]))
	   {
	    //Valid auto-detect types
	    case "boolean"       : $val = $this->boolvalue[$param["value"]];
		                       break;
		case "integer"       : $val = $param["value"]; 
		                       settype($val,"integer");
							   $val = MetabaseGetIntegerFieldValue($this->metadb,$val);
							   break;
		case "double"        : $val = $param["value"]; 
		                       settype($val,"double");
							   $val = MetabaseGetFloatFieldValue($this->metadb,$val);
		                       break;
		case "string"        : $val = MetabaseGetTextFieldValue($this->metadb,$param["value"]); 
		                       break;
		case "NULL"          : $val = 'NULL'; break;
		
		//Custom types
		case "date"          : $stamp = $this->AsTimeStamp($param["value"]);
		                       $val = MetabaseGetDateFieldValue($this->metadb,$stamp);   
		                       break;
							   
		case "time"          : $stamp = $this->AsTimeStamp($param["value"]);	                         
		                       $val = MetabaseGetTimeFieldValue($this->metadb,$stamp);   
		                       break;
							   
		case "datetime"      : $stamp = $this->AsTimeStamp($param["value"]);	                         
		                       $val = MetabaseGetTimestampFieldValue($this->metadb,$stamp);   
		                       break;
							   
		case "statement"     : //WARNING: Use this one with caution - its an open hole for SQL injection!
		                       $val = $param["value"];
							   break;
		
		//Invalid auto-types
		case "array"         : 
		case "object"        : 
		case "resource"      : 
		case "unknown type"  : 
		case "user function" :  $this->DoAbort("Parameter type not supported ($param[type]).");
		                        break;
        
	}//switch
	
    $sql = str_replace(":p$c:",$val,$sql);
	$c++;
  }//foreach
  }//if 
  
  return $sql; 
}

function AddParam($val, $type = "auto")
{
  $param["value"] = $val;
  $param["type"]  = $type;
  $this->params[] = $param;
}

function AddParams($values, $type = "auto")
{
 foreach($values as $val)
   $this->AddParam($val,$type);
}


function SetParams() 
{
  $param_list = func_get_args();
  $this->AddParams($param_list);
} 

function ClearParams()
{
 $this->params = array();
}

function AddStr($val){ $this->AddParam($val,"string"); }
function AddString($val){ $this->AddParam($val,"string"); }
function AddInt($val){ $this->AddParam($val,"integer"); }
function AddInteger($val){ $this->AddParam($val,"integer"); }
function AddFloat($val){ $this->AddParam($val,"double"); }
function AddDouble($val){ $this->AddParam($val,"double"); }
function AddDate($val){ $this->AddParam($val,"date"); }
function AddTime($val){ $this->AddParam($val,"time"); }
function AddDateTime($val){ $this->AddParam($val,"datetime"); }
function AddTimeStamp($val){ $this->AddParam($val,"datetime"); }
function AddBool($val){ $this->AddParam($val,"boolean"); }
function AddStatement($val){ $this->AddParam($val,"statement"); }
function AddNull(){ $this->AddParam(NULL,"statement"); }
//===============================================================================

function connect($login = "")
{
 global $metabasepath;
 if(is_array($login)) $this->login = $login;
 
 $this->login["IncludePath"] = "$metabasepath/";
 
 $error = MetabaseSetupDatabase($this->login, $this->metadb);
 if($error!="")
  {
    echo "Database setup error: $error\n";
    exit;
  }
}

function query($sql, $limit = 0, $skip = 0)
{
 if(count($this->params)>0) { $sql = $this->FillSQLParams($sql,$this->params); $this->ClearParams(); }
 
 $this->res_rows = Array();
 $this->column_names = array();
 $this->sql = $sql;
 
 $skip = (int)$skip;
 $limit = (int)$limit;
  
 if($limit>0)
 {
   $success=MetabaseSetSelectedRowRange($this->metadb, $skip, $limit);
   if(!$success) echo "Warning: Unable to set limit and skip query range.";
 }
 
 //$result=MetabaseQueryAll($this->metadb, $sql, $rows);
 $result=MetabaseQuery($this->metadb, $sql);
 if(!$result) $this->DoError($sql);
 
 if(!MetabaseEndOfResult($this->metadb,$result)) 
 {
   $success=MetabaseGetColumnNames($this->metadb, $result, $column_names);
   if(!$success) echo "Warning: Unable to retrieve column names.";
 
   //Switch Key-Values into a more usuable array
   $this->column_names = array();
   foreach($column_names as $val => $key)
     $this->column_names["$key"] = $val;
      
   $success=MetabaseFetchResultAll($this->metadb, $result, $this->res_rows);
   $this->row_pos = 0;
   $this->row_cnt = count($this->res_rows);
 }
	 

 $this->result = $result;
 return $this->result;
}

function open($sql, $limit = 0, $skip = 0)
{
 //Additional arguments passed are used as SQL Parameters
 if(func_num_args()>3) { $param_list = func_get_args(); $this->AddParams(array_slice($param_list,3)); }
 
 return $this->query($sql, $limit, $skip);
}

function OpenEx($sql)
{
 //Additional arguments passed are used as SQL Parameters
 if(func_num_args()>1) { $param_list = func_get_args(); $this->AddParams(array_slice($param_list,1)); }
 $this->open($sql);
}

//Same as query - except
//this does NOT save query result
function execsql($sql)
{
  //Additional arguments passed are used as SQL Parameters
  if(func_num_args()>1) { $param_list = func_get_args(); $this->AddParams(array_slice($param_list,1)); }
  
  //If we have filled parameters, use them
  if(count($this->params)>0) { $sql = $this->FillSQLParams($sql,$this->params); $this->ClearParams(); }
 
  $this->sql = $sql;
  $result=MetabaseQuery($this->metadb, $sql);
  if(!$result) $this->DoError($sql);
}

function next()
{
 if($this->row_pos >= $this->row_cnt) return FALSE;
 $row = $this->res_rows[$this->row_pos];
 $this->row_pos++;
 
 if(!is_array($row)) return FALSE;
 
 reset($row);
 foreach($row as $key => $val)
  {
   if($this->login["Type"]=="mssql") $val = trim($val); //MSSQL does not return empty fields
   $data[$this->column_names["$key"]] = $val;
  }
   
 return $data;
}

function getrow()
{
 return $this->next();
}

function row()
{
 return $this->next();
}

function getrows()
{
 return $this->rows();
}

function rows()
{
 $rows = Array(); 
 while($row = $this->next())
  $rows[] = $row;
 
 return $rows; 
}

function insert($table,$data)
{
 global $commonpath;
 require_once("$commonpath/form.php");

 CreateSQLQuery($data,$fields,$values,"INSERT");
 
 $this->execsql("INSERT INTO $table ($fields) VALUES($values)");
}

function replace($table,$data)
{
 global $commonpath;
 require_once("$commonpath/form.php");

 CreateSQLQuery($data,$fields,$values,"INSERT");
 
 $this->execsql("REPLACE INTO $table ($fields) VALUES($values)");
}

function update($table, $data, $where)
{
 global $commonpath;
 require_once("$commonpath/form.php");

 CreateSQLQuery($data,$fields,$values,"UPDATE");
 
 $this->execsql("UPDATE $table SET $values WHERE $where ");
}

function delete($table, $where)
{
 $this->execsql("DELETE FROM $table WHERE $where ");
}

function insert_id()
{
 echo "insert_id not supported.";
 Exit;
}

function recordcount()
{
 return $this->row_cnt;
}

function num_rows()
{
 return $this->recordcount();
}

function affected_rows()
{
 MetabaseAffectedRows($this->metadb, $affected_rows);
 return $affected_rows;
}

//Data formatting functions
//==============================================================
function FormatTimestamp($value)
{
 return MetabaseGetTimestampFieldValue($this->metadb, $value);
}

function FormatDate($value)
{
 return MetabaseGetDateFieldValue($this->metadb, $value);
}

function FormatTime($value)
{
 return MetabaseGetTimeFieldValue($this->metadb, $value);
}

function FormatFloat($value)
{
 return MetabaseGetFloatFieldValue($this->metadb, $value);
}

function FormatDecimal($value)
{
 return MetabaseGetDecimalFieldValue($this->metadb, $value);
}

function FormatBoolean($value)
{
 return MetabaseGetBooleanFieldValue($this->metadb, $value);
}

function FormatText($value)
{
 return MetabaseGetTextFieldValue($this->metadb, $value);
}


//XML based configuration for different database drivers
function ReadXMLConfig($xmlfile)
{
 global $commonpath;
 require_once("$commonpath/form.php");
 require_once("$commonpath/xml.php");

 $dbdata = File2Str($xmlfile);
 
 //Remove PHP comments
 $dbdata = str_replace("<?/*","",$dbdata);
 $dbdata = str_replace("*/?>","",$dbdata);
 $dbdata = trim($dbdata);
 
 $dbdata = XML2Arr($dbdata);
 $dbdata = $dbdata["CONFIG"]["DATABASE"];
 EmptyNodeCleanUp($dbdata);
 
 
 switch($dbdata["DRIVER"])
 {
   case "FIREBIRD"   : $this->DBSetup_FireBird($dbdata); break;
   case "MSSQL"      :
   case "ADO"        : $this->DBSetup_MSSQL($dbdata); break;
   case "MYSQL"      : $this->DBSetup_MySQL($dbdata); break;
   case "POSTGRESQL" :
   case "POSTGRES"   : $this->DBSetup_PostgreSQL($dbdata); break;
   default           : echo "Error: Unsupported driver: ".$dbdata["DRIVER"]; exit;
 }
 
 return $this->login; 
}

function DBSetup_MySQL($data)
{
 $dblogin = array();
  $dblogin["Type"]     = "mysql";
  $dblogin["Host"]     = $data["HOST"];
  $dblogin["User"]     = $data["USERNAME"];
  $dblogin["Password"] = $data["PASSWORD"];
  $dblogin["Database"] = $data["DATABASE"];
  
  $options = array();
   $options["Port"]    = $data["PORT"]; 
  $dblogin["Options"]  = $options;
  
 $this->login = $dblogin; 
 return $this->login; 
}

function DBSetup_FireBird($data)
{
 $pathinfo = pathinfo($data["DATABASE"]);
 $pathinfo["extension"] = ".".$pathinfo["extension"];
 $pathinfo["dirname"] .= "/";
 $pathinfo["basename"]= substr($pathinfo["basename"],0,strlen($pathinfo["basename"])-strlen($pathinfo["extension"]));
 
 $dblogin = array();
  $dblogin["Type"]              = "ibase";
  $dblogin["Host"]              = $data["HOST"];
  $dblogin["User"]              = $data["USERNAME"];
  $dblogin["Password"]          = $data["PASSWORD"];
  $dblogin["Database"]          = $pathinfo["basename"];
  
 $options = array();
  $options["Port"]              = $data["PORT"]; //This might not be supported?
  $options["DatabasePath"]      = $pathinfo["dirname"];
  $options["Database"]          = $dblogin["Database"];
  $options["DatabaseExtension"] = $pathinfo["extension"];
  $dblogin["Options"]           = $options;
 
  
 $this->login = $dblogin; 
 return $this->login; 
}

function DBSetup_MSSQL($data)
{
 if((is_string($data["INSTANCENAME"])) && (!empty($data["INSTANCENAME"]))) $data["HOST"] .= "\\" . $data["INSTANCENAME"];
 
 $dblogin = array();
  $dblogin["Type"]     = "mssql";
  $dblogin["Host"]     = $data["HOST"];
  $dblogin["User"]     = $data["USERNAME"];
  if($data["AUTHENTICATION"] != "NT")
    $dblogin["Password"] = $data["PASSWORD"];
  $dblogin["Database"] = $data["DATABASE"];
  
 $this->login = $dblogin; 
 return $this->login;   
}



function DBSetup_PostgreSQL($data)
{
 $dblogin = array();
  $dblogin["Type"]     = "pgsql";
  $dblogin["Host"]     = $data["HOST"];
  $dblogin["User"]     = $data["USERNAME"];
  $dblogin["Password"] = $data["PASSWORD"];
  $dblogin["Database"] = $data["DATABASE"];
  
  $options= array();
    $options["Port"]    = $data["PORT"];;
  $dblogin["Options"]   = $options;
  
 $this->login = $dblogin; 
 return $this->login;   
}

}; // end of class
?>