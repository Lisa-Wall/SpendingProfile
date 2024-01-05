<?
/**
 * @author Lisa Wall
 * @date 2009-02-05
 */
class LogEvent
{
	public $sLevel;
	public $sDateTime;
	public $sSource;
	public $sUsername;
	public $sSessionId;
	public $sComputer;
	public $sIPAddress;
	public $sMessage;
	public $sFileName;

	public $aCallStack;

	public $bIsLoop = false;

	private $aFunctionFilter = array('log', 'error', 'debug', 'message', 'warning', 'exceptionHandler', 'errorHandler');

	public function __construct($sLevel, $sMessage, $aCallStack)
	{
		$this->sLevel = $sLevel;
		$this->sMessage = $sMessage;
		$this->aCallStack = $this->cleanCallStack($aCallStack);

		$this->sDateTime = date('Y-m-d H:i:s');
		$this->sSource = $this->findSource($aCallStack);

		$this->sUsername = (isset($_SESSION['email']) ? $_SESSION['email'] : 'UNKNOWN');

		$this->sSessionId = session_id();
		$this->sComputer  = (isset($_SERVER['COMPUTERNAME']) ? $_SERVER['COMPUTERNAME'] : 'UNKNOWN');
		$this->sIPAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN');

		$this->sFileName = Utility::udate('Y-m-d H-i-s') . ' (' . $sLevel .').xml';

		$this->bIsLoop = $this->isLooping($aCallStack);
	}

	private function findSource($aCallStack)
	{
		$iCount = count($aCallStack);
		for ($i = 1; $i < $iCount; $i++)
		{
			$sFunction = $aCallStack[$i]['function'];
			if (!in_array($sFunction, $this->aFunctionFilter)) return $sFunction;
		}
		return ($iCount == 0 ? 'UNKNOWN' :  $aCallStack[$iCount-1]['function']);
	}

	private function isLooping($aCallStack)
	{
		$iLoop = 0;
		for ($i = 1; $i < count($aCallStack); $i++)
		{
			$sFunction = $aCallStack[$i]['function'];
			if (in_array($sFunction, $this->aFunctionFilter)) $iLoop++;
		}
		return ($iLoop > 9);
	}

	private function cleanCallStack($aCallStack)
	{
		for ($i = 0; $i < count($aCallStack); $i++)
		{
			$aCall = $aCallStack[$i];

			if (!isset($aCall['type'])) $aCall['type'] = '';
			if (!isset($aCall['file'])) $aCall['file'] = '';
			if (!isset($aCall['line'])) $aCall['line'] = '';
			if (!isset($aCall['class'])) $aCall['class'] = '';

			if      ($aCall['function'] == 'login') $aCall['args'][1] = '********';
			else if ($aCall['function'] == 'mysql_connect') $aCall['args'][2] = '********';

			$aCall['file'] = str_replace(ROOT_PATH, '', str_replace('\\', '/', $aCall['file']));
			$aCall['arguments'] = $this->serializeArguments($aCall['args']);

			if (strstr(strtolower($aCall['arguments']), 'password') !== false) $aCall['arguments'] = '**** value contains password ***';

			$aCallStack[$i] = $aCall;
		}
		return $aCallStack;
	}

	public function serialize()
	{
		$sCallStack = $this->serializeCallStack($this->aCallStack);
		$sEvent = XML::serialize(false, 'Event', 'Level', $this->sLevel, 'DateTime', $this->sDateTime, 'Source', $this->sSource, 'User', $this->sUsername, 'SessionId', $this->sSessionId, 'Computer', $this->sComputer, 'IPAddress', $this->sIPAddress, 'Message', $this->sMessage, 'FileName', $this->sFileName);
		return $sEvent . $sCallStack . '</Event>';
	}

	private function serializeCallStack($aCallStack)
	{
		$sCalls = '';
		for ($i = 0; $i < count($aCallStack); $i++)
		{
			$aCall = $aCallStack[$i];
			$sCalls .= XML::serialize(true, 'Call', 'Class', $aCall['class'], 'Type', $aCall['type'], 'Function', $aCall['function'], 'Arguments', $aCall['arguments'], 'File', $aCall['file'], 'Line', $aCall['line']);
		}

		return $sCalls;
	}

	private function serializeArguments($aArguments)
	{
		$sArguments = "";

		if (!is_array($aArguments)) return $sArguments;
		
		foreach ($aArguments as $iIndex => $oArguments)
		{
			if      (is_object($oArguments))  $sArgument = get_class($oArguments);
			else if (is_numeric($oArguments)) $sArgument = $oArguments;
			else if (is_array($oArguments))   $sArgument = 'Array';
			else if (is_bool($oArguments))    $sArgument = ($oArguments ? 'true' : 'false');
			else                              $sArgument = "<i>'".XML::clean($oArguments)."'</i>";

			$sArguments .= ($iIndex == 0 ? '' : ' , ') . $sArgument;
		}

		return $sArguments;
	}

