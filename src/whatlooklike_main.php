<?
  include_once("server/config.php");
  include_once("inc_session.php");
?>
<html>
	<head>
		<title>SpendingProfile.com - Track your finances online!</title>
		<meta name="description" content="Track your finances online at SpendingProfile.com" />
		<meta name="keywords" content="Personal Finance, Financial Planning, Financial Advisor, Budget, Budgeting, Spending, Spending Habits, Debt, Credit Card Debt, Financial Goals" />
		<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
		<link rel="STYLESHEET" type="text/css" href="styles/index.php"></link>
		<script src="scripts/index.php?page=<?=$_SERVER['SCRIPT_NAME']?>"></script>
		<script>

			UI.image = "<?=$sImage?>";
			var oTooltip = UI_Tooltip();

			var FIXED_VAR = "Fixed/Variable Option<hr/>This controls which transactions are used in the pie charts.<br/>Transactions are tagged as fixed (repeat regularly) or variable.<br/>When calculating totals for the pie charts, you can decide to<br/> include only fixed transactions, only variable transactions, or both.";
			var PIE_CHARTS = "Pie Charts<hr/>Show the breakdown of your spending by category and vendor.";
			var PIE_CHART_SUMMARY_TABLES = "Pie Chart Summary Tables<hr/>These tables correspond to the pie charts. They also show <br/>the amounts and percentages for each category or vendor.";
			var TRANSACTION_PERIOD = "Transaction Period<hr/>Select the year and month you wish to display. <br/>You can view all transactions in a given year by choosing the <i>All</i> &nbsp;button.";
			var TRANSACTION_FORM = "Transaction Entry Form<hr/>This is where you enter your income and expenses and assign them to categories. <br/>You add new categories using the Category Manager, which you open by clicking on <img src='<?=$sImage?>icon=edit.png'/>. <br/><br/>You can also set the transaction type to be fixed (repeats regularly) or variable. <br/>Your most common categories and vendors are shown as links for quick access.";
			var TRANSACTION_TABLE = "Transaction Table<hr/>Shows all transactions for the selected time period.<br/>Income is shown in bold with a plus symbol.<br/>The overall totals and balance appear at the bottom of the table.";

			function showTooltip(oEvent, sId)
			{
				var oEvent = (oEvent ? oEvent : event);
				oTooltip.show(eval(sId), oEvent.clientX+15, oEvent.clientY+15);
			}
			
			function showCoords(oEvent)
			{
				oEvent=(oEvent?oEvent:event);
				//document.getElementById('coords').innerHTML = oEvent.offsetX + "," + oEvent.offsetY;
			}

		</script>
	</head>
	<body class="body" left="0" topmargin="4" leftmargin="0">

<!--div id="coords"></div -->

		<center>
			<!-- Instructions -->
			<div style="width:998" align="center" class="page_subtitle">
				<input id="button" type="button" value="Back to screen shots" onclick="history.go(-1)"/><br/>
				<b>Move your mouse around the image to see explanations of the different areas.</b>
			</div>

			<!-- Image Map -->
			<map name="hot_areas" id="hot_areas">
				<area shape="rect" coords="300,190,950,230" onmousemove="showTooltip(event, this.id)" onmouseout="oTooltip.hide()" id="TRANSACTION_PERIOD">
				<area shape="rect" coords="0,190,250,730"   onmousemove="showTooltip(event, this.id)" onmouseout="oTooltip.hide()" id="PIE_CHARTS">
				<area shape="rect" coords="190,230,950,520" onmousemove="showTooltip(event, this.id)" onmouseout="oTooltip.hide()" id="TRANSACTION_FORM">
				<area shape="rect" coords="190,565,950,790" onmousemove="showTooltip(event, this.id)" onmouseout="oTooltip.hide()" id="TRANSACTION_TABLE">
				
				<!-- <area shape="rect" coords="190,790,950,860"  onmousemove="showTooltip(event, this.id)" onmouseout="oTooltip.hide()" id="PIE_CHART_SUMMARY_TABLES"> -->
				<!-- <area shape="rect" coords="0,140,165,175"   onmousemove="showTooltip(event, this.id)" onmouseout="oTooltip.hide()" id="FIXED_VAR">	-->
			</map>

			<!-- Image. Note the map is not used directly; I use the coords in my own implementation of hot areas,
					 in order to allow simple cross-browser compliance. -->
			<img src="<?=$sImage?>image=sample/mainpagefull.png" border="0" id="screenshot" usemap="#hot_areas" onmousemove="showCoords(event)" >
		</center>
	</body>
</html>
