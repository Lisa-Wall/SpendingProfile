<?

require_once('Mail.php');
include_once('core/utility.php');
include_once('core/database.php');

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


function MassMail($sSubject, $sMessage)
{
	//$oDatabase = new Database("spendingprofile.com", "lisawall_spv3", "aspender", "lisawall_spv3");

	//$oUsers = $oDatabase->execute("SELECT email as Email FROM users");
	//$aUsers = $oDatabase->resultsToArray($oUsers);

	//$oSmtp = @Mail::factory('smtp', array ('host'=>'mail.spendingprofile.com', 'auth'=>true, 'username'=>'info@spendingprofile.com', 'password'=>'aspender'));

	//foreach ($aUsers as $aUser)
	{
		//$sEmail = $aUser['Email'];

		//echo $sEmail . "<br/>";

		//$aHeaders = array ('From' => 'Spending Profile <info@SpendingProfile.com>', 'To'=>$sEmail, 'Subject'=>$sSubject, 'Content-type'=>'text/html; charset=iso-8859-1');
		//@$oSmtp->send($sEmail, $aHeaders, $sMessage);
	}

}

//MassMail("New version of Spending Profile released!", file_get_contents('content/en/new_version_email.html'));

?>