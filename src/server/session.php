<?

/**
 * Architecture note: All server side data are stored in the session class while the other class provide
 * functionallity that gets' it's data from the session class.
 *
 * @author Lisa Wall
 * @version 2.0
 */
class Session
{
	public $oDatabase = null;
	
	public $iLoginAttempts = 0;

	public $aUser = null;
	public $iUserId = 0;

	public $oHash = null;


	public $iDebit = 0;
	public $iCredit = 0;
	public $iTotal = 0;

	public $sOrderIn = 'DESC';
	public $sOrderBy = 'Date';

	public $sTo = '';
	public $sFrom = '';
	public $aTransactions = array();

	public $sFilter = '';
	public $sFilter_SQL = '';


	public $aPieGraphs = array();

	public $sBudgetTo = null;
	public $sBudgetFrom = null;
	public $sBudgetOrderIn = 'ASC';
	public $sBudgetOrderBy = 'Name';
	public $sBudgetAvgPeriod = null;
	public $bBudgetActive = '0';

	///////////////////////////////////////////////
	// TODO OOOOOO: make all the above and below part of a preference table to get and set.
	///////////////////////////////////////////////

	public $aAnalysis = array();
	public $aAnalysisMap = array();


	public $aImport = array();


	public $bDemo = false;

	/**
	 *
	 */
	public function __construct($bDemo = false)
	{
		$this->setDatabase($bDemo);
		$this->sTo = date('Y-m-t');
		$this->sFrom = date('Y-m-01');

		$this->sBudgetTo = date('Y-m-t');
		$this->sBudgetFrom = date('Y-m-01');
		$this->sBudgetAvgPeriod = '-3 month';

		$_SESSION['demo'] = $bDemo;
		$_SESSION['Session'] = $this;

		$this->aPieGraphs['vendorsFlat'] = true;
		$this->aPieGraphs['accountFlat'] = true;
		$this->aPieGraphs['categoriesFlat'] = true;

		$this->aAnalysis['To'] = date('Y-m-t');
		$this->aAnalysis['From'] = date('Y-m-01', strtotime('-3 month'));
		$this->aAnalysis['ViewBy'] = 'CATEGORY';
		$this->aAnalysis['GraphType'] = 'BAR';
		$this->aAnalysis['TotalExpenses'] = '1';
		$this->aAnalysis['TotalIncome'] = '1';
		$this->aAnalysis['Categories'] = '-1,-2,-3,-4';

		$this->aImport['GuessVendor'] = true;
		$this->aImport['FormatVendor'] = true;
		$this->aImport['FormatNotes'] = true;

		$this->bDemo = $bDemo;
	}

	public function setDatabase($bDemo = false)
	{
		$this->oDatabase = new Database(($bDemo ? SQL_HOST_DEMO : SQL_HOST), ($bDemo ? SQL_USERNAME_DEMO : SQL_USERNAME), ($bDemo ? SQL_PASSWORD_DEMO : SQL_PASSWORD), ($bDemo ? SQL_DATABASE_DEMO : SQL_DATABASE));
		return $this->oDatabase;
	}

	public function get($sKey, $sDefault = '')
	{
		return (isset($_SESSION[$sKey]) ? $_SESSION[$sKey] : $sDefault);
	}

	/**
	 * Sets the user and agent information into this session.
	 */
	public function setUser($aUser)
	{
		//Sets session variables.
		$this->aUser = $aUser;
		$this->iUserId = $aUser['Id'];

		//Setup user infomration in Session and Class.
		$this->updateUser();
	}

	public function updateUser()
	{
		$_SESSION['demo'] = $this->bDemo;
		$_SESSION['id'] = $this->aUser['Id'];
		$_SESSION['email'] = $this->aUser['Email'];
		$_SESSION['currency'] = $this->aUser['Currency'];
		$_SESSION['preference'] = (isset($this->aUser['Preference']) ? $this->aUser['Preference'] : '');

		//TODO: make this part of a preference component
		$this->aPieGraphs['vendorsFlat'] = $this->getPreference('vendorsFlat', '1') == '1';
		$this->aPieGraphs['accountFlat'] = $this->getPreference('accountFlat', '1') == '1';
		$this->aPieGraphs['categoriesFlat'] = $this->getPreference('categoriesFlat', '1') == '1';

		$this->aAnalysis['Categories'] = $this->getPreference('Graphs_Categories', '-1,-2,-3,-4');
	}

