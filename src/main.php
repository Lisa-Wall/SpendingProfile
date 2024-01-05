<? $PAGE_INNER = true; $bHttps = true; $bSecured = true; include_once("inc_header.php"); ?>

<script>

function loadPage()
{
	oManager = new Manager();
	oManager.init();
}

</script>
<!--blockquote style="color:red">
************************************************<br>
We are currently experiencing technical issues. There is no ETA at this point but we are actively working on the problem. So sorry for the inconvenience. -Lisa, Spending Profile<br>
************************************************
</blockquote-->
<table class="page_text" width="100%">
	<tr>
		<td valign="top" width="250px">
			<div id="piegraph/category"></div>
			<br/>
			<div id="piegraph/vendor"></div>
		</td>
		<td></td>
		<td valign="top">
			<div id="calendarselector" align="center"></div>

			<br/>
			<? include("addon/transactioninput.php"); ?>

			<br/>
			<table id="toolbar" width="100%"></table><div id="transaction/table"></div>

			<br/>
			<div id="balancesummary" align="center"></div>

			<br/>
			<hr style="border:1px solid #77a6c7"/>
			<div id="feedback"></div>
		</td>
	</tr>
</table>

<? include_once("inc_footer.php"); ?>