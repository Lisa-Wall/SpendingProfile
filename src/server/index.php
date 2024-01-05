<?
	ini_set("include_path", '/home/beta/php:' . ini_get("include_path") );

	require_once('Mail.php');
	include_once('config.php');

	include_once('core/xml.php');
	include_once('core/hash.php');
	include_once('core/xpath.php');
	include_once('core/logger.php');
	include_once('core/utility.php');
	include_once('core/service.php');
	include_once('core/debugger.php');
	include_once('core/database.php');
	include_once('core/validator.php');
	include_once('core/ofx_reader.php');
	include_once('core/ofx_document.php');

	include_once('tag.php');
	include_once('user.php');
	include_once('goal.php');
	include_once('filter.php');
	include_once('budget.php');
	include_once('guesser.php');
	include_once('session.php');
	include_once('transaction.php');
	include_once('statement.php');

	include_once('graphs.php');
	include_once('analysis.php');
	include_once('piegraph.php');
	include_once('bargraph.php');
	include_once('linegraph.php');

	include_once('export.php');
	include_once('export_abstract.php');
	include_once('export_csv.php');
	include_once('export_ofx.php');

	include_once('import.php');
	include_once('import_csv.php');
	include_once('import_ofx.php');
	include_once('import_filters.php');

	include_once('demo.php');
	include_once('admin.php');
	include_once('ad.php');
	include_once('receipt.php');

	date_default_timezone_set('Canada/Eastern');
	enableErrorHandlers();

	define('RESPONSE_XML', 'XML');
	define('RESPONSE_TEXT', 'TEXT');
	define('RESPONSE_HTML', 'HTML');
	define('RESPONSE_IMAGE', 'IMAGE');
	define('RESPONSE_IMAGE_JPEG', 'IMAGE_JPEG');


	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);

	$sResponseType = RESPONSE_XML;
	function setOutputType($sType)
	{
		global $sResponseType;
		$sResponseType = $sType;

		if ($sType == RESPONSE_IMAGE)
		{
			header('Content-type: image/png', true);
		}
		else if ($sType == RESPONSE_IMAGE_JPEG)
		{
			header('Content-type: image/jpeg', true);
		}
	}

	//Get the request from the post.
	$sRequest = (isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

	//If there is get request then process it.
	if (isset($_REQUEST['request'])) $sRequest = stripslashes($_REQUEST['request']);
	else if (strlen($sRequest) == 0) $sRequest = file_get_contents('php://input');

	// If no request given then return generic welcome message.
	if (strlen($sRequest) == 0)
	{
		echo 'Welcome to Spending Profile server v' . VERSION;
		exit();
	}
	
//ini_set("session.entropy_file", "/dev/random");
//ini_set("session.entropy_length", "512");

	//Start or load a session.
	session_start();

	//outout debug message.
	if (DEBUG_ENABLED) debug('<b>REQUEST</b>: '. XML::clean($sRequest));

	//Get or create a session, then load it.
	$oSession = (isset($_SESSION['Session']) ? $_SESSION['Session'] : new Session());

	//Expose some variable globaly
	//$oDatabase = $oSession->oDatabase;

	//Create the service and execute the request.
	$oService = new Service('content/schema.xml', 'authenticate', 'requestLogger');
	$sResponse = $oService->execute($sRequest);

	if (DEBUG_ENABLED)
	{
		debug('<b>RESPONSE</b>: '. XML::clean(substr($sResponse, 0, 1024)));
		debugDump();
	}

	//Put the setting back into the session.
	$_SESSION['Session'] = $oSession;

	if ($sResponseType == RESPONSE_XML)
	{
		header('content-type: text/xml; charset=UTF-8', true);
		echo utf8_encode($sResponse);
	}
	else if ($sResponseType == RESPONSE_TEXT)
	{
		header('content-type: text/plain; charset=UTF-8', true);
		echo utf8_encode($sResponse);
	}
	else if ($sResponseType == RESPONSE_HTML)
	{
		header('content-type: text/html; charset=UTF-8', true);
		echo utf8_encode($sResponse);
	}
	else if ($sResponseType == RESPONSE_IMAGE || $sResponseType == RESPONSE_IMAGE_JPEG)
	{
		//header('Content-type: image/png', true);
		echo $sResponse;
	}
	else
	{
		header('Content-Disposition: attachment; filename="'.urlencode($sResponseType).'";');
		header('Content-Type: application/download');
		header('Content-Type: application/force-download');
		header('Content-Description: File Transfer');
		header('X-Download-Options: noopen');
		header('X-Content-Type-Options: nosniff');
		header('Cache-Control: private');
		header('Pragma: private');
		//header('Content-Length:'.strlen($sResponse));

		echo $sResponse;
	}

?>