	/**
	 * Clear the user and agent information.
	 */
	public function logout()
	{
		//Sets session variables.
		$this->aUser      = null;
		$this->iUserId    = 0;
		$this->sUserEmail = null;

		//remove user infomration in Session and Class.
		unset($_SESSION['demo']);
		unset($_SESSION['id']);
		unset($_SESSION['email']);
		unset($_SESSION['currency']);
		unset($_SESSION['preference']);

		unset($_SESSION['Session']);
	}

	public function getPreference($sKey, $sDefault = null, $bToUpper = false)
	{
		if ($this->oHash == null) $this->oHash = new Hash($this->aUser['Preference']);
		return $this->oHash->get($sKey, $sDefault, $bToUpper);
	}

	public function setPreference($sKey, $sValue)
	{
		if ($this->oHash == null) $this->oHash = new Hash($this->aUser['Preference']);

		$this->oHash->set($sKey, $sValue);

		//Write the preference back into the user account.
		$bResult = $this->oDatabase->update('sql/users/update_preferences.sql', $this->iUserId, $this->oHash->toString());
		if ($bResult === false || $bResult === null) return error("Setting preference: $sPreference", false);

		return $sValue;
	}

	/**
	 * Internal serialization of the settings.
	 */
	public function __sleep()
	{
		$this->oDatabase->disconnect();
		$this->oDatabase = null;

		if ($this->oHash !== null) $this->aUser['Preference'] = $this->oHash->toString();

		$this->oHash = null;

		return array('aUser', 'iUserId', 'iLoginAttempts',
		'iDebit', 'iCredit', 'iTotal',
		'sTo', 'sFrom', 'sOrderIn', 'sOrderBy', 'aTransactions', 
		'sFilter', 'sFilter_SQL',
		'sBudgetTo', 'sBudgetFrom', 'sBudgetAvgPeriod',  'sBudgetOrderIn', 'sBudgetOrderBy', 'bBudgetActive', 
		'aPieGraphs',
		'aAnalysis', 'aAnalysisMap',
		'aImport',
		'bDemo'); 
		//array_keys(get_class_vars(get_class($this)));
	}

	/**
	 * Internal deserialization of the settings.
	 */
	public function __wakeup()
	{	
		$this->setDatabase($this->bDemo);
	}
}


$aAdministrators = array(1 /*'licac@live.ca'*/, 1165 /*'charbel.choueiri@live.ca'*/);

function authenticate($bAdministrator = false)
{
	global $oSession, $aAdministrators;
	return ($oSession->iUserId !== 0) && ($bAdministrator ? in_array($oSession->iUserId, $aAdministrators) : true);
}

function requestLogger($oRequest)
{
	if (!LOG_REQUESTS) return true;

	$sFile = 'logs/requests/'.date('Y-m-d').'.xml';

	$sUser = (isset($_SESSION['id']) ? $_SESSION['id'] : 'UNKNOWN');
	$sDate = date('Y-m-d H:i:s');

	$oCopy = $oRequest->cloneNode(false);
	$oCopy->setAttribute('__User', $sUser);
	$oCopy->setAttribute('__Datetime', $sDate);
	
	if ($oCopy->hasAttribute('Password')) $oCopy->setAttribute('Password', '********');
	if ($oCopy->hasAttribute('OldPassword')) $oCopy->setAttribute('OldPassword', '********');
	if ($oCopy->hasAttribute('NewPassword')) $oCopy->setAttribute('NewPassword', '********');


	$iTries = 0;
	$iMaxTries = 100;
	$oFile = fopen($sFile, 'a');

	if (!$oFile) return false;

	do
	{ 
		if ($iTries > 0) usleep(rand(1, 10000)); 
		$iTries++;

	} while (!flock($oFile, LOCK_EX) && $iTries <= $iMaxTries);

	if ($iTries == $iMaxTries)
	{
		fclose($oFile);
		return false;
	}

	fwrite($oFile, XML::save($oCopy)."\n");
	flock($oFile, LOCK_UN); 
	fclose($oFile);
	
	return true;
}

?>