<?

define('RESPONSE_SERVER_OK'         , '<Response Type="OK"/>');
define('RESPONSE_SERVER_ERROR'      , '<Error Type="SERVER_ERROR" Message="%MESSAGE%"/>');
define('RESPONSE_NOT_AUTHENTICATED' , '<Error Type="NOT_AUTHENTICATED" Message="%MESSAGE%"/>');
define('RESPONSE_INVALID_REQUEST'   , '<Error Type="INVALID_REQUEST" Message="%MESSAGE%"/>');
define('RESPONSE_INVALID_ARGUMENTS' , '<Error Type="INVALID_ARGUMENTS" Message="%MESSAGE%" />');
define('RESPONSE_ALREADY_EXISTS'    , '<Error Type="ALREADY_EXISTS" Message="%MESSAGE%" />');
define('RESPONSE_NO_PERMISSIONS'    , '<Error Type="INSUFFICIENT_PERMISSION" Message="%MESSAGE%"/>');
define('RESPONSE_TOO_MANY_ATTEMPTS' , '<Error Type="TOO_MANY_ATTEMPTS" Message="%MESSAGE%"/>');
define('RESPONSE_MANY_ATTEMPTS_WARNING', '<Error Type="MANY_ATTEMPTS_WARNING" Message="%MESSAGE%"/>');


/**
 * This is a service class similar to SOAP but more like RMI.
 *
 * @author Lisa Wall
 * @date 2009-03-28
 */
class Service
{
	/**
	 * Contains the service xml document schema.
	 */
	protected $oSchema = null;
	protected $fLogger = null;
	protected $fAuthenticate = null;

	/**
	 * Creates the service class and uses the specified schema file for xml call definision. Throws an exception if
	 * an xml error occured.
	 *
	 * @param sSchemaFile the service xml call definision.
	 * @exception XMLParserError
	 */
	public function __construct($sSchemaFile, $fAuthenticate = null, $fLogger = null)
	{
		//Create a document for the schema
		$this->oSchema = new DOMDocument('1.0', 'UTF-8');

		//Load the schema file.
		$bResult = $this->oSchema->load($sSchemaFile);

		//If did not load successfully then throw an exception.
		if ($bResult === false) throw new Exception('Unable to load or parse schema file.');

		//Add the logger and authentication value.
		$this->fLogger = $fLogger;
		$this->fAuthenticate = $fAuthenticate;
	}

	/**
	 * Executes the service class and or method.
	 *
	 * @return the result of the executed call.
	 */
	public function execute($sXML)
	{
		//Create document for parsing xml.
		$oDocument = new DOMDocument('1.0', 'UTF-8');

		//Parse the xml string. If parser error then return response error.
		$bResult = @$oDocument->loadXML($sXML);
		$oRequest = $oDocument->documentElement;
		if ($bResult === false || $oRequest == null) return error('Loading xml request.', RESPONSE_INVALID_REQUEST);

		return $this->executeXml($oRequest);
	}

	/**
	 * Executes the request within the xml element.
	 *
	 * @return the result of the executed call.
	 */
	public function executeXml($oRequest)
	{
		//Get the node name and split it to class.function.
		$aName = explode('.', $oRequest->nodeName);
		if (count($aName) != 2) return error('Expecting class.method.', RESPONSE_INVALID_REQUEST);

		//Get the class schema.
		for ($oSchema = $this->oSchema->documentElement->firstChild; $oSchema != null && $oSchema->nodeName != $aName[0]; $oSchema = $oSchema->nextSibling);
		if ($oSchema === null) return error('Class not found.', RESPONSE_INVALID_REQUEST);

		//Get the method schema.
		for ($oSchema = $oSchema->firstChild ; $oSchema != null && $oSchema->nodeName != $aName[1]; $oSchema = $oSchema->nextSibling);
		if ($oSchema === null) return error('Method not found.', RESPONSE_INVALID_REQUEST);

		//Execute the class call.
		return $this->executeClass($oRequest, $oSchema, $aName[0], $aName[1]);
	}

