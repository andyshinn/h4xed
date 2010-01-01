<?php
/**
*	@author wickedfather at hotmail.com
*
*	PHP4 and PHP5 compatible
*
*	@modified 14th Apr cdataKey index for open tags fixed
*/

	/**
	*	Class to parse xml into an array the way I like it.
	*
	*	@author wickedfather at hotmail.com
	*	@version 0.0.2
	*/

class XMLThing
{
	    /** @var string xml passed during constructor */
    var $rawXML;
		/** @var array values array filled by xml_parse_into_struct */
    var $valueArray = array();
		/** @var array key array filled by xml_parse_into_struct */
    var $keyArray = array();

	    /** @var array parsed data */
    var $parsed = array();
		/** @var int counter used while parsing */
	var $index = 0;

		/** @var string what the key will be for any attributes found */
	var $attribKey = 'attributes';
		/** @var string name used when translating complete tags with attribs to an attrib/value array */
	var $valueKey = 'value';
		/** @var string name for the key used when adding cdata */
	var $cdataKey = 'cdata';

		/** @var bool whether an error occured */
	var $isError = false;
		/** @var string error description */
	var $error = '';


		/**
		*	Constructor
		*	@param string xml data to parse - optional
		*/

    function XMLThing($xml = NULL)
	{
        $this->rawXML = $xml;
    }


		/**
		*	Turns xml into a lovely array
		*	@param string option xml to parse.  That supplied to constructor used otherwise.
		*	@return array parsed data
		*/

	function parse($xml = NULL)
	{
		if (!is_null($xml))
		{
			$this->rawXML = $xml;
		}

		$this->isError = false;
			// setup
		if (!$this->parse_init())
		{
			return false;
		}

		$this->index = 0;
		$this->parsed = $this->parse_recurse();
		$this->status = 'parsing complete';

		return $this->parsed;
	}


		/**
		*	Turns the struct array into a nested one
		*	@return array
		*/

	function parse_recurse()
	{		// data found at this level
		$found = array();
			// count of the number of times a tag has been found at this level
		$tagCount = array();

			// loop through the tags - I use a lazy referencing thing for where data should go

		while (isset($this->valueArray[$this->index]))
		{
			$tag = $this->valueArray[$this->index];
			$this->index++;

				// if it's a close then return straight away

			if ($tag['type'] == 'close')
			{
				return $found;
			}
				// if it's cdata translate it as a complete tag named $this->cdataKey
			if ($tag['type'] == 'cdata')
			{
				$tag['tag'] = $this->cdataKey;
				$tag['type'] = 'complete';
			}

			$tagName = $tag['tag'];

				// this bit below creates a reference to where the data should be going, 
				// it saves on some conditions in the switch, but it probably annoyingly obfuscating
				// has this tag already appeared at this level ?  If so we need mods

			if (isset($tagCount[$tagName]))
			{		// transform to an array IF only one of the tags been before
				if ($tagCount[$tagName] == 1)
				{
					$found[$tagName] = array($found[$tagName]);
				}
					//$found[$tagName][$tagCount[$tagName]] = '';	// doesn't need to happen, the reference below is enough
				$tagRef =& $found[$tagName][$tagCount[$tagName]];
				$tagCount[$tagName]++;
			}
			else	// just use the variable
			{
				$tagCount[$tagName] = 1;
				$tagRef =& $found[$tagName];
			}

				// now do the work

			switch ($tag['type'])
			{
				case 'open':
					$tagRef = $this->parse_recurse();

					if (isset($tag['attributes']))
					{
						$tagRef[$this->attribKey] = $tag['attributes'];
					}
						// open CAN have a value - it makes sense to include it as cdata
					if (isset($tag['value']))
					{
						if (isset($tagRef[$this->cdataKey]))	// push it to the start of the cdata array if it's present
						{
							$tagRef[$this->cdataKey] = (array)$tagRef[$this->cdataKey];	// <-- needed for php5 compatibility];
							array_unshift($tagRef[$this->cdataKey], $tag['value']);
						}
						else
						{
							$tagRef[$this->cdataKey] = $tag['value'];
						}
					}
					break;

				case 'complete':
					if (isset($tag['attributes']))
					{
						$tagRef[$this->attribKey] = $tag['attributes'];
						$tagRef =& $tagRef[$this->valueKey];
					}

					if (isset($tag['value']))
					{
						$tagRef = $tag['value'];
					}
					break;
			}			
		}

		return $found;
	}


		/**
		*	Turns the xml into the array pairs with xml_parse_into_struct
		*	@return bool
		*/

	function parse_init()
	{
        $this->parser = xml_parser_create();

        $parser = $this->parser;
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 	// Dont mess with my cAsE sEtTings
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);		// Dont bother with empty info
        if (!$res = (bool)xml_parse_into_struct($parser, $this->rawXML, $this->valueArray, $this->keyArray))
		{
			$this->isError = true;
            $this->error = 'error: '.xml_error_string(xml_get_error_code($parser)).' at line '.xml_get_current_line_number($parser);
        }
        xml_parser_free($parser);

		return $res;
	}
}	// *** CLASS ENDS *** //
?>