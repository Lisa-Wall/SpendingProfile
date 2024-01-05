<?
/**
 * @author Lisa Wall
 * @date 2009-03-24
 */
class Import_OFX implements ImportInterface
{
	private $aColumns = array('FITID'=>'Id', 'NAME'=>'Vendor', 'DTPOSTED'=>'Date', 'TRNAMT'=>'Amount', 'MEMO'=>'Notes');

	public function import($sFilePath, $oAttributes = null)
	{
		//Read file content.
		$oDocument = new OFXDocument('1.0');
		if ( ($oDocument->loadOFXFile($sFilePath)) === false) return false;

		//Read and return transaction array;
		return $this->expand($oDocument);
	}

	public function expand($oDocument)
	{
		$aTransactions = array();

		//Get the statement element where all the transactions are housed.
		$oTransactions = XPath::find($oDocument, 'BANKTRANLIST');
		
		if ($oTransactions == null) return false;

		//Get the account name.
		$sAccount = '';

		//For each transaction.
		for ($oTransaction = $oTransactions->firstChild; $oTransaction != null; $oTransaction = $oTransaction->nextSibling)
		{
			if ($oTransaction->nodeName != 'STMTTRN') continue;

			$aTransaction = array();

			//For each element in statement.
			for ($oField = $oTransaction->firstChild; $oField != null; $oField = $oField->nextSibling)
			{
				$sValue = trim($oField->nodeValue);
				switch($oField->nodeName)
				{
				case 'FITID':     $aTransaction['Id']     = $sValue; break;
				case 'NAME':      $aTransaction['Vendor'] = $sValue; break;
				case 'MEMO':      $aTransaction['Notes']  = $sValue; break;
				case 'DTPOSTED':
					$aDateTime = date_parse($sValue);
					$aTransaction['Date']   = date('Y-m-d', mktime(0, 0, 0, $aDateTime['month'], $aDateTime['day'], $aDateTime['year']));
					break;
				case 'TRNAMT':
					$aTransaction['Amount'] = ($sValue < 0 ? -$sValue : $sValue);
					$aTransaction['Debit']  = ($sValue < 0 ? '1' : '0');
					break;
				}
			}

			//Append row to rows array.
			$aTransactions[] = $aTransaction;
		}

		return $aTransactions;
	}
}

?>