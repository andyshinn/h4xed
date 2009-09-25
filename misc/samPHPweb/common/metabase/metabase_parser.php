<?php
/*
 * metabase_parser.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/metabase/metabase_parser.php,v 1.49 2002/09/05 00:13:53 mlemos Exp $
 *
 */

/*
 * Parser error numbers:
 *
 * 1 - Could not parse data
 * 2 - Could not read from input stream
 * 3 - Metabase format syntax error
 * 4 - Variable not defined
 *
 */

class metabase_parser_class
{
	/* PUBLIC DATA */
	var $stream_buffer_size=4096;
	var $error_number=0;
	var $error="";
	var $error_line,$error_column,$error_byte_index;
	var $variables=array();
	var $fail_on_invalid_names=1;
	var $database=array();

	/* PRIVATE DATA */
	var $invalid_names=array(
		"user"=>array(),
		"is"=>array(),
		"file"=>array(
			"oci"=>array(),
			"oracle"=>array()
		),
		"notify"=>array(
			"pgsql"=>array()
		),
		"restrict"=>array(
			"mysql"=>array()
		),
		"password"=>array(
			"ibase"=>array()
		)
	);
	var $xml_parser=0;
	var $database_properties=array(
		"name"=>1,
		"create"=>1,
		"table"=>0,
		"sequence"=>0,
		"description"=>0,
		"comment"=>0
	);
	var $table_properties=array(
		"name"=>1,
		"was"=>1,
		"declaration"=>0,
		"initialization"=>0,
		"description"=>0,
		"comment"=>0
	);
	var $field_properties=array(
		"name"=>1,
		"was"=>1,
		"type"=>1,
		"default"=>1,
		"notnull"=>1,
		"unsigned"=>1,
		"length"=>1,
		"description"=>0,
		"comment"=>0
	);
	var $index_properties=array(
		"name"=>1,
		"was"=>1,
		"unique"=>1,
		"field"=>0,
		"description"=>0,
		"comment"=>0
	);
	var $index_field_properties=array(
		"name"=>1,
		"sorting"=>1
	);
	var $sequence_properties=array(
		"name"=>1,
		"was"=>1,
		"start"=>1,
		"on"=>0,
		"description"=>0,
		"comment"=>0
	);
	var $sequence_on_properties=array(
		"table"=>1,
		"field"=>1
	);
	var $insert_properties=array(
		"field"=>0
	);
	var $insert_field_properties=array(
		"name"=>1,
		"value"=>1
	);

	/* PRIVATE METHODS */

	Function SetError($error,$error_number,$line,$column,$byte_index)
	{
		$this->error_number=$error_number;
		$this->error=$error;
		$this->error_line=$line;
		$this->error_column=$column;
		$this->error_byte_index=$byte_index;
		$this->xml_parser=0;
	}

	Function SetParserError($path,$error)
	{
		$this->SetError($error,3,$this->xml_parser->positions[$path]["Line"],$this->xml_parser->positions[$path]["Column"],$this->xml_parser->positions[$path]["Byte"]);
		return($error);
	}

	Function CheckWhiteSpace($data,$path)
	{
		$line=$this->xml_parser->positions[$path]["Line"];
		$column=$this->xml_parser->positions[$path]["Column"];
		$byte_index=$this->xml_parser->positions[$path]["Byte"];
		for($previous_return=0,$position=0;$position<strlen($data);$position++)
		{
			switch($data[$position])
			{
				case " ":
				case "\t":
					$column++;
					$byte_index++;
					$previous_return=0;
					break;
				case "\n":
					if(!$previous_return)
						$line++;
					$column=1;
					$byte_index++;
					$previous_return=0;
					break;
				case "\r":
					$line++;
					$column=1;
					$byte_index++;
					$previous_return=1;
					break;
				default:
					$this->SetError("data is not white space",3,$line,$column,$byte_index);
					return(0);
			}
		}
		return(1);
	}

