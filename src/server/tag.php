<?

define('TAG_DELIMITER', ':');

/**
 * @author Lisa Wall
 * @date 2009-03-16
 */
class Tag
{
	protected $sTag = '';
	protected $sTable = '';

	protected $iUserId = 0;
	protected $oSession = null;
	protected $oDatabase = null;

	public function __construct($sTag, $sTable)
	{
		global $oSession;

		$this->iUserId = $oSession->iUserId;
		$this->oSession = $oSession;
		$this->oDatabase = $oSession->oDatabase;

		$this->sTag = $sTag;
		$this->sTable = $sTable;
	}

	public function getAll()
	{
		//Get user tag names.
		$aNames = $this->oDatabase->selectRows('sql/'.$this->sTable.'/get_all_count.sql', $this->iUserId);
		if ($aNames === false || $aNames === null) return RESPONSE_SERVER_ERROR;

		//Return list.
		return '<'.$this->sTag.'.getAll>'.XML::fromArrays($this->sTag, $aNames).'</'.$this->sTag.'.getAll>';
	}

	public function getTree()
	{
		//Get user tag names.
		$aNames = $this->oDatabase->selectRows('sql/'.$this->sTable.'/get_all.sql', $this->iUserId);
		if ($aNames === false || $aNames === null) return RESPONSE_SERVER_ERROR;

		//Make array into an array tree.
		$oNames = $this->expand($aNames, $this->sTag.'.getTree', $this->sTag, array('Id'));

		return $oNames->ownerDocument->saveXML($oNames);
	}

	public function add($sName)
	{
		//Clean the name.
		$sName = Tag::clean($sName);

		//Check if exists.
		$iNameId = $this->oDatabase->selectValue('sql/'.$this->sTable.'/get_id.sql',$this->iUserId, $sName);
		$bCreated = ($iNameId === false || $iNameId === null);

		//If does not exist then add it.
		if($bCreated)
		{
			$iNameId = $this->oDatabase->insert('sql/'.$this->sTable.'/add.sql', $this->sTable, $this->iUserId, $sName);
			if ($iNameId === false || $iNameId === null) return error('While adding new tag.', RESPONSE_SERVER_ERROR);
		}
		else return RESPONSE_ALREADY_EXISTS;

		return XML::serialize(true, $this->sTag.'.add', 'Id', $iNameId, 'Name', $sName, 'Created', $bCreated);
	}

