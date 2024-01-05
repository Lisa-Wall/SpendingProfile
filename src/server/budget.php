<?
/**
 * @author Lisa Wall
 * @date 2009-03-16
 */
class Budget
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

	/**
	 * Returns a list of all caregories with thier budget assosiation.
	 *
	 * @return  <Budget.getAll><Category Id="INTEGER" Name="STRING" Amount="FLOAT" Visible="INTEGER"/></Budget.getAll>
	 *          <Error Type='SERVER_ERROR'/>       (If a server error occured).
	 *          <Error Type='NOT_AUTHENTICATED'/>  (If user is not logged on).
	 */
	public function getAll($sFrom, $sTo, $sAveragePeriod, $bActiveOnly, $sOrderBy, $sOrderIn, $bTree)
	{
		$this->oSession->sBudgetTo = $sTo = ($sTo == null && $sFrom != null ? date('Y-m-t', strtotime($sFrom)) : ($sTo == null ? $this->oSession->sBudgetTo : $sTo));
		$this->oSession->sBudgetFrom = $sFrom = ($sFrom == null ? $this->oSession->sBudgetFrom : $sFrom);
		$this->oSession->sBudgetAvgPeriod = $sAveragePeriod = ($sAveragePeriod == null ? $this->oSession->sBudgetAvgPeriod : $sAveragePeriod);
		$this->oSession->bBudgetActive = $bActiveOnly = ($bActiveOnly == null ? $this->oSession->bBudgetActive : $bActiveOnly);
		$this->oSession->sBudgetOrderBy = $sOrderBy = ($sOrderBy == null ? $this->oSession->sBudgetOrderBy : $sOrderBy);
		$this->oSession->sBudgetOrderIn = $sOrderIn = ($sOrderIn == null ? $this->oSession->sBudgetOrderIn : $sOrderIn);

		$sAvgTo = date('Y-m-t', strtotime('Yesterday', strtotime($sFrom)));
		$sAvgFrom = date('Y-m-1', strtotime($sAveragePeriod, strtotime($sFrom)));

		//Calculate number of months to cacluate averate.
		$iMonths = ceil((strtotime($sAvgTo) - strtotime($sAvgFrom))/2628000); //(60*60*24*30.41666)

		//Get the budget with the averages.
		$aBudget = $this->oDatabase->selectRows('sql/budget/get_average.sql', $this->iUserId, $sFrom, $sTo, $sAvgFrom, $sAvgTo, $iMonths, $bActiveOnly, $sOrderBy, $sOrderIn);
		if ($aBudget === false || $aBudget === null) return error('Getting budget information', RESPONSE_SERVER_ERROR);

		//Format the percent and average.
		foreach ($aBudget as &$oCategory)
		{
			$oCategory['Average'] = number_format($oCategory['Average'], 2, '.', ',');
			$oCategory['Percent'] = number_format($oCategory['Percent'], 1, '.', ',');
		}

		if ($bTree)
		{
			$oBudget = Tag::expand($aBudget, 'Budget.getAll', 'Category', array('Id', 'Active', 'Amount'/*, 'AverageDebit', 'AverageCredit'*/, 'Average'/*, 'Debit', 'Credit'*/, 'Total'));
			$oBudget->setAttribute('From', $sFrom);
			$oBudget->setAttribute('To', $sTo);
			$oBudget->setAttribute('OrderBy', $sOrderBy);
			$oBudget->setAttribute('OrderIn', $sOrderIn);
			$oBudget->setAttribute('ActiveOnly', $bActiveOnly);
			$oBudget->setAttribute('AveragePeriod', $sAveragePeriod);
			return $oBudget->ownerDocument->saveXML($oBudget);
		}
		else
		{
			//Return list.
			return XML::serialize(false, 'Budget.getAll', 'From', $sFrom, 'To', $sTo, 'ActiveOnly', $bActiveOnly, 'AveragePeriod', $sAveragePeriod, 'OrderBy', $sOrderBy, 'OrderIn', $sOrderIn).XML::fromArrays('Category', $aBudget).'</Budget.getAll>';
		}
	}

	/**
	 * @expample <Budget.update Id='3' Amount='100' Visible='1' />
	 */
	public function update($iId, $iAmount, $iActive)
	{
		//If iId is not 0 Ensure specified id belongs to user.
		if ($this->oDatabase->selectValue('sql/budget/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to update budget not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Update debug values.
		if (!$this->oDatabase->update('sql/budget/update.sql', $this->iUserId, $iId, $iAmount, $iActive)) return error('While updating budget.', RESPONSE_SERVER_ERROR);

		//Return response.
		return $sResult = XML::serialize(true, 'Budget.update', 'Id', $iId, 'Amount', $iAmount, 'Visible', $iActive);
	}
}

?>