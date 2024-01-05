
var XML =
{
	createDocument:function()
	{
		if (window.ActiveXObject) return new ActiveXObject("Msxml2.DOMDocument");
		else return document.implementation.createDocument("", "", null);
	},

	createElement:function(sName)
	{
		var oDocument = XML.createDocument();
		return oDocument.createElement(sName);
	},

	save:function(pElement)
	{
		if (window.ActiveXObject) return pElement.xml
		else
		{
			var pSerializer = new XMLSerializer();
			return pSerializer.serializeToString(pElement);
		}
	},

	clear:function(oElement)
	{
		while (oElement.firstChild != null) oElement.removeChild(oElement.lastChild);
	},

	clean:function(sString)
	{
		sString = new String(sString);
		sString = sString.replace(/&/g, "&amp;");
		sString = sString.replace(/</g, "&lt;");
		sString = sString.replace(/>/g, "&gt;");
		sString = sString.replace(/'/g, "&apos;");  //'
		sString = sString.replace(/"/g, "&quot;");  //"
		return sString;
	},

	toString:function(sXML)
	{
		sString = new String(sXML);
		sString = sString.replace(/&amp;/g, "&");
		sString = sString.replace(/&lt;/g, "<");
		sString = sString.replace(/&gt;/g, ">");
		sString = sString.replace(/&apos;/g, "'");  //'
		sString = sString.replace(/&quot;/g, "\"");  //"
		return sString;
	},

	serialize:function(bClosed, sName /* ,... */ )
	{
		var sXML = "<" + sName;
		for (i = 2; i < arguments.length; i+=2)
		{
			var sName  = arguments[i];
			var sValue = arguments[i+1];
			if(sValue != null) sXML += " " + sName + '="' + XML.clean(sValue) + '"';
		}

		return sXML + (bClosed ? "/>" : ">");
	},

	serializeFromArray:function(bClosed, sName, aAttributes)
	{
		var sXML = "<" + sName;
		for (i = 0; i < aAttributes.length; i+=2)
		{
			var sName  = aAttributes[i];
			var sValue = aAttributes[i+1];
			if(sValue != null) sXML += " " + sName + '="' + XML.clean(sValue) + '"';
		}

		return sXML + (bClosed ? "/>" : ">");
	},

	fill:function(oElement /*, ... */)
	{
		for (i = 1; i < arguments.length; i+=2)
		{
			var sName  = arguments[i];
			var sValue = arguments[i+1];
			oElement.setAttribute(sName, sValue);
		}

		return oElement;
	},

	toArray:function(oRoot)
	{
		var aRoot = new Array();

		if (oRoot == null) return aRoot;

		var oElement = oRoot.firstChild;
		while (oElement != null)
		{
			if (oElement.nodeType == 1) aRoot.push(oElement);
			oElement = oElement.nextSibling;
		}

		return aRoot;
	},

	arrayToXML:function(aArray, sElement, sAttribute)
	{
		var oDocument = XML.createDocument();
		var oRoot = oDocument.createElement(sElement);
		for (var i = 0; i < aArray.length; i++) oRoot.appendChild(oDocument.createElement(sElement)).setAttribute(sAttribute, aArray[i]);
		return oRoot;
	},

	next:function(pRoot, oCurrent)
	{
		if (oCurrent.firstChild != null)
		{
			return oCurrent.firstChild;
		}

		if (oCurrent == pRoot) return null;
		while (oCurrent.nextSibling == null)
		{
			oCurrent = oCurrent.parentNode;
			if (oCurrent == pRoot) return null;
		}

		return oCurrent.nextSibling;
	},

	normalize:function(oRoot)
	{
		var oChild = oRoot.firstChild;
		while(oChild != null)
		{
			if(oChild.nodeType == 3)
			{
				var oTemp = oChild;
				oChild = oChild.nextSibling;
				oRoot.removeChild(oTemp);
			}
			else
			{
				XML.normalize(oChild);
				oChild = oChild.nextSibling;
			}
		}

		return oRoot;
	},

	isBefore:function(oFirst, oSecond)
	{
		while(oFirst != null && oFirst != oSecond) oFirst = oFirst.nextSibling;
		return (oFirst == oSecond);
	},

	getAttribute:function(oElement, sName, sDefault)
	{
		var sValue = oElement.getAttribute(sName);
		return (sValue == null ? sDefault : sValue);
	},

	getAttributeInt:function(oElement, sName, sDefault)
	{
		var sValue = oElement.getAttribute(sName);

		return (sValue == null ? sDefault : parseInt(sValue, 10));
	},

	getAttributeBool:function(oElement, sName, bDefault)
	{
		var sValue = oElement.getAttribute(sName);
		return (sValue == null ? bDefault : (sValue == "1" || sValue.toLowerCase() == "true"));
	},

	getElementByName:function(oRoot, sName, bDeep)
	{
		for (var oElement = oRoot.firstChild; (oElement != null && oElement.nodeName != sName); oElement = oElement.nextSibling);
		return oElement;
	},

	getElementByAttribute:function(oRoot, sName, sValue, bDeep)
	{
	//TODO: implement bDeep.

		for (var oElement = oRoot.firstChild; (oElement != null && oElement.getAttribute(sName) != sValue); oElement = oElement.nextSibling);
		return oElement;
	},

	equalAttributes: function(oFirst, oSecond, aAttributes)
	{
		for(var i = 0; i < aAttributes.length; i++)
		{
			var sName = aAttributes[i];
			if(oFirst.getAttribute(sName) != oSecond.getAttribute(sName)) return false;
		}

		return true;
	}
}
