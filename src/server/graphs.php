<?
/**
 * @author Lisa Wall
 * @date 2009-03-28
 */
class Graphs
{
	private $iUserId = 0;
	private $oSession = null;
	private $oDatabase = null;

	public function __construct()
	{
		global $oSession;
		$this->iUserId = $oSession->iUserId;
		$this->oSession = $oSession;
		$this->oDatabase = $oSession->oDatabase;
	}

	public function getBalanceSummary($iWidth, $iHeight, $iFontSize)
	{
		setOutputType(RESPONSE_IMAGE);

		$oPieGraph = new PieGraph($iWidth, $iHeight, $iFontSize, true, 10, 10);

		$oPieGraph->drawBalance($this->oSession->iDebit, $this->oSession->iCredit);
		$oPieGraph->save();
	}

	public function getPie($sTag, $iWidth, $iHeight, $bFlat, $iFontSize, $sName, $bShowExtended, $iMinMapAngle, $iMinTextPixels)
	{
		$iColor = 0;
		$iTotal = 0;
		$iLevels = 0;

		if ($this->oSession->sFrom == '' || $this->oSession->sTo == '') return error('Must get transactions first.', RESPONSE_INVALID_REQUEST);

		if ($bFlat === null) $bFlat = $this->oSession->aPieGraphs[$sTag.'Flat'];
		else
		{
			//Also save the settings.
			$this->oSession->setPreference($sTag.'Flat', $bFlat?'1':'0');
			$this->oSession->aPieGraphs[$sTag.'Flat'] = $bFlat;
		}

		setOutputType(RESPONSE_IMAGE);

		$aTagExpenses = $this->oDatabase->selectRows('sql/graphs/get_'.$sTag.'.sql', $this->iUserId, $this->oSession->sFrom, $this->oSession->sTo, array(0, $this->oSession->sFilter_SQL), ($bFlat ? "" : $sName));
		if ($aTagExpenses === false || $aTagExpenses === null) return error('Getting tag expenses.', RESPONSE_SERVER_ERROR);

		if ($bFlat)
		{
			$iLevels = 1;

			foreach ($aTagExpenses as &$aTagExpense)
			{
				$aTagExpense['Children'] = array();
				$iTotal += floatval($aTagExpense['Total']);
			}

			$aTags = array('Name'=>'', 'Total'=>$iTotal, 'SubTotal'=>0, 'Children'=>$aTagExpenses, 'StartAngle'=>0, 'EndAngle'=>360);
		}
		else
		{
			$aTagExpenses = $this->expand($aTagExpenses, $iTotal, $iLevels);
			if ($bShowExtended) $aTagExpenses = $this->extend($aTagExpenses);

			$aTags = array('Name'=>'', 'Total'=>$iTotal, 'SubTotal'=>0, 'Children'=>$aTagExpenses, 'StartAngle'=>0, 'EndAngle'=>360);

			if (strlen($sName) > 0)
			{
				$sName = Tag::clean($sName);
				$aNames = explode(TAG_DELIMITER, $sName);

				foreach ($aNames as $sSubName)
				{
					$iLevels--;
					$aTags = $aTags['Children'][$sSubName];
				}
			}

			$aTags['Name'] = $sName;
			$aTags['EndAngle'] = 360;
			$aTags['StartAngle'] = 0;
		}

		$oPieGraph = new PieGraph($iWidth, $iHeight, $iFontSize, $bShowExtended, $iMinMapAngle, $iMinTextPixels);
		$oPieGraph->calculate($aTags, $iLevels, 1);

		if (count($aTagExpenses) > 0)
		{
			$oPieGraph->drawPie($aTags, $iLevels, 0, 0, $iColor);
			$oPieGraph->drawText($aTags, $iLevels, 0);
		}
		else
		{
			$oPieGraph->drawEmpty("No Expenses");
		}

		$this->oSession->aPieGraphs[$sTag] = $aTags;

		$oPieGraph->save();

		return "";
	}

	/**
	 * Gets the map of the last cashed call to getPie for the specified tag.
	 */
	public function getMap($sTag)
	{
		$aMap = array();

		//Ensure there is a map for the specified tag.
		if (!array_key_exists($sTag, $this->oSession->aPieGraphs) || $this->oSession->aPieGraphs[$sTag] == null) return error('Must call getPie first.', RESPONSE_INVALID_REQUEST);

		//Get the array tags.
		$aTags  = $this->oSession->aPieGraphs[$sTag];
		$bFlat  = $this->oSession->aPieGraphs[$sTag.'Flat'];
		$sName  = $aTags['Name'];
		$iTotal = $aTags['Total'];

		//Flaten out the tree and output the map array.
		$this->getMapArray($aMap, $aTags['Children'], $sName);

		return XML::serialize(false, 'Graphs.getMap', 'Tag', $sTag, 'Name', $sName, 'Total', $iTotal, 'Flat', ($bFlat?'true':'fales')) . XML::fromArrays('Map', $aMap) . '</Graphs.getMap>';
	}


	public static function getMapArray(&$aMap, $aTags, $sTag)
	{
		$sTags = (strlen($sTag) == 0 ? '' : $sTag.TAG_DELIMITER);

		foreach($aTags as $aTag)
		{
			$sName = $sTags.$aTag['Name'];
			$bChildren = count($aTag['Children']);

			$aMap[] = array('Name'=>$sName, 'Total'=>$aTag['Total'], 'SubTotal'=>$aTag['SubTotal'], 'hasChildren'=>($bChildren ? 'true' : 'false'), 'Map'=>implode(',', $aTag['Map']));

			if ($bChildren)
			{
				//TODO: Get all the children from the database.
				Graphs::getMapArray($aMap, $aTag['Children'], $sName);
			}
		}
	}


	public static function expand($aTags, &$iGrandTotal, &$iLevels)
	{
		$aRoot = array();
		$iLevels = 0;
		$iGrandTotal = 0;

		foreach ($aTags as $aTag)
		{
			$iTotal = $aTag['Total'];
			$aNames = explode (TAG_DELIMITER, $aTag['Name']);
			$iNames = count($aNames);
			$iGrandTotal += $iTotal;

			if (count($aNames) > $iLevels) $iLevels = count($aNames);

			$aCurrent = &$aRoot;
			for ($i = 0; $i < $iNames; $i++)
			{
				$sName = $aNames[$i];

				if (array_key_exists($sName, $aCurrent))
				{
					$aCurrent[$sName]['Total'] += $iTotal;
					$aCurrent[$sName]['SubTotal'] += ($i == $iNames - 1 ? 0 : $iTotal);
				}
				else $aCurrent[$sName] = array('Name'=>$sName, 'Total'=>$iTotal, 'SubTotal'=>($i == $iNames - 1 ? 0 : $iTotal), 'Children'=>array());

				$aCurrent = &$aCurrent[$sName]['Children'];
			}
		}

		return $aRoot;
	}

	public function extend(&$aTags)
	{
		foreach ($aTags as &$aTag)
		{
			$iTotal      = $aTag['Total'];
			$iSubTotal   = $aTag['SubTotal'];
			$aChildren   = &$aTag['Children'];

			$iDeltaTotal = $iTotal - $iSubTotal;

			if (count($aChildren) > 0) $this->extend($aChildren);
			if ($iSubTotal != 0 && $iDeltaTotal != 0) $aChildren[] = array('Name'=>"[".$aTag['Name']."]", 'Total'=>$iDeltaTotal, 'SubTotal'=>0, 'Children'=>array(), 'Extended'=>true);
		}

		return $aTags;
	}
}

?>