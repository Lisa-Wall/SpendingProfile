
function ExpenseTable(sTitle, sCall)
{
	var self = this;

	self.init = function()
	{
		var sTable = "<div class='scrollpane'><table class='label'><tbody></tbody><tfoot><tr style='font-weight:bold' align='right'><td>Total:</td><td></td><td>100%</td></tr></tfoot></table></div>";
		var sButton  = "<div style='padding-top:10px' align='center'><input type='button' class='button' value='Close' style='width:70px'/></div>";

		self.oWindow = UI_Window(sTitle, false, false);
		self.oWindow.windowPane.className = "windowpane";
		self.oWindow.windowPane.innerHTML = sTable + sButton;

		self.oTable = self.oWindow.windowPane.getElementsByTagName("TBODY")[0];
		self.oTotal = self.oWindow.windowPane.getElementsByTagName("TFOOT")[0].rows[0].cells[1];

		var oClose = self.oWindow.windowPane.getElementsByTagName("INPUT")[0];

		oClose.onclick = self.oWindow.onClose;
		self.isVisible = self.oWindow.isVisible;
	}

	self.show = function()
	{
		UI.centerWindow(self.oWindow);
		self.update();
	}

	self.update = function()
	{
		if (self.oWindow.isVisible())
		{
			AJAX.call(XML.serialize(true, sCall), self.update_Response);
		}
	}

	self.update_Response = function(oExpense)
	{
		XML.clear(self.oTable);

		var iTotal = oExpense.getAttribute("Total");
		self.oTotal.innerHTML = Utility.formatCurrency(iTotal, oSession.currency);

		for (var oExpense = oExpense.firstChild; oExpense != null; oExpense = oExpense.nextSibling)
		{
			var pRow = self.oTable.insertRow(-1);

			pRow.style.whiteSpace = "nowrap";

			pRow.insertCell(-1).innerHTML = oExpense.getAttribute("Name");
			pRow.insertCell(-1).innerHTML = oSession.currency + oExpense.getAttribute("Total");
			pRow.insertCell(-1).innerHTML = oExpense.getAttribute("Percent") + "%";

			pRow.cells[0].className = "table_cells";
			pRow.cells[1].className = "table_cell_amount";
			pRow.cells[2].className = "table_cell_amount";
		}

		self.oWindow.windowPane.firstChild.style.height = (self.oTable.rows.length > 10 ? "400px" : "");
	}

	self.init();
}
