<?
/**
* @author Lisa Wall
* @date 2009-08-02
*/
class Ad
{
	public function __construct()
	{
	}
	
	public function get($iIndex)
	{
		//Load the XML file.
		$oAds = XML::load('content/ads.xml');

		//Count number of ads available.
		$iCount = XML::count($oAds);

		//Module index with ads count.
		$iIndex = abs($iIndex)%$iCount;

		//Get the ad the specified location.
		$oAd = XML::getElementAtIndex($oAds, $iIndex+1);

		//return the ad.
		return file_get_contents($oAd->getAttribute('Path'));
	}
}

?>