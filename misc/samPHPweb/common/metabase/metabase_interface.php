<?php
/*
 * metabase_interface.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_interface.php,v 1.68 2002/12/11 22:52:24 mlemos Exp $
 *
 */

Function MetabaseSetupDatabase($arguments,&$database)
{
	global $metabase_databases;

	$database=count($metabase_databases)+1;
	if(strcmp($error=MetabaseSetupInterface($arguments,$metabase_databases[$database]),""))
	{
		Unset($metabase_databases[$database]);
		$database=0;
	}
	else
		$metabase_databases[$database]->database=$database;
	return($error);
}

Function MetabaseQuery($database,$query)
{
	global $metabase_databases;

	return($metabase_databases[$database]->Query($query));
}

Function MetabaseQueryField($database,$query,&$field,$type="text")
{
	global $metabase_databases;

	return($metabase_databases[$database]->QueryField($query,$field,$type));
}

Function MetabaseQueryRow($database,$query,&$row,$types="")
{
	global $metabase_databases;

	return($metabase_databases[$database]->QueryRow($query,$row,$types));
}

Function MetabaseQueryColumn($database,$query,&$column,$type="text")
{
	global $metabase_databases;

	return($metabase_databases[$database]->QueryColumn($query,$column,$type));
}

Function MetabaseQueryAll($database,$query,&$all,$types="")
{
	global $metabase_databases;

	return($metabase_databases[$database]->QueryAll($query,$all,$types));
}

Function MetabaseReplace($database,$table,&$fields)
{
	global $metabase_databases;

	return($metabase_databases[$database]->Replace($table,$fields));
}

Function MetabasePrepareQuery($database,$query)
{
	global $metabase_databases;

	return($metabase_databases[$database]->PrepareQuery($query));
}

Function MetabaseFreePreparedQuery($database,$prepared_query)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FreePreparedQuery($prepared_query));
}

Function MetabaseExecuteQuery($database,$prepared_query)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ExecuteQuery($prepared_query));
}

Function MetabaseQuerySet($database,$prepared_query,$parameter,$type,$value,$is_null=0,$field="")
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySet($prepared_query,$parameter,$type,$value,$is_null,$field));
}

Function MetabaseQuerySetNull($database,$prepared_query,$parameter,$type)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetNull($prepared_query,$parameter,$type));
}

Function MetabaseQuerySetText($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetText($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetCLOB($database,$prepared_query,$parameter,$value,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetCLOB($prepared_query,$parameter,$value,$field));
}

Function MetabaseQuerySetBLOB($database,$prepared_query,$parameter,$value,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetBLOB($prepared_query,$parameter,$value,$field));
}

Function MetabaseQuerySetInteger($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetInteger($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetBoolean($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetBoolean($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetDate($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetDate($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetTimestamp($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetTimestamp($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetTime($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetTime($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetFloat($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetFloat($prepared_query,$parameter,$value));
}

Function MetabaseQuerySetDecimal($database,$prepared_query,$parameter,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->QuerySetDecimal($prepared_query,$parameter,$value));
}

Function MetabaseAffectedRows($database,&$affected_rows)
{
	global $metabase_databases;

	return($metabase_databases[$database]->AffectedRows($affected_rows));
}

Function MetabaseFetchResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchResult($result,$row,$field));
}

Function MetabaseFetchCLOBResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchCLOBResult($result,$row,$field));
}

Function MetabaseFetchBLOBResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchBLOBResult($result,$row,$field));
}

Function MetabaseDestroyResultLOB($database,$lob)
{
	global $metabase_databases;

	return($metabase_databases[$database]->DestroyResultLOB($lob));
}

Function MetabaseEndOfResultLOB($database,$lob)
{
	global $metabase_databases;

	return($metabase_databases[$database]->EndOfResultLOB($lob));
}

Function MetabaseReadResultLOB($database,$lob,&$data,$length)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ReadResultLOB($lob,$data,$length));
}

