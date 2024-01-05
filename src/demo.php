<? include_once('inc_header.php'); ?>
<? include_once('inc_menu.php'); ?>

<script>
function startDemo()
{
	//Show loading icon.

	AJAX.call("<Demo.start />", function(oResponse, sResponse, bSuccess)
	{
		if (!bSuccess || oResponse.getAttribute("Type") != "OK")
		{
			document.getElementById("demo/message").style.innerHTML = "An errored occured while preparing the demo, please try again later.";
		}
		else
		{
			window.location = "main.php";
		}

	}, loading);
}

function loading(bLoading)
{
	if (bLoading) document.getElementById("demo/message").style.innerHTML = "";
	document.getElementById("demo/loader").style.display = (bLoading ? "" : "none");
}

</script>

<div class="page_area">

	<div class="page_title">Live Demo</div>

	<p>Try the demo! It will give you a better idea of the service and help you decide if you want to create your own account.</p>
	<p>You will be logged in as a guest. The demo is fully-functional. Explore all the features by adding transactions and categories, viewing the graphs and pie charts, and clicking on buttons just to see what they do! Don`t be afraid of breaking anything. This account is regenerated periodically.</p>

	<form name="demo"> </form>
	<p align="center"><a href="javascript:startDemo()">Start the Demo!</a></p>

	<p align="center" id="demo/loader" style="color:red;display:none"><img src="<?=$sImage?>icon=loader.gif"/> Preparing demo, please wait...</p>
	<p align="center" id="demo/message" style="color:red"></p>

</div>

<? include_once('inc_footer.php'); ?>