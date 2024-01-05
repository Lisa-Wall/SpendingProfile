

function TagEditor(oManager, iMaxLength, sName, sTitle, sMessage) //get, remove, rename, add, onClose
{
	var self = this;

	self.iUpdates = 0;
	self.oWindow = new UI_Window(sTitle, false, false);

	self.init = function()
	{
		var sAdd = "<table width='100%'><tr><td width='100%'><input type='text' class='textfield' style='width:100%' maxlength='"+iMaxLength+"'/></td><td><input type='button' class='button' value='Add " + sName + "' style='width:90px'/></td></tr></table>";
		var sTable  = "<div class='scrollpane' style='height:400px'><table class='label' width='100%'></table></div><div style='padding-top:10px' align='center'><input type='button' class='button' value='Close' style='width:70px'/></div>";

		self.oWindow.windowPane.className = "windowpane";
		self.oWindow.windowPane.innerHTML = sMessage + sAdd + sTable;

		var oInputs = self.oWindow.windowPane.getElementsByTagName("INPUT");
		self.oTable = self.oWindow.windowPane.getElementsByTagName("TABLE")[1];

		self.oAddInput = oInputs[0];

		oInputs[0].onkeyup = self.onEnter;
		oInputs[1].onclick = self.onAdd;
		oInputs[2].onclick = self.onClose;
		self.oWindow.onClose = self.onClose;

		oManager.setEditor(self);
	}

	self.onAdd = function()
	{
		if (self.oAddInput.value.length == 0) alert('The new entry cannot be blank.');
		else oManager.add(self.oAddInput.value);
	}

	self.onClose = function()
	{
		UI.hide(self.oWindow);
		if (self.iUpdates != 0) oManager.onClose();
	}

	self.onDelete = function()
	{
		var sValue = this.parentNode.rowValue;
		if (confirm("Delete " + sValue + "?")) oManager.remove(this.parentNode.rowId);
	}

	self.onEdit = function()
	{
		var oCell = this.nextSibling;
		UI_TableCellEditor.textField(oCell, oCell.innerHTML, self.onChange);
	}

	self.onClick = function()
	{
		UI_TableCellEditor.textField(this, this.innerHTML, self.onChange);
	}

	self.onChange = function(oCell, sValue, sNewValue)
	{
		if (sNewValue.length == 0) alert('The new entry cannot be blank.');
		else oManager.rename(oCell.parentNode.rowId, sNewValue);
	}

	self.onEnter = function(oEvent)
	{
		oEvent = (oEvent?oEvent:event);
		if (oEvent.keyCode == KeyCode.ENTER) self.onAdd();
	}

	self.add = function(sId, sName)
	{		self.iUpdates++;			var aRows = self.oTable.rows;
		for (var iIndex = 0; iIndex < aRows.length; iIndex++)
		{
			if (sName.localeCompare(aRows[iIndex].cells[3].innerHTML) < 0) break;
		}

		var oRow = self.oTable.insertRow(iIndex);
		self.addRow(oRow, sId, sName, 0, 0);
		self.renumber();

		oRow.style.background = "yellow";
		self.oAddInput.value = "";
	}

	self.modify = function(sId, sValue)
	{
		self.iUpdates++;
		var oRow = self.getRow(sId);
		if (oRow != null) oRow.cells[3].innerHTML = sValue;
	}

	self.remove = function(sId)
	{
		self.iUpdates++;
		var oRow = self.getRow(sId);

		if (oRow != null)
		{
			oRow.parentNode.removeChild(oRow);
			self.renumber();
		}
	}

	self.renumber = function()
	{
		var aRows = self.oTable.rows;
		for (var iIndex = 0; iIndex < aRows.length; iIndex++)
		{
			aRows[iIndex].style.background = "";
			aRows[iIndex].cells[0].innerHTML = (iIndex+1) + ".";
		}
	}

	self.getRow = function(sId)
	{
		var aRows = self.oTable.rows;
		for (var iIndex = 0; iIndex < aRows.length; iIndex++) if (aRows[iIndex].rowId == sId) return aRows[iIndex];
		return null;
	}

	self.show = function()
	{
		self.iUpdates = 0;
		oManager.get();

		XML.clear(self.oTable);
		UI.centerWindow(self.oWindow);
	}

	self.update = function(oTags)
	{
		XML.clear(self.oTable);

		var iIndex = 1;
		for (var oTag = oTags.firstChild; oTag != null; oTag = oTag.nextSibling)
		{
			self.addRow(self.oTable.insertRow(-1), oTag.getAttribute("Id"), oTag.getAttribute("Name"), oTag.getAttribute("Count"), iIndex++)
		}
	}

	self.addRow = function(oRow, sId, sName, iCount, iIndex)
	{
		oRow.rowId = sId;
		oRow.rowValue = sName;

		self.addCell(oRow, iIndex + ".", null);
		self.addCell(oRow, "<img src='" + UI.image + "icon=remove.png'/>", self.onDelete);
		self.addCell(oRow, "<img src='" + UI.image + "icon=edit.png'/>", self.onEdit);
		var oCell = self.addCell(oRow, sName, self.onClick);
		self.addCell(oRow, (iCount == 0 ? "unused" : "&nbsp;"), null);

		oCell.style.width = "100%";
	}

	self.addCell = function(oRow, sValue, fOnClick)
	{
		var oCell = oRow.insertCell(-1);
		oCell.className = "table_cells";
		oCell.innerHTML = sValue;
		oCell.onclick = fOnClick
		return oCell;
	}

	self.init();
}