	/**
	 * Executes the class method represented by the specified xml element and returns the results.
	 */
	protected function executeClass($oRequest, $oSchema, $sClassName, $sMethodName)
	{
		//Placed out there for scope purposes. NOTE: Even though it will work in PHP without this, it is good practice to put it.
		$oClassInstance = null;

		//Check if clas is within the session.
		if (isset($_SESSION[$sClassName]))
		{
			$oClassInstance = $_SESSION[$sClassName];
			$sClassName = get_class($oClassInstance);
		}

		//If class exists then create it. Otherwise return error.
		if (class_exists($sClassName)) $oReflectionClass = new ReflectionClass($sClassName);
		else return error('Class instance not found.', RESPONSE_INVALID_REQUEST);

		//Get the method from class. If not found return error.
		if (!$oReflectionClass->hasMethod($sMethodName)) return error('Method instance not found.', RESPONSE_INVALID_REQUEST);
		$oReflectionMethod = $oReflectionClass->getMethod($sMethodName);

		//Get the parameters from element using the schema.
		$aParameters = $this->getParameters($oRequest, $oSchema);
		if (!is_array($aParameters)) return $aParameters;

		//If parameters are missing, then error.
		if (count($aParameters) < $oReflectionMethod->getNumberOfRequiredParameters()) return error('Missing parameters.', RESPONSE_INVALID_REQUEST);

		//If class not yet created then create it.
		if ($oClassInstance == null) $oClassInstance = @$oReflectionClass->newInstance();

		//Log this call to a file.
		if ($this->fLogger !== null) { $fLogger = $this->fLogger; $fLogger($oRequest); }

		//Finally invoke the method and return the results.
		return $oReflectionMethod->invokeArgs($oClassInstance, $aParameters);
	}

	/**
	 * Gets the value of the required attribute and converts it to the specified type.
	 */
	protected function getParameters($oRequest, $oSchema)
	{
		//Array containing the parameters.
		$aParameters = array();
		$fAuthenticate = $this->fAuthenticate;

		//Travers all schema attributes and get the corresponding values from the element.
		foreach ($oSchema->attributes as $oAttribute)
		{
			if ($oAttribute->name == "__authenticate")
			{
				//If there is an authentication method then call it.
				if ($fAuthenticate != null && !$fAuthenticate()) return error('User not logged on.', RESPONSE_NOT_AUTHENTICATED);
			}
			else if ($oAttribute->name == "__administrator")
			{
				//TODO: check if administraotr.
				if ($fAuthenticate != null && !$fAuthenticate(true)) return error('User not logged on.', RESPONSE_NOT_AUTHENTICATED);
			}
			else
			{
				//Given the name of the type %name, it gets the attributes value within the document element with the same name. and returns it.
				if ($oAttribute->value[0] == '%') $oAttribute->value = $this->oSchema->documentElement->getAttribute(substr($oAttribute->value, 1));

				//Get the element's attribute value and try to convert the attribute to the required type.
				if ( ($sResult = $this->getParameter($oRequest, $oAttribute->name, $oAttribute->value, $sValue)) !== true) return $sResult;

				//Add value to parameter list.
				array_push($aParameters, $sValue);
			}
		}

		//Return the parameters.
		return $aParameters;
	}

	/**
	 * Gets the value of the required attribute and converts it to the specified type.
	 */
	protected function getParameter($oRequest, $sName, $sType, &$sValue)
	{
		$bResult = true;

		//If type is a key value then set value to it.
		if      ($sType == '__self')  $sValue = $oRequest;
		else if ($sType == '__child') $sValue = $oRequest->firstChild;
		else
		{
			$sValue = ($oRequest->hasAttribute($sName) ? $oRequest->getAttribute($sName) : null);
			$bResult = Validate::type($sValue, $sType);
			if      ($bResult === Validate::MISSING_TYPE)    $bResult = error("Invalide parameter type: $sName."    , RESPONSE_INVALID_REQUEST);
			else if ($bResult === Validate::MISSING_VALUE)   $bResult = error("Missing parameter: $sName."          , RESPONSE_INVALID_REQUEST);
			else if ($bResult === Validate::INVALID_TYPE)    $bResult = error("Unsupported parameter type: $sName." , RESPONSE_INVALID_REQUEST);
			else if ($bResult === Validate::INVALID_SYNTAX)  $bResult = error("Expecting schema type key:value."    , RESPONSE_SERVER_ERROR);
			else if ($bResult === false)                     $bResult = error("Invalid parameter: $sName"           , RESPONSE_INVALID_ARGUMENTS);
			else                                             $bResult = true;
		}

		return $bResult;
	}
}

?>