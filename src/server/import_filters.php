<?
/**
 * The filter cash contains a list of all vendors, categoreis and accounts along with all matched values cached
 * to help speed the process.
 *
 * @author Lisa Wall
 * @date 2009-03-24
 */
class ImportFilter_Clean implements ImportFilterInterface
{
	private $sField;

	public function __construct($sField)
	{
		$this->sField = $sField;
	}

	public function apply(&$aTransaction, $oGuesser)
	{
		$sOriginalValue = $aTransaction[$this->sField];

		$aValue = array_map("Utility::capitalize", $oGuesser->split($sOriginalValue));
		$sValue = implode(' ', $aValue);

		$aTransaction[$this->sField] = $sValue;
		$aTransaction[$this->sField.'_Original'] = $sOriginalValue;
	}
}

class ImportFilter_GuessVendor implements ImportFilterInterface
{
	public function apply(&$aTransaction, $oGuesser)
	{
		if (!isset($aTransaction['Vendor_Original'])) $aTransaction['Vendor_Original'] = $aTransaction['Vendor'];

		$sNotes = $aTransaction['Notes'];
		$sVendor = $aTransaction['Vendor_Original'];

		$oGuesser->vendor((strlen($sVendor) == 0 ? $sNotes : $sVendor), $aTransaction);
	}
}

class ImportFilter_GuessRest implements ImportFilterInterface
{
	public function apply(&$aTransaction, $oGuesser)
	{
		if (!isset($aTransaction['Vendor_Original'])) $aTransaction['Vendor_Original'] = $aTransaction['Vendor'];

		$sNotes = $aTransaction['Notes'];
		$sVendor = $aTransaction['Vendor_Original'];

		$oGuesser->all((strlen($sVendor) == 0 ? $sNotes : $sVendor), $aTransaction);
	}
}

class ImportFilter_Duplicate implements ImportFilterInterface
{
	private $iUserId;
	private $oDatabase = null;

	public function __construct()
	{
		global $oSession;
		$this->iUserId = $oSession->iUserId;
		$this->oDatabase = $oSession->oDatabase;
	}

	public function apply(&$aTransaction, $oGuesser)
	{
		$sDate = $aTransaction['Date'];
		$sDebit = $aTransaction['Debit'];
		$sAmount = $aTransaction['Amount'];

		$aDuplicates = $this->oDatabase->selectRows("sql/import/duplicates.sql", $this->iUserId, $sDebit, $sAmount, $sDate);
		if ($aDuplicates === false || $aDuplicates === null || count($aDuplicates) == 0) return;

		$aIds = array();
		foreach ($aDuplicates as $aTransactions) $aIds[] = $aTransactions['Id'];

		$aTransaction['Duplicate'] = '1';
		$aTransaction['Duplicate_Ids'] = implode(',', $aIds);
		$aTransaction['__children'][] = array('__name'=>'Duplicates', '__children'=>$aDuplicates);
	}
}

?>