<?
include_once('../server/config.php');

$sImage = URL.'styles/image.php?';
$sPage = (isset($_REQUEST['page']) ? $_REQUEST['page'] : '');

$aFiles = array('util/xml.js', 'util/ajax.js', 'util/hash.js', 'util/utility.js', 'util/validate.js', 'ui/widgets.js', 'site/site.js');
$aPage = array();

if      (strpos($sPage, 'signin')) $aPage = array('site/signin.js');
else if (strpos($sPage, 'createaccount')) $aPage = array('site/createaccount.js');
else if (strpos($sPage, 'resetpassword')) $aPage = array('site/resetpassword.js');
else if (strpos($sPage, 'whatlooklike_main')) $aPage = array('ui/shadow.js', 'ui/tooltip.js');

else if (strpos($sPage, 'account')) $aPage = array(
'site/account/account.js',
'site/account/tellafriend.js',
'site/account/preference.js',
'site/account/password.js',
'site/account/information.js',
'site/account/email.js',
'site/account/currency.js',

'ui/widgets.js',
'ui/shadow.js',
'ui/pushbutton.js',
'ui/smartpopup.js'
);

else if (strpos($sPage, 'import')) $aPage = array(
'util/date.js',

'ui/table.js',
'ui/shadow.js',
'ui/helptip.js',
'ui/tooltip.js',
'ui/toolbar.js',
'ui/calendar.js',
'ui/pushbutton.js',
'ui/windowpopup.js',
'ui/smartpopup.js',
'ui/smartdropdown.js',
'ui/textareapopup.js',
'ui/tablecelleditor.js',

'site/import.js',
'site/vendormenu.js',
'site/importoptions.js',
'site/helptipsimport.js'
);

else if (strpos($sPage, 'budget')) $aPage = array(
'util/date.js',

'ui/table.js',
'ui/shadow.js',
'ui/helptip.js',
'ui/tooltip.js',
'ui/calendar.js',
'ui/pushbutton.js',
'ui/windowpopup.js',
'ui/monthselector.js',

'site/budget.js',
'site/helptipsbudget.js'
);

else if (strpos($sPage, 'graphs')) $aPage = array(
'util/date.js',

'ui/shadow.js',
'ui/helptip.js',
'ui/tooltip.js',
'ui/calendar.js',
'ui/pushbutton.js',
'ui/smartpopup.js',
'ui/smartdropdown.js',
'ui/windowpopup.js',
'ui/monthselector.js',
'ui/periodselector.js',
'ui/calendarselector.js',

'site/analysis.js',
'site/helptipsanalysis.js'
);

else if (strpos($sPage, 'printview')) $aPage = array(
'util/date.js',
'ui/table.js',
'ui/shadow.js',
'ui/tooltip.js'
);

else if (strpos($sPage, 'main')) $aPage = array(
'util/date.js',
'util/form.js',
'util/xpath.js',

'ui/window.js',
'ui/shadow.js',
'ui/helptip.js',
'ui/tooltip.js',
'ui/calendar.js',
'ui/calculator.js',
'ui/pushbutton.js',
'ui/smartpopup.js',
'ui/smartdropdown.js',
'ui/table.js',
'ui/tablecelleditor.js',
'ui/textareapopup.js',
'ui/toolbar.js',
'ui/windowpopup.js',
'ui/monthselector.js',
'ui/periodselector.js',
'ui/calendarselector.js',
'ui/uploader.js',

//Main
'site/main.js',
'site/feedback.js',
'site/piegraph.js',
'site/tageditor.js',
'site/tagmanager.js',
'site/exportpopup.js',
'site/expensetable.js',
'site/balancesummary.js',
'site/piegraphcontroll.js',
'site/transactioninput.js',
'site/transactiontable.js',
'site/transactiontoolbar.js',

'site/filterspopup.js',
'site/advancedsearch.js',
'site/receiptwindow.js',

'site/helptipsmain.js',
'site/ad.js'
);


$aFiles = array_merge($aFiles, $aPage);

header("Content-Type: text/javascript");
foreach ($aFiles as $sFile) if (file_exists($sFile)) include_once($sFile);

/*
$aInclude = array();
foreach ($aFiles as $sFile)
{
	if (!file_exists($sFile) || in_array($sFile, $aInclude)) continue;

	$aInclude[] = $sFile;

	//Get file content.
	$sContent = file_get_contents($sFile);

	//Replace all consegutive tabs with ' ' and all consecutive \n with ' '
	//$sContent = preg_replace('/(\/\*(.)*\*\/)/', '', $sContent);
	//$sContent = preg_replace('/(\/\/(.)*\n)/', '', $sContent);
	$sContent = preg_replace('([\t]+)', '', $sContent);

	echo $sContent;
}
*/

?>