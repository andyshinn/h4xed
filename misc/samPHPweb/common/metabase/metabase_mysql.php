<?php
if(!defined("METABASE_MYSQL_INCLUDED"))
{
	define("METABASE_MYSQL_INCLUDED",1);

/*
 * metabase_mysql.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_mysql.php,v 1.72 2004/07/27 06:26:03 mlemos Exp $
 *
 */
 
class metabase_mysql_class extends metabase_database_class
{
	var $connection=0;
	var $connected_host;
	var $connected_user;
	var $connected_password;
	var $connected_port;
	var $opened_persistent="";
	var $decimal_factor=1.0;
	var $highest_fetched_row=array();
	var $columns=array();
	var $fixed_float=0;
	var $escape_quotes="\\";
	var $sequence_prefix="_sequence_";
	var $dummy_primary_key="dummy_primary_key";
	var $manager_class_name="metabase_manager_mysql_class";
	var $manager_include="manager_mysql.php";
	var $manager_included_constant="METABASE_MANAGER_MYSQL_INCLUDED";
	var $default_table_type="";
	var $select_queries=array(
		"select"=>"",
		"show"=>"",
		"check"=>"",
		"repair"=>"",
		"analyze"=>"",
		"optimize"=>"",
		"explain"=>""
	);

	Function Connect()
	{
		$port=(IsSet($this->options["Port"]) ? $this->options["Port"] : "");
		if($this->connection!=0)
		{
			if(!strcmp($this->connected_host,$this->host)
			&& !strcmp($this->connected_user,$this->user)
			&& !strcmp($this->connected_password,$this->password)
			&& !strcmp($this->connected_port,$port)
			&& $this->opened_persistent==$this->persistent)
				return(1);
			mysql_Close($this->connection);
			$this->connection=0;
			$this->affected_rows=-1;
		}
		$this->fixed_float=30;
		$function=($this->persistent ? "mysql_pconnect" : "mysql_connect");
		if(!function_exists($function))
			return($this->SetError("Connect","MySQL support is not available in this PHP configuration"));
		if(($this->connection=@$function($this->host.(!strcmp($port,"") ? "" : ":".$port),$this->user,$this->password))<=0)
			return($this->SetError("Connect",IsSet($php_errormsg) ? $php_errormsg : "Could not connect to MySQL server"));
		if(IsSet($this->options["FixedFloat"]))
			$this->fixed_float=$this->options["FixedFloat"];
		else
		{
			if(($result=mysql_query("SELECT VERSION()",$this->connection)))
			{
				$version=explode(".",mysql_result($result,0,0));
				$major=intval($version[0]);
				$minor=intval($version[1]);
				$revision=intval($version[2]);
				if($major>3
				|| ($major==3
				&& $minor>=23
				&& ($minor>23
				|| $revision>=6)))
					$this->fixed_float=0;
				mysql_free_result($result);
			}
		}
		if(IsSet($this->supported["Transactions"])
		&& !$this->auto_commit)
		{
			if(!mysql_query("SET AUTOCOMMIT=0",$this->connection))
			{
				mysql_Close($this->connection);
				$this->connection=0;
				$this->affected_rows=-1;
				return(0);
			}
			$this->RegisterTransactionShutdown(0);
		}
		$this->connected_host=$this->host;
		$this->connected_user=$this->user;
		$this->connected_password=$this->password;
		$this->connected_port=$port;
		$this->opened_persistent=$this->persistent;
		return(1);
	}

	Function Close()
	{
		if($this->connection!=0)
		{
			if(IsSet($this->supported["Transactions"])
			&& !$this->auto_commit)
				$this->AutoCommitTransactions(1);
			mysql_Close($this->connection);
			$this->connection=0;
			$this->affected_rows=-1;
		}
	}

	Function Query($query)
	{
		$this->Debug("Query: $query");
		$first=$this->first_selected_row;
		$limit=$this->selected_row_limit;
		$this->first_selected_row=$this->selected_row_limit=0;
		if(!strcmp($this->database_name,""))
			return($this->SetError("Query","it was not specified a valid database name to select"));
		if(!$this->Connect())
			return(0);
		$query_string=strtolower(strtok(ltrim($query)," \t\n\r"));
		if(($select=IsSet($this->select_queries[$query_string]))
		&& $limit>0)
			$query.=" LIMIT $first,$limit";
		if(mysql_select_db($this->database_name,$this->connection)
		&& ($result=mysql_query($query,$this->connection)))
		{
			if($select)
				$this->highest_fetched_row[$result]=-1;
			else
				$this->affected_rows=mysql_affected_rows($this->connection);
		}
		else
			return($this->SetError("Query",mysql_error($this->connection)));
		return($result);
	}

	Function Replace($table,&$fields)
	{
		$count=count($fields);
		for($keys=0,$query=$values="",Reset($fields),$field=0;$field<$count;Next($fields),$field++)
		{
			$name=Key($fields);
			if($field>0)
			{
				$query.=",";
				$values.=",";
			}
			$query.=$name;
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
			$values.=$value;
			if(IsSet($fields[$name]["Key"])
			&& $fields[$name]["Key"])
			{
				if($value=="NULL")
					return($this->SetError("Replace","key values may not be NULL"));
				$keys++;
			}
		}
		if($keys==0)
			return($this->SetError("Replace","it were not specified which fields are keys"));
		return($this->Query("REPLACE INTO $table ($query) VALUES($values)"));
	}

	Function EndOfResult($result)
	{
		if(!IsSet($this->highest_fetched_row[$result]))
		{
			$this->SetError("End of result","attempted to check the end of an unknown result");
			return(-1);
		}
		return($this->highest_fetched_row[$result]>=$this->NumberOfRows($result)-1);
	}

	Function FetchResult($result,$row,$field)
	{
		$this->highest_fetched_row[$result]=max($this->highest_fetched_row[$result],$row);
		return(mysql_result($result,$row,$field));
	}

	Function FetchResultArray($result,&$array,$row)
	{
		if(!mysql_data_seek($result,$row)
		|| !($array=mysql_fetch_row($result)))
			return($this->SetError("Fetch result array",mysql_error($this->connection)));
		$this->highest_fetched_row[$result]=max($this->highest_fetched_row[$result],$row);
		return($this->ConvertResultRow($result,$array));
	}

	Function FetchCLOBResult($result,$row,$field)
	{
		return($this->FetchLOBResult($result,$row,$field));
	}

	Function FetchBLOBResult($result,$row,$field)
	{
		return($this->FetchLOBResult($result,$row,$field));
	}

	Function ConvertResult(&$value,$type)
	{
		switch($type)
		{
			case METABASE_TYPE_BOOLEAN:
				$value=(strcmp($value,"Y") ? 0 : 1);
				return(1);
			case METABASE_TYPE_DECIMAL:
				$value=sprintf("%.".$this->decimal_places."f",doubleval($value)/$this->decimal_factor);
				return(1);
			case METABASE_TYPE_FLOAT:
				$value=doubleval($value);
				return(1);
			case METABASE_TYPE_DATE:
			case METABASE_TYPE_TIME:
			case METABASE_TYPE_TIMESTAMP:
				return(1);
			default:
				return($this->BaseConvertResult($value,$type));
		}
	}

	Function NumberOfRows($result)
	{
		return(mysql_num_rows($result));
	}

	Function FreeResult($result)
	{
		UnSet($this->highest_fetched_row[$result]);
		UnSet($this->columns[$result]);
		UnSet($this->result_types[$result]);
		return(mysql_free_result($result));
	}

	Function GetCLOBFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($field["length"]))
		{
			$length=$field["length"];
			if($length<=255)
				$type="TINYTEXT";
			else
			{
				if($length<=65535)
					$type="TEXT";
				else
				{
					if($length<=16777215)
						$type="MEDIUMTEXT";
					else
						$type="LONGTEXT";
				}
			}
		}
		else
			$type="LONGTEXT";
		return("$name $type".(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetBLOBFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($field["length"]))
		{
			$length=$field["length"];
			if($length<=255)
				$type="TINYBLOB";
			else
			{
				if($length<=65535)
					$type="BLOB";
				else
				{
					if($length<=16777215)
						$type="MEDIUMBLOB";
					else
						$type="LONGBLOB";
				}
			}
		}
		else
			$type="LONGBLOB";
		return("$name $type".(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetIntegerFieldTypeDeclaration($name,&$field)
	{
		return("$name ".(IsSet($field["unsigned"]) ? "INT UNSIGNED" : "INT").(IsSet($field["default"]) ? " DEFAULT ".$field["default"] : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDateFieldTypeDeclaration($name,&$field)
	{
		return($name." DATE".(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimestampFieldTypeDeclaration($name,&$field)
	{
		return($name." DATETIME".(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimeFieldTypeDeclaration($name,&$field)
	{
		return($name." TIME".(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetFloatFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($this->options["FixedFloat"]))
			$this->fixed_float=$this->options["FixedFloat"];
		else
		{
			if($this->connection==0)
				$this->Connect();
		}
		return("$name DOUBLE".($this->fixed_float ? "(".($this->fixed_float+2).",".$this->fixed_float.")" : "").(IsSet($field["default"]) ? " DEFAULT ".$this->GetFloatFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDecimalFieldTypeDeclaration($name,&$field)
	{
		return("$name BIGINT".(IsSet($field["default"]) ? " DEFAULT ".$this->GetDecimalFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetCLOBFieldValue($prepared_query,$parameter,$clob,&$value)
	{
		for($value="'";!MetabaseEndOfLOB($clob);)
		{
			if(MetabaseReadLOB($clob,$data,$this->lob_buffer_length)<0)
			{
				$value="";
				return($this->SetError("Get CLOB field value",MetabaseLOBError($clob)));
			}
			$this->EscapeText($data);
			$value.=$data;
		}
		$value.="'";
		return(1);			
	}

	Function FreeCLOBValue($prepared_query,$clob,&$value,$success)
	{
		Unset($value);
	}

	Function GetBLOBFieldValue($prepared_query,$parameter,$blob,&$value)
	{
		for($value="'";!MetabaseEndOfLOB($blob);)
		{
			if(!MetabaseReadLOB($blob,$data,$this->lob_buffer_length))
			{
				$value="";
				return($this->SetError("Get BLOB field value",MetabaseLOBError($blob)));
			}
			$value.=AddSlashes($data);
		}
		$value.="'";
		return(1);			
	}

	Function FreeBLOBValue($prepared_query,$blob,&$value,$success)
	{
		Unset($value);
	}

	Function GetFloatFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "$value");
	}

	Function GetDecimalFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : strval(round(doubleval($value)*$this->decimal_factor)));
	}

	Function GetColumnNames($result,&$column_names)
	{
		$result_value=intval($result);
		if(!IsSet($this->highest_fetched_row[$result_value]))
			return($this->SetError("Get column names","it was specified an inexisting result set"));
		if(!IsSet($this->columns[$result_value]))
		{
			$this->columns[$result_value]=array();
			$columns=mysql_num_fields($result);
			for($column=0;$column<$columns;$column++)
				$this->columns[$result_value][strtolower(mysql_field_name($result,$column))]=$column;
		}
		$column_names=$this->columns[$result_value];
		return(1);
	}

	Function NumberOfColumns($result)
	{
		if(!IsSet($this->highest_fetched_row[intval($result)]))
		{
			$this->SetError("Get column names","it was specified an inexisting result set");
			return(-1);
		}
		return(mysql_num_fields($result));
	}

	Function GetSequenceNextValue($name,&$value)
	{
		$sequence_name=$this->sequence_prefix.$name;
		if(!$this->Query("INSERT INTO $sequence_name (sequence) VALUES (NULL)"))
			return(0);
		$value=intval(mysql_insert_id($this->connection));
		if(!$this->Query("DELETE FROM $sequence_name WHERE sequence<$value"))
			$this->warning="could delete previous sequence table values";
		return(1);
	}

	Function AutoCommitTransactions($auto_commit)
	{
		$this->Debug("AutoCommit: ".($auto_commit ? "On" : "Off"));
		if(!IsSet($this->supported["Transactions"]))
			return($this->SetError("Auto-commit transactions","transactions are not in use"));
		if(((!$this->auto_commit)==(!$auto_commit)))
			return(1);
		if($this->connection)
		{
			if($auto_commit)
			{
				if(!$this->Query("COMMIT")
				|| !$this->Query("SET AUTOCOMMIT=1"))
					return(0);
			}
			else
			{
				if(!$this->Query("SET AUTOCOMMIT=0"))
					return(0);
			}
		}
		$this->auto_commit=$auto_commit;
		return($this->RegisterTransactionShutdown($auto_commit));
	}

	Function CommitTransaction()
	{
 		$this->Debug("Commit Transaction");
		if(!IsSet($this->supported["Transactions"]))
			return($this->SetError("Commit transaction","transactions are not in use"));
		if($this->auto_commit)
			return($this->SetError("Commit transaction","transaction changes are being auto commited"));
		return($this->Query("COMMIT"));
	}

	Function RollbackTransaction()
	{
 		$this->Debug("Rollback Transaction");
		if(!IsSet($this->supported["Transactions"]))
			return($this->SetError("Rollback transaction","transactions are not in use"));
		if($this->auto_commit)
			return($this->SetError("Rollback transaction","transactions can not be rolled back when changes are auto commited"));
		return($this->Query("ROLLBACK"));
	}

	Function Setup()
	{
		$this->supported["Sequences"]=
		$this->supported["Indexes"]=
		$this->supported["AffectedRows"]=
		$this->supported["SummaryFunctions"]=
		$this->supported["OrderByText"]=
		$this->supported["GetSequenceCurrentValue"]=
		$this->supported["SelectRowRanges"]=
		$this->supported["LOBs"]=
		$this->supported["Replace"]=
			1;
		if(IsSet($this->options["UseTransactions"])
		&& $this->options["UseTransactions"])
		{
			$this->supported["Transactions"]=1;
			$this->default_table_type="BDB";
		}
		else
			$this->default_table_type="";
		if(IsSet($this->options["DefaultTableType"]))
		{
			switch($this->default_table_type=strtoupper($this->options["DefaultTableType"]))
			{
				case "BERKELEYDB":
					$this->default_table_type="BDB";
				case "BDB":
				case "INNODB":
				case "GEMINI":
					break;
				case "HEAP":
				case "ISAM":
				case "MERGE":
				case "MRG_MYISAM":
				case "MYISAM":
					if(IsSet($this->supported["Transactions"]))
						return($this->options["DefaultTableType"]." is not a transaction-safe default table type");
					break;
				default:
					return($this->options["DefaultTableType"]." is not a supported default table type");
			}
		}
		$this->decimal_factor=pow(10.0,$this->decimal_places);
		return("");
	}
};

}
?>