
function Budget()
{
	var self = this;

	self.oTooltip = UI_Tooltip();

	self.init = function()
	{
		self.oMonthSelector = new UI_MonthSelector("budget/monthselector");
		self.oActiveOnly = document.getElementById("budget/activeonly");
		self.oAveragePeriod = document.getElementById("budget/averageperiod");

		self.oMonthSelector.onSelect = function(sDate)
		{
			AJAX.call(XML.serialize(true, "Budget.getAll", "From", sDate), self.get_Response);
		}
		self.oActiveOnly.onchange = function()
		{
			AJAX.call(XML.serialize(true, "Budget.getAll", "ActiveOnly", this.value), self.get_Response);
		}
		self.oAveragePeriod.onchange = function()
		{
			AJAX.call(XML.serialize(true, "Budget.getAll", "AveragePeriod", this.value), self.get_Response);
		}

		self.oTable = new UI_Table("budget/table");
		self.oTable.addHeader(""        , ""         , false, false, 25 , null);
		self.oTable.addHeader("Active"  , "Active"   , true , true , 60 , self.onOrder);
		self.oTable.addHeader("Name"    , "Category" , true , true , 250, self.onOrder);
		self.oTable.addHeader("Average" , "3-Month Average"  , true , true , 80 , self.onOrder);
		self.oTable.addHeader("Amount"  , "Budget"   , true , true , 70 , self.onOrder);
		self.oTable.addHeader("Total"   , "Actual Spending"  , true , true , 80 , self.onOrder);
		self.oTable.addHeader("Percent" , "Percent Used"  , true , true , 70 , self.onOrder);
		self.oTable.addHeader("Graph"   , "Portion of Budget Spent"         , false, false, 200, null);

		//Set a bigger header background.
		self.oTable.body.style.fontSize = "9pt";
		for (var i = 0; i < self.oTable.header.rows[0].cells.length; i++) self.oTable.header.rows[0].cells[i].style.background = "URL(" + UI.image+"image=ui/table/header_bg_30.png)";

		//Create average header table.
		self.aAveragePeriod = new Array();
		self.aAveragePeriod["-1 month"] = "1-Month Average";
		self.aAveragePeriod["-3 month"] = "3-Month Average";
		self.aAveragePeriod["-6 month"] = "6-Month Average";
		self.aAveragePeriod["-12 month"] = "12-Month Average";

		AJAX.call(XML.serialize(true, "Budget.getAll"), self.get_Response);
	}

	self.update = function(oBudget)
	{
		self.oMonthSelector.set(oBudget.getAttribute("From"));
		self.oActiveOnly.value = oBudget.getAttribute("ActiveOnly");
		self.oAveragePeriod.value = oBudget.getAttribute("AveragePeriod");
		self.oTable.orderBy(oBudget.getAttribute("OrderBy"), oBudget.getAttribute("OrderIn") == "ASC");

		//Update table header.
		self.oTable.header.rows[0].cells[3].innerHTML = self.aAveragePeriod[oBudget.getAttribute("AveragePeriod")]
	}

	self.paint = function(oBudget)
	{
		var iIndex = 1;
		var oTable = self.oTable.clear();

		var iTotalBudget = 0;
		var iTotalAmount = 0
		var iTotalAverage = 0;

		for (var oCategory = oBudget.firstChild; oCategory != null; oCategory = oCategory.nextSibling)
		{
			var iPercent = oCategory.getAttribute("Percent");
			var iBudget = oCategory.getAttribute("Amount");
			var iAmount = oCategory.getAttribute("Total");
			var iAverage = oCategory.getAttribute("Average");
			var iActive = oCategory.getAttribute("Active");

			iTotalBudget += parseFloat(iBudget);
			iTotalAmount += parseFloat(iAmount);
			iTotalAverage += parseFloat(iAverage);

			var oRow = oTable.insertRow(-1);
			oRow.sId = oCategory.getAttribute("Id");
			oRow.oCategory = oCategory;

			if (iBudget < 0) iBudget = "+" + Utility.formatNumber(Math.abs(iBudget), 100);
			var sBudget = "<input class='textfield' style='width:100%;text-align:right' type='text' budget='"+iBudget+"' value='"+iBudget+"' onkeypress='self.onEditAmount(event, this)' onblur='this.value = this.getAttribute(\"budget\")'/>";

			self.addCell(oRow, (iIndex++) + "."              , "table_cell_first" , false, null);
			self.addCell(oRow, self.paintActive(iActive)     , "table_cell_type"  , false, null);
			self.addCell(oRow, oCategory.getAttribute("Name"), "table_cells"      , true , null);
			self.addCell(oRow, iAverage                      , "table_cell_amount", true , null);
			self.addCell(oRow, sBudget                       , "table_cell_amount", false, null);
			self.addCell(oRow, self.paintAmount(iAmount)     , "table_cell_amount", false, null);
			self.addCell(oRow, iPercent+"%"                  , "table_cell_amount", false, null);
			self.addCell(oRow, self.paintPercent(iPercent)   , "table_cells"      , false, null);

			oRow.cells[1].firstChild.row = oRow;
			oRow.cells[1].firstChild.onclick = self.editActive;
			oRow.cells[3].getTooltip = self.getAverageTooltip;
		}

		for (var i = 1; i < 7; i++) UI.setTooltip(self.oTable.header.rows[0].cells[i], "Click to sort by this column");

		self.paintResult(oTable.insertRow(-1), iTotalBudget, iTotalAmount, iTotalAverage);
	}

	self.paintAmount = function(iAmount)
	{
		return (iAmount < 0 ? "<b>+" + Utility.formatCurrency(Math.abs(iAmount), "") + "</b>" : iAmount);
	}

	self.paintActive = function(iActive)
	{
		return "<input type='checkbox' "+(iActive == "1" ? "checked" : "")+"></input>";
	}

	self.paintPercent = function(iPercent)
	{
		var sTooltip = "onmousemove='UI.tooltipShow(event, this)' onmouseout='UI.tooltipHide()' tooltip='" + (iPercent > 100 ? Utility.formatNumber(iPercent-100, 10) + "% over budget'" : iPercent + "% of budget spent'");
		if (iPercent > 100) sSpan = "<div style='width:100%;background:red' "+sTooltip+">&nbsp;</div>";
		else if (iPercent > 0) sSpan = "<div style='width:" + iPercent + "%;background:blue' "+sTooltip+">&nbsp;</div>";
		else sSpan = "<span>&nbsp;</span>";

		return sSpan;
	}

	self.paintResult = function(oRow, iTotalBudget, iTotalAmount, iTotalAverage)
	{
		var iPercent = (iTotalBudget == 0 ? 0 : (iTotalAmount/iTotalBudget*100));
		iTotalBudget = Utility.formatCurrency(iTotalBudget, oSession.currency);
		iTotalAmount = Utility.formatCurrency(iTotalAmount, oSession.currency);
		iTotalAverage = Utility.formatCurrency(iTotalAverage, oSession.currency);

		oRow.style.fontSize = "10pt";
		oRow.style.fontWeight = "bold";

		var sPercent = Math.round(iPercent*10)/10;

		self.addCell(oRow, "", "", false, null);
		self.addCell(oRow, "", "", false, null);
		self.addCell(oRow, "Total"                          , "table_cell_right" , false, null);
		self.addCell(oRow, iTotalAverage                    , "table_cell_amount", false, null);
		self.addCell(oRow, iTotalBudget                     , "table_cell_amount", false, null);
		self.addCell(oRow, iTotalAmount                     , "table_cell_amount", false, null);
		self.addCell(oRow, sPercent + "%"                   , "table_cell_amount", false, null);
		self.addCell(oRow, self.paintPercent(sPercent)      , "table_cells"      , false, null);
	}

	self.addCell = function(oRow, sValue, sClass, bShowTooltip, fOnEdit)
	{
		var oCell = oRow.insertCell(-1);
		oCell.innerHTML = sValue;
		oCell.className = sClass;
		oCell.onclick = fOnEdit;

		if (bShowTooltip)
		{
			oCell.onmouseout = function(){ self.oTooltip.hide(); };
			oCell.onmousemove = function(oEvent){ oEvent = (oEvent?oEvent:event); self.oTooltip.show((oCell.getTooltip ? oCell.getTooltip(this) : oCell.innerHTML), oEvent.clientX + 15, oEvent.clientY + 18); };
		}
	}

	self.getAverageTooltip = function(oCell)
	{
		var sCategory = oCell.parentNode.oCategory.getAttribute("Name");
		var sAverage = oSession.currency + oCell.parentNode.oCategory.getAttribute("Average");
		return "Your average spending in the "+sCategory+" category is " + sAverage + " per month.";
	}

	self.editActive = function()
	{
		if (self.oActiveOnly.value == '1')
		{
			alert("Deactiviating a category from the budget will remove it from the view.\nTo reactive it switch to full view.");
		}

		var sId = this.row.sId;
		var sAmount = this.row.cells[4].firstChild.value;
		var sActive = (this.row.cells[1].firstChild.checked ? "1" : "0");

		AJAX.call(XML.serialize(true, "Budget.update", "Id", sId, "Amount", sAmount, "Active", sActive), self.update_Response);
	}

	self.onEditAmount = function(oEvent, oCell)
	{
		oEvent = (oEvent?oEvent:event);

		//Editing cancellation
		if (oEvent.keyCode == KeyCode.ESCAPE) oCell.value = oCell.getAttribute("budget");

		//Pressing the ENTER key submitts the value.
		else if (oEvent.keyCode == KeyCode.ENTER)
		{
			var sNewValue = oCell.value;

			if (sNewValue != oCell.getAttribute("budget"))
			{
				var sId = oCell.parentNode.parentNode.sId;
				var sActive = (oCell.parentNode.parentNode.cells[1].firstChild.checked ? "1" : "0");

				sNewValue = sNewValue.trim();
				if (sNewValue.length == 0) sNewValue = "0";
				else if (sNewValue.charAt(0) == "+") sNewValue = "-" + Math.abs(parseFloat(sNewValue));

				AJAX.call(XML.serialize(true, "Budget.update", "Id", sId, "Amount", sNewValue, "Active", sActive), self.update_Response);
			}

		}
	}
/*
	self.editAmount = function()
	{
		UI_TableCellEditor.textField(this, this.innerHTML, function(oCell, sValue, sNewValue)
		{
			if (sValue != sNewValue)
			{
				var sId = oCell.parentNode.sId;
				var sActive = (oCell.parentNode.cells[1].firstChild.checked ? "1" : "0");

				AJAX.call(XML.serialize(true, "Budget.update", "Id", sId, "Amount", sNewValue, "Active", sActive), self.update_Response);
			}
		});
	}
*/
	self.onOrder = function(sOrderBy, sOrderIn)
	{
		AJAX.call(XML.serialize(true, "Budget.getAll", "OrderBy", sOrderBy, "OrderIn", (sOrderIn ? "ASC" : "DESC")), self.get_Response);
	}

	self.get_Response = function(oBudget, sBudget, bSuccess)
	{
		if (!bSuccess) return false;

		self.update(oBudget);
		self.paint(oBudget);
	}

	self.update_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		AJAX.call(XML.serialize(true, "Budget.getAll"), self.get_Response);
	}

	return self.init();
}