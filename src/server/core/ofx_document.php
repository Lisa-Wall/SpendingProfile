<?
/**
 * @author Lisa Wall
 * @date 2009-03-28
 */
class OFXDocument extends DOMDocument
{
	public function loadOFXFile($sFileName)
	{
		//Read file content.
		if ( ($sContent = file_get_contents($sFileName)) === false) return false;

		//Check file head to see if 1.0 or 2.0
		return (strpos($sContent, 'VERSION:1') === false ? $this->loadXML($sContent) : $this->loadOFX($sContent));
	}

	public function loadOFX($sOFXContent)
	{
		$oCurrent = $this;

		//Create an OFX parser.
		$oParser = new OFXReader($sOFXContent);

		//Read header
		if ( ($aHeader = $oParser->header()) === false) return false;

		//Read Document.
		$iToken = 0;
		$sValue = null;
		while ( ($iToken = $oParser->next($sValue)) !== false)
		{
			switch($iToken)
			{
			case OFX_PARSER_OPEN:

			  $oCurrent = $oCurrent->appendChild($this->createElement($sValue));
			  break;

			case OFX_PARSER_CLOSE:

			  while ($oCurrent != null && $oCurrent->nodeName != $sValue) $oCurrent = $oCurrent->parentNode;
			  $oCurrent = $oCurrent->parentNode;

			  if ($oCurrent == null) return error('Close tag does not match: $sValue', false);
			  break;

			case OFX_PARSER_TEXT:

			  $oCurrent->nodeValue = $sValue;
			  $oCurrent = $oCurrent->parentNode;
			  break;

			case OFX_PARSER_COMMENT:
			  break;
			default:
			  return;
			}
		}

		return true;
	}

	public function saveOFX(DOMNode $oNode = null)
	{
		$sHeader = "OFXHEADER:100\nDATA:OFXSGML\nVERSION:102\nSECURITY:TYPE1\nENCODING:USASCII\nCHARSET:1252\nCOMPRESSION:NONE\nOLDFILEUID:NONE\nNEWFILEUID:NONE\n";
		return ($oNode === null ? $sHeader : '') . $this->saveOFXElement(($oNode === null ? $this->documentElement : $oNode), '');
	}

	private function saveOFXElement($oElement, $sDepth)
	{
		//Serialize Element.
		$sElement = "\n".$sDepth.'<'.$oElement->nodeName.'>';
		$sChildren = '';

		for ($oChild = $oElement->firstChild; $oChild != null; $oChild = $oChild->nextSibling)
		{
			if ($oChild->nodeType == XML_TEXT_NODE)
			{
				$sValue = trim($oChild->nodeValue);
				if (strlen($sValue) > 0) return $sElement . $sValue;
			}
			else $sElement .= $this->saveOFXElement($oChild, $sDepth.'  ');
		}

		$sElement .= "\n".$sDepth.'</'.$oElement->nodeName.'>';

		//Return data.
		return $sElement;
	}
}

?>