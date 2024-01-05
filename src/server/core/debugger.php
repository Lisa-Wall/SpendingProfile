<?
	/**
	 * @author Lisa Wall
	 * @date 2009-02-05
	 */
	define('DEBUG'  , 'debug');
	define('ERROR'  , 'error');
	define('WARNING', 'warning');
	define('MESSAGE', 'message');

	$DEBUG = '';
	$DEBUG_INDEX = 0;

	function debug($sMessage, $sResponse = null, $sType = DEBUG)
	{
		global $DEBUG, $DEBUG_INDEX;

		if (DEBUG_ENABLED) $DEBUG .= "<tr class='debug_".$sType."'><td>".($DEBUG_INDEX++)."</td><td><img src='icon_".($sType).".png'/></td><td class='debug_date'>".(date("Y-m-d H:i:s"))."</td><td>".$sMessage."</td></tr>\n";

		return (is_string($sResponse) ? str_replace ('%MESSAGE%', $sMessage, $sResponse) : $sResponse);
	}

	/**
	* Writes the current session debug information to the specified path using the username and session id to generate a unique
	* html file name within the specified path that will be uses throught the users's session. Subsequest session debug information
	* are appended to the end of the log file.
	* File name: DEBUG_PATH + sessionid + ".html"
	*
	* @param sPath a directory path where the log file is to be saved.
	* @param sUsername a name to identify the current session.
	*/
	function debugDump()
	{
		global $DEBUG;

		if (strlen($DEBUG) == 0) return;

		//Get the session id which is used to generate the file name.
		$sUsername  = (isset($_SESSION['username']) ? $_SESSION['username'] : "UNKNOWN");
		$sFilepath  = DEBUG_PATH . 'debug ('. $sUsername . ') (' .session_id() . ').html';

		//If file does not already exist then add head to it.
		if (!file_exists($sFilepath))
		{
			$sStyles = '<style>.debug{font-size: 10pt; font-family: arial; } .debug_error{color:red} .debug_warning{color: orange} .debug_message{color:green} .debug_debug{color: blue} .debug_date{white-space: nowrap}</style>';

			$DEBUG = '<html><head>' . $sStyles . "</head><body><table border='1' class='debug'>\n" . $DEBUG . "</table><br/><br/>\n";
		}
		else
		{
			$DEBUG = "<table border='1' class='debug'>\n" . $DEBUG . "</table><br/><br/>\n";
		}

		//Write the content to the file.
		file_put_contents($sFilepath, $DEBUG, FILE_APPEND);

		//Clear the written debug info.
		$DEBUG = '';
	}

?>