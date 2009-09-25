<?php
/*
 * metabase_database.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_database.php,v 1.88 2003/01/08 04:35:34 mlemos Exp $
 *
 */

define("METABASE_TYPE_TEXT",0);
define("METABASE_TYPE_BOOLEAN",1);
define("METABASE_TYPE_INTEGER",2);
define("METABASE_TYPE_DECIMAL",3);
define("METABASE_TYPE_FLOAT",4);
define("METABASE_TYPE_DATE",5);
define("METABASE_TYPE_TIME",6);
define("METABASE_TYPE_TIMESTAMP",7);
define("METABASE_TYPE_CLOB",8);
define("METABASE_TYPE_BLOB",9);

$metabase_registered_transactions_shutdown=0;

$metabase_databases=array();

Function MetabaseParseConnectionArguments($connection,&$arguments)
{
	$parameters=parse_url($connection);
	if(!IsSet($parameters["scheme"]))
		return("it was not specified the connection type argument");
	$arguments["Type"]=$parameters["scheme"];
	if(IsSet($parameters["host"]))
		$arguments["Host"]=UrlDecode($parameters["host"]);
	if(IsSet($parameters["user"]))
		$arguments["User"]=UrlDecode($parameters["user"]);
	if(IsSet($parameters["pass"]))
		$arguments["Password"]=UrlDecode($parameters["pass"]);
	if(IsSet($parameters["port"]))
		$arguments["Options"]["Port"]=$parameters["port"];
	if(IsSet($parameters["path"]))
		$arguments["Database"]=UrlDecode(substr($parameters["path"],1));
	if(IsSet($parameters["query"]))
	{
		$options=explode("&",$parameters["query"]);
		for($option=0;$option<count($options);$option++)
		{
			if(GetType($equal=strpos($options[$option],"="))!="integer")
				return($options[$option]." connection option argument does not specify a value");
			$argument=UrlDecode(substr($options[$option],0,$equal));
			$value=UrlDecode(substr($options[$option],$equal+1));
			if(GetType($slash=strpos($argument,"/"))=="integer")
			{
				if(substr($argument,0,$slash)!="Options")
					return("it was not specified a valid conection option argument");
				$arguments["Options"][substr($argument,$slash+1)]=$value;
			}
			else
				$arguments[$argument]=$value;
		}
	}
	return("");
}

Function MetabaseLoadClass($include,$include_path,$type)
{
	$separator="";
	$directory_separator=(defined("DIRECTORY_SEPARATOR") ? DIRECTORY_SEPARATOR : "/");
	$length=strlen($include_path);
	if($length)
	{
		if($include_path[$length-1]!=$directory_separator)
			$separator=$directory_separator;
	}
	if(file_exists($include_path.$separator.$include))
	{
		include($include_path.$separator.$include);
		return("");
	}
	if(function_exists("ini_get")
	&& strlen($php_include_paths=ini_get("include_path")))
	{
		$paths=explode((defined("PHP_OS") && !strcmp(substr(PHP_OS,0,3),"WIN")) ? ";" : ":",$php_include_paths);
		for($path=0;$path<count($paths);$path++)
		{
			$php_include_path=$paths[$path];
			$length=strlen($php_include_path);
			if($length)
			{
				if($php_include_path[$length-1]!=$directory_separator)
					$separator=$directory_separator;
			}
			if(file_exists($php_include_path.$separator.$include))
			{
				include($php_include_path.$separator.$include);
				return("");
			}
		}
	}
	$directory=0;
	if(strlen($include_path)==0
	|| ($directory=@opendir($include_path)))
	{
		if($directory)
			closedir($directory);
		return("it was not specified an existing $type file ($include)".(strlen($include_path)==0 ? " and no Metabase IncludePath option was specified in the setup call" : ""));
	}
	return("it was not specified a valid $type include path");
}

