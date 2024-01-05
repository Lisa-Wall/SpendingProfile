<?

/**
 * @author Lisa Wall
 * @date 2009-03-26
 */
class Filter
{
	private $aOperators = array('=', '!=', '<=', '>=', '<', '>');
	private $sExpression = "/\s|([\"].*[\"])|(,)|([!<>]?=)|([<>])|(OR)/";

	private $aColumnMap = array('id'=>'transactions.id', 'vendor'=>'vendors.name', 'account'=>'accounts.name', 'category'=>'categories.name', 'date'=>'transactions.date', 'debit'=>'transactions.debit', 'fixed'=>'transactions.fixed','amount'=>'transactions.amount', 'notes'=>'transactions.notes');


	private $iUserId = 0;
	private $oDatabase = null;

	public function __construct()
	{
		global $oSession;
		$this->iUserId = $oSession->iUserId;
		$this->oDatabase = $oSession->oDatabase;
	}




	public function getAll()
	{
		$aFilters = $this->oDatabase->selectRows('sql/filters/get_all.sql', $this->iUserId);
		if ($aFilters === false || $aFilters === null) return RESPONSE_SERVER_ERROR;

		//Return list.
		return '<Filters.getAll>'.XML::fromArrays('Filter', $aFilters).'</Filters.getAll>';
	}
	
	public function add($sFilter)
	{
		$iId = $this->oDatabase->insert('sql/filters/add.sql', 'filters', $this->iUserId, $sFilter);
		if ($iId === false || $iId === null) return error('While adding new filter.', RESPONSE_SERVER_ERROR);

		return XML::serialize(true, 'Filter.add', 'Id', $iId, 'Filter', $sFilter);
	}
	
