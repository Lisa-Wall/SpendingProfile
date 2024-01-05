<?
include_once('Mail.php');
include_once('core/utility.php');
include_once('core/database.php');

include_once('piegraph.php');
include_once('statement.php');

class Statements
{
	private $oDatabase = null;
	
	public function __construct($oDatabase)
	{
		$this->oDatabase = $oDatabase;
	}

	public function send($sMonth)
	{
		$oUsers = $this->oDatabase->execute('SELECT id, email, currency, preference FROM users');
		$aUsers = Database::resultsToArray($oUsers);
		
		$oSmtp = @Mail::factory('smtp', array ('host'=>'mail.spendingprofile.com', 'auth'=>true, 'username'=>'support@spendingprofile.com', 'password'=>'<pwd>'));

		$iCount = 0;
		foreach ($aUsers as $aUser)
		{
			$sId = $aUser['id'];
			$sEmail = $aUser['email'];
			$sCurrency = $aUser['currency'];
			$sPreference = $aUser['preference'];

//TODO: remove this.			
if ($sEmail !== 'charbel.choueiri@live.ca') continue;

			if (stripos($sPreference, 'STATEMENT:1') !== false)
			{
				$iCount++;
				message("$iCount - $sId - $sEmail");

				$oStatement = new Statement($sId, $sCurrency, $this->oDatabase);
				$sStatement = $oStatement->email($sMonth);

				$oStatement->aHeader['To'] = $sEmail;
				$oStatement->aHeader['From'] = "Spending Profile <admin@spendingprofile.com>";
				$oStatement->aHeader['Subject'] = date('F Y', $oStatement->iMonth).' Statement from Spending Profile';

				$oSmtp->send($sEmail, $oStatement->aHeader, $sStatement);

				message('Send Email: '. date('F Y', $oStatement->iMonth).' Statement from Spending Profile');
			}
		}
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

          $oStatements = new Statements(new Database('localhost', 'lisawall_spv3', 'aspender', 'lisawall_spv3'));
          $oStatements->send(date('Y-m-d', strtotime("last Month")));
        ?>

      </tr>
    </table>
  </body>
</html>