Function MetabaseSetupInterface(&$arguments,&$db)
{
	if(IsSet($arguments["Connection"])
	&& strlen($error=MetabaseParseConnectionArguments($arguments["Connection"],$arguments)))
		return($error);
	if(IsSet($arguments["Type"]))
	{
		if(GetType($dash=strpos($arguments["Type"],"-"))=="integer")
		{
			$type=substr($arguments["Type"],0,$dash);
			$sub_type=substr($arguments["Type"],$dash+1);
		}
		else
		{
			$type=$arguments["Type"];
			$sub_type="";
		}
	}
	else
		$type=$sub_type="";
	$sub_include=$sub_included="";
	switch($type)
	{
		case "ibase";
			$include="metabase_ibase.php";
			$class_name="metabase_ibase_class";
			$included="METABASE_IBASE_INCLUDED";
			break;
		case "ifx";
			$include="metabase_ifx.php";
			$class_name="metabase_ifx_class";
			$included="METABASE_IFX_INCLUDED";
			break;
		case "msql";
			$include="metabase_msql.php";
			$class_name="metabase_msql_class";
			$included="METABASE_MSQL_INCLUDED";
			break;
		case "mssql";
			$include="metabase_mssql.php";
			$class_name="metabase_mssql_class";
			$included="METABASE_MSSQL_INCLUDED";
			break;
		case "mysql";
			$include="metabase_mysql.php";
			$class_name="metabase_mysql_class";
			$included="METABASE_MYSQL_INCLUDED";
			break;
		case "pgsql";
			$include="metabase_pgsql.php";
			$class_name="metabase_pgsql_class";
			$included="METABASE_PGSQL_INCLUDED";
			break;
		case "odbc";
			$include="metabase_odbc.php";
			$class_name="metabase_odbc_class";
			$included="METABASE_ODBC_INCLUDED";
			switch($sub_type)
			{
				case "":
					break;
				case "msaccess":
					$sub_include="metabase_odbc_msaccess.php";
					$class_name="metabase_odbc_msaccess_class";
					$sub_included="METABASE_ODBC_MSACCESS_INCLUDED";
					break;
				default:
					return("\"$sub_type\" is not a supported ODBC database sub type");
			}
			break;
		case "oci";
			$include="metabase_oci.php";
			$class_name="metabase_oci_class";
			$included="METABASE_OCI_INCLUDED";
			break;
		case "sqlite";
			$include="metabase_sqlite.php";
			$class_name="metabase_sqlite_class";
			$included="METABASE_SQLITE_INCLUDED";
			break;
		case "":
			$included=(IsSet($arguments["IncludedConstant"]) ? $arguments["IncludedConstant"] : "");
			if(!IsSet($arguments["Include"])
			|| !strcmp($include=$arguments["Include"],""))
				return(IsSet($arguments["Include"]) ? "it was not specified a valid database include file" : "it was not specified a valid DBMS driver type");

			$sub_included=(IsSet($arguments["SubIncludedConstant"]) ? $arguments["SubIncludedConstant"] : "");
			if(!IsSet($arguments["SubInclude"])
			|| !strcmp($sub_include=$arguments["SubInclude"],""))
				return(IsSet($arguments["SubInclude"]) ? "it was not specified a valid database sub-include file" : "it was not specified a valid DBMS sub-driver type");

			if(!IsSet($arguments["ClassName"])
			|| !strcmp($class_name=$arguments["ClassName"],""))
				return("it was not specified a valid database class name");
			break;
		default:
			return("\"$type\" is not a supported driver type");
	}
	$include_path=(IsSet($arguments["IncludePath"]) ? $arguments["IncludePath"] : "");
	$length=strlen($include_path);
	$directory_separator=(defined("DIRECTORY_SEPARATOR") ? DIRECTORY_SEPARATOR : "/");
	$separator="";
	if($length)
	{
		if($include_path[$length-1]!=$directory_separator)
			$separator=$directory_separator;
	}
	if(strlen($included)
	&& !defined($included))
	{
		$error=MetabaseLoadClass($include,$include_path,"DBMS driver");
		if(strlen($error))
			return($error);
	}
	if(strlen($sub_included)
	&& !defined($sub_included))
	{
		$error=MetabaseLoadClass($sub_include,$include_path,"DBMS sub driver");
		if(strlen($error))
			return($error);
	}
	$db=new $class_name;
	$db->include_path=$include_path;
	if(IsSet($arguments["Host"]))
		$db->host=$arguments["Host"];
	if(IsSet($arguments["User"]))
		$db->user=$arguments["User"];
	if(IsSet($arguments["Password"]))
		$db->password=$arguments["Password"];
	if(IsSet($arguments["Persistent"]))
		$db->persistent=$arguments["Persistent"];
	if(IsSet($arguments["Debug"]))
		$db->debug=$arguments["Debug"];
	$db->decimal_places=(IsSet($arguments["DecimalPlaces"]) ? $arguments["DecimalPlaces"] : 2);
	$db->lob_buffer_length=(IsSet($arguments["LOBBufferLength"]) ? $arguments["LOBBufferLength"] : 8000);
	if(IsSet($arguments["LogLineBreak"]))
		$db->log_line_break=$arguments["LogLineBreak"];
	if(IsSet($arguments["Options"]))
		$db->options=$arguments["Options"];
	if(strlen($error=$db->Setup()))
		return($error);
	if(IsSet($arguments["Database"]))
		$db->SetDatabase($arguments["Database"]);
	return("");
}

Function MetabaseSetupDatabaseObject($arguments,&$db)
{
	global $metabase_databases;

	$database=count($metabase_databases)+1;
	if(strcmp($error=MetabaseSetupInterface($arguments,$db),""))
		Unset($metabase_databases[$database]);
	else
	{
		eval("\$metabase_databases[\$database]= &\$db;");
		$db->database=$database;
	}
	return($error);
}

Function MetabaseCloseSetup($database)
{
	global $metabase_databases;

	$metabase_databases[$database]->CloseSetup();
	$metabase_databases[$database]="";
}

Function MetabaseNow()
{
	return(strftime("%Y-%m-%d %H:%M:%S"));
}

Function MetabaseToday()
{
	return(strftime("%Y-%m-%d"));
}

Function MetabaseTime()
{
	return(strftime("%H:%M:%S"));
}

Function MetabaseShutdownTransactions()
{
	global $metabase_databases;

	for(Reset($metabase_databases),$database=0;$database<count($metabase_databases);Next($metabase_databases),$database++)
	{
		$metabase_database=Key($metabase_databases);
		if($metabase_databases[$metabase_database]->in_transaction
		&& MetabaseRollbackTransaction($metabase_database))
			MetabaseAutoCommitTransactions($metabase_database,1);
	}
}

Function MetabaseDefaultDebugOutput($database,$message)
{
	global $metabase_databases;

	$metabase_databases[$database]->debug_output.="$database $message".$metabase_databases[$database]->log_line_break;
}

class metabase_database_class
{
	/* PUBLIC DATA */

	var $database=0;
	var $host="";
	var $user="";
	var $password="";
	var $options=array();
	var $supported=array();
	var $persistent=1;
	var $database_name="";
	var $warning="";
	var $affected_rows=-1;
	var $auto_commit=1;
	var $prepared_queries=array();
	var $decimal_places=2;
	var $first_selected_row=0;
	var $selected_row_limit=0;
	var $lob_buffer_length=8000;
	var $escape_quotes="";
	var $log_line_break="\n";

	/* PRIVATE DATA */

