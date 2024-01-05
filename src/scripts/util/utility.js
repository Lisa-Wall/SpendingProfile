
String.prototype.trim  = function() { return this.replace(/^\s+|\s+$/g, ""); }
String.prototype.ltrim = function() { return this.replace(/^\s+/,""); }
String.prototype.rtrim = function() { return this.replace(/\s+$/,""); }

String.prototype.startsWidthIgnoreCase = function(sValue)
{
	return (sValue == null || sValue.length == 0 || (this.toLowerCase().indexOf(sValue.toLowerCase()) == 0));
}

String.prototype.equalIgnoreCase = function(sValue)
{
	return (sValue == null || sValue.length == 0 || this.toLowerCase() == sValue.toLowerCase());
}

String.prototype.startsWith = function(sValue)
{
	return (this.indexOf(sValue) == 0);
}

Array.prototype.indexOf = function(oValue)
{
	for (var i = 0; i < this.length; i++) if(this[i] == oValue) return i;
	return -1;
}

Array.prototype.lastIndexOf = function(oValue)
{
	for (var i = this.length-1; i >= 0; i--) if(this[i] == oValue) return i;
	return -1;
}

Array.prototype.contains = function(oValue)
{
	for (var i = 0; i < this.length; i++) if(this[i] == oValue) return true;
	return false;
}

Array.prototype.remove = function(iIndex)
{
	var oValue = this[iIndex];
	this.splice(iIndex, 1);
	return oValue;
}

Array.prototype.removeIt = function(oValue)
{
	var iIndex = this.indexOf(oValue);
	if (iIndex != -1) this.remove(iIndex);
}

Array.prototype.insert = function(iIndex, oValue)
{
	return this.splice(iIndex, 0, oValue);
}

Array.prototype.getMap = function(oValue)
{
	for (var i = 0; i < this.length; i+=2) if(this[i] == oValue) return this[i+1];
	return null;
}

var iDEBUG = 0;

var Utility =
{
	login: function()
	{
		window.location = oSession.url+"login.php?url=" + encodeURIComponent(window.location.href);
	},

	location: function(sPage)
	{
		window.location = sPage;
	},

	debug:function(sMessage)
	{
		var oDebug = document.getElementById("DEBUG");
		if (oDebug == null)
		{
			oDebug = document.body.insertBefore(document.createElement("DIV"), document.body.firstChild)
			oDebug.id = "DEBUG";
		}

		oDebug.innerHTML = (iDEBUG++) + " - " + sMessage;
	},

	isDefined:function(pParam)
	{
			return (typeof(pParam) != "undefined");
	},

	getParam: function(sName, sDefault)
	{
		if (!Utility.isDefined(sDefault)) sDefault = null;

		var sHref  = window.location.href;
		var iIndex = sHref.indexOf("?");

		if (iIndex == -1) return sDefault;

		var sQueryString = sHref.substr(iIndex + 1);
		var aQueryString = sQueryString.split("&");

		for (var iParam = 0; iParam < aQueryString.length; iParam++)
		{
			var aParam = aQueryString[iParam].split("=");
			if (aParam[0] == sName) return decodeURIComponent(aParam[1]);
		}

		return sDefault;
	},

	isIE:function ()
	{
		return ((navigator.userAgent.toLowerCase().indexOf("msie") != -1) && (navigator.userAgent.toLowerCase().indexOf("opera") == -1));
	},

	isFireFox:function()
	{
		return ((navigator.userAgent.toLowerCase().indexOf("gecko") != -1) || (navigator.userAgent.toLowerCase().indexOf("firefox") != -1));
	},

	formatNumber:function(iNumber, iRound)
	{
		iNumber = iNumber.toString().replace(/\$|\,/g,'');
		if (isNaN(iNumber)) iNumber = "0";

		var bSign = (iNumber == (iNumber = Math.abs(iNumber)));

		iNumber = Math.floor(iNumber * iRound + 0.50000000001);
		iDecimal = new String(iNumber%iRound);
		sRound = new String(iRound);

		while (iDecimal.length < sRound.length-1) iDecimal += "0";

		iNumber = Math.floor(iNumber/iRound);

		return iNumber+"."+iDecimal;
	},

	formatCurrency:function(iNumber, iCurrency)
	{
		iNumber = iNumber.toString().replace(/\$|\,/g,'');

		if (isNaN(iNumber)) iNumber = "0";

		var bSign = (iNumber == (iNumber = Math.abs(iNumber)));

		iNumber = Math.floor(iNumber * 100 + 0.50000000001);

		var iCent = iNumber%100;

		iNumber = Math.floor(iNumber/100).toString();

		if (iCent < 10) iCent = "0" + iCent;

		for (var i = 0; i < Math.floor((iNumber.length - (1+i))/3); i++)
		{
			iNumber = iNumber.substring(0, iNumber.length -(4 * i + 3)) + ',' + iNumber.substring(iNumber.length - (4*i+3));
		}

		return (((bSign) ? '' : '-') + iCurrency + iNumber + '.' + iCent);
	}
}