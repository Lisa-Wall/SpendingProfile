<?
/**
 * @author Lisa Wall
 * @date 2009-03-16
 */
class Transaction
{
	private $iUserId;
	private $oSession = null;
	private $oDatabase = null;

	private $aValidate = array('Category'=>'TYPE:STRING;MIN:1;MAX:128', 'Vendor'=>'TYPE:STRING;MIN:1;MAX:32', 'Account'=>'TYPE:STRING;MIN:1;MAX:64', 'Fixed'=>'TYPE:BOOLEAN;RETURN:STRING_INT', 'Debit'=>'TYPE:BOOLEAN;RETURN:STRING_INT', 'Date'=>'TYPE:DATE', 'Amount'=>'TYPE:FLOAT;MIN:0;MAX:9999999999.99', 'Notes'=>'TYPE:STRING;MAX:128');
	private $aColumnMap = array('Id'=>'Id', 'Vendor'=>'vendor_id', 'Account'=>'account_id', 'Category'=>'category_id', 'Date'=>'date', 'Debit'=>'debit', 'Fixed'=>'fixed', 'Amount'=>'amount', 'Notes'=>'notes');
	private $aColumnSortMap = array('Id'=>'Id', 'Vendor'=>'vendors.name', 'Account'=>'accounts.name', 'Category'=>'categories.name', 'Date'=>'date', 'Debit'=>'debit', 'Fixed'=>'fixed', 'Amount'=>'amount', 'Notes'=>'notes');

	public function __construct()
	{
		global $oSession;

		$this->iUserId = $oSession->iUserId;
		$this->oSession = $oSession;
		$this->oDatabase = $oSession->oDatabase;
	}

	public function getPeriodFilter($sFrom, $sTo, $sFilter, $sOrderBy, $sOrderIn)
	{
		//Validate the start and end date.
		$this->oSession->sTo = $sTo = ($sTo == null && $sFrom != null? date('Y-m-t', strtotime($sFrom)) : ($sTo == null ? $this->oSession->sTo : $sTo));
		$this->oSession->sFrom = $sFrom = ($sFrom == null ? $this->oSession->sFrom : $sFrom);
		$this->oSession->sOrderBy = ($sOrderBy == null ? $this->oSession->sOrderBy : $sOrderBy);
		$this->oSession->sOrderIn = ($sOrderIn == null ? $this->oSession->sOrderIn : $sOrderIn);

		//Process the filter string.
		if ($sFilter !== null)
		{
			$this->oSession->sFilter_SQL = Filter::process($sFilter);
			$this->oSession->sFilter = $sFilter;
		}

		//Do the sql query.
		$aTransactions = $this->oDatabase->selectRows('sql/transactions/get_filter.sql', $this->iUserId, $this->aColumnSortMap[$this->oSession->sOrderBy], $this->oSession->sOrderIn, $sFrom, $sTo, array(0, $this->oSession->sFilter_SQL));
		if ($aTransactions === false || $aTransactions === null) return error('Error getting transactions', RESPONSE_SERVER_ERROR);

		//Get totals from the database.
		$aTotals = $this->getTotals($iDebit, $iCredit, $iTotal);
		if ($aTotals === false) return error('Error getting transactions totals', RESPONSE_SERVER_ERROR);

		//Set the cash and update cash id.
		$this->oSession->aTransactions = $aTransactions;

		//Return the transactions.
		$sTransactions = XML::serialize(false, 'Transaction.getPeriodFilter', 'From', $sFrom, 'To', $sTo, 'Filter', $this->oSession->sFilter, 'Debit', $iDebit, 'Credit', $iCredit, 'Total', $iTotal, 'OrderBy', $this->oSession->sOrderBy, 'OrderIn', $this->oSession->sOrderIn);
		$sTransactions .= XML::fromArrays('Transaction', $aTransactions).'</Transaction.getPeriodFilter>';

		return $sTransactions;
	}

