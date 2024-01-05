<?
/**
* @author Lisa Wall
* @date 2009-08-03
*/
class Receipt
{
	private $iUserId = 0;
	private $oDatabase = null;
	
	public function __construct()
	{
		global $oSession;
		$this->iUserId = $oSession->iUserId;
		$this->oDatabase = $oSession->oDatabase;
	}
	
	public function get($iId)
	{
		//Validate transaction id belongs to user.
		if ($this->oDatabase->selectValue('sql/receipts/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to get receipt not belonging to them.', RESPONSE_INVALID_ARGUMENTS);
		
		//Read the receipt data.
		$sReceipt = $this->oDatabase->selectValue('sql/receipts/get.sql', $this->iUserId, $iId);
		if ($sReceipt === false || $sReceipt === null) return RESPONSE_SERVER_ERROR;

		setOutputType(RESPONSE_IMAGE_JPEG);

		//return the decode base 64 image.
		return base64_decode($sReceipt);
	}
	
	public function add($iId, $sField, $sCallback)
	{
		$sImage = '';
		$iFileSize = 0;
		$sFileName = '';
		$sResult = $this->getImage($sField, $sFileName, $iFileSize, $sImage);
		
		if ($sResult === true)
		{
			//Check that the id belongs to the user.
			if ($this->oDatabase->selectValue('sql/receipts/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to add receipt to transaction not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

			//Add the receipt to the database.
			$bResult = $this->oDatabase->update('sql/receipts/add.sql', $this->iUserId, $iId, base64_encode($sImage));
			if (!$bResult) $sResult = error('Adding receipt to transaction.', 'Error adding receipt to transaction.');
		}

		setOutputType(RESPONSE_HTML);
		return '<html><head><title>-</title></head><body><script language="JavaScript" type="text/javascript">window.parent.'.$sCallback.'("'.$sResult.'");</script></body></html>';
	}

	public function remove($iId)
	{
		//Check that the id belongs to the user.
		if ($this->oDatabase->selectValue('sql/receipts/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to remove receipt not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Read the receipt data.
		$sReceipt = $this->oDatabase->update('sql/receipts/remove.sql', $this->iUserId, $iId);
		if ($sReceipt === false || $sReceipt === null) return RESPONSE_SERVER_ERROR;

		return RESPONSE_SERVER_OK;
	}

	public function clearAll()
	{
		if (isset($_SESSION['receipts'])) unset($_SESSION['receipts']);
		return RESPONSE_SERVER_OK;
	}

	public function append($iId, $sName)
	{
		//If the receipt dose not exist then return error.
		if (!isset($_SESSION['receipts'][$sName])) return RESPONSE_INVALID_ARGUMENTS;
		
		$sImage = $_SESSION['receipts'][$sName];

		//Check that the id belongs to the user.
		if ($this->oDatabase->selectValue('sql/receipts/get_user_id.sql', $iId) != $this->iUserId) return error('User attempted to add receipt to transaction not belonging to them.', RESPONSE_INVALID_ARGUMENTS);

		//Add the receipt to the database.
		$bResult = $this->oDatabase->update('sql/receipts/add.sql', $this->iUserId, $iId, base64_encode($sImage));
		if (!$bResult) $sResult = error('Adding receipt to transaction.', 'Error adding receipt to transaction.');

		return RESPONSE_SERVER_OK;
	}

	public function upload($sName, $sField, $sCallback)
	{
		$sResult = 'OK';
		$sImage = '';
		$sFileName = '';
		$iFileSize = 0;

		//Get the image name.
		$sResult = $this->getImage($sField, $sFileName, $iFileSize, $sImage);

		//If success then add it to the session.
		if ($sResult === true) $_SESSION['receipts'][$sName] = $sImage;

		//If error then insure to unset the receipt name.
		else if (isset($_SESSION['receipts'][$sName])) unset($_SESSION['receipts'][$sName]);

		//Return the results to the call back.		
		setOutputType(RESPONSE_HTML);		
		return '<html><head><title>-</title></head><body><script language="JavaScript" type="text/javascript">window.parent.'.$sCallback.'("'.$sResult.'","'.$sFileName.'","'.number_format (($iFileSize/1024), 2).' KB");</script></body></html>';
	}
	
	private function getImage($sField, &$sFileName, &$sFileSize, &$sImage)
	{
		$sResult = true;
		$sMaxFileSize = 262144;

		if (isset($_FILES[$sField]))
		{
			//if ($_FILES[$sField]['error'] == UPLOAD_ERR_OK)
			{
				$sFileName = $_FILES[$sField]['name'];

				if (is_uploaded_file($_FILES[$sField]['tmp_name']))
				{
					$sFileSize = $_FILES[$sField]['size'];

					//TODO test file size.
					if ($sFileSize <= $sMaxFileSize)
					{
						$sImage = file_get_contents($_FILES[$sField]['tmp_name']);
						$oImage = imagecreatefromstring($sImage);

						if ($oImage !== false) 
						{
							ob_start();
							imagejpeg($oImage, null, 85);
							$sImage = ob_get_contents();
							ob_end_clean();
						}
						else $sResult = 'File is not an image.';
					}
					else $sResult = 'File size can not exceed '.number_format($sMaxFileSize/1024, 2).'KB';
				}
				else $sResult = 'File not uploaded by client.';
			}
			//else $sResult = 'Unknown error';
		}
		else $sResult = 'Expecting receipt file.';

		return $sResult;
	}
}

?>