	Function GetTagData($path,&$value,$error)
	{
		$elements=$this->xml_parser->structure[$path]["Elements"];
		for($value="",$element=0;$element<$elements;$element++)
		{
			$element_path="$path,$element";
			$data=$this->xml_parser->structure[$element_path];
			if(GetType($data)=="array")
			{
				switch($data["Tag"])
				{
					case "variable":
						if(!$this->GetTagData($element_path,$variable,$error))
							return(0);
						if(!IsSet($this->variables[$variable]))
						{
							$this->SetError("it was specified a variable (\"$variable\") that was not defined",4,$this->xml_parser->positions[$element_path]["Line"],$this->xml_parser->positions[$element_path]["Column"],$this->xml_parser->positions[$element_path]["Byte"]);
							return(0);
						}
						$value.=$this->variables[$variable];
						break;
					default:
						$this->SetParserError($element_path,$error);
						return(0);
				}
			}
			else
				$value.=$data;
		}
		return(1);
	}

	Function ValidateFieldValue(&$field,&$value,$value_type,$path)
	{
		switch($field["type"])
		{
			case "text":
			case "clob":
				if(IsSet($field["length"])
				&& strlen($value)>$field["length"])
				{
					$this->SetParserError($path,"it was specified a text field $value_type value that is longer than the specified length value");
					return(0);
				}
				break;
			case "blob":
				if(!eregi("^([0-9a-f]{2})*\$",$value))
				{
					$this->SetParserError($path,"it was specified an invalid hexadecimal binary field $value_type value");
					return(0);
				}
				$value=pack("H*",$value);
				if(IsSet($field["length"])
				&& strlen($value)>$field["length"])
				{
					$this->SetParserError($path,"it was specified a text field $value_type value that is longer than the specified length value");
					return(0);
				}
				break;
			case "integer":
				if(strcmp(strval($value=intval($value)),$value))
				{
					$this->SetParserError($path,"field $value_type is not a valid integer value");
					return(0);
				}
				$value=intval($value);
				if(IsSet($field["unsigned"])
				&& $value<0)
				{
					$this->SetParserError($path,"field $value_type is not a valid unsigned integer value");
					return(0);
				}
				break;
			case "boolean":
				switch($value)
				{
					case "0":
					case "1":
						$value=intval($value);
						break;
					default:
						$this->SetParserError($path,"field $value_type is not a valid boolean value");
						return(0);
				}
				break;
			case "date":
				if(!ereg("^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$",$value))
				{
					$this->SetParserError($path,"field value is not a valid date value");
					return(0);
				}
				break;
			case "timestamp":
				if(!ereg("^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$",$value))
				{
					$this->SetParserError($path,"field value is not a valid timestamp value");
					return(0);
				}
				break;
			case "time":
				if(!ereg("^([0-9]{2}):([0-9]{2}):([0-9]{2})$",$value))
				{
					$this->SetParserError($path,"field value is not a valid time value");
					return(0);
				}
				break;
			case "float":
			case "decimal":
				if(strcmp(strval($value=doubleval($value)),$value))
				{
					$this->SetParserError($path,"field $value_type is not a valid float value");
					return(0);
				}
				break;
		}
		return(1);
	}

	Function ParseProperties($path,&$properties,$property_type,&$tags,&$values)
	{
		$tags=$values=array();
		$property_elements=$this->xml_parser->structure[$path]["Elements"];
		for($property_element=0;$property_element<$property_elements;$property_element++)
		{
			$property_element_path="$path,$property_element";
			$data=$this->xml_parser->structure[$property_element_path];
			if(GetType($data)=="array")
			{
				if(IsSet($properties[$data["Tag"]]))
				{
					if($properties[$data["Tag"]])
					{
						if(IsSet($tags[$data["Tag"]]))
						{
							$this->SetParserError($property_element_path,"$property_type property ".$data["Tag"]." is defined more than once");
							return(0);
						}
						$tags[$data["Tag"]]=$property_element_path;
						if(!$this->GetTagData($property_element_path,$values[$data["Tag"]],"Could not parse $property_type ".$data["Tag"]." property"))
							return(0);
					}
					else
						$tags[$data["Tag"]][]=$property_element_path;
				}
				else
				{
					$this->SetParserError($property_element_path,"it was not specified a valid $property_type property (".$data["Tag"].")");
					return(0);
				}
			}
			else
			{
				if(!$this->CheckWhiteSpace($data,$property_element_path))
					return(0);
			}
		}
		return(1);
	}

	/* PUBLIC METHODS */

