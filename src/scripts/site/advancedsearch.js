
function AdvancedSearch(fUseSearch)
{
	var self = this;

	self.oWindow = new UI_Window('Advanced Search', false, false);

	self.aAndOr = new Array('and', 'or');
	self.aOperators = new Array('=', '!=', '<=', '>=', '<', '>');
	self.aColumns = new Array('', 'Id', 'Debit', 'Fixed', 'Date', 'Amount', 'Category', 'Vendor', 'Account', 'Notes');
	
	self.aFixed = new Array('Fixed', 'Variable');
	self.aDebit = new Array('Expense', 'Income');
	
	self.oCalendar = new UI_Calendar();

	self.init = function()
	{
		var sSearch = "<table class='textfield'><tr><td valign='top'>Search:</td><td><div style='color:gray;float:right;width:370'></div></td></tr></table><br/>";
		var sFieldset = "<fieldset class='fieldset'><legend>Conditions</legend><table class='label'><tr><td width='14px'>&nbsp;</td><td>Look For:</td><td colspan='3'><input class='textfield' type='text' style='width:280px' /></td><td></td></tr></table></fieldset>";
		var sNote = "<span style='font-size:8pt;color:gray'><b>Note:</b> Use * as a wildcards. ex: search all car subcategories by using car:*</span>";
		var sButtons = "<center style='padding-top:10px'><input type='button' class='button' style='width:70px' value='Use'/>&nbsp;<input type='button' class='button' style='width:70px' value='Save'/><span style='padding:0 10 0 10'></span><input type='button' class='button' style='width:70px' value='Clear'/>&nbsp;<input type='button' class='button' style='width:70px' value='Close'/></center>";

		self.oWindow.onClose = self.onClose;
		self.oWindow.windowPane.className = "windowpane";
		self.oWindow.windowPane.innerHTML = sSearch + sFieldset + sNote + sButtons;
		
		var oInputs = self.oWindow.windowPane.getElementsByTagName("INPUT");
		self.oTable = self.oWindow.windowPane.getElementsByTagName("TABLE")[1];
		
		self.oSearch = self.oWindow.windowPane.getElementsByTagName("DIV")[0];
		self.oGeneral = oInputs[0];
		self.oGeneral.onkeyup = self.rebuild;

		oInputs[oInputs.length-1].onclick = self.onClose;
		oInputs[oInputs.length-2].onclick = self.onClear;
		oInputs[oInputs.length-3].onclick = self.onSave;
		oInputs[oInputs.length-4].onclick = self.onUse;

		self.onAdd();
	}

	self.show = function()
	{
		if (!self.oWindow.isVisible())
		{
			self.onClear();

			UI.centerWindow(self.oWindow);
			
			self.rebuild();
		}
	}

	self.onClose = function()
	{
		UI.hide(self.oWindow);
	}


	self.getOptions = function(aOptions, sDefault)
	{
		var sOptions = '';
		for (var i = 0; i < aOptions.length; i++)
		{
			var sValue = aOptions[i];
			var sSelected = (sValue == sDefault ? " Selected='true'" : "");
			sOptions += "<option value='"+sValue+"'"+sSelected+">"+sValue+"</option>";
		}

		return sOptions;
	}

	self.rebuild = function()
	{
		var oAndOr = self.oTable.rows[0].cells[3].firstChild;

		var sSearch = (self.oGeneral.value.length == 0 ? '' : self.oGeneral.value + (oAndOr == null ? '' : (oAndOr.value == "or" ? " OR" : "" )));

		for (var i = 1; i < self.oTable.rows.length; i++)
		{
			var oRow = self.oTable.rows[i];
			
			if (oRow.oColumn.value.length == 0) continue;
			
			var sValue = oRow.oValue.value;
			var sColumn = oRow.oColumn.value;

			if (sColumn == "Fixed") sValue = (sValue == 'Fixed' ? '0' : '1');
			else if (sColumn == "Debit") sValue = (sValue == 'Expense' ? '0' : '1');
			else if (sColumn == "Vendor" || sColumn == "Account" || sColumn == "Category") sValue = oRow.oValue.oInput.value;
			
			var sCondition = " ";
			sCondition += oRow.oColumn.value;
			sCondition += oRow.oOperation.value;
			sCondition += (sValue.length == 0 || sValue.indexOf(" ") >= 0 ? '"' + sValue + '"' : sValue);

			if (oRow.oAndOr != null) sCondition += " " + (oRow.oAndOr.value == "or" ? "OR" : '');

			sSearch += sCondition;
		}
		
		self.oSearch.innerHTML = sSearch;
	}


	self.onAdd = function()
	{
		//Get previous row to add 'and/or'.
		var oPrevious = self.oTable.rows[self.oTable.rows.length-1];
		oPrevious.cells[oPrevious.cells.length-1].innerHTML = "<select class='dropdown' style='width:50px'>"+self.getOptions(self.aAndOr)+"</select>";
		oPrevious.oAndOr = oPrevious.cells[oPrevious.cells.length-1].firstChild;
		oPrevious.oAndOr.onchange = self.rebuild;

		//Create a new row.
		var oRow = self.oTable.insertRow(-1);
		oRow.insertCell(-1).innerHTML = "<input type='button' class='button' value='X'/>";
		oRow.insertCell(-1).innerHTML = "Column:";
		oRow.insertCell(-1).innerHTML = "<select class='dropdown'>"+self.getOptions(self.aColumns)+"</select>";
		oRow.insertCell(-1).innerHTML = "<select class='dropdown'>"+self.getOptions(self.aOperators)+"</select>";
		oRow.insertCell(-1).innerHTML = "<input type='text' class='textfield' style='width:150px' />";
		oRow.insertCell(-1).innerHTML = "<input type='button' class='button' value='Add' style='width:100%'/>";

		//Get the the input objects.
		oRow.oColumn    = oRow.cells[2].firstChild;
		oRow.oOperation = oRow.cells[3].firstChild;
		oRow.oValue     = oRow.cells[4].firstChild;
		oRow.oAndOr     = null;

		//Add the events to the inputs.
		oRow.cells[0].firstChild.onclick = function(){ self.onRemove(oRow); };
		oRow.cells[5].firstChild.onclick = function(){ self.onAdd(); };

		oRow.oColumn.onchange = function() { self.updateValueField(oRow, this.value); self.rebuild(); }
		oRow.oOperation.onchange = self.rebuild;
		oRow.oValue.onkeyup = self.rebuild;

		//Rebuild the search text.
		self.rebuild();
	}
	
	self.updateValueField = function(oRow, sValue)
	{
		var oCell = oRow.cells[4];
	
		if (sValue == 'Id' || sValue == 'Amount' || sValue == 'Notes')
		{
			oCell.innerHTML = "<input type='text' class='textfield' style='width:150px' />";
			oRow.oValue = oCell.firstChild;
			oRow.oValue.onkeyup = self.rebuild;
		}
		else if (sValue == 'Vendor' || sValue == 'Category' || sValue == 'Account')
		{
			var oDropdown = new UI_SmartDropdown();

			oCell.innerHTML = "";
			oCell.appendChild(oDropdown);

			oDropdown.oInput.focus();
			oDropdown.oInput.onkeyup = self.rebuild;
			oDropdown.onChange = self.rebuild;
			
			oRow.oValue = oDropdown;

			if (sValue == 'Vendor') oDropdown.getValues = oManager.getVendors; 
			else if (sValue == 'Account') oDropdown.getValues = oManager.getAccounts; 
			else if (sValue == 'Category') oDropdown.getValues = oManager.getCategories; 
		}
		else if (sValue == 'Fixed')
		{
			oCell.innerHTML = "<select class='dropdown' style='width:150px'>"+self.getOptions(self.aFixed)+"</select>";;
			oRow.oValue = oCell.firstChild;
			oRow.oValue.onchange = self.rebuild;
		}
		else if (sValue == 'Debit')
		{
			oCell.innerHTML = "<select class='dropdown' style='width:150px'>"+self.getOptions(self.aDebit)+"</select>";;
			oRow.oValue = oCell.firstChild;
			oRow.oValue.onchange = self.rebuild;
		}
		else if (sValue == 'Date')
		{
			oCell.innerHTML = "<input type='text' class='textfield' style='width:130px' /><img src='"+UI.image+"icon=calendar.png' style='vertical-align:top' class='clickicon'/>";
			oCell.lastChild.onclick = function()
			{
				self.oCalendar.set(new Date());			
				self.oCalendar.onSelect = function(iYear, iMonth, iDay)
				{
					oCell.firstChild.value = iYear + "-" + (iMonth < 10 ? "0" + iMonth : iMonth) + "-" + (iDay < 10 ? "0" + iDay : iDay);
					UI.hide(self.oCalendar);
					
					self.rebuild();
				};

				UI.showPopupRelativeTo(self.oCalendar, oCell, 0, 15);
			}
			
			oRow.oValue = oCell.firstChild;
			oRow.oValue.onkeyup = self.rebuild;
		}
		
	}

	self.onRemove = function(oRow)
	{
		var oPrevious = self.oTable.rows[oRow.rowIndex-1];

		//If removing last row then remove the 'and/or' from previous row.
		if (oRow.rowIndex == self.oTable.rows.length-1) 
		{
			oPrevious.oAndOr = null;
			oPrevious.cells[oPrevious.cells.length-1].innerHTML = "<input type='button' class='button' value='Add' style='width:50px'/>";
			oPrevious.cells[oPrevious.cells.length-1].firstChild.onclick = self.onAdd;
		}

		//Remove the row from the table.
		self.oTable.deleteRow(oRow.rowIndex);

		//Rebuild the search text.
		self.rebuild();
	}

	self.onUse = function()
	{
		self.rebuild();
		if (fUseSearch != null) fUseSearch(self.oSearch.innerHTML);
	}

	self.onSave = function()
	{
		self.rebuild();
		
		if (self.oSearch.innerHTML.length == 0) return alert("No search conditions entered.");
		
		AJAX.call( XML.serialize(true, "Filter.add", "Filter", self.oSearch.innerHTML), self.onSave_Response);
		
	}
	
	self.onSave_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.nodeName == "Error") return alert("An error occured while saving search text. Please try again.");
		
		else alert("Search text saved.");
	}

	self.onClear = function()
	{
		self.oSearch.innerHTML = "";
		self.oGeneral.value = "";
		while (self.oTable.rows.length > 1) self.onRemove(self.oTable.rows[1]);
		
		self.onAdd();
	}

	self.init();
}