	var $lobs=array();
	var $clobs=array();
	var $blobs=array();
	var $last_error="";
	var $in_transaction=0;
	var $debug="";
	var $debug_output="";
	var $pass_debug_handle=0;
	var $result_types=array();
	var $error_handler="";
	var $manager;
	var $include_path="";
	var $manager_included_constant="";
	var $manager_include="";
	var $manager_sub_included_constant="";
	var $manager_sub_include="";
	var $manager_class_name="";

	/* PRIVATE METHODS */

	Function EscapeText(&$text)
	{
		if(strcmp($this->escape_quotes,"'"))
			$text=str_replace($this->escape_quotes,$this->escape_quotes.$this->escape_quotes,$text);
		$text=str_replace("'",$this->escape_quotes."'",$text);
	}

	/* PUBLIC METHODS */

	Function Close()
	{
	}

	Function CloseSetup()
	{
		if($this->in_transaction
		&& $this->RollbackTransaction()
		&& $this->AutoCommitTransactions(1))
			$this->in_transaction=0;
		$this->Close();
	}

	Function Debug($message)
	{
		if(strcmp($function=$this->debug,""))
		{
			if($this->pass_debug_handle)
				$function($this->database,$message);
			else
				$function($message);
		}
	}

	Function DebugOutput()
	{
		return($this->debug_output);
	}

	Function SetDatabase($name)
	{
		$previous_database_name=$this->database_name;
		$this->database_name=$name;
		return($previous_database_name);
	}

	Function RegisterTransactionShutdown($auto_commit)
	{
		global $metabase_registered_transactions_shutdown;

		if(($this->in_transaction= !$auto_commit)
		&& !$metabase_registered_transactions_shutdown)
		{
			register_shutdown_function("MetabaseShutdownTransactions");
			$metabase_registered_transactions_shutdown=1;
		}
		return(1);
	}

	Function CaptureDebugOutput($capture)
	{
		$this->pass_debug_handle=$capture;
		$this->debug=($capture ? "MetabaseDefaultDebugOutput" : "");
	}

	Function SetError($scope,$message)
	{
		$this->last_error=$message;
		$this->Debug($scope.": ".$message);
		if(strcmp($function=$this->error_handler,""))
		{
			$error=array(
				"Scope"=>$scope,
				"Message"=>$message
			);
			$function($this,$error);
		}
		return(0);
	}

	Function LoadExtension($scope,$extension,$included_constant,$include)
	{
		if(strlen($included_constant)==0
		|| !defined($included_constant))
		{
			$error=MetabaseLoadClass($include,$this->include_path,$extension);
			if(strlen($error))
				return($this->SetError($scope,$error));
		}
		return(1);
	}

	Function LoadManager($scope)
	{
		if(IsSet($this->manager))
			return(1);
		if(!$this->LoadExtension($scope,"database manager","METABASE_MANAGER_DATABASE_INCLUDED","manager_database.php"))
			return(0);
		if(strlen($this->manager_class_name))
		{
			if(strlen($this->manager_include)==0)
				return($this->SetError($scope,"it was not configured a valid database manager include file"));
			if(!$this->LoadExtension($scope,"database manager",$this->manager_included_constant,$this->manager_include))
				return(0);
			if(strlen($this->manager_sub_include)
			&& !$this->LoadExtension($scope,"database manager",$this->manager_sub_included_constant,$this->manager_sub_include))
				return(0);
			$class_name=$this->manager_class_name;
		}
		else
			$class_name="metabase_manager_database_class";
		$this->manager=new $class_name;
		return(1);
	}

	Function CreateDatabase($database)
	{
		if(!$this->LoadManager("Create database"))
			return(0);
		return($this->manager->CreateDatabase($this,$database));
	}

	Function DropDatabase($database)
	{
		if(!$this->LoadManager("Drop database"))
			return(0);
		return($this->manager->DropDatabase($this,$database));
	}

	Function CreateTable($name,&$fields)
	{
		if(!$this->LoadManager("Create table"))
			return(0);
		return($this->manager->CreateTable($this,$name,$fields));
	}

	Function DropTable($name)
	{
		if(!$this->LoadManager("Drop table"))
			return(0);
		return($this->manager->DropTable($this,$name));
	}

	Function AlterTable($name,&$changes,$check)
	{
		if(!$this->LoadManager("Alter table"))
			return(0);
		return($this->manager->AlterTable($this,$name,$changes,$check));
	}

	Function ListTables(&$tables)
	{
		if(!$this->LoadManager("List tables"))
			return(0);
		return($this->manager->ListTables($this,$tables));
	}

	Function ListTableFields($table,&$fields)
	{
		if(!$this->LoadManager("List table fields"))
			return(0);
		return($this->manager->ListTableFields($this,$table,$fields));
	}

	Function GetTableFieldDefinition($table,$field,&$definition)
	{
		if(!$this->LoadManager("Get table field definition"))
			return(0);
		return($this->manager->GetTableFieldDefinition($this,$table,$field,$definition));
	}

	Function ListTableIndexes($table,&$indexes)
	{
		if(!$this->LoadManager("List table indexes"))
			return(0);
		return($this->manager->ListTableIndexes($this,$table,$indexes));
	}

	Function GetTableIndexDefinition($table,$index,&$definition)
	{
		if(!$this->LoadManager("Get table index definition"))
			return(0);
		return($this->manager->GetTableIndexDefinition($this,$table,$index,$definition));
	}

	Function ListSequences(&$sequences)
	{
		if(!$this->LoadManager("List sequences"))
			return(0);
		return($this->manager->ListSequences($this,$sequences));
	}

	Function GetSequenceDefinition($sequence,&$definition)
	{
		if(!$this->LoadManager("Get sequence definition"))
			return(0);
		return($this->manager->GetSequenceDefinition($this,$sequence,$definition));
	}

