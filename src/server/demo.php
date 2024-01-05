<?
define('RESPONSE_DEMO_RESTRICTION', '<Error Type="DEMO_RESTRICTION" Message="This operation is restricted because you are in the demo."/>');

/**
 * @author Lisa Wall
 * @date 2009-06-18
 */
class Demo
{
	public $oDatabase;

	public function __construct()
	{
	}

	public function start()
	{
		global $oSession;

		//Clear the session.
		$oSession = new Session(true);
		$this->oDatabase = $oSession->oDatabase;

		//Load the xml file.
		$oDocument = XML::load('content/demo_template.xml');

		//Creat user email
		$sEmail = 'demo_'. microtime(true).'@spendingprofile.com';

		//Delete the user if it already exists.
		$this->clean($sEmail);

		//Create the account and get UserId.
		XML::setAttributeDefault($oDocument, 'Email', $sEmail);
		XML::setAttributeDefault($oDocument, 'CreatedOn', date('Y-m-d H:i:s'));
		XML::setAttributeDefault($oDocument, 'Location', 'Unknown');
		XML::setAttributeDefault($oDocument, 'LastIP', '');
		XML::setAttributeDefault($oDocument, 'LastLogin', '');

		$sUserId = $this->oDatabase->insertXML('sql/demo/add_user.sql', 'users', $oDocument, array('Email', 'Password', 'FirstName', 'LastName', 'Currency', 'Preference', 'CreatedOn', 'Location', 'LastIP', 'LastLogin'));
		if ($sUserId === null || $sUserId === false) return error('Creating user');

		//Import Vendors, Accounts, Categories, and Transactions.
		$this->addAll('sql/demo/add_vendor.sql', $sUserId, XML::getElementByTagName($oDocument, 'Vendors'), array('UserId', 'Name'));
		$this->addAll('sql/demo/add_account.sql', $sUserId, XML::getElementByTagName($oDocument, 'Accounts'), array('UserId', 'Name'));
		$this->addAll('sql/demo/add_category.sql', $sUserId, XML::getElementByTagName($oDocument, 'Categories'), array('UserId', 'Name', 'BudgetAmount', 'BudgetActive'));

		//Add transactions and update dates to reflect on this month.
		$sRelativeFrom = $oDocument->getAttribute('RelativeFrom');
		$oTransaction = XML::getElementByTagName($oDocument, 'Transactions');
		for ($oTransaction = $oTransaction->firstChild; $oTransaction != null; $oTransaction = $oTransaction->nextSibling)
		{
			if ($oTransaction->nodeType != XML_ELEMENT_NODE) continue;
			$this->addTransaction($sUserId, $oTransaction, $sRelativeFrom);
		}

		//Set graph preference for user.
		$aCategoryIds = $this->getCategoryIds($sUserId, array('Car'.TAG_DELIMITER.'Gas', 'Phone'.TAG_DELIMITER.'Cell Phone', 'Food'.TAG_DELIMITER.'Groceries', 'Food'.TAG_DELIMITER.'Restaurants'));
		$sPreference = $oDocument->getAttribute("Preference") . ';Graphs_Categories:'.implode(',',$aCategoryIds);
		$this->oDatabase->update('sql/users/update_preferences.sql', $sUserId, $sPreference);

		$oUser = new User();
		return $oUser->login_demo($sEmail, 'test');
	}

	private function getCategoryIds($sUserId, $aCategories)
	{
		$aIds = array();
		foreach ($aCategories as $sName) $aIds[] = Tag::getTagId($sUserId, 'categories', $sName, $bCreated, $this->oDatabase);
		return $aIds;
	}

	private function addAll($sSQL, $sUserId, $oParent, $aMap)
	{
		for ($oElement = $oParent->firstChild; $oElement != null; $oElement = $oElement->nextSibling)
		{
			if ($oElement->nodeType != XML_ELEMENT_NODE) continue;

			$oElement->setAttribute('UserId', $sUserId);
			$this->oDatabase->insertXML($sSQL, null, $oElement, $aMap);
		}
	}

	private function addTransaction($sUserId, $oTransaction, $sRelativeFrom)
	{
		//Populate VendorId, 'AccountId', 'CateogryId'
		$oTransaction->setAttribute('UserId', $sUserId);
		$oTransaction->setAttribute('VendorId', Tag::getTagId($sUserId, 'vendors', $oTransaction->getAttribute('Vendor'), $bCreated, $this->oDatabase));
		$oTransaction->setAttribute('AccountId', Tag::getTagId($sUserId, 'accounts', $oTransaction->getAttribute('Account'), $bCreated, $this->oDatabase));
		$oTransaction->setAttribute('CategoryId', Tag::getTagId($sUserId, 'categories', $oTransaction->getAttribute('Category'), $bCreated, $this->oDatabase));

		if (!$oTransaction->hasAttribute('EnteredOn')) $oTransaction->setAttribute('EnteredOn', date('Y-m-d H:i:s'));

		//TODO: modify transaction date.
		$oTransaction->setAttribute('Date', Utility::getRelativeDate($oTransaction->getAttribute('Date'), $sRelativeFrom, date('Y-m-d')));

		if ($oTransaction->hasAttribute('id'))
		{
			$this->oDatabase->insertXML('sql/demo/add_transaction_id.sql', null, $oTransaction, array('Id', 'UserId', 'VendorId', 'AccountId', 'CategoryId', 'Date', 'Debit', 'Fixed', 'Amount', 'Notes', 'EnteredOn'));
		}
		else
		{
			$this->oDatabase->insertXML('sql/demo/add_transaction.sql', null, $oTransaction, array('UserId', 'VendorId', 'AccountId', 'CategoryId', 'Date', 'Debit', 'Fixed', 'Amount', 'Notes', 'EnteredOn'));
		}

	}

	private function clean($sEmail)
	{
		//Get Id.
		$sId = $this->oDatabase->selectValue('sql/demo/get_id.sql', $sEmail);
		if ($sId == null || $sId == false) return;

		//Delete transactions
		$this->oDatabase->delete('sql/demo/delete_tag.sql', 'transactions', $sId);

		//Delete Vendors, Accounts, Categories.
		$this->oDatabase->delete('sql/demo/delete_tag.sql', 'vendors', $sId);
		$this->oDatabase->delete('sql/demo/delete_tag.sql', 'accounts', $sId);
		$this->oDatabase->delete('sql/demo/delete_tag.sql', 'categories', $sId);

		//Delete the user while will delete the transactions.
		$this->oDatabase->delete('sql/demo/delete_user.sql', $sId);
	}

	public function cleanAll()
	{
		$sEmail = 'charbel.choueiri@live.ca';

		global $oSession;
		$oDatabase = $oSession->oDatabase;

		//Get Id.
		$sId = $oDatabase->selectValue('sql/demo/get_id.sql', $sEmail);
		if ($sId == null || $sId == false) return;

		//Delete transactions
		$oDatabase->delete('sql/demo/delete_tag.sql', 'transactions', $sId);

		//Delete Vendors, Accounts, Categories.
		$oDatabase->delete('sql/demo/delete_tag.sql', 'vendors', $sId);
		$oDatabase->delete('sql/demo/delete_tag.sql', 'accounts', $sId);
		$oDatabase->delete('sql/demo/delete_tag.sql', 'categories', $sId);

		//Delete the user while will delete the transactions.
		$oDatabase->delete('sql/demo/delete_user.sql', $sId);

		return RESPONSE_SERVER_OK;
	}

}

?>