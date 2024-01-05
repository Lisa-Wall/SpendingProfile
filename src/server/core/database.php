<?
/**
 * Database abstraction class designed to provide functions to simplify and reduce code replication.
 *
 * @author Lisa Wall
 * @date 2008-02-01
 */
class Database
{
	private $sHost       = null;
	private $sUsername   = null;
	private $sPassword   = null;
	private $sDatabase   = null;
	private $oConnection = null;

	public $iDuration   = 0;
	public $sLastQuery  = null;

	/**
	 * Creates a database class with the specified information. The constructor will not
	 * attempt to connect to the database until it is required. To force connection then call
	 * connect() after creating the class.
	 *
	 */
	public function __construct($sHost, $sUsername, $sPassword, $sDatabase)
	{
		$this->sHost     = $sHost;
		$this->sUsername = $sUsername;
		$this->sPassword = $sPassword;
		$this->sDatabase = $sDatabase;
	}

	/**
	 * If not yet connected, attempts to connect using the specified connection data set
	 * when creating this class. If an error occurs then the error is reported in
	 * error().
	 *
	 * @return connection object if successful, otherwise null.
	 */
	public function connect()
	{
		//If not already connected then try to connect.
		if ($this->oConnection == null)
		{
			$testConnection = mysql_pconnect($this->sHost, $this->sUsername, $this->sPassword);

			if ($testConnection !== false)
			{
				$this->oConnection = $testConnection;
				if (!mysql_select_db($this->sDatabase, $this->oConnection)) return error('Selecting database.', null);
			}
			else return error('Connecting to database.', null);
		}

		return $this->oConnection;
	}

	/**
	 * If the database was connected then closes the connection, and cleans up local variables.
	 */
	public function disconnect()
	{
		if ($this->oConnection != null) mysql_close($this->oConnection);
		$this->oConnection = null;
	}

	/**
	 * Executes the specified SQL query.
	 *
	 * @param sSQL an SQL query string.
	 * @return the SQL result set from the query if successful, otherwise returns FALSE.
	 */
	public function execute($sSQL)
	{
		$this->sLastQuery = $sSQL;
		debug("SQL Query: $sSQL");

		//Start a timer.
		$iTimeStart = microtime(true);

		//If not connected then try to connect.
		if ($this->connect() == null) return false;

		//Execute the query.
		$queryResult = @mysql_query($sSQL, $this->oConnection);

		//If an error occured then report it.
		if($queryResult === false) error("<b>Database Error:</b> ".mysql_error($this->oConnection)."<br/><b>QUERY:</b>$sSQL");

		//Recored the duration of this queury.
		$this->iDuration += round((microtime(true) - $iTimeStart), 6);

		//Return the result set, which could be a valid query result set, or false.
		return $queryResult;
	}

	/**
	 * Loads the query within the specified file and populates it using the specified array values.
	 *
	 * @param sFile the name and path fo the sql file to be loaded.
	 * @param aValues an array of values to repace %x with within the query.
	 * @return the SQL result set from the query if successful, otherwise returns FALSE.
	 * @see execute()
	 */
	private function executeFile($sFile, $aValues)
	{
		$sQuery = file_get_contents($sFile);
		$sQuery = str_replace("\n", ' ', $sQuery);
		$sQuery = preg_replace('([ ]+)', ' ', $sQuery);

		//Travers the array and replace the values.
		$iIndex = 1;
		$aIndexes = array("", '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g');
		foreach ($aValues as $sValue)
		{
			if (is_array($sValue)) $sValue = $sValue[1];
			else if (is_string($sValue) && $sValue != 'NULL' && strlen($sValue) > 0) $sValue = $this->safeSQL($sValue, false);

			$sQuery = str_replace("%".$aIndexes[($iIndex++)], $sValue, $sQuery);
		}

		//Execute the query and return results.
		return $this->execute($sQuery);
	}

	public function selectRows($sFile /*, ...*/)
	{
		$aValues = array_slice(func_get_args(), 1);
		$pResult = $this->executeFile($sFile, $aValues);
		return ($pResult === false ? false : $this->resultsToArray($pResult));
	}

