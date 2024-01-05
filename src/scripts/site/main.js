
function Manager()
{
	var self = this;

	self.oHighlight = null;
	self.oVendors = null;
	self.oAccounts = null;
	self.oCategories = null;

	self.init = function()
	{
		//oAd = new Ad("ad");
		oReceiptWindow = new ReceiptWindow();
		oFeedback = new Feedback("feedback");
		oBalanceSummary = new BalanceSummary("balancesummary");
		oCalendarSelector = new UI_CalendarSelector("calendarselector", true, self.period);

		oTransactionInput = new TransactionInput(self);
		oTransactionTable = new TransactionTable("transaction/table", self);
		oTransactionToolbar = new TransactionToolbar("toolbar", self);

		oVendorEditor = new TagEditor(new TagManager("Vendor", self), 63, "Vendor", "Vendor Editor", "Delete or rename vendors using the table below.<br/>Use the '" + oSession.delimiter + "' symbol to create subvendors.<br/>ex: Bakeries" + oSession.delimiter + "Bob's Bread Shop<br/>");
		oAccountEditor = new TagEditor(new TagManager("Account", self), 63, "Account", "Account Editor", "Delete or rename accounts using the table below.<br/>Use the '" + oSession.delimiter + "' symbol to create subaccounts. ex: Personal" + oSession.delimiter + "Savings.<br/>");
		oCategoryEditor = new TagEditor(new TagManager("Category", self), 128, "Category", "Category Editor", "Delete or rename categories using the table below.<br/>Use the '" + oSession.delimiter + "' symbol to create subcategories. ex: Car" + oSession.delimiter + "Gas.<br/>");

		oVendorExpenses = new ExpenseTable("Expenses by Vendor", "Vendor.expenses");
		oCategoryExpenses = new ExpenseTable("Expenses by Category", "Category.expenses");

		oVendorPie = new PieGraphControll("piegraph/vendor", "vendors", "Expenses by Vendor", oVendorExpenses.show);
		oCategoryPie = new PieGraphControll("piegraph/category", "categories", "Expenses by Category", oCategoryExpenses.show);

		self.getVendorTree();
		self.getAccountTree();
		self.getCategoryTree();

		self.get();
	}

	self.add = function(sForm)
	{
		AJAX.call(sForm, self.add_Response);
	}

	self.modify = function(sId, sValue, sField)
	{
		AJAX.call(XML.serialize(true, "Transaction.update", "Id", sId, "Field", sField, "Value", sValue), self.modify_Response);
	}

	self.remove = function(aIds)
	{
		var sRequest = "";
		for (var i = 0; i < aIds.length; i++) sRequest += "<Transaction Id='" + aIds[i] + "'/>";

		AJAX.call("<Transaction.deleteAll>" + sRequest + "</Transaction.deleteAll>", self.remove_Response);
	}

	self.copy = function(aIds, sDate, bUseFullDate)
	{
		var sRequest = "";
		for (var i = 0; i < aIds.length; i++) sRequest += "<Transaction Id='" + aIds[i] + "'/>";

		AJAX.call("<Transaction.copy Date='" + sDate + "' UseFullDate='" + (bUseFullDate ? 'true' : 'false') + "'>" + sRequest + "</Transaction.copy>", self.copy_Response);
	}

	self.period = function(sFrom, sTo)
	{
		AJAX.call(XML.serialize(true, "Transaction.getPeriodFilter", "From", sFrom, "To", sTo), self.get_Response);
	}

	self.filter = function(sFilter)
	{
		AJAX.call(XML.serialize(true, "Transaction.getPeriodFilter", "Filter", sFilter), self.get_Response);
	}

	self.orderBy = function(sOrderBy, sOrderIn)
	{
		AJAX.call(XML.serialize(true, "Transaction.getPeriodFilter", "OrderBy", sOrderBy, "OrderIn", (sOrderIn ? "ASC" : "DESC")), self.orderBy_Response);
	}



	self.get = function()
	{
		AJAX.call(XML.serialize(true, "Transaction.getPeriodFilter"), self.get_Response);
	}

	self.getVendorTree = function()
	{
		AJAX.call("<Vendor.getTree />", function(oC, sC, bS){ if (!bS) return; self.oVendors = oC; } );
	}

	self.getAccountTree = function()
	{
		AJAX.call("<Account.getTree />", function(oC, sC, bS){ if (!bS) return; self.oAccounts = oC; } );
	}

	self.getCategoryTree = function()
	{
		AJAX.call("<Category.getTree />", function(oC, sC, bS){ if (!bS) return; self.oCategories = oC; } );
	}

	self.refresh = function(sTag)
	{
		self.get();
		if (sTag == "Vendor") self.getVendorTree();
		if (sTag == "Account") self.getAccountTree();
		if (sTag == "Category") self.getCategoryTree();
	}

	self.highlight = function()
	{
		if (self.oHighlight != null)
		{
			if (self.oHighlight.firstChild != null)
			{
				for (var oTransaction = self.oHighlight.firstChild; oTransaction != null; oTransaction = oTransaction.nextSibling)
				{
					oTransactionTable.highlight(oTransaction.getAttribute("Id"));
				}
			}
			else if ((sId = self.oHighlight.getAttribute("Id")) != null)
			{
				oTransactionTable.highlight(sId);
			}

			self.oHighlight = null;
		}
	}

	self.getVendors = function() { return self.oVendors; }
	self.getAccounts = function() { return self.oAccounts; }
	self.getCategories = function() { return self.oCategories; }
	self.getSelected = function() { return oTransactionTable.getSelected(); }
	self.getSelectedDate = function() { return oCalendarSelector.oMonthSelector.sDate.toDate(); }
	self.showVendorEditor = function() { oVendorEditor.show(); }
	self.showAccountEditor = function() { oAccountEditor.show(); }
	self.showCategoryEditor = function() { oCategoryEditor.show(); }



	self.get_Response = function(oTransactions, sTransactions, bSuccess)
	{
		if (!bSuccess) return;
		if (oTransactions.nodeName == "Error") return alert(oTransactions.getAttribute("Message"));

		var oToDate = CDate.fromString(oTransactions.getAttribute("To"));
		var oFromDate = CDate.fromString(oTransactions.getAttribute("From"));

		//Set the filter.
		oTransactionToolbar.setFilter(oTransactions.getAttribute("Filter"));

		//Update the month selector.
		oCalendarSelector.set(oFromDate.toText(), oToDate.toText());

		//Update pies
		oVendorPie.update();
		oCategoryPie.update();

		//Update the table
		oTransactionTable.update(oTransactions);

		//Update the balance summary.
		oBalanceSummary.update(false, oTransactions.getAttribute("Debit"), oTransactions.getAttribute("Credit"), oTransactions.getAttribute("Total"));

		if (oVendorExpenses.isVisible()) oVendorExpenses.update();
		if (oCategoryExpenses.isVisible()) oCategoryExpenses.update();

		self.highlight();
	}

	self.orderBy_Response = function(oTransactions, sTransactions, bSuccess)
	{
		if (!bSuccess) return;
		if (oTransactions.nodeName == "Error") return alert(oTransactions.getAttribute("Message"));

		oTransactionTable.update(oTransactions);
	}

	self.copy_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;
		if (oResponse.nodeName == "Error") return alert(oResponse.getAttribute("Message"));

		if (oResponse.getAttribute("Visible") == "true")
		{
			self.oHighlight = oResponse;
			self.get();
		}
		else
		{
			alert("Transactions copied successfully to " + oResponse.getAttribute("Month"));
		}
	}

	self.remove_Response = function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess) return;

		//Table
		oTransactionTable.removeAll(oResponse);

		//BalanceSummary
		oBalanceSummary.update(false, oResponse.getAttribute("Debit"), oResponse.getAttribute("Credit"), oResponse.getAttribute("Total"));

		//Update pies
		oVendorPie.update();
		oCategoryPie.update();

		if (oVendorExpenses.isVisible()) oVendorExpenses.update();
		if (oCategoryExpenses.isVisible()) oCategoryExpenses.update();
	}

	self.add_Response = function(oTransaction, sTransaction, bSuccess)
	{
		if (!bSuccess) return alert("Error adding transactoin.");
		if (oTransaction.nodeName == "Error") return alert(oTransaction.getAttribute("Message"));

		oTransactionInput.clearForm();

		if (oTransaction.getAttribute("Visible") == "true")
		{
			self.oHighlight = oTransaction;
			self.get();
/*
			//Update pies
			oVendorPie.update();
			oCategoryPie.update();

			//Update the table
			oTransactionTable.add(oTransaction);

			//Update the balance summary.
			oBalanceSummary.update(false, oTransactions.getAttribute("Debit"), oTransactions.getAttribute("Credit"), oTransactions.getAttribute("Total"));

			if (oVendorExpenses.isVisible()) oVendorExpenses.update();
			if (oCategoryExpenses.isVisible()) oCategoryExpenses.update();
*/
		}
		else
		{
			alert("Transaction successfully added but is not in the current view. To view the transaction switch time period to " + oTransaction.getAttribute("Date") + ".");
		}

		if (oTransaction.getAttribute("VendorCreated") == "1") self.getVendorTree();
		if (oTransaction.getAttribute("AccountCreated") == "1") self.getAccountTree();
		if (oTransaction.getAttribute("CategoryCreated") == "1") self.getCategoryTree();
	}

	self.modify_Response = function(oResponse, sResponse, bSuccess)
	{
		//TODO: handle error.
		if (!bSuccess) return;

		sId = oResponse.getAttribute("Id");
		sValue = oResponse.getAttribute("Value");
		sField = oResponse.getAttribute("Field");
		sVisible = oResponse.getAttribute("Visible");

		//If the transaction is not visible then remove it otherwise update it.
		if (sVisible == "false")
		{
			alert("The updated transaction (id: "+sId+") is no longer visible due to selected period or search.");
			oTransactionTable.remove(sId, true);
		}
		else oTransactionTable.modify(sId, sValue, sField);


		//Update the Graphs if fields are amount, category, vendor
		if (sVisible == "false" || sField == "Amount" || sField == "Category" || sField == "Vendor")
		{
			oVendorPie.update();
			oCategoryPie.update();

			if (sVisible == "false" || sField == "Amount")
			{
				oBalanceSummary.update(false, oResponse.getAttribute("Debit"), oResponse.getAttribute("Credit"), oResponse.getAttribute("Total"));
			}
		}

		if (oVendorExpenses.isVisible()) oVendorExpenses.update();
		if (oCategoryExpenses.isVisible()) oCategoryExpenses.update();
		if (oResponse.getAttribute("VendorCreated") == "1") self.getVendorTree();
		if (oResponse.getAttribute("AccountCreated") == "1") self.getAccountTree();
		if (oResponse.getAttribute("CategoryCreated") == "1") self.getCategoryTree();
	}
}

var oAd = null;
var oFeedback = null;
var oReceiptWindow = null;
var oVendorEditor = null;
var oAccountEditor = null;
var oCategoryEditor = null;
var oBalanceSummary = null;
var oCalendarSelector = null
var oTransactionInput = null;
var oTransactionTable = null;