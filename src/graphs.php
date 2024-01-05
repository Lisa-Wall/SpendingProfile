<? $PAGE_INNER = true; $bHttps = true; $bSecured = true; include_once("inc_header.php"); ?>

<script>

var oAnalysis = null;

function loadPage()
{
	oAnalysis = new Analysis();
}

</script>

<table align="center" width="900px" border="0" class="label">
	<tr>
		<td colspan="3"><div id="analysis/periodselector" align="center"></div></td>
	</tr>
	<tr><td colspan="3" height="10px"></td></tr>
	<tr>
		<td width="130px" valign="top" style="padding-top:60px">
			<fieldset class="label">
				<legend>Available Graphs</legend>
				<input onclick="oAnalysis.setViewBy('TOTALS')" id="analysis/viewby/totals" name="analysis/availablegraphs" type="radio" checked="true"><span onclick="this.previousSibling.click()" style="cursor:default"> Overall Totals</span><br/>
				<input onclick="oAnalysis.setViewBy('CATEGORY')" id="analysis/viewby/category" name="analysis/availablegraphs" type="radio"><span onclick="this.previousSibling.click()" style="cursor:default"> Totals by Category</span>
			</fieldset><br/>
			<fieldset class="label">
				<legend>Graph Type</legend>
				<input onclick="oAnalysis.setGraphType('BAR')" id="analysis/graphtype/bar" name="analysis/graphtype" type="radio" checked="true"><span onclick="this.previousSibling.click()" style="cursor:default"> <img src="<?=$sImage?>icon=bargraph.png"  style="vertical-align:bottom"/> Bar Graph</span><br/>
				<input onclick="oAnalysis.setGraphType('LINE')" id="analysis/graphtype/line" name="analysis/graphtype" type="radio"><span onclick="this.previousSibling.click()" style="cursor:default"> <img src="<?=$sImage?>icon=linegraph.png" style="vertical-align:bottom"/> Line Graph</span>
			</fieldset>
		</td>
		<td width="550px">
			<img id="analysis/image" width="550px" height="300px" usemap="#analysis/map" onload="oAnalysis.updateMap()"/>
			<map id="analysis/map" name="analysis/map"></map>
		</td>
		<td valign="top" style="padding-top:60px;padding-left:10px">

			<fieldset id="analysis/totals">
				<legend>Overall Totals <img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Overall Totals', sOverallTotalsHelptip)"/></legend>
				<table class="label">
					<tr>
						<td><img src="<?=$sImage?>icon=legend1.png"/></td>
						<td><input onclick="oAnalysis.setTotalIncome(this.checked)" id="analysis/totals/income" type="checkbox"/><span onclick="this.previousSibling.click()" style="cursor:default">Total Income</span></td>
					</tr>
					<tr>
						<td><img src="<?=$sImage?>icon=legend2.png"/></td>
						<td><input onclick="oAnalysis.setTotalExpenses(this.checked)" id="analysis/totals/expenses" type="checkbox"/><span onclick="this.previousSibling.click()" style="cursor:default">Total Expenses</span></td>
					</tr>
					<tr>
						<td></td>
						<td><img src="<?=$sImage?>icon=legend3.png" style="vertical-align:bottom"/> Variable Expenses</td>
					</tr>
					<tr>
						<td></td>
						<td><img src="<?=$sImage?>icon=legend4.png" style="vertical-align:bottom"/> Fixed Expenses</td>
					</tr>
				</table>
			</fieldset>

			<fieldset id="analysis/categories" style="display:none">
				<legend>Totals by Category <img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Totals by Category', sTotalsByCategoryHelptip)"/></legend>
				<table id="analysis/categories/table"class="label">
					<tr>
						<td><img src="<?=$sImage?>icon=legend1.png"/></td>
						<td></td>
					</tr>
					<tr>
						<td><img src="<?=$sImage?>icon=legend2.png"/></td>
						<td></td>
					</tr>
					<tr>
						<td><img src="<?=$sImage?>icon=legend3.png"/></td>
						<td></td>
					</tr>
					<tr>
						<td><img src="<?=$sImage?>icon=legend4.png"/></td>
						<td></td>
					</tr>
				</table>
			</fieldset>

		</td>
	</tr>
	<tr>
		<td colspan="3" class="page_subtitle" align="center" Id="analysis/title"></td>
	</tr>
</table>

<? include_once("inc_footer.php"); ?>