	public function selectRow($sFile /*, ...*/)
	{
		$aValues = array_slice(func_get_args(), 1);
		$pResult = $this->executeFile($sFile, $aValues);

		if ($pResult === false) return false;
		$aResult = $this->resultsToArray($pResult);

		return (count($aResult) > 0 ? $aResult[0] : null);
	}

	public function selectValue($sFile /*, ...*/)
	{
		$aValues = array_slice(func_get_args(), 1);
		$pResult = $this->executeFile($sFile, $aValues);

		if ($pResult === false) return false;
		$aResult = $this->resultsToArray($pResult);

		return (count($aResult) > 0 ? Utility::getArrayFirstValue($aResult[0]) : null);
	}

	/**
	 * Executes the specified query, if the table name is not null then returns the insterted ID value otherwise true
	 * if successful, false otherwise.
	 * @param sFile the path of the query file to execute.
	 * @param sTable the name of the table to get the last inserted id of, or null if not required.
	 * @param ... a list of values to be replaced within the query.
	 * @return true if successful otherwise false, if sTable is not null then returns last inserted id if successful.
	 * @see executeFile()
	 */
	public function insert($sFile, $sTable = null /*, ...*/)
	{
		$aValues = array_slice(func_get_args(), 2);
		$pResult = $this->executeFile($sFile, $aValues);
		return ($pResult === false ? false : ($sTable != null ? $this->getLastInsertedId($sTable) : true));
	}

	public function insertXML($sFile, $sTable = null, $oElement, $aMap)
	{
		$aValues = array();
		foreach ($aMap as $sName) $aValues[] = $oElement->getAttribute($sName);
		$oResult = $this->executeFile($sFile, $aValues);

		return ($oResult === false ? false : ($sTable != null ? $this->getLastInsertedId($sTable) : true));
	}


	/**
	 * Executes the specified query and returns true if the query was successful otherwise returns false.
	 * @param sFile the path of the query file to execute.s
	 * @param ... a list of values to be replaced within the query.
	 * @return true if successful otherwise false.
	 * @see executeFile()
	 */
	public function delete($sFile /*, ...*/)
	{
		$aValues = array_slice(func_get_args(), 1);
		$pResult = $this->executeFile($sFile, $aValues);
		return ($pResult === false ? false : true);
	}

	/**
	 * Executes the specified query and returns true if the query was successful otherwise returns false.
	 * @param sFile the path of the query file to execute.s
	 * @param ... a list of values to be replaced within the query.
	 * @return true if successful otherwise false.
	 * @see executeFile()
	 */
	public function update($sFile /*, ...*/)
	{
		$aValues = array_slice(func_get_args(), 1);
		$pResult = $this->executeFile($sFile, $aValues);
		return ($pResult === false ? false : true);
	}

	/**
	 * Gets the last insert id within the specified table for the current connection.
	 *
	 * @param sTable the name of the database table to get last auto-increment id from.
	 * @return the id of the last inserted id, otherwise false if no id exists or an error occured.
	 */
	public function getLastInsertedId($sTable = null)
	{
		return mysql_insert_id($this->oConnection);
	}

	/**
	 * Makes the specified data string SQL safe to be passed in as a value. This is used to help prevent SQL injections.
	 *
	 * @param sData string to make SQL safe.
	 * @return cleans the data
	 */
	public static function safeSQL($sData, $bQuotes = true)
	{
		$sData = str_replace("'", "''",$sData);
		return ($bQuotes ? "'${sData}'" : $sData);
	}

	/**
	 * Converts the specified database result from a SELECT statement to an array of rows.
	 *
	 * @param $oResultSet a database result set as returned from a select statemenet.
	 * @return a double array, where each row is in the format (column name => value);
	 */
	public static function resultsToArray($oResultSet)
	{
		$aaResult = array();
		$aResult = mysql_fetch_array($oResultSet, MYSQL_ASSOC);

		while ($aResult !== false)
		{
			array_push($aaResult, $aResult);
			$aResult = mysql_fetch_array($oResultSet, MYSQL_ASSOC);
		}

		return $aaResult;
	}
}

?>