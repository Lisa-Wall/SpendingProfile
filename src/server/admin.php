<?
/**
* @author Lisa Wall
* @date 2009-04-18
*/
class Admin
{
	private $iUserId = 0;
	private $oDatabase = null;
	
	private $aIpToCountry = array();

	public function __construct()
	{
		global $oSession;
		$this->iUserId = $oSession->iUserId;
		$this->oDatabase = $oSession->oDatabase;
	}

	public function getUsers($sOrderBy, $sOrderIn)
	{
		$aColumnMap = array('Id'=>'id', 'Email'=>'email', 'CreatedOn'=>'created_on', 'LastSignIn'=>'last_login');
	
		$aResults = $this->oDatabase->selectRows('sql/admin/users.sql', $sOrderBy, $sOrderIn);
		if ($aResults === false || $aResults === null) return error('getUsers', RESPONSE_SERVER_ERROR);
		
		foreach($aResults as &$aUser) $aUser['Country'] = $this->getCountry($aUser['LastIp']);
	
		return '<Users>'.XML::fromArrays('User', $aResults).'</Users>';
	}

	public function getUserCount()
	{
		$aResult = $this->oDatabase->selectRow('sql/admin/user_count.sql');
		if ($aResult === false || $aResult === null) return error('getUserCount', RESPONSE_SERVER_ERROR);
		
		return XML::fromArray(true, 'UserCount', $aResult);
	}

	public function getSignedInHistory()
	{
		$aResults = $this->oDatabase->selectRows('sql/admin/signed_in_history.sql');
		if ($aResults === false || $aResults === null) return error('getSignedInHistory', RESPONSE_SERVER_ERROR);
		
		return '<SignedInHistory>'.XML::fromArrays('History', $aResults).'</SignedInHistory>';
	}

	public function getCreatedOnHistory()
	{
		$aResults = $this->oDatabase->selectRows('sql/admin/created_on_history.sql');
		if ($aResults === false || $aResults === null) return error('getCreatedOnHistory', RESPONSE_SERVER_ERROR);
		
		return '<CreatedOnHistory>'.XML::fromArrays('History', $aResults).'</CreatedOnHistory>';
	}

	public function getUserStats($sFrom)
	{
		if ($sFrom === null) $sFrom = date('Y-m-d');
	
		$aResult = $this->oDatabase->selectRow('sql/admin/user_stats.sql', $sFrom);
		if ($aResult === false || $aResult === null) return error('getUserStats', RESPONSE_SERVER_ERROR);
		
		$aResult['From'] = $sFrom;
		
		return XML::fromArray(true, 'UserStats', $aResult);
	}

	public function getTransactionsAndTagsStats()
	{
		$aResult = $this->oDatabase->selectRow('sql/admin/transactions_tags_stats.sql');
		if ($aResult === false || $aResult === null) return error('getTransactionsAndTagsStats', RESPONSE_SERVER_ERROR);
		
		return XML::fromArray(true, 'TransactionsTagsStats', $aResult);
	}
	
	public function getTags($sTag, $sOrderBy, $sOrderIn)
	{
		$aResults = $this->oDatabase->selectRows('sql/admin/get_tags.sql', $sTag, $sOrderBy, $sOrderIn);
		if ($aResults === false || $aResults === null) return error('getTags', RESPONSE_SERVER_ERROR);
		
		return '<Tags>'.XML::fromArrays('Tag', $aResults).'</Tags>';
	}
	
	public function getRequests()
	{
		$sRequests = '';

		$sFolder = 'logs/requests/';
		$oFolder = opendir($sFolder);
		while ($sFile = readdir($oFolder))
		{
			if ($sFile == '.' || $sFile == '..') continue;
			$sRequests .= file_get_contents($sFolder.$sFile);
		}

		return '<Requests>'.$sRequests.'</Requests>';
	}

	public function getRequestStats()
	{
		$sRequests = $this->getRequests();
		$oRequests = XML::loadXML($sRequests);
		$aRequests = array();

		for ($oRequest = $oRequests->firstChild; $oRequest != null; $oRequest = $oRequest->nextSibling)
		{
			if ($oRequest->nodeType != XML_ELEMENT_NODE) continue;
			$aRequests[$oRequest->nodeName] = ( isset($aRequests[$oRequest->nodeName]) ? $aRequests[$oRequest->nodeName] + 1 : 1);
		}

		arsort($aRequests);

		$aRequestList = array();
		foreach ($aRequests as $sCall=>$iCount) $aRequestList[] = array('Count'=>$iCount, 'Feature'=>$sCall);

		return '<Requests>'.XML::fromArrays('Request', $aRequestList).'</Requests>';
	}
	
	private function getCountry($sIp)
	{
		$iIp = ip2long($sIp);

		return 'UNKNOWN';
		
		if ($iIp == 0 || $sIp == '0') return 'UNKNOWN';
		
		if (count($this->aIpToCountry) == 0) 
		{
			//Open the file 
			$oFile = fopen('content/ip_to_country.csv', 'r'); 
			
			//Get CSV data.
			while ($aRow = fgetcsv($oFile, 1024, ',', '"')) $this->aIpToCountry[] = $aRow;
			
			//Close file.
			fclose($oFile);
			
		}

		foreach ($this->aIpToCountry as $aRow) if ($aRow[0] <= $iIp AND $aRow[1] >= $iIp) return $aRow[4];
		return 'UNKNOWN';
	}
}

?>