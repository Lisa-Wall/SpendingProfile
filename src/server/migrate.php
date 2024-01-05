<?
include_once('core/utility.php');
include_once('core/database.php');

class Migrate
{
	private $oTarget = null;
	private $oSource = null;
	
	public function __construct($oTarget, $oSource)
	{
		$this->oTarget = $oTarget;
		$this->oSource = $oSource;
	}

	public function update()
	{
		$aStatements = $this->get();
		
		if ($aStatements === false) return false;
		
		$this->set($aStatements);
	}
	
	public function set($aStatements)
	{
		$oUsers = $this->oTarget->execute('SELECT id, email, preference FROM users');
		if ($oUsers === false || $oUsers === null) return error("Unable to get users from target database.", false);

		$iCount = 0;
		$aUsers = Database::resultsToArray($oUsers);
		foreach ($aUsers as $aUser)
		{
			$sId = $aUser['id'];
			$sEmail = $aUser['email'];
			$sPreference = $aUser['preference'];

			$iCount++;
			message("$iCount - $sEmail - $sPreference");
			
			if (stripos($sPreference, 'STATEMENT:') === false)
			{
				$iStatement = (array_key_exists(strtolower($sEmail), $aStatements) ? $aStatements[strtolower($sEmail)] : '1');

				//Update preferences
				$sPreference .= ( strlen($sPreference) == 0 ? '' : ($sPreference[strlen($sPreference)-1] == ';' ? '' : ';') ) . "STATEMENT:$iStatement";
				
				message("preference: $sPreference");
				
				$bResult = $this->oTarget->execute("UPDATE users SET preference='$sPreference' WHERE id=$sId");
				if ($bResult === false || $bResult === null) error("Update preferences for: $sEmail");
			}
			
		}
	}
	
	public function get()
	{
		$aStatements = array();

		$oUsers = $this->oSource->execute('SELECT 731c7ebc AS email, sa65953c AS statement FROM u89f3e87');
		if ($oUsers === false || $oUsers === null) return error("Unable to get users from source database.", false);

		$iCount = 0;
		$aUsers = Database::resultsToArray($oUsers);
		foreach ($aUsers as $aUser)
		{
			$sEmail = $aUser['email'];
			$sPreference = $aUser['statement'];

			$iCount++;
			//message("$iCount - $sEmail - $sPreference");
			
			$aStatements[strtolower($sEmail)] = $sPreference;
		}
		
		return $aStatements;
	}
}

function setOutputType($sFilename)
{
}

function debug($sMessage)
{
	//echo "<tr class='debug'><td>DEBUG</td><td>$sMessage</td></tr>\n";
}

function error($sMessage, $iReturn = null)
{
	echo "<tr class='error'><td>ERROR</td><td>$sMessage</td></tr>\n";
	return $iReturn;
}

function message($sMessage)
{
	echo "<tr class='message'><td>MESSAGE</td><td>$sMessage</td></tr>\n";
}


?>

<html>
  <head>
    <style>
      .table{font-size: 10pt; font-family: arial; }
      .error{color: red}
      .debug{color: green}
      .message{color: blue}
      .header{text-align:center;font-weight:bold}
    </style>
  </head>
  <body>
    <table border="1" class="table">
      <tr class="header">
        <td>Type</td>
        <td>Message</td>

        <?
          set_time_limit(360);
          
          //$oTarget = new Database('localhost', 'lisawall_spv3', 'aspender', 'lisawall_spv3');
          //$oSource = new Database('localhost', 'lisawall_lisawal', 'easylearn', 'lisawall_spendingprofile');

          //$oMigrate = new Migrate($oTarget, $oSource);
          //$oMigrate->update();
        ?>

      </tr>
    </table>
  </body>
</html>