	public function delete($iId)
	{
		//Ensure specified id belongs to user.
		if ($this->oDatabase->selectValue('sql/'.$this->sTable.'/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to delete tag not belonging to them.', RESPONSE_INVALID_ARGUMENTS);
		
		$iTotal = $this->oDatabase->selectValue('sql/'.$this->sTable.'/is_used.sql', $this->iUserId, $iId);
		if ($iTotal === FALSE || $iTotal === null) return error('Failed to execute is_used query while attempting to delete an entry.', RESPONSE_SERVER_ERROR);
		
		if ($iTotal != '0') return RESPONSE_INVALID_ARGUMENTS;

		//Delete transaction.
		if (!$this->oDatabase->delete('sql/'.$this->sTable.'/delete.sql', $this->iUserId, $iId)) return error('Delete tag from database.', RESPONSE_SERVER_ERROR);

		//Return deleted response.
		return XML::serialize(true, $this->sTag.'.delete', 'Id', $iId);
	}

	public function rename($iId, $sName, $bReplace)
	{
		$sName = Tag::clean($sName);

		//Ensure specified id belongs to user.
		if ($this->oDatabase->selectValue('sql/'.$this->sTable.'/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to rename tag not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Ensure Name does not already exist.
		$iNameId = $this->oDatabase->selectValue('sql/'.$this->sTable.'/get_id.sql', $this->iUserId, $sName);

		if ($iId == $iNameId)
		{
			//Do nothing, just rename it.
		}
		else if ($iNameId !== false && $iNameId !== null)
		{
			//if replace then
			if ($bReplace)
			{
				//Replace all transactions with the specified ID.
				if (!$this->oDatabase->update('sql/'.$this->sTable.'/replace.sql', $this->iUserId, $iNameId, $iId)) return error('While replacing transactions tag.', RESPONSE_SERVER_ERROR);

				//Remove the found tag.
				if (!$this->oDatabase->delete('sql/'.$this->sTable.'/delete.sql', $this->iUserId, $iNameId)) return error('Removing replaced tag.', RESPONSE_SERVER_ERROR);

				//Rename the tag to the new name.
				if (!$this->oDatabase->update('sql/'.$this->sTable.'/update.sql', $this->iUserId, $iId, $sName)) return error('While renaming tag.', RESPONSE_SERVER_ERROR);

				//Return the replaced value.
				return XML::serialize(true, $this->sTag.'.rename', 'Id', $iId, 'Name', $sName, 'Replaced', $iNameId);
			}
			else return XML::serialize(true, $this->sTag.'.rename', 'Id', $iId, 'Name', $sName, "Type", "ALREADY_EXISTS");
		}

		//Rename the tag to the new name.
		if (!$this->oDatabase->update('sql/'.$this->sTable.'/update.sql', $this->iUserId, $iId, $sName)) return error('While renaming tag.', RESPONSE_SERVER_ERROR);

		//Return response.
		return XML::serialize(true, $this->sTag.'.rename', 'Id', $iId, 'Name', $sName);
	}


	public function getOrCreateId($sName, &$bCreated)
	{
		return $this->getTagId($this->iUserId, $this->sTable, $sName, $bCreated, $this->oDatabase);
	}

	public static function getTagId($iUserId, $sTable, $sName, &$bCreated, $oDatabase)
	{
		//Clean the name.
		$sName = Tag::clean($sName);

		//Get the id of the specified name.
		$iNameId = $oDatabase->selectValue('sql/'.$sTable.'/get_id.sql',$iUserId, $sName);
		$bCreated = ($iNameId === false || $iNameId === null);

		//If does not exist then add it.
		if($bCreated)
		{
			$iNameId = $oDatabase->insert('sql/'.$sTable.'/add.sql', $sTable, $iUserId, $sName);

			//Make sure there was no errors adding value.
			if ($iNameId === false || $iNameId === null) return error('While adding new tag.', 0);
		}

		return $iNameId;
	}

	public static function clean($sName)
	{
		$sNameClean = '';

		//If string is empty then return empty string.
		if (strlen($sName) == 0) return $sNameClean;

		//Split the name using delimiter.
		$aName = explode(TAG_DELIMITER, $sName);

		//Add the root category.
		$sNameClean = trim($aName[0]);

		//For each sub category trim it and concatinate it to a new clean string.
		for ($i = 1; $i < count($aName); $i++) $sNameClean .= TAG_DELIMITER.trim($aName[$i]);

		//return cleaned values.
		return $sNameClean;
	}

	public static function expand($aTags, $sParentName, $sNodeName, $aAttributes)
	{
		$oDocument = new DOMDocument('1.0');
		$oRoot = $oDocument->appendChild($oDocument->createElement($sParentName));

		foreach ($aTags as $aTag)
		{
			$aNames = explode (TAG_DELIMITER, $aTag['Name']);

			$oCurrent = $oRoot;
			foreach ($aNames as $sName)
			{
				if ( ($oElement = XML::getElementByAttribute($oCurrent, 'Name', $sName)) == null)
				{
					$oElement = $oCurrent->appendChild($oDocument->createElement($sNodeName));
					$oElement->setAttribute('Name', utf8_encode($sName));
				}

				$oCurrent = $oElement;
			}

			foreach ($aAttributes as $sAttribute) $oCurrent->setAttribute($sAttribute, $aTag[$sAttribute]);
		}

		return $oRoot;
	}


	public function expenses()
	{
		if ($this->oSession->sFrom == '' || $this->oSession->sTo == '') return error('Must get transactions first.', RESPONSE_INVALID_REQUEST);

		$aExpenses = $this->oDatabase->selectRows('sql/graphs/get_'.$this->sTable.'.sql', $this->iUserId, $this->oSession->sFrom, $this->oSession->sTo, array(0, $this->oSession->sFilter_SQL), '');

		$iTotal = 0;
		foreach ($aExpenses as $aExpens)  $iTotal += floatval($aExpens['Total']);
		foreach ($aExpenses as &$aExpens)
		{
			$iValue = $aExpens['Total'];

			$aExpens['Total'] = number_format($iValue, 2, '.', ',');
			$aExpens['Percent'] = number_format(100 * ($iValue/$iTotal), 1);
		}

		return '<Tag.expenses Total="' . $iTotal . '">' . XML::fromArrays('Tag', $aExpenses) . '</Tag.expenses>';
	}

}

class Vendor extends Tag
{
	function __construct()
	{
		parent::__construct('Vendor', 'vendors', 32);
	}

	public static function getId($sName, &$bCreated)
	{
		$oVendor = new Vendor();
		return $oVendor->getOrCreateId($sName, $bCreated);
	}

	public function setMap($sVendor, $sMap)
	{
	}

	public function removeMap($sVendor)
	{
	}
}

class Account extends Tag
{
	function __construct()
	{
		parent::__construct('Account', 'accounts', 64);
	}

	public static function getId($sName, &$bCreated)
	{
		$oAccount = new Account();
		return $oAccount->getOrCreateId($sName, $bCreated);
	}
}

class Category extends Tag
{
	function __construct()
	{
		parent::__construct('Category', 'categories', 128);
	}

	public static function getId($sName, &$bCreated)
	{
		$oCategory = new Category();
		return $oCategory->getOrCreateId($sName, $bCreated);
	}
}

?>