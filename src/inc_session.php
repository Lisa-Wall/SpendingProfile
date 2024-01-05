<?
	//$bHttps = false;
	ensureHTTPS((isset($bHttps) ? $bHttps : false));

	session_start();

	$sImage = URL.'styles/image.php?';
	$bLoggedOn = getSessionValue('email', false);
	$bDemo = (isset($_SESSION['demo']) ? $_SESSION['demo'] : false);

	$PAGE_WIDTH = (isset($PAGE_INNER) ? PAGE_WIDTH_INNER : PAGE_WIDTH_OUTER);

	if (isset($bSecured) && $bSecured) ensureLoggedOn();

	function getJScriptSession()
	{
		$sURL      = URL;
		$sServer   = SERVER;
		$sVersion  = VERSION;
		$sEmail    = getSessionValue('email', null);
		$sCurrency = getSessionValue('currency', '$');
		$sPreference = getSessionValue('preference', '');

		$sUsername = ($sEmail == null ? 'null' : "'$sEmail'");
		$sIsLoggedIn = ($sEmail == null ? 'false' : 'true');

		return "{url: '$sURL', server: '$sServer', version: '$sVersion', isLoggedIn: $sIsLoggedIn, email: $sUsername, currency: '$sCurrency', delimiter: ':', preference: new Hash('$sPreference') }";
	}

	function getSessionValue($sName, $sDefault)
	{
		return (isset($_SESSION[$sName]) ? $_SESSION[$sName] : $sDefault);
	}

	function ensureHTTPS($bHttps = false, $bForceWww = true)
	{
		$bIsWww = (strpos($_SERVER['HTTP_HOST'],'www.') !== false);
		$bIsHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');

		if ( (($bHttps && $bIsHttps) || (!$bHttps && !$bIsHttps)) && (($bForceWww && $bIsWww ) || (!$bForceWww && !$bIsWww )) ) return true;

		$sHost = $_SERVER['HTTP_HOST'];
		$sScript = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
		$sQuery = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');

		if (strlen($sQuery) > 0) $sQuery = '?'.$sQuery;

		if ($bForceWww && strpos($sHost,'www.') === false) $sHost = 'www.'.$sHost;

		header("Location: ".($bHttps ? "https" : "http")."://${sHost}${sScript}${sQuery}");
		exit();
	}

	function ensureLoggedOn()
	{
		$sEmail = getSessionValue('email', null);
		if ($sEmail == null) redirect('signin.php');
	}

	function getPageName()
	{
		$sPageName = $_SERVER["PHP_SELF"];
		$aUrl = Explode('/', $sPageName);
		return $aUrl[count($aUrl) - 1];
	}

	function redirect($sPage)
	{
		$sHttp = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http');
		$sScript = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
		$sHost = $_SERVER['HTTP_HOST'];

		if (($iIndex = strrpos($sScript, '/')) !== false) $sScript = substr($sScript, 0, $iIndex+1);

		header("Location: ${sHttp}://${sHost}${sScript}${sPage}");
		exit();
	}
?>