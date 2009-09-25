<?php
if(!defined("METABASE_MSSQL_INCLUDED"))
{
	define("METABASE_MSSQL_INCLUDED",1);

/*
 * metabase_mssql.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_mssql.php,v 1.22 2004/07/27 06:26:03 mlemos Exp $
 *
 */
 
class metabase_mssql_class extends metabase_database_class
{
	var $connection=0;
	var $connected_host;
	var $connected_user;
	var $connected_password;
	var $opened_persistent="";

	var $current_row=-1;
	var $fetched_row=array();
	var $columns=array();
	var $highest_fetched_row=array();
	var $ranges=array();
	var $escape_quotes="'";
	var $manager_included_constant="METABASE_MANAGER_MSSQL_INCLUDED";
	var $manager_include="manager_mssql.php";
	var $manager_class_name="metabase_manager_mssql_class";
	var $select_queries=array(
		"select"=>"",
		"exec"=>"",
		"execute"=>""
	);

	Function SetMSSQLError($scope,$error)
	{
		if(($last_error=mssql_get_last_message())!="")
			$error.=": ".$last_error;
		return($this->SetError($scope,$error));
	}

	Function DoQuery($query)
	{
		$this->current_row=$this->affected_rows=-1;
		return(@mssql_query($query,$this->connection));
	}

	Function Close()
	{
		if($this->connection!=0)
		{
			if(!$this->auto_commit)
				$this->DoQuery("ROLLBACK TRANSACTION");
			mssql_close($this->connection);
			$this->connection=0;
			$this->affected_rows=$this->current_row=-1;
		}
	}

	Function Connect()
	{
		if($this->connection!=0)
		{
			if(!strcmp($this->connected_host,$this->host)
			&& !strcmp($this->connected_user,$this->user)
			&& !strcmp($this->connected_password,$this->password)
			&& $this->opened_persistent==$this->persistent)
				return(1);
			$this->Close();
		}
		$function=($this->persistent ? "mssql_pconnect" : "mssql_connect");
		if(!function_exists($function))
			return($this->SetError("Connect","Microsoft SQL server support is not available in this PHP configuration"));
		if(($this->connection=@$function($this->host,$this->user,$this->password))<=0)
			return($this->SetMSSQLError("Connect","Could not connect to the Microsoft SQL server"));
		if(strcmp($this->database_name,"")
		&& !@mssql_select_db($this->database_name,$this->connection))
		{
			$this->SetMSSQLError("Connect","Could not select a Microsoft SQL server database");
			mssql_close($this->connection);
			$this->connection=0;
			return(0);
		}
		$this->selected_database=$this->database_name;
		if(!$this->auto_commit
		&& !$this->DoQuery("BEGIN TRANSACTION"))
		{
			$this->SetMSSQLError("Connect","Could not begin the initial transaction");
			mssql_close($this->connection);
			$this->connection=0;
			return(0);
		}
		$this->connected_host=$this->host;
		$this->connected_user=$this->user;
		$this->connected_password=$this->password;
		$this->opened_persistent=$this->persistent;
		return(1);
	}

	Function SelectDatabase()
	{
		if(!strcmp($this->database_name,""))
			return($this->SetError("Select database","It was not specified a valid database name to select"));
		$last_connection=$this->connection;
		if(!$this->Connect())
			return(0);
		if($last_connection==$this->connection
		&& strcmp($this->selected_database,"")
		&& !strcmp($this->selected_database,$this->database_name))
			return(1);
		if(!mssql_select_db($this->database_name,$this->connection))
			return($this->SetMSSQLError("Select database","Could not select a Microsoft SQL server database"));
		$this->selected_database=$this->database_name;
		return(1);
	}

	Function Query($query)
	{
		$this->Debug("Query: $query");
		$first=$this->first_selected_row;
		$limit=$this->selected_row_limit;
		$this->first_selected_row=$this->selected_row_limit=0;
		if(!$this->SelectDatabase())
			return(0);
		$query_string=strtolower(strtok(ltrim($query)," \t\n\r"));
		if(($select=IsSet($this->select_queries[$query_string]))
		&& $limit>0)
		{
			$result=0;
			if($this->DoQuery("DECLARE select_cursor SCROLL CURSOR FOR $query FOR READ ONLY")
			&& $this->DoQuery("OPEN select_cursor")
			&& ($result=$this->DoQuery("FETCH ABSOLUTE ".($first+1)." FROM select_cursor")))
			{
				$this->ranges[$result][0]=mssql_fetch_row($result);
				for($row=1;$row<$limit;$row++)
				{
					if(!($row_result=$this->DoQuery("FETCH FROM select_cursor")))
					{
						Unset($this->ranges[$result]);
						mssql_free_result($result);
						$result=0;
						break;
					}
					if(mssql_num_rows($row_result)==0)
						break;
					$this->ranges[$result][$row]=mssql_fetch_row($row_result);
					mssql_free_result($row_result);
				}
				if($result
				&& !$this->DoQuery("DEALLOCATE select_cursor"))
				{
					Unset($this->ranges[$result]);
					mssql_free_result($result);
					$result=0;
				}
			}
		}
		else
			$result=$this->DoQuery($query);
		if($result)
		{
			if($select)
				$this->highest_fetched_row[$result]=-1;
		}
		else
			$this->SetMSSQLError("Query","Could not query the Microsoft SQL server");
		return($result);
	}

	Function AffectedRows(&$affected_rows)
	{
		if(!$this->connection)
			return($this->SetError("Affected rows","it was not established a connection with the Microsoft SQL server"));
		if($this->affected_rows==-1)
		{
			if(($result=mssql_query("SELECT @@ROWCOUNT",$this->connection)))
			{
				if(mssql_num_rows($result)!=1)
					return($this->SetError("Affected rows","Microsoft SQL server did not return one row with the number of affected rows"));
				$this->affected_rows=intval(mssql_result($result,0,0));
				mssql_free_result($result);
			}
			else
				return($this->SetMSSQLError("Affected rows","Could not retrieve the number of affected rows of a Microsoft SQL server database"));
		}
		$affected_rows=$this->affected_rows;
		return(1);
	}

	Function FetchRow($result,$row)
	{
		if($this->current_row!=$row)
		{
			if(IsSet($this->ranges[$result]))
			{
				if($row>count($this->ranges[$result]))
					return($this->SetError("Fetch row","attempted to retrieve a row outside the result set range"));
				$this->fetched_row[$result]=$this->ranges[$result][$row];
			}
			else
			{
				if(!mssql_data_seek($result,$row))
					return($this->SetError("Fetch row","could not move the result row position"));
				if(GetType($this->fetched_row[$result]=mssql_fetch_row($result))!="array")
				{
					$this->current_row=-1;
					return($this->SetError("Fetch row","could not fetch the result row"));
				}
			}
			$this->current_row=$row;
			$this->highest_fetched_row[$result]=max($this->highest_fetched_row[$result],$row);
		}
		return(1);
	}

	Function GetColumnNames($result,&$column_names)
	{
		$result_value=intval($result);
		if(!IsSet($this->highest_fetched_row[$result_value]))
			return($this->SetError("Get column names","it was specified an inexisting result set"));
		if(!IsSet($this->columns[$result_value]))
		{
			$this->columns[$result_value]=array();
			for($column=0;@mssql_field_seek($result,$column);$column++)
			{
				$field=mssql_fetch_field($result);
				$this->columns[$result_value][strtolower($field->name)]=$column;
			}
		}
		$column_names=$this->columns[$result_value];
		return(1);
	}

	Function NumberOfColumns($result)
	{
		if(!IsSet($this->highest_fetched_row[intval($result)]))
		{
			$this->SetError("Number of columns","it was specified an inexisting result set");
			return(-1);
		}
		return(mssql_num_fields($result));
	}

	Function GetColumn($result,$field)
	{
		if(!$this->GetColumnNames($result,$column_names))
			return(-1);
		if(GetType($field)=="integer")
		{
			if(($column=$field)<0
			|| $column>=count($this->columns[$result]))
			{
				$this->SetError("Get column","attempted to fetch an query result column out of range");
				return(-1);
			}
		}
		else
		{
			$name=strtolower($field);
			if(!IsSet($this->columns[$result][$name]))
			{
				$this->SetError("Get column","attempted to fetch an unknown query result column");
				return(-1);
			}
			$column=$this->columns[$result][$name];
		}
		return($column);
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
		if(($column=$this->GetColumn($result,$field))==-1
		|| !$this->FetchRow($result,$row))
			return("");
		if(!IsSet($this->fetched_row[$result][$column]))
		{
			$this->SetError("Fetch result","attempted to fetch a NULL result value");
			return("");
		}
		return($this->fetched_row[$result][$column]);
	}

	Function FetchResultArray($result,&$array,$row)
	{
		if(!$this->FetchRow($result,$row))
			return(0);
		$array=$this->fetched_row[$result];
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

	Function ResultIsNull($result,$row,$field)
	{
		if(($column=$this->GetColumn($result,$field))==-1
		|| !$this->FetchRow($result,$row))
			return("");
		return(!IsSet($this->fetched_row[$result][$column]));
	}

	Function ConvertResult(&$value,$type)
	{
		switch($type)
		{
			case METABASE_TYPE_BOOLEAN:
				$value=(strcmp($value,"1") ? 0 : 1);
				return(1);
			case METABASE_TYPE_DECIMAL:
				return(1);
			case METABASE_TYPE_FLOAT:
				$value=doubleval($value);
				return(1);
			case METABASE_TYPE_DATE:
				if(strlen($value)>10)
					$value=substr($value,0,10);
				return(1);
			case METABASE_TYPE_TIME:
				if(strlen($value)>8)
					$value=substr($value,11,8);
				return(1);
			case METABASE_TYPE_TIMESTAMP:
				return(1);
			default:
				return($this->BaseConvertResult($value,$type));
		}
	}

	Function NumberOfRows($result)
	{
		return(IsSet($this->ranges[$result]) ? count($this->ranges[$result]) : mssql_num_rows($result));
	}

	Function FreeResult($result)
	{
		UnSet($this->fetched_row[$result]);
		UnSet($this->highest_fetched_row[$result]);
		UnSet($this->columns[$result]);
		UnSet($this->ranges[$result]);
		UnSet($this->result_types[$result]);
		return(mssql_free_result($result));
	}

	Function GetIntegerFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($field["unsigned"]))
			$this->warning="unsigned integer field \"$name\" is being declared as signed integer";
		return("$name INT".(IsSet($field["default"]) ? " DEFAULT ".$field["default"] : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetTextFieldTypeDeclaration($name,&$field)
	{
		return((IsSet($field["length"]) ? "$name VARCHAR (".$field["length"].")" : "$name TEXT").(IsSet($field["default"]) ? " DEFAULT ".$this->GetTextFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetCLOBFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($field["length"]))
		{
			$length=$field["length"];
			if($length<=8000)
				$type="VARCHAR($length)";
			else
				$type="TEXT";
		}
		else
			$type="TEXT";
		return("$name $type".(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetBLOBFieldTypeDeclaration($name,&$field)
	{
		if(IsSet($field["length"]))
		{
			$length=$field["length"];
			if($length<=8000)
				$type="VARBINARY($length)";
			else
				$type="IMAGE";
		}
		else
			$type="IMAGE";
		return("$name $type".(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetDateFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (".strlen("YYYY-MM-DD").")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetDateFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetTimestampFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (".strlen("YYYY-MM-DD HH:MM:SS").")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetTimestampFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetTimeFieldTypeDeclaration($name,&$field)
	{
		return("$name CHAR (".strlen("HH:MM:SS").")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetTimeFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetBooleanFieldTypeDeclaration($name,&$field)
	{
		return("$name BIT".(IsSet($field["default"]) ? " DEFAULT ".$field["default"] : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetFloatFieldTypeDeclaration($name,&$field)
	{
		return("$name FLOAT".(IsSet($field["default"]) ? " DEFAULT ".$field["default"] : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
	}

	Function GetDecimalFieldTypeDeclaration($name,&$field)
	{
		return("$name DECIMAL(18,".$this->decimal_places.")".(IsSet($field["default"]) ? " DEFAULT ".$this->GetDecimalFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : " NULL"));
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
		for($value="0x";!MetabaseEndOfLOB($blob);)
		{
			if(!MetabaseReadLOB($blob,$data,$this->lob_buffer_length))
			{
				$value="";
				return($this->SetError("Get BLOB field value",MetabaseLOBError($blob)));
			}
			$value.=Bin2Hex($data);
		}
		return(1);			
	}

	Function FreeBLOBValue($prepared_query,$blob,&$value,$success)
	{
		Unset($value);
	}

	Function GetBooleanFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "$value");
	}

	Function GetFloatFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "$value");
	}

	Function GetDecimalFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "$value");
	}

	Function GetSequenceNextValue($name,&$value)
	{
		if(!$this->Query("INSERT INTO _sequence_$name DEFAULT VALUES")
		|| !($result=$this->Query("SELECT @@IDENTITY FROM _sequence_$name")))
			return(0);
		$value=intval($this->FetchResult($result,0,0));
		$this->FreeResult($result);
		if(!$this->Query("DELETE FROM _sequence_$name WHERE sequence<$value"))
			$this->warning="could delete previous sequence table values";
		return(1);
	}

	Function AutoCommitTransactions($auto_commit)
	{
		$this->Debug("AutoCommit: ".($auto_commit ? "On" : "Off"));
		if(((!$this->auto_commit)==(!$auto_commit)))
			return(1);
		if($this->connection)
		{
			if(!$this->Query($auto_commit ? "COMMIT TRANSACTION" : "BEGIN TRANSACTION"))
				return(0);
		}
		$this->auto_commit=$auto_commit;
		return($this->RegisterTransactionShutdown($auto_commit));
	}

	Function CommitTransaction()
	{
 		$this->Debug("Commit Transaction");
		if($this->auto_commit)
			return($this->SetError("Commit transaction","transaction changes are being auto commited"));
		return($this->Query("COMMIT TRANSACTION") && $this->Query("BEGIN TRANSACTION"));
	}

	Function RollbackTransaction()
	{
 		$this->Debug("Rollback Transaction");
		if($this->auto_commit)
			return($this->SetError("Rollback transaction","transactions can not be rolled back when changes are auto commited"));
		return($this->Query("ROLLBACK TRANSACTION") && $this->Query("BEGIN TRANSACTION"));
	}

	Function Setup()
	{
		$this->supported["AffectedRows"]=
		$this->supported["Indexes"]=
		$this->supported["OrderByText"]=
		$this->supported["Sequences"]=
		$this->supported["SummaryFunctions"]=
		$this->supported["Transactions"]=
		$this->supported["SelectRowRanges"]=
		$this->supported["LOBs"]=
		$this->supported["Replace"]=
			1;
		return("");
	}

};

}
?>