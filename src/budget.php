<? $PAGE_INNER = true; $bHttps = true; $bSecured = true; include_once("inc_header.php"); ?>

<script>

var oBudget = null;

function loadPage()
{
	oBudget = Budget();
}

</script>

<div id="budget/monthselector" align="center" style="padding-bottom:10px"></div>
<table align="center" width="840px" border="0" class="label" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<table width="100%" class='ui_toolbar'>
				<tr>
					<td>View: <select id="budget/activeonly" class="dropdown" style="vertical-align:middle"><option value="1">Active Categories only</option><option value="0">All Categories</option></select></td>
					<td>Average Period: <select id="budget/averageperiod" class="dropdown" style="vertical-align:middle"><option value="-1 month">1 Month</option><option value="-3 month">3 Months</option><option value="-6 month">6 Months</option><option value="-12 month">12 Months</option></select></td>
					<td align="right" width="430px"><img src="<?=$sImage?>icon=help.gif" class="clickicon" onload="UI.setHelptip(this, 'Budget', sBudgetHelptip)"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2"><div id="budget/table" style="width:100%" class="label"></div></td>
	</tr>
</table>

<? include_once("inc_footer.php"); ?>