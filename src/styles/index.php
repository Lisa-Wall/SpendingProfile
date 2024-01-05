<?
$aFiles = array(

'ui/table.css',
'ui/shadow.css',
'ui/window.css',
'ui/tooltip.css',
'ui/toolbar.css',
'ui/calendar.css',
'ui/smartpopup.css',
'ui/calculator.css',
'ui/dropdown.css',
'ui/uploader.css',

'site/styles.css',
'site/site.css'

);

include_once('theme.php');

header('content-type: text/css', true);

foreach ($aFiles as $sFile) if (file_exists($sFile)) include_once($sFile);

function getImage($sImage)
{
    return $sImage;
}


?>