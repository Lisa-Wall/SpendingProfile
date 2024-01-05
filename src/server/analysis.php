<?

/**
* @author Lisa Wall
* @date 2009-03-28
*/
class Analysis
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

	public function setGraph($sFrom, $sTo, $sViewBy, $sGraphType, $bTotalExpenses, $bTotalIncome, $sCategories)
	{
		$oSession = ($this->oSession);

		$oSession->aAnalysis['To'] = $sTo = ($sTo == null ? $oSession->aAnalysis['To'] : $sTo);
		$oSession->aAnalysis['From'] = $sFrom = ($sFrom == null ? $oSession->aAnalysis['From'] : $sFrom);
		$oSession->aAnalysis['ViewBy'] = $sViewBy = ($sViewBy == null ? $oSession->aAnalysis['ViewBy'] : $sViewBy);
		$oSession->aAnalysis['GraphType'] = $sGraphType = ($sGraphType == null ? $oSession->aAnalysis['GraphType'] : $sGraphType);
		$oSession->aAnalysis['TotalExpenses'] = $bTotalExpenses = ($bTotalExpenses == null ? $oSession->aAnalysis['TotalExpenses'] : $bTotalExpenses);
		$oSession->aAnalysis['TotalIncome'] = $bTotalIncome = ($bTotalIncome == null ? $oSession->aAnalysis['TotalIncome'] : $bTotalIncome);
		$oSession->aAnalysis['Categories'] = $sCategories = ($sCategories == null ? $oSession->aAnalysis['Categories'] : $oSession->setPreference('Graphs_Categories', $sCategories));

		$aCategories = $this->oDatabase->selectRows('sql/analysis/get_categories.sql', $this->iUserId, $sCategories);
		if ($aCategories === false || $aCategories === null) return error('Getting categories', RESPONSE_SERVER_ERROR);

		return XML::serialize(false, 'Analysis.setGraph', 'From', $sFrom, 'To', $sTo, 'ViewBy', $sViewBy, 'GraphType', $sGraphType, 'TotalExpenses', $bTotalExpenses, 'TotalIncome', $bTotalIncome, 'Categories', $sCategories) . XML::fromArrays('Category', $aCategories) . '</Analysis.setGraph>';
	}

	public function getGraph($iWidth, $iHeight)
	{
		setOutputType(RESPONSE_IMAGE);

		$this->oSession->aAnalysisMap = array();
		$aAnalysis = $this->oSession->aAnalysis;

		$sTo = $aAnalysis['To'];
		$sFrom = $aAnalysis['From'];
		$sCategories = $aAnalysis['Categories'];
		$bBar = ($aAnalysis['GraphType'] == 'BAR');
		$bTotals = ($aAnalysis['ViewBy'] == 'TOTALS');
		$bTotalIncome = ($aAnalysis['TotalIncome'] == '1');
		$bTotalExpenses = ($aAnalysis['TotalExpenses'] == '1');

		if ($bTotals) $this->getOverAllTotals($bBar, $iWidth, $iHeight, $sFrom, $sTo, $bTotalIncome, $bTotalExpenses);
		else          $this->getCategoryGraph($bBar, $iWidth, $iHeight, $sFrom, $sTo, $sCategories);

		return "";
	}

	public function getCategoryGraph($bBar, $iWidth, $iHeight, $sFrom, $sTo, $sCategories)
	{
		//Get values: YearMonth, CategoryId, Total
		$aResults = $this->oDatabase->selectRows('sql/analysis/get_expenses_by_category.sql', $this->iUserId, $sFrom, $sTo, $sCategories);
		if ($aResults === false || $aResults === null) return error('While getting total category expenses.', RESPONSE_SERVER_ERROR);

		$aIndexes = explode(',', $sCategories);
		$aColor = array(0x6495ED /*blue*/, 0xFF4500 /*red*/, 0x9ACD32 /*green*/, 0xFFD700 /*yellow*/);

		//Place each category in it's own array.
		$aaResults = array();
		foreach ($aIndexes as $sId)
		{
			$aRows = array();
			foreach ($aResults as $aResult) if ($aResult['CategoryId'] == $sId) $aRows[] = $aResult;
			$aaResults[$sId] = $aRows;
		}

		//Calculate Axises.
		$iMax = Utility::arrayMax($aResults, array('TotalABS'));
		$aXAxis = $this->getMonths($sFrom, $sTo);
		$aYAxis = $this->getAmounts($iMax, $iHeight-50, 10, $this->oSession->get('currency'));

		//Draw Bar graph
		if ($bBar)
		{
			$oGraph = new BarGraph($iWidth, $iHeight);
			$oGraph->draw($aXAxis, $aYAxis, $iMax);

			$iColor = 0;
			$iBarIndex = 0;
			$iBarCount = count($aaResults);
			foreach ($aaResults as $sName=>$aResults)
			{
				$oGraph->plot($aResults, $sName, 'YearMonth', 'Total', $aColor[$iColor++], $iBarCount, $iBarIndex++, $this->oSession->aAnalysisMap);
			}
			$oGraph->save();
		}
		else
		{
			$oGraph = new LineGraph($iWidth, $iHeight);
			$oGraph->draw($aXAxis, $aYAxis, $iMax);

			$iColor = 0;
			foreach ($aaResults as $sId=>$aResults)
			{
				$oGraph->plot($aResults, $sId, 'YearMonth', 'Total', $aColor[$iColor++], $this->oSession->aAnalysisMap);
			}
			$oGraph->save();
		}
	}

	public function getOverAllTotals($bBar, $iWidth, $iHeight, $sFrom, $sTo, $bTotalIncome, $bTotalExpenses)
	{
		//Get values: YearMonth, TotalExpenses, FixedExpenses, VariableExpenses, TotalIncome
		$aResults = $this->oDatabase->selectRows('sql/analysis/get_total_income_expenses.sql', $this->iUserId, $sFrom, $sTo);
		if ($aResults === false || $aResults === null) return error('While getting total income expenses.', RESPONSE_SERVER_ERROR);

		$aColor = array();
		$aIndexes = array();
		if ($bTotalIncome)
		{
			$aColor = array_merge($aColor, array(0x6495ED /*blue*/));
			$aIndexes = array_merge($aIndexes, array('Total Income'=>'TotalIncome', 'TEMP'=>'TotalIncomeABS'));
		}
		if ($bTotalExpenses)
		{
			$aColor = array_merge($aColor, array(0xFF4500 /*red*/, 0x9ACD32 /*green*/, 0xFFD700 /*yellow*/));
			$aIndexes = array_merge($aIndexes, array('Total Expenses'=>'TotalExpenses', 'Variable Expenses'=>'VariableExpenses', 'Fixed Expenses'=>'FixedExpenses'));
		}

		//Calculate Axises.
		$iMax = Utility::arrayMax($aResults, $aIndexes);
		$aXAxis = $this->getMonths($sFrom, $sTo);
		$aYAxis = $this->getAmounts($iMax, $iHeight-50, 10, $this->oSession->get('currency'));

		//Remove the temp value.
		unset($aIndexes['TEMP']);

		//Draw Bar graph
		if ($bBar)
		{
			$oGraph = new BarGraph($iWidth, $iHeight);
			$oGraph->draw($aXAxis, $aYAxis, $iMax);

			$iColor = 0;
			$iBarIndex = 0;
			$iBarCount = count($aIndexes);

			foreach ($aIndexes as $sName=>$sXKey)
			{
				$oGraph->plot($aResults, $sName, 'YearMonth', $sXKey, $aColor[$iColor++], $iBarCount, $iBarIndex++, $this->oSession->aAnalysisMap);
			}
			$oGraph->save();
		}
		//Draw line graph
		else
		{
			$oGraph = new LineGraph($iWidth, $iHeight);
			$oGraph->draw($aXAxis, $aYAxis, $iMax);

			$iColor = 0;
			foreach ($aIndexes as $sName=>$sXKey)
			{
				$oGraph->plot($aResults, $sName, 'YearMonth', $sXKey, $aColor[$iColor++], $this->oSession->aAnalysisMap);
			}
			$oGraph->save();
		}
	}


	public function getMap()
	{
		$aMaps = $this->oSession->aAnalysisMap;
		foreach($aMaps as &$aMap) $aMap['Map'] = implode(',', $aMap['Map']);

		return XML::serialize(false, 'Analysis.getMap', 'ViewBy', $this->oSession->aAnalysis['ViewBy']).XML::fromArrays('Map', $aMaps).'</Analysis.getMap>';
	}

	//Gets the X-axis intervals
	private function getAmounts($iMax, $iHeight, $iCount, $sCurrency)
	{
		$aAmounts = array();

		$iValue = 0;
		$iInterval = $iMax/($iCount-1);
		for ($i = 0; $i < $iCount; $i++)
		{
			$aAmounts[] = $sCurrency . ($iMax < 10 ? number_format($iValue, 1, '.', ',') : number_format($iValue, 0, '.', ',')) ;
			$iValue += $iInterval;
		}

		return $aAmounts;
	}

	//Gets the Y-axis intervals
	private function getMonths($sFrom, $sTo)
	{
		$iToYear; $iToMonth;
		$iFromYear; $iFromMonth;

		$aMonths = array();
		$aMonthNames = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

		Utility::getDateParts($sTo, $iToYear, $iToMonth);
		Utility::getDateParts($sFrom, $iFromYear, $iFromMonth);

		//Put the first value in.
		$aMonths[Utility::dateToString($iFromYear, $iFromMonth, 1)] = $iFromYear . ' ' . $aMonthNames[$iFromMonth];
		while (!($iFromMonth++ >= $iToMonth-1 && $iFromYear >= $iToYear))
		{
			if ($iFromMonth > 12)
			{
				$iFromYear++;
				$iFromMonth = 1;
				$aMonths[Utility::dateToString($iFromYear, $iFromMonth, 1)] = $iFromYear . ' ' . $aMonthNames[$iFromMonth];
			}
			else $aMonths[Utility::dateToString($iFromYear, $iFromMonth, 1)] = $aMonthNames[$iFromMonth];
		}
		$aMonths[Utility::dateToString($iFromYear, $iFromMonth, 1)] = $iFromYear . ' ' . $aMonthNames[$iFromMonth];

		return $aMonths;
	}
}

?>