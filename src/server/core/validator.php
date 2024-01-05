<?

/**
 * @author Lisa Wall
 * @date 2009-03-28
 */
class Validate
{
	const MISSING_TYPE   = 2;
	const MISSING_VALUE  = 3;
	const INVALID_TYPE   = 4;
	const INVALID_SYNTAX = 5;

	/**
	 * Checks if the specified type is implemented.
	 */
	public static function isType($sType)
	{
		$aTypes = array('STRING', 'INTEGER', 'FLOAT', 'BOOLEAN', 'ENUM', 'DATE', 'EMAIL');
		return in_array($sType, $aTypes);
	}

	public static function getType($aType, $sKey, $sDefault, $bToUpper = false)
	{
		return (array_key_exists($sKey, $aType) ? ($bToUpper ? strtoupper($aType[$sKey]) : $aType[$sKey]) : $sDefault);
	}

	/**
	 * Read html css style like attributes (name:value;name1:value1...) and parser
	 * them into an array of key=>value. At least 1 attribute must exist.
	 *
	 * @return an array of key=>value. If none found or syntax was incorrect returns false.
	 */
	static function parse($sType)
	{
		$aType = array();
		$aAttributes = explode(';', $sType);

		foreach ($aAttributes as $sAttribute)
		{
			$aAttribute = explode(':', $sAttribute);
			$iLength = count($aAttribute);
			if ($iLength == 0 || $iLength > 2) return false;

			$sKey = trim($aAttribute[0]);
			$sValue = ($iLength == 2 ? trim($aAttribute[1]) : $sKey);

			$aType[strtoupper($sKey)] = $sValue;
		}

		return (count($aType) > 0 ? $aType : false);
	}

	static function xml($sSchema, $oXml, &$aValues)
	{
		$oSchema = XML::loadXML($sSchema);
		if ($oSchema === false) return false;

		foreach ($oSchema->attributes as $oAttribute)
		{
			$iResult = 0;
			$sName = $oAttribute->name;
			$sType = $oAttribute->value;
			$bValue = $oXml->hasAttribute($sName);
			$sValue = $oXml->getAttribute($sName);

			if ($bValue === false || ($iResult = Validate::type($sValue, $sType)) !== true) return $sName . '-' . $iResult;

			$aValues[$sName] = $sValue;
		}

		return true;
	}


	static function type(&$sValue, $sType = 'TYPE:STRING')
	{
		//Check and validate the specified type.
		$aType = (is_array($sType) ? $sType : Validate::parse($sType));
		$sType = self::getType($aType, 'TYPE', false, true);

		if ($aType === false) return Validate::INVALID_SYNTAX;
		if ($sType === false) return Validate::MISSING_TYPE;

		//Check the value.
		if ($sValue === null)
		{
			if (!array_key_exists('__OPTIONAL', $aType)) return Validate::MISSING_VALUE;
			$sValue = $aType['__OPTIONAL'];

			if ($sValue == '__NULL')
			{
				$sValue = null;
				return true;
			}
		}
		else if ($sValue == '__NULL') $sValue = null;

		//Validate value.
		switch($sType)
		{
		case 'ENUM':    return Validate::enum($sValue, $aType);
		case 'DATE':    return Validate::date($sValue, $aType);
		case 'EMAIL':   return Validate::email($sValue, $aType);
		case 'FLOAT':   return Validate::float($sValue, $aType);
		case 'STRING':  return Validate::string($sValue, $aType);
		case 'INTEGER': return Validate::integer($sValue, $aType);
		case 'BOOLEAN': return Validate::boolean($sValue, $aType);
		default:        return Validate::INVALID_TYPE;
		}
	}

	//TYPE:ENUM;key1:value1,key2:value2,...  __OPTIONAL:
	static function enum(&$sValue, $aType = array())
	{
		//TODO: Remove first entery: TYPE and __OPTIONAL from the list.

		$sKey = strtoupper($sValue);
		if (!array_key_exists($sKey, $aType)) return false;
		$sValue = $aType[$sKey];
		return true;
	}

