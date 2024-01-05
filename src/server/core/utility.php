<?
/**
 * @author Lisa Wall
 * @date 2007-09-01
 */
class Utility
{
	public static $iMax = 0;
	public static $aKeys = null;
	public static function arrayMax($aaArray, $aKeys = null)
	{
		self::$aKeys = $aKeys;
		self::$iMax = 0;
		array_walk_recursive($aaArray, "Utility::isMax");
		return self::$iMax;
	}

	public static function isMax($iValue, $sKey)
	{
		if (self::$aKeys != null)
		{
			if (in_array($sKey, self::$aKeys) && $iValue > self::$iMax) self::$iMax = $iValue;
		}
		else if ($iValue > self::$iMax) self::$iMax = $iValue;
	}

	/**
	 * Returns the first value found in the array. This is required in the case the array
	 * does not contain numeric indexes but rather a map.
	 *
	 * @param aArray any array.
	 * @return if the array is empty returns null, otherwise returns the value of the first index.
	 */
	public static function getArrayFirstValue($aArray)
	{
		$aArray = array_values($aArray);
		return (count($aArray) > 0 ? $aArray[0] : null);
	}


	public static function isArrayOrdered($aArray)
	{
		if (count($aArray) != 0)
		{
			$iLastValue = $aArray[0];
			foreach ($aArray as $iValue)
			{
				if ($iValue < $iLastValue) return false;
				$iLastValue = $iValue;
			}
		}

		return true;
	}

