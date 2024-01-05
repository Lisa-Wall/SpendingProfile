<?

/**
 * @author Lisa Wall
 * @date 2009-03-28
 */
class PieGraph
{
	public $oImage = null;

	protected $iCx = 0;
	protected $iCy = 0;
	protected $iWidth = 0;
	protected $iHeight = 0;
	protected $iDiameter = 0;
	protected $iMinMapAngle = 25;
	protected $iMinTextPixels = 20;

	protected $bShowExtended = true;

	protected $sFontFamily = 'fonts/arialnarrow.ttf';

	//Create colours to use
	public $aColor = array('BACKGROUND'=>0xFFFFFF, 'SHADOW'=>0x909090, 'FILL'=>0xDDDDDD, 'TEXT'=>0x404040, 'OUTLINE'=>0x777777, 'INCOME'=>0x0000FF, 'EXPENS'=>0xFF0000);
	public $aSliceColors = array(0xFF4500, 0xFFD700, 0x6495ED, 0x9ACD32);
	//public $aSliceColors = array(0xFF4500, 0xFFD700, 0xFE8400, 0x6495ED, 0x9ACD32, 0xD902DC, 0x00DFD7); //, 0x6d03d9);

	public function __construct($iWidth, $iHeight, $iFontSize, $bShowExtended, $iMinMapAngle, $iMinTextPixels)
	{
		$this->iWidth = $iWidth;
		$this->iHeight = $iHeight;
		$this->iFontSize = $iFontSize;
		$this->iMinMapAngle = $iMinMapAngle;
		$this->iMinTextPixels = $iMinTextPixels;
		$this->bShowExtended = $bShowExtended;

		//Create the image.
		$this->oImage = imageCreateTrueColor($iWidth, $iHeight);

		//Fill the image with the background color.
		imageFill($this->oImage, 0, 0, $this->aColor['BACKGROUND']);

		//Do some pre-calculations where the pie is going to go.
		$iShortestSide = ($iWidth > $iHeight ? $iHeight : $iWidth);

		// Center of pie
		$this->iCx = $iShortestSide/2;
		$this->iCy = $iShortestSide/2;

		// Diameter of pie
		$this->iDiameter = $iShortestSide - 10;
		$this->iRadius = $this->iDiameter/2;

		//Fill the pie.
		//imagefilledellipse($this->oImage, $this->iCx+3, $this->iCy+3, $this->iDiameter, $this->iDiameter, $this->aColor['SHADOW']);
	}

	public function drawEmpty($sMessage)
	{
		$iFontSize = 10;

		imagefilledellipse($this->oImage, $this->iCx, $this->iCy, $this->iDiameter, $this->iDiameter, $this->aColor['FILL']);
		imageellipse($this->oImage, $this->iCx, $this->iCy, $this->iDiameter, $this->iDiameter, $this->aColor['OUTLINE']);

		$aBox = imageTTFBbox($iFontSize, 0, $this->sFontFamily, $sMessage);

		imagettftext($this->oImage, $iFontSize, 0,  $this->iCx - abs($aBox[2]-$aBox[0])/2,  $this->iCy+6, $this->aColor['SHADOW'], $this->sFontFamily, $sMessage);
	}

	public function drawMap($aTable, $iLevels, $iLevel, &$iColor)
	{
		//Draw Children First.
		foreach ($aTable['Children'] as $aChild) $this->drawMap($aChild, $iLevels, $iLevel+1, $iColor);

		//If first level then return.
		if ($iLevel == 0) return;

		$aMap = $aTable['Map'];
		$iColorIndex = ($iColor++) % count($this->aSliceColors);

		imagefilledpolygon($this->oImage, $aMap, count($aMap)/2, $this->aSliceColors[$iColorIndex]);
		imagepolygon($this->oImage, $aMap, count($aMap)/2, $this->aColor['OUTLINE']);

		//imagepolygon($this->oImage, $aMap, count($aMap)/2, $this->aSliceColors[$iColorIndex]);
	}

	public function drawPie($aTable, $iLevels, $iLevel, $iParentColor, &$iColor)
	{
		$iSliceColor = $iColor++;

		//Draw Children First.
		foreach ($aTable['Children'] as $aChild) $this->drawPie($aChild, $iLevels, $iLevel+1, $iSliceColor, $iColor);

		//If first level then return.
		if ($iLevel == 0) return;

		$this->drawSlice($aTable, (isset($aTable['Extended']) ? $iParentColor : $iSliceColor), false);
	}

