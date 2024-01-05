<?
$bHttps = true;
$bSecured = true;
$PAGE_INNER = true;
include_once("server/config.php");
include_once("inc_session.php");

?>
<html>
	<head>
		<link rel="STYLESHEET" type="text/css" href="styles/index.php"/>
		<script src="scripts/index.php?page=<?=$_SERVER['SCRIPT_NAME']?>"></script>
<script>

var oSession = <?=getJScriptSession()?>;
AJAX.url = oSession.server;
UI.image = "<?=$sImage?>";

var oPrintView = null;

function onLoad()
{
	oPrintView = new PrintView();
}

function PrintView()
{
	var self = this;

	self.init = function()
	{
		self.load(new Array("fromPeriod", "toPeriod", "vendorExpenses", "categoryExpenses", "vendorPie", "categoryPie", "balanceDebit", "balanceCredit", "balanceTotal", "balancePie", "totalCategoryExpense", "categoryExpenseTable", "transactionTable"));
		self.build();

		//Get the Transactions.
		AJAX.call(XML.serialize(true, "Transaction.getPeriodFilter"), self.get_Response);

		//Get the Category and Vendor Graph.
		self.vendorPie.src = AJAX.url + "?request=" + encodeURIComponent(XML.serialize(true, "Graphs.getPie", "Tag", "vendors", "Width", 240, "Height", 200)) + "&SID=" + Math.random();
		self.categoryPie.src = AJAX.url + "?request=" + encodeURIComponent(XML.serialize(true, "Graphs.getPie", "Tag", "categories", "Width", 240, "Height", 200)) + "&SID=" + Math.random();
		self.balancePie.src = AJAX.url + "?request=" + encodeURIComponent(XML.serialize(true, "Graphs.getBalanceSummary", "Width", "150", "Height", "50", "FontSize", "9.8")) + "&SID=" + Math.random();

		//Get the Category Expenses.
		AJAX.call(XML.serialize(true, "Category.expenses"), self.categoryExpenses_Response);


		return self;
	}

	self.load = function(aMap)
	{
		for (var i = 0; i < aMap.length; i++)
		{
			self[aMap[i]] = document.getElementById(aMap[i]);
		}
	}

	self.build = function()
	{
		var oTable = self.transactionTable;

		//Build table header.
		oTable = new UI_Table("transactionTable");
		oTable.addHeader(""        , "", false, 0, 20);
		oTable.addHeader("Id"      , "Id"       , true, true, 40 , null);
		oTable.addHeader("Fixed"   , "F/V"      , true, true, 25 , null);
		oTable.addHeader("Date"    , "Date"     , true, true, 60 , null);
		oTable.addHeader("Amount"  , "Amount"   , true, true, 60 , null);
		oTable.addHeader("Category", "Category" , true, true, 90 , null);
		oTable.addHeader("Vendor"  , "Vendor"   , true, true, 80 , null);
		oTable.addHeader("Account" , "Account"  , true, true, 70 , null);
		oTable.addHeader("Notes"   , "Notes"    , true, true, 100, null);

	}

	self.updateBalance = function(iDebit, iCredit, iTotal)
	{
		self.balanceDebit.innerHTML = Utility.formatCurrency(iDebit, oSession.currency);
		self.balanceCredit.innerHTML = Utility.formatCurrency(iCredit, oSession.currency);
		self.balanceTotal.innerHTML = Utility.formatCurrency(iTotal, oSession.currency);
		self.balanceTotal.style.color = (iTotal < 0 ? "red" : "black");
	}

	self.categoryExpenses_Response = function(oExpense, sExpenses, bSuccess)
	{
		XML.clear(self.categoryExpenseTable);

		var iTotal = oExpense.getAttribute("Total");
		self.totalCategoryExpense.innerHTML = Utility.formatCurrency(iTotal, oSession.currency);

		for (var oExpense = oExpense.firstChild; oExpense != null; oExpense = oExpense.nextSibling)
		{
			var pRow = self.categoryExpenseTable.insertRow(-1);

			pRow.style.whiteSpace = "nowrap";

			pRow.insertCell(-1).innerHTML = oExpense.getAttribute("Name");
			pRow.insertCell(-1).innerHTML = oSession.currency + oExpense.getAttribute("Total");
			pRow.insertCell(-1).innerHTML = oExpense.getAttribute("Percent") + "%";

			pRow.cells[0].className = "table_cells";
			pRow.cells[1].className = "table_cell_amount";
			pRow.cells[2].className = "table_cell_amount";
		}
	}

	self.get_Response = function(oTransactions, sTransactions, bSuccess)
	{
		if (!bSuccess) return;
		if (oTransactions.nodeName == "Error") return alert(oTransactions.getAttribute("Message"));

		var oToDate = CDate.fromString(oTransactions.getAttribute("To"));
		var oFromDate = CDate.fromString(oTransactions.getAttribute("From"));

		self.toPeriod.innerHTML = oTransactions.getAttribute("To");
		self.fromPeriod.innerHTML = oTransactions.getAttribute("From");

		//update pie totals
		self.vendorExpenses.innerHTML = oTransactions.getAttribute("Debit");
		self.categoryExpenses.innerHTML = oTransactions.getAttribute("Debit");

		//Update the table
		self.paint(oTransactions);

		//Update the balance summary.
		self.updateBalance(oTransactions.getAttribute("Debit"), oTransactions.getAttribute("Credit"), oTransactions.getAttribute("Total"));
	}

	//Paint

	self.paint = function(oTransactions)
	{
		self.oTable = self.transactionTable;
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
		var bDebit = (oTransaction.getAttribute("Debit")=="1");
		var bFixed = (oTransaction.getAttribute("Fixed")=="1");
		var sAmount = oTransaction.getAttribute("Amount");

		sRow += self.paintCell(iIndex + "."                         , "table_cell_first");
		sRow += self.paintCell(oTransaction.getAttribute("Id")      , "table_cells");
		sRow += self.paintCell((bFixed ? 'F' : 'V')                 , "table_cell_type");
		sRow += self.paintCell(oTransaction.getAttribute("Date")    , "table_cell_date");
		sRow += self.paintCell((bDebit?sAmount:"+"+sAmount)         , "table_cell_amount"+(bDebit?"":"_credit"));
		sRow += self.paintCell(oTransaction.getAttribute("Category"), "table_cells");
		sRow += self.paintCell(oTransaction.getAttribute("Vendor")  , "table_cells");
		sRow += self.paintCell(oTransaction.getAttribute("Account") , "table_cells");
		sRow += self.paintCell(oTransaction.getAttribute("Notes")   , "table_cells");

		return "<tr>"+sRow+"</tr>";
	}

	self.paintCell = function(sValue, sClass)
	{
		return "<td class='"+sClass+"'>"+sValue+"</td>";
	}

	//Events

	self.onPrint = function()
	{
		window.print();
	}

	self.onBack = function()
	{
		window.location = "main.php";
	}

	return self.init();
}

