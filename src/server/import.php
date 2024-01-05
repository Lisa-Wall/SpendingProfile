<?
/**
 * @author Lisa Wall
 * @date 2009-03-24
 */
class Import
{
	private $iUserId;
	private $oSession = null;
	private $oDatabase = null;

	public function __construct()
	{
		global $oSession;

		$this->iUserId = $oSession->iUserId;
		$this->oSession = $oSession;
		$this->oDatabase = $oSession->oDatabase;
	}

	public function preview($oAttributes)
	{
		//Get the uploaded file name located on the server.
		$sFormat = (isset($_SESSION['EXTENSION']) ? $_SESSION['EXTENSION'] : '');
		$sFilePath = 'content/import/'.$this->iUserId;
		if (!file_exists($sFilePath)) return error('Loaded file was not found.', RESPONSE_INVALID_ARGUMENTS);

		//Get the import implementation.
		$oImport = Import::getImplementation($sFormat);
		if ($oImport === null) return error('Unsupported file type: '.$sFormat, RESPONSE_INVALID_ARGUMENTS);

		//Parser the import information into a transaction array.
		$aTransactions = $oImport->import($sFilePath, $oAttributes);
		if ($aTransactions === false) return error('Unable to open file or invalid file: '.$sFormat, RESPONSE_INVALID_ARGUMENTS);

		//Get all the filters.
		$aFilters = $this->getFilters();
		$oGuesser = new Guesser();

		//For every transaction apply the filter.
		foreach ($aTransactions as &$aTransaction)
		{
			//Add the basic required columns. Date and amount are requried.
			if (!array_key_exists('Notes', $aTransaction)) $aTransaction['Notes'] = '';
			if (!array_key_exists('Vendor', $aTransaction)) $aTransaction['Vendor'] = '';

			$aTransaction['__children'] = array();

			//Apply all ther filters to the transaction.
			foreach ($aFilters as $oFilter) $oFilter->apply($aTransaction, $oGuesser);

			if (!array_key_exists('Type', $aTransaction)) $aTransaction['Type'] = '0';
			if (!array_key_exists('Account', $aTransaction)) $aTransaction['Account'] = '';
			if (!array_key_exists('Category', $aTransaction)) $aTransaction['Category'] = '';
			if (!array_key_exists('Duplicate', $aTransaction)) $aTransaction['Duplicate'] = '';
		}

		//Return transactions to user.
		return '<Import.import>'.XML::fromArrayTree('Transaction', $aTransactions).'</Import.import>';
	}

	public function import($oTransactions)
	{
		$iIndex = 1;
		$sResults = '';

		//Traverse the list of transactions and add them one at a time.
		for ($oTransaction = $oTransactions->firstChild; $oTransaction != null; $oTransaction = $oTransaction->nextSibling)
		{
			$bResult = Import::add($this->iUserId, $oTransaction, $this->oDatabase);

			if ($bResult === true) $sResults .= XML::serialize(true, 'Transaction', 'Index', $iIndex++);
			else                   $sResults .= XML::serialize(true, 'Error', 'Index', $iIndex++, 'Message', $bResult);
		}

		//TODO: delete uploaded file and the extension in the session.
		unset($_SESSION['EXTENSION']);
		unlink('content/import/'.$this->iUserId);

		return '<Import.import Type="OK">'.$sResults.'</Import.import>';
	}

	public function getPreference()
	{
		$bGuessVendor = ($this->oSession->aImport['GuessVendor'] ? "true" : "false");
		$bFormatNotes = ($this->oSession->aImport['FormatNotes'] ? "true" : "false");
		$bFormatVendor = ($this->oSession->aImport['FormatVendor'] ? "true" : "false");
		return XML::serialize(true, "Import.getPreference", "GuessVendor", $bGuessVendor, "FormatVendor", $bFormatVendor, "FormatNotes", $bFormatNotes);
	}

	public function setPreference($bGuessVendor, $bFormatVendor, $bFormatNotes)
	{
		$this->oSession->aImport['GuessVendor'] = $bGuessVendor;
		$this->oSession->aImport['FormatVendor'] = $bFormatVendor;
		$this->oSession->aImport['FormatNotes'] = $bFormatNotes;
		return $this->getPreference();
	}


	public static function getImplementation($sFormat)
	{
		$oClass = null;
		$sFormat = strtolower($sFormat);

		if     ($sFormat == 'csv')  $oClass = new Import_CSV();
		else if($sFormat == 'ofx')  $oClass = new Import_OFX();
		else if($sFormat == 'qfx')  $oClass = new Import_OFX();

		return $oClass;
	}

	public function getFilters()
	{
		$aFilterClasses = array();

		if ($this->oSession->aImport['FormatNotes']) $aFilterClasses[] = new ImportFilter_Clean('Notes');
		if ($this->oSession->aImport['FormatVendor']) $aFilterClasses[] = new ImportFilter_Clean('Vendor');
		if ($this->oSession->aImport['GuessVendor']) $aFilterClasses[] = new ImportFilter_GuessVendor();

		$aFilterClasses[] = new ImportFilter_Duplicate();
		$aFilterClasses[] = new ImportFilter_GuessRest();

		return $aFilterClasses;
	}

	public static function add($iUserId, $oTransaction, $oDatabase)
	{
		$aValues = array();
		$sEnteredOn= date('Y-m-d H:i:s');
		$sSchema = '<Transaction Account="TYPE:STRING;MIN:0;MAX:64" Vendor="TYPE:STRING;MIN:0;MAX:64" Category="TYPE:STRING;MIN:1;MAX:128" Date="TYPE:DATE" Debit="TYPE:BOOLEAN;RETURN:STRING_INT" Fixed="TYPE:BOOLEAN;RETURN:STRING_INT" Amount="TYPE:FLOAT;MIN:0;MAX:9999999999.99" Notes="TYPE:STRING;MAX:128" />';

		$sResult = Validate::xml($sSchema, $oTransaction, $aValues);
		if ($sResult !== true) return $sResult;

		$iVendorId   = Vendor::getId($aValues['Vendor'], $bVendorCreated);
		$iAccountId  = Account::getId($aValues['Account'], $bAccountCreated);
		$iCategoryId = Category::getId($aValues['Category'], $bCategoryCreated);

		if ($iVendorId == 0 || $iAccountId == 0 || $iCategoryId == 0) return 'Getting tag id';

		$sId = $oDatabase->insert('sql/transactions/add.sql', 'transactions', $iUserId, $iVendorId, $iAccountId, $iCategoryId, $aValues['Date'], $aValues['Debit'], $aValues['Fixed'], $aValues['Amount'], $aValues['Notes'], $sEnteredOn);
		if ($sId === null || $sId === false) return 'Adding transactoin';

		return true;
	}
}

interface ImportInterface
{
	public function import($sFilePath, $oAttributes = null);
}

interface ImportFilterInterface
{
	public function apply(&$aTransaction, $oGuesser);
}

?>