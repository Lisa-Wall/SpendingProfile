
function TransactionTable(sId, oManager) //modify, orderBy, getVendors, getAccounts, getCategories
{
	var self = this;

	self.init = function()
	{
		//For editing fixed/variable column.
		self.oTypes = XML.arrayToXML(new Array("Fixed", "Variable"), "Type", "Name");

		//Columns ID reference.
		self.aColumns = new Array("", "", "Receipt", "", "Fixed", "Date", "Amount", "Category", "Vendor", "Account", "Notes");

		//Build table header.
		self.oTable = new UI_Table(sId);
		self.oTable.addHeader(""        , "", false, 0, 25);
		self.oTable.addHeader(""        , "<input type='checkbox'/>", false, 0, 25);
		self.oTable.addHeader("Receipt" , ""         , true, true, 25 , oManager.orderBy);
		self.oTable.addHeader("Id"      , "Id"       , true, true, 55 , oManager.orderBy);
		self.oTable.addHeader("Fixed"   , "F/V"      , true, true, 25 , oManager.orderBy);
		self.oTable.addHeader("Date"    , "Date"     , true, true, 70 , oManager.orderBy);
		self.oTable.addHeader("Amount"  , "Amount"   , true, true, 60 , oManager.orderBy);
		self.oTable.addHeader("Category", "Category" , true, true, 90 , oManager.orderBy);
		self.oTable.addHeader("Vendor"  , "Vendor"   , true, true, 80 , oManager.orderBy);
		self.oTable.addHeader("Account" , "Account"  , true, true, 70 , oManager.orderBy);
		self.oTable.addHeader("Notes"   , "Notes"    , true, true, 100, oManager.orderBy);

		self.oSelectAll = self.oTable.header.rows[0].cells[1].firstChild;
		self.oSelectAll.onclick = function(){ self.selectAll(this.checked); };

		UI.setTooltip(self.oTable.header, "Click to sort by this column");
	}


	//Public

	self.update = function(oTransactions)
	{
		self.paint(oTransactions);
	}

	self.add = function(oTransaction)
	{
		//TODO:
	}

	self.highlight = function(sId)
	{
		//Find it and high light it.
		if ((self.oHighlighted = self.getRow(sId)) != null) self.oHighlighted.className = "table_row_highlighted";
	}

	self.removeAll = function(oTransactions)
	{
		for (var oTransaction = oTransactions.firstChild; oTransaction != null; oTransaction = oTransaction.nextSibling)
		{
			self.remove(oTransaction.getAttribute("Id"), false);
		}
		self.renumber();
	}

	self.remove = function(sId, bRenumber)
	{
		if ((oRow = self.getRow(sId)) != null) oRow.parentNode.removeChild(oRow);
		if (bRenumber) self.renumber();
	}

	self.modify = function(sId, sValue, sField)
	{
		if ((oRow = self.getRow(sId)) == null) return;
		var oCell = oRow.cells[ self.aColumns.indexOf(sField) ];

		if (sField == "Amount")
		{
			oCell.innerHTML = sValue;
			oCell.className = "table_cell_amount" + (sValue.charAt(0) == "+" ? "_credit" : "");
		}
		else if (sField == "Fixed")
		{
			oCell.innerHTML = (sValue == "1" ? "F" : "V");
			oCell.setAttribute("tooltip", (sValue == "1" ? "Fixed" : "Variable"));
		}
		else if (sField != "Date")
		{
			oCell.innerHTML = sValue;
			oCell.setAttribute("tooltip", sValue);
		}
		else
		{
			oCell.innerHTML = sValue;
		}
	}

	self.renumber = function()
	{
		self.setSelectAll(false);
		var aRows = self.oTable.body.rows;
		for (var iIndex = 1; iIndex < aRows.length; iIndex++) aRows[iIndex].cells[0].innerHTML = iIndex + ".";
	}

	self.getSelected = function()
	{
		var aSelected = new Array();
		for (var oRow = self.oTable.body.rows[1]; oRow != null; oRow = oRow.nextSibling) if (oRow.cells[1].firstChild.checked) aSelected.push(oRow.getAttribute('rowId'));
		return aSelected;
	}

	//Private

	self.setSelectAll = function(bSelectAll)
	{
		self.oSelectAll.checked = bSelectAll;
	}

	self.selectAll = function(bSelectAll)
	{
		var oRows = self.oTable.body.rows;
		for (var i = 1; i < oRows.length; i++) oRows[i].cells[1].firstChild.checked = bSelectAll;
	}

	self.onModify = function(oCell, sValue, sNewValue)
	{
		if (sValue != sNewValue)
		{
			var sField = self.aColumns[oCell.cellIndex];
			if (sField == "Fixed") sNewValue = (sNewValue == "Fixed" ? "true" : "false");
			oManager.modify(oCell.parentNode.getAttribute("rowId"), sNewValue, sField);
		}
	}

	self.getRow = function(sId)
	{
		var aRows = self.oTable.body.rows;
		for (var iIndex = 1; iIndex < aRows.length; iIndex++) if (aRows[iIndex].getAttribute("rowId") == sId) return aRows[iIndex];
		return null;
	}

	//Edit functions

	self.editVendor = function(oCell)
	{
		UI_TableCellEditor.smartdropdown(oCell, {items: oManager.getVendors(), value: oCell.innerHTML}, self.onModify);
	}

	self.editAccount = function(oCell)
	{
		UI_TableCellEditor.smartdropdown(oCell, {items: oManager.getAccounts(), value: oCell.innerHTML}, self.onModify);
	}

	self.editCategory = function(oCell)
	{
		UI_TableCellEditor.smartdropdown(oCell, {items: oManager.getCategories(), value: oCell.innerHTML}, self.onModify);
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


	//Paint

	self.paint = function(oTransactions)
	{
		self.setSelectAll(false);
		self.oTable.orderBy(oTransactions.getAttribute("OrderBy"), oTransactions.getAttribute("OrderIn") == "ASC");

		var sRows = "";
		for (var oTransaction = oTransactions.firstChild, iIndex = 1; oTransaction != null; oTransaction = oTransaction.nextSibling, iIndex++)
		{
			sRows += self.paintRow(oTransaction, iIndex);
		}

		self.oTable.content.innerHTML = "<table class='table_content' cellpadding='0' cellspacing='1'>" + sRows + "</table>";
		self.oTable.body = self.oTable.content.firstChild;
		self.oTable.resizeColumns();
	}

	self.paintRow = function(oTransaction, iIndex)
	{
		var sRow = "";
		var oRow = null;

		var sId     = oTransaction.getAttribute("Id");
		var bDebit  = (oTransaction.getAttribute("Debit")=="1");
		var bFixed  = (oTransaction.getAttribute("Fixed")=="1");
		var sAmount = oTransaction.getAttribute("Amount");

		var iReceipt = oTransaction.getAttribute("Receipt");
		var bReceipt = (iReceipt == "1");
		var sReceipt = "<img sId='"+sId+"' src='"+UI.image+"icon=receipt"+(bReceipt ? "" : "_add")+".png' onclick='oReceiptWindow.open("+sId+", "+iReceipt+", this)' class='clickicon' onload='UI.setTooltip(this, "+(bReceipt ? "sViewReceiptTooltip" : "sAddReceiptTooltip") +")'/>";

		sRow += self.paintCell(oRow, iIndex + "."                         , "table_cell_first", null, null);
		sRow += self.paintCell(oRow, "<input type='checkbox'/>"           , "table_cell_type" , null, null);
		sRow += self.paintCell(oRow, sReceipt                             , "table_cells"     , null, null);
		sRow += self.paintCell(oRow, sId                                   , "table_cells"     , null, null);
		sRow += self.paintCell(oRow, (bFixed ? 'F' : 'V')                 , "table_cell_type" , (bFixed?"Fixed":"Variable"), "oTransactionTable.editType");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Date")    , "table_cell_date" , null, "oTransactionTable.editDate");
		sRow += self.paintCell(oRow, (bDebit?sAmount:"+"+sAmount)         , "table_cell_amount"+(bDebit?"":"_credit"), null, "oTransactionTable.editAmount");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Category"), "table_cells"     , true, "oTransactionTable.editCategory");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Vendor")  , "table_cells"     , true, "oTransactionTable.editVendor");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Account") , "table_cells"     , true, "oTransactionTable.editAccount");
		sRow += self.paintCell(oRow, oTransaction.getAttribute("Notes")   , "table_cells"     , true, "oTransactionTable.editNotes");

		return "<tr rowId='"+sId+"'>"+sRow+"</tr>";
	}

	self.paintCell = function(oRow, sValue, sClass, sTooltip, sOnEdit)
	{
		sEdit = (sOnEdit == null ? "" : "onclick='"+sOnEdit+"(this)'");
		sTooltip = (sTooltip == null ? "" : "tooltip='"+XML.clean(sTooltip == true ? sValue : sTooltip)+"' onmousemove='UI.tooltipShow(event, this)' onmouseout='UI.tooltipHide()'");
		return "<td class='"+sClass+"' "+sTooltip+" "+sEdit+">"+sValue+"</td>";
	}
	
	self.paintReceipt = function(oImage, bReceipt)
	{
		var sId = oImage.getAttribute("sId");
		var oParent = oImage.parentNode;
		oParent.innerHTML = "<img sId='"+sId+"' src='"+UI.image+"icon=receipt"+(bReceipt ? "" : "_add")+".png' onclick='oReceiptWindow.open("+sId+", "+bReceipt+", this)' class='clickicon' onload='UI.setTooltip(this, "+(bReceipt ? "sViewReceiptTooltip" : "sAddReceiptTooltip") +")'/>";	
		return oParent.firstChild;
	}

	self.init();
}

var sAddReceiptTooltip = "Add Receipt";
var sViewReceiptTooltip = "View Receipt";