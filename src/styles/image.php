<?

$sPath  = (isset($_REQUEST['icon']) ? 'icons' : 'images');
$sImage = (isset($_REQUEST['icon']) ? $_REQUEST['icon'] : $_REQUEST['image']);
$sExtension = substr($sImage, strrpos($sImage, '.')+1);

//Open the file.
$oImage = fopen("$sPath/$sImage", 'rb');

//Set the header and send the image to the client.
header("Content-Type: image/$sExtension");
header("Content-Length: " . filesize("$sPath/$sImage"));

ob_end_clean();
fpassthru($oImage);
exit();

?>