	private function drawSlice($aTable, $iColor, $bFull)
	{
		$iDiameter   = ($bFull ? $this->iDiameter : $aTable['Diameter']);
		$iEndAngle   = $aTable['EndAngle'];
		$iStartAngle = $aTable['StartAngle'];
		$iColorIndex = $iColor % count($this->aSliceColors);

		//If angle is less than 1 then make them 1. This is to fix a bug in the rendering.
		if (($iEndAngle - $iStartAngle) < 1)
		{
			$iEndAngle = ceil($iEndAngle);
			$iStartAngle = floor($iStartAngle);
		}

		//Draw the pie arcs
		imagefilledarc($this->oImage, $this->iCx, $this->iCy, $iDiameter, $iDiameter, $iStartAngle, $iEndAngle, $this->aSliceColors[$iColorIndex], IMG_ARC_PIE);
		imagefilledarc($this->oImage, $this->iCx, $this->iCy, $iDiameter, $iDiameter, $iStartAngle, $iEndAngle, $this->aColor['OUTLINE'], IMG_ARC_EDGED | IMG_ARC_NOFILL);
	}

	public function drawText($aTable, $iLevels, $iLevel)
	{
		//Draw Children First.
		foreach ($aTable['Children'] as $aChild) $this->drawText($aChild, $iLevels, $iLevel+1);

		$sName = $aTable['Name'];

		//If first level then return.
		if ($iLevel == 0 || strlen($sName) == 0 || $aTable['AnglePixels'] < $this->iMinTextPixels) return;

		//Draw The text.
		imagettftext($this->oImage, $this->iFontSize, 0,  $this->iCx + $aTable['TextX'],  $this->iCy + $aTable['TextY'] + ($this->iFontSize/2), $this->aColor['TEXT'], $this->sFontFamily, $sName);
	}

	/**
	 * Calculates the start and end angles of all children within the parent level.
	 * Also calculates the text position and the size of the pie in pixels as well as the pie map.
	 *
	 * Writes: StartAngle, EndAngle, TextX, TextY, AnglePixels, Diameter, Map.
	 */
	public function calculate(&$aParent, $iLevels, $iLevel)
	{
		$iTotal      = $aParent['Total'];
		$iEndAngle   = $aParent['StartAngle'];
		$iStartAngle = $aParent['StartAngle'];
		$iTotalAngle = $aParent['EndAngle']-$iStartAngle;

		$iInset = ($iLevels == 0 ? 0 : ($this->iRadius/$iLevels));
		$iSubDiameter = ($iLevels == 0 ? 0 : ($this->iDiameter/$iLevels)*$iLevel);
		$iSubRadius = $iSubDiameter/2;

		foreach ($aParent['Children'] as &$aSlice)
		{
			$iValue = $aSlice['Total'];
			$aChildren = &$aSlice['Children'];
			$iChildren = count($aChildren);

			if ($this->bShowExtended)
			{
				$iDiameter = ($iChildren == 0 ? $this->iDiameter : $iSubDiameter);
				$iRadius = $iDiameter/2;
			}
			else
			{
				$iDiameter = $iSubDiameter;
				$iRadius = $iSubRadius;
			}

			//Calculate the cumlative end angle form the value.
			$iEndAngle += ($iTotal == 0 ? 0 : ($iValue/$iTotal)*$iTotalAngle);

			//Add start and end angle to the slice.
			$aSlice['EndAngle'] = $iEndAngle;
			$aSlice['StartAngle'] = $iStartAngle;

			//Calculate angle in pixels.
			$iDeltaAngle = $iEndAngle - $iStartAngle;
			$aSlice['AnglePixels'] = abs(sin(deg2rad(($iDeltaAngle > 90 ? 90 : $iDeltaAngle)))*$iRadius);

			//Calculate Text position.
			$iAngle = deg2rad(floor($iStartAngle) + ($iDeltaAngle/2));

			if ($this->bShowExtended) $iTextRadius = ($iLevel * $iInset) + ($iChildren == 0 ? (($iLevels - $iLevel)*$iInset/2) : 0) - ($iInset/3);
			else                      $iTextRadius = ($iLevel * $iInset) - ($iInset/3);

			$aSlice['TextX'] = cos($iAngle)*($iTextRadius);
			$aSlice['TextY'] = sin($iAngle)*($iTextRadius);

			//Calculate map coordinates.
			$aSlice['Map'] = $this->map($iRadius, $iStartAngle, $iEndAngle);

			//Add slice diameter to faciliate drawing it.
			$aSlice['Diameter'] = $iDiameter;

			//If has children then calculate for them as well.
			if ($iChildren > 0) $this->calculate($aSlice, $iLevels, $iLevel+1);

			//Set up for next iteration.
			$iStartAngle = $iEndAngle;
		}
	}