	Function CreateIndex($table,$name,&$definition)
	{
		if(!$this->LoadManager("Create index"))
			return(0);
		return($this->manager->CreateIndex($this,$table,$name,$definition));
	}

	Function DropIndex($table,$name)
	{
		if(!$this->LoadManager("Drop index"))
			return(0);
		return($this->manager->DropIndex($this,$table,$name));
	}

	Function CreateSequence($name,$start)
	{
		if(!$this->LoadManager("Create sequence"))
			return(0);
		return($this->manager->CreateSequence($this,$name,$start));
	}

	Function DropSequence($name)
	{
		if(!$this->LoadManager("Drop sequence"))
			return(0);
		return($this->manager->DropSequence($this,$name));
	}

	Function GetSequenceNextValue($name,&$value)
	{
		return($this->SetError("Get sequence next value","getting sequence next value is not supported"));
	}

	Function GetSequenceCurrentValue($name,&$value)
	{
		if(!$this->LoadManager("Get sequence current value"))
			return(0);
		return($this->manager->GetSequenceCurrentValue($this,$name,$value));
	}

	Function Query($query)
	{
		$this->Debug("Query: $query");
		return($this->SetError("Query","database queries are not implemented"));
	}

	Function Replace($table,&$fields)
	{
		if(!$this->supported["Replace"])
			return($this->SetError("Replace","replace query is not supported"));
		$count=count($fields);
		for($keys=0,$condition=$update=$insert=$values="",Reset($fields),$field=0;$field<$count;Next($fields),$field++)
		{
			$name=Key($fields);
			if($field>0)
			{
				$update.=",";
				$insert.=",";
				$values.=",";
			}
			$update.=$name;
			$insert.=$name;
			if(IsSet($fields[$name]["Null"])
			&& $fields[$name]["Null"])
				$value="NULL";
			else
			{
				if(!IsSet($fields[$name]["Value"]))
					return($this->SetError("Replace","it was not specified a value for the $name field"));
				switch(IsSet($fields[$name]["Type"]) ? $fields[$name]["Type"] : "text")
				{
					case "text":
						$value=$this->GetTextFieldValue($fields[$name]["Value"]);
						break;
					case "boolean":
						$value=$this->GetBooleanFieldValue($fields[$name]["Value"]);
						break;
					case "integer":
						$value=strval($fields[$name]["Value"]);
						break;
					case "decimal":
						$value=$this->GetDecimalFieldValue($fields[$name]["Value"]);
						break;
					case "float":
						$value=$this->GetFloatFieldValue($fields[$name]["Value"]);
						break;
					case "date":
						$value=$this->GetDateFieldValue($fields[$name]["Value"]);
						break;
					case "time":
						$value=$this->GetTimeFieldValue($fields[$name]["Value"]);
						break;
					case "timestamp":
						$value=$this->GetTimestampFieldValue($fields[$name]["Value"]);
						break;
					default:
						return($this->SetError("Replace","it was not specified a supported type for the $name field"));
				}
			}
			$update.="=".$value;
			$values.=$value;
			if(IsSet($fields[$name]["Key"])
			&& $fields[$name]["Key"])
			{
				if($value=="NULL")
					return($this->SetError("Replace","key values may not be NULL"));
				$condition.=($keys ? " AND " : " WHERE ").$name."=".$value;
				$keys++;
			}
		}
		if($keys==0)
			return($this->SetError("Replace","it were not specified which fields are keys"));
		if(!($in_transaction=$this->in_transaction)
		&& !$this->AutoCommitTransactions(0))
			return(0);
		if(($success=$this->QueryField("SELECT COUNT(*) FROM $table$condition",$affected_rows,"integer")))
		{
			switch($affected_rows)
			{
				case 0:
					$success=$this->Query("INSERT INTO $table ($insert) VALUES($values)");
					$affected_rows=1;
					break;
				case 1:
					$success=$this->Query("UPDATE $table SET $update$condition");
					$affected_rows=$this->affected_rows*2;
					break;
				default:
					$success=$this->SetError("Replace","replace keys are not unique");
					break;
			}
		}
		if(!$in_transaction)
		{
			if($success)
			{
				if(($success=($this->CommitTransaction() && $this->AutoCommitTransactions(1)))
				&& IsSet($this->supported["AffectedRows"]))
					$this->affected_rows=$affected_rows;
			}
			else
			{
				$this->RollbackTransaction();
				$this->AutoCommitTransactions(1);
			}
		}
		return($success);
	}

	Function PrepareQuery($query)
	{
		$this->Debug("PrepareQuery: $query");
		$positions=array();
		for($position=0;$position<strlen($query) && GetType($question=strpos($query,"?",$position))=="integer";)
		{
			if(GetType($quote=strpos($query,"'",$position))=="integer"
			&& $quote<$question)
			{
				if(GetType($end_quote=strpos($query,"'",$quote+1))!="integer")
					return($this->SetError("Prepare query","it was specified a query with an unterminated text string"));
				switch($this->escape_quotes)
				{
					case "":
					case "'":
						$position=$end_quote+1;
						break;
					default:
						if($end_quote==$quote+1)
							$position=$end_quote+1;
						else
						{
							if($query[$end_quote-1]==$this->escape_quotes)
								$position=$end_quote;
							else
								$position=$end_quote+1;
						}
						break;
				}
			}
			else
			{
				$positions[]=$question;
				$position=$question+1;
			}
		}
		$this->prepared_queries[]=array(
			"Query"=>$query,
			"Positions"=>$positions,
			"Values"=>array(),
			"Types"=>array()
		);
		$prepared_query=count($this->prepared_queries);
		if($this->selected_row_limit>0)
		{
			$this->prepared_queries[$prepared_query-1]["First"]=$this->first_selected_row;
			$this->prepared_queries[$prepared_query-1]["Limit"]=$this->selected_row_limit;
		}
		return($prepared_query);
	}

