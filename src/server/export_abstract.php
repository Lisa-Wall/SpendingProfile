<?
/**
 * @author Lisa Wall
 * @date 2009-03-24
 */
abstract class AbstractExport
{
	abstract public function export($aTransactions, $oAttributes = null);
	abstract public function extension($oAttributes = null);
	abstract public function outputType();

	/**
	 * Return the columns to select in the specified order.
	 */
	protected function getColumns($aTransactions, $oAttributes)
	{
		$aColumns = array();
		$aTransaction = (count($aTransactions) > 0 ? $aTransactions[0] : null);

		$bAllColumns = XML::getAttributeBool($oAttributes, 'All', true);

		//If Get all columns then create an array of all the columns in the transaction list.
		if ($bAllColumns)
		{
			if ($aTransactions != null) $aColumns = array_keys($aTransaction);
		}
		else
		{
			//Travers the attribute children to read the columns.
			for ($oColumn = $oAttributes->firstChild; $oColumn != null; $oColumn = $oColumn->nextSibling)
			{
				//If the key exists then add it to the column list.
				if ($aTransaction == null || array_key_exists($oColumn->nodeValue, $aTransaction)) array_push($aColumns, $oColumn->nodeValue);
			}
		}

		return $aColumns;
	}
}

?>