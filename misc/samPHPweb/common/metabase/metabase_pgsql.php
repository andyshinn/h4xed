<?php
if(!defined("METABASE_PGSQL_INCLUDED"))
{
	define("METABASE_PGSQL_INCLUDED",1);

/*
 * metabase_pgsql.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_pgsql.php,v 1.58 2004/07/27 06:26:03 mlemos Exp $
 *
 */

class metabase_pgsql_class extends metabase_database_class
{
	var $connection=0;
	var $connected_host;
	var $connected_port;
	var $selected_database="";
	var $opened_persistent="";
	var $transaction_started=0;
	var $decimal_factor=1.0;
	var $highest_fetched_row=array();
	var $columns=array();
	var $escape_quotes="\\";
	var $manager_included_constant="METABASE_MANAGER_PGSQL_INCLUDED";
	var $manager_include="manager_pgsql.php";
	var $manager_class_name="metabase_manager_pgsql_class";

	Function DoConnect($database_name,$persistent)
	{
		$function=($persistent ? "pg_pconnect" : "pg_connect");
		if(!function_exists($function))
			return($this->SetError("Do Connect","PostgreSQL support is not available in this PHP configuration"));
		if(strlen($database_name)==0)
			return($this->SetError("Do Connect","it was not specified a PostgreSQL database to connect"));
		Putenv("PGUSER=".$this->user);
		Putenv("PGPASSWORD=".$this->password);
		Putenv("PGDATESTYLE=ISO");
		$port=(IsSet($this->options["Port"]) ? $this->options["Port"] : "");
		if(($connection=@$function($this->host,strval($port),$database_name))>0)
			return($connection);
		return($this->SetError("Do Connect",IsSet($php_errormsg) ? $php_errormsg : "Could not connect to PostgreSQL server"));
	}

	Function Connect()
	{
		$port=(IsSet($this->options["Port"]) ? $this->options["Port"] : "");
		if($this->connection!=0)
		{
			if(!strcmp($this->connected_host,$this->host)
			&& !strcmp($this->connected_port,$port)
			&& !strcmp($this->selected_database,$this->database_name)
			&& $this->opened_persistent==$this->persistent)
				return(1);
			pg_Close($this->connection);
			$this->affected_rows=-1;
			$this->connection=0;
		}
		if(!($this->connection=$this->DoConnect($this->database_name,$this->persistent)))
			return(0);
		if(!$this->auto_commit
		&& !$this->DoQuery("BEGIN"))
		{
			pg_Close($this->connection);
			$this->connection=0;
			$this->affected_rows=-1;
			return(0);
		}
		$this->connected_host=$this->host;
		$this->connected_port=$port;
		$this->selected_database=$this->database_name;
		$this->opened_persistent=$this->persistent;
		return(1);
	}

	Function Close()
	{
		if($this->connection!=0)
		{
			if(!$this->auto_commit)
				$this->DoQuery("END");
			pg_Close($this->connection);
			$this->connection=0;
			$this->affected_rows=-1;
		}
	}

	Function DoQuery($query)
	{
		if(($result=@pg_Exec($this->connection,$query)))
			$this->affected_rows=(IsSet($this->supported["AffectedRows"]) ? pg_cmdTuples($result) : -1);
		else
			$this->SetError("Do Query",pg_ErrorMessage($this->connection));
		return($result);
	}

	Function Query($query)
	{
		$this->Debug("Query: $query");
		$first=$this->first_selected_row;
		$limit=$this->selected_row_limit;
		$this->first_selected_row=$this->selected_row_limit=0;
		if(!$this->Connect())
			return(0);
		if(($select=!strcmp(strtolower(strtok(ltrim($query)," \t\n\r")),"select"))
		&& $limit>0)
		{
			if($this->auto_commit
			&& !$this->DoQuery("BEGIN"))
				return(0);
			$error="";
			if(($result=$this->DoQuery("DECLARE select_cursor SCROLL CURSOR FOR $query")))
			{
				if($first>0
				&& !($result=$this->DoQuery("MOVE FORWARD $first FROM select_cursor")))
					$error=$this->Error();
				if($result
				&& !($result=$this->DoQuery("FETCH FORWARD $limit FROM select_cursor")))
					$error=$this->Error();
			}
			else
				$error=$this->Error();
			if($this->auto_commit
			&& !$this->DoQuery("END"))
			{
				if($result)
				{
					$error=$this->Error();
					$this->FreeResult($result);
					$result=0;
				}
				else
					$error.=" and could not end the implicit transaction (".$this->Error().")";
			}
			$this->SetError("Query",$error);
		}
		else
			$result=$this->DoQuery($query);
		if($result
		&& $select)
			$this->highest_fetched_row[$result]=-1;
		return($result);
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
		return(pg_result($result,$row,$field));
	}

	Function FetchResultArray($result,&$array,$row)
	{
		if(!($array=pg_fetch_row($result,$row)))
			return($this->SetError("Fetch result array",pg_ErrorMessage($this->connection)));
		$this->highest_fetched_row[$result]=max($this->highest_fetched_row[$result],$row);
		return($this->ConvertResultRow($result,$array));
	}

	Function RetrieveLOB($lob)
	{
		if(!IsSet($this->lobs[$lob]))
			return($this->SetError("Retrieve LOB","it was not specified a valid lob"));
		if(!IsSet($this->lobs[$lob]["Value"]))
		{
			if($this->auto_commit)
			{
				if(!@pg_Exec($this->connection,"BEGIN"))
					return($this->SetError("Retrieve LOB",pg_ErrorMessage($this->connection)));
				$this->lobs[$lob]["InTransaction"]=1;
			}
			$this->lobs[$lob]["Value"]=$this->FetchResult($this->lobs[$lob]["Result"],$this->lobs[$lob]["Row"],$this->lobs[$lob]["Field"]);
			if(!($this->lobs[$lob]["Handle"]=pg_loopen($this->connection,$this->lobs[$lob]["Value"],"r")))
			{
				if(IsSet($this->lobs[$lob]["InTransaction"]))
				{
					@pg_Exec($this->connection,"END");
					UnSet($this->lobs[$lob]["InTransaction"]);
				}
				Unset($this->lobs[$lob]["Value"]);
				return($this->SetError("Retrieve LOB",pg_ErrorMessage($this->connection)));
			}
		}
		return(1);
	}

	Function EndOfResultLOB($lob)
	{
		if(!$this->RetrieveLOB($lob))
			return(0);
		return(IsSet($this->lobs[$lob]["EndOfLOB"]));
	}

	Function ReadResultLOB($lob,&$data,$length)
	{
		if(!$this->RetrieveLOB($lob))
			return(-1);
		$data=pg_loread($this->lobs[$lob]["Handle"],$length);
		if(GetType($data)!="string")
		{
			$this->SetError("Read Result LOB",pg_ErrorMessage($this->connection));
			return(-1);
		}
		if(($length=strlen($data))==0)
			$this->lobs[$lob]["EndOfLOB"]=1;
		return($length);
	}

	Function DestroyResultLOB($lob)
	{
		if(IsSet($this->lobs[$lob]))
		{
			if(IsSet($this->lobs[$lob]["Value"]))
			{
				pg_loclose($this->lobs[$lob]["Handle"]);
				if(IsSet($this->lobs[$lob]["InTransaction"]))
					@pg_Exec($this->connection,"END");
			}
			$this->lobs[$lob]="";
		}
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
		$this->highest_fetched_row[$result]=max($this->highest_fetched_row[$result],$row);
		return(pg_FieldIsNull($result,$row,$field));
	}

	Function NumberOfRows($result)
	{
		return(pg_numrows($result));
	}

	Function FreeResult($result)
	{
		UnSet($this->highest_fetched_row[$result]);
		UnSet($this->columns[$result]);
		UnSet($this->result_types[$result]);
		return(pg_freeresult($result));
	}

	Function GetTextFieldTypeDeclaration($name,&$field)
	{
		return((IsSet($field["length"]) ? "$name VARCHAR (".$field["length"].")" : "$name TEXT").(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetCLOBFieldTypeDeclaration($name,&$field)
	{
		return("$name OID".(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetBLOBFieldTypeDeclaration($name,&$field)
	{
		return("$name OID".(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDateFieldTypeDeclaration($name,&$field)
	{
		return($name." DATE".(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimeFieldTypeDeclaration($name,&$field)
	{
		return($name." TIME".(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetFloatFieldTypeDeclaration($name,&$field)
	{
		return("$name FLOAT8 ".(IsSet($field["default"]) ? " DEFAULT ".$this->GetFloatFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDecimalFieldTypeDeclaration($name,&$field)
	{
		return("$name INT8 ".(IsSet($field["default"]) ? " DEFAULT ".$this->GetDecimalFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetLOBFieldValue($prepared_query,$parameter,$lob,&$value)
	{
		if(!$this->Connect())
			return(0);
		if($this->auto_commit
		&& !@pg_Exec($this->connection,"BEGIN"))
			return(0);
		$success=1;
		if(($lo=pg_locreate($this->connection)))
		{
			if(($handle=pg_loopen($this->connection,$lo,"w")))
			{
				while(!MetabaseEndOfLOB($lob))
				{
					if(MetabaseReadLOB($lob,$data,$this->lob_buffer_length)<0)
					{
						$this->SetError("Get LOB field value",MetabaseLOBError($lob));
						$success=0;
						break;
					}
					if(!pg_lowrite($handle,$data))
					{
						$this->SetError("Get LOB field value",pg_ErrorMessage($this->connection));
						$success=0;
						break;
					}
				}
				pg_loclose($handle);
				if($success)
					$value=strval($lo);
			}
			else
			{
				$this->SetError("Get LOB field value",pg_ErrorMessage($this->connection));
				$success=0;
			}
			if(!$success)
				pg_lounlink($this->connection,$lo);
		}
		else
		{
			$this->SetError("Get LOB field value",pg_ErrorMessage($this->connection));
			$success=0;
		}
		if($this->auto_commit)
			@pg_Exec($this->connection,"END");
		return($success);
	}

	Function GetCLOBFieldValue($prepared_query,$parameter,$clob,&$value)
	{
		return($this->GetLOBFieldValue($prepared_query,$parameter,$clob,$value));
	}

	Function FreeCLOBValue($prepared_query,$clob,&$value,$success)
	{
		if(!$success)
			pg_lounlink($this->connection,intval($value));
	}

	Function GetBLOBFieldValue($prepared_query,$parameter,$blob,&$value)
	{
		return($this->GetLOBFieldValue($prepared_query,$parameter,$blob,$value));
	}

	Function FreeBLOBValue($prepared_query,$blob,&$value,$success)
	{
		if(!$success)
			pg_lounlink($this->connection,intval($value));
	}

	Function GetFloatFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : "$value");
	}

	Function GetDecimalFieldValue($value)
	{
		return(!strcmp($value,"NULL") ? "NULL" : strval(round($value*$this->decimal_factor)));
	}

	Function GetColumnNames($result,&$column_names)
	{
		if(!IsSet($this->highest_fetched_row[$result]))
			return($this->SetError("Get Column Names","it was specified an inexisting result set"));
		if(!IsSet($this->columns[$result]))
		{
			$this->columns[$result]=array();
			$columns=pg_numfields($result);
			for($column=0;$column<$columns;$column++)
				$this->columns[$result][strtolower(pg_fieldname($result,$column))]=$column;
		}
		$column_names=$this->columns[$result];
		return(1);
	}

	Function NumberOfColumns($result)
	{
		if(!IsSet($this->highest_fetched_row[$result]))
		{
			$this->SetError("Number of columns","it was specified an inexisting result set");
			return(-1);
		}
		return(pg_numfields($result));
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
				return(1);
			case METABASE_TYPE_TIMESTAMP:
				$value=substr($value,0,strlen("YYYY-MM-DD HH:MM:SS"));
				return(1);
			default:
				return($this->BaseConvertResult($value,$type));
		}
	}

	Function GetSequenceNextValue($name,&$value)
	{
		if(!($result=$this->Query("SELECT NEXTVAL ('$name')")))
			return(0);
		if($this->NumberOfRows($result)==0)
		{
			$this->FreeResult($result);
			return($this->SetError("Get sequence next value","could not find value in sequence table"));
		}
		$value=intval($this->FetchResult($result,0,0));
		$this->FreeResult($result);
		return(1);
	}

	Function AutoCommitTransactions($auto_commit)
	{
		$this->Debug("AutoCommit: ".($auto_commit ? "On" : "Off"));
		if(((!$this->auto_commit)==(!$auto_commit)))
			return(1);
		if($this->connection)
		{
			if(!$this->Query($auto_commit ? "END" : "BEGIN"))
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
		return($this->Query("COMMIT") && $this->Query("BEGIN"));
	}

	Function RollbackTransaction()
	{
 		$this->Debug("Rollback Transaction");
		if($this->auto_commit)
			return($this->SetError("Rollback transaction","transactions can not be rolled back when changes are auto commited"));
		return($this->Query("ROLLBACK") && $this->Query("BEGIN"));
	}

	Function Setup()
	{
		if(!function_exists("pg_connect"))
			return("PostgreSQL support is not available in this PHP configuration");
		$this->supported["Sequences"]=
		$this->supported["Indexes"]=
		$this->supported["SummaryFunctions"]=
		$this->supported["OrderByText"]=
		$this->supported["Transactions"]=
		$this->supported["GetSequenceCurrentValue"]=
		$this->supported["SelectRowRanges"]=
		$this->supported["LOBs"]=
		$this->supported["Replace"]=
			1;
		if(function_exists("pg_cmdTuples"))
		{
			if(($connection=$this->DoConnect("template1",0)))
			{
				if(($result=@pg_Exec($connection,"BEGIN")))
				{
					$error_reporting=error_reporting(63);
					@pg_cmdTuples($result);
					if(!IsSet($php_errormsg)
					|| strcmp($php_errormsg,"This compilation does not support pg_cmdtuples()"))
						$this->supported["AffectedRows"]=1;
					error_reporting($error_reporting);
				}
				else
					$this->SetError("Setup",pg_ErrorMessage($connection));
				pg_Close($connection);
			}
			else
				$result=0;
			if(!$result)
				return($this->Error());
		}
		$this->decimal_factor=pow(10.0,$this->decimal_places);
		return("");
	}

};

}

?>