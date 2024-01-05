<?
/**
 * @author Lisa Wall
 * @date 2009-07-17
 */
class Statement
{
	public $iUserId;
	public $oDatebase = null;
	
	public $aHeader = array();

	public $sTo;
	public $sFrom;
	public $sMonth;
	public $iMonth;
	
	public $sCurrency = '$';

	public $iDebit = 0;
	public $iCredit = 0;
	public $iBalance = 0;
	public $iDebitPercent = 0;
	public $iCreditPercent = 0;

	public $iVendorExpense = 0;
	public $iCategoryExpense = 0;

	public $bVendorFlat= true;
	public $bCategoryFlat = true;


	public function __construct($iUserId = null, $sCurrency = null, $oDatabase = null)
	{
		if ($iUserId !== null && $sCurrency !== null && $oDatabase !== null)
		{
			$this->iUserId = $iUserId;
			$this->oDatabase = $oDatabase;
			$this->sCurrency = $sCurrency;
		}
		else
		{
			global $oSession;
			$this->iUserId = $oSession->iUserId;
			$this->oDatabase = $oSession->oDatabase;
			$this->sCurrency = $oSession->aUser['Currency'];
		}
	}

	public function setMonth($sMonth)
	{
		$this->sMonth = $sMonth;
		$this->iMonth = strtotime($sMonth);
		$this->sTo = date('Y-m-t', $this->iMonth);
		$this->sFrom = date('Y-m-1', $this->iMonth);
		
		$this->getTotals();
	}
	
	public function email($sMonth)
	{
		$RL = "\r\n";
		$sBoundry = sha1(date('r', time()));
		$sBoundryContent = sha1(date('Y-m-d', time()));
		//$sStatementDownload = $this->download($sMonth);

		$this->setMonth($sMonth);

		$this->aHeader['X-Mailer'] = 'Spending Profile Mailer';
		$this->aHeader['Content-Type'] = "multipart/related; boundary=\"{$sBoundry}\"";

		$oStatement = $this;
		ob_start();
		include('content/en/statement_email.html');
		$sStatement = ob_get_contents();
		ob_end_clean();

		return $sStatement;
	}

	public function download($sMonth)
	{
		$this->setMonth($sMonth);
		
		$oStatement = $this;
		
		setOutputType('SpendingProfile_Statement_'.date('F_Y', $this->iMonth).'.html');
		
		ob_start();
		include('content/en/statement.html');
		$sStatement = ob_get_contents();
		ob_end_clean();

		return $sStatement;
	}

	private function getTotals()
	{
		//Get totals from the database.
		$aTotals = $this->oDatabase->selectRow('sql/transactions/get_totals.sql', $this->iUserId, $this->sFrom, $this->sTo, array(0, ''));
		if ($aTotals === false || $aTotals === null) return false;

		$this->iDebit  = $aTotals['Debit'];
		$this->iCredit = $aTotals['Credit'];
		$this->iBalance  = $this->iCredit - $this->iDebit;
		
		if ($this->iDebit > $this->iCredit)
		{
			$this->iDebitPercent = 100;
			$this->iCreditPercent = $this->iCredit/$this->iDebit*100;
		}
		else if ($this->iCredit == 0)
		{
			$this->iDebitPercent = 0;
			$this->iCreditPercent = 0;
		}
		else
		{
			$this->iDebitPercent = $this->iDebit/$this->iCredit*100;
			$this->iCreditPercent = 100;
		}
	}

	public function getBalancePie($iWidth = 250, $iHeight = 150)
	{
		$oPieGraph = new PieGraph($iWidth, $iHeight, 8, true, 10, 10);
		$oPieGraph->drawBalance($this->iDebit, $this->iCredit);
		return $oPieGraph->get();
	}

	public function getPie($sTag, $iWidth = 250, $iHeight = 200)
	{
		$iColor = 0;
		$iTotal = 0;
		$iLevels = 0;

		$aTransactions = $this->oDatabase->selectRows('sql/graphs/get_'.$sTag.'.sql', $this->iUserId, $this->sFrom, $this->sTo, array(0, ''), '');

		if ( ($sTag == 'vendors' ? $this->bVendorFlat : $this->bCategoryFlat) )
		{
			$iLevels = 1;

			foreach ($aTransactions as &$aTransaction)
			{
				$aTransaction['Children'] = array();
				$iTotal += floatval($aTransaction['Total']);
			}

			$aTags = array('Name'=>'', 'Total'=>$iTotal, 'SubTotal'=>0, 'Children'=>$aTransactions, 'StartAngle'=>0, 'EndAngle'=>360);
		}
		
		if ($sTag == 'vendors') $this->iVendorExpense = $iTotal;
		else                    $this->iCategoryExpense = $iTotal;

		$oPieGraph = new PieGraph($iWidth, $iHeight, 8, true, 25, 20);
		$oPieGraph->calculate($aTags, $iLevels, 1);

		if (count($aTransactions) > 0)
		{
			$oPieGraph->drawPie($aTags, $iLevels, 0, 0, $iColor);
			$oPieGraph->drawText($aTags, $iLevels, 0);
		}
		else
		{
			$oPieGraph->drawEmpty("No Expenses");
		}

		return $oPieGraph->get();
	}
}

?>
