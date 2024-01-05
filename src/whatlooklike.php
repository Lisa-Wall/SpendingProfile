<? include_once("inc_header.php"); ?>
<? include_once('inc_menu.php'); ?>

<div class="page_area">

	<div class="page_title">What Does it Look Like?</div>

	<p>Click on the images below to enlarge them and see more detail.</p>

	<span class="page_subtitle">Main page</span>
	<a href="whatlooklike_main.php"><img src="<?=$sImage?>image=thumbnail/mainpage.png" style="border:solid 1px gray" title="Click to see the full size image with a description of each area"/></a>
	<p>The main page shows all transactions for the selected time period. Pie charts give your spending breakdown by category and vendor. You can add new transactions, update existing transactions, add new categories, or select a different time period to view. The balance of your income and expenses appears at the bottom of the page.</p>
	<p>Click on the image to enlarge it and see descriptions of each area.</p>

	<span class="page_subtitle">Budget Page</span>

	<a href="whatlooklike_sample.php?page=budget"><img src="<?=$sImage?>image=thumbnail/budget.png" style="border:solid 1px gray" title="Click to see the full size image"/></a>
	<p>Create a monthy budget and compare it to your actual spending.</p>

	<span class="page_subtitle">Graphs page</span>
	<a href="whatlooklike_sample.php?page=graphcategoriesbar"><img src="<?=$sImage?>image=thumbnail/graphcategoriesbar_medium.png" style="border:solid 1px gray" title="Click to see the full size image"/></a>
	<p>Bar and line graphs show your spending habits over time. View your overall totals, and your totals by category. It`s easy spot trends in your spending!</p>

	<p>
		<b>Totals Graph:</b> Shows your totals over all categories. Also shows the totals for all <span title="Fixed transactions repeat regularly, such as monthly rental payments." style="color:blue;text-decoration:underline;cursor:hand">fixed</span> and <span title="Variable transactions change from month to month, such as clothing purchases, for example." style="color:blue;text-decoration:underline;cursor:hand">variable</span> transactions.<br/>
		<b>Categories Graph:</b> Shows your totals by category over time.  Pick the categories you wish to display.You can pick the start and end dates of the time period you wish to display.
	</p>

	<span class="page_subtitle">Import Transactions</span>
	<a href="whatlooklike_sample.php?page=import"><img src="<?=$sImage?>image=thumbnail/import.png" style="border:solid 1px gray" title="Click to see the full size image"></a>
	<p>Import transactions from your bank account or credit card.</p>

</div>

<? include_once("inc_footer.php"); ?>