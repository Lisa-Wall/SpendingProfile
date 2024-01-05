<? include_once("inc_header.php"); ?>
<? include_once('inc_menu.php'); ?>

<div class="page_area">
	<div class="page_title">Sitemap</div>
	<br/>
	<span>You can link to the pages below on our website.</span>
	<br/><br/>

	<table class="page_text" width="100%">
		<tr>
			<td valign="top">
				<ul>
					<li><a href="index.php">Home</a></li>
					<li><a href="whatisit.php">What is Spending Profile?</a></li>
					<li><a href="whatlooklike.php">What does it look like?</a></li>
					<li><a href="howuse.php">How do you use it?</a></li>
					<li><a href="createaccount.php">Free Account</a></li>
					<!-- li><a href="fullservice.php">Full Service Option</a></li -->
					<li><a href="signin.php">Sign in</a></li>
					<li><a href="demo.php">Live Demo</a></li>
					<li><a href="aboutus.php">About Us</a></li>
					<li><a href="http://www.spendingprofile.com/blog">Blog</a></li>
					<li><a href="contact.php">Contact</a></li>
					<li><a href="privacy.php">Privacy</a></li>
					<li><a href="security.php">Security</a></li>
				</ul>
			</td>
			<td valign="top">
				<?if ($bLoggedOn) { ?>

				<ul>
					<li><a href="main.php">Main</a></li>
					<li><a href="graphs.php">Graphs</a></li>
					<li><a href="budget.php">Budget</a></li>
					<!--li><a href="goals.php">Goals</a></li-->
					<li><a href="import.php">Import</a></li>
					<li><a href="account.php">Account</a></li>
					<ul>
						<li><a href="account.php">Account Information</a></li>
						<li><a href="account.php?page=password">Change Password</a></li>
						<li><a href="account.php?page=email">Change Email</a></li>
						<li><a href="account.php?page=currency">Change Currency Symbol</a></li>
						<li><a href="account.php?page=preference">Preferences</a></li>
						<li><a href="account.php?page=tellafriend">Tell-A-Friend!</a></li>
					</ul>
				</ul>

				<? } ?>
			</td>
		</tr>
	</table>
</div>

<? include_once("inc_footer.php"); ?>