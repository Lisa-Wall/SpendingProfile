<?
/**
 * @author Lisa Wall
 * @date 2009-03-24
 */
class Import_CSV implements ImportInterface
{
	private $aColumns = array('id'=>'Id', 'vendor'=>'Vendor', 'account'=>'Account', 'category'=>'Category', 'date'=>'Date', 'debit'=>'Debit', 'fixed'=>'Fixed', 'amount'=>'Amount', 'notes'=>'Notes');

	public function import($sFilePath, $oAttributes = null)
	{
		$sDelimiter = XML::getAttribute($oAttributes, 'Delimitor', ',');

		//Open the file.
		$aRows = file($sFilePath, FILE_TEXT | FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
		if ($aRows === false || count($aRows) == 0) return warning('Unable to read import file.', false);

		return $this->parse($aRows, $sDelimiter);
	}

	public function header($aHeader)
	{
		$aColumns = array();

		foreach($aHeader as $sColumn)
		{
			$sColumn = strtolower(trim($sColumn));
			if (!array_key_exists($sColumn, $this->aColumns)) return false;

			$aColumns[] = $this->aColumns[$sColumn];
		}

		return $aColumns;
	}

	function parse($aLines, $sDelimiter = ',')
	{
		$sExpression = "/$sDelimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";

		//Read the header columns and validate them.
		$aHeader = explode($sDelimiter, array_shift($aLines));
		if ( ($aHeader = $this->header($aHeader)) === false) return warning('Invalid header names.', false);

		$aTable = array();
		$iHeader = count($aHeader);

		foreach ($aLines as $sLine)
		{
			if (empty($sLine)) continue;

			$aLine = preg_split($sExpression, trim($sLine));
			$aLine = preg_replace('/^"(.*)"$/s', "$1", $aLine);
			$iLine = count($aLine);

			$aRow = array();
			foreach ($aHeader as $iIndex=>$sColumn) $aRow[$sColumn] = ($iIndex < $iLine ? $aLine[$iIndex] : '');

			$aTable[] = $aRow;;
		}

		return $aTable;
	}

}

?>