	Function Parse($data,$end_of_data)
	{
		if(strcmp($this->error,""))
			return($this->error);
		if(GetType($this->xml_parser)!="object")
		{
			$this->xml_parser=new xml_parser_class;
			$this->xml_parser->store_positions=1;
			$this->xml_parser->simplified_xml=1;
			$this->database=array(
				"name"=>"",
				"create"=>0,
				"TABLES"=>array()
			);
		}
		if(!strcmp($this->error=$this->xml_parser->Parse($data,$end_of_data),""))
		{
			if($end_of_data)
			{
				if(strcmp($this->xml_parser->structure["0"]["Tag"],"database"))
					return($this->SetParserError("0","it was not defined a valid database definition"));
				$this->database=array();
				if(!$this->ParseProperties("0",$this->database_properties,"database",$database_tags,$database_values))
					return($this->error);
				if(!IsSet($database_tags["name"]))
					return($this->SetParserError("0","it was not defined the database name property"));
				if(!strcmp($database_values["name"],""))
					return($this->SetParserError($database_tags["name"],"It was not defined a valid database name"));
				if($this->fail_on_invalid_names
				&& IsSet($this->invalid_names[$database_values["name"]]))
					return($this->SetParserError($database_tags["name"],"It was defined a potentially invalid database name"));
				$this->database["name"]=$database_values["name"];
				if(IsSet($database_tags["create"]))
				{
					switch($database_values["create"])
					{
						case "0":
						case "1":
							$this->database["create"]=$database_values["create"];
							break;
						default:
							$this->SetParserError($database_tags["create"],"it was not defined a valid database create boolean flag value");
							break;
					}
				}
				if(!IsSet($database_tags["table"])
				|| ($tables=count($database_tags["table"]))==0)
					return($this->SetParserError("0","it were not specified any database tables"));
				for($table=0;$table<$tables;$table++)
				{
					$table_definition=array("FIELDS"=>array());
					if(!$this->ParseProperties($database_tags["table"][$table],$this->table_properties,"table",$table_tags,$table_values))
						return($this->error);
					if(!IsSet($table_tags["name"]))
						return($this->SetParserError($database_tags["table"][$table],"it was not defined the table name property"));
					if(!strcmp($table_values["name"],""))
						return($this->SetParserError($table_tags["name"],"It was not defined a valid table name property"));
					if($this->fail_on_invalid_names
					&& IsSet($this->invalid_names[$table_values["name"]]))
						return($this->SetParserError($table_tags["name"],"It was defined a potentially invalid table name property"));
					if(IsSet($this->database["TABLES"][$table_values["name"]]))
						return($this->SetParserError($table_tags["name"],"TABLE is already defined"));
					if(IsSet($table_tags["was"]))
					{
						if(!strcmp($table_definition["was"]=$table_values["was"],""))
							return($this->SetParserError($table_tags["was"],"It was not defined a valid table was property"));
					}
					else
						$table_definition["was"]=$table_values["name"];
					if(!IsSet($table_tags["declaration"]))
						return($this->SetParserError($database_tags["table"][$table],"it was not defined the table declaration section"));
					if(count($table_tags["declaration"])>1)
						return($this->SetParserError($table_tags["declaration"][1],"it was defined the table declaration section more than once"));
					$declaration_tags=$declaration_definition=array();
					$declaration_elements=$this->xml_parser->structure[$table_tags["declaration"][0]]["Elements"];
					for($declaration_element=0;$declaration_element<$declaration_elements;$declaration_element++)
					{
						$declaration_element_path=$table_tags["declaration"][0].",$declaration_element";
						$data=$this->xml_parser->structure[$declaration_element_path];
						if(GetType($data)=="array")
						{
							switch($data["Tag"])
							{
								case "field":
									$declaration_definition=array();
									if(!$this->ParseProperties($declaration_element_path,$this->field_properties,"field",$field_tags,$field_values))
										return($this->error);
									if(!IsSet($field_tags[$property="name"])
									|| !IsSet($field_tags[$property="type"]))
										return($this->SetParserError($declaration_element_path,"it was not defined the field $property property"));
									if(!strcmp($field_values["name"],""))
										return($this->SetParserError($field_tags["name"],"It was not defined a valid field name property"));
									if($this->fail_on_invalid_names
									&& IsSet($this->invalid_names[$field_values["name"]]))
										return($this->SetParserError($field_tags["name"],"It was defined a potentially invalid field name property"));
									if(IsSet($table_definition["FIELDS"][$field_values["name"]]))
										return($this->SetParserError($field_tags["name"],"field is already defined"));
									if(IsSet($field_tags["was"]))
									{
										if(!strcmp($field_values["was"],""))
											return($this->SetParserError($field_tags["was"],"It was not defined a valid field was property"));
										$declaration_definition["was"]=$field_values["was"];
									}
									else
										$declaration_definition["was"]=$field_values["name"];
									switch($declaration_definition["type"]=$field_values["type"])
									{
										case "integer":
											if(IsSet($field_tags["unsigned"]))
											{
												switch($field_values["unsigned"])
												{
													case "1":
														$declaration_definition["unsigned"]=1;
													case "0":
														break;
													default:
														return($this->SetParserError($field_tags["unsigned"],"It was not defined a valid unsigned integer boolean value"));
												}
											}
											break;
										case "text":
										case "clob":
										case "blob":
											if(IsSet($field_tags["length"]))
											{
												$length=intval($field_values["length"]);
												if(strcmp(strval($length),$field_values["length"])
												|| $length<=0)
													return($this->SetParserError($field_tags["length"],"It was not specified a valid text field length value"));
												$declaration_definition["length"]=$length;
											}
											break;
										case "boolean":
										case "date":
										case "timestamp":
										case "time":
										case "float":
										case "decimal":
											break;
										default:
											return($this->SetParserError($field_tags["type"],"It was not defined a valid field type property"));
									}
									if(IsSet($field_tags["notnull"]))
									{
										switch($field_values["notnull"])
										{
											case "1":
												$declaration_definition["notnull"]=1;
											case "0":
												break;
											default:
												return($this->SetParserError($field_tags["notnull"],"It was not defined a valid notnull boolean value"));
										}
									}
									if(IsSet($field_tags["default"]))
									{
										switch($declaration_definition["type"])
										{
											case "clob":
											case "blob":
												return($this->SetParserError($field_tags["default"],"It was specified a default value for a large object field"));
										}
										$value=$field_values["default"];
										if(!$this->ValidateFieldValue($declaration_definition,$value,"default",$field_tags["default"]))
											return($this->error);
										$declaration_definition["default"]=$value;
									}
									else
									{
										if(IsSet($declaration_definition["notnull"]))
										{
											switch($declaration_definition["type"])
											{
												case "clob":
												case "blob":
													break;
												default:
													return($this->SetParserError($field_tags["notnull"],"It was not defined a default value for a notnull field"));
											}
										}
									}
									$table_definition["FIELDS"][$field_values["name"]]=$declaration_definition;
									break;
								case "index":
									$declaration_definition=array("FIELDS"=>array());
									if(!$this->ParseProperties($declaration_element_path,$this->index_properties,"index",$index_tags,$index_values))
										return($this->error);
									if(!IsSet($index_tags["name"]))
										return($this->SetParserError($declaration_element_path,"it was not defined the index name property"));
									if(!strcmp($index_values["name"],""))
										return($this->SetParserError($index_tags["name"],"It was not defined a valid index name property"));
									if($this->fail_on_invalid_names
									&& IsSet($this->invalid_names[$index_values["name"]]))
										return($this->SetParserError($index_tags["name"],"It was defined a potentially invalid index name property"));
									if(IsSet($table_definition["INDEXES"][$index_values["name"]]))
										return($this->SetParserError($index_tags["name"],"index is already defined"));
									if(IsSet($index_tags["was"]))
									{
										if(!strcmp($index_values["was"],""))
											return($this->SetParserError($index_tags["was"],"It was not defined a valid index was property"));
										$declaration_definition["was"]=$index_values["was"];
									}
									else
										$declaration_definition["was"]=$index_values["name"];
									if(IsSet($index_tags["unique"]))
									{
										switch($index_values["unique"])
										{
											case "1":
												$declaration_definition["unique"]=1;
											case "0":
												break;
											default:
												return($this->SetParserError($index_tags["unique"],"It was not defined a valid unique boolean value"));
										}
									}
									if(IsSet($index_tags["field"]))
									{
										$fields=count($index_tags["field"]);
										for($field=0;$field<$fields;$field++)
										{
											$index_declaration_definition=array();
											if(!$this->ParseProperties($index_tags["field"][$field],$this->index_field_properties,"index field",$index_field_tags,$index_field_values))
												return($this->error);
											if(!IsSet($index_field_tags["name"]))
												return($this->SetParserError($index_tags["field"][$field],"It was not defined the index field name property"));
											if(!strcmp($index_field_values["name"],""))
												return($this->SetParserError($index_field_tags["name"],"It was not defined a valid index field name property"));
											if(IsSet($declaration_definition["FIELDS"][$index_field_values["name"]]))
												return($this->SetParserError($index_tags["field"][$field],"Field was already declared for this index"));
											if(!IsSet($table_definition["FIELDS"][$index_field_values["name"]]))
												return($this->SetParserError($index_field_tags["name"],"It was declared index field not defined for this table"));
											switch($table_definition["FIELDS"][$index_field_values["name"]]["type"])
											{
												case "clob":
												case "blob":
													return($this->SetParserError($index_field_tags["name"],"Index field of a large object types are not supported"));
											}
											if(!IsSet($table_definition["FIELDS"][$index_field_values["name"]]["notnull"]))
												return($this->SetParserError($index_tags["field"][$field],"It was declared a index field without defined as notnull"));
											if(IsSet($index_field_tags["sorting"]))
											{
												switch($index_field_values["sorting"])
												{
													case "ascending":
													case "descending":
														$index_declaration_definition["sorting"]=$index_field_values["sorting"];
														break;
													default:
														return($this->SetParserError($index_field_tags["sorting"],"it was not defined a valid index field sorting type"));
												}
											}
											$declaration_definition["FIELDS"][$index_field_values["name"]]=$index_declaration_definition;
										}
									}
									if(count($declaration_definition["FIELDS"])==0)
										return($this->SetParserError($declaration_element_path,"index declaration has not specified any fields"));
									$table_definition["INDEXES"][$index_values["name"]]=$declaration_definition;
									break;
								default:
									return($this->SetParserError($declaration_element_path,"it was not specified a valid table declaration property (".$data["Tag"].")"));
							}
						}
						else
						{
							if(!$this->CheckWhiteSpace($data,$declaration_element_path))
								return($this->error);
						}
					}
					if(count($table_definition["FIELDS"])==0)
						return($this->SetParserError($database_tags["table"][$table],"it were not specified any table fields"));
					if(IsSet($table_tags["initialization"]))
					{
						if(count($table_tags["initialization"])>1)
							return($this->SetParserError($table_tags["initialization"][1],"it was defined the table initialization section more than once"));
						$initialization=array();
						$instruction_elements=$this->xml_parser->structure[$table_tags["initialization"][0]]["Elements"];
						for($instruction_element=0;$instruction_element<$instruction_elements;$instruction_element++)
						{
							$instruction_element_path=$table_tags["initialization"][0].",$instruction_element";
							$data=$this->xml_parser->structure[$instruction_element_path];
							if(GetType($data)=="array")
							{
								switch($data["Tag"])
								{
									case "insert":
										$instruction_definition=array("type"=>"insert","FIELDS"=>array());
										if(!$this->ParseProperties($instruction_element_path,$this->insert_properties,"insert",$insert_tags,$insert_values))
											return($this->error);
										if(($fields=count($insert_tags["field"]))==0)
											return($this->SetParserError($instruction_element_path,"it were not specified any insert fields"));
										for($field=0;$field<$fields;$field++)
										{
											if(!$this->ParseProperties($insert_tags["field"][$field],$this->insert_field_properties,"insert field",$insert_field_tags,$insert_field_values))
												return($this->error);
											if(!IsSet($insert_field_tags[$property="name"])
											|| !IsSet($insert_field_tags[$property="value"]))
												return($this->SetParserError($insert_tags["field"][$field],"it was not defined the insert field $property property"));
											$name=$insert_field_values["name"];
											if(IsSet($instruction_definition["FIELDS"][$name]))
												return($this->SetParserError($insert_field_tags["name"],"insert field is already defined"));
											if(!IsSet($table_definition["FIELDS"][$name]))
												return($this->SetParserError($insert_field_tags["name"],"it was specified a insert field that was not declared"));
											$value=$insert_field_values["value"];
											if(!$this->ValidateFieldValue($table_definition["FIELDS"][$name],$value,"value",$insert_field_tags["value"]))
												return($this->error);
											$instruction_definition["FIELDS"][$name]=$value;
										}
										if(($fields=count($table_definition["FIELDS"]))!=count($instruction_definition["FIELDS"]))
										{
											for(Reset($table_definition["FIELDS"]),$field=0;$field<$fields;$field++,Next($table_definition["FIELDS"]))
											{
												$name=Key($table_definition["FIELDS"]);
												if(!IsSet($instruction_definition["FIELDS"][$name])
												&& IsSet($table_definition["FIELDS"][$name]["notnull"]))
													return($this->SetParserError($instruction_element_path,"It was not specified a field ($name) that may not be inserted as NULL"));
											}
										}
										break;
									default:
										return($this->SetParserError($instruction_element_path,$data["Tag"]." is not table initialization instruction"));
								}
								$initialization[]=$instruction_definition;
							}
							else
							{
								if(!$this->CheckWhiteSpace($data,$instruction_element_path))
									return(0);
							}
						}
						$table_definition["initialization"]=$initialization;
					}
					$this->database["TABLES"][$table_values["name"]]=$table_definition;
				}
				if(IsSet($database_tags["sequence"]))
				{
					$sequences=count($database_tags["sequence"]);
					for($sequence=0;$sequence<$sequences;$sequence++)
					{
						$sequence_definition=array();
						if(!$this->ParseProperties($database_tags["sequence"][$sequence],$this->sequence_properties,"sequence",$sequence_tags,$sequence_values))
							return($this->error);
						if(!IsSet($sequence_tags["name"]))
							return($this->SetParserError($database_tags["sequence"][$sequence],"it was not defined the sequence name property"));
						if(!strcmp($sequence_values["name"],""))
							return($this->SetParserError($sequence_tags["name"],"It was not defined a valid sequence name property"));
						if($this->fail_on_invalid_names
						&& IsSet($this->invalid_names[$sequence_values["name"]]))
							return($this->SetParserError($sequence_tags["name"],"It was defined a potentially invalid sequence name property"));
						if(IsSet($this->database["SEQUENCES"][$sequence_values["name"]]))
							return($this->SetParserError($sequence_tags["name"],"sequence is already defined"));
						if(IsSet($sequence_tags["was"]))
						{
							if(!strcmp($sequence_definition["was"]=$sequence_values["was"],""))
								return($this->SetParserError($sequence_tags["was"],"It was not defined a valid sequence was property"));
						}
						else
							$sequence_definition["was"]=$sequence_values["name"];
						if(IsSet($sequence_tags["start"]))
						{
							$start=$sequence_values["start"];
							if(strcmp(strval(intval($start)),$start))
								return($this->SetParserError($sequence_tags["start"],"it was not specified a valid sequence start value"));
							$sequence_definition["start"]=$start;
						}
						else
							return($this->SetParserError($database_tags["sequence"][$sequence],"it was not specified a valid sequence start value"));
						if(IsSet($sequence_tags["on"]))
						{
							if(count($sequence_tags["on"])>1)
								return($this->SetParserError($sequence_tags["on"][1],"it was defined the sequence on section more than once"));
							$sequence_on_definition=array();
							if(!$this->ParseProperties($sequence_tags["on"][0],$this->sequence_on_properties,"sequence on",$sequence_on_tags,$sequence_on_values))
								return($this->error);
							if(!IsSet($sequence_on_tags[$property="table"])
							|| !IsSet($sequence_on_tags[$property="field"]))
								return($this->SetParserError($sequence_tags["on"][0],"it was not defined the on sequence $property property"));
							$table=$sequence_on_values["table"];
							if(!IsSet($this->database["TABLES"][$table]))
								return($this->SetParserError($sequence_on_tags["table"],"it was not specified a sequence valid on table"));
							$field=$sequence_on_values["field"];
							if(!IsSet($this->database["TABLES"][$table]["FIELDS"][$field])
							|| strcmp($this->database["TABLES"][$table]["FIELDS"][$field]["type"],"integer"))
								return($this->SetParserError($sequence_on_tags["table"],"it was not specified a sequence valid on table integer field"));
							$sequence_definition["on"]=array(
								"table"=>$table,
								"field"=>$field
							);
						}
						$this->database["SEQUENCES"][$sequence_values["name"]]=$sequence_definition;
					}
				}
			}
		}
		else
			$this->SetError($this->error,1,$this->xml_parser->error_line,$this->xml_parser->error_column,$this->xml_parser->error_byte_index);
		return($this->error);
	}

	Function ParseStream($stream)
	{
		if(strcmp($this->error,""))
			return($this->error);
		do
		{
			if(!($data=fread($stream,$this->stream_buffer_size)))
			{
				$this->SetError("Could not read from input stream",2,0,0,0);
				break;
			}
			if(strcmp($error=$this->Parse($data,feof($stream)),""))
				break;
		}
		while(!feof($stream));
		return($this->error);
	}
};

?>