	/**
	 * Return an array of (cx, cy, x1, y1, x2, y2, ...) of the slice specified.
	 */
	public function map($iRadius, $iStartAngle, $iEndAngle)
	{
		//Add the center.
		$aMap = array($this->iCx, $this->iCy);

		do
		{
			//Add the start and all in bitween.
			$iRadian = deg2rad($iStartAngle);
			$aMap[]  = round($this->iCx + ((cos($iRadian))*($iRadius)));
			$aMap[]  = round($this->iCy + ((sin($iRadian))*($iRadius)));

			$iStartAngle += $this->iMinMapAngle;

		}while ($iStartAngle < $iEndAngle);

		//Add end angle.
		$iRadian = deg2rad($iEndAngle);
		$aMap[]  = round($this->iCx + ((cos($iRadian))*($iRadius)));
		$aMap[]  = round($this->iCy + ((sin($iRadian))*($iRadius)));

		return $aMap;
	}

	public function save($sFileName = null)
	{
		imagepng($this->oImage, $sFileName);
	}
	
	public function get()
	{
		ob_start();
		imagepng($this->oImage, null);
		$sImage = ob_get_contents();
		ob_end_clean();
		return $sImage;
	}


	public function drawBalance($iDebit, $iCredit)
	{

		$iAngle = $iAngle = ($iCredit == 0 ? 0 : 360 * $iDebit / $iCredit);
		$iOuterColor = $this->aColor['INCOME'];
		$iInnerColor = $this->aColor['EXPENS'];
		$iInnerRatio = 0.80;
		$iOuterValue = $iCredit;
		$iInnerValue = $iDebit;

		if ($iCredit < $iDebit)
		{
			$iAngle = 360 * $iCredit / $iDebit;
			$iOuterColor = $this->aColor['EXPENS'];
			$iInnerColor = $this->aColor['INCOME'];
			$iOuterValue = $iDebit;
			$iInnerValue = $iCredit;
		}

		//Draw outer pie.
		imagefilledellipse ($this->oImage, $this->iCx, $this->iCy, $this->iDiameter, $this->iDiameter, ($iOuterValue == 0 ?  $this->aColor['FILL'] : $iOuterColor));
		imageellipse($this->oImage, $this->iCx, $this->iCy, $this->iDiameter, $this->iDiameter, $this->aColor['OUTLINE']);

		//Draw inner pie.
		if ($iInnerValue != 0)
		{
			imagefilledarc($this->oImage, $this->iCx, $this->iCy, $this->iDiameter * $iInnerRatio, $this->iDiameter * $iInnerRatio, 0, $iAngle, $iInnerColor, IMG_ARC_PIE);
			imagefilledarc($this->oImage, $this->iCx, $this->iCy, $this->iDiameter * $iInnerRatio, $this->iDiameter * $iInnerRatio, 0, $iAngle, $this->aColor['OUTLINE'], IMG_ARC_EDGED | IMG_ARC_NOFILL);
		}

		//Draw Legend
		$iDelta = 5;
		$iLegendW = 25;
		$iLegendH = 6;
		$iLegendY = $this->iCy - $iDelta - $iLegendH;
		$iLegendX = $this->iCx + $this->iRadius + 20;

		ImageFilledRectangle($this->oImage, $iLegendX, $iLegendY, $iLegendX + $iLegendW, $iLegendY + $iLegendH, $this->aColor['INCOME']);
		ImageRectangle      ($this->oImage, $iLegendX, $iLegendY, $iLegendX + $iLegendW, $iLegendY + $iLegendH, $this->aColor['OUTLINE']);
		imagettftext        ($this->oImage, $this->iFontSize, 0,  $iLegendX + $iLegendW + 10,  $iLegendY + $iLegendH, $this->aColor['TEXT'], $this->sFontFamily, 'Income');

		$iLegendY = $this->iCy + $iDelta;

		ImageFilledRectangle($this->oImage, $iLegendX, $iLegendY, $iLegendX + $iLegendW, $iLegendY + $iLegendH, $this->aColor['EXPENS']);
		ImageRectangle      ($this->oImage, $iLegendX, $iLegendY, $iLegendX + $iLegendW, $iLegendY + $iLegendH, $this->aColor['OUTLINE']);
		imagettftext        ($this->oImage, $this->iFontSize, 0,  $iLegendX + $iLegendW + 10,  $iLegendY + $iLegendH, $this->aColor['TEXT'], $this->sFontFamily, 'Expenses');
	}
}

?>