	public static function inArrayIgnoreCase($needle, $haystack)
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}

	/**
	 * Maps the given rows values to the specified array map.
	 *
	 * @param aMap an array map of [name]=>[friendly name].
	 * @param aArray an array of [name]=>[value]
	 * @return an array of [friendly name]=>[value].
	 */
	public static function mapArray($aMap, $aArray)
	{
		$aAttributes = array();
		foreach($aMap as $sKey => $sMap) if (array_key_exists($sKey, $aArray)) $aAttributes[$sMap] = $aArray[$sKey][0];

		return $aAttributes;
	}

	public static function fileExtension($sFile)
	{
		return (($iPosition = strrpos($sFile, '.')) === false ? null : substr($sFile, $iPosition+1));
	}

	public static function createMap($aaArray, $sKey, $sValue)
	{
		$aMap = array();
		foreach ($aaArray as $aArray) $aMap[$aArray[$sKey]] = $aArray[$sValue];

		return $aMap;
	}

	public static function replaceStrings($sString, $aArray)
	{
		foreach ($aArray as $sKey=>$sValue) $sString = str_replace($sKey, $sValue, $sString);
		return $sString;
	}

	public static function splitEmail($sEmails, $sDefault = null)
	{
		//If email is empty then return the default.
		if ($sEmails === null || $sEmails === false || strlen($sEmails) == 0) return ($sDefault === null ? null : Utility::splitEmail($sDefault));

		//Replace new lines with space, and insure there is only one space.
		$sEmails = str_replace("\n", ' ', $sEmails);
		$sEmails = preg_replace('([ ]+)', ' ', $sEmails);
		$aEmails = split((strpos($sEmails, ";") === false ? ' ' : ';'), $sEmails);

		array_walk($aEmails, 'trim');

		return $aEmails;
	}

	public static function extractSubject(&$sEmail)
	{
		$iIndex   = strpos($sEmail, "\n");
		$sSubject = substr($sEmail, 0, $iIndex-1);
		$sEmail   = substr($sEmail, $iIndex+1);
		return $sSubject;
	}

	public static function email1($aTo, $sSubject, $sBody, $aHeader = null)
	{
		global $bEnableErrorHandlers;

		$bEnableErrorHandlers = false;

		$sLRLF = "\r\n";
		$sHeader = '';
		$aHeaders = array ('From' => 'Spending Profile <support@SpendingProfile.com>', 'Content-type' => 'text/html; charset=iso-8859-1');
		if ($aHeader !== null) $aHeaders = array_merge($aHeaders, $aHeader);

		foreach ($aHeaders as $sKey=>$sValue) $sHeader .= $sKey.': '.$sValue.$sLRLF;
		$sHeader .= $sLRLF;

		for($i = 0; $i < count($aTo); $i++) @mail($aTo[$i], $sSubject, $sBody, $sHeader);

		$bEnableErrorHandlers = true;

		debug("<b>Sending Email: </b>:".var_export($aTo, true)."<br/><b>Subject: </b>$sSubject</br><b>Body: <b/><br/>$sBody");
	}

	public static function email($aTo, $sSubject, $sBody, $aHeader = null)
	{
		global $bEnableErrorHandlers;
		$bEnableErrorHandlers = false;


		$sTo = $aTo[0];
		$sFrom = 'no-reply@spendingprofile.com';

		//Build SMTP authentication the header.
		$aSmtp = array('host'=>'mail.spendingprofile.com', 'port'=>587, 'auth'=>true, 'username'=>$sFrom, 'password'=>'<pwd>', 'debug'=>false);
		$aHeader = array ('MIME-Version'=>'1.0', 'Content-type'=>"text/html; charset=iso-8859-1;", 'From'=>$sFrom, 'To'=>$sTo, 'Subject'=>$sSubject);

		//Create a mail object.
		$oMail = Mail::factory('smtp', $aSmtp);

		//Send the email
		$oResult = $oMail->send($sTo, $aHeader, $sBody);


		$bEnableErrorHandlers = true;

		if (PEAR::isError($oResult)) debug("An error occurred while send the email: " . $oResult->getMessage(), false, true);

		debug("<b>Sending Email: </b>:".var_export($aTo, true)."<br/><b>Subject: </b>$sSubject</br><b>Body: <b/><br/>$sBody");
		
	}

	/**
	 * @param $aTo a string or array to whom this email is going to be sent to.
	 */
	public static function sendEmail($aTo, $sFile, $aFind, $aReplace, $aHeader = null)
	{
		$sEmailBody = file_get_contents($sFile);

		$sEmailBody = str_replace($aFind, $aReplace, $sEmailBody);
		$sSubject   = Utility::extractSubject($sEmailBody);

		Utility::email($aTo, $sSubject, $sEmailBody, $aHeader);
	}

	public static function echoString($sString)
	{
		return "<echoString Message='Echo string class method.' String='ECHOING: $sString'/>";
	}

	public static function udate($sFormat, $uTimestamp = null)
	{
		if (is_null($uTimestamp)) $uTimestamp = microtime(true);

		$iTimestamp = floor($uTimestamp);
		$iMilliseconds = round(($uTimestamp - $iTimestamp) * 1000000);

		return date($sFormat . '.' . $iMilliseconds, $iTimestamp);
	}

	public static function isDateInRange($sDate, $sFrom, $sTo)
	{
		$iTo = strtotime($sTo);
		$iDate = strtotime($sDate);
		$iFrom = strtotime($sFrom);
		return ($iDate >= $iFrom && $iDate <= $iTo);
	}

	public static function getRelativeDate($sDate, $sRelativeFrom, $sRelativeTo)
	{
		$iDate = strtotime($sDate);
		$iTo = strtotime($sRelativeTo);
		$iFrom = strtotime($sRelativeFrom);

		$sD = date('Y-m-15', $iDate);
		$sF = date('Y-m-15', $iFrom);
		$iD = strtotime($sD);
		$iF = strtotime($sF);
		$sDay = date('d', $iDate);

		return date('Y-m-'.$sDay, $iTo - ($iF - $iD));
	}

	public static function copyMonthYear($sDate, $sSource)
	{
		$aDate = getdate(strtotime($sDate));
		$aSource = getdate(strtotime($sSource));

		$sDay = $aDate['mday'];
		$iDaysInSource = intval(date('t', strtotime($sSource)));

		//If day of the month does not exist then set it to the highest day.
		if ($iDaysInSource < $sDay) $sDay = $iDaysInSource;

		return $aSource['year'] . '-' . $aSource['mon'] . '-' . $sDay;
	}

	public static function getDateParts($sDate, &$iYear = -1, &$iMonth = -1, &$iDay = -1)
	{
		$aDate = date_parse($sDate);

		if ($iMonth != -1) $iMonth = $aDate['month'];
		if ($iYear != -1) $iYear = $aDate['year'];
		if ($iDay != -1) $iDay = $aDate['day'];
	}

	public static function dateToString($iYear, $iMonth, $iDay)
	{
		return ($iYear . '-' . ($iMonth < 10 ? '0' : '') . $iMonth . '-' . ($iDay < 10 ? '0' : '') . $iDay);
	}

	public static function capitalize($sString)
	{
		$sString = strtolower($sString);
		$sString[0] = strtoupper($sString[0]);
		return $sString;
	}

	/**
	 * Randomly auto generates a password of the specified length;
	 */
	public static function generatePassword($iLength = 6)
	{
		//Start with a blank password.
		$sPassword = "";

		//Define possible characters.
		$sPossibleChars = "0123456789AbcdfghjkmnpqrstvwxyzABCDFGHJKMNPQRSTVWXYZ";

		//Set up a counter.
		$iIndex = 0;

		//add random characters to the password until it is the correct length.
		while ($iIndex < $iLength)
		{
			//Pick a random character from the possible ones.
			$cChar = substr($sPossibleChars, mt_rand(0, strlen($sPossibleChars)-1), 1);

			//Add the character to the password, unless it is already present.
			if (!strstr($sPassword , $cChar))
			{
				$sPassword .= $cChar;
				$iIndex++;
			}
		}

		//Return the generated password.
		return $sPassword ;
	}

	/**
	 *
	 * @param sText
	 * @param iLength
	 * @param iMinRemaining
	 * @return an array of length 2. Index 0 always will contain part or all the text, and part contains the second part of the text or null if does not
	 * require splitting.
	 */
	public static function smartTextSplit($sText, $iLength, $iMinRemaining)
	{
		$iSize = strlen($sText);
		if ($iSize <= $iLength) return array($sText, null);

		$iStop = strpos($sText, '.', $iLength);
		if ($iStop === false) $iStop = strpos($sText, '?', $iLength);
		if ($iStop === false) $iStop = strpos($sText, '!', $iLength);
		if ($iStop === false) $iStop = $iLength;

		return ($iStop === false || $iStop >= $iSize-$iMinRemaining ? array($sText, null) : array(substr($sText, 0, $iStop+1), substr($sText, $iStop+2)) );
	}
}

?>