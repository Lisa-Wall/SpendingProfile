<?

/**
 * @author Lisa Wall
 * @date 2009-04-05
 */
class Hash
{
	private $aHash = array();

	public function __construct($sHash)
	{
		$this->aHash = $this->parse($sHash);
	}

	public function add($sHash)
	{
		$aHash = $this->parse($sHash);
		$this->aHash = array_merge($this->aHash, $aHash);
	}

	public function set($sKey, $sValue)
	{
		if (strlen($sKey) == 0) return false;

		$this->aHash[strtoupper($sKey)] = trim($sValue);

		return true;
	}

	public function get($sKey, $sDefault = null, $bToUpper = false)
	{
		$sKey = strtoupper($sKey);
		return (array_key_exists($sKey, $this->aHash) ? ($bToUpper ? strtoupper($this->aHash[$sKey]) : $this->aHash[$sKey]) : $sDefault);
	}

	public function parse($sHash)
	{
		$aHash = array();
		$aAttributes = explode(';', $sHash);

		foreach ($aAttributes as $sAttribute)
		{
			$aAttribute = explode(':', $sAttribute);

			if (($iLength = count($aAttribute)) == 0) continue;

			$sKey = trim($aAttribute[0]);
			$sValue = ($iLength == 2 ? trim($aAttribute[1]) : '');

			$aHash[strtoupper($sKey)] = $sValue;
		}

		return $aHash;
	}

	public function toString()
	{
		$sHash = '';
		foreach ($this->aHash as $sKey=>$sValue)
		{
			if (strlen($sKey) != 0) $sHash .= $sKey . ':' . $sValue . ';';
		}

		return $sHash;
	}
}

?>