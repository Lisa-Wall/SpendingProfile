<? include_once("inc_header.php"); ?>
<? include_once('inc_menu.php'); ?>

<? function debug(){} function error(){} ?>
<? include_once("server/core/utility.php"); ?>
<? include_once("server/core/database.php"); ?>

<script>
function toggleMoreLess(oElement)
{
	var bMore = (oElement.previousSibling.style.display == "");
	oElement.innerHTML = (bMore ? "More &gt;&gt;" : "Less &lt;&lt;");
	oElement.previousSibling.style.display = (bMore ? "none" : "");
}
</script>

<div class="page_area">

	<div class="page_title">About Us</div>

	<p>Spending Profile was founded in June 2005 by Lisa Wall, a computer science graduate from the University of Ottawa. Originally designed to help her track her own finances, it has since grown into a much larger endeavour. The main website as well as its accompanying financial blog continue to help people from around the world understand and track their finances.</p>

</div>

<? include_once("inc_footer.php"); ?>