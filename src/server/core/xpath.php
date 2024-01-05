<?
/**
 * An XML class that provides static functions to simplify use of xml.
 *
 * @author Lisa Wall
 * @date 2008-05-01
 */
class XPath
{
	public static function get($oRoot, $sPath)
	{
		$i = 0;
		$aPath = explode("/", $sPath);
		$oElement = $oRoot;

		if ($oRoot != null)
		{
			//If starts with "/" then start from document element, otherwise from current element.
			if($aPath[0] == "")
			{
				$i = 1;
				$oElement = $oRoot->ownerDocument->documentElement;
			}

			for (; $i < count($aPath); $i++)
			{
				$sSubPath = $aPath[$i];

				if($sSubPath[0] == "@")
				{
					$sName = substr($sSubPath, 1, strlen($sSubPath));
					return $oElement->getAttribute($sName);
				}
				else if($sSubPath[0] == "$")
				{
					return $oElement->ownerDocument->saveXML($oElement);
				}
				else if($sSubPath == "#")
				{
					return ($oElement->firstChild == null ? null : $oElement->firstChild->nodeValue);
				}
				else
				{
					$oElement = XPath::getElementByNodeName($oElement, $sSubPath);
				}

				if($oElement == null) break;
			}
		}

		return $oElement;
	}

	public static function set($oRoot, $sPath, $oObject)
	{
		$i = 0;
		$aPath = explode("/", $sPath);
		$oElement = $oRoot;

		if ($oRoot != null)
		{
			if($aPath[0] == "")
			{
				$i = 1;
				$oElement = $oRoot->ownerDocument->documentElement;
			}

			for (; $i < count($aPath); $i++)
			{
				$sSubPath = $aPath[$i];

				if($sSubPath[0] == "@")
				{
					$sName = substr($sSubPath, 1);
					$oElement->setAttribute($sName, $oObject);
					return $oElement;
				}
				else if($sSubPath == "#")
				{
					XML::clear($oElement);
					//$oElement->appendChild($oRoot->ownerDocument->createTextNode($oObject));
					$oElement->nodeValue  = $oObject;

					//TODO: check if object is a node then add it to element, If string the set nodevalue.

					return $oElement;
				}
				else
				{
					$oElement = XPath::getElementByNodeName($oElement, $sSubPath);
				}

				if($oElement == null) return null;
			}

			$oElement->parentNode->replaceChild($oObject, $oElement);
		}

		return $oElement;
	}

	public static function getElementByNodeName($oParent, $sNodeName)
	{
		$oElement = $oParent->firstChild;
		while ($oElement != null)
		{
			if ($oElement->nodeName == $sNodeName) break;
			$oElement = $oElement->nextSibling;
		}

		return $oElement;
	}

	public static function find($oParent, $sNodeName)
	{
		$sNodeName = strtoupper($sNodeName);
	
		$oCurrent = $oParent;
		while ( ($oCurrent = XPath::next($oParent, $oCurrent)) != null)
		{
			if (strtoupper($oCurrent->nodeName) == $sNodeName) return $oCurrent;
		}
		
		return null;
	}
	
	public static function next($oRoot, $oCurrent)
	{
		if ($oCurrent->firstChild != null)
		{
			return $oCurrent->firstChild;
		}

		if ($oCurrent == $oRoot) return null;

		while ($oCurrent->nextSibling == null)
		{
			$oCurrent = $oCurrent->parentNode;
			if ($oCurrent == $oRoot) return null;
		}

		return $oCurrent->nextSibling;
	}

}

?>