	//TYPE:DATE;RETURN:(STRING|DATE|TIMESTAMP);PARTUAL:(FALSE|TRUE)
	static function date(&$sValue, $aType = array())
	{
		$sReturn = self::getType($aType, 'RETURN', 'STRING', true);
		$sPartual = self::getType($aType, 'PARTUAL', 'FALSE', true);

		if (preg_match("/^([0-9]{4,4})-([0-9]{1,2})-([0-9]{1,2})$/", $sValue, $aDate) === false) return false;

		if (!checkdate($aDate[2], $aDate[3], $aDate[1])) return false;

		$iTime = mktime(0, 0, 0, $aDate[2], $aDate[3], $aDate[1]);

		if      ($sReturn == 'STRING')    $sValue = date('Y-m-d', $iTime);
		else if ($sReturn == 'DATE')      $sValue = getdate($iTime);
		else if ($sReturn == 'TIMESTAMP') $sValue = $iTime;

		return true;
	}

	//TYPE:EMAIL;MAX:int
	static function email(&$sValue, $aType = array())
	{
		$iMax = self::getType($aType, 'MAX', 4096);
		$iLength = strlen($sValue);

		// eregi is depricated: return ($iLength > $iMax ? false : eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sValue));
		
		return ($iLength > $iMax ? false : (filter_var($sValue, FILTER_VALIDATE_EMAIL) === false ? false : true));
	}

	static function float(&$sValue, $aType = array('TYPE'=>'FLOAT'))
	{
		return self::numeric($sValue, $aType);
	}

	static function integer(&$sValue, $aType = array('TYPE'=>'INTEGER'))
	{
		return self::numeric($sValue, $aType);
	}

	//TYPE:(FLOAT|INTEGER);MIN:number;MAX:numer
	static function numeric(&$sValue, $aType = array())
	{
		$iMin = self::getType($aType, 'MIN', false);
		$iMax = self::getType($aType, 'MAX', false);
		$sType = self::getType($aType, 'TYPE', 'INTEGER', true);

		//Remove ',' and trim it.
		$sValue = trim(str_replace(',', '', $sValue));

		if (!is_numeric($sValue)) return false;

		if ($sType == 'INTEGER') $sValue = intval($sValue);
		else if ($sType == 'FLOAT') $sValue = floatval($sValue);

		if ($iMin !== false && $sValue < $iMin) return false;
		if ($iMax !== false && $sValue > $iMax) return false;

		return true;
	}

	//TYPE:STRING;MIN:int;MAX:int;TRUNC:(TRUE|FALSE)
	static function string(&$sValue, $aType = array())
	{
		$iMin    = self::getType($aType, 'MIN', 0);
		$iMax    = self::getType($aType, 'MAX', 4096);
		$bTrunc  = self::getType($aType, 'TRUNC', 'TRUE', true);
		$iLength = strlen($sValue);

		if ($iLength < $iMin) return false;
		else if ($iLength > $iMax)
		{
			if (!$bTrunc) return false;
			$sValue = substr($sValue, 0, $iMax);
		}

		return true;
	}

	//TYPE:BOOLEAN;FORCE:(BOOL|INT|EITHER);RETURN:(BOOL|INT|STRING_INT|STRING_BOOL)
	static function boolean(&$sValue, $aType = array())
	{
		$sValue = strtolower($sValue);
		$sForce = self::getType($aType, 'FORCE', 'EITHER', true);
		$sReturn = self::getType($aType, 'RETURN', 'BOOL', true);

		if ($sForce == 'INT' && ($sValue != '0' || $sValue != '1')) return false;
		else if ($sForce == 'BOOL' && ($sValue != 'true' || $sValue != 'false')) return false;

		if      ($sValue == '0')      $sValue = false;
		else if ($sValue == '1')      $sValue = true;
		else if ($sValue == 'true')   $sValue = true;
		else if ($sValue == 'false')  $sValue = false;
		else                          return false;

		if      ($sReturn == 'INT')         $sValue = ($sValue ? 1 : 0);
		else if ($sReturn == 'BOOL')        $sValue = ($sValue ? true : false);
		else if ($sReturn == 'STRING_INT')  $sValue = ($sValue ? '1' : '0');
		else if ($sReturn == 'STRING_BOOL') $sValue = ($sValue ? 'true' : 'false');

		return true;
	}
 }

?>