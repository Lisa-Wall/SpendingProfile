
function TransactionToolbar(sId, oManager) //remove, copy, filter, getSelected, getSelectedDate
{
	var self = this;

	self.oExport = new ExportPopup();
	self.oCalendar = new UI_Calendar();

	self.oFilters = null;
	self.oAdvancedSearch = null;

	self.aSelected = null;

	self.init = function()
	{
		var sSearch = "<input class='textfield' style='height:18px;width:220px'/><button type='button' class='button' style='height:18px;width:17px;padding-right:1px;padding-left:1px;vertical-align:bottom'><img src='"+UI.image+"icon=drop_button.png'/></button><input type='button' class='button' value='Search' style='height:18px;font-size:8pt'/><button type='button' class='button' style='height:18px;vertical-align:bottom'><img src='"+UI.image+"icon=info_button.png'/></button>";

		var oToolbar = new UI_Toolbar(sId);

		oToolbar.addButton("DELETE", "Delete", null, "Delete selected transaction(s).", self.onDelete);
		oToolbar.addButton("COPY"  , "Copy"  , null, "Copy the selected transaction(s) to a different month.", self.onCopy);
		oToolbar.addButton("EXPORT", "Export", null, "Export transactions to your computer.", self.onExport);
		oToolbar.addButton("PRINT", "Print", null, "View a printable version of this page.", self.onPrint);
		oToolbar.addButton("STATEMENT", "Statement", null, "Download a monthly statement.", self.onStatement);
		//oToolbar.addSeparator();
		var oCell = oToolbar.addButton("FILTER", sSearch, null, "", null);
		oToolbar.addButton("ADVANCEDSEARCH", "Advanced Search", null, "Advanced search field.", self.onAdvancedSearch);
		var oHelp = oToolbar.addButton("TABLEHELP", null, "help.gif", "", null, true);

		var oInputs = oCell.getElementsByTagName("INPUT");
		var oButtons = oCell.getElementsByTagName("BUTTON");

		oInputs[1].onclick = self.onFilter;
		oInputs[0].onkeypress = self.onFilterKey;
		self.oFilter = oInputs[0];
		
		oButtons[0].onclick = self.onShowSavedSearches;

		UI.setHelptip(oButtons[1], "Search", sSearchHelptip);
		UI.setHelptip(oHelp, "Transaction Table", sTransactionTableHelptip);
		
		self.oFilters = new FiltersPopup(self.onSelectFilter);
		self.oAdvancedSearch = new AdvancedSearch(self.onUseFilter);
	}

	self.onSelectFilter = function(sFilter)
	{
		self.oFilter.value = sFilter;
		self.onFilter();
	}

	self.onAdvancedSearch = function()
	{
		self.oAdvancedSearch.show();
	}

	self.onShowSavedSearches = function()
	{
		self.oFilters.show();
		
		self.oFilters.style.width = "250px";
		
		UI.showPopupRelativeTo(self.oFilters, this.previousSibling, -10, 20, true);
	}

	self.onCopy = function()
	{
		self.aSelected = oManager.getSelected();

		if (self.aSelected.length == 0) alert("Please select transactions to copy.");
		else
		{
			self.oCalendar.setMonth(new Date());

			//Set the event for the copy action.
			self.oCalendar.onSelect = self.onCopyDaySelect;
			self.oCalendar.onSelectMonth = self.onCopyMonthSelect;

			UI.showPopupRelativeTo(self.oCalendar, this, -10, 20, true);
		}
	}

	self.onCopyMonthSelect = function(iYear, iMonth, iDay)
	{
		var oFromDate = oManager.getSelectedDate();

		//If selection is the same as current then drill in.
		if (oFromDate.getFullYear() == iYear && oFromDate.getMonth()+1 == iMonth) return true;

		var sConfirm = "Copy the selected transaction(s) to " + CDate.months[iMonth] + ", " + iYear + "?";

		UI.hide(self.oCalendar);
		if (confirm(sConfirm)) oManager.copy(self.aSelected, CDate.format(iYear, iMonth, iDay), false);

		return false;
	}

	self.onCopyDaySelect = function(iYear, iMonth, iDay)
	{
		var sConfirm = "Copy the selected transaction(s) to " + CDate.months[iMonth] + " " + iDay + ", " + iYear + "?";

		UI.hide(self.oCalendar);
		if (confirm(sConfirm)) oManager.copy(self.aSelected, CDate.format(iYear, iMonth, iDay), true);

		return false;
	}

	self.onDelete = function()
	{
		var sMessage = "Are you sure you want to delete the selected transaction(s)?";
		var aSelected = oManager.getSelected();

		if (aSelected.length > 0 && confirm(sMessage)) oManager.remove(aSelected);
	}

	self.onUseFilter = function(sFilter)
	{
		self.oFilter.value = sFilter;
		self.onFilter();
	}

	self.onFilter = function()
	{
		oManager.filter(self.oFilter.value);
	}

	self.onFilterKey = function(oEvent)
	{
		oEvent = (oEvent?oEvent:event);
		if(oEvent.keyCode == KeyCode.ENTER) self.onFilter();
	}

	self.onExport = function()
	{
		UI.showPopupRelativeTo(self.oExport, this, -10, 20, true);
	}

	self.onPrint = function()
	{
		window.location = "printview.php";
	}

	self.onStatement = function()
	{
		self.oCalendar.setMonth(new Date());
		
		//Set the event for the statement action.
		self.oCalendar.onSelect = null;
		self.oCalendar.onSelectMonth = self.onStatementMonthSelect;

		UI.showPopupRelativeTo(self.oCalendar, this, -10, 20, true);
	}

	self.onStatementMonthSelect = function(iYear, iMonth, iDay)
	{
		var sRequest = "<Statement.download Month='" + CDate.format(iYear, iMonth, iDay) + "' />";
		var oWindow = window.open(AJAX.url + "?request=" + encodeURIComponent(sRequest) + "&SID=" + Math.random(), "Statement", "width=200,height=100,scrollbars=no,resizable=no,toolbar=no,menubar=no,location=no,directories=no,status=no", true);
	}

	self.setFilter = function(sFilter)
	{
		self.oFilter.value = sFilter;
	}

	self.init();
}
