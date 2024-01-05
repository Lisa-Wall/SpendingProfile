
var Form =
{
	clearValue:function(pInput)
	{
		Form.setValue(pInput, '');
	},

	get:function(oModel)
	{
		var sModel   = "";
		var oCurrent = oModel;

		while ((oCurrent = XML.next(oModel, oCurrent)) != null)
		{
			if (oCurrent.nodeType != 1) continue;

			var sPath = XPath.getPath(oModel, oCurrent);
			var pElement = document.getElementById(sPath);

			if (pElement != null)
			{
				XML.clear(oCurrent);
				var pValue = Form.getValue(pElement);

				if (typeof(pValue) == "string") oCurrent.appendChild(oCurrent.ownerDocument.createTextNode(pValue));
				else if (pValue != null)        oCurrent.appendChild(pValue);
			}
		}

		return oModel;
	},

	set:function(oModel)
	{
		var sModel   = "";
		var oCurrent = oModel;

		while ((oCurrent = XML.next(oModel, oCurrent)) != null)
		{
			if (oCurrent.nodeType != 1) continue;

			//Get the path of the current element
			var sPath = XPath.getPath(oModel, oCurrent);

			//Get the value from the model.
			if(oCurrent.firstChild != null)
			{
				if(oCurrent.firstChild.nodeType == 3) Form.setValue(sPath, oCurrent.firstChild.nodeValue);
				else                                  Form.setValue(sPath, oCurrent.firstChild);
			}
		}

		return oModel;
	},

	clear:function(oModel)
	{
		var sModel   = "";
		var oCurrent = oModel;

		while ((oCurrent = XML.next(oModel, oCurrent)) != null)
		{
			//If it is an element.
			if (oCurrent.nodeType == 1)
			{
				//Get the path of the current element
				var sPath = XPath.getPath(oModel, oCurrent);

				//Get the path object.
				var pInput = document.getElementById(sPath);

				//Clear the value.
				if (pInput != null && pInput.getAttribute("noclear") != "true") Form.clearValue(pInput);
			}
		}

		return oModel;
	}
};