	public function delete($iId)
	{
		//Ensure specified id belongs to user.
		if ($this->oDatabase->selectValue('sql/filters/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to delete filter not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Delete transaction.
		if (!$this->oDatabase->delete('sql/filters/delete.sql', $this->iUserId, $iId)) return error('Delete filter from database.', RESPONSE_SERVER_ERROR);

		//Return deleted response.
		return XML::serialize(true, 'Filter.delete', 'Id', $iId);
	}




	/**
	 * This function should never fail and should recover from all unexpected tokens by droping them and continuing.
	 *
	 * <filters>      = <filter> (<or>? <filter>)*
	 * <filter>       = <value> | <column_value>
	 * <column_value> = <columnName> <operator> <value> (',' <value>)*     ;Example: Vendor = Zellers, Walmart, "Canadian Tier"
	 * <operator>     = '=' | '!=' | '<=' | '>=' | '<' | '>'
	 * <or>           = 'OR'
	 * <value>        = '*'? <any> '*'? | '"' <any> '"' | <any>
	 */
	public function build(&$sFilter)
	{
		$sSearch_Bit     = "transactions.debit='<VALUE>' OR transactions.fixed='<VALUE>'";
		$sSearch_Date    = "transactions.date LIKE '<VALUE>%'";
		$sSearch_Float   = "transactions.amount='<VALUE>'";
		$sSearch_Integer = "transactions.id='<VALUE>'";
		$sSearch_String  = "vendors.name LIKE '%<VALUE>%' OR accounts.name LIKE '%<VALUE>%' OR categories.name LIKE '%<VALUE>%' OR transactions.notes LIKE '%<VALUE>%'";

		$aFilter = preg_split($this->sExpression, $sFilter, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		$sQuery  = '';
		$sOpcode = '';
		$sFilter = '';

		$iIndex = 0;
		$iLength = count($aFilter);

		while ($iIndex < $iLength)
		{
			//Read token and peek on next token.
			$sToken = $aFilter[$iIndex++];
			$sNext = ($iIndex < $iLength ? $aFilter[$iIndex] : null);

			//If unexpected token then skip it.
			if ($this->isToken($sToken))
			{
				continue;
			}
			//If <column_value> = <name> <operator> <value> (',' <value>)*
			else if ($this->isName($sToken) && $this->isOperator($sNext))
			{
				//Read <name> <operator> <value>
				$sName = strtolower($sToken);
				$sOperator = $aFilter[$iIndex++];
				$sToken = ($iIndex < $iLength ? $aFilter[$iIndex++] : '');
				$sValue = $this->safeSql($sToken);

				//If column does not exist then continue.
				if (!array_key_exists($sName, $this->aColumnMap)) continue;
				$sColumnName = $this->aColumnMap[$sName];


				//If Value contains * then replace $sOperator with LIKE and replace * with %.
				if (strpos($sValue, '*') !== false)
				{
					$sValue = str_replace('*', '%', $sValue);
					$sNot = ($sOperator == '!=' ? 'NOT' : '');
					$sColumn = "($sColumnName $sNot LIKE ".Database::safeSQL($sValue, true).")";
				}
				else
				{
					$sColumn = "($sColumnName$sOperator".Database::safeSQL($sValue, true).")";
				}

				
				$sFilter .= (empty($sFilter) ? '' : ' ') . $sName . $sOperator . $sToken;

				// (',' <value>)*
				while ($iIndex < $iLength && $aFilter[$iIndex] == ',')
				{
					$iIndex++;
					$sToken = ($iIndex < $iLength ? $aFilter[$iIndex++] : '');
					$sValue = $this->safeSql($sToken);
					
					//If Value contains * then replace $sOperator with LIKE and replace * with %.
					if (strpos($sValue, '*') !== false)
					{
						$sNot = ($sOperator == '!=' ? 'NOT' : '');
						$sValue = str_replace('*', '%', $sValue);
						$sColumn .= "OR($sColumnName $sNot LIKE '$sValue')";
					}
					else
					{
						$sColumn .= "OR($sColumnName$sOperator'$sValue')";
					}

					$sFilter .= ',' . $sToken;
				}

				$sQuery .= $sOpcode.$sColumn;
			}
			// <value>
			else
			{
				$sValue = $this->safeSql($sToken);

				//Check type to optimaize search filter.
				if      ($this->isBit($sValue))     $sSearch = "$sSearch_Bit OR $sSearch_Integer OR $sSearch_Float OR $sSearch_String";
				else if ($this->isInteger($sValue)) $sSearch = "$sSearch_Integer OR $sSearch_Float OR $sSearch_Date OR $sSearch_String";
				else if ($this->isFloat($sValue))   $sSearch = "$sSearch_Float OR $sSearch_String";
				else if ($this->isDate($sValue))    $sSearch = "$sSearch_Date OR $sSearch_String";
				else                                $sSearch = "$sSearch_String";

				// <value>
				$sQuery  .= $sOpcode . '(' . str_replace('<VALUE>', $sValue, $sSearch) . ')';
				$sFilter .= (empty($sFilter) ? '' : ' ') . $sToken;
			}

			// <or> = 'OR'
			$sOpcode = ($iIndex < $iLength && $aFilter[$iIndex] == 'OR' ? $aFilter[$iIndex++] : 'AND');
			$sFilter .= ($sOpcode == 'OR' ? ' OR' : '');
		}

		return ($sOpcode != '' ? "AND ($sQuery)" : '');
	}

	public function isToken($sValue)
	{
		return (in_array($sValue, $this->aOperators) || $sValue == 'OR' || $sValue == ',');
	}

	public function isOperator($sValue)
	{
		return in_array($sValue, $this->aOperators);
	}

	public function isName($sValue)
	{
		return (strlen($sValue) > 1 && $sValue[0] != '"');
	}

	public function isBit($sValue)
	{
		return ($sValue == '1' || $sValue == '0');
	}

	public function isInteger($sValue)
	{
		return preg_match('/^[+-]?[0-9]+$/', $sValue);
	}

	public function isFloat($sValue)
	{
		return is_numeric($sValue);
	}

	public function isDate($sValue)
	{
		return preg_match('/^([0-9]{4,4})-?(([0-9]{1,2})-?(([0-9]{1,2}))?)?$/', $sValue);
	}

	/**
	 * Removes double quotes if they exists and makes it sql safe.
	 */
	public function safeSql($sValue)
	{
		if (strlen($sValue) > 1 && $sValue[0] == '"') $sValue = substr($sValue, 1, -1);
		return Database::safeSQL($sValue, false);
	}


	public static function process(&$sFilter)
	{
		$oFilter = new Filter();
		return $oFilter->build($sFilter);
	}
}
?>