	public function populate($sTemplate, $sCallTemplate)
	{
		$sCallTemplate = $this->populateCalls($sCallTemplate);

		$sTemplate = str_replace('%MESSAGE%'  , $this->sMessage   , $sTemplate);
		$sTemplate = str_replace('%LEVEL%'    , $this->sLevel     , $sTemplate);
		$sTemplate = str_replace('%DATETIME%' , $this->sDateTime  , $sTemplate);
		$sTemplate = str_replace('%SOURCE%'   , $this->sSource    , $sTemplate);
		$sTemplate = str_replace('%USERNAME%' , $this->sUsername  , $sTemplate);
		$sTemplate = str_replace('%SESSIONID%', $this->sSessionId , $sTemplate);
		$sTemplate = str_replace('%COMPUTER%' , $this->sComputer  , $sTemplate);
		$sTemplate = str_replace('%IPADDRESS%', $this->sIPAddress , $sTemplate);
		$sTemplate = str_replace('%FILENAME%' , $this->sFileName  , $sTemplate);
		$sTemplate = str_replace('%CALLSTACK%', $sCallTemplate    , $sTemplate);

		return $sTemplate;
	}

	public function populateCalls($sTemplate)
	{
		$sCalls = '';
		for ($i = 0; $i < count($this->aCallStack); $i++)
		{
			$aCall = $this->aCallStack[$i];

			$sCallTemplate = str_replace('%CLASS%'    , $aCall['class']    , $sTemplate);
			$sCallTemplate = str_replace('%TYPE%'     , $aCall['type']     , $sCallTemplate);
			$sCallTemplate = str_replace('%FUNCTION%' , $aCall['function'] , $sCallTemplate);
			$sCallTemplate = str_replace('%ARGUMENTS%', $aCall['arguments'], $sCallTemplate);
			$sCallTemplate = str_replace('%FILE%'     , $aCall['file']     , $sCallTemplate);
			$sCallTemplate = str_replace('%LINE%'     , $aCall['line']     , $sCallTemplate);

			$sCalls .= $sCallTemplate;
		}

		return $sCalls;
	}
}


function error($sMessage, $sResponse = null)
{
//echo $sMessage;
	logger(ERROR, $sMessage);
	return debug($sMessage, $sResponse, ERROR);
}

function message($sMessage, $sResponse = null)
{
	logger(MESSAGE, $sMessage);
	return debug($sMessage, $sResponse, MESSAGE);
}

function warning($sMessage, $sResponse = null)
{
	logger(WARNING, $sMessage);
	return debug($sMessage, $sResponse, WARNING);
}

function errorHandler($iNumber, $sMessage, $sFile, $sLine)
{
	global $bEnableErrorHandlers;
	if ($bEnableErrorHandlers) error("PHP[ERROR $iNumber] '$sMessage' [$sFile : $sLine]");
}

function exceptionHandler($oException)
{
	global $bEnableErrorHandlers;
	if ($bEnableErrorHandlers) error('PHP[EXCEPTION]: ' . $oException->getMessage());
}

$bEnableErrorHandlers = true;
function enableErrorHandlers()
{
	debug("Enabling exception and error handlers.");

	set_exception_handler('exceptionHandler');
	set_error_handler('errorHandler');
}



function logger($sLevel, $sMessage)
{
return;
	//Get the call stack.
	$aCallStack = debug_backtrace();

	//Generate the event.
	$oEvent = new LogEvent($sLevel, $sMessage, $aCallStack);

	//To avoid infinite loops.
	if ($oEvent->bIsLoop) return debug('Infinite loop..', null, WARNING);

	//Write the event to file.
	if (LOG_EVENT) file_put_contents(LOG_PATH.$oEvent->sFileName, $oEvent->serialize(), FILE_APPEND);

	//If email notification is on then email event.
	if ((LOG_NOTIFY || LOG_HTML) && $sLevel == ERROR)
	{
		$sTemplate     = file_get_contents('content/logger/event_email.html');
		$sCallTemplate = file_get_contents('content/logger/call_template.html');
		$sEmail        = $oEvent->populate($sTemplate, $sCallTemplate);

		//Log to file.
		if (LOG_HTML) file_put_contents(LOG_PATH_HTML.$oEvent->sFileName.'.html', $sEmail, FILE_APPEND);

		//Notify by email
		if (LOG_NOTIFY) Utility::email(explode(';', LOG_NOTIFY_EMAILS), $oEvent->sMessage, $sEmail, array('From' => 'Spending Profile Errors <support@SpendingProfile.com>'));
	}
}

?>