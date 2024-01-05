<?
/**
 * An XML class that provides static functions to simplify use of xml.
 *
 * @author Lisa Wall
 * @date 2008-02-01
 */
class XML
{
	/**
	 * Loads the specified xml file and returns the root element.
	 *
	 * @param sPath the path of the xml file to load.
	 * @return false if an error occured while opening the file, otherwise returns the xml root element.
	 */
	public static function load($sPath)
	{
		//Create a document for the schema
		$pDocument = new DOMDocument("1.0", "UTF-8");

		//Load the schema file.
		$bResult = $pDocument->load($sPath);
		$oElement = $pDocument->documentElement;

		//If a parser error occured or no document element, fail.
		return ($bResult === false ? false : $oElement);
	}

	public static function loadXML($sXML)
	{
		//Create a document for the schema
		$pDocument = new DOMDocument("1.0");

		//Load the schema file.
		$bResult = $pDocument->loadXML($sXML);
		$oElement = $pDocument->documentElement;

		//If a parser error occured or no document element, fail.
		return ($bResult === false ? false : $oElement);
	}

	public static function save($oElement)
	{
		return $oElement->ownerDocument->saveXML($oElement);
	}
	
	public static function count($oParent)
	{
		if ($oParent == null) return 0;

		$iCount = 0;
		$oElement = $oParent->firstChild;
		while ($oElement != null)
		{
			if ($oElement->nodeType == XML_ELEMENT_NODE) $iCount++;
			$oElement = $oElement->nextSibling;
		}

		return $iCount;
	}
	
	public static function getElementAtIndex($oParent, $iIndex)
	{
		if ($oParent == null) return 0;

		$iCount = 0;
		$oElement = $oParent->firstChild;
		while ($oElement != null)
		{
			if ($oElement->nodeType == XML_ELEMENT_NODE)
			{
				if (++$iCount >= $iIndex) break;
			}
			
			$oElement = $oElement->nextSibling;
		}

		return $oElement;
	}

	/**
	 * Converts any xml specific character to thier corresponding codes. (&, <, >, ", ')
	 *
	 * @param sString a string.
	 * @return a clean xml string.
	 */
	public static function clean($sString)
	{
		if (!is_string($sString))
		{
			if      ($sString == 1) $sString = "1";
			else if ($sString == 0) $sString = "0";
		}

		$sString = str_replace( '&', '&amp;' , $sString);
		$sString = str_replace( '<', '&lt;'  , $sString);
		$sString = str_replace( '>', '&gt;'  , $sString);
		$sString = str_replace( '"', '&quot;', $sString);
		$sString = str_replace( "'", '&#39;' , $sString);

		return $sString;
	}

	/**
	 * Creates an xml element string using the specified name as the element's name and
	 * the map array as attribute [name]=>[value].
	 *
	 * @param bClose a boolean indicates if the returned string element is closed or open. ('>' or '/>').
	 * @param sName a string representing the name of the created string element.
	 * @param aArray a map array [name]=>[value] of the attriubtes' names and values.
	 * @reutrn an xml string representation of the element name and attributes.
	 */
	public static function fromArray($bClose, $sName, $aArray)
	{
		$sXML = "<$sName";
		foreach ($aArray as $sAttr => $sValue)
		{
			if ($sAttr == '__name' || $sAttr == '__children') continue;
			$sXML .= " $sAttr=\"" .XML::clean($sValue) ."\"";
		}

		return ($bClose ? "$sXML/>" : "$sXML>");
	}

	/**
	 * Returns an xml string representation of the specified mapped arrys.
	 *
	 * @param sName a string representing the name of the elements.
	 * @param aArrays an array of mapped arrays [name]=>[value] of attributes.
	 * @return an xml string representation of all the elements's within the array.
	 * @see fromArray()
	 */
	public static function fromArrays($sName, $aArrays)
	{
		$sElements = "";
		if ($aArrays != null) foreach($aArrays as $aArray) $sElements .= XML::fromArray(true, $sName, $aArray);
		return $sElements;
	}