	/**
	 * Deletes the transaction
	 *
	 * Example execution: <Transaction.delete Id='1234'/>
	 *
	 * @param iId   defines the id of the transaction to be deleted. Must belong to logged on user.
	 *
	 * @return  <Transaction.delete Id='INTEGER' />       (If successfull).
	 *          <Error Type='SERVER_ERROR'/>              (If a server error occured).
	 *          <Error Type='INSUFFICIENT_PERMISSION' />  (If id does not belong to logged on user).
	 */
	public function delete($iId)
	{
		//Validate transaction id belongs to user.
		if ($this->oDatabase->selectValue('sql/transactions/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to delete transaction not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Delete transaction.
		if (!$this->oDatabase->delete('sql/transactions/delete.sql', $this->iUserId, $iId)) return error('Delete transaction from database.', RESPONSE_SERVER_ERROR);

		//Return deleted response.
		return XML::serialize(true, 'Transaction.delete', 'Id', $iId);
	}

	/**
	 * Delete the specified list of transactions.
	 *
	 * Example execution: <Transaction.deleteAll><Transaction Id='1234'/>...</Transaction.deleteAll>
	 *
	 * @param iId   defines the id of the transaction to be deleted. Must belong to logged on user.
	 *
	 * @return  <Transaction.deleteAll><Transaction Id='1234' Type='OK|ERROR'/></Transaction.deleteAll> (If successfull).
	 *          <Error Type='SERVER_ERROR'/>              (If a server error occured).
	 *          <Error Type='INSUFFICIENT_PERMISSION' />  (If id does not belong to logged on user).
	 */
	public function deleteAll($oTransactions)
	{
		$sResponse = '';
		$iDebit = $iCredit = $iTotal = 0;

		for ($oTransaction = $oTransactions->firstChild; $oTransaction != null; $oTransaction = $oTransaction->nextSibling)
		{
			$sId = $oTransaction->getAttribute('Id');

			//Validate transaction id belongs to user.
			if ($this->oDatabase->selectValue('sql/transactions/get_user_id.sql', $sId) != $this->iUserId)
			{
				$sResponse .= error('User attempted to delete transaction not belonging to them.', XML::serialize(true, 'Transaction', 'Id', $sId, 'Error', 'INVALID_ARGUMENTS'));
			}
			//Delete transaction.
			else if (!$this->oDatabase->delete('sql/transactions/delete.sql', $this->iUserId, $sId))
			{
				$sResponse .= error('Delete transaction from database.', XML::serialize(true, 'Transaction', 'Id', $sId, 'Error', 'SERVER_ERROR'));
			}
			else
			{
				$sResponse .= XML::serialize(true, 'Transaction', 'Id', $sId);
			}
		}

		//Get totals from the database.
		$aTotals = $this->getTotals($iDebit, $iCredit, $iTotal);
		if ($aTotals === false) return error('Error getting transactions totals', RESPONSE_SERVER_ERROR);

		return XML::serialize(false, 'Transaction.deleteAll', 'Debit', $iDebit, 'Credit', $iCredit, 'Total', $iTotal).$sResponse.'</Transaction.deleteAll>';
	}

	/**
	 * Updates the transaction
	 *
	 * Example execution: <Transaction.update Id='1234' Field='Notes' Value='Tuition'/>
	 *
	 * @param iId       specifies the id of the transaction to update. Must belong to logged on user.
	 * @param sField    specifies the transaction field to update, could be one of the following: (Account, Vendor, Category, Date, Debit, Fixed, Amount, Notes).
	 * @param sValue    contains the new value to be assigned to sField. The value will be validate according to sField.
	 *
	 * @return          <Transaction.add id='INTEGER' Field='STRING' Value='STRING' />  (If successfull).
	 *                  <Error Type='SERVER_ERROR'/>                                    (If a server error occured).
	 *                  <Error Type='INVALID_ARGUMENTS'/>                               (If specified arguments are invalid, or id does not blong to user).
	 */
	public function update($iId, $sField, $sValue)
	{
		//Indicates that the transaction is in period visiable range.
		$sDebit = '0';
		$bVisible = true;
		$sOriginalValue = trim($sValue);
		$iDebit = $iCredit = $iTotal = 0;
		$bVendorCreated = $bAccountCreated = $bCategoryCreated = false;

		//Map the field value to the database column name.
		$sColumnName = $this->aColumnMap[$sField];

		//Validate transaction id belongs to user.
		if ($this->oDatabase->selectValue('sql/transactions/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to update transaction not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//If category, vendor, account then get id from string.
		if      ($sField == 'Vendor')   $sValue = Vendor::getId($sValue, $bVendorCreated);
		else if ($sField == 'Account')  $sValue = Account::getId($sValue, $bAccountCreated);
		else if ($sField == 'Category') $sValue = Category::getId($sValue, $bCategoryCreated);
		else if ($sField == 'Date')     $bVisible = Utility::isDateInRange($sValue, $this->oSession->sFrom, $this->oSession->sTo);

		//Validate Field and Value
		if (!Validate::type($sValue, $this->aValidate[$sField])) return error("User entered invalid field: ($sField = $sValue)", RESPONSE_INVALID_ARGUMENTS);

		//If amount then apply debit and credit
		if ($sField == 'Amount')
		{
			$sDebit = (strlen($sOriginalValue) > 0 && $sOriginalValue[0] == '+' ? '0' : '1');
			if (!$this->oDatabase->update('sql/transactions/update_amount.sql', $this->iUserId, $iId, $sValue, $sDebit)) return error('While updating transaction amount field.', RESPONSE_SERVER_ERROR);
		}
		else
		{
			//Apply the change.
			if (!$this->oDatabase->update('sql/transactions/update.sql', $this->iUserId, $iId, $sColumnName, $sValue)) return error('While updating transaction field.', RESPONSE_SERVER_ERROR);
		}

		//If the field is vendor, account or category then reset the original value insted of the id.
		if ($sField == 'Vendor' || $sField == 'Account' || $sField == 'Category') $sValue = $sOriginalValue;
		else if ($sField == 'Amount') $sValue = ($sDebit == '1' ? '' : '+') . number_format($sValue, 2, '.', '');

		//Get totals from the database.
		$aTotals = $this->getTotals($iDebit, $iCredit, $iTotal);
		if ($aTotals === false) return error('Error getting transactions totals', RESPONSE_SERVER_ERROR);

		//Return response.
		return $sResult = XML::serialize(true, 'Transaction.update', 'Id', $iId, 'Field', $sField, 'Value', $sValue, 'Visible', ($bVisible ? 'true' : 'false'), 'Debit', $iDebit, 'Credit', $iCredit, 'Total', $iTotal, 'VendorCreated', $bVendorCreated, 'AccountCreated', $bAccountCreated, 'CategoryCreated', $bCategoryCreated);
	}

	/**
	 * Adds a new transaction to the logged in user
	 *
	 * Example execution: <Transaction.add Account='Checking' Vendor='Rogers' Category='TV' Date='2008-01-01' Debit='0' Fixed='0' Amount='1000.00' Notes='For basement.'/>
	 *
	 * @param sAccount    string name of the account. If account does not exist it is created.
	 * @param sVendor     string name of the vendor. If vendor does not exist it is created.
	 * @param sCategory   string name of the category. If category does not exist it is created.
	 * @param sDate       date of transaction in the following format yyyy-mm-dd.
	 * @param iDebit      value of 0 or 1 indicating if transaction is a debit or credit.
	 * @param iFixed      value of 0 or 1 indicating if transaction is fixed or variable.
	 * @param sAmount     a float string. If amount is negative it is change to positive. Amount is round to 2 decimal places and can not exceed 9999999999.
	 * @param sNotes      any string between 0 and 128 characters.
	 *
	 * @return            <Transaction.add id='INTEGER' />   (If successfull).
	 *                    <Error Type='SERVER_ERROR'/>       (If a server error occured).
	 *                    <Error Type='INVALID_ARGUMENTS'/>  (If specified arguments are invalid).
	 */
	public function add($sAccount, $sVendor, $sCategory, $sDate, $iDebit, $iFixed, $sAmount, $sNotes)
	{
		$sEnteredOn = date('Y-m-d H:i:s');
		$bVendorCreated = $bAccountCreated = $bCategoryCreated = false;

		//Get the id's of the Vendor, Account and Category from the database if they exist otherwise create them.
		$iVendorId   = Vendor::getId($sVendor, $bVendorCreated);
		$iAccountId  = Account::getId($sAccount, $bAccountCreated);
		$iCategoryId = Category::getId($sCategory, $bCategoryCreated);
		$bVisible    = Utility::isDateInRange($sDate, $this->oSession->sFrom, $this->oSession->sTo);

		//Validate the rest of the fields.
		if ($iVendorId == 0 || $iAccountId == 0 || $iCategoryId == 0) return error('vendor, category, Or account could not created or fetched.', RESPONSE_INVALID_ARGUMENTS);

		//Add the transaction to the databse.
		$sTransactionId = $this->oDatabase->insert('sql/transactions/add.sql', 'transactions', $this->iUserId, $iVendorId, $iAccountId, $iCategoryId, $sDate, $iDebit, $iFixed, $sAmount, $sNotes, $sEnteredOn);

		//If database error occured then return error response.
		if ($sTransactionId === null || $sTransactionId === false) return RESPONSE_SERVER_ERROR;
		
		//Call on the receipt to add if exists.
		$oReceipt = new Receipt();
		$oReceipt->append($sTransactionId, 'Receipt');

		//Return result.
		return XML::serialize(true, 'Transaction.add', 'Id', $sTransactionId, 'Account', $sAccount, 'Vendor', $sVendor, 'Category', $sCategory, 'Date', $sDate, 'Debit', $iDebit, 'Fixed', $iFixed, 'Amount', $sAmount, 'Notes', $sNotes, 'Visible', ($bVisible ? 'true' : 'false'), 'VendorCreated', $bVendorCreated, 'AccountCreated', $bAccountCreated, 'CategoryCreated', $bCategoryCreated);
	}


	public function copy($oTransactions, $sDate, $bUseFullDate)
	{
		$sEnteredOn = date('Y-m-d H:i:s');
		$sResponse = '';
		$sTransactions = '';
		$bVisible = Utility::isDateInRange($sDate, $this->oSession->sFrom, $this->oSession->sTo);

		//Create a string array of the transaction ID.
		if ($oTransactions->firstChild != null)
		{
			$sTransactions = $oTransactions->firstChild->getAttribute('Id');
			for ($oTransaction = $oTransactions->firstChild->nextSibling; $oTransaction != null; $oTransaction = $oTransaction->nextSibling) $sTransactions .= ',' . $oTransaction->getAttribute('Id');
		}
		else return RESPONSE_SERVER_OK;

		//Query for the transactions.
		$aTransactions = $this->oDatabase->selectRows('sql/transactions/get_ids.sql', $this->iUserId, $sTransactions);

		//Travers each transaction.
		foreach ($aTransactions as $aT)
		{
			//Update the date and add it to the database.
			$sNewDate = ($bUseFullDate ? $sDate : Utility::copyMonthYear($aT['Date'], $sDate));

			//Add the transaction to the databse.
			$sId = $this->oDatabase->insert('sql/transactions/add.sql', 'transactions', $this->iUserId, $aT['VendorId'], $aT['AccountId'], $aT['CategoryId'], $sNewDate, $aT['Debit'], $aT['Fixed'], $aT['Amount'], $aT['Notes'], $sEnteredOn);
			if ($sId === null || $sId === false) error('While copying transaction.', RESPONSE_SERVER_ERROR);
			else
			{
				$sResponse .= XML::serialize(true, 'Transaction', 'Id', $sId);
			}
		}

		//Return response.
		return XML::serialize(false, 'Transaction.copy', 'Date', $sDate, 'Month', date('F, Y', strtotime($sDate)), 'Visible', ($bVisible ? 'true' : 'false')).$sResponse.'</Transaction.copy>';
	}

	private function getTotals(&$iDebit, &$iCredit, &$iTotal)
	{
		//Get totals from the database.
		$aTotals = $this->oDatabase->selectRow('sql/transactions/get_totals.sql', $this->iUserId, $this->oSession->sFrom, $this->oSession->sTo, array(0, $this->oSession->sFilter_SQL));
		if ($aTotals === false || $aTotals === null) return false;

		$this->oSession->iDebit  = $iDebit  = $aTotals['Debit'];
		$this->oSession->iCredit = $iCredit = $aTotals['Credit'];
		$this->oSession->iTotal  = $iTotal  = $iCredit - $iDebit;

		return $aTotals;
	}
}

?>