</script>

	</head>
	<body onload="onLoad()">

		<table class="label" cellpadding="0px" cellspacing="0px" align="center" width="1024px">
			<tr>
				<td><a href="index.php"><img src="<?=$sImage?>image=banner/logo_white.png"/></a></td>
				<td><a href="javascript:oPrintView.onPrint()"><br/>Print</a></td>
				<td><a href="javascript:oPrintView.onBack()" onload="UI.setTooltip(this.parentNode, 'Back to main page')"><br/>Back</a></td>
			</tr>
		</table>
		<br/>

		<table class="page_text" border="0" align="center" width="1024px">
			<tr>
				<td valign="top" width="250px">

					<table class='piegraph'>
						<tr><td class="piegraph_title">Expenses By Category</td></tr>
						<tr><td><img id="categoryPie"/></td></tr>
						<tr><td>Total Expenses: <span id="categoryExpenses"></span></td></tr>
					</table>

					<br/>
					<table class='piegraph'>
						<tr><td class="piegraph_title">Expenses By Vendor</td></tr>
						<tr><td><img id="vendorPie"/></td></tr>
						<tr><td>Total Expenses: <span id="vendorExpenses"></span></td></tr>
					</table>

					<br/>
					<table class="label" id="categoryTable">
						<thead>
							<tr><td class="piegraph_title" colspan="3">Expenses By Category</td></tr>
						</thead>
						<tbody class="table_content" id="categoryExpenseTable"></tbody>
						<tfoot>
							<tr style='font-weight:bold' align='right'>
								<td>Total:</td>
								<td id="totalCategoryExpense"></td>
								<td>100%</td>
							</tr>
						</tfoot>
					</table>

				</td>
				<td>&nbsp;</td>
				<td valign="top">
					<table class='ui_periodselector' align="center">
						<tr>
							<td><b>From:</b> <span id="fromPeriod"></span></td>
							<td><b>To:</b> <span id="toPeriod"></span></td>
						</tr>
					</table>

					<br/>
					<div id="transactionTable"></div>

					<br/>
					<div align="center">

						<table class='balancesummary' cellspacing='0'>
							<tr>
								<td class='balancesummary_text'>Total Income:</td>
								<td class='balancesummary_text' id="balanceCredit">$00.00</td>
								<td rowspan='3' class='balancesummary_image'><img id="balancePie"/></td>
							</tr>
							<tr>
								<td class='balancesummary_expenses'>Total Expenses:</td>
								<td class='balancesummary_expenses' id="balanceDebit">$00.00</td>
							</tr>
							<tr>
								<td class='balancesummary_text'>Balance:</td>
								<td class='balancesummary_text' id="balanceTotal">$00.00</td>
							</tr>
						</table>
					</div>

				</td>
			</tr>
		</table>


	</body>
<html>
