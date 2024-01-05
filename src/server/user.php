<?
/**
 * @author Lisa Wall
 * @date 2009-03-28
 */
class User
{
	public $iUserId;
	public $oSession;
	public $oDatabase;

	public function __construct()
	{
		global $oSession;

		$this->iUserId = $oSession->iUserId;
		$this->oSession = $oSession;
		$this->oDatabase = $oSession->oDatabase;
	}

	public function login($sEmail, $sPassword)
	{
		//Create new session with different
		global $oSession;

		//Ensure not demo database.
		$oDatabase = $this->oSession->setDatabase(false);

		//Get user info from database.
		$aUser = $oDatabase->selectRow('sql/users/get.sql', $sEmail, md5($sPassword), date('Y-m-d H:i:s'));
		if ($aUser === false || $aUser === null)
		{
			$this->oSession->iLoginAttempts++;

			//Update the attempts
			$oDatabase->update('sql/users/attempts.sql', $sEmail, date('Y-m-d H:i:s'));

			if ($this->oSession->iLoginAttempts >= 5) return error('User attempted to log in too many times.', RESPONSE_TOO_MANY_ATTEMPTS);
			else if ($this->oSession->iLoginAttempts >= 3) return warning('Almost too many attempts to log on.', RESPONSE_MANY_ATTEMPTS_WARNING);
			else return warning('Invalid email or password', RESPONSE_INVALID_ARGUMENTS);
		}

		//Create new session.
		$oSession = new Session();

		//Set info in session.
		$oSession->setUser($aUser);

		//Update user logon info.
		$oDatabase->update('sql/users/update_last_login.sql', $aUser['Id'], date('Y-m-d H:i:s'), (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN'));

		//return ok.
		return RESPONSE_SERVER_OK;
	}

	public function login_demo($sEmail, $sPassword)
	{
		//Get user info from database.
		$aUser = $this->oDatabase->selectRow('sql/users/get.sql', $sEmail, md5($sPassword));
		if ($aUser === false || $aUser === null) return error('Invalid email or password', RESPONSE_INVALID_ARGUMENTS);

		//Set info in session.
		$this->oSession->setUser($aUser);

		//Update user logon info.
		$this->oDatabase->update('sql/users/update_last_login.sql', $aUser['Id'], date('Y-m-d H:i:s'), (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN'));

		//return ok.
		return RESPONSE_SERVER_OK;
	}

	public function logout()
	{
		global $oSession;

		$this->oSession->logout();

		$oSession = new Session();

		return RESPONSE_SERVER_OK;
	}

	public function setEmail($sEmail)
	{
		//If in demo then restricut this function
		if ($this->oSession->bDemo) return RESPONSE_DEMO_RESTRICTION;

		//Ensure that user does not already exist.
		$iUserId = $this->oDatabase->selectValue("sql/users/exists.sql", $sEmail);
		if ($iUserId !== null) return error('Email already exists.', RESPONSE_ALREADY_EXISTS);

		//Set the email in the database.
		$bResult = $this->oDatabase->update('sql/users/update_email.sql', $this->iUserId, $sEmail);
		if ($bResult === false || $bResult === null) return error('Setting email in database.', RESPONSE_SERVER_ERROR);

		//Send notification email to old address and new.
		Utility::sendEmail(array($sEmail, $this->oSession->aUser['Email']), 'content/en/email_changed.html', '%EMAIL%', $sEmail);

		//Update local info.
		$this->oSession->aUser['Email'] = $sEmail;
		$this->oSession->updateUser();

		return RESPONSE_SERVER_OK;
	}

	public function setCurrency($sCurrency)
	{
		$sCurrency = utf8_decode($sCurrency); //TODO: this is a hack.

		//Set the currency in the database.
		$bResult = $this->oDatabase->update('sql/users/update_currency.sql', $this->iUserId, $sCurrency);
		if ($bResult === false || $bResult === null) return error("Setting symbol address: $sCurrency", RESPONSE_SERVER_ERROR);

		//Update local info.
		$this->oSession->aUser['Currency'] = $sCurrency;
		$this->oSession->updateUser();

		return RESPONSE_SERVER_OK;
	}

	public function setPreference($sPreference)
	{
		//Parse current preference.
		$oHash = new Hash($this->oSession->aUser['Preference']);

		//Merge set preference with current.
		$oHash->add($sPreference);

		//Resave the preferences.
		$sPreference = $oHash->toString();

		//Set the currency in the database.
		$bResult = $this->oDatabase->update('sql/users/update_preferences.sql', $this->iUserId, $sPreference);
		if ($bResult === false || $bResult === null) return error("Setting preference: $sPreference", RESPONSE_SERVER_ERROR);

		//Update local info.
		$this->oSession->aUser['Preference'] = $sPreference;
		$this->oSession->updateUser();

		return RESPONSE_SERVER_OK;
	}

	public function setPassword($sOldPassword, $sNewPassword)
	{
		//If in demo then restricut this function
		if ($this->oSession->bDemo) return RESPONSE_DEMO_RESTRICTION;

		//Check the old password
		if ($this->oSession->aUser['Password'] != md5($sOldPassword)) return error("Invalid old password.", RESPONSE_INVALID_ARGUMENTS);

		//All is well then change the password
		$bResult = $this->oDatabase->update('sql/users/update_password.sql', $this->iUserId, md5($sNewPassword));
		if ($bResult === false || $bResult === null) return error("Resetting password.", RESPONSE_SERVER_ERROR);

		//Update session info.
		$this->oSession->aUser['Password'] = md5($sNewPassword);

		return RESPONSE_SERVER_OK;
	}

	/**
	 * Creates a new user, sets a default password and emails the user the signin information.
	 */
	public function create($sEmail)
	{
		//Ensure the databsae is not demo
		$this->oDatabase = $this->oSession->setDatabase(false);

		//Ensure that user exists.
		$iUserId = $this->oDatabase->selectValue('sql/users/exists.sql', $sEmail);
		if ($iUserId !== null) return error('Failed to create account. Email address already in use.', RESPONSE_ALREADY_EXISTS);

		//Generate password and time stamp.
		$sPassword = Utility::generatePassword();
		$sCreatedOn = date("Y-m-d H:i:s");
		$sIPAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN');

		//Create the user in the databse.
		$iUserId = $this->oDatabase->insert('sql/users/add.sql', 'users', $sEmail, md5($sPassword), $sCreatedOn, $sIPAddress);
		if ($iUserId === false) return error("Failed to create account. Technical error. Please try again later.", RESPONSE_SERVER_ERROR);

		//Send account and password to client.
		Utility::sendEmail(array($sEmail), 'content/en/create_account.html', array('%EMAIL%', '%PASSWORD%'), array($sEmail, $sPassword));

		//Create and send the email to the administrator as well.
		Utility::sendEmail(array(ADMIN_EMAIL), 'content/en/create_account_admin.html', array('%EMAIL%', '%CREATEDON%', '%IPADDRESS%'), array($sEmail, $sCreatedOn, $sIPAddress));




		//Load the xml file.
		$oDocument = XML::load('content/account_template.xml');

		//Create default categories and accounts
		$this->addAll('sql/demo/add_account.sql', $iUserId, XML::getElementByTagName($oDocument, 'Accounts'), array('UserId', 'Name'));
		$this->addAll('sql/demo/add_category.sql', $iUserId, XML::getElementByTagName($oDocument, 'Categories'), array('UserId', 'Name', 'BudgetAmount', 'BudgetActive'));

		//Set default preferences.
		$aCategoryIds = $this->getCategoryIds($iUserId, array('Car'.TAG_DELIMITER.'Gas', 'Phone'.TAG_DELIMITER.'Cell Phone', 'Food'.TAG_DELIMITER.'Groceries', 'Food'.TAG_DELIMITER.'Restaurants'));
		$sPreference = $oDocument->getAttribute("Preference") . ';Graphs_Categories:'.implode(',',$aCategoryIds);
		$this->oDatabase->update('sql/users/update_preferences.sql', $iUserId, $sPreference);



		//Return OK.
		return RESPONSE_SERVER_OK;
	}




//TODO: merge these the demo

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


//TODO::::

















	/**
	 * Resets the users password.
	 */
	public function resetPassword($sEmail)
	{
		//Ensure the databsae is not demo
		$oDatabase = $this->oSession->setDatabase(false);

		//Ensure that user exists.
		$iUserId = $oDatabase->selectValue('sql/users/exists.sql', $sEmail);
		if ($iUserId === false || $iUserId === null) return RESPONSE_SERVER_OK; //NOTE: return ok so if hacker they will not know that the email does or does not exist.

		//Generate password and time stamp.
		$sPassword = Utility::generatePassword();
		$sIPAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN');

		//Set the password in the databas.
		$bResult = $oDatabase->update('sql/users/update_password.sql', $iUserId, md5($sPassword));
		if ($bResult === false) return error("Resetting password.", RESPONSE_SERVER_ERROR);
		
		//Reset session attempts.
		$this->oSession->iLoginAttempts = 0;

		//Send new password to client.
		Utility::sendEmail(array($sEmail), 'content/en/reset_password.html', '%PASSWORD%', $sPassword);

		//Send notification to administrator.
		Utility::sendEmail(array(SMTP_ADMIN), 'content/en/reset_password_admin.html', array('%USERID%', '%EMAIL%', '%IPADDRESS%', '%PASSWORD%'), array($iUserId, $sEmail, $sIPAddress, $sPassword));

		//Return OK.
		return RESPONSE_SERVER_OK;
	}

	/**
	 * Emails the admin a feedback message form this user.
	 */
	public function sendFeedback($sFeedback)
	{
		$sUserId = $this->iUserId;
		$sEmail  = $this->oSession->aUser['Email'];

		if (strlen($sFeedback) > 0)
		{
			Utility::sendEmail(array(SMTP_FEEDBACK), 'content/en/feedback.html', array('%USER_ID%', '%EMAIL%', '%DATETIME%', '%FEEDBACK%'), array($sUserId, $sEmail, date('Y-m-d H:i:s') , $sFeedback), array('Reply-To'=>$sEmail));
		}

		return RESPONSE_SERVER_OK;
	}

	/**
	 * Sends an email to inform friend of referral.
	 */
	public function tellAFriend($sEmail, $sMessage)
	{
		//If in demo then restricut this function
		if ($this->oSession->bDemo) return RESPONSE_DEMO_RESTRICTION;

		$sUserEmail = $this->oSession->aUser['Email'];

		//Check if email is a member.
		$iUserId = $this->oDatabase->selectValue('sql/users/exists.sql', $sEmail);
		if ($iUserId !== null) return message('User is already a member of Spending Profile and therefore cannot be referred.', RESPONSE_ALREADY_EXISTS);

		//Check if email is alread a referral.
		$iId = $this->oDatabase->selectValue('sql/referrals/exists.sql', $sEmail);
		if ($iId !== null) return message('User already been referred by a member of Spending Profile. A person can only be referred once.', RESPONSE_ALREADY_EXISTS);

		//Send email to friend.
		Utility::sendEmail(array($sEmail), 'content/en/tellafriend.html', array('%EMAIL%', '%MESSAGE%'), array($sUserEmail, $sMessage));

		//Send email to admin.
		Utility::sendEmail(array(SMTP_ADMIN), 'content/en/tellafriend_admin.html', array('%USERID%', '%EMAIL%', '%REFERRAL%', '%MESSAGE%'), array($this->iUserId, $sUserEmail, $sEmail, $sMessage));

		//Add user to referral list.
		if ( ($this->oDatabase->insert('sql/referrals/add.sql', null, $this->iUserId, $sEmail, date('Y-m-d H:i:s'))) === false) return error('Unable to add email to referral list.', RESPONSE_SERVER_ERROR);

		return RESPONSE_SERVER_OK;
	}

	/**
	 * Returns a list of all referrals and thier status for this user.
	 */
	public function getReferrals()
	{
		//Get the list of referals from database.
		$aReferrals = $this->oDatabase->selectRows('sql/referrals/get_all.sql', $this->iUserId);
		if ($aReferrals === false || $aReferrals === null) return error('Unable to get referral list.', RESPONSE_SERVER_ERROR);

		//traverse it and clean it up.
		foreach ($aReferrals as &$aReferral)
		{
			$sCreatedOn = $aReferral['CreatedOn'];
			$sLastLogin = $aReferral['LastLogin'];

			if      (strlen($sCreatedOn) == 0) $aReferral['Status'] = 'NOTCREATED';
			else if (strlen($sLastLogin) == 0) $aReferral['Status'] = 'NOTLOGGEDIN';
			else                               $aReferral['Status'] = 'CREATED';

			unset($aReferral['CreatedOn']);
			unset($aReferral['LastLogin']);
		}

		//Return the list.
		return '<User.getReferrals>'.XML::fromArrays('Referral', $aReferrals).'</User.getReferrals>';
	}

	public function backup()
	{
		$sId = $this->iUserId;
		$bIds = true;

		//Get User, Account, Vendors, Categories, and Transactions.
		$aUser = $this->oDatabase->selectRow('sql/export/get_user.sql', $sId);
		$aVendors = $this->oDatabase->selectRows('sql/export/get_vendors.sql', $sId);
		$aAccounts = $this->oDatabase->selectRows('sql/export/get_accounts.sql', $sId);
		$aCategories = $this->oDatabase->selectRows('sql/export/get_categories.sql', $sId);
		$aTransactions = $this->oDatabase->selectRows('sql/export/get_transactions'.($bIds ? '_id' : '').'.sql', $sId);

		//Load the xml template file and populate it.
		$oDocument = XML::load('content/export_template.xml');

		//Populate the template.
		XML::arrayToXML($oDocument, $aUser);
		XML::arraysToXML(XML::getElementByTagName($oDocument, 'Vendors'), $aVendors, 'Vendor');
		XML::arraysToXML(XML::getElementByTagName($oDocument, 'Accounts'), $aAccounts, 'Account');
		XML::arraysToXML(XML::getElementByTagName($oDocument, 'Categories'), $aCategories, 'Category');
		XML::arraysToXML(XML::getElementByTagName($oDocument, 'Transactions'), $aTransactions, 'Transaction');

		//Finally save.
		setOutputType(RESPONSE_XML);
		return $oDocument->ownerDocument->saveXML();
	}
}

?>