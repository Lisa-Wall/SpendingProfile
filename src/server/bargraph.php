<?

/**
 * @author Lisa Wall
 * @date 2009-06-07
 */
class BarGraph
{
	public $oImage = null;

	public $iWidth = 0;
	public $iHeight = 0;

	public $iFrontSize = 9;
	public $sFontFamily = 'fonts/courier.ttf';

	public $iInnerY = 0;

	public $aColor = array('BACKGROUND'=>0xFFFFFF, 'TEXT'=>0x404040, 'GRID'=>0xD0D0D0, 'TICK'=>0x000000, 'OUTLINE'=>0x777777);

	public function __construct($iWidth, $iHeight)
	{
		$this->iWidth = $iWidth;
		$this->iHeight = $iHeight;

		//Create the image.
		$this->oImage = imageCreateTrueColor($iWidth, $iHeight);

		//Fill the image with the background color.
		imageFill($this->oImage, 0, 0, $this->aColor['BACKGROUND']);
	}

	public function draw($aXAxis, $aYAxis, $iMaxY)
	{
		$iInnerX = 50 + 20;
		$iInnerY = $this->iHeight - 50 - 20;
		$iInnerW = $this->iWidth-$iInnerX;
		$iInnerH = $iInnerY;

		$this->aYPoints = $this->drawYAxis($aYAxis, $iInnerX, $iInnerY, $iInnerW, $iInnerH);
		$this->aXPoints = $this->drawXAxis($aXAxis, $iInnerX, $iInnerY, $iInnerW, $iInnerH);

		imagerectangle($this->oImage, $iInnerX, 0, $iInnerX + $iInnerW-1, $iInnerH, $this->aColor['OUTLINE']);

		$this->aXAxis = $aXAxis;
		$this->iInnerY = $iInnerY;
		$this->iYRadio = ($iMaxY == 0 ? 0 : ($iInnerH-10)/$iMaxY);
	}

	//Draws all x bar values for the specified data set.
	public function plot($aaData, $sName, $sXKey, $sYKey, $iColor, $iBarCount, $iBarIndex, &$aMap)
	{
		$iIndex = 0;
		$iXPoints = 0;
		$iY2 = $this->iInnerY - 1;
		$iI = ($this->iXInterval/2);
		$iW = ($this->iXInterval / $iBarCount);
		$iX = $iW * $iBarIndex;
		$iF = $this->iFrontSize/2.5;

		foreach ($this->aXAxis as $sXValue=>$sXText)
		{
			if ($iIndex >= count($aaData)) break;

			$iX1 = $this->aXPoints[$iXPoints++];
			$aData = $aaData[$iIndex];

			if ($aData[$sXKey] != $sXValue) continue;

			$iIndex++;
			$iY = $aData[$sYKey];
			$iY1 = $this->iInnerY - ($this->iYRadio * abs($iY));

			$iPx1 = $iX1 - $iI + $iX;
			$iPx2 = $iX1 - $iI + $iX + $iW - 1;
			imagefilledrectangle($this->oImage, $iPx1, $iY1, $iPx2, $iY2, $iColor);

			if ($iY < 0) imagettftext($this->oImage, $this->iFrontSize, 0, $iPx1 + $iW/2 - $iF, $iY1 - 2, $this->aColor['TEXT'], $this->sFontFamily, '+');

			//Store points for map.
			$iMx1 = round($iPx1); $iMx2 = round($iPx2);
			$aMap[] = array('Name'=>$sName, 'Value'=>$iY, 'Map'=>array($iMx1, $iY1, $iMx2, $iY2));
		}
	}

	public function drawYAxis($aYAxis, $iX, $iY, $iWidth, $iHeight)
	{
		$iFont = $this->iFrontSize/2-2;
		$iInterval = ($iHeight-10)/(count($aYAxis)-1);
		foreach ($aYAxis as $sY)
		{
			$aBox = imageTTFBbox($this->iFrontSize, 0, $this->sFontFamily, $sY);
			imagettftext($this->oImage, $this->iFrontSize, 0, $iX - abs($aBox[2]-$aBox[0]) - 10, $iY+$iFont, $this->aColor['TEXT'], $this->sFontFamily, $sY);
			imageline($this->oImage, $iX-5, $iY, $iX, $iY, $this->aColor['TICK']);
			imageline($this->oImage, $iX, $iY, $iX+$iWidth, $iY, $this->aColor['GRID']);

			$iY -= $iInterval;
		}

		return null;
	}

	public function drawXAxis($aXAxis, $iX, $iY, $iWidth, $iHeight)
	{
		$iFont = $this->iFrontSize/2-2;
		$aPoints = array();
		$iInterval = ($iWidth)/(count($aXAxis));

		$iI2 = ($iInterval/2);
		$iX += ($iInterval/2);

		foreach ($aXAxis as $sX)
		{
			$aBox = imageTTFBbox($this->iFrontSize, 90, $this->sFontFamily, $sX);
			imagettftext($this->oImage, $this->iFrontSize, 90, $iX+$iFont, $iY + abs($aBox[5]-$aBox[1]) + 10, $this->aColor['TEXT'], $this->sFontFamily, $sX);

			imageline($this->oImage, $iX, $iY, $iX, $iY+5, $this->aColor['TICK']);
			imageline($this->oImage, $iX-$iI2, $iY, $iX-$iI2, 0, $this->aColor['GRID']);

			$aPoints[] = $iX;
			$iX += $iInterval;
		}

		$this->iXInterval = $iInterval-4;

		return $aPoints;
	}

	public function save($sFileName = null)
	{
		imagepng($this->oImage, $sFileName);
	}
}

?>