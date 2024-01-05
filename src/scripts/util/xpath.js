
var XPath =
{
	get:function(oRoot, sPath)
	{
		var iIndex = 0;
		var aPath = sPath.split("/");
		var oElement = oRoot;

		if (oRoot != null)
		{
			//If starts with "/" then start from document element, otherwise from current element.
			if(aPath[0] == "")
			{
				iIndex = 1;
				oElement = oRoot.ownerDocument.documentElement;
			}

			for (; iIndex < aPath.length; iIndex++)
			{
				var sSubPath = aPath[iIndex];
				var iFirstChar = sSubPath.charAt(0);

				if(iFirstChar == "@")
				{
					sName = sSubPath.substr(1);
					return oElement.getAttribute(sName);
				}
				else if(iFirstChar == "$")
				{
					return XML.save(oElement);
				}
				else if(sSubPath == "#")
				{
					return oElement.firstChild.nodeValue;
				}
				else
				{
					oElement = XPath.find(oElement, sSubPath);
				}

				if(oElement == null) break;
			}
		}

		return oElement;
	},

	insert:function(oRoot, sPath, oObject)
	{
		var iIndex = 0;
		var aPath = sPath.split("/");
		var oElement = oRoot;

		if (oRoot != null)
		{
			//If starts with "/" then start from document element, otherwise from current element.
			if(aPath[0] == "")
			{
				iIndex = 1;
				oElement = oRoot.ownerDocument.documentElement;
			}

			for (; iIndex < aPath.length; iIndex++)
			{
				var sSubPath = aPath[iIndex];
				var iFirstChar = sSubPath.charAt(0);

				if(iFirstChar == "@")
				{
					var sName = sSubPath.substr(1);
					oElement.setAttribute(sName, oObject);
					return oElement;
				}
				else if(iFirstChar == "$")
				{
					XML.clear(oElement);
					oElement.appendChild(oObject);
					return oElement;
				}
				else if(sSubPath == "#")
				{
					XML.clear(oElement);
					oElement.appendChild(oRoot.ownerDocument.createTextNode(oObject));
					return oElement;
				}
				else
				{
					var oChild = XPath.find(oElement, sSubPath);
					oElement = (oChild != null ? oChild : oElement.appendChild(oRoot.ownerDocument.createElement(sSubPath)));
				}
			}
		}

		return oElement;
	},

	find:function(oParent, sNodeName)
	{
		oElement = oParent.firstChild;
		while (oElement != null)
		{
			if(oElement.nodeName == sNodeName) break;
			oElement = oElement.nextSibling;
		}

		return oElement;
	},

	getPath:function(oParnet, oElement)
	{
		sPath = oElement.nodeName;
		oElement = oElement.parentNode;

		while (oElement != oParnet)
		{
			sPath = oElement.nodeName + "/" + sPath;
			oElement = oElement.parentNode;
		}

		return sPath;
	}
}
