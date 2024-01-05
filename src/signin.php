<? $bHttps = true; include_once("inc_header.php"); ?>
<? include_once("inc_menu.php"); ?>

<script>
	var oSignIn = null;
	function loadPage(){ oSignIn = new SignIn(); }
</script>

<div class="page_area">

	<div class="page_title">Sign In</div>

	<p>Sign in securely with your email address and password.</p>

	<span id="signin/waitmessage" style="color:red;display:none">Please wait...</span>
	<span id="signin/message" style="color:red"></span>&nbsp;<br/>

	<br/>
	<center>
		<form name="signin" method="post" onsubmit="oSignIn.submit(); return false;" action="main.php">
			<fieldset class="page_fieldset" style="width:340px">
				<legend class="page_fieldset_title">Sign In</legend>

				<table class="page_text" width="340px" border="0" cellpadding="0" cellspacing="5px" align="center">
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr>
						<td class="form_label">Email Address:</td>
						<td><input name="email" class="form_input" type="text" tabindex='1' value="" style="width:200px" maxlength="64"/></td>
					</tr>

					<tr>
						<td class="form_label">Password:</td>
						<td><input name="password" class="form_input" type="password" tabindex='2' value="" style="width:200px" maxlength="64"/></td>
					</tr>

					<tr height="40">
						<td colspan="2" align="center">
							<img id="signin/loader" style="visibility:hidden;vertical-align:middle" src="<?=$sImage?>icon=loader.gif"/>
							<input name="apply" class="button" type="submit" tabindex='3' value="Sign In"/>
						</td>
					</tr>
				</table>
			</fieldset>
		</form>
	</center>

	<p align="center">
		Forgot your password? Reset it <a href="resetpassword.php">here</a>.<br/>
		No account? Create one <a href="createaccount.php">here</a>.
	</p>

</div>

<? include_once("inc_footer.php"); ?>