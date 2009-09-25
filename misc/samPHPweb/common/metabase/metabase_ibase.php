<?php
/*
 * metabase_ibase.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_ibase.php,v 1.18 2004/07/27 06:26:03 mlemos Exp $
 *
 */

//Changes by Louis Louw:
//1. Changed @ibase_fetch_row($result,IBASE_TEXT)) so that TEXT blob fields are returned as normal text
//2. Added check to use column Alias names where column Name is not set

if(!defined("METABASE_IBASE_INCLUDED"))
{
	define("METABASE_IBASE_INCLUDED",1);

class metabase_ibase_class extends metabase_database_class
{
	var $connection=0;
	var $connected_host;
	var $connected_user;
	var $connected_password;
	var $connected_database_file;
	var $auto_commit=1;

	var $results=array();
	var $current_row=array();
	var $columns=array();
	var $rows=array();
	var $limits=array();
	var $row_buffer=array();
	var $highest_fetched_row=array();
	var $query_parameters=array();
	var $query_parameter_values=array();
	var $transaction_id=0;
	var $escape_quotes="'";
	var $manager_class_name="metabase_manager_ibase_class";
	var $manager_include="manager_ibase.php";
	var $manager_included_constant="METABASE_MANAGER_IBASE_INCLUDED";

	Function GetDatabaseFile($database_name)
	{
		$database_path=(IsSet($this->options["DatabasePath"]) ? $this->options["DatabasePath"] : "");
		$database_extension=(IsSet($this->options["DatabaseExtension"]) ? $this->options["DatabaseExtension"] : ".gdb");
		return($database_path.$database_name.$database_extension);
	}

	Function Connect()
	{
		if(!strcmp($this->database_name,""))
			return($this->SetError("Connect","it was not specified a valid database name"));
		$database_file=$this->GetDatabaseFile($this->database_name);
		if($this->connection!=0)
		{
			if(!strcmp($this->connected_host,$this->host)
			&& !strcmp($this->connected_user,$this->user)
			&& !strcmp($this->connected_password,$this->password)
			&& !strcmp($this->connected_database_file,$database_file)
			&& $this->opened_persistent==$this->persistent)
				return(1);
			if(!$this->auto_commit
			&& $this->transaction_id
			&& !ibase_rollback($this->transaction_id))
				return($this->SetError("Connect","Could not rollback unfinished transaction: ".ibase_errmsg()));
			$this->transaction_id=0;
			ibase_close($this->connection);
			$this->connection=0;
			$this->affected_rows=-1;
		}
		$function=($this->persistent ? "ibase_pconnect" : "ibase_connect");
		if(!function_exists($function))
			return($this->SetError("Connect","Interbase support is not available in this PHP configuration"));
		if(($this->connection=@$function($this->host.":".$database_file,$this->user,$this->password))<=0)
			return($this->SetError("Connect","Could not connect to Interbase server (".$this->host.":$database_file): ".ibase_errmsg()));
		if(!$this->auto_commit
		&& !($this->transaction_id=ibase_trans(IBASE_COMMITTED,$this->connection)))
			return($this->SetError("Connect","Could not open the initial connection transaction: ".ibase_errmsg()));
		$this->connected_host=$this->host;
		$this->connected_user=$this->user;
		$this->connected_password=$this->password;
		$this->opened_persistent=$this->persistent;
		$this->connected_database_file=$database_file;
		ibase_timefmt("%Y-%m-%d %H:%M:%S",IBASE_TIMESTAMP);
		ibase_timefmt("%Y-%m-%d",IBASE_DATE);
		return(1);
	}

	Function Close()
	{
		if($this->connection!=0)
		{
			if(!$this->auto_commit
			&& $this->transaction_id)
			{
				ibase_rollback($this->transaction_id);
				$this->transaction_id=0;
			}
			ibase_close($this->connection);
			$this->connection=0;
			$this->affected_rows=-1;
		}
	}

	Function GetColumnNames($result,&$column_names)
	{
		$result_value=intval($result);
		if(!IsSet($this->highest_fetched_row[$result_value]))
			return($this->SetError("Get column names","it was specified an inexisting result set"));
		if(!IsSet($this->columns[$result_value]))
		{
			$this->columns[$result_value]=array();
			$columns=ibase_num_fields($result);
			for($column=0;$column<$columns;$column++)
			{
				$column_info=ibase_field_info($result,$column);
				$name = $column_info["name"];
				if(empty($name)) $name = $column_info["alias"];
				$this->columns[$result_value][strtolower($name)]=$column;
			}
		}
		$column_names=$this->columns[$result_value];
		return(1);
	}

	Function NumberOfColumns($result)
	{
		if(!IsSet($this->highest_fetched_row[$result]))
		{
			$this->SetError("Number of columns","it was specified an inexisting result set");
			return(-1);
		}
		return(ibase_num_fields($result));
	}

	Function DoQuery($query,$first=0,$limit=0,$prepared_query=0)
	{
		$connection=($this->auto_commit ? $this->connection : $this->transaction_id);
		if($prepared_query
		&& IsSet($this->query_parameters[$prepared_query])
		&& count($this->query_parameters[$prepared_query])>2)
		{
			if(function_exists("call_user_func_array"))
			{
				$this->query_parameters[$prepared_query][0]=$connection;
				$this->query_parameters[$prepared_query][1]=$query;
				$result=@call_user_func_array("ibase_query",$this->query_parameters[$prepared_query]);
			}
			else
			{
				switch(count($this->query_parameters[$prepared_query]))
				{
					case 3:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2]);
						break;
					case 4:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3]);
						break;
					case 5:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3],$this->query_parameters[$prepared_query][4]);
						break;
					case 6:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3],$this->query_parameters[$prepared_query][4],$this->query_parameters[$prepared_query][5]);
						break;
					case 7:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3],$this->query_parameters[$prepared_query][4],$this->query_parameters[$prepared_query][5],$this->query_parameters[$prepared_query][6]);
						break;
					case 8:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3],$this->query_parameters[$prepared_query][4],$this->query_parameters[$prepared_query][5],$this->query_parameters[$prepared_query][6],$this->query_parameters[$prepared_query][7]);
						break;
					case 9:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3],$this->query_parameters[$prepared_query][4],$this->query_parameters[$prepared_query][5],$this->query_parameters[$prepared_query][6],$this->query_parameters[$prepared_query][7],$this->query_parameters[$prepared_query][8]);
						break;
					case 10:
						$result=@ibase_query($connection,$query,$this->query_parameters[$prepared_query][2],$this->query_parameters[$prepared_query][3],$this->query_parameters[$prepared_query][4],$this->query_parameters[$prepared_query][5],$this->query_parameters[$prepared_query][6],$this->query_parameters[$prepared_query][7],$this->query_parameters[$prepared_query][8],$this->query_parameters[$prepared_query][9]);
						break;
				}
			}
		}
		else
			$result=@ibase_query($connection,$query);
		if($result)
		{
			if(($select=!strcmp(strtolower(strtok(ltrim($query)," \t\n\r")),"select")))
			{
				$result_value=intval($result);
				$this->current_row[$result_value]=-1;
				if($limit>0)
					$this->limits[$result_value]=array($first,$limit,0);
				$this->highest_fetched_row[$result_value]=-1;
			}
			else
				$this->affected_rows=-1;
		}
		else
			return($this->SetError("Do query","Could not execute query ($query): ".ibase_errmsg()));
		return($result);
	}

	Function Query($query)
	{
		$this->Debug("Query: $query");
		$first=$this->first_selected_row;
		$limit=$this->selected_row_limit;
		$this->first_selected_row=$this->selected_row_limit=0;
		if(!$this->Connect($this->user,$this->password,$this->persistent))
			return(0);
		return($this->DoQuery($query,$first,$limit,0));
	}

	Function ExecutePreparedQuery($prepared_query,$query)
	{
		$first=$this->first_selected_row;
		$limit=$this->selected_row_limit;
		$this->first_selected_row=$this->selected_row_limit=0;
		if(!$this->Connect($this->user,$this->password,$this->persistent))
			return(0);
		return($this->DoQuery($query,$first,$limit,$prepared_query));
	}

	Function SkipFirstRows($result)
	{
		$result_value=intval($result);
		$first=$this->limits[$result_value][0];
		for(;$this->limits[$result_value][2]<$first;$this->limits[$result_value][2]++)
		{
			if(GetType(@ibase_fetch_row($result,IBASE_TEXT))!="array")
			{
				$this->limits[$result_value][2]=$first;
				return($this->SetError("Skip first rows","could not skip a query result row"));
			}
		}
		return(1);
	}

	Function FetchRow($result,$row)
	{
		$result_value=intval($result);
		if(!IsSet($this->current_row[$result_value]))
			return($this->SetError("Fetch row","attempted to fetch a row from an unknown query result"));
		if(IsSet($this->results[$result_value][$row]))
			return(1);
		if(IsSet($this->rows[$result_value]))
			return($this->SetError("Fetch row","there are no more rows to retrieve"));
		if(IsSet($this->limits[$result_value]))
		{
			if($row>=$this->limits[$result_value][1])
				return($this->SetError("Fetch row","attempted to fetch a row beyhond the number rows available in the query result"));
			if(!$this->SkipFirstRows($result))
				return(0);
		}
		if(IsSet($this->row_buffer[$result_value]))
		{
			$this->current_row[$result_value]++;
			$this->results[$result_value][$this->current_row[$result_value]]=$this->row_buffer[$result_value];
			Unset($this->row_buffer[$result_value]);
		}
		for(;$this->current_row[$result_value]<$row;$this->current_row[$result_value]++)
		{
			if(GetType($this->results[$result_value][$this->current_row[$result_value]+1]=@ibase_fetch_row($result,IBASE_TEXT))!="array")
			{
				$this->rows[$result_value]=$this->current_row[$result_value]+1;
				return($this->SetError("Fetch row","could not fetch the query result row"));
			}
		}
		return(1);
	}

	Function GetColumn($result,$field)
	{
		$result_value=intval($result);
		if(!$this->GetColumnNames($result,$column_names))
			return(-1);
		if(GetType($field)=="integer")
		{
			if(($column=$field)<0
			|| $column>=count($this->columns[$result_value]))
			{
				$this->SetError("Get column","attempted to fetch an query result column out of range");
				return(-1);
			}
		}
		else
		{
			$name=strtolower($field);
			if(!IsSet($this->columns[$result_value][$name]))
			{
				$this->SetError("Get column","attempted to fetch an unknown query result column");
				return(-1);
			}
			$column=$this->columns[$result_value][$name];
		}
		return($column);
	}

	Function EndOfResult($result)
	{
		$result_value=intval($result);
		if(!IsSet($this->current_row[$result_value]))
		{
			$this->SetError("End of result","attempted to check the end of an unknown result");
			return(-1);
		}
		if(IsSet($this->rows[$result_value]))
			return($this->highest_fetched_row[$result_value]>=$this->rows[$result_value]-1);
		if(IsSet($this->row_buffer[$result_value]))
			return(0);
		if(IsSet($this->limits[$result_value]))
		{
			if(!$this->SkipFirstRows($result)
			|| $this->current_row[$result_value]+1>=$this->limits[$result_value][1])
			{
				$this->rows[$result_value]=0;
				return(1);
			}
		}
		if(GetType($this->row_buffer[$result_value]=@ibase_fetch_row($result,IBASE_TEXT))=="array")
			return(0);
		Unset($this->row_buffer[$result_value]);
		$this->rows[$result_value]=$this->current_row[$result_value]+1;
		return(1);
	}

	Function FetchResult($result,$row,$field)
	{
		$result_value=intval($result);
		if(($column=$this->GetColumn($result,$field))==-1
		|| !$this->FetchRow($result,$row))
			return("");
		if(!IsSet($this->results[$result_value][$row][$column]))
			return("");
		$this->highest_fetched_row[$result_value]=max($this->highest_fetched_row[$result_value],$row);
		return($this->results[$result_value][$row][$column]);
	}

	Function FetchResultArray($result,&$array,$row)
	{
		if(!$this->FetchRow($result,$row))
			return(0);
		$result_value=intval($result);
		$array=$this->results[$result_value][$row];
		$this->highest_fetched_row[$result_value]=max($this->highest_fetched_row[$result_value],$row);
		return($this->ConvertResultRow($result,$array));
	}

	Function RetrieveLOB($lob)
	{
		if(!IsSet($this->lobs[$lob]))
			return($this->SetError("Retrieve LOB","it was not specified a valid lob"));
		if(!IsSet($this->lobs[$lob]["Value"]))
		{
			$this->lobs[$lob]["Value"]=$this->FetchResult($this->lobs[$lob]["Result"],$this->lobs[$lob]["Row"],$this->lobs[$lob]["Field"]);
			if(!($this->lobs[$lob]["Handle"]=ibase_blob_open($this->lobs[$lob]["Value"])))
			{
				Unset($this->lobs[$lob]["Value"]);
				return($this->SetError("Retrieve LOB","Could not open fetched large object field: ".ibase_errmsg()));
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
		$data=ibase_blob_get($this->lobs[$lob]["Handle"],$length);
		if(GetType($data)!="string")
		{
			$this->SetError("Read result LOB","Could not open fetched large object field: ".ibase_errmsg());
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
				ibase_blob_close($this->lobs[$lob]["Handle"]);
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
		$result_value=intval($result);
		if(($column=$this->GetColumn($result,$field))==-1
		|| !$this->FetchRow($result,$row))
			return(0);
		$this->highest_fetched_row[$result_value]=max($this->highest_fetched_row[$result_value],$row);
		return(!IsSet($this->results[$result_value][$row][$column]));
	}

	Function NumberOfRows($result)
	{
		$result_value=intval($result);
		if(!IsSet($this->current_row[$result_value]))
			return($this->SetError("Number of rows","attemped to obtain the number of rows contained in an unknown query result"));
		if(!IsSet($this->rows[$result_value]))
		{
			if(!$this->GetColumnNames($result,$column_names))
				return(0);
			if(IsSet($this->limits[$result_value]))
			{
				if(!$this->SkipFirstRows($result))
				{
					$this->rows[$result_value]=0;
					return(0);
				}
				$limit=$this->limits[$result_value][1];
			}
			else
				$limit=0;
			if($limit==0
			|| $this->current_row[$result_value]+1<$limit)
			{
				if(IsSet($this->row_buffer[$result_value]))
				{
					$this->current_row[$result_value]++;
					$this->results[$result_value][$this->current_row[$result_value]]=$this->row_buffer[$result_value];
					Unset($this->row_buffer[$result_value]);
				}
				for(;($limit==0 || $this->current_row[$result_value]+1<$limit) && GetType($this->results[$result_value][$this->current_row[$result_value]+1]=@ibase_fetch_row($result,IBASE_TEXT))=="array";$this->current_row[$result_value]++);
			}
			$this->rows[$result_value]=$this->current_row[$result_value]+1;
		}
		return($this->rows[$result_value]);
	}

	Function FreeResult($result)
	{
		$result_value=intval($result);
		if(!IsSet($this->current_row[$result_value]))
			return($this->SetError("Free result","attemped to free an unknown query result"));
		UnSet($this->highest_fetched_row[$result_value]);
		UnSet($this->row_buffer[$result_value]);
		UnSet($this->limits[$result_value]);
		UnSet($this->current_row[$result_value]);
		UnSet($this->results[$result_value]);
		UnSet($this->columns[$result_value]);
		UnSet($this->rows[$result_value]);
		UnSet($this->result_types[$result]);
		return(ibase_free_result($result));
	}

	Function GetFieldTypeDeclaration(&$field)
	{
		switch($field["type"])
		{
			case "text":
				return("VARCHAR (".(IsSet($field["length"]) ? $field["length"] : (IsSet($this->options["DefaultTextFieldLength"]) ? $this->options["DefaultTextFieldLength"] : 4000)).")");
			case "clob":
				return("BLOB SUB_TYPE 1");
			case "blob":
				return("BLOB SUB_TYPE 0");
			case "integer":
				return("INTEGER");
			case "boolean":
				return("CHAR (1)");
			case "date":
				return("DATE");
			case "timestamp":
				return("TIMESTAMP");
			case "time":
				return("TIME");
			case "float":
				return("DOUBLE PRECISION");
			case "decimal":
				return("DECIMAL(18". "," . $this->decimal_places .")");
		}
	}

	Function GetTextFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT ".$this->GetTextFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetCLOBFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetBLOBFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetIntegerFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT ".$field["default"] : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetBooleanFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT ".($field["default"] ? "'Y'" : "'N'") : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDateFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimestampFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetTimeFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetFloatFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT ".$this->GetFloatFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetDecimalFieldTypeDeclaration($name,&$field)
	{
		return($name." ".$this->GetFieldTypeDeclaration($field).(IsSet($field["default"]) ? " DEFAULT ".$this->GetDecimalFieldValue($field["default"]) : "").(IsSet($field["notnull"]) ? " NOT NULL" : ""));
	}

	Function GetLOBFieldValue($prepared_query,$parameter,$lob,&$value)
	{
		if(!$this->Connect())
			return(0);
		$success=1;
		if(($blob=ibase_blob_create($this->auto_commit ? $this->connection : $this->transaction_id)))
		{
			while(!MetabaseEndOfLOB($lob))
			{
				if(MetabaseReadLOB($lob,$data,$this->lob_buffer_length)<0)
				{
					$this->SetError("Get LOB field value",MetabaseLOBError($lob));
					$success=0;
					break;
				}
				if(!ibase_blob_add($blob,$data))
				{
					$this->SetError("Get LOB field value","Could not add data to a large object: ".ibase_errmsg());
					$success=0;
					break;
				}
			}
			if($success)
			{
				if(GetType($value=ibase_blob_close($blob)))
				{
					if(!IsSet($this->query_parameters[$prepared_query]))
					{
						$this->query_parameters[$prepared_query]=array(0,"");
						$this->query_parameter_values[$prepared_query]=array();
					}
					$query_parameter=count($this->query_parameters[$prepared_query]);
					$this->query_parameter_values[$prepared_query][$parameter]=$query_parameter;
					$this->query_parameters[$prepared_query][$query_parameter]=$value;
					$value="?";					
				}
				else
					$success=0;
			}
			if(!$success)
				ibase_blob_cancel($blob);
		}
		else
		{
			$this->SetError("Get LOB field value","Could not create a large object: ".ibase_errmsg());
			$success=0;
		}
		return($success);
	}

	Function FreeLOBValue($prepared_query,$lob,&$value,$success)
	{
		$query_parameter=$this->query_parameter_values[$prepared_query][$lob];

		Unset($this->query_parameters[$prepared_query][$query_parameter]);
		Unset($this->query_parameter_values[$prepared_query][$lob]);
		if(count($this->query_parameter_values[$prepared_query])==0)
		{
			Unset($this->query_parameters[$prepared_query]);
			Unset($this->query_parameter_values[$prepared_query]);
		}		
		Unset($value);
	}

	Function GetCLOBFieldValue($prepared_query,$parameter,$clob,&$value)
	{
		return($this->GetLOBFieldValue($prepared_query,$parameter,$clob,$value));
	}

	Function FreeCLOBValue($prepared_query,$clob,&$value,$success)
	{
		$this->FreeLOBValue($prepared_query,$clob,$value,$success);
	}

	Function GetBLOBFieldValue($prepared_query,$parameter,$blob,&$value)
	{
		return($this->GetLOBFieldValue($prepared_query,$parameter,$blob,$value));
	}

	Function FreeBLOBValue($prepared_query,$blob,&$value,$success)
	{
		$this->FreeLOBValue($prepared_query,$blob,$value,$success);
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
		if(($result=$this->Query("SELECT GEN_ID($name,1) as the_value FROM RDB\$DATABASE "))==0)
			return($this->SetError("Get sequence next value", ibase_errmsg()));
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
			if($auto_commit)
			{
				if(!ibase_commit($this->transaction_id))
					return($this->SetError("Auto commit transactions","Could not commit a pending transaction: ".ibase_errmsg()));
				$this->transaction_id=0;
			}
			else
			{
				if(!($this->transaction_id=ibase_trans(IBASE_COMMITTED,$this->connection)))
					return($this->SetError("Auto commit transactions","Could not start a transaction: ".ibase_errmsg()));
			}
		}
		$this->auto_commit=$auto_commit;
		return($this->RegisterTransactionShutdown($auto_commit));
	}

	Function CommitTransaction()
	{
 		$this->Debug("Commit Transaction");
		if($this->auto_commit)
			return($this->SetError("Commit transaction","transaction changes are being auto commited"));
		if($this->transaction_id
		&& !ibase_commit($this->transaction_id))
			return($this->SetError("Commit transaction","Could not commit a pending transaction: ".ibase_errmsg()));
		if(!($this->transaction_id=ibase_trans(IBASE_COMMITTED,$this->connection)))
			return($this->SetError("Commit transaction","Could start a new transaction: ".ibase_errmsg()));
		return(1);
	}

	Function RollbackTransaction()
	{
 		$this->Debug("Rollback Transaction");
		if($this->auto_commit)
			return($this->SetError("Rollback transaction","transactions can not be rolled back when changes are auto commited"));
		if($this->transaction_id
		&& !ibase_rollback($this->transaction_id))
			return($this->SetError("Rollback transaction","Could not rollback a pending transaction: ".ibase_errmsg()));
		if(!($this->transaction_id=ibase_trans(IBASE_COMMITTED,$this->connection)))
			return($this->SetError("Rollback transaction","Could start a new transaction: ".ibase_errmsg()));
		return(1);
	}

	Function Setup()
	{
		$this->supported["Sequences"]=
		$this->supported["Indexes"]=
		$this->supported["IndexSorting"]=
		$this->supported["SummaryFunctions"]=
		$this->supported["OrderByText"]=
		$this->supported["GetSequenceCurrentValue"]=
		$this->supported["SelectRowRanges"]=
		$this->supported["Transactions"]=
		$this->supported["LOBs"]=
		$this->supported["Replace"]=
			1;
		return("");
	}
};

}
?>