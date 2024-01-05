<?
/**
 * @auther Lisa Wall
 * @date 2009-03-04
 */
class Export_CSV extends AbstractExport
{
	/**
	 * The export function takes an array of transactions and returns a string
	 * representation of these transactions in the requested format.
	 *
	 * @param $aTransactions  An array of transactions to export.
	 * @param $oAttributes    an xml element of columns to export and attributes.
	 *
	 * @return $sTransactions A string representation of the transactions.
	 */
	public function export($aTransactions, $oAttributes = null)
	{
		$aColumns   = $this->getColumns($aTransactions, $oAttributes);
		$sRows      = $this->getHeader($aColumns, $oAttributes);
		$sNewLine   = $this->getNewLine($oAttributes);
		$sDelimiter = $this->getDelimiter($oAttributes);

		$bFirstLine = (strlen($sRows) == 0);

		foreach ($aTransactions as $aTransaction)
		{
			$sRow = '';
			$bFirstColumn = true;
			foreach ($aColumns as $sColumn)
			{
				$sRow .= ($bFirstColumn ? '' : $sDelimiter) . $this->clean($aTransaction[$sColumn], $sDelimiter);
				$bFirstColumn = false;
			}

			$sRows .= ($bFirstLine ? '' : $sNewLine) . $sRow;
			$bFirstLine = false;
		}

		return $sRows;
	}

	public function extension($oAttributes = null)
	{
		return 'csv';
	}

	public function outputType()
	{
		return RESPONSE_TEXT;
	}

	/**
	 * Check if the delimiter is within the value. If so then places quotes around the value.
	 */
	private function clean($sValue, $sDelimiter)
	{
		if (strpos($sValue, $sDelimiter) !== false) $sValue = '"' . str_replace('"', '\"', $sValue) . '"';
		return $sValue;
	}

	/**
	 * Check if the delimiter is specified, otherwise uses default.
	 */
	private function getDelimiter($oAttributes)
	{
		$sDelimiter = XML::getAttribute($oAttributes, 'Delimiter', ',');
		$sDelimiter = str_replace('\n', "\n", $sDelimiter);
		$sDelimiter = str_replace('\r', "\r", $sDelimiter);
		$sDelimiter = str_replace('\t', "\t", $sDelimiter);

		return $sDelimiter;
	}

	/**
	 * Check if the new line attribute is specified, otherwise uses default.
	 */
	private function getNewLine($oAttributes)
	{
		$sNewLine = XML::getAttribute($oAttributes, 'NewLine', "\n");
		$sNewLine = str_replace('\n', "\n", $sNewLine);
		$sNewLine = str_replace('\r', "\r", $sNewLine);

		return $sNewLine;
	}

	/**
	 * Checks if it needs to output the column header names
	 */
	private function getHeader($aColumns, $oAttributes)
	{
		$sHeader = '';
		$bFirstColumn = true;
		$bOutputColumns = XML::getAttributeBool($oAttributes, 'Header', true);

		if (!$bOutputColumns) return $sHeader;

		foreach ($aColumns as $sColumn)
		{
			$sHeader .= ($bFirstColumn ? '' : ',') . $sColumn;
			$bFirstColumn = false;
		}

		return $sHeader;
	}
}

?>