Function MetabaseResultIsNull($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ResultIsNull($result,$row,$field));
}

Function MetabaseFetchDateResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchDateResult($result,$row,$field));
}

Function MetabaseFetchTimestampResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchTimestampResult($result,$row,$field));
}

Function MetabaseFetchTimeResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchTimeResult($result,$row,$field));
}

Function MetabaseFetchBooleanResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchBooleanResult($result,$row,$field));
}

Function MetabaseFetchFloatResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchFloatResult($result,$row,$field));
}

Function MetabaseFetchDecimalResult($database,$result,$row,$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchDecimalResult($result,$row,$field));
}

Function MetabaseFetchResultField($database,$result,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchResultField($result,$field));
}

Function MetabaseFetchResultArray($database,$result,&$array,$row)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchResultArray($result,$array,$row));
}

Function MetabaseFetchResultRow($database,$result,&$row)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchResultRow($result,$row));
}

Function MetabaseFetchResultColumn($database,$result,&$column)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchResultColumn($result,$column));
}

Function MetabaseFetchResultAll($database,$result,&$all)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FetchResultAll($result,$all));
}

Function MetabaseNumberOfRows($database,$result)
{
	global $metabase_databases;

	return($metabase_databases[$database]->NumberOfRows($result));
}

Function MetabaseNumberOfColumns($database,$result)
{
	global $metabase_databases;

	return($metabase_databases[$database]->NumberOfColumns($result));
}

Function MetabaseGetColumnNames($database,$result,&$column_names)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetColumnNames($result,$column_names));
}

Function MetabaseSetResultTypes($database,$result,&$types)
{
	global $metabase_databases;

	return($metabase_databases[$database]->SetResultTypes($result,$types));
}

Function MetabaseFreeResult($database,$result)
{
	global $metabase_databases;

	return($metabase_databases[$database]->FreeResult($result));
}

Function MetabaseError($database)
{
	global $metabase_databases;

	return($metabase_databases[$database]->Error());
}

Function MetabaseSetErrorHandler($database,$function)
{
	global $metabase_databases;

	return($metabase_databases[$database]->SetErrorHandler($function));
}

Function MetabaseCreateDatabase($database,$name)
{
	global $metabase_databases;

	return($metabase_databases[$database]->CreateDatabase($name));
}

Function MetabaseDropDatabase($database,$name)
{
	global $metabase_databases;

	return($metabase_databases[$database]->DropDatabase($name));
}

Function MetabaseSetDatabase($database,$name)
{
	global $metabase_databases;

	return($metabase_databases[$database]->SetDatabase($name));
}

Function MetabaseGetIntegerFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetIntegerFieldTypeDeclaration($name,$field));
}

Function MetabaseGetTextFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTextFieldTypeDeclaration($name,$field));
}

Function MetabaseGetCLOBFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetCLOBFieldTypeDeclaration($name,$field));
}

Function MetabaseGetBLOBFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetBLOBFieldTypeDeclaration($name,$field));
}

Function MetabaseGetBooleanFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetBooleanFieldTypeDeclaration($name,$field));
}

Function MetabaseGetDateFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetDateFieldTypeDeclaration($name,$field));
}

Function MetabaseGetTimestampFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTimestampFieldTypeDeclaration($name,$field));
}

Function MetabaseGetTimeFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTimeFieldTypeDeclaration($name,$field));
}

Function MetabaseGetFloatFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetFloatFieldTypeDeclaration($name,$field));
}

Function MetabaseGetDecimalFieldTypeDeclaration($database,$name,&$field)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetDecimalFieldTypeDeclaration($name,$field));
}

Function MetabaseGetTextFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTextFieldValue($value));
}

Function MetabaseGetBooleanFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetBooleanFieldValue($value));
}

Function MetabaseGetDateFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetDateFieldValue($value));
}

Function MetabaseGetTimestampFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTimestampFieldValue($value));
}

Function MetabaseGetTimeFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTimeFieldValue($value));
}