	Function ValidatePreparedQuery($prepared_query)
	{
		if($prepared_query<1
		|| $prepared_query>count($this->prepared_queries))
			return($this->SetError("Validate prepared query","invalid prepared query"));
		if(GetType($this->prepared_queries[$prepared_query-1])!="array")
			return($this->SetError("Validate prepared query","prepared query was already freed"));
		return(1);
	}

	Function FreePreparedQuery($prepared_query)
	{
		if(!$this->ValidatePreparedQuery($prepared_query))
			return(0);
		$this->prepared_queries[$prepared_query-1]="";
		return(1);
	}

	Function ExecutePreparedQuery($prepared_query,$query)
	{
		return($this->Query($query));
	}

	Function ExecuteQuery($prepared_query)
	{
		if(!$this->ValidatePreparedQuery($prepared_query))
			return(0);
		$index=$prepared_query-1;
		for($this->clobs[$prepared_query]=$this->blobs[$prepared_query]=array(),$success=1,$query="",$last_position=$position=0;$position<count($this->prepared_queries[$index]["Positions"]);$position++)
		{
			if(!IsSet($this->prepared_queries[$index]["Values"][$position]))
				return($this->SetError("Execute query","it was not defined query argument ".($position+1)));
			$current_position=$this->prepared_queries[$index]["Positions"][$position];
			$query.=substr($this->prepared_queries[$index]["Query"],$last_position,$current_position-$last_position);
			$value=$this->prepared_queries[$index]["Values"][$position];
			if($this->prepared_queries[$index]["IsNULL"][$position])
				$query.=$value;
			else
			{
				switch($this->prepared_queries[$index]["Types"][$position])
				{
					case "clob":
						if(!($success=$this->GetCLOBFieldValue($prepared_query,$position+1,$value,$this->clobs[$prepared_query][$position+1])))
						{
							Unset($this->clobs[$prepared_query][$position+1]);
							break;
						}
						$query.=$this->clobs[$prepared_query][$position+1];
						break;
					case "blob":
						if(!($success=$this->GetBLOBFieldValue($prepared_query,$position+1,$value,$this->blobs[$prepared_query][$position+1])))
						{
							Unset($this->blobs[$prepared_query][$position+1]);
							break;
						}
						$query.=$this->blobs[$prepared_query][$position+1];
						break;
					default:
						$query.=$value;
						break;
				}
			}
			$last_position=$current_position+1;
		}
		if($success)
		{
			$query.=substr($this->prepared_queries[$index]["Query"],$last_position);
			if($this->selected_row_limit>0)
			{
				$this->prepared_queries[$index]["First"]=$this->first_selected_row;
				$this->prepared_queries[$index]["Limit"]=$this->selected_row_limit;
			}
			if(IsSet($this->prepared_queries[$index]["Limit"])
			&& $this->prepared_queries[$index]["Limit"]>0)
			{
				$this->first_selected_row=$this->prepared_queries[$index]["First"];
				$this->selected_row_limit=$this->prepared_queries[$index]["Limit"];
			}
			else
				$this->first_selected_row=$this->selected_row_limit=0;
			$success=$this->ExecutePreparedQuery($prepared_query,$query);
		}
		for(Reset($this->clobs[$prepared_query]),$clob=0;$clob<count($this->clobs[$prepared_query]);$clob++,Next($this->clobs[$prepared_query]))
			$this->FreeCLOBValue($prepared_query,Key($this->clobs[$prepared_query]),$this->clobs[$prepared_query][Key($this->clobs[$prepared_query])],$success);
		UnSet($this->clobs[$prepared_query]);
		for(Reset($this->blobs[$prepared_query]),$blob=0;$blob<count($this->blobs[$prepared_query]);$blob++,Next($this->blobs[$prepared_query]))
			$this->FreeBLOBValue($prepared_query,Key($this->blobs[$prepared_query]),$this->blobs[$prepared_query][Key($this->blobs[$prepared_query])],$success);
		UnSet($this->blobs[$prepared_query]);
		return($success);
	}

	Function QuerySet($prepared_query,$parameter,$type,$value,$is_null=0,$field="")
	{
		if(!$this->ValidatePreparedQuery($prepared_query))
			return(0);
		$index=$prepared_query-1;
		if($parameter<1
		|| $parameter>count($this->prepared_queries[$index]["Positions"]))
			return($this->SetError("Query set","it was not specified a valid argument number"));
		$this->prepared_queries[$index]["Values"][$parameter-1]=$value;
		$this->prepared_queries[$index]["Types"][$parameter-1]=$type;
		$this->prepared_queries[$index]["Fields"][$parameter-1]=$field;
		$this->prepared_queries[$index]["IsNULL"][$parameter-1]=$is_null;
		return(1);
	}

	Function QuerySetNull($prepared_query,$parameter,$type)
	{
		return($this->QuerySet($prepared_query,$parameter,$type,"NULL",1,""));
	}

