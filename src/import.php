<?
$bHttps = true;
$bSecured = true; 
$PAGE_INNER = true;
include_once("inc_header.php");

$sError = '';
$bUploaded = 'false';

if (isset($_FILES['import/filepath']))
{
	$iSize = $_FILES['import/filepath']['size'];
	$sFilename = $_FILES['import/filepath']['name'];
	$sTempFile = $_FILES['import/filepath']['tmp_name'];
	$sExtension = (($iIndex = strrpos($sFilename, '.')) === false ? null : substr($sFilename, $iIndex+1));

	if (strlen($sFilename) == 0) $sError = 'Please select a file to upload.';
	else if ($sExtension == null) $sError = 'File must have an extension to determine type.';
	else if ($iSize > 1048576)  $sError = 'File size must not exceed 1MB';
	else
	{
		$bResult = move_uploaded_file($sTempFile, 'server/content/import/'.$_SESSION['id']);

		if ($bResult)
		{
			$bUploaded = 'true';
			$_SESSION['EXTENSION'] = $sExtension;
		}
		else $sError = 'An error occured while uploading file.';
	}
}

?>

<script>
	var sError = "<?=$sError?>";
	var bUploaded = <?=$bUploaded?>;

	var oImport = null;
	function loadPage()
	{
		oImport = new Import(bUploaded, sError);
	}
</script>

<div class="page_text" style="width:100%">
	<div class="page_subtitle" align="center" style="font-size:21">Import Transactions</div>
	<br/>
	Save time by importing transactions from your bank or credit card account, or from your personal accounting software.
	<form enctype="multipart/form-data" action="import.php" method="POST" onsubmit="">
		<ol>
			<li style="padding-bottom:5px">Go to your bank`s website, and download your transactions to your computer. <span onclick="UI.showHelptip(this, 'How To Download A Bank File', sBankFileHelptip)"><a href="javascript:">How?</a></span></li>
			<li style="padding-bottom:5px">Upload the file here:
				<input type="hidden" name="MAX_FILE_SIZE" value="1048576"/>
				<input name="import/filepath" class="textfield" type="file" value="" style="height:20px"/>&nbsp;
				<input type="submit" class="button" value="Preview Transactions" />
			</li>
			<li>A table will table appear below with a preview of your transactions. Edit the table and then save the transactions to your Spending Profile account.</li>
		</ol>
	</form>
	<center><input type="button" style="witdh:400px" class="button" onclick="oImport.onSave()" value="Save Transactions To Your Account"/></center>
	<br/>
</div>

<table id="toolbar" width="100%"></table><div id="table"></div>

<table id="import/options" class="label" style="margin:5px;display:none">
	<tr><td><input type="checkbox"/><span onclick="this.previousSibling.click()" style="cursor:default">Guess Vendor</span></td><td align="right"><img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Guess Vendor', sGuessVendorHelptip)"/></td></tr>
	<tr><td><input type="checkbox"/><span onclick="this.previousSibling.click()" style="cursor:default">Format Vendor</span></td><td align="right"><img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Format Vendor', sFormatVendorHelptip)"/></td></tr>
	<tr><td><input type="checkbox"/><span onclick="this.previousSibling.click()" style="cursor:default">Format Notes</span></td><td align="right"><img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Format Notes', sFormatNotesHelptip)"/></td></tr>
	<tr><td colspan="2">You must refresh the data in order<br/> for the changes to take effect.</td></tr>
	<tr><td colspan="2" align="center" style="padding-top:5px"><input type="button" class="button" value="Save" style="width:70px"/> <input type="button" class="button" value="Cancel" style="width:70px"/></td>
</table>

<? include_once("inc_footer.php"); ?>