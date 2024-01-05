
function UI_SmartPopup(onSelect, bSubmenu)
{
	var self = UI_Shadow();

	self.oItem = null;
	self.oItems = null;
	self.onSelect = UI.defaultValue(onSelect, null);
	self.bSubmenu = UI.defaultValue(bSubmenu, false);
	self.oSubmenu = null;

	var sDilimiter = UI.delimiter;

	self.init = function()
	{
		self.shadowPane.className = "ui_smartpopup";

		self.panel = self.shadowPane.appendChild(document.createElement("DIV"));
		self.menu = self.panel.appendChild(document.createElement("TABLE"));
		self.menu.className = "ui_smartmenu";
		self.menu.cellSpacing = 0;
		self.menu.cellPadding = 0;

		//Insure that if these events occur on the event they are not bouble to the body and window.
		self.onscroll = function(event){ return UI.cancelBubble(event); };
		self.onresize = function(event){ return UI.cancelBubble(event); };
		self.onkeypress = function(event){ return UI.cancelBubble(event); };
		self.onmousedown = function(event){ return UI.cancelBubble(event); };

		return self;
	}

	self.hide = function()
	{
		if (self.oSubmenu != null) self.oSubmenu.hide();

		UI.hide(self);
	}
	self.onHide = self.hide;

	self.show = function(oItems, sAutoComplete, oRelative, oOffset, oPosition, oBounds)
	{
		self.hide();

		self.oItems = oItems;

		oOffset = UI.defaultValue(oOffset, {x: 0, y: 0});
		oBounds = UI.defaultValue(oBounds, {minWidth: 200, minHeight: 20, maxWidth: 300, maxHeight: 300});
		oPosition = UI.defaultValue(oPosition, {position: "BOTTOM", alignment: "RIGHT"});

		self.panel.style.width = "auto";
		self.panel.style.height = "auto";

		document.body.appendChild(self);

		var sSubMenu = self.paint(self.menu, oItems, sAutoComplete);

		UI.show(self, UI.bounds(oRelative), oPosition, oOffset, oBounds, !self.bSubmenu);

		//If there is a submenu then show it after showing the current one.
		if (sSubMenu != null && self.iRows == 1)
		{
			if (self.oRow.item.firstChild != null) self.showSubmenu(self.oRow.item, sSubMenu, self.oRow);
		}
		//If there are no rows then don't show the menu.
		else if (self.iRows == 0)
		{
			self.hide();
		}

		return self;
	}

	self.showSubmenu = function(oItem, sAutoComplete, oRelative)
	{
		self.oItem = oItem;
		if (self.oSubmenu == null) self.oSubmenu = new UI_SmartPopup(self.subMenuAction, true);

		return self.oSubmenu.show(oItem, sAutoComplete, oRelative, {x: -3, y: -10}, {position: "RIGHT", alignment: "LEFT"}, {minWidth: 50, minHeight: 20, maxWidth: 300, maxHeight: 300});
	}

	self.subMenuAction = function(sValue, iId)
	{
		if (self.onSelect != null)
		{
			if (self.oItem.getAttribute("NotSelectable") == null)
			{
				self.onSelect(self.oItem.getAttribute("Name") + sDilimiter + sValue, iId);
			}
			else
			{
				self.onSelect(sValue, iId);
			}
		}
	}

	self.paint = function(oMenu, oItems, sAutoComplete)
	{
		var oValue = self.split(sAutoComplete);

		var sAutoComplete = oValue.first;
		var iAutoComplete = oValue.first.length;

		self.iRows = 0;
		self.oRow = null;

		var oRow = self.clearTable(oMenu);
		for (var oItem = oItems.firstChild; oItem != null; oItem = oItem.nextSibling)
		{
			var sText = oItem.getAttribute("Name");

			if (!sText.startsWidthIgnoreCase(sAutoComplete)) continue;
			else if (iAutoComplete > 0) sText = "<b style='color:blue'>"+ sText.substring(0, iAutoComplete) +"</b>" + sText.substring(iAutoComplete);

			oRow = self.getNextRow(oMenu, oRow);

			oRow.item = oItem;
			oRow.className = "ui_smartitem";

			oRow.cells[0].innerHTML = sText;
			oRow.cells[1].innerHTML = (oItem.firstChild != null ? "<img src='" + UI.image + "image=ui/popup/submenu.png' align='right'/>" : "");

			if (oItem.getAttribute("NotSelectable") == null)
			{
				oRow.onclick = self.onItemClick;
				oRow.onmouseout = function() { this.className = "ui_smartitem"; };
				oRow.onmouseover = function() { this.className = "ui_smartitem_over"; if (this.item.firstChild != null) self.showSubmenu(this.item, "", this); else if (self.oSubmenu != null) self.oSubmenu.hide(); };
			}
			else if (oItem.firstChild != null)
			{
				oRow.onclick = null;
				oRow.onmouseout = null;
				oRow.onmouseover = function() { self.showSubmenu(this.item, "", this); };
			}
			else
			{
				oRow.onclick = null;
				oRow.onmouseout = null;
				oRow.onmouseover = null;
			}

			self.iRows++; self.oRow = oRow;
		}

		return oValue.second;
	}

	self.onItemClick = function()
	{
		if (self.onSelect != null)
		{
			var sAlt = this.item.getAttribute("Alt");
			self.onSelect((sAlt == null ? this.item.getAttribute("Name") : sAlt), this.item.getAttribute("Id"));
		}
	}

	self.getNextRow = function(oTable, oRow)
	{
		if (oRow == null || oRow.nextSibling == null)
		{
			oRow = oTable.insertRow(-1);
			oRow.insertCell(-1);
			oRow.insertCell(-1);
		}
		else
		{
			oRow = oRow.nextSibling;
			oRow.style.display = "";
		}

		return oRow;
	}

	self.clearTable = function(oTable)
	{
		for (var i = 0; i < oTable.rows.length; i++) oTable.rows[i].style.display = "none";
		return (oTable.rows.length > 0 ? oTable.rows[0] : null);
	}

	self.split = function(sValue)
	{
		var iIndex = sValue.indexOf(sDilimiter);
		if (iIndex > 0) return {first: sValue.substring(0, iIndex).trim(), second: sValue.substring(iIndex+sDilimiter.length).trim() };
		else            return {first: sValue.trim(), second: null};
	}

	return self.init();
}