<?
define('OFX_PARSER_OPEN'   , 1);
define('OFX_PARSER_CLOSE'  , 2);
define('OFX_PARSER_TEXT'   , 3);
define('OFX_PARSER_COMMENT', 4);
define('OFX_PARSER_END'    , 5);

/**
 * @author Lisa Wall
 * @date 2009-03-28
 */
class OFXReader
{
	//OPEN    name        <NAME>
	//CLOSE   name        </NAME>
	//TEXT    value       VALUE
	//COMMENT value       <!-- VALUE -->

	private $iIndex;
	private $iLength;
	private $aBuffer;

	public function __construct($aBuffer)
	{
		$this->aBuffer = $aBuffer;
		$this->iIndex = 0;
		$this->iLength = strlen($aBuffer);
	}

	/**
	 * Reads all the header and puts the data in a map array. Read header until found \n\n. Expected format STRING:VALUE\n trimed.
	 */
	public function header()
	{
		$aHeader = array();

		$iChar = $this->skipSpace();
		while ($iChar != '<' && $iChar !== 0)
		{
			$sKey = $this->readUntil(':');
			$iChar = $this->read();
			$sValue = $this->readUntil("\n");

			$aHeader[$sKey] = $sValue;

			$iChar = $this->skipSpace();
		}

		return $aHeader;
	}

	/**
	 * Skip spaces until first character.
	 */
	public function next(&$sValue)
	{
		$iToken = OFX_PARSER_END;
		$iChar = $this->skipSpace();

		if ($iChar === 0)
		{
			$iValue = false;
		}
		else if ($iChar == '<')
		{
			$iNext = $this->peek(1);

			if ($iNext == '/')
			{
				$iToken = OFX_PARSER_CLOSE;
				$sValue = $this->readClose();
			}
			else if ($iNext == '!')
			{
				$iToken = OFX_PARSER_COMMENT;
				$sValue = $this->readComment();
			}
			else
			{
				$iToken = OFX_PARSER_OPEN;
				$sValue = $this->readOpen();
			}
		}
		else
		{
			$iToken = OFX_PARSER_TEXT;
			$sValue = $this->readText();
		}

		return ($sValue === false ? false : $iToken);
	}

	// <open> = '<' <name> '>'
	private function readOpen()
	{
		if ($this->read() != '<') return $this->error('Expecting <', false);
		$sValue = $this->readUntil('>');
		if ($this->read() != '>') return $this->error('Expecting <', false);

		return $sValue;
	}

	// <close> = '</' <name> '>'
	private function readClose()
	{
		if (!$this->readString('</')) return $this->error('Expecting </', false);
		$sValue = $this->readUntil('>');
		if ($this->read() != '>') return $this->error('Expecting <', false);

		return $sValue;
	}

	// <text> = <characters>+
	private function readText()
	{
		return $this->readUntil('<');
	}

	// <comment> = '<!--' <characters>* '-->'
	private function readComment()
	{
		if (!$this->readString('<!--')) return $this->error('Expecting <!--', false);
		$sValue = $this->readUntil('-->');
		if (!$this->readString('-->')) return $this->error('Expecting -->', false);

		return $sValue;
	}

	private function skipSpace()
	{
		$iChar = $this->peek();
		while (($iChar == " " || $iChar == "\t" || $iChar == "\n" || $iChar == "\r") && ($iChar !== 0))
		{
			$iChar = $this->read();
			$iChar = $this->peek();
		}

		return $iChar;
	}

	private function peek($iIndex = 0)
	{
		return ($this->iIndex + $iIndex < $this->iLength ? $this->aBuffer[$this->iIndex + $iIndex] : 0);
	}

	private function peekString($sString)
	{
		for ($i = 0; $i < strlen($sString); $i++) if ($this->peek($i) != $sString[$i]) return false;
		return true;
	}

	private function read()
	{
		$iChar = 0;
		if ($this->iIndex < $this->iLength)
		{
			$iChar = $this->aBuffer[$this->iIndex++];
			if ($iChar == "\n")
			{
				$this->iLine++;
				$this->iColumn = 1;
			}
			else $this->iColumn++;
		}

		return $iChar;
	}

	private function readUntil($sString)
	{
		$sValue = '';
		$iStart = $sString[0];

		while ( ($iChar = $this->peek()) !== 0)
		{
			if ($iChar == $iStart && $this->peekString($sString)) break;
			$sValue .= $this->read();
		}

		return ($iChar === 0 ? false : trim($sValue));
	}

	private function readString($sString)
	{
		for ($i = 0; $i < strlen($sString); $i++) if ($this->read() != $sString[$i]) return false;
		return true;
	}

	private function error($sMessage, $oReturn)
	{
		echo "ERROR: $sMessage (line {$this->iLine}, column {$this->iColumn})" ;
		return $oReturn;
	}
}

?>