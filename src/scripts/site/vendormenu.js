
function VendorMenu(oVendors)
{
	var self = this;

	self.init = function()
	{
		self.oDocument = oVendors.ownerDocument;

		//Create root element.
		self.oVendorMenu = self.oDocument.createElement("VendorMenu");

		//Create Matches.
		self.oMatchTitle = self.oVendorMenu.appendChild(self.oDocument.createElement("MatchesTitle"));
		self.oMatchTitle.setAttribute("Name", "<table class='label' cellpadding='0' cellspacing='0' width='100%'><tr><td valign='bottom'><b style='color:blue'>Use Closest Match:</b></td><td align='right'><img src='"+UI.image+"icon=info.png' class='clickicon' onload='UI.setHelptip(this, \"Closest Match\", sClosestMatchHelptip)'/><td></tr></table>");
		self.oMatchTitle.setAttribute("NotSelectable", "true");

		//Create formatted title.
		self.oFormatTitle = self.oVendorMenu.appendChild(self.oDocument.createElement("FormattedTitle"));
		self.oFormatTitle.setAttribute("Name", "<table class='label' cellpadding='0' cellspacing='0' width='100%'><tr><td valign='bottom'><b style='color:blue'>Use Formatted Value:</b></td><td align='right'><img src='"+UI.image+"icon=info.png' class='clickicon' onload='UI.setHelptip(this, \"Original Value\", sFormattedValueHelptip)'/><td></tr></table>");
		self.oFormatTitle.setAttribute("NotSelectable", "true");

		self.oFormatted = self.oVendorMenu.appendChild(self.oDocument.createElement("Formatted"));
		self.oFormatted.setAttribute("Alt", "");
		self.oFormatted.setAttribute("Name", "");

		//Create original value.
		self.oOriginalTitle = self.oVendorMenu.appendChild(self.oDocument.createElement("OriginalTitle"));
		self.oOriginalTitle.setAttribute("Name", "<table class='label' cellpadding='0' cellspacing='0' width='100%'><tr><td valign='bottom'><b style='color:blue'>Use Original Value:</b></td><td align='right'><img src='"+UI.image+"icon=info.png' class='clickicon' onload='UI.setHelptip(this, \"Original Value\", sOriginalValueHelptip)'/><td></tr></table>");
		self.oOriginalTitle.setAttribute("NotSelectable", "true");

		self.oOriginal = self.oVendorMenu.appendChild(self.oDocument.createElement("Vendor"));
		self.oOriginal.setAttribute("Alt", "");
		self.oOriginal.setAttribute("Name", "");

		//Create all vendors
		self.oAllVendors = self.oVendorMenu.appendChild(oVendors);
		self.oAllVendors.setAttribute("Name", "<b style='color:blue'>See All Vendors:</b>");
		self.oAllVendors.setAttribute("NotSelectable", "true");
	}

	self.set = function(oTransaction)
	{
		//Get values.
		var sFormatted = XML.getAttribute(oTransaction, "Vendor_Clean", "");
		var sOriginal = XML.getAttribute(oTransaction, "Vendor_Original", "");
		var oVendorMatches = XML.getElementByName(oTransaction, "VendorMatches");

		self.setMatches(oVendorMatches);
		self.setMenuItem(self.oFormatted, sFormatted);
		self.setMenuItem(self.oOriginal, sOriginal);

		return self.oVendorMenu;
	}

	self.setMatches = function(oVendorMatches)
	{
		//Clear current matches.
		while (self.oMatchTitle.nextSibling.nodeName == "Match") self.oVendorMenu.removeChild(self.oMatchTitle.nextSibling);

		if (oVendorMatches == null) return;

		//Add the new matches.
		for (var oVendorMatch = oVendorMatches.firstChild, i = 0; oVendorMatch != null && i < 4; oVendorMatch = oVendorMatch.nextSibling, i++)
		{
			var sName = oVendorMatch.getAttribute("Name");
			var oMatch = self.oVendorMenu.insertBefore(self.oDocument.createElement("Match"), self.oFormatTitle);
			oMatch.setAttribute("Alt", sName);
			oMatch.setAttribute("Name", "&nbsp;&nbsp;&nbsp;&nbsp;" + sName);
		}
	}

	self.setMenuItem = function(oMenuItem, sValue)
	{
		oMenuItem.setAttribute("Alt", sValue);
		oMenuItem.setAttribute("Name", "&nbsp;&nbsp;&nbsp;&nbsp;" + sValue);
	}

	return self.init();
}