	public static function fromArrayTree($sName, $aArrays)
	{
		if ($aArrays == null || count($aArrays) == 0) return '';

		$sElements = '';
		foreach($aArrays as $aArray)
		{
			$sNodeName = (isset($aArray['__name']) ? $aArray['__name'] : $sName);
			$aChildren = (isset($aArray['__children']) ? $aArray['__children'] : null);
			$sElements .= XML::fromArray($aChildren === null, $sNodeName, $aArray);

			if ($aChildren !== null)
			{
				$sElements .= XML::fromArrayTree($sName, $aChildren);
				$sElements .= "</$sNodeName>";
			}
		}

		return $sElements;
	}

	/**
	 * Help serialize/create xml elements. This function accepts a list of name, value of attributes to be serialized.
	 *
	 * @param bClose a boolean indicates if the returned string element is closed or open. ('>' or '/>').
	 * @param sName a string representing the name of the created string element.
	 * @param ... a list of name, value of attributes.
	 * @reutrn an xml serialized string representation of the element name and attributes.
	 */
	public static function serialize($bClose, $sName /*, ...*/)
	{
		$aAttributes = func_get_args();

		$sXML = "";
		for ($i = 2; $i < count($aAttributes); $i+=2)
		{
			$sAttr  = $aAttributes[$i];
			$sValue = $aAttributes[$i+1];
			$sXML .= " ${sAttr}=\"".XML::clean($sValue)."\"";
		}

		return ($bClose ? "<${sName} ${sXML}/>" : "<${sName} ${sXML}>");
	}

	public static function arrayToXML($oElement, $aArray)
	{
		foreach ($aArray as $sAttr => $sValue)
		{
			//Ensure the data is valid
			if (is_string($sValue))
			{
				for ($i = 0; $i < strlen($sValue); $i++)
				{
					$iOrd = ord($sValue{$i});
					if ($iOrd < 32 && !($iOrd == 10 || $iOrd == 13 || $iOrd == 9)) $sValue{$i} = '?';
				}
			}
			$oElement->setAttribute($sAttr, utf8_encode($sValue));
		}
		return $oElement;
	}

	public static function arraysToXML($oParent, $aArrays, $sName)
	{
		if ($aArrays == null) return $oParent;

		foreach($aArrays as $aArray)
		{
			$oElement = $oParent->ownerDocument->createElement($sName);
			$oElement = XML::arrayToXML($oElement, $aArray);
			$oParent->appendChild($oElement);
		}

		return $oParent;
	}

	public static function clear($oParent)
	{
		while ($oParent->firstChild != null) $oParent->removeChild($oParent->firstChild);
	}

	public static function getAttributeBool($oElement, $sName, $bDefault = null)
	{
		if ($oElement == null) return $bDefault;

		$sValue = $oElement->getAttribute($sName);
		return ($sValue == null ? $bDefault : (bool)(strtolower($sValue) == 'true' || $sValue == '1'));
	}

	public static function getAttribute($oElement, $sName, $sDefault = null)
	{
		if ($oElement == null) return $bDefault;

		$sValue = $oElement->getAttribute($sName);
		return ($sValue == null ? $sDefault : $sValue);
	}

	public static function getElementByAttribute($oParent, $sName, $sValue, $bDeep = false)
	{
		for ($oElement = $oParent->firstChild; $oElement != null; $oElement = $oElement->nextSibling)
		{
			if ($oElement->getAttribute($sName) == $sValue) return $oElement;
		}

		return null;
	}

	public static function getElementByTagName($oParent, $sName, $bDeep = false)
	{
		$sName = strtoupper($sName);
		for ($oElement = $oParent->firstChild; $oElement != null; $oElement = $oElement->nextSibling)
		{
			if (strtoupper($oElement->nodeName) == $sName) return $oElement;
		}

		return null;
	}

	public static function setAttributeDefault($oElement, $sName, $sValue)
	{
		if (!$oElement->hasAttribute($sName)) $oElement->setAttribute($sName, $sValue);
		return $sValue;
	}
}

?>