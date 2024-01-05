
/**
 * @author Lisa Wall
 * @date 2009-04-06
 */
function Hash(sHash)
{
	var self = this;
	self.aHash = null

	self.set = function(sKey, sValue)
	{
		if (sKey.length == 0) return false;

		sKey = sKey.toUpperCase();
		sValue = sValue.trim();

		self.aHash[sKey] = sValue;

		return true;
	}

	self.get = function(sKey, sDefault, bToUpper)
	{
		if (typeof(sDefault) == "undefined") sDefault = null;
		if (typeof(bToUpper) == "undefined") bToUpper = false;

		sKey = sKey.toUpperCase();
		var sValue = self.aHash[sKey];

		return (sValue == "undefined" ? sDefault : (bToUpper ? sValue.toUpperCase() : sValue));
	}

	self.parse = function(sHash)
	{
		var aHash = { };
		var aAttributes = sHash.split(";");

		for (var i = 0; i < aAttributes.length; i++)
		{
			var aAttribute = aAttributes[i].split(":");

			if (aAttribute.length == 0) continue;

			sKey = aAttribute[0].trim().toUpperCase();
			sValue = (aAttribute.length == 2 ? aAttribute[1].trim() : '');

			aHash[sKey] = sValue;
		}

		return aHash;
	}

	self.toString = function()
	{
		var sHash = '';
		for (var sKey in self.aHash)
		{
			if (sKey.length == 0) continue;
			sHash += sKey + ":" + self.aHash[sKey] + ";";
		}

		return sHash;
	}

	self.aHash = self.parse(sHash);
}

