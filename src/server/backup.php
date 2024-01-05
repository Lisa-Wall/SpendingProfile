<?
include_once('config.php');

/**
 * Automatic database backup and sends file to a remote ftp server.
 *
 * @author Lisa Wall
 * @version 2010-01-28
 */
class Backup
{
	var $sMessages = '';

	public function Backup()
	{
		$sDate = date("Y-m-d H:i:s");
		$this->message("Backup @ {$sDate}");
	
		// Ensure the script will have enough time to execute.
		set_time_limit(10*60);
	}

	public function run($sHost, $sUserName, $sPassword, $sDatabaseName, $sBackupFolder, $bCompressed = true, $bTimeStamp = true)
	{
		$this->message("Creating backup of: {$sDatabaseName}.");

		// Create the date and output file name.
		$sDate = ($bTimeStamp ? date("Y-m-d_H-i-s") : date("Y-m-d"));
		$sFileName = "{$sDatabaseName}_{$sDate}.gz";

		// Build the backup command.
		$sCommand = "mysqldump --opt --host={$sHost} --user={$sUserName} --password={$sPassword} {$sDatabaseName} | gzip -9 > {$sBackupFolder}{$sFileName}";

		// Execute the command.
		$bResult = system($sCommand);

		//If error then report it. Otherwise report success.
		if ($bResult !== false) $this->message("Created backup: {$bResult}.");
		else $this->error("Failed to create backup of: {$sDatabaseName}");
	}

	public function send($sServer, $iPort, $sUserName, $sPassword, $sRemoteFolder, $sLocalFile)
	{
		// Create the ftp connection.
		if ( ($oFTP = ftp_connect($sServer, $iPort)) )
		{
			// Attempt to log into the server.
			if (ftp_login($oFTP, $sUserName, $sPassword))
			{
				//Attempt to upload the file.
				if ( ftp_put($oFTP, $sRemoteFolder . $sLocalFile, $sLocalFile, FTP_BINARY) )
				{
					// TODO: add success message.
				}
				else echo 'FTP upload failed!';
			}
			else die('FTP login attempt failed!');

			ftp_close($oFTP);
		}
		else die('FTP connection failed!');
	}

	/*
	 Only keep one week of files. and every week for that past year. 
	 So there should be a max of 58 backups: 52 weeks and last 7 days.
	*/
	public function cleanup($sFolder)
	{

	}

	public function message($sMessage)
	{
		$this->sMessages .= "<font color='blue'>MESSAGE: {$sMessage}</font><br/>\n";
	}
	
	public function error($sMessage)
	{
		$this->sMessages .= "<font color='red'>ERROR: {$sMessage}</font><br/>\n";
	}
}

$bRunBackup = (isset($_REQUEST['runBackup']) || (isset($argv) && isset($argv[1]) && $argv[1] == 'runBackup'));

$oBackup = new Backup();

if ($bRunBackup)
{
	$oBackup->run(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE, '/home/beta/backups/', true, false);
}
else $oBackup->message('No Backup to run...');

echo $oBackup->sMessages;

?>