Function MetabaseGetFloatFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetFloatFieldValue($value));
}

Function MetabaseGetDecimalFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetDecimalFieldValue($value));
}

Function MetabaseGetIntegerFieldValue($database,$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetIntegerFieldValue($value));
}

Function MetabaseSupport($database,$feature)
{
	global $metabase_databases;

	return($metabase_databases[$database]->Support($feature));
}

Function MetabaseCreateTable($database,$name,&$fields)
{
	global $metabase_databases;

	return($metabase_databases[$database]->CreateTable($name,$fields));
}

Function MetabaseDropTable($database,$name)
{
	global $metabase_databases;

	return($metabase_databases[$database]->DropTable($name));
}

Function MetabaseAlterTable($database,$name,&$changes,$check=0)
{
	global $metabase_databases;

	return($metabase_databases[$database]->AlterTable($name,$changes,$check));
}

Function MetabaseListTables($database,&$tables)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ListTables($tables));
}

Function MetabaseListTableFields($database,$table,&$fields)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ListTableFields($table,$fields));
}

Function MetabaseGetTableFieldDefinition($database,$table,$field,&$definition)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTableFieldDefinition($table,$field,$definition));
}

Function MetabaseListTableIndexes($database,$table,&$indexes)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ListTableIndexes($table,$indexes));
}

Function MetabaseGetTableIndexDefinition($database,$table,$index,&$definition)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetTableIndexDefinition($table,$index,$definition));
}

Function MetabaseListSequences($database,&$sequences)
{
	global $metabase_databases;

	return($metabase_databases[$database]->ListSequences($sequences));
}

Function MetabaseGetSequenceDefinition($database,$sequence,&$definition)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetSequenceDefinition($sequence,$definition));
}

Function MetabaseCreateSequence($database,$name,$start)
{
	global $metabase_databases;

	return($metabase_databases[$database]->CreateSequence($name,$start));
}

Function MetabaseDropSequence($database,$name)
{
	global $metabase_databases;

	return($metabase_databases[$database]->DropSequence($name));
}

Function MetabaseGetSequenceNextValue($database,$name,&$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetSequenceNextValue($name,$value));
}

Function MetabaseGetSequenceCurrentValue($database,$name,&$value)
{
	global $metabase_databases;

	return($metabase_databases[$database]->GetSequenceCurrentValue($name,$value));
}

Function MetabaseAutoCommitTransactions($database,$auto_commit)
{
	global $metabase_databases;

	return($metabase_databases[$database]->AutoCommitTransactions($auto_commit));
}

Function MetabaseCommitTransaction($database)
{
	global $metabase_databases;

	return($metabase_databases[$database]->CommitTransaction());
}

Function MetabaseRollbackTransaction($database)
{
	global $metabase_databases;

	return($metabase_databases[$database]->RollbackTransaction());
}

Function MetabaseCreateIndex($database,$table,$name,$definition)
{
	global $metabase_databases;

	return($metabase_databases[$database]->CreateIndex($table,$name,$definition));
}

Function MetabaseDropIndex($database,$table,$name)
{
	global $metabase_databases;

	return($metabase_databases[$database]->DropIndex($table,$name));
}

Function MetabaseSetSelectedRowRange($database,$first,$limit)
{
	global $metabase_databases;

	return($metabase_databases[$database]->SetSelectedRowRange($first,$limit));
}

Function MetabaseEndOfResult($database,$result)
{
	global $metabase_databases;

	return($metabase_databases[$database]->EndOfResult($result));
}

Function MetabaseCaptureDebugOutput($database,$capture)
{
	global $metabase_databases;

	$metabase_databases[$database]->CaptureDebugOutput($capture);
}

Function MetabaseDebugOutput($database)
{
	global $metabase_databases;

	return($metabase_databases[$database]->DebugOutput());
}

Function MetabaseDebug($database,$message)
{
	global $metabase_databases;

	return($metabase_databases[$database]->Debug($message));
}

?>