	Function QuerySetText($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"text",$this->GetTextFieldValue($value)));
	}

	Function QuerySetCLOB($prepared_query,$parameter,$value,$field)
	{
		return($this->QuerySet($prepared_query,$parameter,"clob",$value,0,$field));
	}

	Function QuerySetBLOB($prepared_query,$parameter,$value,$field)
	{
		return($this->QuerySet($prepared_query,$parameter,"blob",$value,0,$field));
	}

	Function QuerySetInteger($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"integer",$this->GetIntegerFieldValue($value)));
	}

	Function QuerySetBoolean($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"boolean",$this->GetBooleanFieldValue($value)));
	}

	Function QuerySetDate($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"date",$this->GetDateFieldValue($value)));
	}

	Function QuerySetTimestamp($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"timestamp",$this->GetTimestampFieldValue($value)));
	}

	Function QuerySetTime($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"time",$this->GetTimeFieldValue($value)));
	}

	Function QuerySetFloat($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"float",$this->GetFloatFieldValue($value)));
	}

	Function QuerySetDecimal($prepared_query,$parameter,$value)
	{
		return($this->QuerySet($prepared_query,$parameter,"decimal",$this->GetDecimalFieldValue($value)));
	}

	Function AffectedRows(&$affected_rows)
	{
		if($this->affected_rows==-1)
			return($this->SetError("Affected rows","there was no previous valid query to determine the number of affected rows"));
		$affected_rows=$this->affected_rows;
		return(1);
	}

	Function EndOfResult($result)
	{
		$this->SetError("End of result","end of result method not implemented");
		return(-1);
	}

	Function FetchResult($result,$row,$field)
	{
		$this->warning="fetch result method not implemented";
		return("");
	}

	Function FetchLOBResult($result,$row,$field)
	{
		$lob=count($this->lobs)+1;
		$this->lobs[$lob]=array(
			"Result"=>$result,
			"Row"=>$row,
			"Field"=>$field,
			"Position"=>0
		);
		$character_lob=array(
			"Database"=>$this->database,
			"Error"=>"",
			"Type"=>"resultlob",
			"ResultLOB"=>$lob
		);
		if(!MetabaseCreateLOB($character_lob,$clob))
			return($this->SetError("Fetch LOB result",$character_lob["Error"]));
		return($clob);
	}

	Function RetrieveLOB($lob)
	{
		if(!IsSet($this->lobs[$lob]))
			return($this->SetError("Fetch LOB result","it was not specified a valid lob"));
		if(!IsSet($this->lobs[$lob]["Value"]))
			$this->lobs[$lob]["Value"]=$this->FetchResult($this->lobs[$lob]["Result"],$this->lobs[$lob]["Row"],$this->lobs[$lob]["Field"]);
		return(1);
	}

	Function EndOfResultLOB($lob)
	{
		if(!$this->RetrieveLOB($lob))
			return(0);
		return($this->lobs[$lob]["Position"]>=strlen($this->lobs[$lob]["Value"]));
	}

	Function ReadResultLOB($lob,&$data,$length)
	{
		if(!$this->RetrieveLOB($lob))
			return(-1);
		$length=min($length,strlen($this->lobs[$lob]["Value"])-$this->lobs[$lob]["Position"]);
		$data=substr($this->lobs[$lob]["Value"],$this->lobs[$lob]["Position"],$length);
		$this->lobs[$lob]["Position"]+=$length;
		return($length);
	}

	Function DestroyResultLOB($lob)
	{
		if(IsSet($this->lobs[$lob]))
			$this->lobs[$lob]="";
	}

	Function FetchCLOBResult($result,$row,$field)
	{
		return($this->SetError("Fetch CLOB result","fetch clob result method is not implemented"));
	}

	Function FetchBLOBResult($result,$row,$field)
	{
		return($this->SetError("Fetch BLOB result","fetch blob result method is not implemented"));
	}

	Function ResultIsNull($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		return(!IsSet($value));
	}

	Function BaseConvertResult(&$value,$type)
	{
		switch($type)
		{
			case METABASE_TYPE_TEXT:
				return(1);
			case METABASE_TYPE_INTEGER:
				$value=intval($value);
				return(1);
			case METABASE_TYPE_BOOLEAN:
				$value=(strcmp($value,"Y") ? 0 : 1);
				return(1);
			case METABASE_TYPE_DECIMAL:
				return(1);
			case METABASE_TYPE_FLOAT:
				$value=doubleval($value);
				return(1);
			case METABASE_TYPE_DATE:
			case METABASE_TYPE_TIME:
			case METABASE_TYPE_TIMESTAMP:
				return(1);
			case METABASE_TYPE_CLOB:
			case METABASE_TYPE_BLOB:
				$value="";
				return($this->SetError("BaseConvertResult","attempt to convert result value to an unsupported type $type"));
			default:
				$value="";
				return($this->SetError("BaseConvertResult","attempt to convert result value to an unknown type $type"));
		}
	}

	Function ConvertResult(&$value,$type)
	{
		return($this->BaseConvertResult($value,$type));
	}

	Function ConvertResultRow($result,&$row)
	{
		if(IsSet($this->result_types[$result]))
		{
			if(($columns=$this->NumberOfColumns($result))==-1)
				return(0);
			for($column=0;$column<$columns;$column++)
			{
				if(!IsSet($row[$column]))
					continue;
				switch($type=$this->result_types[$result][$column])
				{
					case METABASE_TYPE_TEXT:
						break;
					case METABASE_TYPE_INTEGER:
						$row[$column]=intval($row[$column]);
						break;
					default:
						if(!$this->ConvertResult($row[$column],$type))
							return(0);
				}
			}
		}
		return(1);
	}

	Function FetchDateResult($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		$this->ConvertResult($value,METABASE_TYPE_DATE);
		return($value);
	}

	Function FetchTimestampResult($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		$this->ConvertResult($value,METABASE_TYPE_TIMESTAMP);
		return($value);
	}

	Function FetchTimeResult($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		$this->ConvertResult($value,METABASE_TYPE_TIME);
		return($value);
	}

	Function FetchBooleanResult($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		$this->ConvertResult($value,METABASE_TYPE_BOOLEAN);
		return($value);
	}

	Function FetchFloatResult($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		$this->ConvertResult($value,METABASE_TYPE_FLOAT);
		return($value);
	}

	Function FetchDecimalResult($result,$row,$field)
	{
		$value=$this->FetchResult($result,$row,$field);
		$this->ConvertResult($value,METABASE_TYPE_DECIMAL);
		return($value);
	}

	Function NumberOfRows($result)
	{
		$this->warning="number of rows method not implemented";
		return(0);
	}

	Function FreeResult($result)
	{
		$this->warning="free result method not implemented";
		return(0);
	}

	Function Error()
	{
		return($this->last_error);
	}

	Function SetErrorHandler($function)
	{
		$last_function=$this->error_handler;
		$this->error_handler=$function;
		return($last_function);
	}

	Function GetIntegerFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($field["unsigned"]))
			$this->warning="unsigned integer field \"$name\" is being declared as signed integer";
		return("$name INT".(IsSet($field["default"]) ? " DEFAULT ".$field["default"] : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTextFieldTypeDeclaration($name,&$field)
	{
		return((IsSet($field["length"]) ? "$name CHAR (".$field["length"].")" : "$name TEXT").(IsSet($field["default"]) ? " DEFAULT ".$this->GetTextFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetCLOBFieldTypeDeclaration($name,&$field)
	{
		return((IsSet($field["length"]) ? "$name CHAR (".$field["length"].")" : "$name TEXT").(IsSet($field["default"]) ? " DEFAULT ".$this->GetTextFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetBLOBFieldTypeDeclaration($name,&$field)
	{
		return((IsSet($field["length"]) ? "$name CHAR (".$field["length"].")" : "$name TEXT").(IsSet($field["default"]) ? " DEFAULT ".$this->GetTextFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetBooleanFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (1)".(IsSet($field["default"]) ? " DEFAULT ".$this->GetBooleanFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDateFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (".strlen("YYYY-MM-DD").")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetDateFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimestampFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (".strlen("YYYY-MM-DD HH:MM:SS").")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetTimestampFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimeFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (".strlen("HH:MM:SS").")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetTimeFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetFloatFieldTypeDeclaration($name,&$field)
	{
		return("$name TEXT ".(IsSet($field["default"]) ? " DEFAULT ".$this->GetFloatFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDecimalFieldTypeDeclaration($name,&$field)
	{
		return("$name TEXT ".(IsSet($field["default"]) ? " DEFAULT ".$this->GetDecimalFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetIntegerFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "$value");
	}

	Function GetTextFieldValue($value)
	{
		$this->EscapeText($value);
		return("'$value'");
	}
	
	Function GetCLOBFieldValue($prepared_query,$parameter,$clob,&$value)
	{
		return($this->SetError("Get CLOB field value","prepared queries with values of type \"clob\" are not yet supported"));
	}

	Function FreeCLOBValue($prepared_query,$clob,&$value,$success)
	{
	}

	Function GetBLOBFieldValue($prepared_query,$parameter,$blob,&$value)
	{
		return($this->SetError("Get BLOB field value","prepared queries with values of type \"blob\" are not yet supported"));
	}

	Function FreeBLOBValue($prepared_query,$blob,&$value,$success)
	{
	}

	Function GetBooleanFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : ($value ? "'Y'" : "'N'"));
	}

	Function GetDateFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "'$value'");
	}

	Function GetTimestampFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "'$value'");
	}

	Function GetTimeFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "'$value'");
	}

	Function GetFloatFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "'$value'");
	}

	Function GetDecimalFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "'$value'");
	}

	Function GetFieldValue($type,$value)
	{
		switch($type)
		{
			case "integer":
				return($this->GetIntegerFieldValue($value));
			case "text":
				return($this->GetTextFieldValue($value));
			case "boolean":
				return($this->GetBooleanFieldValue($value));
			case "date":
				return($this->GetDateFieldValue($value));
			case "timestamp":
				return($this->GetTimestampFieldValue($value));
			case "time":
				return($this->GetTimeFieldValue($value));
			case "float":
				return($this->GetFloatFieldValue($value));
			case "decimal":
				return($this->GetDecimalFieldValue($value));
		}
		return("");
	}

	Function Support($feature)
	{
		return(IsSet($this->supported[$feature]));
	}

	Function AutoCommitTransactions()
	{
		$this->Debug("AutoCommit: ".($auto_commit ? "On" : "Off"));
		return($this->SetError("Auto-commit transactions","transactions are not supported"));
	}

	Function CommitTransaction()
	{
 		$this->Debug("Commit Transaction");
		return($this->SetError("Commit transaction","commiting transactions are not supported"));
	}

	Function RollbackTransaction()
	{
 		$this->Debug("Rollback Transaction");
		return($this->SetError("Rollback transaction","rolling back transactions are not supported"));
	}

	Function Setup()
	{
		return("");
	}

	Function SetSelectedRowRange($first,$limit)
	{
		if(!IsSet($this->supported["SelectRowRanges"]))
			return($this->SetError("Set selected row range","selecting row ranges is not supported by this driver"));
		if(GetType($first)!="integer"
		|| $first<0)
			return($this->SetError("Set selected row range","it was not specified a valid first selected range row"));
		if(GetType($limit)!="integer"
		|| $limit<1)
			return($this->SetError("Set selected row range","it was not specified a valid selected range row limit"));
		$this->first_selected_row=$first;
		$this->selected_row_limit=$limit;
		return(1);
	}

	Function GetColumnNames($result,&$columns)
	{
		$columns=array();
		return($this->SetError("Get column names","obtaining result column names is not implemented"));
	}

	Function NumberOfColumns($result)
	{
		$this->SetError("Number of columns","obtaining the number of result columns is not implemented");
		return(-1);
	}

	Function SetResultTypes($result,&$types)
	{
		if(IsSet($this->result_types[$result]))
			return($this->SetError("Set result types","attempted to redefine the types of the columns of a result set"));
		if(($columns=$this->NumberOfColumns($result))==-1)
			return(0);
		if($columns<count($types))
			return($this->SetError("Set result types","it were specified more result types (".count($types).") than result columns ($columns)"));
		$valid_types=array(
			"text" =>      METABASE_TYPE_TEXT,
			"boolean" =>   METABASE_TYPE_BOOLEAN,
			"integer" =>   METABASE_TYPE_INTEGER,
			"decimal" =>   METABASE_TYPE_DECIMAL,
			"float" =>     METABASE_TYPE_FLOAT,
			"date" =>      METABASE_TYPE_DATE,
			"time" =>      METABASE_TYPE_TIME,
			"timestamp" => METABASE_TYPE_TIMESTAMP,
			"clob" =>      METABASE_TYPE_CLOB,
			"blob" =>      METABASE_TYPE_BLOB
		);
		for($column=0;$column<count($types);$column++)
		{
			if(!IsSet($valid_types[$types[$column]]))
				return($this->SetError("Set result types",$types[$column]." is not a supported column type"));
			$this->result_types[$result][$column]=$valid_types[$types[$column]];
		}
		for(;$column<$columns;$column++)
			$this->result_types[$result][$column]=METABASE_TYPE_TEXT;
		return(1);
	}

	Function FetchResultField($result,&$value)
	{
		if(!$result)
			return($this->SetError("Fetch field","it was not specified a valid result set"));
		if($this->EndOfResult($result))
			$success=$this->SetError("Fetch field","result set is empty");
		else
		{
			if($this->ResultIsNull($result,0,0))
				Unset($value);
			else
				$value=$this->FetchResult($result,0,0);
			$success=1;
		}
		if($success
		&& IsSet($this->result_types[$result]))
		{
			switch($type=$this->result_types[$result][0])
			{
				case METABASE_TYPE_TEXT:
					break;
				case METABASE_TYPE_INTEGER:
					$value=intval($value);
					break;
				default:
					$success=$this->ConvertResult($value,$type);
					break;
			}
		}
		$this->FreeResult($result);
		return($success);
	}

	Function BaseFetchResultArray($result,&$array,$row)
	{
		if(($columns=$this->NumberOfColumns($result))==-1)
			return(0);
		for($array=array(),$column=0;$column<$columns;$column++)
		{
			if(!$this->ResultIsNull($result,$row,$column))
				$array[$column]=$this->FetchResult($result,$row,$column);
		}
		return($this->ConvertResultRow($result,$array));
	}

	Function FetchResultArray($result,&$array,$row)
	{
		return($this->BaseFetchResultArray($result,$array,$row));
	}

	Function FetchResultRow($result,&$row)
	{
		if(!$result)
			return($this->SetError("Fetch field","it was not specified a valid result set"));
		if($this->EndOfResult($result))
			$success=$this->SetError("Fetch field","result set is empty");
		else
			$success=$this->FetchResultArray($result,$row,0);
		$this->FreeResult($result);
		return($success);
	}

	Function FetchResultColumn($result,&$column)
	{
		if(!$result)
			return($this->SetError("Fetch field","it was not specified a valid result set"));
		for($success=1,$column=array(),$row=0;!$this->EndOfResult($result);$row++)
		{
			if($this->ResultIsNull($result,0,0))
				continue;
			$column[$row]=$this->FetchResult($result,$row,0);
			if(IsSet($this->result_types[$result]))
			{
				switch($type=$this->result_types[$result][0])
				{
					case METABASE_TYPE_TEXT:
						break;
					case METABASE_TYPE_INTEGER:
						$column[$row]=intval($column[$row]);
						break;
					default:
						if(!($success=$this->ConvertResult($column[$row],$type)))
							break 2;
						break;
				}
			}
		}
		$this->FreeResult($result);
		return($success);
	}

	Function FetchResultAll($result,&$all)
	{
		if(!$result)
			return($this->SetError("Fetch field","it was not specified a valid result set"));
		for($success=1,$all=array(),$row=0;!$this->EndOfResult($result);$row++)
		{
			if(!($success=$this->FetchResultArray($result,$all[$row],$row)))
				break;
		}
		$this->FreeResult($result);
		return($success);
	}

	Function QueryField($query,&$field,$type="text")
	{
		if(!($result=$this->Query($query)))
			return(0);
		if(strcmp($type,"text"))
		{
			$types=array($type);
			if(!($success=$this->SetResultTypes($result,$types)))
			{
				$this->FreeResult($result);
				return(0);
			}
		}
		return($this->FetchResultField($result,$field));
	}

	Function QueryRow($query,&$row,$types="")
	{
		if(!($result=$this->Query($query)))
			return(0);
		if(GetType($types)=="array")
		{
			if(!($success=$this->SetResultTypes($result,$types)))
			{
				$this->FreeResult($result);
				return(0);
			}
		}
		return($this->FetchResultRow($result,$row));
	}

	Function QueryColumn($query,&$column,$type="text")
	{
		if(!($result=$this->Query($query)))
			return(0);
		if(strcmp($type,"text"))
		{
			$types=array($type);
			if(!($success=$this->SetResultTypes($result,$types)))
			{
				$this->FreeResult($result);
				return(0);
			}
		}
		return($this->FetchResultColumn($result,$column));
	}

	Function QueryAll($query,&$all,$types="")
	{
		if(!($result=$this->Query($query)))
			return(0);
		if(GetType($types)=="array")
		{
			if(!($success=$this->SetResultTypes($result,$types)))
			{
				$this->FreeResult($result);
				return(0);
			}
		}
		return($this->FetchResultAll($result,$all));
	}

};

?>