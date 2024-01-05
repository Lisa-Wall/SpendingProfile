<?
/**
 * @author Lisa Wall
 * @date 2009-03-24
 */
class Export
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

	public function export($sFormat, $oAttributes)
	{
		$oExport = Export::getImplementation($sFormat);

		$sFileName = XML::getAttribute($oAttributes, 'FileName', null);

		//if ($sFileName == null) setOutputType($oExport->outputType());
		//else                    setOutputType($sFileName.'.'.$oExport->extension());

		setOutputType('SpendingProfile_'.$this->oSession->sFrom.'_to_'.$this->oSession->sTo.'.'.$oExport->extension());

//TODO: append the start and end date of selected transactions and indicate if they have been filtered.

		$oAttributes->setAttribute('DateEnd', $this->oSession->sTo);
		$oAttributes->setAttribute('DateStart', $this->oSession->sFrom);

//TODO: get the list from the database again because it may have changed from deletion for modification.

		return $oExport->export($this->oSession->aTransactions, $oAttributes);
	}

	/**
	 * Returns the specific implementation of the ExportImport class for the specified type.
	 * Any classes that extend this class must be assosiated with a string type, ie. ofx, xml, csv and
	 * added to this function to provide access to the implementation.
	 *
	 * @param $sType A string, the type of the export (ex: OFX, CSV, PDF, etc.)
	 */
	public static function getImplementation($sFormat)
	{
		$oClass = null;
		$sFormat = strtolower($sFormat);

		if     ($sFormat == 'csv')  $oClass = new Export_CSV();
		else if($sFormat == 'ofx')  $oClass = new Export_OFX();

		return $oClass;
	}
}


?>