
function UI_Toolbar(sId)
{
	var self = (typeof(sId) == "string" ? document.getElementById(sId) : sId);

	self.init = function()
	{
		self.className = "ui_toolbar";
		self.cellPadding = 0;
		self.cellSpacing = 1;

		self.oRow = self.insertRow(-1);
		self.oRow.insertCell(-1).width = "100%";
		return self;
	}

	self.addButton = function(sId, sText, sIcon, sTooltip, fAction, bInsertAtEnd)
	{
		var oCell = self.oRow.insertCell(self.oRow.cells.length + (UI.defaultValue(bInsertAtEnd, false) ? 0 : -1));

		oCell.ident = sId;
		oCell.text = sText;
		oCell.icon = sIcon;
		oCell.action = fAction;
		oCell.className = "ui_toolbar_item";
		oCell.innerHTML = (sIcon != null ? "<img src='"+UI.image+"icon="+sIcon+"' style='vertical-align:middle'/>" : "") + (sText != null ? "&nbsp;" + sText + "&nbsp;" : "");

		if (sTooltip != null && sTooltip.length > 0) UI.setTooltip(oCell, sTooltip);

		oCell.onclick = function(){ if (this.action != null) this.action(this.ident); };
		oCell.onmouseout = function(){ this.className = "ui_toolbar_item"; };
		oCell.onmouseover = function(){ this.className = "ui_toolbar_item_over"; };

		return oCell;
	}

	self.addSeparator = function()
	{
		var oCell = self.oRow.insertCell(self.oRow.cells.length-1);
		oCell.innerHTML = "<img src='"+UI.image+"image=ui/toolbar/separater.png'/>";
	}

	self.get = function(sId)
	{
		for (var oCell = self.rows[0].firstChild; oCell != null; oCell=oCell.nextSibling) if (oCell.ident == sId) return oCell;
		return null;
	}

	return self.init();
}

function UI_ToolbarBuilder()
{
	var self = this;
	
	self.sItems = "";
	
	self.addButton = function(sId, sText, bAddLink)
	{
		if (bAddLink)
		{
			self.sItems += "<li class='ui_toolbar_li' " + (sId == null ? "" : "id='"+sId+"'") + "><a class='ui_toolbar_a' href='javascript:;' ><span>" + sText + "</span></a></li>";
		}
		else
		{
			self.sItems += "<li class='ui_toolbar_li' " + (sId == null ? "" : "id='"+sId+"'") + "><span class='ui_toolbar_a'>" + sText + "</span></li>";
		}
	}
	
	self.addSeparator = function()
	{
		self.sItems += "<li class='ui_toolbar_li'><span class='ui_toolbar_separator'>|</span></li>";;
	}
	
	self.build = function()
	{
		return "<ul class='ui_toolbar_ul'>" + self.sItems + "</ul>";
	}
}