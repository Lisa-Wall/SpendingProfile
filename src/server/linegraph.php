<?

/**
 * @author Lisa Wall
 * @date 2009-06-07
 */
class LineGraph
{
	public $oImage = null;

	public $aXAxis = null;
	public $iXInterval = 0;
	public $iYRadio = 0;
	public $iInnerY = 0;
	public $aXPoints = null;
	public $aYPoints = null;

	public $iWidth = 0;
	public $iHeight = 0;

	public $iFrontSize = 9;
	public $sFontFamily = 'fonts/courier.ttf';

	public $aColor = array('BACKGROUND'=>0xFFFFFF, 'SHADOW'=>0x909090, 'FILL'=>0xDDDDDD, 'TEXT'=>0x404040, 'GRID'=>0xD0D0D0, 'TICK'=>0x000000, 'OUTLINE'=>0x777777);
	public $aSliceColors = array(0xFF4500, 0xFFD700, 0x6495ED, 0x9ACD32);

	public function __construct($iWidth, $iHeight)
	{
		$this->iWidth = $iWidth;
		$this->iHeight = $iHeight;

		//Create the image.
		$this->oImage = imageCreateTrueColor($iWidth, $iHeight);

		//Fill the image with the background color.
		imageFill($this->oImage, 0, 0, $this->aColor['BACKGROUND']);
	}

	public function draw($aX, $aY, $iMaxY)
	{
		$iMaxLengthY = 50; //$this->getMaxLength($aY)
		$iMaxLengthX = 50; //$this->getMaxLength($aY)

		$iInnerX = $iMaxLengthY + 20;
		$iInnerY = $this->iHeight - $iMaxLengthX - 20;
		$iInnerW = $this->iWidth-$iInnerX;
		$iInnerH = $iInnerY;

		$this->aYPoints = $this->drawYAxis($aY, $iInnerX, $iInnerY, $iInnerW, $iInnerH);
		$this->aXPoints = $this->drawXAxis($aX, $iInnerX, $iInnerY, $iInnerW, $iInnerH);

		imagerectangle($this->oImage, $iInnerX, 0, $iInnerX + $iInnerW-1, $iInnerH, $this->aColor['OUTLINE']);

		$this->aXAxis = $aX;
		$this->iInnerY = $iInnerY;
		$this->iYRadio = ($iMaxY == 0 ? 0 : ($iInnerH-10)/$iMaxY);
	}

	public function plot($aaData, $sName, $sXKey, $sYKey, $iColor, &$aMap)
	{
		$iC = 1; $iS = 3; $iS2 = 4;
		$iIndex = 0;
		$iXPoints = 0;
		$bFirstPoint = true;
		foreach ($this->aXAxis as $sXValue=>$sXText)
		{
			if ($iIndex >= count($aaData)) break;

			$iX2 = $this->aXPoints[$iXPoints++];
			$aData = $aaData[$iIndex];

			if ($aData[$sXKey] != $sXValue) continue;

			$iIndex++;
			$iY = $aData[$sYKey];
			$iY2 = $this->iInnerY - ($this->iYRadio * abs($iY));

			if ($bFirstPoint)
			{
				$iX1 = $iX2;
				$iY1 = $iY2;
				$bFirstPoint = false;
			}

			imageline($this->oImage, $iX1, $iY1, $iX2, $iY2, $iColor);

			if ($iY >= 0 ) imagefilledellipse($this->oImage, $iX2, $iY2, 6, 6, $iColor);
			else
			{
				imagefilledrectangle($this->oImage, $iX2-$iC, $iY2-$iS2, $iX2+$iC, $iY2+$iS2, $iColor);
				imagefilledrectangle($this->oImage, $iX2-$iS2, $iY2-$iC, $iX2+$iS2, $iY2+$iC, $iColor);
			}

			//Store points for map.
			$iMx = round($iX2); $iMy = round($iY2);
			$aMap[] = array('Name'=>$sName, 'Value'=>$iY, 'Map'=>array($iMx-$iS, $iMy-$iS, $iMx+$iS, $iMy+$iS));

			$iX1 = $iX2;
			$iY1 = $iY2;
		}
	}

	public function drawYAxis($aY, $iX, $iY, $iWidth, $iHeight)
	{
		$iFont = $this->iFrontSize/2-2; //TODO: calculate the font height
		$iInterval = ($iHeight-10)/(count($aY)-1);
		foreach ($aY as $sY)
		{
			$aBox = imageTTFBbox($this->iFrontSize, 0, $this->sFontFamily, $sY);
			imagettftext($this->oImage, $this->iFrontSize, 0, $iX - abs($aBox[2]-$aBox[0]) - 10, $iY+$iFont, $this->aColor['TEXT'], $this->sFontFamily, $sY);

			//Draw Tick.
			imageline($this->oImage, $iX-5, $iY, $iX, $iY, $this->aColor['TICK']);

			//Draw Line.
			imageline($this->oImage, $iX, $iY, $iX+$iWidth, $iY, $this->aColor['GRID']);

			$iY -= $iInterval;
		}

		return null;
	}

	public function drawXAxis($aX, $iX, $iY, $iWidth, $iHeight)
	{
		$iFont = $this->iFrontSize/2-2;
		$iInterval = ($iWidth-10)/(count($aX)-1);
		$aPoints = array();

		foreach ($aX as $sX)
		{
			$aBox = imageTTFBbox($this->iFrontSize, 90, $this->sFontFamily, $sX);
			imagettftext($this->oImage, $this->iFrontSize, 90, $iX+$iFont, $iY + abs($aBox[5]-$aBox[1]) + 10, $this->aColor['TEXT'], $this->sFontFamily, $sX);

			//Draw Tick.
			imageline($this->oImage, $iX, $iY, $iX, $iY+5, $this->aColor['TICK']);

			//Draw line.
			imageline($this->oImage, $iX, $iY, $iX, 0, $this->aColor['GRID']);

			$aPoints[] = $iX;
			$iX += $iInterval;
		}

		$this->iXInterval = $iInterval;

		return $aPoints;
	}

	public function getMaxLength($aArray, $iAngle = 0)
	{
		$sMax = '';
		foreach ($aArray as $sValue) if (strlen($sMax) < strlen($sValue)) $sMax = $sValue;

		$aBox = imageTTFBbox($this->iFrontSize, $iAngle, $this->sFontFamily, $sMax);

		return abs($aBox[2]-$aBox[0]);
	}

	public function save($sFileName = null)
	{
		imagepng($this->oImage, $sFileName);
	}
}

?>