
function Import(bUploaded, sError)
{
	var self = this;

	self.init = function()
	{
		//For editing fixed/variable column.
		self.oTypes = XML.arrayToXML(new Array("Fixed", "Variable"), "Type", "Name");

		//Columns ID reference.
		self.aColumns = new Array("", "", "", "Fixed", "Date", "Amount", "Vendor", "Category", "Account", "Notes");

		//Load option window
		self.oOptionWindow = ImportOptions();

		//Build GUI
		self.buildTable();
		self.buildToolbar();

		//if file has been uploaded then get it.
		if (sError.length > 0)
		{
			alert(sError);
		}
		else if (bUploaded)
		{
			AJAX.call("<Vendor.getTree />", function(oC, sC, bS){ if (!bS) return; self.oVendorMenu = new VendorMenu(oC); } );
			AJAX.call("<Account.getTree />", function(oC, sC, bS){ if (!bS) return; self.oAccounts = oC; } );
			AJAX.call("<Category.getTree />", function(oC, sC, bS){ if (!bS) return; self.oCategories = oC; } );
			AJAX.call(XML.serialize(true, "Import.preview"), self.preview_Response);
		}

		window.onbeforeunload = self.onUnload;
	}

	self.buildTable = function()
	{
		//Build table
		self.oTable = UI_Table("table");
		self.oTable.addHeader(""         , ""         , false, true, 25);
		self.oTable.addHeader(""         , "<input type='checkbox'/>", false, 0, 25);
		self.oTable.addHeader("Duplicate", "Dup"      , false, true, 25);
		self.oTable.addHeader("Fixed"    , "F/V"         , false, true, 20 , null);
		self.oTable.addHeader("Date"     , "Date"     , false, true, 40 , null);
		self.oTable.addHeader("Amount"   , "Amount"   , false, true, 40 , null);
		self.oTable.addHeader("Vendor"   , "Vendor"   , false, true, 100, null);
		self.oTable.addHeader("Category" , "Category" , false, true, 100 , null);
		self.oTable.addHeader("Account"  , "Account"  , false, true, 100, null);
		self.oTable.addHeader("Notes"    , "Notes"    , false, true, 100, null);

		self.oSelectAll = self.oTable.header.rows[0].cells[1].firstChild;
		self.oSelectAll.onclick = function(){ self.selectAll(this.checked); };

		UI.setTooltip(self.oTable.header.rows[0].cells[1], "Select all transactons");
		UI.setTooltip(self.oTable.header.rows[0].cells[2], "Duplicate");
		UI.setTooltip(self.oTable.header.rows[0].cells[3], "Fixed/Variable");

		self.oTable.content.innerHTML = "<br/><center class='label' style='font-size:12pt;color:green'>Start by uploading a bank file using the form above.</center>";
	}

	self.buildToolbar = function()
	{
		//Build toolbar
		self.oToolbar = new UI_Toolbar("toolbar");
		self.oToolbar.addButton("DELETE" , "Delete", null, "Delete selected transactions.", self.onDelete);
		self.oToolbar.addButton("REVERT" , "Revert", null, "Revert selected transactions to their original values.", self.onRevert);
		self.oToolbar.addButton("SAVE"   , "Save"  , null, "Save transactions to your Spending Profile account.", self.onSave);
		self.oToolbar.addSeparator();
		self.oToolbar.addButton("REFRESH", "Refresh", null, "Refresh the data from the file, re-applying all options.<br/>All changes made to the table below will be lost.", self.onRefresh);

		self.oToolbar.addButton("OPTIONS", "Options", "sub_menu.png", "Select options for processing bank files.", self.onOptions, true);
		var oHelp = self.oToolbar.addButton("TABLEHELP", null, "help.gif", "", null, true);

		UI.setHelptip(oHelp, "Import Table", sImportTableHelptip);
	}

	//Toolbar events

	self.onOptions = function()
	{
		UI.showPopupRelativeTo(self.oOptionWindow, this, -10, 20, true);
	}

	self.onUnload = function()
	{
		if (bUploaded) return "The imported transactions have not yet been saved.";
	}

	self.onRefresh = function()
	{
		if (!bUploaded)
		{
			alert("There is no data to refresh. Please upload a file first.");
		}
		else if (confirm("Refreshing the data will erase any changes you have made since uploading the bank file. Are you sure you want to continue?"))
		{
			AJAX.call(XML.serialize(true, "Import.preview"), self.preview_Response);
		}
	}

	self.onDelete = function(sEvent, bForce)
	{
		bForce = UI.defaultValue(bForce, false);

		if (!bForce)
		{
			if (!self.isSelected()) return alert("Nothing is selected to delete. Use the checkboxes on the left of the table to select entries to delete.");
			if (!confirm("Delete the selected entries?")) return;
		}

		for (var oRow = self.oTable.body.rows[1]; oRow != null; oRow = oRow.nextSibling)
		{
			if (oRow.cells[1].firstChild.checked)
			{
				oTemp = oRow.previousSibling;
				oRow.parentNode.removeChild(oRow);
				oRow = oTemp;
			}
		}

		self.renumber();
		self.oSelectAll.checked = false;
	}

	self.onRevert = function()
	{
		if (!self.isSelected()) return alert("Nothing is selected to revert. Use the checkboxes on the left of the table to select entries to revert.");
		if (!confirm("Revert the selected transactions to their original values?")) return;

		for (var oRow = self.oTable.body.rows[1]; oRow != null; oRow = oRow.nextSibling)
		{
			if (!oRow.cells[1].firstChild.checked) continue;

			var oTransaction = oRow.oTransaction;
			oRow.cells[1].firstChild.checked = false;
			self.onModify(oRow.cells[3], oRow.cells[3].innerHTML, (oTransaction.getAttribute("Fixed")=="1"?"Fixed":"Variable"));
			self.onModify(oRow.cells[4], oRow.cells[4].innerHTML, oTransaction.getAttribute("Date"));
			self.onModify(oRow.cells[5], oRow.cells[5].innerHTML, (oTransaction.getAttribute("Debit")=="1"?"":"+")+ oTransaction.getAttribute("Amount"));
			self.onModify(oRow.cells[6], oRow.cells[6].innerHTML, oTransaction.getAttribute("Vendor"));
			self.onModify(oRow.cells[7], oRow.cells[7].innerHTML, oTransaction.getAttribute("Category"));
			self.onModify(oRow.cells[8], oRow.cells[8].innerHTML, oTransaction.getAttribute("Account"));
			self.onModify(oRow.cells[9], oRow.cells[9].innerHTML, oTransaction.getAttribute("Notes"));
		}

		self.oSelectAll.checked = false;
	}

	self.onSave = function()
	{
		if (!bUploaded) return alert("There is no data to save. Please upload a file first.");

		UI_TableCellEditor.apply();

		var iErrors = 0;
		var sTransactions = "";
		for (var oRow = self.oTable.body.rows[1]; oRow != null; oRow = oRow.nextSibling)
		{
			var sFixed    = oRow.cells[3].innerHTML == "F" ? "true" : "false";
			var sDate     = oRow.cells[4].innerHTML;
			var sAmount   = oRow.cells[5].innerHTML;
			var sDebit    = sAmount.trim().charAt(0) != "+";
			var sVendor   = XML.toString(oRow.cells[6].innerHTML);
			var sCategory = XML.toString(oRow.cells[7].innerHTML);
			var sAccount  = XML.toString(oRow.cells[8].innerHTML);
			var sNotes    = oRow.cells[9].innerHTML;

			if (sAmount.length == 0 || !Validate.float(sAmount))
			{
				iErrors++
				oRow.cells[5].style.background = "red";
			}
			else sAmount = parseFloat(sAmount);

			if (sCategory.length == 0)
			{
				iErrors++
				oRow.cells[7].style.background = "red";
			}

			sTransactions += XML.serialize(true, "Transaction", "Fixed", sFixed, "Date", sDate, "Amount", sAmount, "Debit", sDebit, "Vendor", sVendor, "Category", sCategory, "Account", sAccount, "Notes", sNotes);
		}

		if (iErrors == 0)
		{
			if (confirm("Ready to save?"))
			{
				AJAX.call(XML.serialize(false, "Import.import")+sTransactions+"</Import.import>", self.import_Response);
			}
		}
		else alert("Failed to save. Either some of the amount fields were invalid, or some categories were left blank.\n Please fix the values marked in red and try saving again.");
	}

	self.onModify = function(oCell, sValue, sNewValue)
	{
		if (sValue == sNewValue) return;
		var sField = self.aColumns[oCell.cellIndex];

		if (sField == "Amount")
		{
			oCell.innerHTML = sNewValue;
			oCell.className = "table_cell_amount" + (sNewValue.charAt(0) == "+" ? "_credit" : "");
		}
		else if (sField == "Fixed")
		{
			oCell.innerHTML = (sNewValue == "Fixed" ? "F" : "V");
			oCell.setAttribute("tooltip", sNewValue);
		}
		else if (sField == "Date")
		{
			oCell.innerHTML = sNewValue;
		}
		else
		{
			if (sField == "Vendor") oCell.style.color = "";
			oCell.innerHTML = sNewValue;
			oCell.setAttribute("tooltip", sNewValue);
		}

		//Clear background.
		oCell.style.background = "";
	}

	//Edit functions

	self.editVendor = function(oCell)
	{
		var oVendors = self.oVendorMenu.set(oCell.parentNode.oTransaction);
		UI_TableCellEditor.smartdropdown(oCell, {items: oVendors, value: oCell.innerHTML}, self.onModify);
	}

	self.editAccount = function(oCell)
	{
		UI_TableCellEditor.smartdropdown(oCell, {items: self.oAccounts, value: oCell.innerHTML}, self.onModify);
	}

	self.editCategory = function(oCell)
	{
		UI_TableCellEditor.smartdropdown(oCell, {items: self.oCategories, value: oCell.innerHTML}, self.onModify);
	}

	self.editType = function(oCell)
	{
		UI_TableCellEditor.dropdown(oCell, {items: self.oTypes, value: oCell.innerHTML}, self.onModify);
	}

	self.editDate = function(oCell)
	{
		UI_TableCellEditor.calendar(oCell, oCell.innerHTML, self.onModify);
	}

	self.editAmount = function(oCell)
	{
		UI_TableCellEditor.textField(oCell, oCell.innerHTML, self.onModify);
	}

	self.editNotes = function(oCell)
	{
		UI_TableCellEditor.textArea(oCell, {title: "Notes", value: oCell.innerHTML}, self.onModify);
	}


	//Server Response

	self.preview_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		if (oResponse.nodeName == "Error") return alert(oResponse.getAttribute("Message"));
		self.paint(oResponse);
	}

	self.import_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		if (oResponse.nodeName == "Error") return alert(oResponse.getAttribute("Message"));

		var bErrors = false;
		self.selectAll(false);

		for (oResponse = oResponse.firstChild; oResponse != null; oResponse = oResponse.nextSibling)
		{
			if (oResponse.nodeName == "Error")
			{
				bErrors = true;
			}
			else
			{
				self.oTable.body.rows[oResponse.getAttribute("Index")].cells[1].firstChild.checked = true;
			}
		}

		bUploaded = false;
		self.onDelete("DELETE", true);

		if (bErrors) alert("An error occured while attempting to add the remaining transactions. Please review them again.");
		else
		{
			self.oTable.content.innerHTML = "<br/><center class='label' style='font-size:12pt;color:green'>Start by uploading a bank file using the form above.</center>";
			alert("Transactions imported successfully!")
		}
	}

	//Table Operations

	self.selectAll = function(bSelectAll)
	{
		var oRows = self.oTable.body.rows;
		for (var i = 1; i < oRows.length; i++) oRows[i].cells[1].firstChild.checked = bSelectAll;
	}

	self.isSelected = function()
	{
		var oRows = self.oTable.body.rows;
		for (var i = 1; i < oRows.length; i++) if (oRows[i].cells[1].firstChild.checked) return true;
		return false;
	}

	self.renumber = function()
	{
		var aRows = self.oTable.body.rows;
		for (var iIndex = 1; iIndex < aRows.length; iIndex++) aRows[iIndex].cells[0].innerHTML = iIndex + ".";
	}

	//Paint

	self.paint = function(oTransactions)
	{
		var sRows = "";
		for (var oTransaction = oTransactions.firstChild, iIndex = 1; oTransaction != null; oTransaction = oTransaction.nextSibling, iIndex++)
		{
			sRows += self.paintRow(oTransaction, iIndex);
		}

		self.oTable.content.innerHTML = "<table class='table_content' cellpadding='0' cellspacing='1'>" + sRows + "</table>";
		self.oTable.body = self.oTable.content.firstChild;
		self.oTable.resizeColumns();

		//Attached the transactions to the rows.
		for (var oTransaction = oTransactions.firstChild, iIndex = 1; oTransaction != null; oTransaction = oTransaction.nextSibling, iIndex++)
		{
			var oRow = self.oTable.body.rows[iIndex];
			oRow.oTransaction = oTransaction;

			//TODO: if (oRow.cells[2].innerHTML == 'D') self.setDuplicateTooltip(oRow.cells[2]);
		}
	}

	self.paintRow = function(oTransaction, iIndex)
	{
		var sRow = "";
		var oRow = null;

		var bDebit = (oTransaction.getAttribute("Debit")=="1");
		var bFixed = (oTransaction.getAttribute("Fixed")=="1");
		var bDuplicate = (oTransaction.getAttribute("Duplicate")=="1");
		var sAmount = oTransaction.getAttribute("Amount");
		var sRank = oTransaction.getAttribute("Vendor_Rank");
		var sColor = (sRank == 'EXACT' ? 'purple' : (sRank == 'FULL' ? 'blue' : (sRank == 'PARTIAL' ? 'red' : 'black')));

		sRow += self.paintCell(oRow, iIndex + "."                         , "table_cell_first", null, null, null);
		sRow += self.paintCell(oRow, "<input type='checkbox'/>"           , "table_cell_type" , null, null, null);
		sRow += self.paintCell(oRow, (bDuplicate ? 'D' : '')              , "table_cell_type" , "red", (bDuplicate?"Possible duplicate of transaction(s) with id(s) " + oTransaction.getAttribute("Duplicate_Ids"):null), null);
		sRow += self.paintCell(oRow, (bFixed ? 'F' : 'V')                 , "table_cell_type" , null, (bFixed?"Fixed":"Variable"), "oImport.editType");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Date")    , "table_cell_date" , null, null, "oImport.editDate");
		sRow += self.paintCell(oRow, (bDebit?sAmount:"+"+sAmount)         , "table_cell_amount"+(bDebit?"":"_credit"), null, null, "oImport.editAmount");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Vendor")  , "table_cells"     , sColor, true, "oImport.editVendor");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Category"), "table_cells"     , null, true, "oImport.editCategory");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Account") , "table_cells"     , null, true, "oImport.editAccount");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Notes")   , "table_cells"     , null, true, "oImport.editNotes");

		return "<tr rowId='"+oTransaction.getAttribute("Id")+"'>"+sRow+"</tr>";
	}

	self.paintCell = function(oRow, sValue, sClass, sColor, sTooltip, sOnEdit)
	{
		sColor = (sColor == null ? "" : "style='color:"+sColor+"' color='"+sColor+"'");
		sEdit = (sOnEdit == null ? "" : "onclick='"+sOnEdit+"(this)'");
		sTooltip = (sTooltip == null ? "" : "tooltip='"+XML.clean(sTooltip == true ? sValue : sTooltip)+"' onmousemove='UI.tooltipShow(event, this)' onmouseout='UI.tooltipHide()'");
		return "<td class='"+sClass+"' "+sTooltip+" "+sEdit+" " + sColor + ">"+sValue+"</td>";
	}


	self.init();
}