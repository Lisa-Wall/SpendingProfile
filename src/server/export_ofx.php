<?

/**
 * @auther Lisa Wall
 * @version 3.0
 * @date 2009-03-04
 */
class Export_OFX extends AbstractExport
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
		$aColumns  = $this->getColumns($aTransactions, $oAttributes);
		$oDocument = $this->getDocument($oAttributes);

		//Travers the transactions and add them to the document.
		foreach ($aTransactions as $aTransaction) $this->addTransaction($oDocument, $aTransaction);

		$iVersion = XML::getAttribute($oAttributes, 'Version', "2");

		return ($iVersion == '1' ? $this->outputV1($oDocument, $aColumns) : $this->outputV2($oDocument, $aColumns));
	}

	public function extension($oAttributes = null)
	{
		return 'ofx';
	}

	public function outputType()
	{
		return RESPONSE_XML;
	}

	private function getDocument($oAttributes)
	{
		global $oSession;

		//Get transactions period.
		$sDateEnd = XML::getAttribute($oAttributes, 'DateEnd', date('Y-m-d'));
		$sDateStart = XML::getAttribute($oAttributes, 'DateStart', date('Y-m-d'));
		$sCurrency = XML::getAttribute($oAttributes, 'Currency', 'USD');

		$oDocument = new OFXDocument('1.0');
		$oDocument->formatOutput = true;

//TODO: look into BANKID element to check possible value types.
//TODO: check for amount value if it has to be negative id debit or if the debit type is enough.

		if (!$oDocument->load('content/ofx.xml')) return error('Unable to open ofx file.', null);

		//Set Server Time.
		XPath::set($oDocument, 'OFX/SIGNONMSGSRSV1/SONRS/DTSERVER/#', date('c'));

		//Set Start and End dates of the transaction list.
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKTRANLIST/DTEND/#', date('c', strtotime($sDateEnd)));
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKTRANLIST/DTSTART/#', date('c', strtotime($sDateStart)));

		//Set the currency.
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/CURDEF/#', $sCurrency);

		//Set SpendingProfile information.
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKACCTFROM/BANKID/#', md5('SpendingProfile'));
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKACCTFROM/ACCTID/#', $oSession->iUserId);
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKACCTFROM/ACCTTYPE/#', 'CHECKING');

		//Set Ballence.
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/LEDGERBAL/BALAMT/#', '0.00');
		XPath::set($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/LEDGERBAL/DTASOF/#', date('c'));

		//Return document.
		return $oDocument;
	}

	private function addTransaction($oDocument, $aTransaction)
	{
		$sDebit = $aTransaction['Debit'];

		//Get the element statements element.
		$oStatements = XPath::get($oDocument, 'OFX/BANKMSGSRSV1/STMTTRNRS/STMTRS/BANKTRANLIST');

		//Create transaction element <STMTTRN>
		$oStatement = $oStatements->appendChild($oDocument->createElement('STMTTRN'));

		//Append type, date, amount, id, vencor and notes.
		$oStatement->appendChild($oDocument->createElement('TRNTYPE'))->nodeValue = ($sDebit == '1' ? 'DEBIT' : 'CREDIT');
		$oStatement->appendChild($oDocument->createElement('DTPOSTED'))->nodeValue = date('c', strtotime($aTransaction['Date']));
		$oStatement->appendChild($oDocument->createElement('TRNAMT'))->nodeValue = $aTransaction['Amount'];
		$oStatement->appendChild($oDocument->createElement('FITID'))->nodeValue = $aTransaction['Id'];
		$oStatement->appendChild($oDocument->createElement('NAME'))->nodeValue = $aTransaction['Vendor'];
		$oStatement->appendChild($oDocument->createElement('MEMO'))->nodeValue = $aTransaction['Notes'];

		return true;
	}

	private function outputV1($oDocument, $aColumns)
	{
		return $oDocument->saveOFX();
	}

	private function outputV2($oDocument, $aColumns)
	{
		$sHeader = '<?OFX OFXHEADER="200" VERSION="211" SECURITY="NONE" OLDFILEUID="NONE" NEWFILEUID="NONE" ?>'."\n";
		return $sHeader . $oDocument->saveXML($oDocument->documentElement);
	}
}

?>