<?
/**
 * @author Lisa Wall
 * @date 2009-03-25
 */
class Goal
{
	private $iUserId = 0;
	private $oSession = null;
	private $oDatabase = null;

	private $aValidate = array('Name'=>'TYPE:STRING;MIN:1;MAX:32', 'From'=>'TYPE:DATE', 'To'=>'TYPE:DATE', 'Amount'=>'TYPE:FLOAT;MIN:0;MAX:9999999999.99', 'Category'=>'TYPE:STRING;MIN:1;MAX:128', 'Note'=>'TYPE:STRING;MAX:128');
	private $aColumnMap = array('Name'=>'name', 'Category'=>'category_id', 'Note'=>'notes', 'Amount'=>'amount', 'From'=>'date_start', 'To'=>'date_mature');

	public function __construct()
	{
		global $oSession;

	   $this->iUserId = $oSession->iUserId;
	   $this->oSession = $oSession;
	   $this->oDatabase = $oSession->oDatabase;
	}

	/**
	 * Returns all goals with their details along with progress for each goal.
	 */
	public function getAll()
	{
		//Get user tag names.
		$aGoals = $this->oDatabase->selectRows('sql/goals/get_all.sql', $this->iUserId);
		if ($aGoals === false || $aGoals === null) return RESPONSE_SERVER_ERROR;

		//Return list.
		return '<Goal.getAll>'.XML::fromArrays('Goal', $aGoals).'</Goal.getAll>';
	}

	public function delete($iId)
	{
		//Ensure goal id belongs to user.
		if ($this->oDatabase->selectValue('sql/goals/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to delete goal not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Delete transaction.
		if (!$this->oDatabase->delete('sql/goals/delete.sql', $this->iUserId, $iId)) return error('Delete goal from database.', RESPONSE_SERVER_ERROR);

		//Return deleted response.
		return XML::serialize(true, 'Goal.delete', 'Id', $iId);
	}

	public function updateField($iId, $sField, $sValue)
	{
		//Map the field value to the database column name.
		$sColumnName = $this->aColumnMap[$sField];

		//Validate transaction id belongs to user.
		if ($this->oDatabase->selectValue('sql/goals/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to update goal not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//If category, vendor, account then get id from string.
		if ($sField == 'Category')
		{
			//Get the category id.
			$iCategoryId = $this->getCategoryId($sCategory);
			if      ($iCategoryId === null) return RESPONSE_ALREADY_EXISTS;
			else if ($iCategoryId === flse) return error('Getting or creating category.', RESPONSE_SERVER_ERROR);
		}

		//Validate Field and Value
		if (!Validate::type($sValue, $this->aValidate[$sField])) return error("User entered invalid field: ($sField = $sValue)", RESPONSE_INVALID_ARGUMENTS);

		//Apply the change.
		if (!$this->oDatabase->update('sql/goals/update_field.sql', $this->iUserId, $iId, $sColumnName, $sValue)) return error('While updating goal field.', RESPONSE_SERVER_ERROR);

		//Return response.
		return $sResult = XML::serialize(true, 'Transaction.update', 'Id', $iId, 'Field', $sField, 'Value', $sValue);
	}

	public function update($iId, $sName, $sFrom, $sTo, $iAmount, $sCategory, $sNote)
	{
		//Validate goal belongs to user.
		if ($this->oDatabase->selectValue('sql/goals/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to update goal not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Get the category id.
		$iCategoryId = $this->getCategoryId($sCategory);
		if      ($iCategoryId === null) return RESPONSE_ALREADY_EXISTS;
		else if ($iCategoryId === flse) return error('Getting or creating category.', RESPONSE_SERVER_ERROR);

		//Execute update query.
		if (!$this->oDatabase->update('sql/goals/update.sql', $this->iUserId, $iId, $sName, $sFrom, $sTo, $iAmount, $iCategoryId, $sNote)) return error('While updating goal.', RESPONSE_SERVER_ERROR);

		//Return the goal
		return XML::serialize(true, 'Goal.update', 'Id', $iId, 'Name', $sName, 'From', $sFrom, 'To', $sTo, 'Amount', $iAmount, 'Category', $sCategory, 'Note', $sNote);
	}

	/**
	 * Validate that from is less than to date.
	 */
	public function add($sName, $sFrom, $sTo, $iAmount, $sCategory, $sNote)
	{
		//TODO: Ensure from is less than to date.

		//Ensure the name does not already exist.
		if ($this->oDatabase->selectValue('sql/goals/get_id.sql', $this->iUserId, $sName) != null) return RESPONSE_ALREADY_EXISTS;

		//Get the category id.
		$iCategoryId = $this->getCategoryId($sCategory);
		if      ($iCategoryId === null) return RESPONSE_ALREADY_EXISTS;
		else if ($iCategoryId === flse) return error('Getting or creating category.', RESPONSE_SERVER_ERROR);

		//Add the goal to the database.
		$iGoalId = $this->oDatabase->insert('sql/goals/add.sql', 'goals', $this->iUserId, $sName, $sFrom, $sTo, $iAmount, $iCategoryId, $sNote);
		if ($iGoalId === null || $iGoalId === false) return RESPONSE_SERVER_ERROR;

		//Return the goal
		return XML::serialize(true, 'Goal.add', 'Id', $iGoalId, 'Name', $sName, 'From', $sFrom, 'To', $sTo, 'Amount', $iAmount, 'Category', $sCategory, 'Note', $sNote);
	}

	private function getCategoryId($sName)
	{
		//Get the category id.
		$bCreated = false;
		if ( ($iId = Category::getId($sCategory, $bCreated)) === 0) return error('Getting or creating category.', false);

		//Check if it is bying used.
		if ($this->oDatabase->selectValue('sql/goals/get_id.sql', $this->iUserId, $iId) != null) return null;

		//Return category id.
		return $iId;
	}
}

?>