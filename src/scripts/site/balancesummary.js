
function BalanceSummary(sId)
{
	var self = document.getElementById(sId);

	self.init = function()
	{
		var sTable = "<table class='balancesummary' cellspacing='0'><tr><td class='balancesummary_text'>Total Income:</td><td class='balancesummary_text'>$00.00</td><td rowspan='3' class='balancesummary_image'><img/></td></tr><tr><td class='balancesummary_expenses'>Total Expenses:</td><td class='balancesummary_expenses'>$00.00</td></tr><tr><td class='balancesummary_text'>Balance:</td><td class='balancesummary_text'>$00.00</td></tr></table>";

		self.innerHTML = sTable;
		var oTable = self.firstChild;

		self.oCredit = oTable.rows[0].cells[1];
		self.oDebit = oTable.rows[1].cells[1];
		self.oTotal = oTable.rows[2].cells[1];
		self.oImage = oTable.rows[0].cells[2].firstChild;

		return self;
	}

	self.update = function(bFetch, iDebit, iCredit, iTotal)
	{
		if (bFetch)
		{
			//TODO: AJAX.call("<Graphs.getTotals />", self.update_Response);
		}
		else
		{
			self.oDebit.innerHTML = Utility.formatCurrency(iDebit, oSession.currency);
			self.oCredit.innerHTML = Utility.formatCurrency(iCredit, oSession.currency);
			self.oTotal.innerHTML = Utility.formatCurrency(iTotal, oSession.currency);
			self.oTotal.style.color = (iTotal < 0 ? "red" : "black");
		}

		var sRequest = XML.serialize(true, "Graphs.getBalanceSummary", "Width", "150", "Height", "50", "FontSize", "9.8");
		self.oImage.src = AJAX.url + "?request=" + encodeURIComponent(sRequest) + "&SID=" + Math.random();
	}

	self.update_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		var iTotal = oResponse.getAttribute("Total");
		var iDebit = oResponse.getAttribute("Debit");
		var iCredit = oResponse.getAttribute("Credit");

		self.oDebit.innerHTML = Utility.formatCurrency(iDebit, oSession.currency);
		self.oCredit.innerHTML = Utility.formatCurrency(iCredit, oSession.currency);
		self.oTotal.innerHTML = Utility.formatCurrency(iTotal, oSession.currency);
		self.oTotal.style.color = (iTotal < 0 ? "red" : "black");
	}

	return self.init();
}