<?
/**
 * @author Lisa Wall
 * @date 2009-04-11
 */
class Guesser
{
	private $iUserId;
	private $oDatabase;

	private $fOrderRanks;

	private $aIgnoreList = array('and', 'or', 'of', 'in');

	public function __construct()
	{
		global $oSession;
		$this->iUserId = $oSession->iUserId;
		$this->oDatabase = $oSession->oDatabase;

		$this->fOrderRanks = create_function('$v1,$v2', 'return ($v1["Rank"] == $v2["Rank"] ? (strlen($v1["Name"]) < strlen($v2["Name"]) ? -1 : +1) : ($v1["Rank"] < $v2["Rank"] ? +1 : -1));');
	}

	/**
	 * Requires only the vendor which is used to guess Category, Account, Type.
	 * TODO: possibly use the amount and date as well? to find exact otherwise anything.
	 */
	public function all($sVendor, &$aResult)
	{
		if (strlen($sVendor) == 0) return;

		$aVendor = $this->split($sVendor);
		$sFilter = $this->getSqlFilter($aVendor, 'vendors.name', true);
		$aMatches = $this->oDatabase->selectRows("sql/import/category_rank.sql", $this->iUserId, array(0, $sFilter));

		if (count($aMatches) == 0) return;

		$aMatch = $aMatches[0];

		if (!array_key_exists('Fixed', $aResult)) $aResult['Fixed'] = $aMatch['Fixed'];
		if (!array_key_exists('Account', $aResult)) $aResult['Account'] = $aMatch['Account'];
		if (!array_key_exists('Category', $aResult)) $aResult['Category'] = $aMatch['Category'];

		//$aAccouns = array();
		//$aCategories = array();

		//foreach ($aMatches as $aMatch)
		//{
		//    $aAccouns[$aMatch['Account']] = $aMatch['Account'];
		//    $aCategories[$aMatch['Category']] = $aMatch['Rank'] . " - " . $aMatch['Category'];
		//}

	//TODO: user a different character than ; or filter out ;
		//$aResult['Account_Matches'] = implode(';', $aAccouns);
		//$aResult['Category_Matches'] = implode(';', $aCategories);
	}

	/**
	 * Attempts to guess the vendor from the specified vendor string.
	 *
	 * Algorithm:
	 *  - Cleans up the vendor.
	 *  - Finds all exact matches in the vendor map table.
	 *    - If more than one, rank list by number of found transactions for each vendor.
	 *  - If no matches found Finds all partial in vendor table.
	 *    - Rank the list according to rank algorithm.
	 */
	public function vendor($sVendor, &$aResult)
	{
		//If no value is specified then return the value.
		if (strlen($sVendor) == 0) return $sVendor;

		//Clean the value.
		$aVendor = array_map('Utility::capitalize', $this->split($sVendor));
		$sClean  = implode(' ', $aVendor);
		$aVendor = $this->clean($aVendor);

		//Create the sql filter. Get a list of matches.
		$sFilter = $this->getSqlFilter($aVendor, 'name');
		$aMatches = $this->oDatabase->selectRows("sql/import/vendors_rank.sql", $this->iUserId, array(0, $sFilter));

		//Find rank the found matches.
		$sRank = '';
		$sVendor = $this->rank($aVendor, $aMatches, $sRank);

		$aResult['Vendor'] = $sVendor;
		$aResult['Vendor_Rank'] = $sRank;
		$aResult['Vendor_Clean'] = $sClean;
		$aResult['__children'][] = array('__name'=>'VendorMatches', '__children'=>$aMatches);
	}


	/**
	 * All ranking ignores case.
	 *
	 * For each on of the following  conditions rank is incromented by one.
	 * - If found partial match.
	 * - If found full match.
	 * - If found all matches.
	 * - If found all matches ordered.
	 * - If found exact match.
	 *
	 * The rank is dependent on the size of the value.
	 * - If Exact match: (count * 2 + 3)
	 * - If Full match: (count * 2 + 2)
	 * - If Partial: less than full.
	 */
	public function rank($aValue, &$aMatches, &$sRank)
	{
		//Store the count of the values.
		$iValue = count($aValue);

		//Travers the list and rank it.
		foreach ($aMatches as &$aMatch)
		{
			//Contains the position of each match in order to later check if match as ordered.
			$iRank = 0;
			$aPositions = array();

			//Split the match name by spaces to check if exact word match exists.
			$sMatchName = $aMatch['Name'];
			$aMatchName = array_map('strtolower', preg_split('/[\s]+/', $sMatchName));

			//For each value part rank match according to partial and full matches.
			foreach ($aValue as $sValue)
			{
				//TODO: handle single character values.
				$iPosition = stripos($sMatchName, $sValue);

				//If found then add position and incroment rank.
				if ($iPosition !== false)
				{
					$iRank++;
					$aPositions[] = $iPosition;

					//If full word match then add incroment rank.
					if (in_array(strtolower($sValue), $aMatchName)) $iRank++;
				}
			}

			//If all words where found then incroment rank.
			if ($iValue == count($aPositions))
			{
				$iRank++;

				//If all found and in order then incroment rank.
				if (Utility::isArrayOrdered($aPositions))
				{
					$iRank++;

					//If exact match then incroment rank.
					if ($iValue == count($aMatchName)) $iRank++;
				}
			}

			//Add the rank to the array.
			$aMatch['Rank'] = $iRank;
			$aMatch['Strength'] = ($iRank == ($iValue * 2 + 3) ? 'EXACT' : ($iRank == ($iValue * 2 + 2) ? 'FULL' : 'PARTIAL'));
		}

		//Order the matches according to highest rank.
		usort($aMatches, $this->fOrderRanks);

		//Return the top ranking value.
		if (count($aMatches) > 0)
		{
			$sRank = $aMatches[0]['Strength'];
			return $aMatches[0]['Name'];
		}
		else
		{
			$sRank = 'NONE';
			return implode(' ', $aValue);
		}
	}

	/**
	 * Only keeps Letters and splits the word up by consecutive letters.
	 */
	public static function split($sValue)
	{
		return preg_split("/\s|([A-Za-z]*)|[0-9]*|[`~!@#$%^&*(){};:,<.>\/?|]*/", $sValue, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Cleans the specified value by remove all words that are in the ignore list.
	 */
	public function clean($aValue)
	{
		$aClean = array();
		foreach ($aValue as $sValue) if (!in_array(strtolower($sValue), $this->aIgnoreList)) $aClean[] = $sValue;
		return $aClean;
	}

	/**
	 * Generates an SQL search filter for the specified value array.
	 */
	public function getSqlFilter($aValue, $sField, $bExactMatch = false)
	{
		$sFilter = '';
		$sOperation = '';
		foreach($aValue as $sValue)
		{
			$sValue = Database::safeSQL($sValue, false);

			if ($bExactMatch || strlen($sValue) == 1) $sFilter .= " $sOperation ($sField LIKE '$sValue %') OR ($sField LIKE '% $sValue') OR ($sField LIKE '% $sValue %')";
			else                                      $sFilter .= " $sOperation ($sField LIKE '$sValue%') OR ($sField LIKE '% $sValue%')";

			$sOperation = 'OR';
		}

		return $sFilter;
	}
}


?>